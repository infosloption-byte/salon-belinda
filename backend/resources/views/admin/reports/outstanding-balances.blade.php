@extends('layouts.admin')

@section('title', 'Outstanding Balances')

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm underline">&larr; All Reports</a>
    <h1 class="font-display text-2xl mt-3 mb-6">Outstanding Balances</h1>

    <div class="bg-white p-6 mb-6 max-w-xs">
        <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Total Outstanding</p>
        <p class="font-display text-3xl" style="color:#7A2E3A;">LKR {{ number_format($totalOutstanding) }}</p>
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Job Date</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Paid</th>
                    <th class="p-4">Balance Due</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ $job->job_date->format('d M Y') }}</td>
                        <td class="p-4">{{ $job->customer->name }} · {{ $job->customer->phone }}</td>
                        <td class="p-4"><span class="badge" style="background:#F3ECE3;">{{ str_replace('_', ' ', $job->status) }}</span></td>
                        <td class="p-4">LKR {{ number_format($job->subtotal) }}</td>
                        <td class="p-4">LKR {{ number_format($job->total_paid) }}</td>
                        <td class="p-4 font-medium" style="color:#7A2E3A;">LKR {{ number_format($job->balance_due) }}</td>
                        <td class="p-4"><a href="{{ route('admin.jobs.show', $job) }}" class="text-xs underline">Open Job</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center opacity-60">Nothing outstanding — all jobs are settled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
