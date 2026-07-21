@extends('layouts.admin')

@section('title', 'Revenue Report')

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm underline">&larr; All Reports</a>
    <h1 class="font-display text-2xl mt-3 mb-6">Revenue</h1>

    @include('admin.reports._date-range-form')

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6">
            <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Total Revenue</p>
            <p class="font-display text-3xl" style="color:#7A2E3A;">LKR {{ number_format($totalRevenue) }}</p>
        </div>
        <div class="bg-white p-6">
            <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Shop ({{ $totalOrders }} orders)</p>
            <p class="font-display text-3xl">LKR {{ number_format($totalShopRevenue) }}</p>
        </div>
        <div class="bg-white p-6">
            <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Salon (Jobs)</p>
            <p class="font-display text-3xl">LKR {{ number_format($totalSalonRevenue) }}</p>
        </div>
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Date</th>
                    <th class="p-4">Shop Orders</th>
                    <th class="p-4">Shop Revenue</th>
                    <th class="p-4">Salon Payments</th>
                    <th class="p-4">Salon Revenue</th>
                    <th class="p-4">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($combined as $row)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ \Carbon\Carbon::parse($row['day'])->format('d M Y') }}</td>
                        <td class="p-4">{{ $row['orders_count'] }}</td>
                        <td class="p-4">LKR {{ number_format($row['shop']) }}</td>
                        <td class="p-4">{{ $row['payments_count'] }}</td>
                        <td class="p-4">LKR {{ number_format($row['salon']) }}</td>
                        <td class="p-4 font-medium">LKR {{ number_format($row['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center opacity-60">No revenue in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
