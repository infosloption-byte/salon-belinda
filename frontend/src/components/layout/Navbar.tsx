import { useEffect, useState } from 'react';
import { Link, NavLink, useLocation } from 'react-router-dom';
import { Menu, X } from 'lucide-react';
import Seal from '../ui/Seal';
import { site } from '../../data/site';

const links = [
  { to: '/', label: 'Home' },
  { to: '/about', label: 'About' },
  { to: '/services', label: 'Services' },
  { to: '/gallery', label: 'Gallery' },
  { to: '/reviews', label: 'Reviews' },
  { to: '/contact', label: 'Contact' },
];

// The shop is a separate app on its own subdomain, so it isn't part of the
// router — it always opens in a new tab. Set VITE_SHOP_URL per environment
// (e.g. http://localhost:5174 locally, https://shop.salonbelinda.com in prod).
const shopUrl = import.meta.env.VITE_SHOP_URL || 'http://localhost:5174';

export default function Navbar() {
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    setOpen(false);
  }, [location.pathname]);

  return (
    <header
      className={`sticky top-0 z-50 transition-shadow duration-300 ${
        scrolled ? 'shadow-[0_1px_0_0_rgba(38,34,32,0.08)]' : ''
      }`}
      style={{ backgroundColor: 'var(--color-ivory)' }}
    >
      <div className="max-w-7xl mx-auto px-5 md:px-8 flex items-center justify-between h-20">
        <Link to="/" className="flex items-center gap-3 group">
          <Seal size={46} />
          <span className="flex flex-col leading-none">
            <span className="font-display italic text-2xl" style={{ color: 'var(--color-deep)' }}>
              Salon Belinda
            </span>
            <span className="eyebrow mt-1" style={{ color: 'var(--color-magenta)', fontSize: '0.6rem' }}>
              Bridal &amp; Ladies Salon
            </span>
          </span>
        </Link>

        <nav className="hidden lg:flex items-center gap-8">
          {links.map((l) => (
            <NavLink
              key={l.to}
              to={l.to}
              end={l.to === '/'}
              className={({ isActive }) =>
                `text-sm tracking-wide transition-colors ${
                  isActive ? 'text-[var(--color-magenta)]' : 'text-[var(--color-ink)]/75 hover:text-[var(--color-magenta)]'
                }`
              }
            >
              {l.label}
            </NavLink>
          ))}
          <a
            href={shopUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="text-sm tracking-wide transition-colors text-[var(--color-ink)]/75 hover:text-[var(--color-magenta)]"
          >
            Shop
          </a>
        </nav>

        <div className="hidden lg:flex items-center gap-4">
          <Link
            to="/booking"
            className="px-6 py-2.5 text-sm tracking-wide uppercase transition-colors border"
            style={{
              backgroundColor: 'var(--color-magenta)',
              color: 'var(--color-ivory)',
              borderColor: 'var(--color-magenta)',
            }}
          >
            Book Appointment
          </Link>
        </div>

        <button
          className="lg:hidden p-2"
          onClick={() => setOpen((v) => !v)}
          aria-label={open ? 'Close menu' : 'Open menu'}
          aria-expanded={open}
        >
          {open ? <X color="var(--color-ink)" /> : <Menu color="var(--color-ink)" />}
        </button>
      </div>

      {open && (
        <div
          className="lg:hidden border-t px-5 pb-6 pt-2 flex flex-col gap-1"
          style={{ borderColor: 'rgba(38,34,32,0.08)', backgroundColor: 'var(--color-ivory)' }}
        >
          {links.map((l) => (
            <NavLink
              key={l.to}
              to={l.to}
              end={l.to === '/'}
              className={({ isActive }) =>
                `py-3 text-base border-b border-[rgba(38,34,32,0.06)] ${
                  isActive ? 'text-[var(--color-magenta)]' : 'text-[var(--color-ink)]/80'
                }`
              }
            >
              {l.label}
            </NavLink>
          ))}
          <a
            href={shopUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="py-3 text-base border-b border-[rgba(38,34,32,0.06)] text-[var(--color-ink)]/80"
          >
            Shop
          </a>
          <Link
            to="/booking"
            className="mt-4 text-center px-6 py-3 text-sm tracking-wide uppercase"
            style={{ backgroundColor: 'var(--color-magenta)', color: 'var(--color-ivory)' }}
          >
            Book Appointment
          </Link>
          <a href={site.phoneHref} className="mt-3 text-center text-sm" style={{ color: 'var(--color-ink)' }}>
            Call {site.phone}
          </a>
        </div>
      )}
    </header>
  );
}
