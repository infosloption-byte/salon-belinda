@extends('layouts.admin')

@section('title', 'Admin Users')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm opacity-60">Everyone listed here can log into this dashboard.</p>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add Admin</a>
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Name</th>
                    <th class="p-4">Email</th>
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
                        <td class="p-4 opacity-60">{{ $u->created_at->format('d M Y') }}</td>
                        <td class="p-4 flex gap-3 justify-end">
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-xs underline">Edit</a>
                            @unless ($u->id === auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Remove this admin account?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center opacity-60">No admin accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
