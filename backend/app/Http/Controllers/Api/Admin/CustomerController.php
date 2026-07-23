<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * JSON port of Admin\CustomerController — see routes/api.php
 * /api/admin/customers*. Same staff-scoped job-count visibility as the
 * Blade version (the customer registry itself is shared; job/revenue
 * visibility per customer is not, for non-admin logins).
 */
class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $isAdmin = Auth::user()->isAdminRole();
        $userId = Auth::id();
        $staffId = Auth::user()->staff_id;

        $customers = Customer::query()
            ->when($request->query('q'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount(['jobs' => function ($q) use ($isAdmin, $userId, $staffId) {
                if (! $isAdmin) {
                    $q->where(function ($q) use ($userId, $staffId) {
                        $q->where('created_by', $userId);
                        if ($staffId) {
                            $q->orWhereHas('items', fn ($q) => $q->where('staff_id', $staffId));
                        }
                    });
                }
            }])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return response()->json(['customers' => $customers, 'isAdmin' => $isAdmin]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $customer = Customer::create($data);
        ActivityLogger::log('customer.created', "Registered customer {$customer->name} ({$customer->phone})", $customer);

        return response()->json(['customer' => $customer, 'message' => 'Customer registered.'], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $jobsQuery = $customer->jobs()->latest('job_date');

        // Staff only see the jobs they're assigned to, even here — the
        // customer registry is shared, but job/revenue visibility isn't.
        if (! Auth::user()->isAdminRole()) {
            $user = Auth::user();
            $jobsQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id);
                if ($user->staff_id) {
                    $q->orWhereHas('items', fn ($q) => $q->where('staff_id', $user->staff_id));
                }
            });
        }

        $jobs = $jobsQuery->get();

        return response()->json([
            'customer' => $customer,
            'jobs' => $jobs,
            'visitCount' => $jobs->count(),
            'totalSpent' => $jobs->sum('total_paid'),
        ]);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $this->validated($request, $customer->id);

        $customer->update($data);
        ActivityLogger::log('customer.updated', "Updated customer {$customer->name}", $customer);

        return response()->json(['customer' => $customer, 'message' => 'Customer updated.']);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->jobs()->exists()) {
            return response()->json(['message' => "{$customer->name} has job history and can't be deleted."], 422);
        }

        ActivityLogger::log('customer.deleted', "Deleted customer {$customer->name}", $customer);
        $customer->delete();

        return response()->json(['message' => 'Customer removed.']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($ignoreId)],
            'email' => ['nullable', 'email', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
