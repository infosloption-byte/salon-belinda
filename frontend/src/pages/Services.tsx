import { Link } from 'react-router-dom';
import { Clock, ArrowRight } from 'lucide-react';
import SectionHeading from '../components/ui/SectionHeading';
import { serviceCategories } from '../data/services';

export default function Services() {
  return (
    <div>
      <section className="py-20 md:py-24 text-center" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-amber-light)' }}>
            Service Menu
          </p>
          <h1 className="font-display text-4xl md:text-5xl" style={{ color: 'var(--color-ivory)' }}>
            Every Occasion, Considered
          </h1>
          <p className="mt-5" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            Prices shown are a guide — final pricing depends on hair length, product choice, and
            add-ons, confirmed at booking.
          </p>
        </div>
      </section>

      <div className="max-w-5xl mx-auto px-5 md:px-8 py-20 space-y-20">
        {serviceCategories.map((cat) => (
          <section key={cat.id} id={cat.id}>
            <SectionHeading eyebrow={`${cat.services.length} Services`} title={cat.title} />
            <p className="mt-4 mb-9 max-w-2xl text-sm leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
              {cat.intro}
            </p>
            <div className="divide-y" style={{ borderColor: 'rgba(38,34,32,0.1)' }}>
              {cat.services.map((s) => (
                <div
                  key={s.id}
                  className="py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t"
                  style={{ borderColor: 'rgba(38,34,32,0.1)' }}
                >
                  <div className="sm:max-w-md">
                    <h3 className="font-display text-xl mb-1" style={{ color: 'var(--color-ink)' }}>
                      {s.name}
                    </h3>
                    <p className="text-sm mb-2" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                      {s.description}
                    </p>
                    <span className="inline-flex items-center gap-1 text-xs" style={{ color: 'var(--color-ink)', opacity: 0.5 }}>
                      <Clock size={13} /> {s.duration}
                    </span>
                  </div>
                  <div className="flex items-center gap-5 sm:flex-col sm:items-end shrink-0">
                    <span className="font-display italic text-lg" style={{ color: 'var(--color-magenta)' }}>
                      {s.price}
                    </span>
                    <Link
                      to={`/booking?service=${s.id}`}
                      className="text-xs uppercase tracking-wide inline-flex items-center gap-1"
                      style={{ color: 'var(--color-ink)' }}
                    >
                      Book <ArrowRight size={12} />
                    </Link>
                  </div>
                </div>
              ))}
            </div>
          </section>
        ))}
      </div>
    </div>
  );
}
