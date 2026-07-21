import SectionHeading from '../components/ui/SectionHeading';
import PortfolioImage from '../components/ui/PortfolioImage';
import { LinkButton } from '../components/ui/Button';
import { Sparkles, Heart, ShieldCheck, Users } from 'lucide-react';
import { site } from '../data/site';

const stats = [
  { value: '12+', label: 'Years Styling' },
  { value: '400+', label: 'Brides Styled' },
  { value: '2,000+', label: 'Happy Clients' },
  { value: '4.9★', label: 'Average Rating' },
];

const whyUs = [
  {
    icon: Sparkles,
    title: 'Trial-first bridal process',
    text: "Every bridal booking starts with a trial, so your wedding-day look is never a surprise — it's confirmed and photographed weeks ahead.",
  },
  {
    icon: Heart,
    title: 'One stylist, start to finish',
    text: `${site.ownerFirstName} personally oversees every bridal booking from consultation to the final pin, so nothing is handed off along the way.`,
  },
  {
    icon: ShieldCheck,
    title: 'Safe, tested products',
    text: 'Ammonia-friendly colour and dermatologically tested skincare are used across every service — bridal or everyday.',
  },
  {
    icon: Users,
    title: 'Built for the whole bridal party',
    text: 'Bridesmaids, mothers, and family are styled alongside the bride, so everyone in the photos looks and feels their best.',
  },
];

const values = [
  {
    title: 'Every bride, unhurried',
    text: 'Bridal days are built around real timelines, with trials scheduled early so nothing is decided in a rush.',
  },
  {
    title: 'Products that respect your skin',
    text: 'We use ammonia-friendly colour and dermatologically tested skincare for every treatment, bridal or otherwise.',
  },
  {
    title: 'Tradition and modern, side by side',
    text: 'From Kandyan draping to contemporary soft-glam, we style for the look you actually want — not a template.',
  },
];

const process = [
  { step: '01', title: 'Consultation', text: 'Tell us your date, venue, and vision. We recommend a package and timeline that fits.' },
  { step: '02', title: 'Trial Session', text: 'A full rehearsal of your look — hair, makeup, and draping — photographed so you can compare options.' },
  { step: '03', title: 'Confirmation', text: 'Once you approve the trial, we lock in your date, timing, and any final adjustments.' },
  { step: '04', title: 'The Big Day', text: `${site.ownerFirstName} and the team arrive on time and styled-ready, so all you have to do is enjoy it.` },
];

