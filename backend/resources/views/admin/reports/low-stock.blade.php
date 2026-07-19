@extends('layouts.admin')

@section('title', 'Low Stock')

@section('content')
    <a href="{{ route('admin.reports.index') }}" class="text-sm underline">&larr; All Reports</a>
    <h1 class="font-display text-2xl mt-3 mb-6">Low Stock (10 or fewer)</h1>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Product</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Stock</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">{{ $p->name }}</td>
                        <td class="p-4">{{ $p->category }}</td>
                        <td class="p-4">
                            <span class="badge" style="background: {{ $p->stock_count === 0 ? '#7A2E3A' : '#F3ECE3' }}; color: {{ $p->stock_count === 0 ? '#FBF6F1' : '#262220' }};">
                                {{ $p->stock_count }}
                            </span>
                        </td>
                        <td class="p-4"><a href="{{ route('admin.products.edit', $p) }}" class="text-xs underline">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center opacity-60">Nothing low on stock right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
