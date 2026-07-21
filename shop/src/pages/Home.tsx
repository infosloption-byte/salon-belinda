import { Link } from 'react-router-dom';
import { Truck, Store, ShieldCheck } from 'lucide-react';
import { categories } from '../data/products';
import { useProducts } from '../context/ProductsContext';
import { shopSettings, formatCurrency, site } from '../data/site';
import ProductCard from '../components/product/ProductCard';
import { LinkButton } from '../components/ui/Button';

const categoryImages: Record<string, string> = {
  'Hair Care': 'https://images.unsplash.com/photo-1585232004423-be5da21e0d18?auto=format&fit=crop&w=700&q=80',
  'Skin Care': 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=700&q=80',
  Makeup: 'https://images.unsplash.com/photo-1631730359585-38a4935cbec4?auto=format&fit=crop&w=700&q=80',
  'Bridal Accessories': 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=700&q=80',
};

export default function Home() {
  const { products } = useProducts();
  const bestSellers = products.filter((p) => p.bestSeller).slice(0, 4);
  const newArrivals = products.filter((p) => p.isNew).slice(0, 4);

  return (
    <div>
      <section className="py-20 md:py-28 text-center relative" style={{ backgroundColor: 'var(--color-green)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-gold-light)' }}>
            {site.name} Shop
          </p>
          <h1 className="font-display text-4xl md:text-6xl leading-tight" style={{ color: 'var(--color-ivory)' }}>
            Take Home the Products We Use
          </h1>
          <p className="mt-5 text-sm md:text-base" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            The same hair, skin, makeup, and bridal essentials from our treatments — for pickup
            at the salon or delivery island-wide.
          </p>
          <div className="mt-9 flex flex-wrap gap-4 justify-center">
            <LinkButton to="/products" variant="primary">Shop All Products</LinkButton>
            <LinkButton to="/products?category=Bridal Accessories" variant="ghost">Bridal Edit</LinkButton>
          </div>
        </div>
      </section>

      <section className="border-b" style={{ borderColor: 'rgba(38,34,32,0.08)' }}>
        <div className="max-w-7xl mx-auto px-5 md:px-8 py-6 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center sm:text-left">
          <div className="flex items-center gap-3 justify-center sm:justify-start">
            <Truck size={20} style={{ color: 'var(--color-gold)' }} />
            <p className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
              Free delivery over {formatCurrency(shopSettings.freeDeliveryThreshold)}
            </p>
          </div>
          <div className="flex items-center gap-3 justify-center sm:justify-start">
            <Store size={20} style={{ color: 'var(--color-gold)' }} />
            <p className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
              Or pick up in-salon at Galle
            </p>
          </div>
          <div className="flex items-center gap-3 justify-center sm:justify-start">
            <ShieldCheck size={20} style={{ color: 'var(--color-gold)' }} />
            <p className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
              The exact products used in your treatments
            </p>
          </div>
        </div>
      </section>

      <section className="max-w-7xl mx-auto px-5 md:px-8 py-16 md:py-20">
        <p className="eyebrow text-center mb-3" style={{ color: 'var(--color-gold)' }}>Shop by Category</p>
        <h2 className="font-display text-3xl md:text-4xl text-center mb-10" style={{ color: 'var(--color-ink)' }}>
          Find What You Need
        </h2>
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          {categories.map((c) => (
            <Link key={c} to={`/products?category=${encodeURIComponent(c)}`} className="group block">
              <div className="relative aspect-[4/5] overflow-hidden mb-3" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
                <img
                  src={categoryImages[c]}
                  alt={c}
                  className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                  loading="lazy"
                />
                <div className="absolute inset-0" style={{ background: 'linear-gradient(to top, rgba(38,34,32,0.55), transparent 55%)' }} />
                <span
                  className="absolute bottom-3 left-3 font-display text-lg md:text-xl italic"
                  style={{ color: 'var(--color-ivory)' }}
                >
                  {c}
                </span>
              </div>
            </Link>
          ))}
        </div>
      </section>

      {bestSellers.length > 0 && (
        <section className="max-w-7xl mx-auto px-5 md:px-8 py-16 md:py-20 border-t" style={{ borderColor: 'rgba(38,34,32,0.08)' }}>
          <div className="flex items-end justify-between mb-10">
            <div>
              <p className="eyebrow mb-3" style={{ color: 'var(--color-gold)' }}>Customer Favourites</p>
              <h2 className="font-display text-3xl md:text-4xl" style={{ color: 'var(--color-ink)' }}>Best Sellers</h2>
            </div>
            <Link to="/products" className="hidden sm:block text-sm underline" style={{ color: 'var(--color-maroon)' }}>
              View All
            </Link>
          </div>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            {bestSellers.map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        </section>
      )}

      {newArrivals.length > 0 && (
        <section className="max-w-7xl mx-auto px-5 md:px-8 pb-20 md:pb-24">
          <div className="flex items-end justify-between mb-10">
            <div>
              <p className="eyebrow mb-3" style={{ color: 'var(--color-gold)' }}>Just Landed</p>
              <h2 className="font-display text-3xl md:text-4xl" style={{ color: 'var(--color-ink)' }}>New Arrivals</h2>
            </div>
          </div>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            {newArrivals.map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
