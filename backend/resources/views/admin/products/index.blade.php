@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn {{ !request('category') ? 'btn-primary' : 'btn-outline' }}">All</a>
            @foreach ($categories as $c)
                <a href="{{ route('admin.products.index', ['category' => $c]) }}" class="btn {{ request('category') === $c ? 'btn-primary' : 'btn-outline' }}">{{ $c }}</a>
            @endforeach
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ New Product</a>
    </div>

    <div class="bg-white overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                    <th class="p-4">Product</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Price</th>
                    <th class="p-4">Stock</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                        <td class="p-4">
                            {{ $p->name }}
                            @if ($p->best_seller)<span class="badge ml-2" style="background:#D6B076;">Bestseller</span>@endif
                            @if ($p->is_new)<span class="badge ml-2" style="background:#2F3E2E; color:#fff;">New</span>@endif
                        </td>
                        <td class="p-4">{{ $p->category }}</td>
                        <td class="p-4">LKR {{ number_format($p->price) }}</td>
                        <td class="p-4">
                            <span class="badge" style="background: {{ $p->in_stock ? '#F3ECE3' : '#F3DEDB' }}; color: {{ $p->in_stock ? '#262220' : '#7A2E3A' }};">
                                {{ $p->stock_count }} in stock
                            </span>
                        </td>
                        <td class="p-4 flex gap-3">
                            <a href="{{ route('admin.products.edit', $p) }}" class="text-xs underline">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center opacity-60">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection
