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
@endsection
