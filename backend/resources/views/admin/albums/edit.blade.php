@extends('layouts.admin')

@section('title', 'Edit Album')

@section('content')
    <div class="bg-white p-6 max-w-3xl mb-8">
        <form method="POST" action="{{ route('admin.albums.update', $album) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Album Title *</label>
                    <input name="title" required value="{{ old('title', $album->title) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Couple Names</label>
                    <input name="couple_names" value="{{ old('couple_names', $album->couple_names) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Event Date</label>
                    <input type="date" name="event_date" value="{{ old('event_date', $album->event_date?->format('Y-m-d')) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Location</label>
                    <input name="location" value="{{ old('location', $album->location) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Category / Tag</label>
                    <input name="category" value="{{ old('category', $album->category) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Description</label>
                <textarea name="description" rows="3" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">{{ old('description', $album->description) }}</textarea>
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Cover Photo</label>
                <div class="flex items-center gap-4 mb-2">
                    @if ($album->cover_image)
                        <img src="{{ $album->cover_image }}" alt="Current cover" class="w-20 h-20 object-cover" style="background:#F3ECE3;" onerror="this.style.opacity=0.2">
                    @endif
                    <label
                        for="cover-image-input"
                        class="flex-1 flex flex-col items-center justify-center gap-1 border-2 border-dashed px-4 py-4 text-sm cursor-pointer hover:bg-black/[0.02]"
                        style="border-color: rgba(38,34,32,0.25);"
                    >
                        <span id="cover-image-label" class="opacity-60">Click to replace cover photo</span>
                    </label>
                </div>
                <input
                    id="cover-image-input" type="file" name="cover_image_file" accept="image/*" class="hidden"
                    onchange="document.getElementById('cover-image-label').textContent = this.files[0] ? this.files[0].name : 'Click to replace cover photo';"
                >
                <details>
                    <summary class="text-xs uppercase tracking-wide opacity-60 cursor-pointer select-none">Or use an image URL instead</summary>
                    <input name="cover_image" type="url" value="{{ old('cover_image', $album->cover_image) }}" class="w-full border px-3 py-2.5 text-sm mt-2" style="border-color: rgba(38,34,32,0.2);">
                </details>
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Add More Photos</label>
                <label
                    for="photo-files-input"
                    class="flex flex-col items-center justify-center gap-2 border-2 border-dashed px-4 py-6 text-sm cursor-pointer hover:bg-black/[0.02]"
                    style="border-color: rgba(38,34,32,0.25);"
                >
                    <span id="photo-files-label" class="opacity-60">Click to choose photos from your phone or computer — select multiple at once</span>
                </label>
                <input
                    id="photo-files-input" type="file" name="photo_files[]" accept="image/*" multiple class="hidden"
                    onchange="document.getElementById('photo-files-label').textContent = this.files.length ? this.files.length + ' photo(s) selected' : 'Click to choose photos from your phone or computer — select multiple at once';"
                >
                <details class="mt-3">
                    <summary class="text-xs uppercase tracking-wide opacity-60 cursor-pointer select-none">Or add photo URLs instead</summary>
                    <textarea name="photo_urls" rows="4" placeholder="One photo URL per line, optional caption after a pipe: https://... | Caption" class="w-full border px-3 py-2.5 text-sm font-mono mt-2" style="border-color: rgba(38,34,32,0.2);"></textarea>
                </details>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $album->is_published) ? 'checked' : '' }}>
                Published (visible on the public gallery)
            </label>

            <div class="flex items-center gap-3">
                <button class="btn btn-primary">Save Changes</button>
                <form method="POST" action="{{ route('admin.albums.destroy', $album) }}" onsubmit="return confirm('Delete this album and all its photos? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline" style="color:#7A2E3A;">Delete Album</button>
                </form>
            </div>
        </form>
    </div>

    <h2 class="font-display text-xl mb-4">Photos ({{ $album->photos->count() }})</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @forelse ($album->photos as $photo)
            <div class="bg-white p-2">
                <div class="aspect-square overflow-hidden mb-2" style="background:#F3ECE3;">
                    <img src="{{ $photo->image }}" alt="{{ $photo->caption }}" class="w-full h-full object-cover" onerror="this.style.opacity=0.2">
                </div>
                @if ($photo->caption)
                    <p class="text-xs opacity-70 truncate mb-1">{{ $photo->caption }}</p>
                @endif
                <form method="POST" action="{{ route('admin.albums.photos.destroy', [$album, $photo]) }}" onsubmit="return confirm('Remove this photo?')">
                    @csrf @method('DELETE')
                    <button class="text-xs" style="color:#7A2E3A;">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-sm opacity-60 col-span-full py-8 text-center bg-white">No photos in this album yet — add some above.</p>
        @endforelse
    </div>
@endsection
