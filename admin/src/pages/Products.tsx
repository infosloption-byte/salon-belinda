import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, Pencil, X } from 'lucide-react';
import {
  createProduct,
  createProductCategory,
  deleteProduct,
  deleteProductCategory,
  fetchProducts,
  updateProduct,
  updateProductCategory,
  type PaginatedProducts,
  type Product,
  type ProductCategoryItem,
} from '../lib/api';

function formatPrice(cents: number) {
  return `LKR ${cents.toLocaleString('en-US')}`;
}

function buildFormData(form: HTMLFormElement, removeImages: string[] = []): FormData {
  const fd = new FormData(form);
  fd.delete('best_seller');
  fd.delete('is_new');
  if ((form.elements.namedItem('best_seller') as HTMLInputElement | null)?.checked) {
    fd.set('best_seller', '1');
  }
  if ((form.elements.namedItem('is_new') as HTMLInputElement | null)?.checked) {
    fd.set('is_new', '1');
  }
  removeImages.forEach((url) => fd.append('remove_images[]', url));
  return fd;
}

interface ProductFormProps {
  product?: Product;
  categories: ProductCategoryItem[];
  onCancel: () => void;
  onSaved: () => void;
}

function ProductForm({ product, categories, onCancel, onSaved }: ProductFormProps) {
  const [error, setError] = useState<string | null>(null);
  const [removeImages, setRemoveImages] = useState<string[]>([]);
  const [saving, setSaving] = useState(false);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSaving(true);
    setError(null);
    try {
      const fd = buildFormData(e.currentTarget, removeImages);
      if (product) {
        await updateProduct(product.id, fd);
      } else {
        await createProduct(fd);
      }
      onSaved();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save product.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="mirror-card grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
      {error && <p className="col-span-full text-sm text-danger">{error}</p>}

      <input name="name" required placeholder="Product name" defaultValue={product?.name}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold sm:col-span-2" />

      <select name="category" required defaultValue={product?.category ?? ''}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
        <option value="" disabled>Category…</option>
        {categories.map((c) => (
          <option key={c.id} value={c.name}>{c.name}</option>
        ))}
      </select>

      <input name="stock_count" type="number" min={0} required placeholder="Stock count" defaultValue={product?.stock_count}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <input name="price" type="number" min={0} required placeholder="Price (LKR)" defaultValue={product?.price}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <input name="compare_at_price" type="number" min={0} placeholder="Compare-at price (optional)" defaultValue={product?.compare_at_price ?? ''}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <textarea name="description" placeholder="Description" defaultValue={product?.description ?? ''} rows={2}
        className="col-span-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <textarea name="details_text" placeholder="Details — one per line" defaultValue={product?.details?.join('\n') ?? ''} rows={3}
        className="col-span-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <textarea name="images_text" placeholder="Image URLs — one per line (in addition to any uploaded below)"
        defaultValue={product?.images?.join('\n') ?? ''} rows={2}
        className="col-span-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      {product && product.images.length > 0 && (
        <div className="col-span-full flex flex-wrap gap-3">
          {product.images.map((url) => (
            <div key={url} className="relative">
              <img src={url} alt="" className="h-16 w-16 rounded-lg border border-ink/10 object-cover" />
              <label className="mt-1 flex items-center gap-1 text-[11px] text-muted">
                <input
                  type="checkbox"
                  checked={removeImages.includes(url)}
                  onChange={(e) =>
                    setRemoveImages((prev) => (e.target.checked ? [...prev, url] : prev.filter((u) => u !== url)))
                  }
                />
                Remove
              </label>
            </div>
          ))}
        </div>
      )}

      <label className="col-span-full text-xs text-muted">
        Upload new images
        <input name="image_files" type="file" accept="image/*" multiple className="mt-1 block w-full text-sm" />
      </label>

      <label className="flex items-center gap-2 text-sm text-ink">
        <input name="best_seller" type="checkbox" defaultChecked={product?.best_seller} /> Best seller
      </label>
      <label className="flex items-center gap-2 text-sm text-ink">
        <input name="is_new" type="checkbox" defaultChecked={product?.is_new} /> New
      </label>

      <div className="col-span-full flex gap-2">
        <button type="submit" disabled={saving} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light disabled:opacity-60">
          {saving ? 'Saving…' : 'Save'}
        </button>
        <button type="button" onClick={onCancel} className="rounded-lg border border-ink/10 px-4 py-2 text-sm text-ink hover:bg-paper-dim">
          Cancel
        </button>
      </div>
    </form>
  );
}

