import { Link } from 'react-router-dom';
import { MapPin, Mail, Phone } from 'lucide-react';
import { site } from '../../data/site';

function FacebookIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z" />
    </svg>
  );
}

function InstagramIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" aria-hidden="true">
      <rect x="3" y="3" width="18" height="18" rx="5" />
      <circle cx="12" cy="12" r="4" />
      <circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none" />
    </svg>
  );
}

export default function Footer() {
  return (
    <footer style={{ backgroundColor: 'var(--color-deep)', color: 'var(--color-ivory)' }}>
      <div className="max-w-7xl mx-auto px-5 md:px-8 py-16 grid grid-cols-1 md:grid-cols-4 gap-12">
        <div>
          <img src="/brand/wordmark.png" alt="Salon Belinda" className="h-16 w-auto object-contain mb-4" />
          <p className="text-sm leading-relaxed text-[var(--color-ivory)]/70">
            Bridal artistry and ladies' salon services on Havelock Rd, Galle — led by Shanika
            Madushani.
          </p>
          <div className="flex gap-4 mt-5">
            <a href={site.social.facebook} aria-label="Facebook" className="opacity-80 hover:opacity-100">
              <FacebookIcon />
            </a>
            <a href={site.social.instagram} aria-label="Instagram" className="opacity-80 hover:opacity-100">
              <InstagramIcon />
            </a>
          </div>
        </div>

        <div>
          <p className="eyebrow mb-4 text-[var(--color-amber-light)]">Explore</p>
          <ul className="space-y-2 text-sm text-[var(--color-ivory)]/85">
            <li><Link to="/about" className="hover:text-[var(--color-amber-light)]">About</Link></li>
            <li><Link to="/services" className="hover:text-[var(--color-amber-light)]">Services</Link></li>
            <li><Link to="/gallery" className="hover:text-[var(--color-amber-light)]">Gallery</Link></li>
            <li><Link to="/reviews" className="hover:text-[var(--color-amber-light)]">Reviews</Link></li>
            <li><Link to="/shop" className="hover:text-[var(--color-amber-light)]">Shop</Link></li>
            <li><Link to="/booking" className="hover:text-[var(--color-amber-light)]">Book Appointment</Link></li>
          </ul>
        </div>

        <div>
          <p className="eyebrow mb-4 text-[var(--color-amber-light)]">Visit</p>
          <ul className="space-y-3 text-sm text-[var(--color-ivory)]/85">
            <li className="flex gap-2"><MapPin size={16} className="shrink-0 mt-0.5" /> {site.address}</li>
            <li className="flex gap-2"><Phone size={16} className="shrink-0 mt-0.5" /> <a href={site.phoneHref}>{site.phone}</a></li>
            <li className="flex gap-2"><Mail size={16} className="shrink-0 mt-0.5" /> <a href={`mailto:${site.email}`}>{site.email}</a></li>
          </ul>
        </div>

        <div>
          <p className="eyebrow mb-4 text-[var(--color-amber-light)]">Hours</p>
          <ul className="space-y-2 text-sm text-[var(--color-ivory)]/85">
            {site.hours.map((h) => (
              <li key={h.day} className="flex justify-between gap-4">
                <span>{h.day}</span>
                <span className="text-[var(--color-ivory)]/60">{h.time}</span>
              </li>
            ))}
          </ul>
        </div>
      </div>
      <div
        className="border-t border-[var(--color-ivory)]/10 py-5 text-center text-xs text-[var(--color-ivory)]/50"
      >
        © {new Date().getFullYear()} Salon Belinda. All rights reserved.
      </div>
    </footer>
  );
}
