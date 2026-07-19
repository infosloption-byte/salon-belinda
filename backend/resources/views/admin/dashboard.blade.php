@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        @php
            $cards = [
                ['label' => 'Pending Appointments', 'value' => $pendingAppointments, 'href' => route('admin.appointments.index', ['status' => 'pending'])],
                ['label' => "Today's Appointments", 'value' => $todayAppointments, 'href' => route('admin.appointments.index')],
                ['label' => 'Reviews Awaiting Approval', 'value' => $pendingTestimonials, 'href' => route('admin.testimonials.index', ['status' => 'pending'])],
                ['label' => 'New Messages', 'value' => $newMessages, 'href' => route('admin.contact-messages.index')],
                ['label' => 'Open Orders', 'value' => $pendingOrders, 'href' => route('admin.orders.index')],
                ['label' => "Today's Revenue (Paid)", 'value' => 'LKR ' . number_format($todayRevenue), 'href' => route('admin.orders.index')],
            ];
        @endphp
        @foreach ($cards as $card)
            <a href="{{ $card['href'] }}" class="bg-white p-5 block hover:shadow-sm transition-shadow">
                <p class="text-xs uppercase tracking-wide mb-2" style="color:#7A2E3A;">{{ $card['label'] }}</p>
                <p class="font-display text-3xl">{{ $card['value'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl">Recent Appointments</h2>
                <a href="{{ route('admin.appointments.index') }}" class="text-xs underline">View all</a>
            </div>
            @forelse ($recentAppointments as $a)
                <div class="flex items-center justify-between py-3 border-t text-sm" style="border-color: rgba(38,34,32,0.08);">
                    <div>
                        <p class="font-medium">{{ $a->name }}</p>
                        <p class="text-xs opacity-60">{{ $a->service_name }} · {{ $a->date->format('d M') }} at {{ $a->time }}</p>
                    </div>
                    <span class="badge" style="background:#F3ECE3;">{{ $a->status }}</span>
                </div>
            @empty
                <p class="text-sm opacity-60 py-3">No appointments yet.</p>
            @endforelse
        </div>

        <div class="bg-white p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs underline">View all</a>
            </div>
            @forelse ($recentOrders as $o)
                <div class="flex items-center justify-between py-3 border-t text-sm" style="border-color: rgba(38,34,32,0.08);">
                    <div>
                        <p class="font-medium">{{ $o->order_number }} — {{ $o->customer_name }}</p>
                        <p class="text-xs opacity-60">LKR {{ number_format($o->total) }} · {{ ucfirst($o->payment_method) }}</p>
                    </div>
                    <span class="badge" style="background: {{ $o->payment_status === 'paid' ? '#F3DEDB' : '#F3ECE3' }}; color: {{ $o->payment_status === 'paid' ? '#7A2E3A' : '#262220' }};">
                        {{ $o->payment_status }}
                    </span>
                </div>
            @empty
                <p class="text-sm opacity-60 py-3">No orders yet.</p>
            @endforelse
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="font-display text-xl">Revenue — Last 14 Days</p>
                <a href="{{ route('admin.reports.revenue') }}" class="text-xs underline">Full report</a>
            </div>
            @php $max = max(1, $revenueTrend->max('total')); @endphp
            <div class="flex items-end gap-1.5" style="height: 120px;">
                @foreach ($revenueTrend as $point)
                    <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ \Carbon\Carbon::parse($point['day'])->format('d M') }}: LKR {{ number_format($point['total']) }}">
                        <div style="width: 100%; background: #7A2E3A; height: {{ max(2, ($point['total'] / $max) * 100) }}%;"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[10px] opacity-50 mt-2">
                <span>{{ \Carbon\Carbon::parse($revenueTrend->first()['day'])->format('d M') }}</span>
                <span>{{ \Carbon\Carbon::parse($revenueTrend->last()['day'])->format('d M') }}</span>
            </div>
        </div>
        <div class="bg-white p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="font-display text-xl">Best Sellers (All Time)</p>
                <a href="{{ route('admin.reports.bestSellers') }}" class="text-xs underline">Full report</a>
            </div>
            <table class="w-full text-sm">
                @forelse ($bestSellers as $row)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="py-2">{{ $row->product_name }}</td>
                        <td class="py-2 text-right">{{ $row->units_sold }} sold</td>
                    </tr>
                @empty
                    <tr><td class="py-2 opacity-60">No sales yet.</td></tr>
                @endforelse
            </table>
        </div>
    </div>
@endsection
