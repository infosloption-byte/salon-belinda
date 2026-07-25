import { useEffect, useState, type FormEvent } from 'react';
import {
  createStockMovement,
  fetchStockMovements,
  type PaginatedStockMovements,
  type Product,
  type StockMovementType,
} from '../../lib/api';

const TYPE_LABEL: Record<StockMovementType, string> = {
  sale: 'Sale',
  restock: 'Restock',
  adjustment: 'Adjustment',
  correction: 'Correction (manual edit)',
};

const TYPE_COLOR: Record<StockMovementType, string> = {
  sale: 'text-ink',
  restock: 'text-emerald-600',
  adjustment: 'text-amber-600',
  correction: 'text-muted',
};

function formatDelta(n: number) {
  return n > 0 ? `+${n}` : `${n}`;
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

interface StockLedgerPanelProps {
  product: Product;
  onStockChanged: (product: Product) => void;
}

/**
 * SALON-OPS-ENHANCEMENTS.md, "Inventory" (Tier 3) — inventory movement
 * ledger. Shown as an expandable panel under a product in the Products
 * page: full history of stock changes (sales, restocks, adjustments,
 * and corrections from raw product-form edits) plus a form to log a
 * deliberate restock or adjustment.
 */
export function StockLedgerPanel({ product, onStockChanged }: StockLedgerPanelProps) {
  const [movements, setMovements] = useState<PaginatedStockMovements | null>(null);
  const [page, setPage] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [movementType, setMovementType] = useState<'restock' | 'adjustment'>('restock');

  function load() {
    setIsLoading(true);
    fetchStockMovements(product.id, page)
      .then((res) => setMovements(res.movements))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load stock history.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [product.id, page]);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    const form = new FormData(e.currentTarget);
    const amount = Number(form.get('amount'));
    const direction = movementType === 'adjustment' ? String(form.get('direction') || 'add') : 'add';
    const quantity_change = direction === 'remove' ? -Math.abs(amount) : Math.abs(amount);

    if (!amount || amount <= 0) {
      setError('Enter a quantity greater than zero.');
      return;
    }

    setSaving(true);
    try {
      const res = await createStockMovement(product.id, {
        type: movementType,
        quantity_change,
        reason: String(form.get('reason') || '') || undefined,
      });
      onStockChanged(res.product);
      (e.target as HTMLFormElement).reset();
      setPage(1);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to record stock movement.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="border-t border-ink/5 bg-paper-dim/40 p-4">
      {error && <p className="mb-3 text-xs text-danger">{error}</p>}

      <form onSubmit={handleSubmit} className="mb-4 grid grid-cols-2 gap-2 rounded-lg border border-ink/10 p-3 sm:grid-cols-5">
        <select
          value={movementType}
          onChange={(e) => setMovementType(e.target.value as 'restock' | 'adjustment')}
          className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold"
        >
          <option value="restock">Restock</option>
          <option value="adjustment">Adjustment</option>
        </select>
        {movementType === 'adjustment' && (
          <select name="direction" className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold">
            <option value="add">Add stock</option>
            <option value="remove">Remove stock</option>
          </select>
        )}
        <input
          name="amount"
          type="number"
          min={1}
          required
          placeholder="Quantity"
          className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold"
        />
        <input
          name="reason"
          placeholder="Reason (optional)"
          className="col-span-2 rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold sm:col-span-1"
        />
        <button
          type="submit"
          disabled={saving}
          className="rounded-lg bg-wine px-3 py-1.5 text-xs font-medium text-paper hover:bg-wine-light disabled:opacity-60"
        >
          {saving ? 'Saving…' : 'Record'}
        </button>
      </form>

      <p className="mb-2 text-xs text-muted">
        Currently <span className="font-medium text-ink">{product.stock_count}</span> in stock.
      </p>

      {isLoading ? (
        <p className="text-xs text-muted">Loading…</p>
      ) : !movements || movements.data.length === 0 ? (
        <p className="text-xs text-muted">No stock movements recorded yet.</p>
      ) : (
        <div className="divide-y divide-ink/5 rounded-lg border border-ink/10">
          {movements.data.map((m) => (
            <div key={m.id} className="flex items-center justify-between gap-2 px-3 py-2 text-xs">
              <div>
                <span className={`font-medium ${TYPE_COLOR[m.type]}`}>{TYPE_LABEL[m.type]}</span>
                <span className="text-muted"> · {formatDate(m.created_at)}</span>
                {m.reason && <span className="text-muted"> · {m.reason}</span>}
                {m.creator && <span className="text-muted"> · {m.creator.name}</span>}
              </div>
              <div className="shrink-0 text-right">
                <span className={m.quantity_change >= 0 ? 'text-emerald-600' : 'text-danger'}>
                  {formatDelta(m.quantity_change)}
                </span>
                <span className="text-muted"> → {m.balance_after}</span>
              </div>
            </div>
          ))}
        </div>
      )}

      {movements && movements.last_page > 1 && (
        <div className="mt-2 flex items-center justify-end gap-2 text-xs">
          <button
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            className="rounded-lg border border-ink/10 px-2 py-1 text-ink disabled:opacity-40"
          >
            Prev
          </button>
          <span className="text-muted">
            {movements.current_page} / {movements.last_page}
          </span>
          <button
            disabled={page >= movements.last_page}
            onClick={() => setPage((p) => Math.min(movements.last_page, p + 1))}
            className="rounded-lg border border-ink/10 px-2 py-1 text-ink disabled:opacity-40"
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
}
