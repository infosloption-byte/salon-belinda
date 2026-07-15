import { useState } from 'react';
import { X, ChevronLeft, ChevronRight } from 'lucide-react';
import CornerFrame from '../components/ui/CornerFrame';
import PortfolioImage from '../components/ui/PortfolioImage';
import { galleryItems, galleryCategories, type GalleryItem } from '../data/gallery';

type Filter = 'All' | GalleryItem['category'];

export default function Gallery() {
  const [filter, setFilter] = useState<Filter>('All');
  const [activeIndex, setActiveIndex] = useState<number | null>(null);

  const filtered = filter === 'All' ? galleryItems : galleryItems.filter((g) => g.category === filter);

  const openAt = (item: GalleryItem) => {
    setActiveIndex(filtered.findIndex((g) => g.id === item.id));
  };

  const close = () => setActiveIndex(null);
  const step = (dir: 1 | -1) => {
    if (activeIndex === null) return;
    setActiveIndex((activeIndex + dir + filtered.length) % filtered.length);
  };

  const active = activeIndex !== null ? filtered[activeIndex] : null;

  return (
    <div>
      <section className="py-20 md:py-24 text-center" style={{ backgroundColor: 'var(--color-green)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-gold-light)' }}>
            Portfolio
          </p>
          <h1 className="font-display text-4xl md:text-5xl" style={{ color: 'var(--color-ivory)' }}>
            Gallery
          </h1>
          <p className="mt-5" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            Bridal work, first and foremost — alongside hair, makeup, nails, and special
            occasion styling.
          </p>
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-5 md:px-8 py-16">
        <div className="flex flex-wrap gap-3 justify-center mb-12">
          {(['All', ...galleryCategories] as Filter[]).map((c) => (
            <button
              key={c}
              onClick={() => setFilter(c)}
              className="px-5 py-2 text-xs uppercase tracking-wide border transition-colors"
              style={
                filter === c
                  ? { backgroundColor: 'var(--color-maroon)', color: 'var(--color-ivory)', borderColor: 'var(--color-maroon)' }
                  : { borderColor: 'rgba(38,34,32,0.2)', color: 'var(--color-ink)' }
              }
            >
              {c}
            </button>
          ))}
        </div>

        <div className="columns-2 md:columns-3 gap-5 [column-fill:_balance]">
          {filtered.map((item) => (
            <button
              key={item.id}
              onClick={() => openAt(item)}
              className="relative block w-full mb-5 break-inside-avoid p-1.5 group text-left"
            >
              <PortfolioImage
                src={item.image}
                alt={item.title}
                className="w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
              />
              <CornerFrame />
              <span
                className="absolute bottom-3 left-3 right-3 text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity"
                style={{ backgroundColor: 'rgba(38,34,32,0.75)', color: 'var(--color-ivory)' }}
              >
                {item.title}
              </span>
            </button>
          ))}
        </div>
      </div>

      {active && (
        <div
          className="fixed inset-0 z-[100] flex items-center justify-center px-4"
          style={{ backgroundColor: 'rgba(38,34,32,0.92)' }}
          onClick={close}
          role="dialog"
          aria-modal="true"
        >
          <button
            className="absolute top-6 right-6 p-2"
            onClick={close}
            aria-label="Close"
          >
            <X color="var(--color-ivory)" size={26} />
          </button>
          <button
            className="absolute left-3 md:left-8 p-2"
            onClick={(e) => { e.stopPropagation(); step(-1); }}
            aria-label="Previous image"
          >
            <ChevronLeft color="var(--color-ivory)" size={32} />
          </button>
          <button
            className="absolute right-3 md:right-8 p-2"
            onClick={(e) => { e.stopPropagation(); step(1); }}
            aria-label="Next image"
          >
            <ChevronRight color="var(--color-ivory)" size={32} />
          </button>
          <div
            className="relative max-w-2xl w-full aspect-[4/5] p-4"
            onClick={(e) => e.stopPropagation()}
          >
            <PortfolioImage src={active.image} alt={active.title} className="w-full h-full object-cover" />
            <CornerFrame color="var(--color-gold-light)" />
            <p className="text-center mt-4 font-display italic text-xl" style={{ color: 'var(--color-ivory)' }}>
              {active.title}
            </p>
          </div>
        </div>
      )}
    </div>
  );
}
