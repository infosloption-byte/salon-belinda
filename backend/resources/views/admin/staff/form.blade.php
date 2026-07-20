@extends('layouts.admin')

@section('title', $staffMember->exists ? 'Edit Staff' : 'Add Staff')

@section('content')
    <form
        method="POST"
        action="{{ $staffMember->exists ? route('admin.staff.update', $staffMember) : route('admin.staff.store') }}"
        class="bg-white p-6 space-y-5 max-w-lg"
    >
        @csrf
        @if ($staffMember->exists) @method('PUT') @endif

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Name</label>
            <input name="name" value="{{ old('name', $staffMember->name) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Role / Title</label>
            <input name="role_title" value="{{ old('role_title', $staffMember->role_title) }}" placeholder="e.g. Hair Stylist, Makeup Artist" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Phone</label>
                <input name="phone" value="{{ old('phone', $staffMember->phone) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Commission %</label>
                <input type="number" step="0.01" min="0" max="100" name="commission_percent" value="{{ old('commission_percent', $staffMember->commission_percent) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
        </div>
        <p class="text-xs opacity-50">Applied to the final (post-discount) price of each treatment this staff member performs.</p>

        <div class="flex gap-3 pt-2">
            <button class="btn btn-primary">{{ $staffMember->exists ? 'Save Changes' : 'Add Staff Member' }}</button>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
@endsection
