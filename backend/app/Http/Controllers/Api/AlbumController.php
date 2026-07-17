<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $albums = Album::query()
            ->where('is_published', true)
            ->when($request->query('q'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('couple_names', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->withCount('photos')
            ->orderBy('sort_order')
            ->orderByDesc('event_date')
            ->get([
                'id', 'title', 'slug', 'couple_names', 'event_date',
                'location', 'category', 'cover_image', 'sort_order',
            ]);

        return response()->json($albums);
    }

    public function show(string $slug): JsonResponse
    {
        $album = Album::where('slug', $slug)
            ->where('is_published', true)
            ->with('photos:id,album_id,image,caption,sort_order')
            ->firstOrFail();

        return response()->json($album);
    }

    public function categories(): JsonResponse
    {
        $categories = Album::where('is_published', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json($categories);
    }
}
