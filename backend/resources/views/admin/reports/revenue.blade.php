@extends('layouts.admin')

@section('title', 'Revenue Report')

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm underline">&larr; All Reports</a>
    <h1 class="font-display text-2xl mt-3 mb-6">Revenue</h1>

    @include('admin.reports._date-range-form')

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-6">
            <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Total Revenue (Paid)</p>
            <p class="font-display text-3xl" style="color:#7A2E3A;">LKR {{ number_format($totalRevenue) }}</p>
        </div>
        <div class="bg-white p-6">
            <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Paid Orders</p>
            <p class="font-display text-3xl">{{ number_format($totalOrders) }}</p>
        </div>
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Date</th>
                    <th class="p-4">Orders</th>
                    <th class="p-4">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daily as $row)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</td>
                        <td class="p-4">{{ $row->orders_count }}</td>
                        <td class="p-4">LKR {{ number_format($row->total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center opacity-60">No paid orders in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
