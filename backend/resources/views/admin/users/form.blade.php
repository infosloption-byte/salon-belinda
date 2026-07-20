@extends('layouts.admin')

@section('title', $editUser->exists ? 'Edit Account' : 'Add Account')

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

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Account Type</label>
            <select name="role" id="role-select" onchange="document.getElementById('staff-link-panel').style.display = this.value === 'staff' ? 'block' : 'none';" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                <option value="admin" @selected(old('role', $editUser->role ?? 'admin') === 'admin')>Admin — full dashboard access</option>
                <option value="staff" @selected(old('role', $editUser->role ?? 'admin') === 'staff')>Staff — Jobs &amp; Customers only</option>
            </select>
        </div>

        <div id="staff-link-panel" style="display: {{ old('role', $editUser->role ?? 'admin') === 'staff' ? 'block' : 'none' }}; background:#F3ECE3;" class="p-4 space-y-4">
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Link to Existing Staff Profile</label>
                <select name="staff_id" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <option value="">— None selected —</option>
                    @foreach ($unlinkedStaff as $s)
                        <option value="{{ $s->id }}" @selected(old('staff_id', $editUser->staff_id ?? null) == $s->id)>{{ $s->name }} @if($s->role_title) ({{ $s->role_title }}) @endif</option>
                    @endforeach
                </select>
                <p class="text-xs opacity-50 mt-1">Or leave unselected and fill in the fields below to create a new staff profile for this login.</p>
            </div>

            <p class="text-xs uppercase tracking-wide opacity-60 pt-2 border-t" style="border-color: rgba(38,34,32,0.15);">— or create a new staff profile —</p>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Staff Name</label>
                <input name="new_staff_name" value="{{ old('new_staff_name') }}" placeholder="Defaults to the account name above if left blank" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Role / Title</label>
                    <input name="new_staff_role_title" value="{{ old('new_staff_role_title') }}" placeholder="e.g. Hair Stylist" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Commission %</label>
                    <input type="number" step="0.01" min="0" max="100" name="new_staff_commission_percent" value="{{ old('new_staff_commission_percent') }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Phone</label>
                <input name="new_staff_phone" value="{{ old('new_staff_phone') }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>

            @if ($editUser->exists && $editUser->staff_id)
                <p class="text-xs" style="color:#7A2E3A;">Currently linked to a staff profile. Picking a different one above will move this login to that profile instead.</p>
            @endif
        </div>

        <div class="flex gap-3 pt-2">
            <button class="btn btn-primary">{{ $editUser->exists ? 'Save Changes' : 'Create Account' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
@endsection