export default function About() {
  return (
    <div>
      <section className="max-w-7xl mx-auto px-5 md:px-8 py-20 md:py-28 grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
        <div className="relative max-w-sm mx-auto lg:mx-0">
          <div
            className="absolute -top-5 -left-5 w-full h-full"
            style={{ backgroundImage: 'var(--gradient-brand)' }}
            aria-hidden="true"
          />
          <div className="relative aspect-[4/5]">
            <PortfolioImage
              src="/brand/owner.jpg"
              alt={`${site.owner}, owner of ${site.name}`}
              className="w-full h-full object-cover"
            />
          </div>
        </div>
        <div>
          <p className="eyebrow mb-4" style={{ color: 'var(--color-magenta)' }}>
            Our Story
          </p>
          <h1 className="font-display text-4xl md:text-5xl leading-tight mb-6" style={{ color: 'var(--color-ink)' }}>
            Led by {site.owner}
          </h1>
          <p className="text-base leading-relaxed mb-4" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
            {site.name} started as a one-chair studio and grew, one wedding at a
            time, into a full bridal and ladies' salon. {site.ownerFirstName} still personally
            styles every bridal trial — {site.ownerFirstName.toLowerCase()} believes the calmest bride is the best-prepared one.
          </p>
          <p className="text-base leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
            Alongside bridal work, the team looks after everyday hair, skin, makeup, and nail
            care for clients across the area, treating every appointment — big or small —
            with the same attention.
          </p>
          <div className="mt-9">
            <LinkButton to="/booking">Book a Consultation</LinkButton>
          </div>
        </div>
      </section>

      <section className="py-14" style={{ backgroundImage: 'var(--gradient-brand)' }}>
        <div className="max-w-5xl mx-auto px-5 md:px-8 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          {stats.map((s) => (
            <div key={s.label}>
              <p className="font-display text-4xl md:text-5xl" style={{ color: 'var(--color-ivory)' }}>
                {s.value}
              </p>
              <p className="eyebrow mt-2" style={{ color: 'var(--color-ivory)', opacity: 0.85 }}>
                {s.label}
              </p>
            </div>
          ))}
        </div>
      </section>

      <section className="py-24" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
        <div className="max-w-7xl mx-auto px-5 md:px-8">
          <SectionHeading eyebrow="What Guides Us" title="Our Values" align="center" className="mb-14" />
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {values.map((v) => (
              <div key={v.title} className="text-center px-4">
                <h3 className="font-display text-2xl mb-3" style={{ color: 'var(--color-ink)' }}>
                  {v.title}
                </h3>
                <p className="text-sm leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
                  {v.text}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24">
        <div className="max-w-6xl mx-auto px-5 md:px-8">
          <SectionHeading eyebrow="Why Choose Us" title="What Sets Us Apart" align="center" className="mb-14" />
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
            {whyUs.map((w) => (
              <div key={w.title} className="flex gap-4 p-6 rounded-lg" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
                <div
                  className="w-11 h-11 rounded-full flex items-center justify-center shrink-0"
                  style={{ backgroundImage: 'var(--gradient-brand)' }}
                >
                  <w.icon size={18} color="var(--color-ivory)" />
                </div>
                <div>
                  <h3 className="font-display text-xl mb-1.5" style={{ color: 'var(--color-ink)' }}>
                    {w.title}
                  </h3>
                  <p className="text-sm leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
                    {w.text}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24 relative overflow-hidden" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div
          className="absolute -bottom-32 -left-32 w-96 h-96 rounded-full opacity-20 blur-3xl"
          style={{ backgroundImage: 'var(--gradient-brand)' }}
          aria-hidden="true"
        />
        <div className="max-w-3xl mx-auto px-5 text-center relative">
          <p className="eyebrow mb-6" style={{ color: 'var(--color-amber-light)' }}>
            Our Philosophy
          </p>
          <p
            className="font-display italic text-2xl md:text-3xl leading-snug"
            style={{ color: 'var(--color-ivory)' }}
          >
            "{site.ownerQuote}"
          </p>
          <p className="mt-6 text-sm" style={{ color: 'var(--color-ivory)', opacity: 0.6 }}>
            — {site.ownerFirstName}, {site.ownerTitle}
          </p>
        </div>
      </section>

      <section className="max-w-6xl mx-auto px-5 md:px-8 py-24">
        <SectionHeading eyebrow="How It Works" title="Your Bridal Journey" align="center" className="mb-14" />
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {process.map((p) => (
            <div key={p.step} className="relative text-center px-4">
              <p
                className="font-display text-5xl mb-4 text-gradient-brand"
                style={{ opacity: 0.9 }}
              >
                {p.step}
              </p>
              <h3 className="font-display text-xl mb-2" style={{ color: 'var(--color-ink)' }}>
                {p.title}
              </h3>
              <p className="text-sm leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
                {p.text}
              </p>
            </div>
          ))}
        </div>
      </section>

      <section className="py-20 text-center" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div className="max-w-2xl mx-auto px-5">
          <h2 className="font-display text-3xl md:text-4xl mb-4" style={{ color: 'var(--color-ivory)' }}>
            Ready to meet the team?
          </h2>
          <p className="mb-8" style={{ color: 'var(--color-ivory)', opacity: 0.8 }}>
            Come by the salon in Galle, or book a consultation and we'll take it from there.
          </p>
          <LinkButton to="/booking">Book Your Visit</LinkButton>
        </div>
      </section>
    </div>
  );
}
