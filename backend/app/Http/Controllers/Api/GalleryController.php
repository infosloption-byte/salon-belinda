<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = GalleryItem::query()
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->get(['id', 'category', 'title', 'image']);

        return response()->json($items);
    }
}
