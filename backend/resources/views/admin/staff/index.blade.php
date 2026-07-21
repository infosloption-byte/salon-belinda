@extends('layouts.admin')

@section('title', 'Staff')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input name="q" value="{{ request('q') }}" placeholder="Search by name..." class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
            <button class="btn btn-outline">Search</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('admin.staff.index', ['status' => 'active']) }}" class="btn {{ $status === 'active' ? 'btn-primary' : 'btn-outline' }}">Active</a>
            <a href="{{ route('admin.staff.index', ['status' => 'inactive']) }}" class="btn {{ $status === 'inactive' ? 'btn-primary' : 'btn-outline' }}">Inactive</a>
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">+ Add Staff</a>
        </div>
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Name</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Commission</th>
                    <th class="p-4">Jobs Performed</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staffMembers as $s)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ $s->name }}</td>
                        <td class="p-4">{{ $s->role_title ?? '—' }}</td>
                        <td class="p-4">{{ $s->phone ?? '—' }}</td>
                        <td class="p-4">{{ rtrim(rtrim(number_format($s->commission_percent, 2), '0'), '.') }}%</td>
                        <td class="p-4">{{ $s->job_items_count }}</td>
                        <td class="p-4 flex gap-3 justify-end">
                            <a href="{{ route('admin.staff.edit', $s) }}" class="text-xs underline">Edit</a>
                            <form method="POST" action="{{ route('admin.staff.toggleActive', $s) }}">
                                @csrf
                                <button class="text-xs underline">{{ $s->is_active ? 'Deactivate' : 'Reactivate' }}</button>
                            </form>
                            @if ($s->job_items_count === 0 && $s->user_count === 0)
                                <form method="POST" action="{{ route('admin.staff.destroy', $s) }}" onsubmit="return confirm('Delete this staff member?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center opacity-60">No staff found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $staffMembers->links() }}</div>
@endsection
