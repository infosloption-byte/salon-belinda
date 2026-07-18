import { Link } from 'react-router-dom';
import { site, mainSiteUrl } from '../../data/site';
import { categories } from '../../data/products';

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
    <footer className="mt-24 border-t" style={{ borderColor: 'rgba(38,34,32,0.1)', backgroundColor: 'var(--color-green)' }}>
      <div className="max-w-7xl mx-auto px-5 md:px-8 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <div>
          <img src="/brand/wordmark.png" alt="Salon Belinda" className="h-14 w-auto object-contain mb-3" />
          <p className="text-sm mt-3 leading-relaxed" style={{ color: 'var(--color-ivory)', opacity: 0.65 }}>
            The hair, skin, makeup, and bridal essentials from our treatments — for pickup at the
            salon or island-wide delivery.
          </p>
          <div className="flex items-center gap-4 mt-5">
            <a href={site.social.facebook} aria-label="Facebook" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
              <FacebookIcon />
            </a>
            <a href={site.social.instagram} aria-label="Instagram" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
              <InstagramIcon />
            </a>
          </div>
        </div>

        <div>
          <p className="eyebrow mb-4" style={{ color: 'var(--color-gold-light)' }}>Shop</p>
          <ul className="space-y-2.5 text-sm" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            {categories.map((c) => (
              <li key={c}>
                <Link to={`/products?category=${encodeURIComponent(c)}`} className="hover:opacity-80">
                  {c}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        <div>
          <p className="eyebrow mb-4" style={{ color: 'var(--color-gold-light)' }}>Support</p>
          <ul className="space-y-2.5 text-sm" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            <li><Link to="/cart" className="hover:opacity-80">Your Bag</Link></li>
            <li><a href={site.phoneHref} className="hover:opacity-80">Call {site.phone}</a></li>
            <li><a href={`mailto:${site.email}`} className="hover:opacity-80">{site.email}</a></li>
          </ul>
        </div>

        <div>
          <p className="eyebrow mb-4" style={{ color: 'var(--color-gold-light)' }}>Salon</p>
          <ul className="space-y-2.5 text-sm" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            <li><a href={mainSiteUrl} className="hover:opacity-80">Main Website</a></li>
            <li><a href={`${mainSiteUrl}/booking`} className="hover:opacity-80">Book an Appointment</a></li>
            <li>{site.address}</li>
          </ul>
        </div>
      </div>
      <div
        className="border-t py-5 text-center text-xs"
        style={{ borderColor: 'rgba(251,246,241,0.12)', color: 'var(--color-ivory)', opacity: 0.5 }}
      >
        © {new Date().getFullYear()} {site.name}. All rights reserved.
      </div>
    </footer>
  );
}
