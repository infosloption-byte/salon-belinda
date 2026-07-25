import { useEffect, useState, type FormEvent } from 'react';
import {
  createCustomerPoint,
  fetchCustomerPoints,
  type Customer,
  type CustomerPointType,
  type PaginatedCustomerPoints,
} from '../../lib/api';

const TYPE_LABEL: Record<CustomerPointType, string> = {
  earned: 'Earned',
  redeemed: 'Redeemed',
  adjustment: 'Adjustment',
};

const TYPE_COLOR: Record<CustomerPointType, string> = {
  earned: 'text-emerald-600',
  redeemed: 'text-amber-600',
  adjustment: 'text-muted',
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

interface CustomerPointsPanelProps {
  customer: Customer;
  onPointsChanged: (customer: Customer) => void;
}

/**
 * SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — points ledger. Shown
 * as an expandable panel under a customer in the Customers page: full
 * history (automatic "earned" entries from job payments, plus manual
 * redeem/adjustment entries) and a form for the two kinds of entry an
 * admin makes on purpose. Mirrors StockLedgerPanel.tsx.
 */
export function CustomerPointsPanel({ customer, onPointsChanged }: CustomerPointsPanelProps) {
  const [ledger, setLedger] = useState<PaginatedCustomerPoints | null>(null);
  const [page, setPage] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [entryType, setEntryType] = useState<'redeemed' | 'adjustment'>('redeemed');

  function load() {
    setIsLoading(true);
    fetchCustomerPoints(customer.id, page)
      .then((res) => setLedger(res.ledger))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load points history.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [customer.id, page]);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    const form = new FormData(e.currentTarget);
    const amount = Number(form.get('amount'));
    const direction = entryType === 'adjustment' ? String(form.get('direction') || 'add') : 'remove';
    const points = direction === 'remove' ? -Math.abs(amount) : Math.abs(amount);

    if (!amount || amount <= 0) {
      setError('Enter a points amount greater than zero.');
      return;
    }

    setSaving(true);
    try {
      const res = await createCustomerPoint(customer.id, {
        type: entryType,
        points,
        reason: String(form.get('reason') || '') || undefined,
      });
      onPointsChanged(res.customer);
      (e.target as HTMLFormElement).reset();
      setPage(1);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to record points entry.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="border-t border-ink/5 bg-paper-dim/40 p-4">
      {error && <p className="mb-3 text-xs text-danger">{error}</p>}

      <form onSubmit={handleSubmit} className="mb-4 grid grid-cols-2 gap-2 rounded-lg border border-ink/10 p-3 sm:grid-cols-5">
        <select
          value={entryType}
          onChange={(e) => setEntryType(e.target.value as 'redeemed' | 'adjustment')}
          className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold"
        >
          <option value="redeemed">Redeem</option>
          <option value="adjustment">Adjustment</option>
        </select>
        {entryType === 'adjustment' && (
          <select name="direction" className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold">
            <option value="add">Add points</option>
            <option value="remove">Remove points</option>
          </select>
        )}
        <input
          name="amount"
          type="number"
          min={1}
          required
          placeholder="Points"
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
        Currently <span className="font-medium text-ink">{customer.points_balance}</span> points.
      </p>

      {isLoading ? (
        <p className="text-xs text-muted">Loading…</p>
      ) : !ledger || ledger.data.length === 0 ? (
        <p className="text-xs text-muted">No points activity yet.</p>
      ) : (
        <div className="divide-y divide-ink/5 rounded-lg border border-ink/10">
          {ledger.data.map((entry) => (
            <div key={entry.id} className="flex items-center justify-between gap-2 px-3 py-2 text-xs">
              <div>
                <span className={`font-medium ${TYPE_COLOR[entry.type]}`}>{TYPE_LABEL[entry.type]}</span>
                <span className="text-muted"> · {formatDate(entry.created_at)}</span>
                {entry.reason && <span className="text-muted"> · {entry.reason}</span>}
                {entry.creator && <span className="text-muted"> · {entry.creator.name}</span>}
              </div>
              <div className="shrink-0 text-right">
                <span className={entry.points >= 0 ? 'text-emerald-600' : 'text-danger'}>
                  {formatDelta(entry.points)}
                </span>
                <span className="text-muted"> → {entry.balance_after}</span>
              </div>
            </div>
          ))}
        </div>
      )}

      {ledger && ledger.last_page > 1 && (
        <div className="mt-2 flex items-center justify-end gap-2 text-xs">
          <button
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            className="rounded-lg border border-ink/10 px-2 py-1 text-ink disabled:opacity-40"
          >
            Prev
          </button>
          <span className="text-muted">
            {ledger.current_page} / {ledger.last_page}
          </span>
          <button
            disabled={page >= ledger.last_page}
            onClick={() => setPage((p) => Math.min(ledger.last_page, p + 1))}
            className="rounded-lg border border-ink/10 px-2 py-1 text-ink disabled:opacity-40"
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
}
