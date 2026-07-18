@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Product' : 'New Product')

@section('content')
    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="bg-white p-6 space-y-5 max-w-2xl">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Name</label>
            <input name="name" value="{{ old('name', $product->name) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Slug (optional — auto-generated from name if left blank)</label>
            <input name="slug" value="{{ old('slug', $product->slug) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Category</label>
                <select name="category" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
                    @foreach ($categories as $c)
                        <option value="{{ $c }}" @selected(old('category', $product->category) === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Price (LKR)</label>
                <input type="number" name="price" value="{{ old('price', $product->price) }}" required class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Compare-at Price (optional)</label>
                <input type="number" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
            </div>
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Stock Count</label>
            <input type="number" name="stock_count" value="{{ old('stock_count', $product->stock_count) }}" required class="w-full sm:w-40 border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Description</label>
            <textarea name="description" rows="3" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">{{ old('description', $product->description) }}</textarea>
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Details (one per line)</label>
            <textarea name="details_text" rows="4" class="w-full border px-3 py-2.5 text-sm" style="border-color: rgba(38,34,32,0.2);">{{ old('details_text', $product->exists ? implode("\n", $product->details ?? []) : '') }}</textarea>
        </div>

        <div>
            <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">Photos</label>

            @if ($product->exists && !empty($product->images))
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-3">
                    @foreach ($product->images as $img)
                        <label class="relative block cursor-pointer group">
                            <img src="{{ $img }}" alt="" class="w-full aspect-square object-cover" style="background:#F3ECE3;" onerror="this.style.opacity=0.2">
                            <input type="checkbox" name="remove_images[]" value="{{ $img }}" class="absolute top-1.5 right-1.5 w-4 h-4">
                            <span class="absolute inset-0 bg-black/0 group-has-[:checked]:bg-black/40 flex items-center justify-center text-white text-xs opacity-0 group-has-[:checked]:opacity-100">Remove</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs opacity-50 mb-3">Tick a photo to remove it when you save.</p>
            @endif

            <label
                for="image-files-input"
                class="flex flex-col items-center justify-center gap-2 border-2 border-dashed px-4 py-8 text-sm cursor-pointer hover:bg-black/[0.02]"
                style="border-color: rgba(38,34,32,0.25);"
            >
                <span id="image-files-label" class="opacity-60">Click to add photos from your phone or computer — select multiple at once</span>
                <span class="text-xs opacity-40">JPG, PNG, or WEBP — up to 8MB each</span>
            </label>
            <input
                id="image-files-input" type="file" name="image_files[]" accept="image/*" multiple class="hidden"
                onchange="document.getElementById('image-files-label').textContent = this.files.length ? this.files.length + ' photo(s) selected' : 'Click to add photos from your phone or computer — select multiple at once';"
            >
            <details class="mt-2">
                <summary class="text-xs uppercase tracking-wide opacity-60 cursor-pointer select-none">Or add image URLs instead</summary>
                <textarea name="images_text" rows="3" placeholder="https://..." class="w-full border px-3 py-2.5 text-sm mt-2" style="border-color: rgba(38,34,32,0.2);">{{ old('images_text') }}</textarea>
            </details>
        </div>

        <div class="flex gap-6">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="best_seller" value="1" @checked(old('best_seller', $product->best_seller))> Bestseller badge
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_new" value="1" @checked(old('is_new', $product->is_new))> New badge
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button class="btn btn-primary">{{ $product->exists ? 'Save Changes' : 'Create Product' }}</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
@endsection
