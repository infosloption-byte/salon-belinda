import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Check, Truck, Store, Banknote, Landmark, CreditCard } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { shopSettings, formatCurrency, site } from '../data/site';
import { createOrder } from '../lib/api';
import { Button } from '../components/ui/Button';

type Step = 'details' | 'payment' | 'review';
type FulfilmentMethod = 'delivery' | 'pickup';
type PaymentMethod = 'cod' | 'bank' | 'card';

interface ShippingDetails {
  fullName: string;
  phone: string;
  email: string;
  address: string;
  city: string;
  notes: string;
}

const steps: { key: Step; label: string }[] = [
  { key: 'details', label: 'Delivery' },
  { key: 'payment', label: 'Payment' },
  { key: 'review', label: 'Review' },
];

export default function Checkout() {
  const { lines, subtotal, clear } = useCart();
  const navigate = useNavigate();

  const [step, setStep] = useState<Step>('details');
  const [method, setMethod] = useState<FulfilmentMethod>('delivery');
  const [payment, setPayment] = useState<PaymentMethod>('cod');
  const [details, setDetails] = useState<ShippingDetails>({
    fullName: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    notes: '',
  });
  const [placing, setPlacing] = useState(false);
  const [placeError, setPlaceError] = useState('');

  const deliveryFee =
    method === 'pickup' || subtotal >= shopSettings.freeDeliveryThreshold ? 0 : shopSettings.deliveryFee;
  const total = subtotal + deliveryFee;

  if (lines.length === 0) {
    navigate('/cart', { replace: true });
    return null;
  }

  const stepIndex = steps.findIndex((s) => s.key === step);

  const handleDetailsSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setStep('payment');
  };

  const handlePlaceOrder = async () => {
    setPlacing(true);
    setPlaceError('');

    try {
      const order = await createOrder({
        lines: lines.map((l) => ({ productId: Number(l.product.id), quantity: l.quantity })),
        fulfilment: method,
        payment,
        customer: {
          fullName: details.fullName,
          phone: details.phone,
          email: details.email,
          address: method === 'delivery' ? details.address : undefined,
          city: method === 'delivery' ? details.city : undefined,
          notes: details.notes || undefined,
        },
      });

      clear();
      navigate('/order-confirmation', {
        state: {
          orderNumber: order.orderNumber,
          total: order.total,
          method,
          payment,
          paymentStatus: order.paymentStatus,
          transactionId: order.transactionId,
          details,
        },
      });
    } catch (err) {
      setPlaceError(err instanceof Error ? err.message : 'Could not place your order. Please try again.');
      setPlacing(false);
    }
  };

  return (
    <div className="max-w-6xl mx-auto px-5 md:px-8 py-12 md:py-16">
      <h1 className="font-display text-3xl md:text-4xl mb-8" style={{ color: 'var(--color-ink)' }}>Checkout</h1>

      <div className="flex items-center gap-2 mb-10 md:mb-14 overflow-x-auto">
        {steps.map((s, i) => (
          <div key={s.key} className="flex items-center gap-2 shrink-0">
            <div
              className="w-7 h-7 rounded-full flex items-center justify-center text-xs shrink-0"
              style={
                i <= stepIndex
                  ? { backgroundColor: 'var(--color-maroon)', color: 'var(--color-ivory)' }
                  : { backgroundColor: 'var(--color-ivory-dim)', color: 'var(--color-ink)', opacity: 0.5 }
              }
            >
              {i < stepIndex ? <Check size={13} /> : i + 1}
            </div>
            <span
              className="text-xs md:text-sm uppercase tracking-wide"
              style={{ color: 'var(--color-ink)', opacity: i <= stepIndex ? 1 : 0.4 }}
            >
              {s.label}
            </span>
            {i < steps.length - 1 && (
              <span className="w-6 md:w-10 h-px shrink-0" style={{ backgroundColor: 'rgba(38,34,32,0.15)' }} />
            )}
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div className="lg:col-span-2">
          {step === 'details' && (
            <form onSubmit={handleDetailsSubmit} className="space-y-8">
              <div>
                <p className="eyebrow mb-4" style={{ color: 'var(--color-gold)' }}>How would you like your order?</p>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <button
                    type="button"
                    onClick={() => setMethod('delivery')}
                    className="flex items-start gap-3 p-4 border text-left transition-colors"
                    style={{ borderColor: method === 'delivery' ? 'var(--color-gold)' : 'rgba(38,34,32,0.2)' }}
                  >
                    <Truck size={18} style={{ color: 'var(--color-gold)' }} className="mt-0.5 shrink-0" />
                    <span>
                      <span className="block text-sm font-medium" style={{ color: 'var(--color-ink)' }}>Delivery</span>
                      <span className="block text-xs mt-0.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                        Island-wide, {formatCurrency(shopSettings.deliveryFee)} or free over {formatCurrency(shopSettings.freeDeliveryThreshold)}
                      </span>
                    </span>
                  </button>
                  <button
                    type="button"
                    onClick={() => setMethod('pickup')}
                    className="flex items-start gap-3 p-4 border text-left transition-colors"
                    style={{ borderColor: method === 'pickup' ? 'var(--color-gold)' : 'rgba(38,34,32,0.2)' }}
                  >
                    <Store size={18} style={{ color: 'var(--color-gold)' }} className="mt-0.5 shrink-0" />
                    <span>
                      <span className="block text-sm font-medium" style={{ color: 'var(--color-ink)' }}>Pickup at Salon</span>
                      <span className="block text-xs mt-0.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                        Free — collect at Galle, ready same day
                      </span>
                    </span>
                  </button>
                </div>
              </div>

              <div>
                <p className="eyebrow mb-4" style={{ color: 'var(--color-gold)' }}>Contact Details</p>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="sm:col-span-2">
                    <label className="field-label" htmlFor="fullName">Full name</label>
                    <input
                      id="fullName"
                      required
                      className="field-input"
                      value={details.fullName}
                      onChange={(e) => setDetails({ ...details, fullName: e.target.value })}
                    />
                  </div>
                  <div>
                    <label className="field-label" htmlFor="phone">Phone number</label>
                    <input
                      id="phone"
                      required
                      type="tel"
                      className="field-input"
                      value={details.phone}
                      onChange={(e) => setDetails({ ...details, phone: e.target.value })}
                    />
                  </div>
                  <div>
                    <label className="field-label" htmlFor="email">Email</label>
                    <input
                      id="email"
                      required
                      type="email"
                      className="field-input"
                      value={details.email}
                      onChange={(e) => setDetails({ ...details, email: e.target.value })}
                    />
                  </div>
                  {method === 'delivery' && (
                    <>
                      <div className="sm:col-span-2">
                        <label className="field-label" htmlFor="address">Delivery address</label>
                        <input
                          id="address"
                          required
                          className="field-input"
                          value={details.address}
                          onChange={(e) => setDetails({ ...details, address: e.target.value })}
                        />
                      </div>
                      <div>
                        <label className="field-label" htmlFor="city">Town / City</label>
                        <input
                          id="city"
                          required
                          className="field-input"
                          value={details.city}
                          onChange={(e) => setDetails({ ...details, city: e.target.value })}
                        />
                      </div>
                    </>
                  )}
                  <div className="sm:col-span-2">
                    <label className="field-label" htmlFor="notes">Order notes (optional)</label>
                    <input
                      id="notes"
                      className="field-input"
                      placeholder="e.g. preferred delivery time, shade match request"
                      value={details.notes}
                      onChange={(e) => setDetails({ ...details, notes: e.target.value })}
                    />
                  </div>
                </div>
              </div>

              <Button type="submit" className="w-full sm:w-auto">Continue to Payment</Button>
            </form>
          )}

          {step === 'payment' && (
            <div className="space-y-6">
              <p className="eyebrow mb-2" style={{ color: 'var(--color-gold)' }}>Payment Method</p>
              <div className="space-y-3">
                <button
                  type="button"
                  onClick={() => setPayment('cod')}
                  className="w-full flex items-start gap-3 p-4 border text-left transition-colors"
                  style={{ borderColor: payment === 'cod' ? 'var(--color-gold)' : 'rgba(38,34,32,0.2)' }}
                >
                  <Banknote size={18} style={{ color: 'var(--color-gold)' }} className="mt-0.5 shrink-0" />
                  <span>
                    <span className="block text-sm font-medium" style={{ color: 'var(--color-ink)' }}>
                      Cash on {method === 'pickup' ? 'Pickup' : 'Delivery'}
                    </span>
                    <span className="block text-xs mt-0.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                      Pay in cash when your order arrives or is collected
                    </span>
                  </span>
                </button>
                <button
                  type="button"
                  onClick={() => setPayment('bank')}
                  className="w-full flex items-start gap-3 p-4 border text-left transition-colors"
                  style={{ borderColor: payment === 'bank' ? 'var(--color-gold)' : 'rgba(38,34,32,0.2)' }}
                >
                  <Landmark size={18} style={{ color: 'var(--color-gold)' }} className="mt-0.5 shrink-0" />
                  <span>
                    <span className="block text-sm font-medium" style={{ color: 'var(--color-ink)' }}>Bank Transfer</span>
                    <span className="block text-xs mt-0.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                      Account details sent by SMS/email after ordering
                    </span>
                  </span>
                </button>
                <button
                  type="button"
                  onClick={() => setPayment('card')}
                  className="w-full flex items-start gap-3 p-4 border text-left transition-colors"
                  style={{ borderColor: payment === 'card' ? 'var(--color-gold)' : 'rgba(38,34,32,0.2)' }}
                >
                  <CreditCard size={18} style={{ color: 'var(--color-gold)' }} className="mt-0.5 shrink-0" />
                  <span>
                    <span className="block text-sm font-medium" style={{ color: 'var(--color-ink)' }}>Pay by Card</span>
                    <span className="block text-xs mt-0.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
                      Card checkout is in test mode right now — no real charge is made
                    </span>
                  </span>
                </button>
              </div>
              <div className="flex gap-3 pt-2">
                <Button variant="outline" onClick={() => setStep('details')}>Back</Button>
                <Button onClick={() => setStep('review')}>Continue to Review</Button>
              </div>
            </div>
          )}

          {step === 'review' && (
            <div className="space-y-8">
              <div>
                <p className="eyebrow mb-3" style={{ color: 'var(--color-gold)' }}>Contact &amp; Fulfilment</p>
                <div className="p-4 text-sm space-y-1" style={{ backgroundColor: 'var(--color-ivory-dim)', color: 'var(--color-ink)' }}>
                  <p>{details.fullName} · {details.phone}</p>
                  {details.email && <p style={{ opacity: 0.7 }}>{details.email}</p>}
                  <p style={{ opacity: 0.7 }}>
                    {method === 'delivery' ? `Delivery to ${details.address}, ${details.city}` : `Pickup at ${site.name}, ${site.address}`}
                  </p>
                  {details.notes && <p style={{ opacity: 0.7 }}>Note: {details.notes}</p>}
                </div>
              </div>
              <div>
                <p className="eyebrow mb-3" style={{ color: 'var(--color-gold)' }}>Payment</p>
                <div className="p-4 text-sm" style={{ backgroundColor: 'var(--color-ivory-dim)', color: 'var(--color-ink)' }}>
                  {payment === 'cod' && `Cash on ${method === 'pickup' ? 'Pickup' : 'Delivery'}`}
                  {payment === 'bank' && 'Bank Transfer'}
                  {payment === 'card' && 'Card (test mode)'}
                </div>
              </div>
              <div className="flex gap-3">
                <Button variant="outline" onClick={() => setStep('payment')}>Back</Button>
                <Button onClick={handlePlaceOrder} disabled={placing}>
                  {placing ? 'Placing Order…' : 'Place Order'}
                </Button>
              </div>
              {placeError && (
                <p className="text-sm px-4 py-3" style={{ backgroundColor: 'var(--color-blush-light)', color: 'var(--color-maroon)' }}>
                  {placeError}
                </p>
              )}
            </div>
          )}
        </div>

        <div>
          <div className="p-6 sticky top-28" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
            <h2 className="font-display text-xl mb-5" style={{ color: 'var(--color-ink)' }}>Order Summary</h2>
            <ul className="space-y-3 mb-5">
              {lines.map((line) => (
                <li key={line.product.id} className="flex justify-between text-sm gap-3">
                  <span style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
                    {line.product.name} × {line.quantity}
                  </span>
                  <span className="shrink-0" style={{ color: 'var(--color-ink)' }}>
                    {formatCurrency(line.product.price * line.quantity)}
                  </span>
                </li>
              ))}
            </ul>
            <div className="space-y-2 text-sm mb-5 pt-4 border-t" style={{ borderColor: 'rgba(38,34,32,0.15)', color: 'var(--color-ink)' }}>
              <div className="flex justify-between">
                <span style={{ opacity: 0.7 }}>Subtotal</span>
                <span>{formatCurrency(subtotal)}</span>
              </div>
              <div className="flex justify-between">
                <span style={{ opacity: 0.7 }}>Delivery</span>
                <span>{deliveryFee === 0 ? 'Free' : formatCurrency(deliveryFee)}</span>
              </div>
            </div>
            <div className="flex justify-between items-baseline pt-4 border-t" style={{ borderColor: 'rgba(38,34,32,0.15)' }}>
              <span className="text-sm" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>Total</span>
              <span className="font-display italic text-2xl" style={{ color: 'var(--color-maroon)' }}>
                {formatCurrency(total)}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
