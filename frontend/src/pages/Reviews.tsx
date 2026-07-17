import { useState } from 'react';
import { Star } from 'lucide-react';
import SectionHeading from '../components/ui/SectionHeading';
import StarRating from '../components/ui/StarRating';
import { Button } from '../components/ui/Button';
import { testimonials as initialTestimonials, type Testimonial } from '../data/testimonials';

export default function Reviews() {
  const [testimonials, setTestimonials] = useState<Testimonial[]>(initialTestimonials);
  const [name, setName] = useState('');
  const [service, setService] = useState('');
  const [rating, setRating] = useState(0);
  const [message, setMessage] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');

  const average =
    testimonials.reduce((sum, t) => sum + t.rating, 0) / (testimonials.length || 1);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !message.trim() || rating === 0) {
      setError('Please add your name, a rating, and a short message.');
      return;
    }
    setError('');
    // Frontend-only for now — once the backend is built, this will POST to
    // an /api/testimonials endpoint and likely await approval before showing.
    const newTestimonial: Testimonial = {
      id: `local-${Date.now()}`,
      name: name.trim(),
      service: service.trim() || 'General Visit',
      rating,
      message: message.trim(),
      date: new Date().toISOString().slice(0, 10),
    };
    setTestimonials([newTestimonial, ...testimonials]);
    setName('');
    setService('');
    setRating(0);
    setMessage('');
    setSubmitted(true);
  };

  return (
    <div>
      <section className="py-20 md:py-24 text-center" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-amber-light)' }}>
            Client Voices
          </p>
          <h1 className="font-display text-4xl md:text-5xl" style={{ color: 'var(--color-ivory)' }}>
            Reviews
          </h1>
          <div className="flex items-center justify-center gap-2 mt-6">
            <div className="flex gap-0.5">
              {Array.from({ length: 5 }).map((_, i) => (
                <Star
                  key={i}
                  size={20}
                  fill={i < Math.round(average) ? 'var(--color-amber)' : 'transparent'}
                  stroke="var(--color-amber)"
                />
              ))}
            </div>
            <span style={{ color: 'var(--color-ivory)', opacity: 0.85 }} className="text-sm">
              {average.toFixed(1)} from {testimonials.length} reviews
            </span>
          </div>
        </div>
      </section>

      <div className="max-w-6xl mx-auto px-5 md:px-8 py-20 grid grid-cols-1 lg:grid-cols-3 gap-14">
        <div className="lg:col-span-2 space-y-8">
          <SectionHeading eyebrow="From Our Brides &amp; Clients" title="What People Say" />
          <div className="space-y-6 mt-8">
            {testimonials.map((t) => (
              <article key={t.id} className="p-7 border" style={{ borderColor: 'rgba(38,34,32,0.1)' }}>
                <div className="flex items-center justify-between mb-3">
                  <div>
                    <p className="font-display text-lg" style={{ color: 'var(--color-ink)' }}>
                      {t.name}
                    </p>
                    <p className="text-xs" style={{ color: 'var(--color-magenta)' }}>
                      {t.service}
                    </p>
                  </div>
                  <StarRating value={t.rating} size={15} />
                </div>
                <p className="text-sm leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
                  {t.message}
                </p>
                <p className="mt-3 text-xs" style={{ color: 'var(--color-ink)', opacity: 0.4 }}>
                  {new Date(t.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })}
                </p>
              </article>
            ))}
          </div>
        </div>

        <div>
          <div className="sticky top-28 p-7 border" style={{ borderColor: 'rgba(38,34,32,0.1)', backgroundColor: 'var(--color-ivory-dim)' }}>
            <h3 className="font-display text-2xl mb-2" style={{ color: 'var(--color-ink)' }}>
              Share Your Experience
            </h3>
            <p className="text-sm mb-6" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
              Visited Salon Belinda? Let other clients know how it went.
            </p>

            {submitted && (
              <p
                className="mb-5 text-sm px-4 py-3"
                style={{ backgroundColor: 'var(--color-rose-light)', color: 'var(--color-magenta)' }}
              >
                Thank you — your review has been added.
              </p>
            )}
            {error && (
              <p className="mb-5 text-sm" style={{ color: 'var(--color-magenta)' }}>
                {error}
              </p>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="text-xs uppercase tracking-wide block mb-1.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                  Your Name
                </label>
                <input
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  className="w-full px-3 py-2.5 border bg-transparent text-sm"
                  style={{ borderColor: 'rgba(38,34,32,0.2)' }}
                  placeholder="e.g. Nimasha Perera"
                />
              </div>
              <div>
                <label className="text-xs uppercase tracking-wide block mb-1.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                  Service (optional)
                </label>
                <input
                  value={service}
                  onChange={(e) => setService(e.target.value)}
                  className="w-full px-3 py-2.5 border bg-transparent text-sm"
                  style={{ borderColor: 'rgba(38,34,32,0.2)' }}
                  placeholder="e.g. Bridal Trial"
                />
              </div>
              <div>
                <label className="text-xs uppercase tracking-wide block mb-1.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                  Rating
                </label>
                <StarRating value={rating} onChange={setRating} size={22} />
              </div>
              <div>
                <label className="text-xs uppercase tracking-wide block mb-1.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                  Your Review
                </label>
                <textarea
                  value={message}
                  onChange={(e) => setMessage(e.target.value)}
                  rows={4}
                  className="w-full px-3 py-2.5 border bg-transparent text-sm resize-none"
                  style={{ borderColor: 'rgba(38,34,32,0.2)' }}
                  placeholder="Tell us about your visit..."
                />
              </div>
              <Button type="submit" className="w-full">
                Submit Review
              </Button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
