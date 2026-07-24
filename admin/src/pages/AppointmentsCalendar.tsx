import { useEffect, useMemo, useState } from 'react';
import { ChevronLeft, ChevronRight, X, Clock3 } from 'lucide-react';
import {
  assignAppointmentStaff,
  deleteAppointment,
  fetchAppointmentsCalendar,
  updateAppointmentStatus,
  type Appointment,
  type AppointmentStaffOption,
  type AppointmentStatus,
} from '../lib/api';

const STATUSES: AppointmentStatus[] = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

const statusStyles: Record<AppointmentStatus, string> = {
  pending: 'bg-amber-100 text-amber-800 border-amber-300',
  confirmed: 'bg-emerald-100 text-emerald-800 border-emerald-300',
  completed: 'bg-blue-100 text-blue-800 border-blue-300',
  cancelled: 'bg-red-100 text-red-800 border-red-300 line-through opacity-70',
  no_show: 'bg-zinc-200 text-zinc-700 border-zinc-300',
};

const DEFAULT_DURATION_MINUTES = 60;
const PX_PER_MINUTE = 1.1;
const DEFAULT_START_HOUR = 9;
const DEFAULT_END_HOUR = 19;

/**
 * `time` is a free-text string column (see SALON-OPS-ENHANCEMENTS.md —
 * this predates the booking-engine work), so parse defensively rather
 * than assuming a single format. Returns minutes since midnight, or
 * null if it genuinely can't be parsed (those appointments get listed
 * separately below the grid instead of silently vanishing).
 */
function parseTimeToMinutes(time: string): number | null {
  const match = time.trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM|am|pm)?$/);
  if (!match) return null;

  let hours = Number(match[1]);
  const minutes = Number(match[2]);
  const meridiem = match[3]?.toUpperCase();

  if (meridiem === 'PM' && hours < 12) hours += 12;
  if (meridiem === 'AM' && hours === 12) hours = 0;
  if (hours > 23 || minutes > 59) return null;

  return hours * 60 + minutes;
}

function formatHourLabel(hour: number): string {
  const h = hour % 24;
  const period = h < 12 ? 'AM' : 'PM';
  const display = h % 12 === 0 ? 12 : h % 12;
  return `${display} ${period}`;
}

function todayIso(): string {
  return new Date().toISOString().slice(0, 10);
}

function shiftDate(iso: string, days: number): string {
  const d = new Date(`${iso}T00:00:00`);
  d.setDate(d.getDate() + days);
  return d.toISOString().slice(0, 10);
}

interface PositionedAppointment {
  appointment: Appointment;
  top: number;
  height: number;
  startMinutes: number;
  endMinutes: number;
}

