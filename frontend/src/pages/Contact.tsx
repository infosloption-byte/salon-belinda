import { useState } from 'react';
import { MapPin, Mail, Phone, CheckCircle2 } from 'lucide-react';
import SectionHeading from '../components/ui/SectionHeading';
import { Button } from '../components/ui/Button';
import { site } from '../data/site';

export default function Contact() {
  const [sent, setSent] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // Frontend-only for now — once the backend is built, this will POST to
    // /api/contact and the salon will receive the message by email.
    setSent(true);
  };

  return (
    <div>
      <section className="py-20 md:py-24 text-center" style={{ backgroundColor: 'var(--color-green)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-gold-light)' }}>
            Get In Touch
          </p>
          <h1 className="font-display text-4xl md:text-5xl" style={{ color: 'var(--color-ivory)' }}>
            Contact Us
          </h1>
        </div>
      </section>

      <div className="max-w-6xl mx-auto px-5 md:px-8 py-20 grid grid-cols-1 lg:grid-cols-2 gap-14">
        <div>
          <SectionHeading eyebrow="Visit The Salon" title="Find Us" className="mb-8" />
          <ul className="space-y-5 mb-10">
            <li className="flex gap-4">
              <MapPin size={20} color="var(--color-gold)" className="shrink-0" />
              <span style={{ color: 'var(--color-ink)', opacity: 0.75 }}>{site.address}</span>
            </li>
            <li className="flex gap-4">
              <Phone size={20} color="var(--color-gold)" className="shrink-0" />
              <a href={site.phoneHref} style={{ color: 'var(--color-ink)', opacity: 0.75 }}>{site.phone}</a>
            </li>
            <li className="flex gap-4">
              <Mail size={20} color="var(--color-gold)" className="shrink-0" />
              <a href={`mailto:${site.email}`} style={{ color: 'var(--color-ink)', opacity: 0.75 }}>{site.email}</a>
            </li>
          </ul>

          <div className="aspect-video w-full overflow-hidden border" style={{ borderColor: 'rgba(38,34,32,0.1)' }}>
            <iframe
              title="Salon Belinda location map"
              className="w-full h-full"
              style={{ border: 0 }}
              loading="lazy"
              src="https://www.google.com/maps?q=Galle+Road,+Ratgama,+Sri+Lanka&output=embed"
            />
          </div>
        </div>

        <div className="p-8" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
          <h3 className="font-display text-2xl mb-2" style={{ color: 'var(--color-ink)' }}>
            Send a Message
          </h3>
          <p className="text-sm mb-6" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
            Questions about a service, a custom bridal package, or bulk shop orders — reach out here.
          </p>

          {sent ? (
            <div className="flex flex-col items-center text-center py-10">
              <CheckCircle2 size={40} color="var(--color-gold)" className="mb-4" />
              <p style={{ color: 'var(--color-ink)' }}>
                Thank you — we've received your message and will reply soon.
              </p>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              <input required placeholder="Full name" className="contact-input" />
              <input required type="email" placeholder="Email address" className="contact-input" />
              <input placeholder="Phone number (optional)" className="contact-input" />
              <textarea required rows={5} placeholder="Your message" className="contact-input resize-none" />
              <Button type="submit" className="w-full">Send Message</Button>
            </form>
          )}
        </div>
      </div>

      <style>{`
        .contact-input {
          width: 100%;
          padding: 0.7rem 0.9rem;
          border: 1px solid rgba(38,34,32,0.2);
          background: transparent;
          font-size: 0.9rem;
          color: var(--color-ink);
        }
        .contact-input:focus { outline: none; border-color: var(--color-gold); }
      `}</style>
    </div>
  );
}
