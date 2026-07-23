import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, Pencil, X, ChevronDown, ChevronUp } from 'lucide-react';
import {
  createCustomer,
  deleteCustomer,
  fetchCustomer,
  fetchCustomers,
  updateCustomer,
  type Customer,
  type CustomerJob,
} from '../lib/api';

function formatCurrency(n: number) {
  return `LKR ${n.toLocaleString('en-US')}`;
}

export function Customers() {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [search, setSearch] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [adding, setAdding] = useState(false);
  const [editing, setEditing] = useState<Customer | null>(null);
  const [expanded, setExpanded] = useState<number | null>(null);
  const [detail, setDetail] = useState<{ jobs: CustomerJob[]; visitCount: number; totalSpent: number } | null>(null);

  function load() {
    setIsLoading(true);
    fetchCustomers(search || undefined)
      .then((res) => setCustomers(res.customers.data))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load customers.'))
      .finally(() => setIsLoading(false));
  }

  useEffect(load, []);

  async function handleAdd(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await createCustomer({
        name: String(form.get('name')),
        phone: String(form.get('phone')),
        email: String(form.get('email') || ''),
        notes: String(form.get('notes') || ''),
      });
      setAdding(false);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add customer.');
    }
  }

  async function handleUpdate(e: FormEvent<HTMLFormElement>, customer: Customer) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await updateCustomer(customer.id, {
        name: String(form.get('name')),
        phone: String(form.get('phone')),
        email: String(form.get('email') || ''),
        notes: String(form.get('notes') || ''),
      });
      setEditing(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update customer.');
    }
  }

  async function handleDelete(customer: Customer) {
    if (!confirm(`Delete ${customer.name}?`)) return;
    try {
      await deleteCustomer(customer.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete customer.');
    }
  }

  async function toggleExpand(customer: Customer) {
    if (expanded === customer.id) {
      setExpanded(null);
      setDetail(null);
      return;
    }
    setExpanded(customer.id);
    try {
      const res = await fetchCustomer(customer.id);
      setDetail({ jobs: res.jobs, visitCount: res.visitCount, totalSpent: res.totalSpent });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load customer history.');
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

      <div className="flex flex-wrap items-end justify-between gap-3">
        <label className="flex-1 min-w-[220px]">
          <span className="mb-1 block text-xs text-muted">Search name / phone / email</span>
          <div className="flex gap-2">
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && load()}
              className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
            />
            <button onClick={load} className="rounded-lg border border-ink/10 px-4 py-2 text-sm text-ink hover:bg-paper-dim">
              Search
            </button>
          </div>
        </label>
        <button
          onClick={() => setAdding((v) => !v)}
          className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light"
        >
          <Plus size={16} /> Add Customer
        </button>
      </div>

      {adding && (
        <form onSubmit={handleAdd} className="mirror-card grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
          <input name="name" required placeholder="Name" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <input name="phone" required placeholder="Phone" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <input name="email" type="email" placeholder="Email (optional)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <input name="notes" placeholder="Notes (optional)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light lg:col-span-4 lg:w-fit">
            Save
          </button>
        </form>
      )}

      {isLoading ? (
        <p className="text-sm text-muted">Loading customers…</p>
      ) : customers.length === 0 ? (
        <p className="mirror-card p-6 text-center text-sm text-muted">No customers found.</p>
      ) : (
        <div className="mirror-card divide-y divide-ink/5">
          {customers.map((customer) =>
            editing?.id === customer.id ? (
              <form
                key={customer.id}
                onSubmit={(e) => handleUpdate(e, customer)}
                className="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4"
              >
                <input name="name" required defaultValue={customer.name} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                <input name="phone" required defaultValue={customer.phone} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                <input name="email" type="email" defaultValue={customer.email ?? ''} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                <input name="notes" defaultValue={customer.notes ?? ''} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                <div className="flex gap-2 lg:col-span-4">
                  <button type="submit" className="rounded-lg bg-wine px-3 py-2 text-xs font-medium text-paper hover:bg-wine-light">
                    Save
                  </button>
                  <button type="button" onClick={() => setEditing(null)} className="rounded-lg border border-ink/10 px-3 py-2 text-xs text-ink hover:bg-paper-dim">
                    Cancel
                  </button>
                </div>
              </form>
            ) : (
              <div key={customer.id}>
                <div className="flex items-center justify-between gap-4 p-4">
                  <button onClick={() => toggleExpand(customer)} className="flex flex-1 items-center gap-2 text-left">
                    {expanded === customer.id ? <ChevronUp size={16} className="text-muted" /> : <ChevronDown size={16} className="text-muted" />}
                    <div>
                      <p className="text-sm text-ink">{customer.name}</p>
                      <p className="text-xs text-muted">
                        {customer.phone}
                        {customer.email ? ` · ${customer.email}` : ''}
                        {customer.jobs_count !== undefined ? ` · ${customer.jobs_count} visits` : ''}
                      </p>
                    </div>
                  </button>
                  <div className="flex shrink-0 items-center gap-2">
                    <button onClick={() => setEditing(customer)} className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim">
                      <Pencil size={14} />
                    </button>
                    <button onClick={() => handleDelete(customer)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                      <Trash2 size={14} />
                    </button>
                  </div>
                </div>
                {expanded === customer.id && (
                  <div className="border-t border-ink/5 bg-paper-dim/40 p-4">
                    {!detail ? (
                      <p className="text-xs text-muted">Loading history…</p>
                    ) : (
                      <>
                        <p className="mb-2 text-xs text-muted">
                          {detail.visitCount} visits · {formatCurrency(detail.totalSpent)} total spent
                        </p>
                        {detail.jobs.length === 0 ? (
                          <p className="text-xs text-muted">No job history yet.</p>
                        ) : (
                          <div className="space-y-1">
                            {detail.jobs.map((job) => (
                              <div key={job.id} className="flex justify-between text-xs text-ink">
                                <span>
                                  {job.job_date} · <span className="capitalize">{job.status}</span>
                                </span>
                                <span>{formatCurrency(job.total_paid)}</span>
                              </div>
                            ))}
                          </div>
                        )}
                      </>
                    )}
                  </div>
                )}
              </div>
            )
          )}
        </div>
      )}
    </div>
  );
}
