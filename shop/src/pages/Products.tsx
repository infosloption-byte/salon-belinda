import { useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { SlidersHorizontal, X } from 'lucide-react';
import { products, categories, type Category } from '../data/products';
import ProductCard from '../components/product/ProductCard';

type SortKey = 'featured' | 'price-asc' | 'price-desc' | 'rating';

const sortOptions: { value: SortKey; label: string }[] = [
  { value: 'featured', label: 'Featured' },
  { value: 'price-asc', label: 'Price: Low to High' },
  { value: 'price-desc', label: 'Price: High to Low' },
  { value: 'rating', label: 'Top Rated' },
];

export default function Products() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [filtersOpen, setFiltersOpen] = useState(false);

  const activeCategory = searchParams.get('category') as Category | null;
  const query = searchParams.get('q') || '';
  const sort = (searchParams.get('sort') as SortKey) || 'featured';
  const inStockOnly = searchParams.get('inStock') === '1';

  const setParam = (key: string, value: string | null) => {
    const next = new URLSearchParams(searchParams);
    if (value) next.set(key, value);
    else next.delete(key);
    setSearchParams(next);
  };

  const filtered = useMemo(() => {
    let list = [...products];
    if (activeCategory) list = list.filter((p) => p.category === activeCategory);
    if (query) {
      const q = query.toLowerCase();
      list = list.filter(
        (p) => p.name.toLowerCase().includes(q) || p.description.toLowerCase().includes(q)
      );
    }
    if (inStockOnly) list = list.filter((p) => p.inStock);

    switch (sort) {
      case 'price-asc':
        list.sort((a, b) => a.price - b.price);
        break;
      case 'price-desc':
        list.sort((a, b) => b.price - a.price);
        break;
      case 'rating':
        list.sort((a, b) => b.rating - a.rating);
        break;
      default:
        list.sort((a, b) => Number(b.bestSeller) - Number(a.bestSeller));
    }
    return list;
  }, [activeCategory, query, sort, inStockOnly]);

  const FilterPanel = (
    <div className="space-y-8">
      <div>
        <p className="eyebrow mb-4" style={{ color: 'var(--color-gold)' }}>Category</p>
        <div className="space-y-2.5">
          <button
            onClick={() => setParam('category', null)}
            className="block text-sm"
            style={{ color: !activeCategory ? 'var(--color-maroon)' : 'var(--color-ink)', opacity: !activeCategory ? 1 : 0.7 }}
          >
            All Products
          </button>
          {categories.map((c) => (
            <button
              key={c}
              onClick={() => setParam('category', c)}
              className="block text-sm"
              style={{ color: activeCategory === c ? 'var(--color-maroon)' : 'var(--color-ink)', opacity: activeCategory === c ? 1 : 0.7 }}
            >
              {c}
            </button>
          ))}
        </div>
      </div>
      <div>
        <p className="eyebrow mb-4" style={{ color: 'var(--color-gold)' }}>Availability</p>
        <label className="flex items-center gap-2.5 text-sm" style={{ color: 'var(--color-ink)', opacity: 0.8 }}>
          <input
            type="checkbox"
            checked={inStockOnly}
            onChange={(e) => setParam('inStock', e.target.checked ? '1' : null)}
            className="accent-[var(--color-maroon)]"
          />
          In stock only
        </label>
      </div>
    </div>
  );

  return (
    <div className="max-w-7xl mx-auto px-5 md:px-8 py-12 md:py-16">
      <div className="mb-8 md:mb-12">
        <p className="eyebrow mb-3" style={{ color: 'var(--color-gold)' }}>
          {query ? `Results for "${query}"` : activeCategory || 'Shop All'}
        </p>
        <h1 className="font-display text-3xl md:text-4xl" style={{ color: 'var(--color-ink)' }}>
          {activeCategory || 'All Products'}
        </h1>
      </div>

      <div className="flex flex-col lg:flex-row gap-10">
        <aside className="hidden lg:block w-56 shrink-0">{FilterPanel}</aside>

        <div className="flex-1 min-w-0">
          <div className="flex items-center justify-between mb-8 gap-3">
            <button
              onClick={() => setFiltersOpen(true)}
              className="lg:hidden flex items-center gap-2 text-sm px-4 py-2 border"
              style={{ borderColor: 'rgba(38,34,32,0.2)', color: 'var(--color-ink)' }}
            >
              <SlidersHorizontal size={14} /> Filters
            </button>
            <p className="text-sm hidden sm:block" style={{ color: 'var(--color-ink)', opacity: 0.55 }}>
              {filtered.length} product{filtered.length !== 1 ? 's' : ''}
            </p>
            <select
              value={sort}
              onChange={(e) => setParam('sort', e.target.value === 'featured' ? null : e.target.value)}
              className="text-sm border px-3 py-2 bg-transparent ml-auto"
              style={{ borderColor: 'rgba(38,34,32,0.2)', color: 'var(--color-ink)' }}
              aria-label="Sort products"
            >
              {sortOptions.map((o) => (
                <option key={o.value} value={o.value}>{o.label}</option>
              ))}
            </select>
          </div>

          {filtered.length === 0 ? (
            <div className="text-center py-24">
              <p className="font-display text-2xl mb-3" style={{ color: 'var(--color-ink)' }}>No products found</p>
              <p className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                Try a different search term or clear your filters.
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
              {filtered.map((p) => (
                <ProductCard key={p.id} product={p} />
              ))}
            </div>
          )}
        </div>
      </div>

      {filtersOpen && (
        <div className="fixed inset-0 z-[95] lg:hidden" role="dialog" aria-modal="true">
          <div className="absolute inset-0" style={{ backgroundColor: 'rgba(38,34,32,0.5)' }} onClick={() => setFiltersOpen(false)} />
          <div
            className="absolute top-0 left-0 h-full w-full max-w-xs p-6 overflow-y-auto"
            style={{ backgroundColor: 'var(--color-ivory)' }}
          >
            <div className="flex items-center justify-between mb-8">
              <h2 className="font-display text-xl" style={{ color: 'var(--color-ink)' }}>Filters</h2>
              <button onClick={() => setFiltersOpen(false)} aria-label="Close filters"><X size={20} /></button>
            </div>
            {FilterPanel}
          </div>
        </div>
      )}
    </div>
  );
}
