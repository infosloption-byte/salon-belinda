@extends('layouts.admin')

@section('title', 'Job — ' . $job->customer->name)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('admin.customers.show', $job->customer) }}" class="text-sm underline opacity-70">{{ $job->customer->name }}</a>
            <h2 class="font-display text-2xl mt-1">Job #{{ $job->id }} &middot; {{ $job->job_date->format('d M Y') }}</h2>
            @if ($job->notes)<p class="text-sm opacity-60 mt-1">{{ $job->notes }}</p>@endif
        </div>
        <form method="POST" action="{{ route('admin.jobs.status', $job) }}" class="shrink-0">
            @csrf @method('PATCH')
            <select name="status" onchange="this.form.submit()" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                @foreach (['scheduled', 'in_progress', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected($job->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-5">
            <p class="text-xs uppercase tracking-wide mb-2 opacity-60">Subtotal</p>
            <p class="font-display text-2xl">LKR {{ number_format($job->subtotal) }}</p>
        </div>
        <div class="bg-white p-5">
            <p class="text-xs uppercase tracking-wide mb-2 opacity-60">Paid</p>
            <p class="font-display text-2xl">LKR {{ number_format($job->total_paid) }}</p>
        </div>
        <div class="bg-white p-5">
            <p class="text-xs uppercase tracking-wide mb-2" style="color:#7A2E3A;">Balance Due</p>
            <p class="font-display text-2xl" style="color:#7A2E3A;">LKR {{ number_format($job->balance_due) }}</p>
        </div>
    </div>

    @if ($job->balance_due <= 0 && $job->status !== 'completed' && $job->status !== 'cancelled')
        <div class="mb-8 p-4 text-sm" style="background:#F3ECE3;">
            Balance is fully paid — you may want to mark this job <strong>Completed</strong> above (not automatic, in case there's more to add).
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        {{-- Treatments --}}
        <div>
            <h3 class="font-display text-xl mb-4">Treatments</h3>
            <div class="bg-white overflow-x-auto mb-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                            <th class="p-3">Treatment</th>
                            <th class="p-3">Staff</th>
                            <th class="p-3">Price</th>
                            <th class="p-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($job->items as $item)
                            <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                                <td class="p-3">
                                    {{ $item->service_name }}
                                    @if ($item->discount_type !== 'none')
                                        <span class="block text-xs opacity-50">
                                            LKR {{ number_format($item->base_price) }} − {{ $item->discount_type === 'percent' ? rtrim(rtrim(number_format($item->discount_value, 2), '0'), '.').'%' : 'LKR '.number_format($item->discount_value) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3">{{ $item->staff->name }}</td>
                                <td class="p-3">
                                    LKR {{ number_format($item->final_price) }}
                                    <span class="block text-xs opacity-50">Comm. LKR {{ number_format($item->commission_amount) }}</span>
                                </td>
                                <td class="p-3">
                                    <form method="POST" action="{{ route('admin.jobs.items.destroy', [$job, $item]) }}" onsubmit="return confirm('Remove this treatment?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs" style="color:#7A2E3A;">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center opacity-60">No treatments added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-5">
                <p class="text-xs uppercase tracking-wide mb-3 opacity-60">Add Treatment</p>
                <form method="POST" action="{{ route('admin.jobs.items.store', $job) }}" class="space-y-3">
                    @csrf
                    <select name="service_id" id="service-select" onchange="toggleCustomFields()" class="w-full border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                        <option value="">— Custom Treatment (type below) —</option>
                        @foreach ($serviceCategories as $cat)
                            <optgroup label="{{ $cat->title }}">
                                @foreach ($cat->services as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} — LKR {{ number_format($s->price) }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    <div id="custom-fields" class="grid grid-cols-2 gap-3">
                        <input name="custom_name" placeholder="Treatment name" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                        <input type="number" name="custom_price" placeholder="Price (LKR)" min="0" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    </div>

                    <select name="staff_id" required class="w-full border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                        <option value="">Assign to staff...</option>
                        @foreach ($activeStaff as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }} @if($staff->role_title) ({{ $staff->role_title }}) @endif</option>
                        @endforeach
                    </select>

                    <div class="grid grid-cols-2 gap-3">
                        <select name="discount_type" id="discount-type" onchange="toggleDiscountValue()" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                            <option value="none">No discount</option>
                            <option value="percent">Discount %</option>
                            <option value="fixed">Discount LKR</option>
                        </select>
                        <input type="number" step="0.01" min="0" name="discount_value" id="discount-value" placeholder="Value" disabled class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    </div>

                    <button class="btn btn-primary w-full">Add Treatment</button>
                </form>
            </div>
        </div>

        {{-- Payments --}}
        <div>
            <h3 class="font-display text-xl mb-4">Payments</h3>
            <div class="bg-white overflow-x-auto mb-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                            <th class="p-3">Date</th>
                            <th class="p-3">Method</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($job->payments as $payment)
                            <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                                <td class="p-3">
                                    {{ $payment->paid_at->format('d M Y, h:i A') }}
                                    @if ($payment->note)<span class="block text-xs opacity-50">{{ $payment->note }}</span>@endif
                                </td>
                                <td class="p-3">{{ str_replace('_', ' ', ucfirst($payment->method)) }}</td>
                                <td class="p-3">LKR {{ number_format($payment->amount) }}</td>
                                <td class="p-3">
                                    <form method="POST" action="{{ route('admin.jobs.payments.destroy', [$job, $payment]) }}" onsubmit="return confirm('Remove this payment?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs" style="color:#7A2E3A;">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center opacity-60">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-5">
                <p class="text-xs uppercase tracking-wide mb-3 opacity-60">Record Payment</p>
                <form method="POST" action="{{ route('admin.jobs.payments.store', $job) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" name="amount" placeholder="Amount (LKR)" min="1" required class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                        <select name="method" required class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <input name="note" placeholder="Note (optional) — e.g. Wedding deposit" class="w-full border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <button class="btn btn-primary w-full">Record Payment</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomFields() {
            var select = document.getElementById('service-select');
            document.getElementById('custom-fields').style.display = select.value === '' ? 'grid' : 'none';
        }
        function toggleDiscountValue() {
            var select = document.getElementById('discount-type');
            var input = document.getElementById('discount-value');
            input.disabled = select.value === 'none';
            if (input.disabled) input.value = '';
        }
        toggleCustomFields();
    </script>
@endsection
