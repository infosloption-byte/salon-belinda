@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="font-display text-2xl mb-1">{{ $customer->name }}</h2>
            <p class="text-sm opacity-60">{{ $customer->phone }} @if($customer->email) &middot; {{ $customer->email }} @endif</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.jobs.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">+ New Job</a>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-5">
            <p class="text-xs uppercase tracking-wide mb-2" style="color:#7A2E3A;">Visits</p>
            <p class="font-display text-3xl">{{ $customer->visitCount() }}</p>
        </div>
        <div class="bg-white p-5">
            <p class="text-xs uppercase tracking-wide mb-2" style="color:#7A2E3A;">Total Spent</p>
            <p class="font-display text-3xl">LKR {{ number_format($customer->totalSpent()) }}</p>
        </div>
    </div>

    @if ($customer->notes)
        <div class="bg-white p-5 mb-8">
            <p class="text-xs uppercase tracking-wide mb-2 opacity-60">Notes</p>
            <p class="text-sm">{{ $customer->notes }}</p>
        </div>
    @endif

    <h3 class="font-display text-xl mb-4">Job History</h3>
    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Date</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Subtotal</th>
                    <th class="p-4">Paid</th>
                    <th class="p-4">Balance</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customer->jobs as $job)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ $job->job_date->format('d M Y') }}</td>
                        <td class="p-4"><span class="badge" style="background:#F3ECE3;">{{ str_replace('_', ' ', $job->status) }}</span></td>
                        <td class="p-4">LKR {{ number_format($job->subtotal) }}</td>
                        <td class="p-4">LKR {{ number_format($job->total_paid) }}</td>
                        <td class="p-4" style="{{ $job->balance_due > 0 ? 'color:#7A2E3A;' : '' }}">LKR {{ number_format($job->balance_due) }}</td>
                        <td class="p-4"><a href="{{ route('admin.jobs.show', $job) }}" class="text-xs underline">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center opacity-60">No jobs logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
