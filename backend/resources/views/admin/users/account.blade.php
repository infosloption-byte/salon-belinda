@extends('layouts.admin')

@section('title', 'My Account')

@section('content')
    <form method="POST" action="{{ route('admin.account.update') }}" class="bg-white p-6 space-y-5 max-w-lg">
        @csrf @method('PUT')

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Name</label>
            <input name="name" value="{{ old('name', $user->name) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <hr style="border-color: rgba(38,34,32,0.1);">

        <p class="text-xs uppercase tracking-wide opacity-60">Change Password (optional)</p>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Current Password</label>
            <input type="password" name="current_password" autocomplete="current-password" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            <p class="text-xs opacity-50 mt-1">Only needed if you're setting a new password below.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">New Password</label>
                <input type="password" name="password" autocomplete="new-password" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Confirm New Password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
        </div>

        <div class="pt-2">
            <button class="btn btn-primary">Save Changes</button>
        </div>
    </form>
@endsection
