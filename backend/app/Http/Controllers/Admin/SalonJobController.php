<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\JobItem;
use App\Models\JobPayment;
use App\Models\SalonJob;
use App\Models\Service;
use App\Models\Staff;
use App\Services\ActivityLogger;
use App\Support\InvoiceBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SalonJobController extends Controller
{
    public function index(Request $request): View
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

        return view('admin.jobs.index', ['jobs' => $jobs]);
    }

    /**
     * Step 1 of the daily-driver screen: find or register the customer
     * before the job itself is created.
     */
    public function create(Request $request): View
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

        return view('admin.jobs.create', [
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'selectedCustomerVisitCount' => $selectedCustomerVisitCount,
            'appointment' => $appointment,
            'q' => $request->query('q'),
        ]);
    }

    /**
     * Inline "no match — register new customer" from the create-job screen.
     * Registers the customer, then sends the admin/staff straight back to
     * step 2 (confirm job details) for that new customer.
     */
    public function quickRegisterCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $customer = Customer::create($data);
        ActivityLogger::log('customer.created', "Registered customer {$customer->name} ({$customer->phone}) from New Job screen", $customer);

        return redirect()->route('admin.jobs.create', array_filter([
            'customer_id' => $customer->id,
            'appointment_id' => $request->query('appointment_id'),
        ]));
    }

    public function store(Request $request): RedirectResponse
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

        return redirect()->route('admin.jobs.show', $job)->with('success', 'Job started — add treatments and payments below.');
    }

    public function show(SalonJob $job): View
    {
        $this->authorizeJobAccess($job);

        $job->load(['customer', 'appointment', 'items.staff', 'items.service', 'payments.recordedBy']);

        return view('admin.jobs.show', [
            'job' => $job,
            'serviceCategories' => \App\Models\ServiceCategory::with('services')->orderBy('sort_order')->get(),
            'activeStaff' => Staff::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function addItem(Request $request, SalonJob $job): RedirectResponse
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
            return back()->withErrors(['staff_id' => "{$staff->name} is deactivated and can't be assigned new treatments."])->withInput();
        }

        if ($data['service_id']) {
            $service = Service::find($data['service_id']);
            $serviceName = $service->name;
            $basePrice = $service->price;
        } else {
            if (! $data['custom_name'] || $data['custom_price'] === null) {
                return back()->withErrors(['custom_name' => 'Pick a service from the catalog, or enter a custom treatment name and price.'])->withInput();
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

        return back()->with('success', 'Treatment added.');
    }

    public function removeItem(SalonJob $job, JobItem $item): RedirectResponse
    {
        $this->authorizeJobAccess($job);
        abort_unless($item->job_id === $job->id, 404);

        ActivityLogger::log('job.item_removed', "Removed {$item->service_name} from job #{$job->id}", $job);
        $item->delete();
        $job->recalculateTotals();

        return back()->with('success', 'Treatment removed.');
    }

    public function addPayment(Request $request, SalonJob $job): RedirectResponse
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

        return back()->with('success', 'Payment recorded.');
    }

    public function removePayment(SalonJob $job, JobPayment $payment): RedirectResponse
    {
        $this->authorizeJobAccess($job);
        abort_unless($payment->job_id === $job->id, 404);

        ActivityLogger::log('job.payment_removed', 'Removed LKR '.number_format($payment->amount)." payment from job #{$job->id}", $job);
        $payment->delete();
        $job->recalculateTotals();

        return back()->with('success', 'Payment removed.');
    }

    public function updateStatus(Request $request, SalonJob $job): RedirectResponse
    {
        $this->authorizeJobAccess($job);

        $data = $request->validate(['status' => ['required', 'in:scheduled,in_progress,completed,cancelled']]);
        $job->update(['status' => $data['status']]);
        ActivityLogger::log('job.status_updated', "Job #{$job->id} marked {$data['status']}", $job);

        return back()->with('success', 'Job status updated.');
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
