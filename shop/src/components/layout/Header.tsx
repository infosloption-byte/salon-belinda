import { useEffect, useState } from 'react';
import { Link, NavLink, useLocation, useNavigate } from 'react-router-dom';
import { Menu, X, Search, ShoppingBag, ArrowLeft, Phone, Mail } from 'lucide-react';
import { categories } from '../../data/products';
import { site, mainSiteUrl } from '../../data/site';
import { useCart } from '../../context/CartContext';

export default function Header({ onCartClick }: { onCartClick: () => void }) {
  const [open, setOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchValue, setSearchValue] = useState('');
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();
  const { count } = useCart();

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    setOpen(false);
    setSearchOpen(false);
  }, [location.pathname, location.search]);

  const submitSearch = (e: React.FormEvent) => {
    e.preventDefault();
    navigate(`/products${searchValue ? `?q=${encodeURIComponent(searchValue)}` : ''}`);
    setSearchOpen(false);
    setSearchValue('');
  };

  return (
    <header
      className={`sticky top-0 z-50 transition-shadow duration-300 ${
        scrolled ? 'shadow-[0_1px_0_0_rgba(38,34,32,0.08)]' : ''
      }`}
    >
      <div className="hidden md:block" style={{ backgroundImage: 'var(--gradient-brand)' }}>
        <div className="max-w-7xl mx-auto px-5 md:px-8 h-9 flex items-center justify-between text-[0.68rem] tracking-wide" style={{ color: 'var(--color-ivory)' }}>
          <a href={mainSiteUrl} className="flex items-center gap-1.5 uppercase hover:opacity-80">
            <ArrowLeft size={11} /> Back to main site
          </a>
          <div className="flex items-center gap-5 opacity-95">
            <a href={site.phoneHref} className="flex items-center gap-1.5 hover:opacity-80">
              <Phone size={11} /> Hotline: {site.phone}
            </a>
            <a href={`mailto:${site.email}`} className="flex items-center gap-1.5 hover:opacity-80">
              <Mail size={11} /> {site.email}
            </a>
          </div>
        </div>
      </div>

      <div style={{ backgroundColor: 'var(--color-ivory)' }}>
        <div className="max-w-7xl mx-auto px-5 md:px-8 flex items-center justify-between h-20 gap-4">
        <Link to="/" className="flex items-center shrink-0">
          <img src="/brand/wordmark.png" alt="Salon Belinda" className="h-16 md:h-20 w-auto object-contain" />
        </Link>

        <nav className="hidden lg:flex items-center gap-7">
          <NavLink
            to="/products"
            end
            className={({ isActive }) =>
              `text-sm tracking-wide transition-colors ${
                isActive ? 'text-[var(--color-maroon)]' : 'text-[var(--color-ink)]/75 hover:text-[var(--color-maroon)]'
              }`
            }
          >
            Shop All
          </NavLink>
          {categories.map((c) => (
            <NavLink
              key={c}
              to={`/products?category=${encodeURIComponent(c)}`}
              className="text-sm tracking-wide transition-colors text-[var(--color-ink)]/75 hover:text-[var(--color-maroon)]"
            >
              {c}
            </NavLink>
          ))}
        </nav>

        <div className="flex items-center gap-2 md:gap-4 shrink-0">
          <button
            onClick={() => setSearchOpen((v) => !v)}
            aria-label="Search products"
            aria-expanded={searchOpen}
            className="p-2 hover:text-[var(--color-maroon)] transition-colors"
            style={{ color: 'var(--color-ink)' }}
          >
            <Search size={19} />
          </button>
          <button
            onClick={onCartClick}
            aria-label={`Open bag, ${count} item${count !== 1 ? 's' : ''}`}
            className="relative p-2 hover:text-[var(--color-maroon)] transition-colors"
            style={{ color: 'var(--color-ink)' }}
          >
            <ShoppingBag size={20} />
            {count > 0 && (
              <span
                className="absolute -top-0.5 -right-0.5 flex items-center justify-center w-4 h-4 text-[0.6rem] rounded-full"
                style={{ backgroundColor: 'var(--color-maroon)', color: 'var(--color-ivory)' }}
              >
                {count > 9 ? '9+' : count}
              </span>
            )}
          </button>
          <button
            className="lg:hidden p-2"
            onClick={() => setOpen((v) => !v)}
            aria-label={open ? 'Close menu' : 'Open menu'}
            aria-expanded={open}
          >
            {open ? <X color="var(--color-ink)" /> : <Menu color="var(--color-ink)" />}
          </button>
        </div>
      </div>
      </div>

      {searchOpen && (
        <div className="border-t px-5 md:px-8 py-4" style={{ borderColor: 'rgba(38,34,32,0.08)' }}>
          <form onSubmit={submitSearch} className="max-w-2xl mx-auto flex gap-2">
            <input
              autoFocus
              value={searchValue}
              onChange={(e) => setSearchValue(e.target.value)}
              placeholder="Search products…"
              className="field-input"
            />
            <button
              type="submit"
              className="px-5 py-2 text-sm uppercase tracking-wide shrink-0"
              style={{ backgroundColor: 'var(--color-maroon)', color: 'var(--color-ivory)' }}
            >
              Search
            </button>
          </form>
        </div>
      )}

      {open && (
        <div
          className="lg:hidden border-t px-5 pb-6 pt-2 flex flex-col gap-1"
          style={{ borderColor: 'rgba(38,34,32,0.08)', backgroundColor: 'var(--color-ivory)' }}
        >
          <NavLink
            to="/products"
            end
            onClick={() => setOpen(false)}
            className={({ isActive }) =>
              `py-3 text-base border-b border-[rgba(38,34,32,0.06)] ${
                isActive ? 'text-[var(--color-maroon)]' : 'text-[var(--color-ink)]/80'
              }`
            }
          >
            Shop All
          </NavLink>
          {categories.map((c) => (
            <NavLink
              key={c}
              to={`/products?category=${encodeURIComponent(c)}`}
              onClick={() => setOpen(false)}
              className="py-3 text-base border-b border-[rgba(38,34,32,0.06)] text-[var(--color-ink)]/80"
            >
              {c}
            </NavLink>
          ))}
          <a
            href={mainSiteUrl}
            onClick={() => setOpen(false)}
            className="mt-4 flex items-center gap-1.5 text-sm"
            style={{ color: 'var(--color-ink)', opacity: 0.6 }}
          >
            <ArrowLeft size={14} /> Back to main site
          </a>
        </div>
      )}
    </header>
  );
}