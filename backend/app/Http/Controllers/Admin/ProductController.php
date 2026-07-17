<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const CATEGORIES = ['Hair Care', 'Skin Care', 'Makeup', 'Bridal Accessories'];

    public function index(Request $request): View
    {
        $products = Product::query()
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', ['products' => $products, 'categories' => self::CATEGORIES]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product added.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'categories' => self::CATEGORIES]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', 'Product removed.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:products,slug,'.($ignoreId ?? 'NULL').',id'],
            'category' => ['required', 'in:'.implode(',', self::CATEGORIES)],
            'price' => ['required', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'details_text' => ['nullable', 'string'],
            'images_text' => ['nullable', 'string'],
            'stock_count' => ['required', 'integer', 'min:0'],
            'best_seller' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
        ]);

        // Textareas in the form use one line per item; convert to arrays for storage.
        $data['details'] = collect(explode("\n", (string) ($data['details_text'] ?? '')))
            ->map(fn ($line) => trim($line))->filter()->values()->all();
        $data['images'] = collect(explode("\n", (string) ($data['images_text'] ?? '')))
            ->map(fn ($line) => trim($line))->filter()->values()->all();
        unset($data['details_text'], $data['images_text']);

        $data['in_stock'] = $data['stock_count'] > 0;
        $data['best_seller'] = $request->boolean('best_seller');
        $data['is_new'] = $request->boolean('is_new');

        return $data;
    }
}
