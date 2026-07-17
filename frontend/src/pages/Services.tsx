import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Clock, ArrowRight, Sparkles, Scissors, Droplet, Palette, Hand } from 'lucide-react';
import SectionHeading from '../components/ui/SectionHeading';
import { serviceCategories } from '../data/services';

const categoryIcons: Record<string, React.ComponentType<{ size?: number; color?: string }>> = {
  bridal: Sparkles,
  hair: Scissors,
  skin: Droplet,
  makeup: Palette,
  nails: Hand,
  occasion: Sparkles,
};

export default function Services() {
  const [active, setActive] = useState(serviceCategories[0]?.id);

  return (
    <div>
      <section
        className="py-24 md:py-28 text-center relative overflow-hidden"
        style={{ backgroundColor: 'var(--color-deep)' }}
      >
        <div
          className="absolute -top-24 -right-24 w-96 h-96 rounded-full opacity-20 blur-3xl"
          style={{ backgroundImage: 'var(--gradient-brand)' }}
          aria-hidden="true"
        />
        <div className="max-w-3xl mx-auto px-5 relative">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-amber-light)' }}>
            Service Menu
          </p>
          <h1 className="font-display text-4xl md:text-6xl leading-tight" style={{ color: 'var(--color-ivory)' }}>
            Every Occasion, <span className="text-gradient-brand italic">Considered</span>
          </h1>
          <p className="mt-6 text-base" style={{ color: 'var(--color-ivory)', opacity: 0.8 }}>
            From a first bridal trial to your weekly wash-and-blow, every service is styled with
            the same care. Prices shown are a guide — final pricing depends on hair length,
            product choice, and add-ons, confirmed at booking.
          </p>
        </div>
      </section>

      {/* Quick-jump category nav */}
      <div
        className="sticky top-20 md:top-[116px] z-30 border-b"
        style={{ backgroundColor: 'var(--color-ivory)', borderColor: 'rgba(38,34,32,0.08)' }}
      >
        <div className="max-w-5xl mx-auto px-5 md:px-8 py-3 flex gap-2 overflow-x-auto no-scrollbar">
          {serviceCategories.map((cat) => {
            const Icon = categoryIcons[cat.id] ?? Sparkles;
            const isActive = active === cat.id;
            return (
              <a
                key={cat.id}
                href={`#${cat.id}`}
                onClick={() => setActive(cat.id)}
                className="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 text-xs uppercase tracking-wide rounded-full transition-colors"
                style={
                  isActive
                    ? { backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }
                    : { backgroundColor: 'var(--color-ivory-dim)', color: 'var(--color-ink)', opacity: 0.75 }
                }
              >
                <Icon size={13} />
                {cat.title}
              </a>
            );
          })}
        </div>
      </div>

      <div className="max-w-5xl mx-auto px-5 md:px-8 py-20 space-y-24">
        {serviceCategories.map((cat, catIndex) => {
          const Icon = categoryIcons[cat.id] ?? Sparkles;
          return (
            <section key={cat.id} id={cat.id} className="scroll-mt-32 md:scroll-mt-44">
              <div className="flex items-start gap-4 mb-4">
                <div
                  className="w-12 h-12 rounded-full flex items-center justify-center shrink-0"
                  style={{ backgroundImage: 'var(--gradient-brand)' }}
                >
                  <Icon size={20} />
                </div>
                <div>
                  <SectionHeading eyebrow={`${cat.services.length} Services`} title={cat.title} />
                </div>
              </div>
              <p
                className="mb-10 max-w-2xl text-sm leading-relaxed"
                style={{ color: 'var(--color-ink)', opacity: 0.65 }}
              >
                {cat.intro}
              </p>

              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {cat.services.map((s, i) => (
                  <div
                    key={s.id}
                    className="group relative p-6 pt-8 rounded-lg border transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl overflow-hidden"
                    style={{
                      borderColor: 'rgba(38,34,32,0.08)',
                      backgroundImage: 'linear-gradient(160deg, var(--color-rose-light) 0%, var(--color-ivory) 60%)',
                    }}
                  >
                    <div
                      className="absolute -top-12 -right-12 w-40 h-40 rounded-full opacity-25 blur-2xl pointer-events-none transition-opacity duration-300 group-hover:opacity-40"
                      style={{ backgroundImage: 'var(--gradient-brand)' }}
                      aria-hidden="true"
                    />
                    <div
                      className="absolute left-0 right-0 top-0 h-1.5"
                      style={{ backgroundImage: 'var(--gradient-brand)' }}
                      aria-hidden="true"
                    />
                    {catIndex === 0 && i === 0 && (
                      <span
                        className="absolute top-3 right-3 px-3 py-1 text-[0.65rem] uppercase tracking-widest rounded-full"
                        style={{ backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }}
                      >
                        Most Requested
                      </span>
                    )}
                    <div
                      className="w-9 h-9 rounded-full flex items-center justify-center mb-3"
                      style={{ backgroundImage: 'var(--gradient-brand)' }}
                    >
                      <Icon size={16} color="var(--color-ivory)" />
                    </div>
                    <h3 className="font-display text-xl mb-2 pr-2" style={{ color: 'var(--color-ink)' }}>
                      {s.name}
                    </h3>
                    <p className="text-sm mb-4 leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                      {s.description}
                    </p>
                    <div className="flex items-center justify-between">
                      <span
                        className="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full"
                        style={{ backgroundColor: 'var(--color-ivory-dim)', color: 'var(--color-ink)', opacity: 0.75 }}
                      >
                        <Clock size={13} /> {s.duration}
                      </span>
                      <span className="font-display italic text-lg" style={{ color: 'var(--color-magenta)' }}>
                        {s.price}
                      </span>
                    </div>
                    <Link
                      to={`/booking?service=${s.id}`}
                      className="mt-4 flex items-center justify-center gap-1.5 py-2.5 text-xs uppercase tracking-wide rounded transition-transform group-hover:scale-[1.02]"
                      style={{ backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }}
                    >
                      Book This <ArrowRight size={12} />
                    </Link>
                  </div>
                ))}
              </div>
            </section>
          );
        })}
      </div>

      {/* Closing CTA banner */}
      <section
        className="py-20 text-center relative overflow-hidden"
        style={{ backgroundImage: 'var(--gradient-brand)' }}
      >
        <div className="max-w-2xl mx-auto px-5 relative">
          <h2 className="font-display text-3xl md:text-4xl mb-4" style={{ color: 'var(--color-ivory)' }}>
            Not sure what you need?
          </h2>
          <p className="mb-8" style={{ color: 'var(--color-ivory)', opacity: 0.9 }}>
            Call or message us and we'll help you build the right look for your occasion.
          </p>
          <Link
            to="/booking"
            className="inline-flex items-center gap-2 px-8 py-3.5 text-sm uppercase tracking-wide"
            style={{ backgroundColor: 'var(--color-ivory)', color: 'var(--color-deep)' }}
          >
            Book a Consultation <ArrowRight size={15} />
          </Link>
        </div>
      </section>

      <style>{`
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      `}</style>
    </div>
  );
}