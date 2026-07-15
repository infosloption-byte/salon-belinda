import { useLocation, Navigate } from 'react-router-dom';
import { CheckCircle2, Truck, Store } from 'lucide-react';
import { formatLKR, site } from '../data/site';
import { LinkButton } from '../components/ui/Button';

interface ConfirmationState {
  orderNumber: string;
  total: number;
  method: 'delivery' | 'pickup';
  details: { fullName: string; phone: string; address: string; city: string };
}

export default function OrderConfirmation() {
  const location = useLocation();
  const state = location.state as ConfirmationState | undefined;

  if (!state) return <Navigate to="/" replace />;

  return (
    <div className="max-w-xl mx-auto px-5 py-24 md:py-32 text-center">
      <CheckCircle2 size={48} style={{ color: 'var(--color-gold)' }} className="mx-auto mb-6" />
      <p className="eyebrow mb-3" style={{ color: 'var(--color-gold)' }}>Order {state.orderNumber}</p>
      <h1 className="font-display text-3xl md:text-4xl mb-4" style={{ color: 'var(--color-ink)' }}>
        Thank You, {state.details.fullName.split(' ')[0] || 'there'}!
      </h1>
      <p className="text-sm leading-relaxed mb-8" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
        We've received your order and will call {state.details.phone} shortly to confirm{' '}
        {state.method === 'pickup' ? 'a pickup time' : 'delivery details'}.
      </p>

      <div className="flex items-center justify-center gap-3 p-4 mb-8" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
        {state.method === 'pickup' ? (
          <Store size={18} style={{ color: 'var(--color-gold)' }} />
        ) : (
          <Truck size={18} style={{ color: 'var(--color-gold)' }} />
        )}
        <span className="text-sm" style={{ color: 'var(--color-ink)' }}>
          {state.method === 'pickup' ? 'Pickup at Salon Belinda, Ratgama' : `Delivery to ${state.details.address}, ${state.details.city}`}
        </span>
      </div>

      <p className="font-display italic text-2xl mb-10" style={{ color: 'var(--color-maroon)' }}>
        Total: {formatLKR(state.total)}
      </p>

      <div className="flex flex-wrap gap-4 justify-center">
        <LinkButton to="/products" variant="outline">Continue Shopping</LinkButton>
      </div>

      <p className="text-xs mt-10" style={{ color: 'var(--color-ink)', opacity: 0.5 }}>
        Questions about your order? Call {site.phone} or email {site.email}.
      </p>
    </div>
  );
}
