@extends('layouts.admin')

@section('title', $customer->exists ? 'Edit Customer' : 'Add Customer')

@section('content')
    <form
        method="POST"
        action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}"
        class="bg-white p-6 space-y-5 max-w-lg"
    >
        @csrf
        @if ($customer->exists) @method('PUT') @endif

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Name</label>
            <input name="name" value="{{ old('name', $customer->name) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Phone</label>
            <input name="phone" value="{{ old('phone', $customer->phone) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Email (optional)</label>
            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Notes</label>
            <textarea name="notes" rows="3" placeholder="Allergies, preferences, anything worth remembering..." class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">{{ old('notes', $customer->notes) }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button class="btn btn-primary">{{ $customer->exists ? 'Save Changes' : 'Register Customer' }}</button>
            <a href="{{ $customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
@endsection
