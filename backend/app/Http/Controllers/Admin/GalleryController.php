<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(private readonly ImageUploadService $uploads)
    {
    }

    public function index(): View
    {
        $items = GalleryItem::orderBy('sort_order')->paginate(24);
        $categories = GalleryCategory::orderBy('sort_order')->get();

        return view('admin.gallery.index', ['items' => $items, 'categories' => $categories->pluck('name')->all(), 'categoryModels' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'in:'.implode(',', GalleryCategory::orderBy('sort_order')->pluck('name')->all())],
            'title' => ['required', 'string', 'max:150'],
            'image_file' => ['nullable', 'image', 'max:8192'],
            'image' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if (! $request->hasFile('image_file') && empty($data['image'])) {
            return back()->withErrors(['image_file' => 'Upload a photo, or paste an image URL.'])->withInput();
        }

        $data['image'] = $request->hasFile('image_file')
            ? $this->uploads->store($request->file('image_file'), 'gallery')
            : $data['image'];

        unset($data['image_file']);

        $item = GalleryItem::create($data + ['sort_order' => $data['sort_order'] ?? 0]);
        ActivityLogger::log('gallery.created', "Added gallery photo \"{$item->title}\"", $item);

        return back()->with('success', 'Photo added to the gallery.');
    }

    public function destroy(GalleryItem $galleryItem): RedirectResponse
    {
        $this->uploads->delete($galleryItem->image);
        ActivityLogger::log('gallery.deleted', "Deleted gallery photo \"{$galleryItem->title}\"", $galleryItem);
        $galleryItem->delete();

        return back()->with('success', 'Photo removed.');
    }
}
