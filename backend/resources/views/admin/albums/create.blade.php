@extends('layouts.admin')

@section('title', 'New Album')

@section('content')
    <div class="bg-white p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.albums.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Album Title *</label>
                    <input name="title" required value="{{ old('title') }}" placeholder="e.g. Nadeesha & Kasun" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Couple Names</label>
                    <input name="couple_names" value="{{ old('couple_names') }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Event Date</label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Location</label>
                    <input name="location" value="{{ old('location') }}" placeholder="e.g. Galle Fort" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Category / Tag</label>
                    <input name="category" value="{{ old('category') }}" placeholder="e.g. Beach Wedding, Kandyan, Homecoming" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    <p class="text-xs opacity-50 mt-1">Used for the filter chips on the public gallery page.</p>
                </div>
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Description</label>
                <textarea name="description" rows="3" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Cover Image URL</label>
                <input name="cover_image" type="url" value="{{ old('cover_image') }}" placeholder="https://... (leave blank to use the first photo below)" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Photos</label>
                <textarea name="photo_urls" rows="6" placeholder="One photo URL per line. Add an optional caption after a pipe:&#10;https://example.com/photo1.jpg | First look&#10;https://example.com/photo2.jpg" class="w-full border px-3 py-2.5 text-sm font-mono" style="border-color: rgba(38,34,32,0.2);">{{ old('photo_urls') }}</textarea>
                <p class="text-xs opacity-50 mt-1">Upload photos to your storage/CDN first, then paste the URLs here — one per line.</p>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                Published (visible on the public gallery)
            </label>

            <button class="btn btn-primary">Create Album</button>
        </form>
    </div>
@endsection
