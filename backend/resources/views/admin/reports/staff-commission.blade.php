@extends('layouts.admin')

@section('title', $isAdmin ? 'Staff Commission' : 'My Commission')

@section('content')
    @if ($isAdmin)
        <a href="{{ route('admin.reports.index') }}" class="text-sm underline">&larr; All Reports</a>
    @endif
    <h1 class="font-display text-2xl mt-3 mb-6">{{ $isAdmin ? 'Staff Performance & Commission' : 'My Commission' }}</h1>

    <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 bg-white p-4">
        @if ($isAdmin && $staffId)
            <input type="hidden" name="staff_id" value="{{ $staffId }}">
        @endif
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">From</label>
            <input type="date" name="date_from" value="{{ $from }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">To</label>
            <input type="date" name="date_to" value="{{ $to }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <button class="btn btn-primary">Update</button>
    </form>

    @if ($isAdmin)
        <div class="bg-white overflow-x-auto mb-8">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                        <th class="p-4">Staff</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Services</th>
                        <th class="p-4">Revenue</th>
                        <th class="p-4">Commission</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $row)
                        <tr class="border-b" style="border-color: rgba(38,34,32,0.06); {{ (string) $staffId === (string) $row->staff_id ? 'background: #F3ECE3;' : '' }}">
                            <td class="p-4">{{ $row->name }}</td>
                            <td class="p-4 opacity-70">{{ $row->role_title }}</td>
                            <td class="p-4">{{ $row->services_count }}</td>
                            <td class="p-4">LKR {{ number_format($row->revenue) }}</td>
                            <td class="p-4 font-medium" style="color:#7A2E3A;">LKR {{ number_format($row->commission) }}</td>
                            <td class="p-4">
                                <a href="{{ route('admin.reports.staffCommission', ['staff_id' => $row->staff_id, 'date_from' => $from, 'date_to' => $to]) }}" class="text-xs underline">View Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center opacity-60">No services performed in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($staffId)
            <a href="{{ route('admin.reports.staffCommission', ['date_from' => $from, 'date_to' => $to]) }}" class="text-sm underline">&larr; Back to all staff</a>
        @endif
    @else
        @php($row = $summary->first())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6">
                <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Services Performed</p>
                <p class="font-display text-3xl">{{ $row->services_count ?? 0 }}</p>
            </div>
            <div class="bg-white p-6">
                <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Revenue Generated</p>
                <p class="font-display text-3xl">LKR {{ number_format($row->revenue ?? 0) }}</p>
            </div>
            <div class="bg-white p-6">
                <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Commission Earned</p>
                <p class="font-display text-3xl" style="color:#7A2E3A;">LKR {{ number_format($row->commission ?? 0) }}</p>
            </div>
        </div>
    @endif

    @if ($staffId)
        <h2 class="font-display text-xl mb-4">{{ $isAdmin ? 'Service Breakdown' : 'My Services' }}</h2>
        <div class="bg-white overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                        <th class="p-4">Job Date</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Treatment</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Commission</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detail as $item)
                        <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                            <td class="p-4">{{ $item->job->job_date->format('d M Y') }}</td>
                            <td class="p-4">{{ $item->job->customer->name }}</td>
                            <td class="p-4">{{ $item->service_name }}</td>
                            <td class="p-4">LKR {{ number_format($item->final_price) }}</td>
                            <td class="p-4">LKR {{ number_format($item->commission_amount) }}</td>
                            <td class="p-4"><a href="{{ route('admin.jobs.show', $item->job_id) }}" class="text-xs underline">Open Job</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center opacity-60">No services in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
