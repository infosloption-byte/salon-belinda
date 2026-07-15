import { Link } from 'react-router-dom';
import { Minus, Plus, Trash2, ShoppingBag } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { shopSettings, formatLKR } from '../data/site';
import { Button, LinkButton } from '../components/ui/Button';

export default function Cart() {
  const { lines, remove, setQuantity, subtotal } = useCart();

  const deliveryFee = subtotal === 0 || subtotal >= shopSettings.freeDeliveryThreshold ? 0 : shopSettings.deliveryFee;
  const total = subtotal + deliveryFee;

  if (lines.length === 0) {
    return (
      <div className="max-w-xl mx-auto px-5 py-24 md:py-32 text-center">
        <ShoppingBag size={40} className="mx-auto mb-6" style={{ color: 'var(--color-ink)', opacity: 0.25 }} />
        <h1 className="font-display text-3xl mb-4" style={{ color: 'var(--color-ink)' }}>Your Bag is Empty</h1>
        <p className="text-sm mb-9" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
          Browse the shop and add a few things you'll love.
        </p>
        <LinkButton to="/products">Shop All Products</LinkButton>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto px-5 md:px-8 py-12 md:py-16">
      <h1 className="font-display text-3xl md:text-4xl mb-10" style={{ color: 'var(--color-ink)' }}>Your Bag</h1>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div className="lg:col-span-2 space-y-6">
          {lines.map((line) => (
            <div
              key={line.product.id}
              className="flex gap-4 md:gap-5 pb-6 border-b"
              style={{ borderColor: 'rgba(38,34,32,0.1)' }}
            >
              <Link to={`/products/${line.product.slug}`} className="w-20 h-20 md:w-24 md:h-24 shrink-0 overflow-hidden" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
                <img src={line.product.images[0]} alt={line.product.name} className="w-full h-full object-cover" />
              </Link>
              <div className="flex-1 min-w-0">
                <div className="flex justify-between gap-3">
                  <div>
                    <Link to={`/products/${line.product.slug}`}>
                      <p className="text-sm md:text-base font-medium" style={{ color: 'var(--color-ink)' }}>
                        {line.product.name}
                      </p>
                    </Link>
                    <p className="text-xs mt-1" style={{ color: 'var(--color-ink)', opacity: 0.5 }}>
                      {line.product.category}
                    </p>
                  </div>
                  <span className="font-display italic text-base shrink-0" style={{ color: 'var(--color-maroon)' }}>
                    {formatLKR(line.product.price * line.quantity)}
                  </span>
                </div>
                <div className="flex items-center gap-4 mt-3">
                  <div className="flex items-center border" style={{ borderColor: 'rgba(38,34,32,0.2)' }}>
                    <button onClick={() => setQuantity(line.product.id, line.quantity - 1)} className="p-2" aria-label="Decrease quantity">
                      <Minus size={12} />
                    </button>
                    <span className="w-8 text-center text-sm">{line.quantity}</span>
                    <button
                      onClick={() => setQuantity(line.product.id, line.quantity + 1)}
                      className="p-2"
                      aria-label="Increase quantity"
                      disabled={line.quantity >= line.product.stockCount}
                    >
                      <Plus size={12} />
                    </button>
                  </div>
                  <button
                    onClick={() => remove(line.product.id)}
                    className="flex items-center gap-1.5 text-xs"
                    style={{ color: 'var(--color-maroon)' }}
                  >
                    <Trash2 size={13} /> Remove
                  </button>
                </div>
              </div>
            </div>
          ))}
          <LinkButton to="/products" variant="outline">Continue Shopping</LinkButton>
        </div>

        <div>
          <div className="p-6 sticky top-28" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
            <h2 className="font-display text-xl mb-5" style={{ color: 'var(--color-ink)' }}>Order Summary</h2>
            <div className="space-y-3 text-sm mb-5" style={{ color: 'var(--color-ink)' }}>
              <div className="flex justify-between">
                <span style={{ opacity: 0.7 }}>Subtotal</span>
                <span>{formatLKR(subtotal)}</span>
              </div>
              <div className="flex justify-between">
                <span style={{ opacity: 0.7 }}>Delivery</span>
                <span>{deliveryFee === 0 ? 'Free' : formatLKR(deliveryFee)}</span>
              </div>
              {deliveryFee > 0 && (
                <p className="text-xs" style={{ color: 'var(--color-ink)', opacity: 0.5 }}>
                  Free delivery on orders over {formatLKR(shopSettings.freeDeliveryThreshold)}
                </p>
              )}
            </div>
            <div
              className="flex justify-between items-baseline pt-4 mb-6 border-t"
              style={{ borderColor: 'rgba(38,34,32,0.15)' }}
            >
              <span className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>Total</span>
              <span className="font-display italic text-2xl" style={{ color: 'var(--color-maroon)' }}>
                {formatLKR(total)}
              </span>
            </div>
            <Link to="/checkout">
              <Button className="w-full">Proceed to Checkout</Button>
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
