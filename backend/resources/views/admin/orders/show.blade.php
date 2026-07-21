@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
    <div class="flex justify-end gap-3 mb-4">
        <a href="{{ route('admin.orders.invoice.preview', $order) }}" target="_blank" class="btn btn-outline">Preview Invoice</a>
        <a href="{{ route('admin.orders.invoice.download', $order) }}" class="btn btn-primary">Download Invoice</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-6">
            <h2 class="font-display text-xl mb-4">Items</h2>
            <table class="w-full text-sm mb-6">
                <thead>
                    <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                        <th class="py-2">Product</th><th class="py-2">Qty</th><th class="py-2">Unit Price</th><th class="py-2">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                            <td class="py-2">{{ $item->product_name }}</td>
                            <td class="py-2">{{ $item->quantity }}</td>
                            <td class="py-2">LKR {{ number_format($item->unit_price) }}</td>
                            <td class="py-2">LKR {{ number_format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="space-y-1 text-sm max-w-xs ml-auto">
                <div class="flex justify-between"><span class="opacity-60">Subtotal</span><span>LKR {{ number_format($order->subtotal) }}</span></div>
                <div class="flex justify-between"><span class="opacity-60">Delivery</span><span>{{ $order->delivery_fee ? 'LKR ' . number_format($order->delivery_fee) : 'Free' }}</span></div>
                <div class="flex justify-between font-medium pt-2 border-t" style="border-color: rgba(38,34,32,0.1);"><span>Total</span><span>LKR {{ number_format($order->total) }}</span></div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6">
                <h2 class="font-display text-xl mb-4">Customer</h2>
                <p class="text-sm">{{ $order->customer_name }}</p>
                <p class="text-sm opacity-70">{{ $order->customer_phone }}</p>
                @if ($order->customer_email)<p class="text-sm opacity-70">{{ $order->customer_email }}</p>@endif
                <p class="text-sm mt-3">
                    {{ $order->fulfilment_method === 'pickup' ? 'Pickup at ' . config('app.name') . ', ' . config('notifications.salon_address') : "Delivery to {$order->address}, {$order->city}" }}
                </p>
                @if ($order->notes)<p class="text-sm opacity-70 mt-2">Note: {{ $order->notes }}</p>@endif
            </div>

            <div class="bg-white p-6">
                <h2 class="font-display text-xl mb-4">Payment</h2>
                <p class="text-sm">Method: {{ ucfirst($order->payment_method) }}</p>
                <p class="text-sm">Status:
                    <span class="badge" style="background: {{ $order->payment_status === 'paid' ? '#F3DEDB' : '#F3ECE3' }}; color: {{ $order->payment_status === 'paid' ? '#7A2E3A' : '#262220' }};">
                        {{ $order->payment_status }}
                    </span>
                </p>
                @if ($order->transaction_id)<p class="text-xs opacity-50 mt-2">Ref: {{ $order->transaction_id }}</p>@endif

                @if ($order->payment_status !== 'paid')
                    <form method="POST" action="{{ route('admin.orders.markPaid', $order) }}" class="mt-4">
                        @csrf
                        <button class="btn btn-primary w-full">Mark as Paid</button>
                    </form>
                @endif
            </div>

            <div class="bg-white p-6">
                <h2 class="font-display text-xl mb-4">Order Status</h2>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="w-full border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                        @foreach (['pending', 'processing', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.orders.index') }}" class="inline-block mt-6 text-sm underline">← Back to Orders</a>
@endsection
