<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->when($request->query('q'), function ($q, $search) {
                $q->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"));
            })
            ->when($request->boolean('inStock'), fn ($q) => $q->where('in_stock', true))
            ->when($request->query('sort'), function ($q, $sort) {
                match ($sort) {
                    'price-asc' => $q->orderBy('price'),
                    'price-desc' => $q->orderByDesc('price'),
                    'rating' => $q->orderByDesc('rating'),
                    default => $q->orderByDesc('best_seller')->orderBy('name'),
                };
            }, fn ($q) => $q->orderByDesc('best_seller')->orderBy('name'))
            ->get();

        return response()->json($products);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        return response()->json($product);
    }
}
