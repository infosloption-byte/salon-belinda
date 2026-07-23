import { useEffect, useState } from 'react';
import { Download, Eye, X } from 'lucide-react';
import {
  downloadOrderInvoice,
  fetchOrder,
  fetchOrders,
  markOrderPaid,
  openOrderInvoice,
  updateOrderStatus,
  type Order,
  type OrderStatus,
} from '../lib/api';

const STATUSES: OrderStatus[] = ['pending', 'processing', 'completed', 'cancelled'];

const statusStyles: Record<OrderStatus, string> = {
  pending: 'bg-amber-100 text-amber-700',
  processing: 'bg-blue-100 text-blue-700',
  completed: 'bg-emerald-100 text-emerald-700',
  cancelled: 'bg-red-100 text-red-700',
};

function formatCurrency(n: number) {
  return `LKR ${n.toLocaleString('en-US')}`;
}

export function Orders() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [statusFilter, setStatusFilter] = useState('');
  const [search, setSearch] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selected, setSelected] = useState<Order | null>(null);

  function load() {
    setIsLoading(true);
    fetchOrders({ status: statusFilter || undefined, q: search || undefined })
      .then((res) => setOrders(res.orders.data))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load orders.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [statusFilter]);

  async function handleStatusChange(order: Order, status: OrderStatus) {
    try {
      const res = await updateOrderStatus(order.id, status);
      setOrders((prev) => prev.map((o) => (o.id === order.id ? res.order : o)));
      if (selected?.id === order.id) setSelected(res.order);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update order status.');
    }
  }

  async function handleMarkPaid(order: Order) {
    try {
      const res = await markOrderPaid(order.id);
      setOrders((prev) => prev.map((o) => (o.id === order.id ? res.order : o)));
      if (selected?.id === order.id) setSelected(res.order);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to mark order as paid.');
    }
  }

  async function openDetail(order: Order) {
    try {
      const res = await fetchOrder(order.id);
      setSelected(res.order);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load order.');
    }
  }

  return (
    <div className="space-y-6">
      {error && (
        <p className="mirror-card flex items-center justify-between p-4 text-sm text-danger">
          {error}
          <button onClick={() => setError(null)} className="text-danger/60 hover:text-danger">
            <X size={16} />
          </button>
        </p>
      )}

      <div className="mirror-card flex flex-wrap items-end gap-3 p-4">
        <label className="flex-1 min-w-[180px]">
          <span className="mb-1 block text-xs text-muted">Search order # / name / phone</span>
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && load()}
            className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
          />
        </label>
        <label>
          <span className="mb-1 block text-xs text-muted">Status</span>
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
          >
            <option value="">All</option>
            {STATUSES.map((s) => (
              <option key={s} value={s}>{s}</option>
            ))}
          </select>
        </label>
        <button onClick={load} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
          Search
        </button>
      </div>

      {isLoading ? (
        <p className="text-sm text-muted">Loading orders…</p>
      ) : orders.length === 0 ? (
        <p className="mirror-card p-6 text-center text-sm text-muted">No orders found.</p>
      ) : (
        <div className="mirror-card divide-y divide-ink/5">
          {orders.map((order) => (
            <div key={order.id} className="flex flex-wrap items-center justify-between gap-3 p-4">
              <button onClick={() => openDetail(order)} className="flex-1 text-left">
                <p className="text-sm font-medium text-ink">{order.order_number} · {order.customer_name}</p>
                <p className="text-xs text-muted">
                  {formatCurrency(order.total)} · {order.payment_method.toUpperCase()} ·{' '}
                  <span className={order.payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700'}>
                    {order.payment_status}
                  </span>
                  {' · '}{new Date(order.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })}
                </p>
              </button>
              <div className="flex shrink-0 items-center gap-2">
                <select
                  value={order.status}
                  onChange={(e) => handleStatusChange(order, e.target.value as OrderStatus)}
                  className={`rounded-lg border border-ink/10 px-2 py-1.5 text-xs font-medium capitalize outline-none ${statusStyles[order.status]}`}
                >
                  {STATUSES.map((s) => (
                    <option key={s} value={s}>{s}</option>
                  ))}
                </select>
                <button onClick={() => openDetail(order)} className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim">
                  <Eye size={14} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {selected && (
        <div className="fixed inset-0 z-30 flex items-center justify-center bg-ink/40 p-4" onClick={() => setSelected(null)}>
          <div className="mirror-card max-h-[85vh] w-full max-w-xl overflow-y-auto p-6" onClick={(e) => e.stopPropagation()}>
            <div className="mb-4 flex items-start justify-between">
              <div>
                <h2 className="font-display text-lg text-ink">{selected.order_number}</h2>
                <p className="text-xs text-muted">{selected.customer_name} · {selected.customer_phone}</p>
              </div>
              <button onClick={() => setSelected(null)} className="text-muted hover:text-ink">
                <X size={18} />
              </button>
            </div>

            <div className="mb-4 space-y-1 text-sm text-ink">
              <p>{selected.fulfilment_method === 'pickup' ? 'Pickup' : `Delivery to ${selected.address}, ${selected.city}`}</p>
              {selected.notes && <p className="italic text-muted">"{selected.notes}"</p>}
            </div>

            <div className="mb-4 divide-y divide-ink/5 rounded-lg border border-ink/10">
              {selected.items?.map((item) => (
                <div key={item.id} className="flex justify-between p-3 text-sm">
                  <span>{item.product_name} × {item.quantity}</span>
                  <span>{formatCurrency(item.line_total)}</span>
                </div>
              ))}
            </div>

            <div className="mb-4 space-y-1 text-sm">
              <div className="flex justify-between text-muted"><span>Subtotal</span><span>{formatCurrency(selected.subtotal)}</span></div>
              <div className="flex justify-between text-muted"><span>Delivery</span><span>{formatCurrency(selected.delivery_fee)}</span></div>
              <div className="flex justify-between font-medium text-ink"><span>Total</span><span>{formatCurrency(selected.total)}</span></div>
            </div>

            <div className="flex flex-wrap gap-2">
              {selected.payment_status !== 'paid' && (
                <button onClick={() => handleMarkPaid(selected)} className="rounded-lg bg-wine px-3 py-2 text-xs font-medium text-paper hover:bg-wine-light">
                  Mark as Paid
                </button>
              )}
              <button onClick={() => openOrderInvoice(selected.id)} className="flex items-center gap-1.5 rounded-lg border border-ink/10 px-3 py-2 text-xs text-ink hover:bg-paper-dim">
                <Eye size={14} /> View Invoice
              </button>
              <button
                onClick={() => downloadOrderInvoice(selected.id, selected.order_number)}
                className="flex items-center gap-1.5 rounded-lg border border-ink/10 px-3 py-2 text-xs text-ink hover:bg-paper-dim"
              >
                <Download size={14} /> Download
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
