import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, Pencil, X } from 'lucide-react';
import {
  createService,
  createServiceCategory,
  deleteService,
  deleteServiceCategory,
  fetchServiceCategories,
  updateService,
  type Service,
  type ServiceCategory,
} from '../lib/api';

function formatPrice(service: Service) {
  const prefix = service.price_prefix ? `${service.price_prefix} ` : '';
  return `${prefix}LKR ${service.price.toLocaleString('en-US')}`;
}

export function Services() {
  const [categories, setCategories] = useState<ServiceCategory[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [addingCategory, setAddingCategory] = useState(false);
  const [addingServiceTo, setAddingServiceTo] = useState<number | null>(null);
  const [editingService, setEditingService] = useState<Service | null>(null);

  function load() {
    setIsLoading(true);
    fetchServiceCategories()
      .then((res) => setCategories(res.categories))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load services.'))
      .finally(() => setIsLoading(false));
  }

  useEffect(load, []);

  async function handleAddCategory(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await createServiceCategory({
        title: String(form.get('title')),
        intro: String(form.get('intro') || ''),
      });
      setAddingCategory(false);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add category.');
    }
  }

  async function handleDeleteCategory(id: number, title: string) {
    if (!confirm(`Delete "${title}" and all its services?`)) return;
    try {
      await deleteServiceCategory(id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete category.');
    }
  }

  async function handleAddService(e: FormEvent<HTMLFormElement>, categoryId: number) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await createService({
        service_category_id: categoryId,
        name: String(form.get('name')),
        description: String(form.get('description') || ''),
        duration: String(form.get('duration') || ''),
        duration_minutes: form.get('duration_minutes') ? Number(form.get('duration_minutes')) : undefined,
        price: Number(form.get('price')),
        price_prefix: String(form.get('price_prefix') || ''),
      });
      setAddingServiceTo(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add service.');
    }
  }

  async function handleUpdateService(e: FormEvent<HTMLFormElement>, service: Service) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await updateService(service.id, {
        name: String(form.get('name')),
        description: String(form.get('description') || ''),
        duration: String(form.get('duration') || ''),
        duration_minutes: form.get('duration_minutes') ? Number(form.get('duration_minutes')) : undefined,
        price: Number(form.get('price')),
        price_prefix: String(form.get('price_prefix') || ''),
      });
      setEditingService(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update service.');
    }
  }

  async function handleDeleteService(id: number, name: string) {
    if (!confirm(`Delete "${name}"?`)) return;
    try {
      await deleteService(id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete service.');
    }
  }

  if (isLoading) return <p className="text-sm text-muted">Loading services…</p>;

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

      <div className="flex justify-end">
        <button
          onClick={() => setAddingCategory((v) => !v)}
          className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light"
        >
          <Plus size={16} /> Add Category
        </button>
      </div>

      {addingCategory && (
        <form onSubmit={handleAddCategory} className="mirror-card flex flex-wrap items-end gap-3 p-4">
          <label className="flex-1 min-w-[180px]">
            <span className="mb-1 block text-xs text-muted">Category title</span>
            <input name="title" required className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          </label>
          <label className="flex-[2] min-w-[220px]">
            <span className="mb-1 block text-xs text-muted">Intro (optional)</span>
            <input name="intro" className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          </label>
          <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
            Save
          </button>
        </form>
      )}

      {categories.length === 0 && !addingCategory && (
        <p className="mirror-card p-6 text-center text-sm text-muted">No service categories yet.</p>
      )}

      {categories.map((category) => (
        <div key={category.id} className="mirror-card p-5">
          <div className="flex items-start justify-between gap-4">
            <div>
              <h2 className="font-display text-lg text-ink">{category.title}</h2>
              {category.intro && <p className="text-sm text-muted">{category.intro}</p>}
            </div>
            <div className="flex shrink-0 items-center gap-2">
              <button
                onClick={() => setAddingServiceTo(addingServiceTo === category.id ? null : category.id)}
                className="flex items-center gap-1 rounded-lg border border-ink/10 px-3 py-1.5 text-xs font-medium text-ink hover:bg-paper-dim"
              >
                <Plus size={14} /> Service
              </button>
              <button
                onClick={() => handleDeleteCategory(category.id, category.title)}
                className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg"
              >
                <Trash2 size={14} />
              </button>
            </div>
          </div>

          {addingServiceTo === category.id && (
            <form
              onSubmit={(e) => handleAddService(e, category.id)}
              className="mt-4 grid grid-cols-1 gap-3 rounded-lg border border-ink/10 p-4 sm:grid-cols-2 lg:grid-cols-6"
            >
              <input name="name" required placeholder="Service name" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold lg:col-span-2" />
              <input name="duration" placeholder="Duration (e.g. 45 min)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input
                name="duration_minutes"
                type="number"
                min={1}
                max={1440}
                placeholder="Minutes (scheduling)"
                title="Single-sitting duration in minutes, used for appointment overlap checks"
                className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
              />
              <input name="price_prefix" placeholder="Prefix (e.g. From)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input name="price" type="number" min={0} required placeholder="Price" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input name="description" placeholder="Description (optional)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold lg:col-span-5" />
              <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
                Save
              </button>
            </form>
          )}

          <div className="mt-4 divide-y divide-ink/5">
            {category.services.length === 0 && <p className="py-3 text-sm text-muted">No services in this category yet.</p>}
            {category.services.map((service) =>
              editingService?.id === service.id ? (
                <form
                  key={service.id}
                  onSubmit={(e) => handleUpdateService(e, service)}
                  className="grid grid-cols-1 gap-3 py-3 sm:grid-cols-2 lg:grid-cols-6"
                >
                  <input name="name" required defaultValue={service.name} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold lg:col-span-2" />
                  <input name="duration" defaultValue={service.duration ?? ''} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                  <input
                    name="duration_minutes"
                    type="number"
                    min={1}
                    max={1440}
                    defaultValue={service.duration_minutes ?? ''}
                    placeholder="Minutes (scheduling)"
                    title="Single-sitting duration in minutes, used for appointment overlap checks"
                    className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
                  />
                  <input name="price_prefix" defaultValue={service.price_prefix ?? ''} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                  <input name="price" type="number" min={0} required defaultValue={service.price} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                  <input name="description" defaultValue={service.description ?? ''} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold lg:col-span-5" />
                  <div className="flex gap-2">
                    <button type="submit" className="rounded-lg bg-wine px-3 py-2 text-xs font-medium text-paper hover:bg-wine-light">
                      Save
                    </button>
                    <button type="button" onClick={() => setEditingService(null)} className="rounded-lg border border-ink/10 px-3 py-2 text-xs text-ink hover:bg-paper-dim">
                      Cancel
                    </button>
                  </div>
                </form>
              ) : (
                <div key={service.id} className="flex items-center justify-between gap-4 py-3">
                  <div>
                    <p className="text-sm text-ink">{service.name}</p>
                    <p className="text-xs text-muted">
                      {formatPrice(service)}
                      {service.duration ? ` · ${service.duration}` : ''}
                      {service.duration_minutes ? ` (${service.duration_minutes} min for scheduling)` : ''}
                    </p>
                  </div>
                  <div className="flex shrink-0 items-center gap-2">
                    <button onClick={() => setEditingService(service)} className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim">
                      <Pencil size={14} />
                    </button>
                    <button onClick={() => handleDeleteService(service.id, service.name)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                      <Trash2 size={14} />
                    </button>
                  </div>
                </div>
              )
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
