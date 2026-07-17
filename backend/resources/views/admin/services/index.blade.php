@extends('layouts.admin')

@section('title', 'Services')

@section('content')
    <div class="bg-white p-6 mb-8">
        <h2 class="font-display text-xl mb-4">Add Category</h2>
        <form method="POST" action="{{ route('admin.services.categories.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf
            <input name="title" placeholder="e.g. Bridal Packages" required class="border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            <input name="intro" placeholder="Short description shown on the Services page" class="border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            <div class="sm:col-span-2"><button class="btn btn-primary">Add Category</button></div>
        </form>
    </div>

    @foreach ($categories as $cat)
        <div class="bg-white p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-xl">{{ $cat->title }}</h2>
                <form method="POST" action="{{ route('admin.services.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category and all its services?')">
                    @csrf @method('DELETE')
                    <button class="text-xs" style="color:#7A2E3A;">Delete Category</button>
                </form>
            </div>

            <table class="w-full text-sm mb-5">
                <thead>
                    <tr class="text-left border-b" style="border-color: rgba(38,34,32,0.1);">
                        <th class="py-2">Name</th><th class="py-2">Duration</th><th class="py-2">Price (LKR)</th><th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cat->services as $s)
                        <tr class="border-b" style="border-color: rgba(38,34,32,0.06);">
                            <td class="py-2">{{ $s->name }}</td>
                            <td class="py-2">{{ $s->duration }}</td>
                            <td class="py-2">{{ $s->price_prefix }} {{ number_format($s->price) }}</td>
                            <td class="py-2">
                                <form method="POST" action="{{ route('admin.services.destroy', $s) }}" onsubmit="return confirm('Delete this service?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 opacity-50">No services in this category yet.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <details>
                <summary class="text-xs uppercase tracking-wide cursor-pointer" style="color:#B98A4A;">+ Add Service to {{ $cat->title }}</summary>
                <form method="POST" action="{{ route('admin.services.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                    @csrf
                    <input type="hidden" name="service_category_id" value="{{ $cat->id }}">
                    <input name="name" placeholder="Service name" required class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <input name="duration" placeholder="e.g. 1 hr" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <input name="price" type="number" placeholder="Price (LKR)" required class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <input name="price_prefix" placeholder="Prefix (optional, e.g. 'From')" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <input name="description" placeholder="Short description" class="sm:col-span-2 border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <div class="sm:col-span-2"><button class="btn btn-primary">Add Service</button></div>
                </form>
            </details>
        </div>
    @endforeach
@endsection
