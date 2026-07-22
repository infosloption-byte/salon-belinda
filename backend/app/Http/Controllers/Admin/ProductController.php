<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * JSON port of Admin\ProductController. Same validation/logging/behaviour
 * as the Blade version, JSON responses instead of redirect()->back().
 * Create/update accept multipart/form-data (image_files[]) same as the
 * Blade form did — routes live under /api/admin/products* in routes/api.php.
 */
class ProductController extends Controller
{
    public function __construct(private readonly ImageUploadService $uploads)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'products' => $products,
            'categories' => ProductCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['images'] = $this->mergeImages($request, [], $data['images']);

        $product = Product::create($data);
        ActivityLogger::log('product.created', "Created product \"{$product->name}\"", $product);

        return response()->json(['product' => $product, 'message' => 'Product added.'], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $this->validated($request, $product->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['images'] = $this->mergeImages($request, $product->images ?? [], $data['images']);

        $product->update($data);
        ActivityLogger::log('product.updated', "Updated product \"{$product->name}\"", $product);

        return response()->json(['product' => $product, 'message' => 'Product updated.']);
    }

    public function destroy(Product $product): JsonResponse
    {
        foreach ($product->images ?? [] as $image) {
            $this->uploads->delete($image);
        }

        ActivityLogger::log('product.deleted', "Deleted product \"{$product->name}\"", $product);
        $product->delete();

        return response()->json(['message' => 'Product removed.']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:products,slug,'.($ignoreId ?? 'NULL').',id'],
            'category' => ['required', 'in:'.implode(',', $this->categoryNames())],
            'price' => ['required', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'details_text' => ['nullable', 'string'],
            'images_text' => ['nullable', 'string'],
            'image_files' => ['nullable', 'array'],
            'image_files.*' => ['image', 'max:8192'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string'],
            'stock_count' => ['required', 'integer', 'min:0'],
            'best_seller' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
        ]);

        $data['details'] = collect(explode("\n", (string) ($data['details_text'] ?? '')))
            ->map(fn ($line) => trim($line))->filter()->values()->all();
        $data['images'] = collect(explode("\n", (string) ($data['images_text'] ?? '')))
            ->map(fn ($line) => trim($line))->filter()->values()->all();
        unset($data['details_text'], $data['images_text']);

        $data['in_stock'] = $data['stock_count'] > 0;
        $data['best_seller'] = $request->boolean('best_seller');
        $data['is_new'] = $request->boolean('is_new');
        unset($data['image_files'], $data['remove_images']);

        return $data;
    }

    private function mergeImages(Request $request, array $existingImages, array $pastedUrls): array
    {
        $toRemove = $request->input('remove_images', []);

        $kept = array_values(array_diff($existingImages, $toRemove));

        foreach ($toRemove as $removedUrl) {
            $this->uploads->delete($removedUrl);
        }

        $uploaded = [];
        foreach ($request->file('image_files', []) as $file) {
            if ($file && $file->isValid()) {
                $uploaded[] = $this->uploads->store($file, 'products');
            }
        }

        return array_values(array_unique([...$kept, ...$pastedUrls, ...$uploaded]));
    }

    /**
     * @return array<int, string>
     */
    private function categoryNames(): array
    {
        return ProductCategory::orderBy('sort_order')->pluck('name')->all();
    }
}
