<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON port of Admin\GalleryController — see routes/api.php
 * /api/admin/gallery*. Create is multipart (image_file) OR a pasted URL
 * (image), same as the Blade form.
 */
class GalleryController extends Controller
{
    public function __construct(private readonly ImageUploadService $uploads)
    {
    }

    public function index(): JsonResponse
    {
        $items = GalleryItem::orderBy('sort_order')->paginate(24);
        $categories = GalleryCategory::orderBy('sort_order')->get();

        return response()->json(['items' => $items, 'categories' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category' => ['required', 'in:'.implode(',', GalleryCategory::orderBy('sort_order')->pluck('name')->all())],
            'title' => ['required', 'string', 'max:150'],
            'image_file' => ['nullable', 'image', 'max:8192'],
            'image' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if (! $request->hasFile('image_file') && empty($data['image'])) {
            return response()->json(['message' => 'Upload a photo, or paste an image URL.'], 422);
        }

        $data['image'] = $request->hasFile('image_file')
            ? $this->uploads->store($request->file('image_file'), 'gallery')
            : $data['image'];

        unset($data['image_file']);

        $item = GalleryItem::create($data + ['sort_order' => $data['sort_order'] ?? 0]);
        ActivityLogger::log('gallery.created', "Added gallery photo \"{$item->title}\"", $item);

        return response()->json(['item' => $item, 'message' => 'Photo added to the gallery.'], 201);
    }

    public function destroy(GalleryItem $galleryItem): JsonResponse
    {
        $this->uploads->delete($galleryItem->image);
        ActivityLogger::log('gallery.deleted', "Deleted gallery photo \"{$galleryItem->title}\"", $galleryItem);
        $galleryItem->delete();

        return response()->json(['message' => 'Photo removed.']);
    }
}
