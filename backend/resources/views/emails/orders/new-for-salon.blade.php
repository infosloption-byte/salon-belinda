@extends('emails.layout')

@section('title', 'New Order')
@section('eyebrow', 'New Order')

@section('content')
    <p style="margin:0 0 20px; font-size:15px; color:#241A21;">
        A new order just came in from <strong>{{ $order->customer_name }}</strong>.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3ECE9; margin-bottom: 20px;">
        <tr>
            <td style="padding: 18px 20px; font-size: 14px; color:#241A21;">
                <p style="margin:0 0 8px;"><strong>Order:</strong> {{ $order->order_number }}</p>
                <p style="margin:0 0 8px;"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $order->customer_email ?: '—' }}</p>
                <p style="margin:0 0 8px;"><strong>Fulfilment:</strong> {{ ucfirst($order->fulfilment_method) }}
                    @if ($order->fulfilment_method === 'delivery')
                        — {{ $order->address }}, {{ $order->city }}
                    @endif
                </p>
                <p style="margin:0;"><strong>Payment:</strong> {{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</p>
                @if ($order->notes)
                    <p style="margin:12px 0 0;"><strong>Notes:</strong><br>{{ $order->notes }}</p>
                @endif
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="padding: 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color:#241A21; opacity:0.5; border-bottom: 1px solid rgba(36,26,33,0.15);">Item</td>
            <td style="padding: 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color:#241A21; opacity:0.5; border-bottom: 1px solid rgba(36,26,33,0.15);" align="center">Qty</td>
            <td style="padding: 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color:#241A21; opacity:0.5; border-bottom: 1px solid rgba(36,26,33,0.15);" align="right">Total</td>
        </tr>
        @foreach ($order->items as $item)
            <tr>
                <td style="padding: 10px 0; font-size: 14px; color:#241A21; border-bottom: 1px solid rgba(36,26,33,0.08);">{{ $item->product_name }}</td>
                <td style="padding: 10px 0; font-size: 14px; color:#241A21; border-bottom: 1px solid rgba(36,26,33,0.08);" align="center">{{ $item->quantity }}</td>
                <td style="padding: 10px 0; font-size: 14px; color:#241A21; border-bottom: 1px solid rgba(36,26,33,0.08);" align="right">LKR {{ number_format($item->line_total) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="padding-top: 12px; font-size: 14px; color:#241A21;" align="right">Subtotal</td>
            <td style="padding-top: 12px; font-size: 14px; color:#241A21;" align="right">LKR {{ number_format($order->subtotal) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 4px; font-size: 14px; color:#241A21;" align="right">Delivery</td>
            <td style="padding-top: 4px; font-size: 14px; color:#241A21;" align="right">{{ $order->delivery_fee > 0 ? 'LKR '.number_format($order->delivery_fee) : 'Free' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 8px; font-size: 15px; color:#241A21; font-weight:bold;" align="right">Total</td>
            <td style="padding-top: 8px; font-size: 15px; color:#C23056; font-weight:bold;" align="right">LKR {{ number_format($order->total) }}</td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background-color:#C23056; border-radius: 2px;">
                <a href="{{ route('admin.orders.show', $order) }}" style="display:inline-block; padding: 12px 24px; font-size: 13px; letter-spacing: 1px; text-transform: uppercase; color:#FBF7F3; text-decoration:none;">
                    View Order
                </a>
            </td>
        </tr>
    </table>
@endsection
