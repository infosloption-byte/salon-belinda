@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach (['' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $value, 'q' => request('q'), 'date_from' => request('date_from'), 'date_to' => request('date_to')])) }}"
               class="btn {{ request('status', '') === $value ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
        @endforeach
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 bg-white p-4">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Order #, name, phone, or email"
                   class="border px-3 py-2 text-sm w-60" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <button class="btn btn-primary">Filter</button>
        @if (request('q') || request('date_from') || request('date_to'))
            <a href="{{ route('admin.orders.index', array_filter(['status' => request('status')])) }}" class="btn btn-outline">Clear</a>
        @endif
    </form>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Order</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Payment</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Invoice</th>
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
                        <td class="p-4 whitespace-nowrap">
                            <a href="{{ route('admin.orders.invoice.preview', $o) }}" target="_blank" class="text-xs underline">Preview</a>
                            <span class="opacity-30">·</span>
                            <a href="{{ route('admin.orders.invoice.download', $o) }}" class="text-xs underline">Download</a>
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
                    <tr><td colspan="7" class="p-8 text-center opacity-60">No orders match your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
