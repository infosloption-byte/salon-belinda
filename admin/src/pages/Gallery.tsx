import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, Pencil, X } from 'lucide-react';
import {
  createGalleryCategory,
  createGalleryItem,
  deleteGalleryCategory,
  deleteGalleryItem,
  fetchGallery,
  updateGalleryCategory,
  type GalleryCategoryItem,
  type GalleryItem,
  type PaginatedGalleryItems,
} from '../lib/api';

export function Gallery() {
  const [items, setItems] = useState<PaginatedGalleryItems | null>(null);
  const [categories, setCategories] = useState<GalleryCategoryItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [addingCategory, setAddingCategory] = useState(false);
  const [editingCategory, setEditingCategory] = useState<GalleryCategoryItem | null>(null);
  const [showAddForm, setShowAddForm] = useState(false);
  const [saving, setSaving] = useState(false);

  function load() {
    setIsLoading(true);
    fetchGallery()
      .then((res) => {
        setItems(res.items);
        setCategories(res.categories);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load gallery.'))
      .finally(() => setIsLoading(false));
  }

  useEffect(load, []);

  async function handleAddCategory(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await createGalleryCategory(String(form.get('name')));
      setAddingCategory(false);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add category.');
    }
  }

  async function handleRenameCategory(e: FormEvent<HTMLFormElement>, category: GalleryCategoryItem) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await updateGalleryCategory(category.id, String(form.get('name')));
      setEditingCategory(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to rename category.');
    }
  }

  async function handleDeleteCategory(category: GalleryCategoryItem) {
    if (!confirm(`Delete category "${category.name}"?`)) return;
    try {
      await deleteGalleryCategory(category.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete category.');
    }
  }

  async function handleAddItem(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSaving(true);
    try {
      const fd = new FormData(e.currentTarget);
      await createGalleryItem(fd);
      setShowAddForm(false);
      (e.target as HTMLFormElement).reset();
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add photo.');
    } finally {
      setSaving(false);
    }
  }

  async function handleDeleteItem(item: GalleryItem) {
    if (!confirm(`Delete "${item.title}"?`)) return;
    try {
      await deleteGalleryItem(item.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete photo.');
    }
  }

  if (isLoading && !items) return <p className="text-sm text-muted">Loading gallery…</p>;

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
          {categories.map((c) =>
            editingCategory?.id === c.id ? (
              <form key={c.id} onSubmit={(e) => handleRenameCategory(e, c)} className="flex items-center gap-1">
                <input name="name" defaultValue={c.name} className="rounded-lg border border-ink/10 bg-paper px-2 py-1 text-xs outline-none focus:border-gold" />
                <button type="submit" className="rounded-lg bg-wine px-2 py-1 text-xs text-paper">Save</button>
                <button type="button" onClick={() => setEditingCategory(null)} className="rounded-lg border border-ink/10 px-2 py-1 text-xs">✕</button>
              </form>
            ) : (
              <span key={c.id} className="group flex items-center gap-1 rounded-lg border border-ink/10 px-3 py-1.5 text-xs font-medium text-ink">
                {c.name}
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
          onClick={() => setShowAddForm((v) => !v)}
          className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light"
        >
          <Plus size={16} /> Add Photo
        </button>
      </div>

      {showAddForm && (
        <form onSubmit={handleAddItem} className="mirror-card grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
          <input name="title" required placeholder="Title" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <select name="category" required defaultValue="" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
            <option value="" disabled>Category…</option>
            {categories.map((c) => (
              <option key={c.id} value={c.name}>{c.name}</option>
            ))}
          </select>
          <label className="col-span-full text-xs text-muted">
            Upload a photo
            <input name="image_file" type="file" accept="image/*" className="mt-1 block w-full text-sm" />
          </label>
          <input name="image" type="url" placeholder="…or paste an image URL instead" className="col-span-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <div className="col-span-full flex gap-2">
            <button type="submit" disabled={saving} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light disabled:opacity-60">
              {saving ? 'Saving…' : 'Save'}
            </button>
            <button type="button" onClick={() => setShowAddForm(false)} className="rounded-lg border border-ink/10 px-4 py-2 text-sm text-ink hover:bg-paper-dim">
              Cancel
            </button>
          </div>
        </form>
      )}

      {(!items || items.data.length === 0) && (
        <p className="mirror-card p-6 text-center text-sm text-muted">No photos yet.</p>
      )}

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        {items?.data.map((item) => (
          <div key={item.id} className="mirror-card overflow-hidden">
            <img src={item.image} alt={item.title} className="h-32 w-full object-cover" />
            <div className="flex items-center justify-between gap-2 p-3">
              <div>
                <p className="text-xs font-medium text-ink">{item.title}</p>
                <p className="text-[11px] text-muted">{item.category}</p>
              </div>
              <button onClick={() => handleDeleteItem(item)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                <Trash2 size={14} />
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
