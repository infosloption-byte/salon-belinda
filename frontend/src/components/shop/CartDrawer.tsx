import { X, Minus, Plus, Trash2 } from 'lucide-react';
import { useCart } from '../../context/CartContext';
import { Button } from '../ui/Button';

interface CartDrawerProps {
  open: boolean;
  onClose: () => void;
  onCheckout: () => void;
}

const formatLKR = (n: number) => `LKR ${n.toLocaleString('en-LK')}`;

export default function CartDrawer({ open, onClose, onCheckout }: CartDrawerProps) {
  const { lines, remove, setQuantity, total } = useCart();

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
          <h2 className="font-display text-2xl" style={{ color: 'var(--color-ink)' }}>Your Bag</h2>
          <button onClick={onClose} aria-label="Close bag"><X size={20} /></button>
        </div>

        <div className="flex-1 overflow-y-auto px-6 py-5 space-y-6">
          {lines.length === 0 && (
            <p className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
              Your bag is empty. Browse the shop to add products.
            </p>
          )}
          {lines.map((line) => (
            <div key={line.product.id} className="flex gap-4">
              <div className="w-16 h-16 shrink-0 overflow-hidden" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
                <img src={line.product.image} alt={line.product.name} className="w-full h-full object-cover" />
              </div>
              <div className="flex-1">
                <p className="text-sm font-medium" style={{ color: 'var(--color-ink)' }}>{line.product.name}</p>
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
                    className="p-1 border"
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
                {formatLKR(total)}
              </span>
            </div>
            <Button className="w-full" onClick={onCheckout}>Checkout</Button>
          </div>
        )}
      </aside>
    </>
  );
}
