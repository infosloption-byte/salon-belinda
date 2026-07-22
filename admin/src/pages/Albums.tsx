import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, X, ChevronDown, ChevronUp } from 'lucide-react';
import {
  createAlbum,
  deleteAlbum,
  deleteAlbumPhoto,
  fetchAlbum,
  fetchAlbums,
  updateAlbum,
  type Album,
  type PaginatedAlbums,
} from '../lib/api';

function buildFormData(form: HTMLFormElement): FormData {
  const fd = new FormData(form);
  fd.delete('is_published');
  if ((form.elements.namedItem('is_published') as HTMLInputElement | null)?.checked) {
    fd.set('is_published', '1');
  }
  return fd;
}

interface AlbumFormProps {
  album?: Album;
  onCancel: () => void;
  onSaved: () => void;
}

function AlbumForm({ album, onCancel, onSaved }: AlbumFormProps) {
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [photos, setPhotos] = useState(album?.photos ?? []);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setSaving(true);
    setError(null);
    try {
      const fd = buildFormData(e.currentTarget);
      if (album) {
        const res = await updateAlbum(album.id, fd);
        setPhotos(res.album.photos ?? []);
      } else {
        await createAlbum(fd);
      }
      onSaved();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save album.');
    } finally {
      setSaving(false);
    }
  }

  async function handleDeletePhoto(photoId: number) {
    if (!album) return;
    if (!confirm('Remove this photo?')) return;
    try {
      await deleteAlbumPhoto(album.id, photoId);
      setPhotos((prev) => prev.filter((p) => p.id !== photoId));
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to remove photo.');
    }
  }

  return (
    <form onSubmit={handleSubmit} className="mirror-card grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">
      {error && <p className="col-span-full text-sm text-danger">{error}</p>}

      <input name="title" required placeholder="Album title" defaultValue={album?.title}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold sm:col-span-2" />

      <input name="couple_names" placeholder="Couple names" defaultValue={album?.couple_names ?? ''}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <input name="event_date" type="date" defaultValue={album?.event_date ?? ''}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <input name="location" placeholder="Location" defaultValue={album?.location ?? ''}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <input name="category" placeholder="Category" defaultValue={album?.category ?? ''}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      <textarea name="description" placeholder="Description" defaultValue={album?.description ?? ''} rows={2}
        className="col-span-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      {album?.cover_image && (
        <img src={album.cover_image} alt="" className="col-span-full h-32 w-full rounded-lg object-cover" />
      )}

      <label className="text-xs text-muted">
        Cover image upload
        <input name="cover_image_file" type="file" accept="image/*" className="mt-1 block w-full text-sm" />
      </label>
      <input name="cover_image" type="url" placeholder="…or paste a cover image URL" defaultValue={album?.cover_image ?? ''}
        className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />

      {album && photos.length > 0 && (
        <div className="col-span-full">
          <p className="mb-2 text-xs text-muted">Photos in this album</p>
          <div className="flex flex-wrap gap-3">
            {photos.map((p) => (
              <div key={p.id} className="relative">
                <img src={p.image} alt={p.caption ?? ''} className="h-16 w-16 rounded-lg border border-ink/10 object-cover" />
                <button
                  type="button"
                  onClick={() => handleDeletePhoto(p.id)}
                  className="absolute -right-1 -top-1 rounded-full bg-danger p-0.5 text-white"
                >
                  <X size={10} />
                </button>
              </div>
            ))}
          </div>
        </div>
      )}

      <label className="col-span-full text-xs text-muted">
        Add more photos (upload)
        <input name="photo_files" type="file" accept="image/*" multiple className="mt-1 block w-full text-sm" />
      </label>
      <textarea
        name="photo_urls"
        placeholder={'…or paste photo URLs, one per line (optionally "URL | Caption")'}
        rows={2}
        className="col-span-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
      />

      <label className="flex items-center gap-2 text-sm text-ink">
        <input name="is_published" type="checkbox" defaultChecked={album?.is_published ?? true} /> Published
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

export function Albums() {
  const [albums, setAlbums] = useState<PaginatedAlbums | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [expandedId, setExpandedId] = useState<number | null>(null);
  const [expandedAlbum, setExpandedAlbum] = useState<Album | null>(null);

  function load() {
    setIsLoading(true);
    fetchAlbums(search || undefined)
      .then((res) => setAlbums(res.albums))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load albums.'))
      .finally(() => setIsLoading(false));
  }

  useEffect(load, []);

  async function toggleExpand(album: Album) {
    if (expandedId === album.id) {
      setExpandedId(null);
      setExpandedAlbum(null);
      return;
    }
    setExpandedId(album.id);
    try {
      const res = await fetchAlbum(album.id);
      setExpandedAlbum(res.album);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load album.');
    }
  }

  async function handleDelete(album: Album) {
    if (!confirm(`Delete album "${album.title}" and all its photos?`)) return;
    try {
      await deleteAlbum(album.id);
      if (expandedId === album.id) setExpandedId(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete album.');
    }
  }

  if (isLoading && !albums) return <p className="text-sm text-muted">Loading albums…</p>;

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

      <div className="mirror-card flex flex-wrap items-end gap-3 p-4">
        <label className="flex-1 min-w-[180px]">
          <span className="mb-1 block text-xs text-muted">Search title / couple names</span>
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && load()}
            className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
          />
        </label>
        <button onClick={load} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
          Search
        </button>
      </div>

      <div className="flex justify-end">
        <button
          onClick={() => setShowCreateForm((v) => !v)}
          className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light"
        >
          <Plus size={16} /> Add Album
        </button>
      </div>

      {showCreateForm && (
        <AlbumForm
          onCancel={() => setShowCreateForm(false)}
          onSaved={() => {
            setShowCreateForm(false);
            load();
          }}
        />
      )}

      {(!albums || albums.data.length === 0) && (
        <p className="mirror-card p-6 text-center text-sm text-muted">No albums yet.</p>
      )}

      <div className="space-y-3">
        {albums?.data.map((album) => (
          <div key={album.id} className="mirror-card overflow-hidden">
            <div className="flex items-center justify-between gap-3 p-4">
              <div className="flex items-center gap-3">
                {album.cover_image && (
                  <img src={album.cover_image} alt="" className="h-12 w-12 rounded-lg object-cover" />
                )}
                <div>
                  <p className="text-sm font-medium text-ink">{album.title}</p>
                  <p className="text-xs text-muted">
                    {album.couple_names ?? '—'} · {album.event_date ?? 'no date'} · {album.photos_count ?? 0} photos
                    {!album.is_published && ' · draft'}
                  </p>
                </div>
              </div>
              <div className="flex shrink-0 items-center gap-2">
                <button onClick={() => toggleExpand(album)} className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim">
                  {expandedId === album.id ? <ChevronUp size={14} /> : <ChevronDown size={14} />}
                </button>
                <button onClick={() => handleDelete(album)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                  <Trash2 size={14} />
                </button>
              </div>
            </div>
            {expandedId === album.id && expandedAlbum && (
              <div className="border-t border-ink/5 p-4">
                <AlbumForm
                  album={expandedAlbum}
                  onCancel={() => setExpandedId(null)}
                  onSaved={() => {
                    load();
                    fetchAlbum(album.id).then((res) => setExpandedAlbum(res.album));
                  }}
                />
              </div>
            )}
          </div>
        ))}
      </div>

      {albums && albums.last_page > 1 && (
        <p className="text-center text-xs text-muted">
          Page {albums.current_page} of {albums.last_page} ({albums.total} total)
        </p>
      )}
    </div>
  );
}
