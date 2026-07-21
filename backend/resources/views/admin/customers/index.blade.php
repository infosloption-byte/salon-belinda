@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <form method="GET" class="flex gap-2">
            <input name="q" value="{{ $q }}" placeholder="Search by name or phone..." class="border px-3 py-2 text-sm w-64" style="border-color: rgba(38,34,32,0.2);">
            <button class="btn btn-outline">Search</button>
        </form>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">+ Add Customer</a>
    </div>

    @unless ($isAdmin)
        <p class="text-xs opacity-50 mb-3">"Visits" shown below counts only jobs you were involved in.</p>
    @endunless

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Name</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Visits</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4"><a href="{{ route('admin.customers.show', $c) }}" class="underline">{{ $c->name }}</a></td>
                        <td class="p-4">{{ $c->phone }}</td>
                        <td class="p-4">{{ $c->email ?? '—' }}</td>
                        <td class="p-4">{{ $c->jobs_count }}</td>
                        <td class="p-4 flex gap-3 justify-end">
                            <a href="{{ route('admin.customers.edit', $c) }}" class="text-xs underline">Edit</a>
                            @if ($c->jobs_count === 0)
                                <form method="POST" action="{{ route('admin.customers.destroy', $c) }}" onsubmit="return confirm('Delete this customer?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center opacity-60">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $customers->links() }}</div>
@endsection
