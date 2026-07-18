@extends('layouts.admin')

@section('title', 'New Album')

@section('content')
    <div class="bg-white p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.albums.store') }}" enctype="multipart/form-data" class="space-y-5">
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
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Cover Photo</label>
                <label
                    for="cover-image-input"
                    class="flex flex-col items-center justify-center gap-2 border-2 border-dashed px-4 py-6 text-sm cursor-pointer hover:bg-black/[0.02]"
                    style="border-color: rgba(38,34,32,0.25);"
                >
                    <img id="cover-image-preview" class="hidden max-h-32 object-contain mb-1" alt="">
                    <span id="cover-image-label" class="opacity-60">Click to choose a cover photo (optional — uses the first photo below if left blank)</span>
                </label>
                <input
                    id="cover-image-input" type="file" name="cover_image_file" accept="image/*" class="hidden"
                    onchange="
                        const f = this.files[0];
                        const preview = document.getElementById('cover-image-preview');
                        const label = document.getElementById('cover-image-label');
                        if (f) { preview.src = URL.createObjectURL(f); preview.classList.remove('hidden'); label.textContent = f.name; }
                    "
                >
                <details class="mt-2">
                    <summary class="text-xs uppercase tracking-wide opacity-60 cursor-pointer select-none">Or use an image URL instead</summary>
                    <input name="cover_image" type="url" value="{{ old('cover_image') }}" placeholder="https://..." class="w-full border px-3 py-2.5 text-sm mt-2" style="border-color: rgba(38,34,32,0.2);">
                </details>
            </div>

            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Photos</label>
                <label
                    for="photo-files-input"
                    class="flex flex-col items-center justify-center gap-2 border-2 border-dashed px-4 py-8 text-sm cursor-pointer hover:bg-black/[0.02]"
                    style="border-color: rgba(38,34,32,0.25);"
                >
                    <span id="photo-files-label" class="opacity-60">Click to choose photos from your phone or computer — select multiple at once</span>
                    <span class="text-xs opacity-40">JPG, PNG, or WEBP — up to 8MB each</span>
                </label>
                <input
                    id="photo-files-input" type="file" name="photo_files[]" accept="image/*" multiple class="hidden"
                    onchange="document.getElementById('photo-files-label').textContent = this.files.length ? this.files.length + ' photo(s) selected' : 'Click to choose photos from your phone or computer — select multiple at once';"
                >
                <details class="mt-3">
                    <summary class="text-xs uppercase tracking-wide opacity-60 cursor-pointer select-none">Or add photo URLs instead</summary>
                    <textarea name="photo_urls" rows="5" placeholder="One photo URL per line. Add an optional caption after a pipe:&#10;https://example.com/photo1.jpg | First look&#10;https://example.com/photo2.jpg" class="w-full border px-3 py-2.5 text-sm font-mono mt-2" style="border-color: rgba(38,34,32,0.2);">{{ old('photo_urls') }}</textarea>
                </details>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                Published (visible on the public gallery)
            </label>

            <button class="btn btn-primary">Create Album</button>
        </form>
    </div>
@endsection
