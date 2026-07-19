@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
    <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 bg-white p-4">
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Admin</label>
            <select name="user_id" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                <option value="">All</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Action</label>
            <select name="action" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                <option value="">All</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>
        <button class="btn btn-primary">Filter</button>
        @if (request('user_id') || request('action') || request('date_from') || request('date_to'))
            <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline">Clear</a>
        @endif
    </form>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">When</th>
                    <th class="p-4">Admin</th>
                    <th class="p-4">Action</th>
                    <th class="p-4">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4 whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        <td class="p-4">{{ $log->user?->name ?? 'Unknown' }}</td>
                        <td class="p-4"><span class="badge" style="background:#F3ECE3;">{{ $log->action }}</span></td>
                        <td class="p-4">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center opacity-60">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $logs->links() }}</div>
@endsection
