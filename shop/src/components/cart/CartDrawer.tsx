import { Link } from 'react-router-dom';
import { X, Minus, Plus, Trash2, ShoppingBag } from 'lucide-react';
import { useCart } from '../../context/CartContext';
import { formatLKR } from '../../data/site';
import { Button } from '../ui/Button';

export default function CartDrawer({ open, onClose }: { open: boolean; onClose: () => void }) {
  const { lines, remove, setQuantity, subtotal } = useCart();

  return (
    <>
      <div
        className={`fixed inset-0 z-[90] transition-opacity ${open ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'}`}
        style={{ backgroundColor: 'rgba(38,34,32,0.5)' }}
        onClick={onClose}
      />
      <aside
        className={`fixed top-0 right-0 h-full w-full max-w-sm z-[91] flex flex-col transition-transform duration-300 ${
          open ? 'translate-x-0' : 'translate-x-full'
        }`}
        style={{ backgroundColor: 'var(--color-ivory)' }}
        aria-hidden={!open}
      >
        <div className="flex items-center justify-between px-6 py-5 border-b" style={{ borderColor: 'rgba(38,34,32,0.1)' }}>
          <h2 className="font-display text-2xl" style={{ color: 'var(--color-ink)' }}>
            Your Bag {lines.length > 0 && `(${lines.reduce((n, l) => n + l.quantity, 0)})`}
          </h2>
          <button onClick={onClose} aria-label="Close bag"><X size={20} /></button>
        </div>

        <div className="flex-1 overflow-y-auto px-6 py-5 space-y-6">
          {lines.length === 0 && (
            <div className="text-center py-16">
              <ShoppingBag size={32} className="mx-auto mb-4" style={{ color: 'var(--color-ink)', opacity: 0.25 }} />
              <p className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                Your bag is empty. Browse the shop to add products.
              </p>
              <Button variant="outline" className="mt-6" onClick={onClose}>
                Continue Shopping
              </Button>
            </div>
          )}
          {lines.map((line) => (
            <div key={line.product.id} className="flex gap-4">
              <Link to={`/products/${line.product.slug}`} onClick={onClose} className="w-16 h-16 shrink-0 overflow-hidden" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
                <img src={line.product.images[0]} alt={line.product.name} className="w-full h-full object-cover" />
              </Link>
              <div className="flex-1">
                <Link to={`/products/${line.product.slug}`} onClick={onClose}>
                  <p className="text-sm font-medium" style={{ color: 'var(--color-ink)' }}>{line.product.name}</p>
                </Link>
                <p className="text-xs mt-0.5" style={{ color: 'var(--color-ink)', opacity: 0.5 }}>
                  {formatLKR(line.product.price)}
                </p>
                <div className="flex items-center gap-3 mt-2">
                  <button
                    onClick={() => setQuantity(line.product.id, line.quantity - 1)}
                    aria-label="Decrease quantity"
                    className="p-1 border"
                    style={{ borderColor: 'rgba(38,34,32,0.2)' }}
                  >
                    <Minus size={12} />
                  </button>
                  <span className="text-sm w-4 text-center">{line.quantity}</span>
                  <button
                    onClick={() => setQuantity(line.product.id, line.quantity + 1)}
                    aria-label="Increase quantity"
                    disabled={line.quantity >= line.product.stockCount}
                    className="p-1 border disabled:opacity-30"
                    style={{ borderColor: 'rgba(38,34,32,0.2)' }}
                  >
                    <Plus size={12} />
                  </button>
                  <button
                    onClick={() => remove(line.product.id)}
                    aria-label={`Remove ${line.product.name}`}
                    className="ml-auto p-1"
                    style={{ color: 'var(--color-maroon)' }}
                  >
                    <Trash2 size={14} />
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        {lines.length > 0 && (
          <div className="px-6 py-5 border-t" style={{ borderColor: 'rgba(38,34,32,0.1)' }}>
            <div className="flex justify-between mb-4 text-sm">
              <span style={{ color: 'var(--color-ink)', opacity: 0.7 }}>Subtotal</span>
              <span className="font-display text-lg" style={{ color: 'var(--color-maroon)' }}>
                {formatLKR(subtotal)}
              </span>
            </div>
            <Link to="/cart" onClick={onClose}>
              <Button className="w-full">View Bag &amp; Checkout</Button>
            </Link>
          </div>
        )}
      </aside>
    </>
  );
}
