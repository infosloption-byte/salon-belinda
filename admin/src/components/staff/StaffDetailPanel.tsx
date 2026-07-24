import { useEffect, useState, type FormEvent } from 'react';
import { Trash2 } from 'lucide-react';
import {
  createStaffShift,
  deleteStaffShift,
  fetchStaffPerformance,
  fetchStaffServices,
  fetchStaffShifts,
  syncStaffServices,
  type QualifiableService,
  type Staff,
  type StaffPerformance,
  type StaffShift,
} from '../../lib/api';

const TABS = ['Schedule', 'Qualified Services', 'Performance'] as const;
type Tab = (typeof TABS)[number];

function ScheduleTab({ staff }: { staff: Staff }) {
  const [shifts, setShifts] = useState<StaffShift[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchStaffShifts({ staff_id: staff.id, date_from: new Date().toISOString().slice(0, 10) })
      .then((res) => setShifts(res.shifts))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load schedule.'))
      .finally(() => setIsLoading(false));
  }

  useEffect(load, [staff.id]);

  async function handleAdd(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const type = String(form.get('type')) as 'work' | 'leave';
    try {
      await createStaffShift({
        staff_id: staff.id,
        date: String(form.get('date')),
        type,
        start_time: type === 'work' ? String(form.get('start_time') || '') : undefined,
        end_time: type === 'work' ? String(form.get('end_time') || '') : undefined,
        notes: String(form.get('notes') || ''),
      });
      (e.target as HTMLFormElement).reset();
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add schedule entry.');
    }
  }

  async function handleDelete(id: number) {
    try {
      await deleteStaffShift(id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to remove schedule entry.');
    }
  }

  return (
    <div className="space-y-3">
      {error && <p className="text-xs text-danger">{error}</p>}
      <form onSubmit={handleAdd} className="grid grid-cols-2 gap-2 rounded-lg border border-ink/10 p-3 sm:grid-cols-5">
        <input name="date" type="date" required defaultValue={new Date().toISOString().slice(0, 10)} className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold" />
        <select name="type" className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold">
          <option value="work">Work</option>
          <option value="leave">Leave</option>
        </select>
        <input name="start_time" type="time" placeholder="Start" className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold" />
        <input name="end_time" type="time" placeholder="End" className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold" />
        <button type="submit" className="rounded-lg bg-wine px-3 py-1.5 text-xs font-medium text-paper hover:bg-wine-light">
          Add
        </button>
        <input name="notes" placeholder="Notes (optional)" className="col-span-2 rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold sm:col-span-5" />
      </form>

      {isLoading ? (
        <p className="text-xs text-muted">Loading…</p>
      ) : shifts.length === 0 ? (
        <p className="text-xs text-muted">No upcoming schedule entries.</p>
      ) : (
        <div className="divide-y divide-ink/5 rounded-lg border border-ink/10">
          {shifts.map((s) => (
            <div key={s.id} className="flex items-center justify-between px-3 py-2 text-xs">
              <span className="text-ink">
                {s.date} · <span className="capitalize">{s.type}</span>
                {s.type === 'work' && s.start_time && s.end_time ? ` ${s.start_time.slice(0, 5)}–${s.end_time.slice(0, 5)}` : ''}
                {s.notes ? ` · ${s.notes}` : ''}
              </span>
              <button onClick={() => handleDelete(s.id)} className="text-danger/70 hover:text-danger">
                <Trash2 size={13} />
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

function QualifiedServicesTab({ staff }: { staff: Staff }) {
  const [services, setServices] = useState<QualifiableService[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    fetchStaffServices(staff.id)
      .then((res) => setServices(res.services))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load services.'))
      .finally(() => setIsLoading(false));
  }, [staff.id]);

  function toggle(id: number) {
    setServices((prev) => prev.map((s) => (s.id === id ? { ...s, qualified: !s.qualified } : s)));
  }

  async function handleSave() {
    setSaving(true);
    setMessage(null);
    try {
      await syncStaffServices(staff.id, services.filter((s) => s.qualified).map((s) => s.id));
      setMessage('Qualified services updated.');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save.');
    } finally {
      setSaving(false);
    }
  }

  if (isLoading) return <p className="text-xs text-muted">Loading…</p>;

  return (
    <div className="space-y-3">
      {error && <p className="text-xs text-danger">{error}</p>}
      {message && <p className="text-xs text-emerald-700">{message}</p>}
      <div className="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
        {services.map((s) => (
          <label key={s.id} className="flex items-center gap-2 rounded-lg border border-ink/10 px-3 py-1.5 text-xs">
            <input type="checkbox" checked={s.qualified} onChange={() => toggle(s.id)} className="accent-wine" />
            <span className="text-ink">{s.name}</span>
            {s.category && <span className="text-muted">· {s.category}</span>}
          </label>
        ))}
      </div>
      <button onClick={handleSave} disabled={saving} className="rounded-lg bg-wine px-4 py-2 text-xs font-medium text-paper hover:bg-wine-light disabled:opacity-60">
        {saving ? 'Saving…' : 'Save Qualified Services'}
      </button>
    </div>
  );
}

function PerformanceTab({ staff }: { staff: Staff }) {
  const [data, setData] = useState<StaffPerformance | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchStaffPerformance(staff.id).then(setData).finally(() => setIsLoading(false));
  }, [staff.id]);

  if (isLoading || !data) return <p className="text-xs text-muted">Loading…</p>;

  const stats = [
    { label: 'Bookings Completed', value: data.bookingsCompleted },
    { label: 'No-Show Rate', value: `${data.noShowRate}%` },
    { label: 'Services Performed', value: data.servicesPerformed },
    { label: 'Avg. Ticket Size', value: `LKR ${Math.round(data.averageTicket).toLocaleString('en-US')}` },
    { label: 'Total Commission', value: `LKR ${data.totalCommission.toLocaleString('en-US')}` },
  ];

  return (
    <div>
      <p className="mb-3 text-xs text-muted">Last 90 days ({data.from} – {data.to})</p>
      <div className="grid grid-cols-2 gap-2 sm:grid-cols-5">
        {stats.map((s) => (
          <div key={s.label} className="rounded-lg border border-ink/10 p-3 text-center">
            <p className="font-display text-lg text-ink">{s.value}</p>
            <p className="text-[10px] text-muted">{s.label}</p>
          </div>
        ))}
      </div>
    </div>
  );
}

export function StaffDetailPanel({ staff }: { staff: Staff }) {
  const [tab, setTab] = useState<Tab>('Schedule');

  return (
    <div className="border-t border-ink/5 bg-paper-dim/40 p-4">
      <div className="mb-3 flex gap-2">
        {TABS.map((t) => (
          <button
            key={t}
            onClick={() => setTab(t)}
            className={`rounded-lg px-2.5 py-1 text-[11px] font-medium ${
              tab === t ? 'bg-wine text-paper' : 'border border-ink/10 text-ink hover:bg-paper-dim'
            }`}
          >
            {t}
          </button>
        ))}
      </div>
      {tab === 'Schedule' && <ScheduleTab staff={staff} />}
      {tab === 'Qualified Services' && <QualifiedServicesTab staff={staff} />}
      {tab === 'Performance' && <PerformanceTab staff={staff} />}
    </div>
  );
}
