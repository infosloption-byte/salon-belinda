@extends('layouts.admin')

@section('title', 'Gallery')

@section('content')
    <div class="bg-white p-6 mb-8">
        <h2 class="font-display text-xl mb-4">Add Photo</h2>
        <form method="POST" action="{{ route('admin.gallery.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Category</label>
                <select name="category" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    @foreach ($categories as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Title</label>
                <input name="title" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <div class="sm:col-span-2">
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Image URL</label>
                <input name="image" type="url" required placeholder="https://..." class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                <p class="text-xs opacity-50 mt-1">Upload the photo to your own storage/CDN first, then paste its URL here.</p>
            </div>
            <div class="sm:col-span-2">
                <button class="btn btn-primary">Add to Gallery</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @forelse ($items as $item)
            <div class="bg-white p-2">
                <div class="aspect-square overflow-hidden mb-2" style="background:#F3ECE3;">
                    <img src="{{ $item->image }}" alt="{{ $item->title }}" class="w-full h-full object-cover" onerror="this.style.opacity=0.2">
                </div>
                <p class="text-xs font-medium truncate">{{ $item->title }}</p>
                <p class="text-xs opacity-50 mb-2">{{ $item->category }}</p>
                <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" onsubmit="return confirm('Remove this photo?')">
                    @csrf @method('DELETE')
                    <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-sm opacity-60 col-span-full py-8 text-center bg-white">No photos yet.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
@endsection
