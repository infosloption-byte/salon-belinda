@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach (['' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $value])) }}"
               class="btn {{ request('status', '') === $value ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Order</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Payment</th>
                    <th class="p-4">Status</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $o)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">
                            <a href="{{ route('admin.orders.show', $o) }}" class="underline">{{ $o->order_number }}</a>
                            <p class="text-xs opacity-50">{{ $o->created_at->format('d M Y, H:i') }}</p>
                        </td>
                        <td class="p-4">{{ $o->customer_name }}</td>
                        <td class="p-4">LKR {{ number_format($o->total) }}</td>
                        <td class="p-4">
                            <span class="badge" style="background: {{ $o->payment_status === 'paid' ? '#F3DEDB' : '#F3ECE3' }}; color: {{ $o->payment_status === 'paid' ? '#7A2E3A' : '#262220' }};">
                                {{ ucfirst($o->payment_method) }} · {{ $o->payment_status }}
                            </span>
                        </td>
                        <td class="p-4">
                            <form method="POST" action="{{ route('admin.orders.status', $o) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="border px-2 py-1 text-xs" style="border-color: rgba(38,34,32,0.2);">
                                    @foreach (['pending', 'processing', 'completed', 'cancelled'] as $status)
                                        <option value="{{ $status }}" @selected($o->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="p-4">
                            @if ($o->payment_status !== 'paid')
                                <form method="POST" action="{{ route('admin.orders.markPaid', $o) }}">
                                    @csrf
                                    <button class="text-xs underline">Mark Paid</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center opacity-60">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
