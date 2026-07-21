@extends('layouts.admin')

@section('title', 'New Job')

@section('content')
    @if ($appointment)
        <div class="mb-6 p-4 text-sm" style="background:#F3ECE3;">
            Starting a job from the appointment for <strong>{{ $appointment->name }}</strong>
            ({{ $appointment->date->format('d M Y') }} at {{ $appointment->time }}) — find or register them below.
        </div>
    @endif

    @if (! $selectedCustomer)
        {{-- Step 1: find the customer --}}
        <div class="bg-white p-6 mb-8 max-w-xl">
            <h2 class="font-display text-xl mb-4">Find Customer</h2>
            <form method="GET" class="flex gap-2 mb-2">
                @if ($appointment)<input type="hidden" name="appointment_id" value="{{ $appointment->id }}">@endif
                <input name="q" value="{{ $q }}" placeholder="Search by name or phone..." autofocus class="flex-1 border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                <button class="btn btn-primary">Search</button>
            </form>
        </div>

        @if ($q)
            <div class="bg-white mb-8 max-w-xl">
                @forelse ($customers as $c)
                    <a href="{{ route('admin.jobs.create', array_filter(['customer_id' => $c->id, 'appointment_id' => $appointment?->id])) }}"
                       class="flex items-center justify-between px-6 py-4 border-b hover:bg-[#F3ECE3]" style="border-color: rgba(38,34,32,0.06);">
                        <span>
                            <span class="block font-medium">{{ $c->name }}</span>
                            <span class="block text-xs opacity-50">{{ $c->phone }} @if($c->email) &middot; {{ $c->email }} @endif</span>
                        </span>
                        <span class="text-xs underline">Select</span>
                    </a>
                @empty
                    <p class="p-6 text-sm opacity-60">No match for "{{ $q }}" — register them as a new customer below.</p>
                @endforelse
            </div>

            <div class="bg-white p-6 max-w-xl">
                <h2 class="font-display text-xl mb-4">Register New Customer</h2>
                <form method="POST" action="{{ route('admin.jobs.quickRegisterCustomer', array_filter(['appointment_id' => $appointment?->id])) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Name</label>
                        <input name="name" value="{{ old('name', $appointment->name ?? '') }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Phone</label>
                        <input name="phone" value="{{ old('phone', $appointment->phone ?? $q) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Email (optional)</label>
                        <input type="email" name="email" value="{{ old('email', $appointment->email ?? '') }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    </div>
                    <button class="btn btn-primary">Register &amp; Continue</button>
                </form>
            </div>
        @endif
    @else
        {{-- Step 2: confirm job details --}}
        <div class="bg-white p-6 max-w-xl">
            <h2 class="font-display text-xl mb-1">{{ $selectedCustomer->name }}</h2>
            <p class="text-sm opacity-60 mb-6">{{ $selectedCustomer->phone }} &middot; {{ $selectedCustomerVisitCount }} previous visit(s){{ auth()->user()->isAdminRole() ? '' : ' with you' }}</p>

            <form method="POST" action="{{ route('admin.jobs.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                @if ($appointment)<input type="hidden" name="appointment_id" value="{{ $appointment->id }}">@endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Job Date</label>
                        <input type="date" name="job_date" value="{{ old('job_date', now()->toDateString()) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Status</label>
                        <select name="status" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                            <option value="in_progress">In Progress</option>
                            <option value="scheduled">Scheduled (e.g. booked ahead with a deposit)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button class="btn btn-primary">Start Job</button>
                    <a href="{{ route('admin.jobs.create') }}" class="btn btn-outline">Change Customer</a>
                </div>
            </form>
        </div>
    @endif
@endsection
