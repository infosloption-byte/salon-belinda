@extends('layouts.admin')

@section('title', 'Dashboard Accounts')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm opacity-60">Admin accounts get full access; Staff accounts are limited to Jobs &amp; Customers.</p>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add Account</a>
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Name</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Staff Profile</th>
                    <th class="p-4">Added</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">
                            {{ $u->name }}
                            @if ($u->id === auth()->id())
                                <span class="badge ml-2" style="background:#F3ECE3;">You</span>
                            @endif
                        </td>
                        <td class="p-4">{{ $u->email }}</td>
                        <td class="p-4">
                            <span class="badge" style="background: {{ $u->role === 'admin' ? '#D6B076' : '#F3ECE3' }};">{{ ucfirst($u->role) }}</span>
                        </td>
                        <td class="p-4">{{ $u->staff?->name ?? '—' }}</td>
                        <td class="p-4 opacity-60">{{ $u->created_at->format('d M Y') }}</td>
                        <td class="p-4 flex gap-3 justify-end">
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-xs underline">Edit</a>
                            @unless ($u->id === auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Remove this account?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center opacity-60">No accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
