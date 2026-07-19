<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        @page { margin: 28px 36px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #262220; font-size: 12px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .header-table td { vertical-align: top; }
        .brand { font-size: 22px; color: #2F3E2E; font-weight: bold; }
        .muted { color: #6b6560; }
        .invoice-title { font-size: 16px; text-transform: uppercase; letter-spacing: 1px; color: #7A2E3A; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b6560; border-bottom: 1px solid #262220; padding: 6px 4px; }
        table.items td { padding: 8px 4px; border-bottom: 1px solid #e6e0d8; }
        table.items td.num, table.items th.num { text-align: right; }
        .totals-table { width: 260px; margin-left: auto; margin-top: 12px; border-collapse: collapse; }
        .totals-table td { padding: 4px 0; }
        .totals-table td.label { color: #6b6560; }
        .totals-table td.value { text-align: right; }
        .totals-table tr.grand td { border-top: 1px solid #262220; padding-top: 8px; font-weight: bold; font-size: 14px; }
        .badge { display: inline-block; padding: 2px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-paid { background: #F3DEDB; color: #7A2E3A; }
        .badge-pending { background: #F3ECE3; color: #262220; }
        .footer { margin-top: 40px; font-size: 10px; color: #6b6560; text-align: center; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="brand">Salon Belinda</div>
                <div class="muted">Galle Road, Ratgama, Sri Lanka</div>
                <div class="muted">{{ config('notifications.salon_email') }} · 070 244 4393</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="invoice-title">Invoice</div>
                <div><strong>{{ $order->order_number }}</strong></div>
                <div class="muted">{{ $order->created_at->format('d M Y, H:i') }}</div>
                <div style="margin-top: 6px;">
                    <span class="badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                        {{ ucfirst($order->payment_method) }} · {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="muted" style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; margin-bottom: 4px;">Billed To</div>
                <div><strong>{{ $order->customer_name }}</strong></div>
                <div>{{ $order->customer_phone }}</div>
                @if ($order->customer_email)<div>{{ $order->customer_email }}</div>@endif
            </td>
            <td style="width: 50%;">
                <div class="muted" style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; margin-bottom: 4px;">Fulfilment</div>
                <div>
                    @if ($order->fulfilment_method === 'pickup')
                        Pickup at Salon Belinda, Ratgama
                    @else
                        Delivery to {{ $order->address }}, {{ $order->city }}
                    @endif
                </div>
                @if ($order->notes)<div class="muted">Note: {{ $order->notes }}</div>@endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">LKR {{ number_format($item->unit_price) }}</td>
                    <td class="num">LKR {{ number_format($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">LKR {{ number_format($order->subtotal) }}</td>
        </tr>
        <tr>
            <td class="label">Delivery</td>
            <td class="value">{{ $order->delivery_fee ? 'LKR ' . number_format($order->delivery_fee) : 'Free' }}</td>
        </tr>
        <tr class="grand">
            <td class="label">Total</td>
            <td class="value">LKR {{ number_format($order->total) }}</td>
        </tr>
    </table>

    <div class="footer">
        Thank you for shopping with Salon Belinda. For questions about this order, quote {{ $order->order_number }}.
    </div>
</body>
</html>
