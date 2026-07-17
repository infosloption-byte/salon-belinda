import SectionHeading from '../components/ui/SectionHeading';
import PortfolioImage from '../components/ui/PortfolioImage';
import { LinkButton } from '../components/ui/Button';

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

const milestones = [
  { year: '2013', text: 'Shanika Madushani began styling from a small home studio in Ratgama.' },
  { year: '2017', text: 'Salon Belinda opened its doors on Galle Road as a full ladies\' salon.' },
  { year: '2021', text: 'Bridal packages expanded to include full-day, on-location styling.' },
  { year: '2026', text: 'Salon Belinda online — bookings, gallery, and shop all in one place.' },
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
              src="/brand/shani.jpg"
              alt="Shanika Madushani (Shani), owner of Salon Belinda"
              className="w-full h-full object-cover"
            />
          </div>
        </div>
        <div>
          <p className="eyebrow mb-4" style={{ color: 'var(--color-magenta)' }}>
            Our Story
          </p>
          <h1 className="font-display text-4xl md:text-5xl leading-tight mb-6" style={{ color: 'var(--color-ink)' }}>
            Led by Shanika Madushani
          </h1>
          <p className="text-base leading-relaxed mb-4" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
            Salon Belinda started as a one-chair studio in Ratgama and grew, one wedding at a
            time, into a full bridal and ladies' salon on Galle Road. Shanika still personally
            styles every bridal trial — she believes the calmest bride is the best-prepared one.
          </p>
          <p className="text-base leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
            Alongside bridal work, the team looks after everyday hair, skin, makeup, and nail
            care for clients across the Galle area, treating every appointment — big or small —
            with the same attention.
          </p>
          <div className="mt-9">
            <LinkButton to="/booking">Book a Consultation</LinkButton>
          </div>
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

      <section className="max-w-4xl mx-auto px-5 md:px-8 py-24">
        <SectionHeading eyebrow="Milestones" title="Along the Way" align="center" className="mb-14" />
        <ol className="relative border-l pl-8 space-y-10" style={{ borderColor: 'var(--color-amber)' }}>
          {milestones.map((m) => (
            <li key={m.year} className="relative">
              <span
                className="absolute -left-[2.55rem] top-0 w-3 h-3 rounded-full"
                style={{ backgroundColor: 'var(--color-amber)' }}
              />
              <p className="font-display italic text-xl mb-1" style={{ color: 'var(--color-magenta)' }}>
                {m.year}
              </p>
              <p className="text-sm leading-relaxed" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
                {m.text}
              </p>
            </li>
          ))}
        </ol>
      </section>
    </div>
  );
}
