import { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
  ArrowLeft,
  Calendar,
  MapPin,
  Download,
  Share2,
  X,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';
import { fetchAlbum, type AlbumDetail as AlbumDetailData } from '../lib/api';

function formatDate(d: string | null) {
  if (!d) return null;
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

async function downloadImage(url: string, filename: string) {
  try {
    const response = await fetch(url, { mode: 'cors' });
    const blob = await response.blob();
    const objectUrl = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = objectUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(objectUrl);
  } catch {
    // Cross-origin host doesn't allow fetch — fall back to opening it directly,
    // the visitor can then save it manually from their browser.
    window.open(url, '_blank');
  }
}

export default function AlbumDetail() {
  const { slug } = useParams<{ slug: string }>();
  const [album, setAlbum] = useState<AlbumDetailData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const [shareCopied, setShareCopied] = useState(false);

  useEffect(() => {
    if (!slug) return;
    setLoading(true);
    fetchAlbum(slug)
      .then(setAlbum)
      .catch(() => setError("This album doesn't exist or may have been removed."))
      .finally(() => setLoading(false));
  }, [slug]);

  const close = useCallback(() => setActiveIndex(null), []);
  const step = useCallback(
    (dir: 1 | -1) => {
      if (!album) return;
      setActiveIndex((i) => {
        if (i === null) return i;
        const len = album.photos.length;
        return (i + dir + len) % len;
      });
    },
    [album]
  );

  useEffect(() => {
    if (activeIndex === null) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowRight') step(1);
      if (e.key === 'ArrowLeft') step(-1);
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [activeIndex, close, step]);

  const shareAlbum = async () => {
    const url = window.location.href;
    if (navigator.share) {
      try {
        await navigator.share({ title: album?.title, url });
        return;
      } catch {
        /* user cancelled — fall through to clipboard */
      }
    }
    await navigator.clipboard.writeText(url);
    setShareCopied(true);
    setTimeout(() => setShareCopied(false), 2000);
  };

  if (loading) {
    return <div className="max-w-6xl mx-auto px-5 py-32 text-center text-sm opacity-60">Loading album…</div>;
  }

  if (error || !album) {
    return (
      <div className="max-w-xl mx-auto px-5 py-32 text-center">
        <p className="text-sm mb-6" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
          {error}
        </p>
        <Link to="/gallery" className="text-sm inline-flex items-center gap-1.5" style={{ color: 'var(--color-magenta)' }}>
          <ArrowLeft size={14} /> Back to Gallery
        </Link>
      </div>
    );
  }

  return (
    <div>
      <section className="py-20 md:py-24" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div className="max-w-6xl mx-auto px-5 md:px-8">
          <Link
            to="/gallery"
            className="inline-flex items-center gap-1.5 text-sm mb-8"
            style={{ color: 'var(--color-ivory)', opacity: 0.7 }}
          >
            <ArrowLeft size={14} /> Back to Gallery
          </Link>

          <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
              {album.category && (
                <p className="eyebrow mb-3" style={{ color: 'var(--color-amber-light)' }}>
                  {album.category}
                </p>
              )}
              <h1 className="font-display text-4xl md:text-5xl mb-4" style={{ color: 'var(--color-ivory)' }}>
                {album.couple_names || album.title}
              </h1>
              <div className="flex flex-wrap gap-x-6 gap-y-2 text-sm" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
                {album.event_date && (
                  <span className="inline-flex items-center gap-1.5">
                    <Calendar size={14} /> {formatDate(album.event_date)}
                  </span>
                )}
                {album.location && (
                  <span className="inline-flex items-center gap-1.5">
                    <MapPin size={14} /> {album.location}
                  </span>
                )}
              </div>
              {album.description && (
                <p className="mt-5 max-w-xl text-sm leading-relaxed" style={{ color: 'var(--color-ivory)', opacity: 0.7 }}>
                  {album.description}
                </p>
              )}
            </div>

            <button
              onClick={shareAlbum}
              className="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 text-xs uppercase tracking-wide rounded-full"
              style={{ backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }}
            >
              <Share2 size={14} /> {shareCopied ? 'Link Copied!' : 'Share Album'}
            </button>
          </div>
        </div>
      </section>

      <div className="max-w-6xl mx-auto px-5 md:px-8 py-16">
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          {album.photos.map((photo, i) => (
            <button
              key={photo.id}
              onClick={() => setActiveIndex(i)}
              className="group relative aspect-square overflow-hidden rounded"
              style={{ backgroundColor: 'var(--color-ivory-dim)' }}
            >
              <img
                src={photo.image}
                alt={photo.caption || album.title}
                className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
              />
            </button>
          ))}
        </div>
      </div>

      {/* Lightbox */}
      {activeIndex !== null && album.photos[activeIndex] && (
        <div
          className="fixed inset-0 z-[100] flex items-center justify-center"
          style={{ backgroundColor: 'rgba(20,12,17,0.96)' }}
        >
          <button onClick={close} className="absolute top-5 right-5 text-white/80 hover:text-white" aria-label="Close">
            <X size={28} />
          </button>

          <button
            onClick={() => step(-1)}
            className="absolute left-3 md:left-8 top-1/2 -translate-y-1/2 text-white/70 hover:text-white"
            aria-label="Previous photo"
          >
            <ChevronLeft size={32} />
          </button>
          <button
            onClick={() => step(1)}
            className="absolute right-3 md:right-8 top-1/2 -translate-y-1/2 text-white/70 hover:text-white"
            aria-label="Next photo"
          >
            <ChevronRight size={32} />
          </button>

          <div className="max-w-4xl w-full px-14 md:px-20 flex flex-col items-center">
            <img
              src={album.photos[activeIndex].image}
              alt={album.photos[activeIndex].caption || album.title}
              className="max-h-[75vh] w-auto mx-auto object-contain"
            />
            <div className="flex items-center justify-between w-full mt-5">
              <p className="text-sm text-white/70">
                {album.photos[activeIndex].caption}
                <span className="ml-2 text-white/40">
                  {activeIndex + 1} / {album.photos.length}
                </span>
              </p>
              <button
                onClick={() =>
                  downloadImage(
                    album.photos[activeIndex].image,
                    `${album.slug}-${activeIndex + 1}.jpg`
                  )
                }
                className="inline-flex items-center gap-1.5 px-4 py-2 text-xs uppercase tracking-wide rounded-full"
                style={{ backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }}
              >
                <Download size={13} /> Download
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