export function AppointmentsCalendar() {
  const [date, setDate] = useState(todayIso());
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [staffList, setStaffList] = useState<AppointmentStaffOption[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [selected, setSelected] = useState<Appointment | null>(null);

  function load() {
    fetchAppointmentsCalendar(date)
      .then((res) => {
        setAppointments(res.appointments);
        setStaffList(res.staffList);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load the day.'));
  }

  useEffect(load, [date]);

  const { startHour, endHour, unparsed, byStaff } = useMemo(() => {
    let minStart = DEFAULT_START_HOUR * 60;
    let maxEnd = DEFAULT_END_HOUR * 60;
    const unparsedList: Appointment[] = [];
    const positioned = new Map<number | 'unassigned', PositionedAppointment[]>();

    for (const appt of appointments) {
      const startMinutes = parseTimeToMinutes(appt.time);
      if (startMinutes === null) {
        unparsedList.push(appt);
        continue;
      }
      const duration = appt.service?.duration_minutes ?? DEFAULT_DURATION_MINUTES;
      const endMinutes = startMinutes + duration;
      minStart = Math.min(minStart, Math.floor(startMinutes / 60) * 60);
      maxEnd = Math.max(maxEnd, Math.ceil(endMinutes / 60) * 60);

      const key = appt.staff_id ?? 'unassigned';
      const list = positioned.get(key) ?? [];
      list.push({ appointment: appt, top: 0, height: 0, startMinutes, endMinutes });
      positioned.set(key, list);
    }

    for (const list of positioned.values()) {
      for (const item of list) {
        item.top = (item.startMinutes - minStart) * PX_PER_MINUTE;
        item.height = Math.max((item.endMinutes - item.startMinutes) * PX_PER_MINUTE, 22);
      }
    }

    return {
      startHour: minStart / 60,
      endHour: maxEnd / 60,
      unparsed: unparsedList,
      byStaff: positioned,
    };
  }, [appointments]);

  const hourMarks = useMemo(() => {
    const marks: number[] = [];
    for (let h = startHour; h <= endHour; h++) marks.push(h);
    return marks;
  }, [startHour, endHour]);

  const gridHeight = (endHour - startHour) * 60 * PX_PER_MINUTE;
  const columns: (AppointmentStaffOption | { id: 'unassigned'; name: string; role_title: null })[] = [
    ...staffList,
    { id: 'unassigned', name: 'Unassigned', role_title: null },
  ];

  async function handleStatusChange(appointment: Appointment, status: AppointmentStatus) {
    try {
      await updateAppointmentStatus(appointment.id, status);
      setSelected(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update status.');
    }
  }

  async function handleStaffChange(appointment: Appointment, staffId: number | null) {
    try {
      await assignAppointmentStaff(appointment.id, staffId);
      setSelected(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to assign staff — that slot may already be booked.");
    }
  }

  async function handleDelete(appointment: Appointment) {
    if (!confirm(`Delete ${appointment.name}'s appointment?`)) return;
    try {
      await deleteAppointment(appointment.id);
      setSelected(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete appointment.');
    }
  }

  return (
    <div className="space-y-4">
      {error && (
        <p className="mirror-card flex items-center justify-between p-4 text-sm text-danger">
          {error}
          <button onClick={() => setError(null)} className="text-danger/60 hover:text-danger"><X size={16} /></button>
        </p>
      )}

      <div className="mirror-card flex items-center justify-between gap-3 p-3">
        <div className="flex items-center gap-2">
          <button onClick={() => setDate(shiftDate(date, -1))} className="rounded-lg border border-ink/10 p-1.5 hover:bg-paper-dim">
            <ChevronLeft size={16} />
          </button>
          <input
            type="date"
            value={date}
            onChange={(e) => setDate(e.target.value)}
            className="rounded-lg border border-ink/10 bg-paper px-3 py-1.5 text-sm outline-none focus:border-gold"
          />
          <button onClick={() => setDate(shiftDate(date, 1))} className="rounded-lg border border-ink/10 p-1.5 hover:bg-paper-dim">
            <ChevronRight size={16} />
          </button>
          <button onClick={() => setDate(todayIso())} className="rounded-lg border border-ink/10 px-3 py-1.5 text-xs hover:bg-paper-dim">
            Today
          </button>
        </div>
        <p className="text-xs text-muted">{appointments.length} appointment{appointments.length === 1 ? '' : 's'} this day</p>
      </div>

      <div className="mirror-card overflow-x-auto p-3">
        <div className="flex" style={{ minWidth: `${80 + columns.length * 160}px` }}>
          {/* Time gutter */}
          <div className="relative shrink-0" style={{ width: 64, height: gridHeight }}>
            {hourMarks.map((h) => (
              <div
                key={h}
                className="absolute right-2 -translate-y-1/2 text-[11px] text-muted"
                style={{ top: (h * 60 - startHour * 60) * PX_PER_MINUTE }}
              >
                {formatHourLabel(h)}
              </div>
            ))}
          </div>

          {/* Staff columns */}
          {columns.map((col) => (
            <div key={col.id} className="relative shrink-0 border-l border-ink/5" style={{ width: 160, height: gridHeight }}>
              <div className="sticky top-0 z-10 -mt-3 mb-1 bg-paper px-2 py-1 text-xs font-medium text-ink">
                {col.name}
              </div>
              {/* Hour gridlines */}
              {hourMarks.map((h) => (
                <div
                  key={h}
                  className="absolute left-0 right-0 border-t border-ink/5"
                  style={{ top: (h * 60 - startHour * 60) * PX_PER_MINUTE }}
                />
              ))}
              {/* Appointment blocks */}
              {(byStaff.get(col.id === 'unassigned' ? 'unassigned' : Number(col.id)) ?? []).map(({ appointment, top, height }) => (
                <button
                  key={appointment.id}
                  onClick={() => setSelected(appointment)}
                  title={appointment.is_waitlisted ? 'Waitlisted — no staff was free when this was booked' : undefined}
                  className={`absolute left-1 right-1 overflow-hidden rounded-md border px-2 py-1 text-left text-[11px] leading-tight ${statusStyles[appointment.status]} ${appointment.is_waitlisted ? 'ring-2 ring-amber-500' : ''}`}
                  style={{ top, height }}
                >
                  <p className="flex items-center gap-1 truncate font-medium">
                    {appointment.is_waitlisted && <Clock3 size={10} className="shrink-0" />}
                    {appointment.name}
                  </p>
                  <p className="truncate">{appointment.time} · {appointment.service_name}</p>
                </button>
              ))}
            </div>
          ))}
        </div>
      </div>

      {unparsed.length > 0 && (
        <div className="mirror-card p-4">
          <p className="mb-2 text-xs text-muted">
            {unparsed.length} appointment{unparsed.length === 1 ? '' : 's'} this day {unparsed.length === 1 ? "has" : "have"} a time that couldn't be placed on the grid — shown here instead:
          </p>
          <div className="divide-y divide-ink/5">
            {unparsed.map((appt) => (
              <button key={appt.id} onClick={() => setSelected(appt)} className="flex w-full items-center justify-between py-2 text-left text-sm hover:bg-paper-dim">
                <span className="flex items-center gap-1.5">
                  {appt.is_waitlisted && <Clock3 size={12} className="text-amber-600" />}
                  {appt.name} — {appt.service_name}
                </span>
                <span className="text-xs text-muted">"{appt.time}"</span>
              </button>
            ))}
          </div>
        </div>
      )}

      {selected && (
        <div className="mirror-card space-y-3 p-4">
          <div className="flex items-center justify-between">
            <p className="flex items-center gap-2 text-sm font-medium text-ink">
              {selected.name} — {selected.service_name}
              {selected.is_waitlisted && (
                <span className="flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700">
                  <Clock3 size={10} /> Waitlisted
                </span>
              )}
            </p>
            <button onClick={() => setSelected(null)} className="text-muted hover:text-ink"><X size={16} /></button>
          </div>
          <p className="text-xs text-muted">
            {selected.date} at {selected.time}
            {selected.service?.duration_minutes ? ` (${selected.service.duration_minutes} min)` : ''}
            {' · '}{selected.phone}{selected.email ? ` · ${selected.email}` : ''}
          </p>
          {selected.notes && <p className="text-xs italic text-muted">"{selected.notes}"</p>}
          <div className="flex flex-wrap items-center gap-2">
            <select
              value={selected.staff_id ?? ''}
              onChange={(e) => handleStaffChange(selected, e.target.value ? Number(e.target.value) : null)}
              className="rounded-lg border border-ink/10 bg-paper px-2 py-1.5 text-xs outline-none focus:border-gold"
              title="Assign staff — qualified staff for this service are listed first"
            >
              <option value="">Unassigned</option>
              {[...staffList]
                .sort((a, b) => {
                  const aQualified = !selected.service_id || a.service_ids.includes(selected.service_id);
                  const bQualified = !selected.service_id || b.service_ids.includes(selected.service_id);
                  if (aQualified === bQualified) return a.name.localeCompare(b.name);
                  return aQualified ? -1 : 1;
                })
                .map((s) => {
                  const qualified = !selected.service_id || s.service_ids.includes(selected.service_id);
                  return (
                    <option key={s.id} value={s.id}>
                      {s.name}{!qualified ? ' (not marked qualified)' : ''}
                    </option>
                  );
                })}
            </select>
            <select
              value={selected.status}
              onChange={(e) => handleStatusChange(selected, e.target.value as AppointmentStatus)}
              className="rounded-lg border border-ink/10 px-2 py-1.5 text-xs font-medium capitalize outline-none"
            >
              {STATUSES.map((s) => <option key={s} value={s}>{s.replace('_', ' ')}</option>)}
            </select>
            <button onClick={() => handleDelete(selected)} className="rounded-lg border border-ink/10 px-3 py-1.5 text-xs text-danger hover:bg-danger-bg">
              Delete
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
