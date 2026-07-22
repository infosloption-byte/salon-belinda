<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * JSON port of Admin\GalleryCategoryController — see routes/api.php
 * /api/admin/gallery/categories*.
 */
class GalleryCategoryController extends Controller
{
    public function store(Request $request): JsonResponse
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

        return response()->json(['category' => $category, 'message' => 'Category added.'], 201);
    }

    public function update(Request $request, GalleryCategory $galleryCategory): JsonResponse
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

        return response()->json(['category' => $galleryCategory->fresh(), 'message' => 'Category renamed. Existing photos were updated to match.']);
    }

    public function destroy(GalleryCategory $galleryCategory): JsonResponse
    {
        if (GalleryItem::where('category', $galleryCategory->name)->exists()) {
            return response()->json([
                'message' => "\"{$galleryCategory->name}\" is still used by one or more photos. Move them to another category first.",
            ], 422);
        }

        ActivityLogger::log('gallery_category.deleted', "Deleted gallery category \"{$galleryCategory->name}\"", $galleryCategory);
        $galleryCategory->delete();

        return response()->json(['message' => 'Category removed.']);
    }
}
