@extends('layouts.admin')

@section('title', 'Jobs')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'All', 'scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                <a href="{{ route('admin.jobs.index', array_filter(['status' => $value, 'q' => request('q'), 'from' => request('from'), 'to' => request('to')])) }}"
                   class="btn {{ request('status', '') === $value ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
            @endforeach
        </div>
        <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary">+ New Job</a>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 bg-white p-4">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Search Customer</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name or phone" class="border px-3 py-2 text-sm w-56" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <button class="btn btn-primary">Filter</button>
        @if (request('q') || request('from') || request('to'))
            <a href="{{ route('admin.jobs.index', array_filter(['status' => request('status')])) }}" class="btn btn-outline">Clear</a>
        @endif
    </form>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Customer</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Subtotal</th>
                    <th class="p-4">Paid</th>
                    <th class="p-4">Balance</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ $job->customer->name }}<br><span class="text-xs opacity-50">{{ $job->customer->phone }}</span></td>
                        <td class="p-4">{{ $job->job_date->format('d M Y') }}</td>
                        <td class="p-4"><span class="badge" style="background:#F3ECE3;">{{ str_replace('_', ' ', $job->status) }}</span></td>
                        <td class="p-4">LKR {{ number_format($job->subtotal) }}</td>
                        <td class="p-4">LKR {{ number_format($job->total_paid) }}</td>
                        <td class="p-4" style="{{ $job->balance_due > 0 ? 'color:#7A2E3A; font-weight:600;' : '' }}">LKR {{ number_format($job->balance_due) }}</td>
                        <td class="p-4 flex gap-3 justify-end">
                            <a href="{{ route('admin.jobs.show', $job) }}" class="text-xs underline">Open</a>
                            <a href="{{ route('admin.jobs.receipt.preview', $job) }}" target="_blank" class="text-xs underline">Receipt</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center opacity-60">No jobs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $jobs->links() }}</div>
@endsection
