<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    private const CATEGORIES = ['Bridal', 'Hair', 'Makeup', 'Nails', 'Special Occasion'];

    public function __construct(private readonly ImageUploadService $uploads)
    {
    }

    public function index(): View
    {
        $items = GalleryItem::orderBy('sort_order')->paginate(24);

        return view('admin.gallery.index', ['items' => $items, 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'in:'.implode(',', self::CATEGORIES)],
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

        GalleryItem::create($data + ['sort_order' => $data['sort_order'] ?? 0]);

        return back()->with('success', 'Photo added to the gallery.');
    }

    public function destroy(GalleryItem $galleryItem): RedirectResponse
    {
        $this->uploads->delete($galleryItem->image);
        $galleryItem->delete();

        return back()->with('success', 'Photo removed.');
    }
}
