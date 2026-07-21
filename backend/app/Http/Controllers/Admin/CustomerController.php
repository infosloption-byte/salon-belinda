<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
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

        return view('admin.customers.index', ['customers' => $customers, 'q' => $request->query('q'), 'isAdmin' => $isAdmin]);
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new Customer]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $customer = Customer::create($data);
        ActivityLogger::log('customer.created', "Registered customer {$customer->name} ({$customer->phone})", $customer);

        if ($request->boolean('return_to_lookup')) {
            return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer registered.');
        }

        return redirect()->route('admin.customers.index')->with('success', 'Customer registered.');
    }

    public function show(Customer $customer): View
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

        return view('admin.customers.show', [
            'customer' => $customer,
            'jobs' => $jobs,
            'visitCount' => $jobs->count(),
            'totalSpent' => $jobs->sum('total_paid'),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer->id);

        $customer->update($data);
        ActivityLogger::log('customer.updated', "Updated customer {$customer->name}", $customer);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->jobs()->exists()) {
            return back()->with('error', "{$customer->name} has job history and can't be deleted.");
        }

        ActivityLogger::log('customer.deleted', "Deleted customer {$customer->name}", $customer);
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer removed.');
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
