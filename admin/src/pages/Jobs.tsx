import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, X, FileDown, Eye, ArrowLeft } from 'lucide-react';
import {
  addJobItem,
  addJobPayment,
  createJob,
  downloadJobReceipt,
  fetchJob,
  fetchJobCreateData,
  fetchJobs,
  openJobReceipt,
  quickRegisterCustomer,
  removeJobItem,
  removeJobPayment,
  updateJobStatus,
  type ActiveStaffMember,
  type JobCustomer,
  type JobStatus,
  type PaginatedJobs,
  type SalonJob,
  type ServiceCategory,
} from '../lib/api';

const STATUSES: JobStatus[] = ['scheduled', 'in_progress', 'completed', 'cancelled'];

function money(cents: number) {
  return `LKR ${cents.toLocaleString('en-US')}`;
}

// ---------- New Job panel ----------

function NewJobPanel({ onCreated, onCancel }: { onCreated: (jobId: number) => void; onCancel: () => void }) {
  const [query, setQuery] = useState('');
  const [customers, setCustomers] = useState<JobCustomer[]>([]);
  const [selected, setSelected] = useState<JobCustomer | null>(null);
  const [visitCount, setVisitCount] = useState(0);
  const [showRegister, setShowRegister] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  function search() {
    fetchJobCreateData({ q: query })
      .then((res) => setCustomers(res.customers))
      .catch((err) => setError(err instanceof Error ? err.message : 'Search failed.'));
  }

  function selectCustomer(customer: JobCustomer) {
    fetchJobCreateData({ customer_id: customer.id })
      .then((res) => {
        setSelected(customer);
        setVisitCount(res.selectedCustomerVisitCount);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load customer.'));
  }

  async function handleRegister(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      const res = await quickRegisterCustomer({
        name: String(form.get('name')),
        phone: String(form.get('phone')),
        email: String(form.get('email') || ''),
      });
      setShowRegister(false);
      selectCustomer(res.customer);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to register customer.');
    }
  }

  async function handleStartJob(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    if (!selected) return;
    setSaving(true);
    const form = new FormData(e.currentTarget);
    try {
      const res = await createJob({
        customer_id: selected.id,
        job_date: String(form.get('job_date')),
        status: String(form.get('status')) as JobStatus,
        notes: String(form.get('notes') || ''),
      });
      onCreated(res.job.id);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to start job.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="mirror-card space-y-4 p-4">
      {error && <p className="text-sm text-danger">{error}</p>}

      {!selected ? (
        <>
          <div className="flex items-end gap-2">
            <input
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && search()}
              placeholder="Search customer by name or phone"
              className="flex-1 rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
            />
            <button onClick={search} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
              Search
            </button>
          </div>

          <div className="divide-y divide-ink/5">
            {customers.map((c) => (
              <button
                key={c.id}
                onClick={() => selectCustomer(c)}
                className="flex w-full items-center justify-between py-2 text-left text-sm hover:bg-paper-dim"
              >
                <span>{c.name}</span>
                <span className="text-xs text-muted">{c.phone}</span>
              </button>
            ))}
          </div>

          {!showRegister ? (
            <button onClick={() => setShowRegister(true)} className="flex items-center gap-1 text-xs text-muted hover:text-ink">
              <Plus size={12} /> No match — register a new customer
            </button>
          ) : (
            <form onSubmit={handleRegister} className="grid grid-cols-1 gap-3 rounded-lg border border-ink/10 p-4 sm:grid-cols-3">
              <input name="name" required placeholder="Full name" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input name="phone" required placeholder="Phone" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input name="email" type="email" placeholder="Email (optional)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light sm:col-span-3">
                Register & continue
              </button>
            </form>
          )}
        </>
      ) : (
        <form onSubmit={handleStartJob} className="space-y-3">
          <div className="rounded-lg border border-ink/10 p-3">
            <p className="text-sm font-medium text-ink">{selected.name}</p>
            <p className="text-xs text-muted">{selected.phone} · {visitCount} previous visit{visitCount === 1 ? '' : 's'}</p>
            <button type="button" onClick={() => setSelected(null)} className="mt-1 text-xs text-muted hover:text-ink">Change customer</button>
          </div>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <input name="job_date" type="date" required defaultValue={new Date().toISOString().slice(0, 10)}
              className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <select name="status" defaultValue="in_progress" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
              {STATUSES.map((s) => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
          <textarea name="notes" placeholder="Notes (optional)" rows={2}
            className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <div className="flex gap-2">
            <button type="submit" disabled={saving} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light disabled:opacity-60">
              {saving ? 'Starting…' : 'Start job'}
            </button>
          </div>
        </form>
      )}

      <button onClick={onCancel} className="flex items-center gap-1 text-xs text-muted hover:text-ink">
        <ArrowLeft size={12} /> Cancel
      </button>
    </div>
  );
}

// ---------- Job detail panel ----------

function JobDetail({ jobId, onBack, onChanged }: { jobId: number; onBack: () => void; onChanged: () => void }) {
  const [job, setJob] = useState<SalonJob | null>(null);
  const [categories, setCategories] = useState<ServiceCategory[]>([]);
  const [staff, setStaff] = useState<ActiveStaffMember[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [addingItem, setAddingItem] = useState(false);
  const [addingPayment, setAddingPayment] = useState(false);

  function load() {
    fetchJob(jobId)
      .then((res) => {
        setJob(res.job);
        setCategories(res.serviceCategories);
        setStaff(res.activeStaff);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load job.'));
  }

  useEffect(load, [jobId]);

  async function handleAddItem(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const serviceId = form.get('service_id');
    try {
      await addJobItem(jobId, {
        service_id: serviceId ? Number(serviceId) : undefined,
        custom_name: String(form.get('custom_name') || '') || undefined,
        custom_price: form.get('custom_price') ? Number(form.get('custom_price')) : undefined,
        staff_id: Number(form.get('staff_id')),
        discount_type: String(form.get('discount_type')) as 'none' | 'percent' | 'fixed',
        discount_value: form.get('discount_value') ? Number(form.get('discount_value')) : undefined,
      });
      setAddingItem(false);
      load();
      onChanged();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add treatment.');
    }
  }

  async function handleRemoveItem(itemId: number) {
    if (!confirm('Remove this treatment?')) return;
    try {
      await removeJobItem(jobId, itemId);
      load();
      onChanged();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to remove treatment.');
    }
  }

  async function handleAddPayment(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await addJobPayment(jobId, {
        amount: Number(form.get('amount')),
        method: String(form.get('method')) as 'cash' | 'card' | 'bank_transfer',
        note: String(form.get('note') || '') || undefined,
      });
      setAddingPayment(false);
      load();
      onChanged();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to record payment.');
    }
  }

  async function handleRemovePayment(paymentId: number) {
    if (!confirm('Remove this payment?')) return;
    try {
      await removeJobPayment(jobId, paymentId);
      load();
      onChanged();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to remove payment.');
    }
  }

  async function handleStatusChange(status: JobStatus) {
    try {
      await updateJobStatus(jobId, status);
      load();
      onChanged();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update status.');
    }
  }

  if (!job) return <p className="text-sm text-muted">Loading job…</p>;

  return (
    <div className="space-y-4">
      <button onClick={onBack} className="flex items-center gap-1 text-xs text-muted hover:text-ink">
        <ArrowLeft size={12} /> Back to jobs
      </button>

      {error && (
        <p className="mirror-card flex items-center justify-between p-4 text-sm text-danger">
          {error}
          <button onClick={() => setError(null)} className="text-danger/60 hover:text-danger"><X size={16} /></button>
        </p>
      )}

      <div className="mirror-card flex flex-wrap items-center justify-between gap-3 p-4">
        <div>
          <p className="text-sm font-medium text-ink">Job #{job.id} — {job.customer?.name}</p>
          <p className="text-xs text-muted">{job.customer?.phone} · {job.job_date}</p>
        </div>
        <div className="flex items-center gap-2">
          <select
            value={job.status}
            onChange={(e) => handleStatusChange(e.target.value as JobStatus)}
            className="rounded-lg border border-ink/10 px-2 py-1.5 text-xs font-medium capitalize outline-none"
          >
            {STATUSES.map((s) => <option key={s} value={s}>{s}</option>)}
          </select>
          <button onClick={() => openJobReceipt(job.id)} className="flex items-center gap-1 rounded-lg border border-ink/10 px-3 py-1.5 text-xs hover:bg-paper-dim">
            <Eye size={14} /> Preview receipt
          </button>
          <button onClick={() => downloadJobReceipt(job.id)} className="flex items-center gap-1 rounded-lg border border-ink/10 px-3 py-1.5 text-xs hover:bg-paper-dim">
            <FileDown size={14} /> Download
          </button>
        </div>
      </div>

      <div className="mirror-card p-4">
        <div className="mb-3 flex items-center justify-between">
          <h3 className="font-display text-base text-ink">Treatments</h3>
          <button onClick={() => setAddingItem((v) => !v)} className="flex items-center gap-1 rounded-lg border border-ink/10 px-3 py-1.5 text-xs hover:bg-paper-dim">
            <Plus size={14} /> Add treatment
          </button>
        </div>

        {addingItem && (
          <form onSubmit={handleAddItem} className="mb-4 grid grid-cols-1 gap-3 rounded-lg border border-ink/10 p-4 sm:grid-cols-3">
            <select name="service_id" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold sm:col-span-3">
              <option value="">Custom treatment (fill fields below)…</option>
              {categories.map((cat) => (
                <optgroup key={cat.id} label={cat.title}>
                  {cat.services.map((s) => (
                    <option key={s.id} value={s.id}>{s.name} — {money(s.price)}</option>
                  ))}
                </optgroup>
              ))}
            </select>
            <input name="custom_name" placeholder="Custom treatment name" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <input name="custom_price" type="number" min={0} placeholder="Custom price" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <select name="staff_id" required className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
              <option value="" disabled>Staff…</option>
              {staff.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
            </select>
            <select name="discount_type" defaultValue="none" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
              <option value="none">No discount</option>
              <option value="percent">Percent off</option>
              <option value="fixed">Fixed amount off</option>
            </select>
            <input name="discount_value" type="number" min={0} placeholder="Discount value" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light sm:col-span-3">
              Add
            </button>
          </form>
        )}

        <div className="divide-y divide-ink/5">
          {(job.items ?? []).map((item) => (
            <div key={item.id} className="flex items-center justify-between gap-3 py-2">
              <div>
                <p className="text-sm text-ink">{item.service_name}</p>
                <p className="text-xs text-muted">{item.staff?.name} · {money(item.final_price)}</p>
              </div>
              <button onClick={() => handleRemoveItem(item.id)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                <Trash2 size={14} />
              </button>
            </div>
          ))}
          {(job.items ?? []).length === 0 && <p className="py-3 text-sm text-muted">No treatments added yet.</p>}
        </div>
      </div>

      <div className="mirror-card p-4">
        <div className="mb-3 flex items-center justify-between">
          <h3 className="font-display text-base text-ink">Payments</h3>
          <button onClick={() => setAddingPayment((v) => !v)} className="flex items-center gap-1 rounded-lg border border-ink/10 px-3 py-1.5 text-xs hover:bg-paper-dim">
            <Plus size={14} /> Record payment
          </button>
        </div>

        {addingPayment && (
          <form onSubmit={handleAddPayment} className="mb-4 grid grid-cols-1 gap-3 rounded-lg border border-ink/10 p-4 sm:grid-cols-3">
            <input name="amount" type="number" min={1} required placeholder="Amount (LKR)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <select name="method" defaultValue="cash" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="bank_transfer">Bank transfer</option>
            </select>
            <input name="note" placeholder="Note (optional)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light sm:col-span-3">
              Record
            </button>
          </form>
        )}

        <div className="divide-y divide-ink/5">
          {(job.payments ?? []).map((p) => (
            <div key={p.id} className="flex items-center justify-between gap-3 py-2">
              <div>
                <p className="text-sm text-ink">{money(p.amount)} · <span className="capitalize">{p.method.replace('_', ' ')}</span></p>
                {p.note && <p className="text-xs text-muted">{p.note}</p>}
              </div>
              <button onClick={() => handleRemovePayment(p.id)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                <Trash2 size={14} />
              </button>
            </div>
          ))}
          {(job.payments ?? []).length === 0 && <p className="py-3 text-sm text-muted">No payments recorded yet.</p>}
        </div>
      </div>

      <div className="mirror-card grid grid-cols-3 gap-3 p-4 text-center">
        <div>
          <p className="text-xs text-muted">Subtotal</p>
          <p className="text-sm font-medium text-ink">{money(job.subtotal)}</p>
        </div>
        <div>
          <p className="text-xs text-muted">Paid</p>
          <p className="text-sm font-medium text-emerald-600">{money(job.total_paid)}</p>
        </div>
        <div>
          <p className="text-xs text-muted">Balance due</p>
          <p className="text-sm font-medium text-danger">{money(job.balance_due)}</p>
        </div>
      </div>
    </div>
  );
}

// ---------- Main page ----------

export function Jobs() {
  const [view, setView] = useState<'list' | 'create' | 'detail'>('list');
  const [activeJobId, setActiveJobId] = useState<number | null>(null);
  const [jobs, setJobs] = useState<PaginatedJobs | null>(null);
  const [statusFilter, setStatusFilter] = useState('');
  const [error, setError] = useState<string | null>(null);

  function load() {
    fetchJobs({ status: statusFilter || undefined })
      .then((res) => setJobs(res.jobs))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load jobs.'));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [statusFilter, view]);

  if (view === 'create') {
    return (
      <NewJobPanel
        onCancel={() => setView('list')}
        onCreated={(jobId) => {
          setActiveJobId(jobId);
          setView('detail');
        }}
      />
    );
  }

  if (view === 'detail' && activeJobId) {
    return (
      <JobDetail
        jobId={activeJobId}
        onBack={() => setView('list')}
        onChanged={load}
      />
    );
  }

  return (
    <div className="space-y-6">
      {error && (
        <p className="mirror-card flex items-center justify-between p-4 text-sm text-danger">
          {error}
          <button onClick={() => setError(null)} className="text-danger/60 hover:text-danger"><X size={16} /></button>
        </p>
      )}

      <div className="flex flex-wrap items-center justify-between gap-3">
        <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}
          className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
          <option value="">All statuses</option>
          {STATUSES.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        <button onClick={() => setView('create')} className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
          <Plus size={16} /> New Job
        </button>
      </div>

      {(!jobs || jobs.data.length === 0) && (
        <p className="mirror-card p-6 text-center text-sm text-muted">No jobs found.</p>
      )}

      <div className="mirror-card divide-y divide-ink/5">
        {jobs?.data.map((job) => (
          <button
            key={job.id}
            onClick={() => {
              setActiveJobId(job.id);
              setView('detail');
            }}
            className="flex w-full items-center justify-between gap-3 p-4 text-left hover:bg-paper-dim"
          >
            <div>
              <p className="text-sm font-medium text-ink">#{job.id} — {job.customer?.name}</p>
              <p className="text-xs text-muted">{job.job_date} · <span className="capitalize">{job.status.replace('_', ' ')}</span></p>
            </div>
            <div className="text-right">
              <p className="text-sm text-ink">{money(job.balance_due)} due</p>
              <p className="text-xs text-muted">of {money(job.subtotal)}</p>
            </div>
          </button>
        ))}
      </div>
    </div>
  );
}
