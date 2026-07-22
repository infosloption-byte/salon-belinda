<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * JSON port of Admin\ProductCategoryController — see routes/api.php
 * /api/admin/products/categories*.
 */
class ProductCategoryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:product_categories,name'],
        ]);

        $category = ProductCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => ProductCategory::max('sort_order') + 1,
        ]);
        ActivityLogger::log('product_category.created', "Added product category \"{$category->name}\"", $category);

        return response()->json(['category' => $category, 'message' => 'Category added.'], 201);
    }

    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('product_categories', 'name')->ignore($productCategory->id)],
        ]);

        $oldName = $productCategory->name;
        $newName = $data['name'];

        if ($oldName !== $newName) {
            DB::transaction(function () use ($productCategory, $oldName, $newName) {
                $productCategory->update(['name' => $newName, 'slug' => Str::slug($newName)]);
                Product::where('category', $oldName)->update(['category' => $newName]);
                ActivityLogger::log('product_category.renamed', "Renamed product category \"{$oldName}\" to \"{$newName}\"", $productCategory);
            });
        }

        return response()->json(['category' => $productCategory->fresh(), 'message' => 'Category renamed. Existing products were updated to match.']);
    }

    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        if (Product::where('category', $productCategory->name)->exists()) {
            return response()->json([
                'message' => "\"{$productCategory->name}\" is still used by one or more products. Move them to another category first.",
            ], 422);
        }

        ActivityLogger::log('product_category.deleted', "Deleted product category \"{$productCategory->name}\"", $productCategory);
        $productCategory->delete();

        return response()->json(['message' => 'Category removed.']);
    }
}
