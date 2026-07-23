import { useEffect, useState } from 'react';
import { Check, Trash2, X, Star } from 'lucide-react';
import {
  deleteTestimonial,
  fetchTestimonials,
  updateTestimonialStatus,
  type Testimonial,
  type TestimonialStatus,
} from '../lib/api';

const TABS: { value: string; label: string }[] = [
  { value: '', label: 'All' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
];

const statusStyles: Record<TestimonialStatus, string> = {
  pending: 'bg-amber-100 text-amber-700',
  approved: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-red-100 text-red-700',
};

export function Testimonials() {
  const [testimonials, setTestimonials] = useState<Testimonial[]>([]);
  const [statusFilter, setStatusFilter] = useState('pending');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchTestimonials(statusFilter || undefined)
      .then((res) => setTestimonials(res.testimonials.data))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load reviews.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [statusFilter]);

  async function handleStatus(t: Testimonial, status: TestimonialStatus) {
    try {
      await updateTestimonialStatus(t.id, status);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update review.');
    }
  }

  async function handleDelete(t: Testimonial) {
    if (!confirm(`Delete ${t.name}'s review?`)) return;
    try {
      await deleteTestimonial(t.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete review.');
    }
  }

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

      <div className="flex gap-2">
        {TABS.map((tab) => (
          <button
            key={tab.value}
            onClick={() => setStatusFilter(tab.value)}
            className={`rounded-lg px-3 py-1.5 text-xs font-medium ${
              statusFilter === tab.value ? 'bg-wine text-paper' : 'border border-ink/10 text-ink hover:bg-paper-dim'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {isLoading ? (
        <p className="text-sm text-muted">Loading reviews…</p>
      ) : testimonials.length === 0 ? (
        <p className="mirror-card p-6 text-center text-sm text-muted">No reviews here.</p>
      ) : (
        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
          {testimonials.map((t) => (
            <div key={t.id} className="mirror-card space-y-2 p-4">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <p className="text-sm font-medium text-ink">{t.name}</p>
                  {t.service && <p className="text-xs text-muted">{t.service}</p>}
                </div>
                <span className={`rounded-full px-2 py-0.5 text-[11px] font-medium capitalize ${statusStyles[t.status]}`}>
                  {t.status}
                </span>
              </div>
              <div className="flex gap-0.5 text-gold">
                {Array.from({ length: 5 }).map((_, i) => (
                  <Star key={i} size={14} fill={i < t.rating ? 'currentColor' : 'none'} />
                ))}
              </div>
              <p className="text-sm text-ink/80">"{t.message}"</p>
              <div className="flex gap-2 pt-1">
                {t.status !== 'approved' && (
                  <button
                    onClick={() => handleStatus(t, 'approved')}
                    className="flex items-center gap-1 rounded-lg border border-ink/10 px-2.5 py-1.5 text-xs text-emerald-700 hover:bg-emerald-50"
                  >
                    <Check size={13} /> Approve
                  </button>
                )}
                {t.status !== 'rejected' && (
                  <button
                    onClick={() => handleStatus(t, 'rejected')}
                    className="flex items-center gap-1 rounded-lg border border-ink/10 px-2.5 py-1.5 text-xs text-amber-700 hover:bg-amber-50"
                  >
                    <X size={13} /> Reject
                  </button>
                )}
                <button
                  onClick={() => handleDelete(t)}
                  className="ml-auto flex items-center gap-1 rounded-lg border border-ink/10 px-2.5 py-1.5 text-xs text-danger hover:bg-danger-bg"
                >
                  <Trash2 size={13} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
