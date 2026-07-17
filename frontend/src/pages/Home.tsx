import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, Star, Sparkles, Scissors, Droplet, Palette } from 'lucide-react';
import CornerFrame from '../components/ui/CornerFrame';
import SectionHeading from '../components/ui/SectionHeading';
import PortfolioImage from '../components/ui/PortfolioImage';
import { LinkButton } from '../components/ui/Button';
import { serviceCategories } from '../data/services';
import { galleryItems } from '../data/gallery';
import { testimonials } from '../data/testimonials';
import { site } from '../data/site';

const heroSlides = galleryItems.filter((g) => g.category === 'Bridal').map((g) => g.image);

const categoryIcons: Record<string, React.ComponentType<{ size?: number; color?: string }>> = {
  bridal: Sparkles,
  hair: Scissors,
  skin: Droplet,
  makeup: Palette,
};

export default function Home() {
  const featuredServices = serviceCategories.filter((c) =>
    ['bridal', 'hair', 'makeup', 'skin'].includes(c.id)
  );
  const bridalShots = galleryItems.filter((g) => g.category === 'Bridal').slice(0, 4);
  const featuredReview = testimonials[0];

  const [slide, setSlide] = useState(0);
  useEffect(() => {
    if (heroSlides.length <= 1) return;
    const id = setInterval(() => setSlide((s) => (s + 1) % heroSlides.length), 5000);
    return () => clearInterval(id);
  }, []);

  return (
    <div>
      {/* Hero */}
      <section className="relative overflow-hidden" style={{ backgroundColor: 'var(--color-deep)' }}>
        {/* Background slider */}
        <div className="absolute inset-0" aria-hidden="true">
          {heroSlides.map((src, i) => (
            <div
              key={src}
              className="absolute inset-0 transition-opacity duration-[1500ms] ease-in-out"
              style={{ opacity: i === slide ? 1 : 0 }}
            >
              <img src={src} alt="" className="w-full h-full object-cover" />
            </div>
          ))}
          {/* Dark gradient overlay so the text stays readable over any photo */}
          <div
            className="absolute inset-0"
            style={{
              background:
                'linear-gradient(100deg, rgba(38,20,32,0.94) 0%, rgba(38,20,32,0.86) 38%, rgba(38,20,32,0.55) 65%, rgba(38,20,32,0.35) 100%)',
            }}
          />
        </div>

        <div className="relative max-w-7xl mx-auto px-5 md:px-8 py-24 md:py-36">
          <div className="max-w-xl">
            <p className="eyebrow mb-5" style={{ color: 'var(--color-amber-light)' }}>
              Havelock Rd · Galle · Est. Bridal &amp; Ladies Salon
            </p>
            <h1
              className="font-display text-5xl md:text-6xl lg:text-[4rem] leading-[1.05]"
              style={{ color: 'var(--color-ivory)' }}
            >
              Bridal artistry,
              <br />
              <span className="italic" style={{ color: 'var(--color-amber-light)' }}>
                born by the shore.
              </span>
            </h1>
            <p className="mt-6 text-base md:text-lg max-w-md leading-relaxed" style={{ color: 'var(--color-ivory)', opacity: 0.85 }}>
              Salon Belinda is Shanika Madushani's studio for brides, homecomings, and every
              occasion in between — hair, makeup, skin, and nails, styled with a steady hand.
            </p>
            <div className="mt-9 flex flex-wrap gap-4">
              <LinkButton to="/booking" variant="gradient">
                Book Appointment <ArrowRight size={16} />
              </LinkButton>
              <LinkButton to="/gallery" variant="ghost">
                View Portfolio
              </LinkButton>
            </div>
          </div>

          {/* Slide indicator dots */}
          {heroSlides.length > 1 && (
            <div className="flex gap-2 mt-16">
              {heroSlides.map((_, i) => (
                <button
                  key={i}
                  aria-label={`Show slide ${i + 1}`}
                  onClick={() => setSlide(i)}
                  className="h-1.5 rounded-full transition-all"
                  style={{
                    width: i === slide ? '1.75rem' : '0.5rem',
                    backgroundColor: i === slide ? 'var(--color-amber-light)' : 'rgba(251,247,243,0.35)',
                  }}
                />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Meet the owner */}
      <section className="max-w-7xl mx-auto px-5 md:px-8 py-24 md:py-28">
        <div className="grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr] gap-14 lg:gap-20 items-center">
          <div className="relative max-w-sm mx-auto lg:mx-0">
            <div
              className="absolute -top-5 -left-5 w-full h-full"
              style={{ backgroundImage: 'var(--gradient-brand)' }}
              aria-hidden="true"
            />
            <div className="relative aspect-[4/5]">
              <PortfolioImage
                src="/brand/shani.jpg"
                alt="Shani, founder and lead stylist of Salon Belinda"
                className="w-full h-full object-cover"
              />
            </div>
          </div>

          <div>
            <p className="eyebrow mb-4" style={{ color: 'var(--color-magenta)' }}>
              Meet the Artist
            </p>
            <h2 className="font-display text-4xl md:text-5xl leading-tight mb-2" style={{ color: 'var(--color-ink)' }}>
              Hi, I'm {site.ownerFirstName}.
            </h2>
            <p className="eyebrow mb-7" style={{ color: 'var(--color-ink)', opacity: 0.5, letterSpacing: '0.18em' }}>
              {site.ownerTitle}
            </p>
            <p
              className="font-display italic text-2xl md:text-[1.7rem] leading-snug mb-7"
              style={{ color: 'var(--color-ink)' }}
            >
              "{site.ownerQuote}"
            </p>
            <p className="text-base leading-relaxed max-w-lg" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
              I started styling from a small home studio in Galle in 2013, and I still take
              every bridal trial myself. Salon Belinda grew one wedding, one homecoming, one
              quiet Tuesday blow-dry at a time — and that's still exactly the pace I like it at.
            </p>
            <div className="mt-8 flex flex-wrap gap-4">
              <LinkButton to="/about" variant="outline">
                My Story <ArrowRight size={16} />
              </LinkButton>
            </div>
          </div>
        </div>
      </section>

      {/* Signature services */}
      <section className="max-w-7xl mx-auto px-5 md:px-8 py-24">
        <SectionHeading eyebrow="What We Do" title="Signature Services" align="center" className="mb-14" />
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {featuredServices.map((cat) => {
            const Icon = categoryIcons[cat.id] ?? Sparkles;
            return (
              <Link
                key={cat.id}
                to="/services"
                className="group relative block p-7 pt-8 rounded-lg border overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl"
                style={{ borderColor: 'rgba(38,34,32,0.08)', backgroundColor: 'var(--color-ivory)' }}
              >
                <div
                  className="absolute left-0 right-0 top-0 h-1.5"
                  style={{ backgroundImage: 'var(--gradient-brand)' }}
                  aria-hidden="true"
                />
                <div
                  className="w-10 h-10 rounded-full flex items-center justify-center mb-4"
                  style={{ backgroundImage: 'var(--gradient-brand)' }}
                >
                  <Icon size={18} color="var(--color-ivory)" />
                </div>
                <p className="eyebrow mb-3" style={{ color: 'var(--color-magenta)' }}>
                  {cat.title === 'Bridal Packages' ? 'Most Booked' : cat.title}
                </p>
                <h3 className="font-display text-2xl mb-3" style={{ color: 'var(--color-ink)' }}>
                  {cat.title}
                </h3>
                <p className="text-sm leading-relaxed mb-5" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
                  {cat.intro}
                </p>
                <span
                  className="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs uppercase tracking-wide transition-transform group-hover:scale-[1.03]"
                  style={{ backgroundImage: 'var(--gradient-brand)', color: 'var(--color-ivory)' }}
                >
                  See services <ArrowRight size={13} />
                </span>
              </Link>
            );
          })}
        </div>
      </section>

      {/* Bridal portfolio teaser */}
      <section className="py-24" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
        <div className="max-w-7xl mx-auto px-5 md:px-8">
          <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14">
            <SectionHeading eyebrow="Bridal Portfolio" title="Recent Brides" />
            <Link to="/gallery" className="text-sm underline underline-offset-4" style={{ color: 'var(--color-magenta)' }}>
              View full gallery
            </Link>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
            {bridalShots.map((item) => (
              <div key={item.id} className="relative aspect-[4/5] p-2">
                <PortfolioImage src={item.image} alt={item.title} className="w-full h-full object-cover" />
                <CornerFrame />
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Testimonial teaser */}
      <section className="max-w-4xl mx-auto px-5 md:px-8 py-24 text-center">
        <div className="flex justify-center gap-1 mb-6">
          {Array.from({ length: featuredReview.rating }).map((_, i) => (
            <Star key={i} size={18} fill="var(--color-amber)" stroke="var(--color-amber)" />
          ))}
        </div>
        <p className="font-display italic text-2xl md:text-3xl leading-snug" style={{ color: 'var(--color-ink)' }}>
          "{featuredReview.message}"
        </p>
        <p className="mt-6 eyebrow" style={{ color: 'var(--color-magenta)' }}>
          {featuredReview.name} — {featuredReview.service}
        </p>
        <Link
          to="/reviews"
          className="inline-block mt-8 text-sm underline underline-offset-4"
          style={{ color: 'var(--color-ink)' }}
        >
          Read more reviews
        </Link>
      </section>

      {/* CTA banner */}
      <section className="relative" style={{ backgroundColor: 'var(--color-magenta)' }}>
        <div className="max-w-5xl mx-auto px-5 md:px-8 py-16 text-center">
          <h2 className="font-display text-3xl md:text-4xl mb-4" style={{ color: 'var(--color-ivory)' }}>
            Planning your big day?
          </h2>
          <p className="mb-8" style={{ color: 'var(--color-ivory)', opacity: 0.85 }}>
            Reserve a bridal trial early — {site.name} takes a limited number of weddings each month.
          </p>
          <LinkButton to="/booking" variant="ghost">
            Book Appointment <ArrowRight size={16} />
          </LinkButton>
        </div>
      </section>
    </div>
  );
}
