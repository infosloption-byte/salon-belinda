@extends('emails.layout')

@section('title', 'Your Order')
@section('eyebrow', 'Order Confirmed')

@section('content')
    <p style="margin:0 0 20px; font-size:15px; color:#241A21;">
        Hi {{ $order->customer_name }}, thanks for your order! Here's your receipt.
    </p>

    <p style="margin:0 0 20px; font-size: 13px; color:#241A21; opacity:0.6;">
        Order <strong>{{ $order->order_number }}</strong> &middot;
        {{ $order->fulfilment_method === 'delivery' ? 'Delivery' : 'Pickup at the salon' }} &middot;
        {{ strtoupper($order->payment_method) }}
    </p>

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

    @if ($order->fulfilment_method === 'delivery')
        <p style="margin:0 0 16px; font-size: 14px; color:#241A21;">
            <strong>Delivering to:</strong><br>{{ $order->address }}, {{ $order->city }}
        </p>
    @else
        <p style="margin:0 0 16px; font-size: 14px; color:#241A21;">
            Ready for pickup at {{ $siteAddress ?? '82 Havelock Rd, Galle 80000, Sri Lanka' }}. We'll let you know once it's packed.
        </p>
    @endif

    @if ($order->payment_method !== 'card' && $order->payment_status === 'pending')
        <p style="margin:0; font-size: 14px; color:#241A21; opacity:0.7;">
            @if ($order->payment_method === 'cod')
                Payment due on delivery/pickup.
            @else
                We'll confirm once your bank transfer is received.
            @endif
        </p>
    @endif
@endsection
