import { useEffect, useState, type ChangeEvent } from 'react';
import { Trash2, X } from 'lucide-react';
import {
  assignAppointmentStaff,
  deleteAppointment,
  fetchAppointments,
  updateAppointmentStatus,
  type Appointment,
  type AppointmentStaffOption,
  type AppointmentStatus,
  type PaginatedAppointments,
} from '../lib/api';

const STATUSES: AppointmentStatus[] = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

const statusStyles: Record<AppointmentStatus, string> = {
  pending: 'bg-amber-100 text-amber-700',
  confirmed: 'bg-emerald-100 text-emerald-700',
  completed: 'bg-blue-100 text-blue-700',
  cancelled: 'bg-red-100 text-red-700',
  no_show: 'bg-zinc-200 text-zinc-700',
};

export function Appointments() {
  const [appointments, setAppointments] = useState<PaginatedAppointments | null>(null);
  const [staffList, setStaffList] = useState<AppointmentStaffOption[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [statusFilter, setStatusFilter] = useState('');
  const [search, setSearch] = useState('');

  function load() {
    setIsLoading(true);
    fetchAppointments({ status: statusFilter || undefined, q: search || undefined })
      .then((res) => {
        setAppointments(res.appointments);
        setStaffList(res.staffList);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load appointments.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [statusFilter]);

  async function handleStatusChange(e: ChangeEvent<HTMLSelectElement>, appointment: Appointment) {
    const status = e.target.value as AppointmentStatus;
    try {
      await updateAppointmentStatus(appointment.id, status);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update status.');
    }
  }

  async function handleStaffChange(e: ChangeEvent<HTMLSelectElement>, appointment: Appointment) {
    const staffId = e.target.value ? Number(e.target.value) : null;
    try {
      await assignAppointmentStaff(appointment.id, staffId);
      load();
    } catch (err) {
      // The API returns a 422 with a human-readable conflict message
      // (e.g. "That staff member already has X's appointment at ...").
      setError(err instanceof Error ? err.message : 'Failed to assign staff — that slot may already be booked.');
    }
  }

  async function handleDelete(appointment: Appointment) {
    if (!confirm(`Delete ${appointment.name}'s appointment?`)) return;
    try {
      await deleteAppointment(appointment.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete appointment.');
    }
  }

  if (isLoading && !appointments) return <p className="text-sm text-muted">Loading appointments…</p>;

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
          <span className="mb-1 block text-xs text-muted">Search name / phone / email</span>
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
              <option key={s} value={s}>{s.replace('_', ' ')}</option>
            ))}
          </select>
        </label>
        <button onClick={load} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
          Search
        </button>
      </div>

      {(!appointments || appointments.data.length === 0) && (
        <p className="mirror-card p-6 text-center text-sm text-muted">No appointments found.</p>
      )}

      <div className="mirror-card divide-y divide-ink/5">
        {appointments?.data.map((appointment) => (
          <div key={appointment.id} className="flex flex-wrap items-center justify-between gap-3 p-4">
            <div>
              <p className="text-sm font-medium text-ink">{appointment.name}</p>
              <p className="text-xs text-muted">
                {appointment.service_name} · {appointment.date} at {appointment.time}
                {appointment.service?.duration_minutes ? ` (${appointment.service.duration_minutes} min)` : ''}
              </p>
              <p className="text-xs text-muted">
                {appointment.phone}
                {appointment.email ? ` · ${appointment.email}` : ''}
              </p>
              {appointment.notes && <p className="mt-1 text-xs text-muted italic">"{appointment.notes}"</p>}
            </div>
            <div className="flex shrink-0 flex-wrap items-center gap-2">
              <select
                value={appointment.staff_id ?? ''}
                onChange={(e) => handleStaffChange(e, appointment)}
                className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold"
                title="Assign staff"
              >
                <option value="">Unassigned</option>
                {staffList.map((s) => (
                  <option key={s.id} value={s.id}>{s.name}</option>
                ))}
              </select>
              <select
                value={appointment.status}
                onChange={(e) => handleStatusChange(e, appointment)}
                className={`rounded-lg border border-ink/10 px-2 py-1.5 text-xs font-medium capitalize outline-none ${statusStyles[appointment.status]}`}
              >
                {STATUSES.map((s) => (
                  <option key={s} value={s}>{s.replace('_', ' ')}</option>
                ))}
              </select>
              <button onClick={() => handleDelete(appointment)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                <Trash2 size={14} />
              </button>
            </div>
          </div>
        ))}
      </div>

      {appointments && appointments.last_page > 1 && (
        <p className="text-center text-xs text-muted">
          Page {appointments.current_page} of {appointments.last_page} ({appointments.total} total)
        </p>
      )}
    </div>
  );
}

