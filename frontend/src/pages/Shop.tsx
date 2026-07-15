import { useState } from 'react';
import { ShoppingBag, CheckCircle2 } from 'lucide-react';
import SectionHeading from '../components/ui/SectionHeading';
import { Button } from '../components/ui/Button';
import CartDrawer from '../components/shop/CartDrawer';
import { useCart } from '../context/CartContext';
import { products, type Product } from '../data/products';

type Filter = 'All' | Product['category'];
const categories: Product['category'][] = ['Hair Care', 'Skin Care', 'Makeup', 'Bridal Accessories'];
const formatLKR = (n: number) => `LKR ${n.toLocaleString('en-LK')}`;

export default function Shop() {
  const [filter, setFilter] = useState<Filter>('All');
  const [cartOpen, setCartOpen] = useState(false);
  const [checkingOut, setCheckingOut] = useState(false);
  const [orderPlaced, setOrderPlaced] = useState(false);
  const { add, count, lines, total, clear } = useCart();

  const filtered = filter === 'All' ? products : products.filter((p) => p.category === filter);

  const handlePlaceOrder = (e: React.FormEvent) => {
    e.preventDefault();
    // Frontend-only for now — once the backend is built, this will POST an
    // order to /api/orders so it appears in the shop's admin dashboard.
    setOrderPlaced(true);
    clear();
  };

  if (orderPlaced) {
    return (
      <div className="max-w-xl mx-auto px-5 py-28 text-center">
        <CheckCircle2 size={48} color="var(--color-gold)" className="mx-auto mb-6" />
        <h1 className="font-display text-3xl mb-4" style={{ color: 'var(--color-ink)' }}>
          Order Placed
        </h1>
        <p className="text-sm leading-relaxed mb-9" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
          Thank you for your order. We'll call to confirm delivery or in-salon pickup details.
        </p>
        <Button variant="outline" onClick={() => setOrderPlaced(false)}>
          Continue Shopping
        </Button>
      </div>
    );
  }

  return (
    <div>
      <section className="py-20 md:py-24 text-center relative" style={{ backgroundColor: 'var(--color-green)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-gold-light)' }}>
            Salon Belinda Shop
          </p>
          <h1 className="font-display text-4xl md:text-5xl" style={{ color: 'var(--color-ivory)' }}>
            Take Home the Products We Use
          </h1>
          <p className="mt-5" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            The same hair, skin, makeup, and bridal essentials from our treatments — for pickup
            at the salon or delivery.
          </p>
        </div>
        <button
          onClick={() => setCartOpen(true)}
          className="absolute top-8 right-6 md:right-10 flex items-center gap-2 px-4 py-2 border"
          style={{ borderColor: 'var(--color-ivory)', color: 'var(--color-ivory)' }}
        >
          <ShoppingBag size={16} /> {count}
        </button>
      </section>

      <div className="max-w-7xl mx-auto px-5 md:px-8 py-16">
        <div className="flex flex-wrap gap-3 justify-center mb-12">
          {(['All', ...categories] as Filter[]).map((c) => (
            <button
              key={c}
              onClick={() => setFilter(c)}
              className="px-5 py-2 text-xs uppercase tracking-wide border transition-colors"
              style={
                filter === c
                  ? { backgroundColor: 'var(--color-maroon)', color: 'var(--color-ivory)', borderColor: 'var(--color-maroon)' }
                  : { borderColor: 'rgba(38,34,32,0.2)', color: 'var(--color-ink)' }
              }
            >
              {c}
            </button>
          ))}
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {filtered.map((p) => (
            <div key={p.id} className="group">
              <div className="relative aspect-square mb-4 overflow-hidden" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
                <img
                  src={p.image}
                  alt={p.name}
                  className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                  loading="lazy"
                />
                {!p.inStock && (
                  <span
                    className="absolute top-3 left-3 text-[0.65rem] uppercase tracking-wide px-2 py-1"
                    style={{ backgroundColor: 'var(--color-ivory)', color: 'var(--color-ink)' }}
                  >
                    Out of stock
                  </span>
                )}
              </div>
              <p className="eyebrow mb-1" style={{ color: 'var(--color-gold)', fontSize: '0.6rem' }}>
                {p.category}
              </p>
              <h3 className="font-display text-lg mb-1" style={{ color: 'var(--color-ink)' }}>
                {p.name}
              </h3>
              <p className="text-xs leading-relaxed mb-3" style={{ color: 'var(--color-ink)', opacity: 0.55 }}>
                {p.description}
              </p>
              <div className="flex items-center justify-between">
                <span className="font-display italic text-base" style={{ color: 'var(--color-maroon)' }}>
                  {formatLKR(p.price)}
                </span>
                <button
                  disabled={!p.inStock}
                  onClick={() => add(p)}
                  className="text-xs uppercase tracking-wide px-3 py-2 border disabled:opacity-30"
                  style={{ borderColor: 'rgba(38,34,32,0.2)', color: 'var(--color-ink)' }}
                >
                  Add to Bag
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>

      <CartDrawer
        open={cartOpen}
        onClose={() => setCartOpen(false)}
        onCheckout={() => {
          setCartOpen(false);
          setCheckingOut(true);
        }}
      />

      {checkingOut && (
        <div
          className="fixed inset-0 z-[95] flex items-center justify-center px-4"
          style={{ backgroundColor: 'rgba(38,34,32,0.6)' }}
          onClick={() => setCheckingOut(false)}
        >
          <div
            className="w-full max-w-md p-8"
            style={{ backgroundColor: 'var(--color-ivory)' }}
            onClick={(e) => e.stopPropagation()}
          >
            <SectionHeading eyebrow="Almost Done" title="Delivery Details" className="mb-6" />
            <form onSubmit={handlePlaceOrder} className="space-y-4">
              <input required placeholder="Full name" className="checkout-input" />
              <input required placeholder="Phone number" className="checkout-input" />
              <input placeholder="Delivery address (or write 'Pickup')" className="checkout-input" />
              <div className="flex justify-between items-center pt-2">
                <span className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
                  {lines.length} item{lines.length !== 1 ? 's' : ''}
                </span>
                <span className="font-display italic text-lg" style={{ color: 'var(--color-maroon)' }}>
                  {formatLKR(total)}
                </span>
              </div>
              <Button type="submit" className="w-full">Place Order</Button>
            </form>
          </div>
          <style>{`
            .checkout-input {
              width: 100%;
              padding: 0.65rem 0.85rem;
              border: 1px solid rgba(38,34,32,0.2);
              background: transparent;
              font-size: 0.9rem;
              color: var(--color-ink);
            }
            .checkout-input:focus { outline: none; border-color: var(--color-gold); }
          `}</style>
        </div>
      )}
    </div>
  );
}
