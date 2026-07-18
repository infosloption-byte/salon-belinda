@extends('layouts.admin')

@section('title', $editUser->exists ? 'Edit Admin' : 'Add Admin')

@section('content')
    <form
        method="POST"
        action="{{ $editUser->exists ? route('admin.users.update', $editUser) : route('admin.users.store') }}"
        class="bg-white p-6 space-y-5 max-w-lg"
    >
        @csrf
        @if ($editUser->exists) @method('PUT') @endif

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Name</label>
            <input name="name" value="{{ old('name', $editUser->name) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Email</label>
            <input type="email" name="email" value="{{ old('email', $editUser->email) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">
                    {{ $editUser->exists ? 'New Password (leave blank to keep current)' : 'Password' }}
                </label>
                <input type="password" name="password" autocomplete="new-password" {{ $editUser->exists ? '' : 'required' }} class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Confirm Password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
        </div>
        <p class="text-xs opacity-50">At least 8 characters.</p>

        <div class="flex gap-3 pt-2">
            <button class="btn btn-primary">{{ $editUser->exists ? 'Save Changes' : 'Create Admin Account' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
@endsection
