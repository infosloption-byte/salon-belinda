@extends('layouts.admin')

@section('title', 'Edit Album')

@section('content')
    <div class="bg-white p-6 max-w-3xl mb-8">
        <form method="POST" action="{{ route('admin.albums.update', $album) }}" class="space-y-5">
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
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Cover Image URL</label>
                <input name="cover_image" type="url" value="{{ old('cover_image', $album->cover_image) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Add More Photos</label>
                <textarea name="photo_urls" rows="4" placeholder="One photo URL per line, optional caption after a pipe: https://... | Caption" class="w-full border px-3 py-2.5 text-sm font-mono" style="border-color: rgba(38,34,32,0.2);"></textarea>
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
