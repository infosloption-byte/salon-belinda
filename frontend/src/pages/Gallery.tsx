import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Search, MapPin, Calendar, Images } from 'lucide-react';
import { fetchAlbums, fetchAlbumCategories, type AlbumSummary } from '../lib/api';
import { site } from '../data/site';

function formatDate(d: string | null) {
  if (!d) return null;
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

export default function Gallery() {
  const [albums, setAlbums] = useState<AlbumSummary[]>([]);
  const [categories, setCategories] = useState<string[]>([]);
  const [search, setSearch] = useState('');
  const [activeCategory, setActiveCategory] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchAlbumCategories()
      .then(setCategories)
      .catch(() => {});
  }, []);

  useEffect(() => {
    setLoading(true);
    setError('');
    const timeout = setTimeout(() => {
      fetchAlbums({ q: search || undefined, category: activeCategory || undefined })
        .then(setAlbums)
        .catch(() => setError('Could not load albums right now. Please try again shortly.'))
        .finally(() => setLoading(false));
    }, 250); // debounce search typing

    return () => clearTimeout(timeout);
  }, [search, activeCategory]);

  const hasFilters = search || activeCategory;

  return (
    <div>
      <section className="py-24 md:py-28 text-center" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-amber-light)' }}>
            Real Weddings
          </p>
          <h1 className="font-display text-4xl md:text-6xl leading-tight" style={{ color: 'var(--color-ivory)' }}>
            Every Couple, <span className="text-gradient-brand italic">Their Own Story</span>
          </h1>
          <p className="mt-6 text-base" style={{ color: 'var(--color-ivory)', opacity: 0.8 }}>
            Browse complete photoshoots from real {site.name} brides — search by name, date, or
            venue, and step inside each album for the full set.
          </p>
        </div>
      </section>

      <div className="max-w-6xl mx-auto px-5 md:px-8 py-16">
        {/* Search + filter bar */}
        <div className="flex flex-col md:flex-row gap-4 mb-10">
          <div className="relative flex-1 max-w-md">
            <Search size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 opacity-40" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by couple name, venue..."
              className="w-full pl-10 pr-4 py-3 text-sm border rounded-full"
              style={{ borderColor: 'rgba(38,34,32,0.15)' }}
            />
          </div>
          <div className="flex gap-2 overflow-x-auto no-scrollbar">
            <button
              onClick={() => setActiveCategory(null)}
              className="shrink-0 px-4 py-2 text-xs uppercase tracking-wide rounded-full transition-colors"
              style={
                !activeCategory
                  ? { backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }
                  : { backgroundColor: 'var(--color-ivory-dim)', color: 'var(--color-ink)', opacity: 0.75 }
              }
            >
              All Albums
            </button>
            {categories.map((c) => (
              <button
                key={c}
                onClick={() => setActiveCategory(c)}
                className="shrink-0 px-4 py-2 text-xs uppercase tracking-wide rounded-full transition-colors"
                style={
                  activeCategory === c
                    ? { backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }
                    : { backgroundColor: 'var(--color-ivory-dim)', color: 'var(--color-ink)', opacity: 0.75 }
                }
              >
                {c}
              </button>
            ))}
          </div>
        </div>

        {error && (
          <p className="text-sm px-4 py-3 mb-8" style={{ backgroundColor: '#F3DEDB', color: '#7A2E3A' }}>
            {error}
          </p>
        )}

        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="aspect-[4/3] animate-pulse" style={{ backgroundColor: 'var(--color-ivory-dim)' }} />
            ))}
          </div>
        ) : albums.length === 0 ? (
          <div className="text-center py-24">
            <Images size={32} className="mx-auto mb-4 opacity-30" />
            <p className="text-sm opacity-60">
              {hasFilters ? 'No albums match your search.' : 'No albums published yet.'}
            </p>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {albums.map((album) => (
              <Link
                key={album.id}
                to={`/gallery/${album.slug}`}
                className="group block rounded-lg overflow-hidden border transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                style={{ borderColor: 'rgba(38,34,32,0.08)', backgroundColor: 'var(--color-ivory)' }}
              >
                <div className="relative aspect-[4/3] overflow-hidden" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
                  {album.cover_image && (
                    <img
                      src={album.cover_image}
                      alt={album.title}
                      className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                  )}
                  <div
                    className="absolute inset-0 flex items-end p-4"
                    style={{ background: 'linear-gradient(to top, rgba(0,0,0,0.55), transparent 55%)' }}
                  >
                    <span
                      className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[0.65rem] uppercase tracking-widest"
                      style={{ backgroundColor: 'rgba(251,247,243,0.9)', color: 'var(--color-ink)' }}
                    >
                      <Images size={11} /> {album.photos_count} photos
                    </span>
                  </div>
                  {album.category && (
                    <span
                      className="absolute top-3 left-3 px-3 py-1 rounded-full text-[0.65rem] uppercase tracking-widest"
                      style={{ backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }}
                    >
                      {album.category}
                    </span>
                  )}
                </div>
                <div className="p-5">
                  <h3 className="font-display text-xl mb-1.5" style={{ color: 'var(--color-ink)' }}>
                    {album.couple_names || album.title}
                  </h3>
                  <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs" style={{ color: 'var(--color-ink)', opacity: 0.55 }}>
                    {album.event_date && (
                      <span className="inline-flex items-center gap-1">
                        <Calendar size={12} /> {formatDate(album.event_date)}
                      </span>
                    )}
                    {album.location && (
                      <span className="inline-flex items-center gap-1">
                        <MapPin size={12} /> {album.location}
                      </span>
                    )}
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      <style>{`
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      `}</style>
    </div>
  );
}
