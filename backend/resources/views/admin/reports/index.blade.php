@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <a href="{{ route('admin.reports.revenue') }}" class="bg-white p-6 block hover:shadow-sm transition-shadow">
            <p class="font-display text-xl mb-2">Revenue</p>
            <p class="text-sm opacity-60">Daily paid revenue and order counts over a date range.</p>
        </a>
        <a href="{{ route('admin.reports.bestSellers') }}" class="bg-white p-6 block hover:shadow-sm transition-shadow">
            <p class="font-display text-xl mb-2">Best Sellers</p>
            <p class="text-sm opacity-60">Top products by units sold and revenue.</p>
        </a>
        <a href="{{ route('admin.reports.lowStock') }}" class="bg-white p-6 block hover:shadow-sm transition-shadow">
            <p class="font-display text-xl mb-2">Low Stock</p>
            <p class="text-sm opacity-60">Products at 10 units or fewer — restock candidates.</p>
        </a>
        <a href="{{ route('admin.reports.appointments') }}" class="bg-white p-6 block hover:shadow-sm transition-shadow">
            <p class="font-display text-xl mb-2">Appointments</p>
            <p class="text-sm opacity-60">Bookings broken down by service and status.</p>
        </a>
    </div>
@endsection
