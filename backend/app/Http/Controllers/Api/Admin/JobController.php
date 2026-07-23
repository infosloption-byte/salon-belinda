<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\JobItem;
use App\Models\JobPayment;
use App\Models\SalonJob;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Services\ActivityLogger;
use App\Support\InvoiceBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * JSON port of Admin\SalonJobController — see routes/api.php
 * /api/admin/jobs*. Same staff-scoping (staff can only reach jobs they
 * created or performed a treatment on), totals recalculation, and PDF
 * receipt generation as the Blade version.
 */
class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $jobs = SalonJob::query()
            ->with('customer')
            ->when(! Auth::user()->isAdminRole(), fn ($q) => $this->scopeToStaff($q))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('from'), fn ($q, $date) => $q->whereDate('job_date', '>=', $date))
            ->when($request->query('to'), fn ($q, $date) => $q->whereDate('job_date', '<=', $date))
            ->when($request->query('q'), function ($q, $search) {
                $q->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('job_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return response()->json(['jobs' => $jobs]);
    }

    /**
     * Data needed for the "new job" screen: customer search results (if a
     * query was given), the selected customer + their visit count, and the
     * appointment being converted (if any). No view — the React app builds
     * the two-step UI itself from this payload.
     */
    public function create(Request $request): JsonResponse
    {
        $customers = collect();
        if ($request->query('q')) {
            $search = $request->query('q');
            $customers = Customer::where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        $selectedCustomer = $request->query('customer_id')
            ? Customer::find($request->query('customer_id'))
            : null;

        $selectedCustomerVisitCount = 0;
        if ($selectedCustomer) {
            $visitsQuery = $selectedCustomer->jobs();
            if (! Auth::user()->isAdminRole()) {
                $visitsQuery->where(function ($q) {
                    $q->where('created_by', Auth::id());
                    if (Auth::user()->staff_id) {
                        $q->orWhereHas('items', fn ($q) => $q->where('staff_id', Auth::user()->staff_id));
                    }
                });
            }
            $selectedCustomerVisitCount = $visitsQuery->count();
        }

        $appointment = $request->query('appointment_id')
            ? Appointment::find($request->query('appointment_id'))
            : null;

        return response()->json([
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'selectedCustomerVisitCount' => $selectedCustomerVisitCount,
            'appointment' => $appointment,
        ]);
    }

    public function quickRegisterCustomer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $customer = Customer::create($data);
        ActivityLogger::log('customer.created', "Registered customer {$customer->name} ({$customer->phone}) from New Job screen", $customer);

        return response()->json(['customer' => $customer, 'message' => 'Customer registered.'], 201);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'job_date' => ['required', 'date'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $job = SalonJob::create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        // The appointment's job is done once it's turned into an actual
        // visit — this keeps it from sitting in the Appointments list
        // looking unhandled forever.
        if (! empty($data['appointment_id'])) {
            Appointment::where('id', $data['appointment_id'])->update(['status' => 'completed']);
        }

        ActivityLogger::log('job.created', "Started job #{$job->id} for {$job->customer->name}", $job);

        return response()->json(['job' => $job->load('customer'), 'message' => 'Job started — add treatments and payments.'], 201);
    }

    public function show(SalonJob $job): JsonResponse
    {
        $this->authorizeJobAccess($job);

        $job->load(['customer', 'appointment', 'items.staff', 'items.service', 'payments.recordedBy']);

        return response()->json([
            'job' => $job,
            'serviceCategories' => ServiceCategory::with('services')->orderBy('sort_order')->get(),
            'activeStaff' => Staff::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function addItem(Request $request, SalonJob $job): JsonResponse
    {
        $this->authorizeJobAccess($job);

        $data = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'custom_name' => ['nullable', 'string', 'max:150'],
            'custom_price' => ['nullable', 'integer', 'min:0'],
            'staff_id' => ['required', 'exists:staff,id'],
            'discount_type' => ['required', 'in:none,percent,fixed'],
            'discount_value' => array_filter([
                'nullable', 'numeric', 'min:0',
                $request->input('discount_type') === 'percent' ? 'max:100' : null,
            ]),
        ]);

        $staff = Staff::find($data['staff_id']);
        if (! $staff->is_active) {
            return response()->json(['message' => "{$staff->name} is deactivated and can't be assigned new treatments."], 422);
        }

        if ($data['service_id']) {
            $service = Service::find($data['service_id']);
            $serviceName = $service->name;
            $basePrice = $service->price;
        } else {
            if (! $data['custom_name'] || $data['custom_price'] === null) {
                return response()->json(['message' => 'Pick a service from the catalog, or enter a custom treatment name and price.'], 422);
            }
            $serviceName = $data['custom_name'];
            $basePrice = $data['custom_price'];
        }

        $item = JobItem::create([
            'job_id' => $job->id,
            'service_id' => $data['service_id'] ?? null,
            'service_name' => $serviceName,
            'staff_id' => $staff->id,
            'base_price' => $basePrice,
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'] ?? 0,
            'commission_percent' => $staff->commission_percent,
            'final_price' => 0, // computed by JobItem::saving()
            'commission_amount' => 0,
        ]);

        $job->recalculateTotals();
        ActivityLogger::log('job.item_added', "Added {$item->service_name} (LKR ".number_format($item->final_price).") to job #{$job->id}", $job);

        return response()->json(['job' => $job->fresh(['items.staff', 'items.service', 'payments']), 'message' => 'Treatment added.'], 201);
    }

    public function removeItem(SalonJob $job, JobItem $item): JsonResponse
    {
        $this->authorizeJobAccess($job);
        abort_unless($item->job_id === $job->id, 404);

        ActivityLogger::log('job.item_removed', "Removed {$item->service_name} from job #{$job->id}", $job);
        $item->delete();
        $job->recalculateTotals();

        return response()->json(['job' => $job->fresh(['items.staff', 'items.service', 'payments']), 'message' => 'Treatment removed.']);
    }

    public function addPayment(Request $request, SalonJob $job): JsonResponse
    {
        $this->authorizeJobAccess($job);

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'in:cash,card,bank_transfer'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:150'],
        ]);

        JobPayment::create([
            'job_id' => $job->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'paid_at' => $data['paid_at'] ?? now(),
            'recorded_by' => Auth::id(),
            'note' => $data['note'] ?? null,
        ]);

        $job->recalculateTotals();
        ActivityLogger::log('job.payment_recorded', 'Recorded LKR '.number_format($data['amount'])." payment ({$data['method']}) on job #{$job->id}", $job);

        return response()->json(['job' => $job->fresh(['items.staff', 'items.service', 'payments.recordedBy']), 'message' => 'Payment recorded.'], 201);
    }

    public function removePayment(SalonJob $job, JobPayment $payment): JsonResponse
    {
        $this->authorizeJobAccess($job);
        abort_unless($payment->job_id === $job->id, 404);

        ActivityLogger::log('job.payment_removed', 'Removed LKR '.number_format($payment->amount)." payment from job #{$job->id}", $job);
        $payment->delete();
        $job->recalculateTotals();

        return response()->json(['job' => $job->fresh(['items.staff', 'items.service', 'payments.recordedBy']), 'message' => 'Payment removed.']);
    }

    public function updateStatus(Request $request, SalonJob $job): JsonResponse
    {
        $this->authorizeJobAccess($job);

        $data = $request->validate(['status' => ['required', 'in:scheduled,in_progress,completed,cancelled']]);
        $job->update(['status' => $data['status']]);
        ActivityLogger::log('job.status_updated', "Job #{$job->id} marked {$data['status']}", $job);

        return response()->json(['job' => $job->fresh(), 'message' => 'Job status updated.']);
    }

    public function receiptPreview(SalonJob $job): Response
    {
        $this->authorizeJobAccess($job);
        $job->load(['customer', 'items.staff', 'payments']);

        return Pdf::loadView('admin.jobs.receipt', ['job' => $job, 'logo' => InvoiceBranding::logo()])
            ->setPaper('a4')
            ->stream("receipt-job-{$job->id}.pdf");
    }

    public function receiptDownload(SalonJob $job): Response
    {
        $this->authorizeJobAccess($job);
        $job->load(['customer', 'items.staff', 'payments']);

        return Pdf::loadView('admin.jobs.receipt', ['job' => $job, 'logo' => InvoiceBranding::logo()])
            ->setPaper('a4')
            ->download("receipt-job-{$job->id}.pdf");
    }

    /**
     * Staff logins may only open jobs they created, or where they performed
     * at least one treatment. Admins always have access.
     */
    private function authorizeJobAccess(SalonJob $job): void
    {
        $user = Auth::user();

        if ($user->isAdminRole()) {
            return;
        }

        $isCreator = $job->created_by === $user->id;
        $isPerformer = $user->staff_id && $job->items()->where('staff_id', $user->staff_id)->exists();

        abort_unless($isCreator || $isPerformer, 403, "You don't have access to this job.");
    }

    private function scopeToStaff($query)
    {
        $user = Auth::user();

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id);
            if ($user->staff_id) {
                $q->orWhereHas('items', fn ($q) => $q->where('staff_id', $user->staff_id));
            }
        });
    }
}
