@extends('layouts.admin')

@section('title', 'Gallery')

@section('content')
    <div class="bg-white p-6 mb-8">
        <h2 class="font-display text-xl mb-4">Add Photo</h2>
        <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Photo</label>
                <label
                    for="gallery-image-input"
                    class="flex flex-col items-center justify-center gap-2 border-2 border-dashed px-4 py-8 text-sm cursor-pointer hover:bg-black/[0.02]"
                    style="border-color: rgba(38,34,32,0.25);"
                >
                    <img id="gallery-image-preview" class="hidden max-h-40 object-contain mb-1" alt="">
                    <span id="gallery-image-label" class="opacity-60">Click to choose a photo from your phone or computer</span>
                    <span class="text-xs opacity-40">JPG, PNG, or WEBP — up to 8MB</span>
                </label>
                <input
                    id="gallery-image-input"
                    type="file"
                    name="image_file"
                    accept="image/*"
                    class="hidden"
                    onchange="
                        const f = this.files[0];
                        const preview = document.getElementById('gallery-image-preview');
                        const label = document.getElementById('gallery-image-label');
                        if (f) {
                            preview.src = URL.createObjectURL(f);
                            preview.classList.remove('hidden');
                            label.textContent = f.name;
                        }
                    "
                >
            </div>

            <div class="sm:col-span-2">
                <details>
                    <summary class="text-xs uppercase tracking-wide opacity-60 cursor-pointer select-none">Or use an image URL instead</summary>
                    <input name="image" type="url" placeholder="https://..." class="w-full border px-3 py-2.5 text-sm mt-2" style="border-color: rgba(38,34,32,0.2);">
                </details>
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
