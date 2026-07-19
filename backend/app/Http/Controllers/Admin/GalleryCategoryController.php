<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GalleryCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:gallery_categories,name'],
        ]);

        $category = GalleryCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => GalleryCategory::max('sort_order') + 1,
        ]);
        ActivityLogger::log('gallery_category.created', "Added gallery category \"{$category->name}\"", $category);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, GalleryCategory $galleryCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('gallery_categories', 'name')->ignore($galleryCategory->id)],
        ]);

        $oldName = $galleryCategory->name;
        $newName = $data['name'];

        if ($oldName !== $newName) {
            DB::transaction(function () use ($galleryCategory, $oldName, $newName) {
                $galleryCategory->update(['name' => $newName, 'slug' => Str::slug($newName)]);
                GalleryItem::where('category', $oldName)->update(['category' => $newName]);
                ActivityLogger::log('gallery_category.renamed', "Renamed gallery category \"{$oldName}\" to \"{$newName}\"", $galleryCategory);
            });
        }

        return back()->with('success', 'Category renamed. Existing photos were updated to match.');
    }

    public function destroy(GalleryCategory $galleryCategory): RedirectResponse
    {
        if (GalleryItem::where('category', $galleryCategory->name)->exists()) {
            return back()->withErrors([
                'category' => "\"{$galleryCategory->name}\" is still used by one or more photos. Move them to another category first.",
            ]);
        }

        ActivityLogger::log('gallery_category.deleted', "Deleted gallery category \"{$galleryCategory->name}\"", $galleryCategory);
        $galleryCategory->delete();

        return back()->with('success', 'Category removed.');
    }
}