export function Products() {
  const [products, setProducts] = useState<PaginatedProducts | null>(null);
  const [categories, setCategories] = useState<ProductCategoryItem[]>([]);
  const [activeCategory, setActiveCategory] = useState<string>('');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [addingCategory, setAddingCategory] = useState(false);
  const [editingCategory, setEditingCategory] = useState<ProductCategoryItem | null>(null);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [editingProduct, setEditingProduct] = useState<Product | null>(null);

  function load() {
    setIsLoading(true);
    fetchProducts(activeCategory || undefined)
      .then((res) => {
        setProducts(res.products);
        setCategories(res.categories);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load products.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [activeCategory]);

  async function handleAddCategory(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await createProductCategory(String(form.get('name')));
      setAddingCategory(false);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add category.');
    }
  }

  async function handleRenameCategory(e: FormEvent<HTMLFormElement>, category: ProductCategoryItem) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await updateProductCategory(category.id, String(form.get('name')));
      setEditingCategory(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to rename category.');
    }
  }

  async function handleDeleteCategory(category: ProductCategoryItem) {
    if (!confirm(`Delete category "${category.name}"?`)) return;
    try {
      await deleteProductCategory(category.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete category.');
    }
  }

  async function handleDeleteProduct(product: Product) {
    if (!confirm(`Delete "${product.name}"?`)) return;
    try {
      await deleteProduct(product.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete product.');
    }
  }

  if (isLoading && !products) return <p className="text-sm text-muted">Loading products…</p>;

  return (
    <div className="space-y-6">
      {error && (
        <p className="mirror-card flex items-center justify-between p-4 text-sm text-danger">
          {error}
          <button onClick={() => setError(null)} className="text-danger/60 hover:text-danger">
            <X size={16} />
          </button>
        </p>
      )}

      <div className="mirror-card p-4">
        <div className="flex flex-wrap items-center gap-2">
          <button
            onClick={() => setActiveCategory('')}
            className={`rounded-lg px-3 py-1.5 text-xs font-medium ${activeCategory === '' ? 'bg-wine text-paper' : 'border border-ink/10 text-ink hover:bg-paper-dim'}`}
          >
            All
          </button>
          {categories.map((c) =>
            editingCategory?.id === c.id ? (
              <form key={c.id} onSubmit={(e) => handleRenameCategory(e, c)} className="flex items-center gap-1">
                <input name="name" defaultValue={c.name} className="rounded-lg border border-ink/10 bg-paper px-2 py-1 text-xs outline-none focus:border-gold" />
                <button type="submit" className="rounded-lg bg-wine px-2 py-1 text-xs text-paper">Save</button>
                <button type="button" onClick={() => setEditingCategory(null)} className="rounded-lg border border-ink/10 px-2 py-1 text-xs">✕</button>
              </form>
            ) : (
              <span key={c.id} className="group flex items-center gap-1">
                <button
                  onClick={() => setActiveCategory(c.name)}
                  className={`rounded-lg px-3 py-1.5 text-xs font-medium ${activeCategory === c.name ? 'bg-wine text-paper' : 'border border-ink/10 text-ink hover:bg-paper-dim'}`}
                >
                  {c.name}
                </button>
                <button onClick={() => setEditingCategory(c)} className="text-muted hover:text-ink"><Pencil size={12} /></button>
                <button onClick={() => handleDeleteCategory(c)} className="text-muted hover:text-danger"><Trash2 size={12} /></button>
              </span>
            )
          )}
          <button
            onClick={() => setAddingCategory((v) => !v)}
            className="flex items-center gap-1 rounded-lg border border-dashed border-ink/20 px-3 py-1.5 text-xs text-muted hover:bg-paper-dim"
          >
            <Plus size={12} /> Category
          </button>
        </div>

        {addingCategory && (
          <form onSubmit={handleAddCategory} className="mt-3 flex items-end gap-2">
            <input name="name" required placeholder="Category name" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <button type="submit" className="rounded-lg bg-wine px-3 py-2 text-sm text-paper hover:bg-wine-light">Save</button>
          </form>
        )}
      </div>

      <div className="flex justify-end">
        <button
          onClick={() => setShowCreateForm((v) => !v)}
          className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light"
        >
          <Plus size={16} /> Add Product
        </button>
      </div>

      {showCreateForm && (
        <ProductForm
          categories={categories}
          onCancel={() => setShowCreateForm(false)}
          onSaved={() => {
            setShowCreateForm(false);
            load();
          }}
        />
      )}

      {(!products || products.data.length === 0) && (
        <p className="mirror-card p-6 text-center text-sm text-muted">No products yet.</p>
      )}

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {products?.data.map((product) =>
          editingProduct?.id === product.id ? (
            <div key={product.id} className="sm:col-span-2 lg:col-span-3">
              <ProductForm
                product={product}
                categories={categories}
                onCancel={() => setEditingProduct(null)}
                onSaved={() => {
                  setEditingProduct(null);
                  load();
                }}
              />
            </div>
          ) : (
            <div key={product.id} className="mirror-card p-4">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <p className="text-sm font-medium text-ink">{product.name}</p>
                  <p className="text-xs text-muted">{product.category}</p>
                </div>
                <div className="flex shrink-0 items-center gap-2">
                  <button onClick={() => setEditingProduct(product)} className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim">
                    <Pencil size={14} />
                  </button>
                  <button onClick={() => handleDeleteProduct(product)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                    <Trash2 size={14} />
                  </button>
                </div>
              </div>
              {product.images[0] && (
                <img src={product.images[0]} alt="" className="mt-3 h-32 w-full rounded-lg object-cover" />
              )}
              <div className="mt-3 flex items-center justify-between text-sm">
                <span className="text-ink">{formatPrice(product.price)}</span>
                <span className={product.in_stock ? 'text-emerald-600' : 'text-danger'}>
                  {product.in_stock ? `${product.stock_count} in stock` : 'Out of stock'}
                </span>
              </div>
            </div>
          )
        )}
      </div>
    </div>
  );
}
