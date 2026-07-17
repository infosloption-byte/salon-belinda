import { Link } from 'react-router-dom';
import { ArrowRight, Star } from 'lucide-react';
import CornerFrame from '../components/ui/CornerFrame';
import SectionHeading from '../components/ui/SectionHeading';
import PortfolioImage from '../components/ui/PortfolioImage';
import { LinkButton } from '../components/ui/Button';
import { serviceCategories } from '../data/services';
import { galleryItems } from '../data/gallery';
import { testimonials } from '../data/testimonials';
import { site } from '../data/site';

export default function Home() {
  const featuredServices = serviceCategories.filter((c) =>
    ['bridal', 'hair', 'makeup', 'skin'].includes(c.id)
  );
  const bridalShots = galleryItems.filter((g) => g.category === 'Bridal').slice(0, 4);
  const featuredReview = testimonials[0];

  return (
    <div>
      {/* Hero */}
      <section className="relative overflow-hidden" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div className="max-w-7xl mx-auto px-5 md:px-8 py-20 md:py-28 grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
          <div className="order-2 lg:order-1">
            <p className="eyebrow mb-5" style={{ color: 'var(--color-amber-light)' }}>
              Ratgama · Galle Road · Est. Bridal &amp; Ladies Salon
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
            <p className="mt-6 text-base md:text-lg max-w-md leading-relaxed" style={{ color: 'var(--color-ivory)', opacity: 0.8 }}>
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

          <div className="order-1 lg:order-2 relative">
            <div className="relative aspect-[4/5] max-w-md mx-auto p-4">
              <PortfolioImage
                src="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80"
                alt="Bridal makeup and hair styling at Salon Belinda"
                className="w-full h-full object-cover"
              />
              <CornerFrame />
            </div>
          </div>
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
              I started styling from a small home studio in Ratgama in 2013, and I still take
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
          {featuredServices.map((cat) => (
            <Link
              key={cat.id}
              to="/services"
              className="group block p-7 border transition-colors"
              style={{ borderColor: 'rgba(38,34,32,0.1)' }}
            >
              <p className="eyebrow mb-3" style={{ color: 'var(--color-amber)' }}>
                {cat.title === 'Bridal Packages' ? 'Most Booked' : cat.title}
              </p>
              <h3 className="font-display text-2xl mb-3" style={{ color: 'var(--color-ink)' }}>
                {cat.title}
              </h3>
              <p className="text-sm leading-relaxed mb-5" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
                {cat.intro}
              </p>
              <span
                className="inline-flex items-center gap-1 text-sm group-hover:gap-2 transition-all"
                style={{ color: 'var(--color-magenta)' }}
              >
                See services <ArrowRight size={14} />
              </span>
            </Link>
          ))}
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
