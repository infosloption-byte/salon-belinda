<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt — Job #{{ $job->id }}</title>
    <style>
        @page { margin: 28px 36px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #262220; font-size: 12px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .header-table td { vertical-align: top; }
        .brand { font-size: 22px; color: #2F3E2E; font-weight: bold; }
        .muted { color: #6b6560; }
        .receipt-title { font-size: 16px; text-transform: uppercase; letter-spacing: 1px; color: #7A2E3A; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b6560; border-bottom: 1px solid #262220; padding: 6px 4px; }
        table.items td { padding: 8px 4px; border-bottom: 1px solid #e6e0d8; }
        table.items td.num, table.items th.num { text-align: right; }
        .totals-table { width: 260px; margin-left: auto; margin-top: 12px; border-collapse: collapse; }
        .totals-table td { padding: 4px 0; }
        .totals-table td.label { color: #6b6560; }
        .totals-table td.value { text-align: right; }
        .totals-table tr.grand td { border-top: 1px solid #262220; padding-top: 8px; font-weight: bold; font-size: 14px; }
        .totals-table tr.balance td { color: #7A2E3A; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-paid { background: #F3DEDB; color: #7A2E3A; }
        .badge-pending { background: #F3ECE3; color: #262220; }
        .footer { margin-top: 40px; font-size: 10px; color: #6b6560; text-align: center; }
        .section-label { text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; color: #6b6560; margin-bottom: 4px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                @if ($logo)
                    <img src="{{ $logo }}" style="height: 46px; margin-bottom: 6px;" alt="{{ config('app.name') }}">
                @endif
                <div class="brand">{{ config('app.name') }}</div>
                <div class="muted">{{ config('notifications.salon_address') }}</div>
                <div class="muted">{{ config('notifications.salon_email') }} &middot; {{ config('notifications.salon_phone') }}</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="receipt-title">Receipt</div>
                <div><strong>Job #{{ $job->id }}</strong></div>
                <div class="muted">{{ $job->job_date->format('d M Y') }}</div>
                <div style="margin-top: 6px;">
                    <span class="badge {{ $job->balance_due <= 0 ? 'badge-paid' : 'badge-pending' }}">
                        {{ $job->balance_due <= 0 ? 'Paid in Full' : 'Balance Due' }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="section-label">Customer</div>
                <div><strong>{{ $job->customer->name }}</strong></div>
                <div>{{ $job->customer->phone }}</div>
                @if ($job->customer->email)<div>{{ $job->customer->email }}</div>@endif
            </td>
            <td style="width: 50%;">
                <div class="section-label">Status</div>
                <div>{{ str_replace('_', ' ', ucfirst($job->status)) }}</div>
                @if ($job->notes)<div class="muted">Note: {{ $job->notes }}</div>@endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Treatment</th>
                <th>Staff</th>
                <th class="num">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($job->items as $item)
                <tr>
                    <td>
                        {{ $item->service_name }}
                        @if ($item->discount_type !== 'none')
                            <br><span class="muted" style="font-size: 10px;">
                                Discount: {{ $item->discount_type === 'percent' ? rtrim(rtrim(number_format($item->discount_value, 2), '0'), '.').'%' : 'LKR '.number_format($item->discount_value) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ $item->staff->name }}</td>
                    <td class="num">LKR {{ number_format($item->final_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($job->payments->isNotEmpty())
        <div class="section-label" style="margin-top: 20px;">Payments</div>
        <table class="items">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($job->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($payment->method)) }}</td>
                        <td class="num">LKR {{ number_format($payment->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">LKR {{ number_format($job->subtotal) }}</td>
        </tr>
        <tr>
            <td class="label">Paid</td>
            <td class="value">LKR {{ number_format($job->total_paid) }}</td>
        </tr>
        <tr class="{{ $job->balance_due > 0 ? 'balance' : 'grand' }}">
            <td class="label">{{ $job->balance_due > 0 ? 'Balance Due' : 'Total' }}</td>
            <td class="value">LKR {{ number_format($job->balance_due > 0 ? $job->balance_due : $job->subtotal) }}</td>
        </tr>
    </table>

    <div class="footer">
        Thank you for visiting {{ config('app.name') }}. For questions about this receipt, quote Job #{{ $job->id }}.
    </div>
</body>
</html>
