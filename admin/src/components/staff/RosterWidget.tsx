import { useEffect, useState } from 'react';
import { fetchStaffRoster, type RosterEntry } from '../../lib/api';

const statusStyles: Record<RosterEntry['status'], string> = {
  work: 'bg-emerald-100 text-emerald-700',
  leave: 'bg-amber-100 text-amber-700',
  unscheduled: 'bg-ink/5 text-muted',
};

const statusLabel: Record<RosterEntry['status'], string> = {
  work: 'On shift',
  leave: 'On leave',
  unscheduled: 'Unscheduled',
};

/**
 * SALON-OPS-ENHANCEMENTS.md, "Staff" — "who's on today" only existed in
 * someone's head before this. Small widget at the top of the Staff page;
 * defaults to today but lets you check any date.
 */
export function RosterWidget() {
  const [date, setDate] = useState(() => new Date().toISOString().slice(0, 10));
  const [roster, setRoster] = useState<RosterEntry[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    setIsLoading(true);
    fetchStaffRoster(date)
      .then((res) => setRoster(res.roster))
      .finally(() => setIsLoading(false));
  }, [date]);

  const isToday = date === new Date().toISOString().slice(0, 10);

  return (
    <div className="mirror-card p-4">
      <div className="mb-3 flex items-center justify-between gap-3">
        <h2 className="font-display text-base text-ink">{isToday ? "Today's Roster" : `Roster — ${date}`}</h2>
        <input
          type="date"
          value={date}
          onChange={(e) => setDate(e.target.value)}
          className="rounded-lg border border-ink/10 bg-paper px-2 py-1 text-xs outline-none focus:border-gold"
        />
      </div>
      {isLoading ? (
        <p className="text-xs text-muted">Loading…</p>
      ) : roster.length === 0 ? (
        <p className="text-xs text-muted">No active staff.</p>
      ) : (
        <div className="flex flex-wrap gap-2">
          {roster.map((entry) => (
            <div key={entry.staff_id} className="flex items-center gap-2 rounded-lg border border-ink/10 px-3 py-1.5">
              <span className="text-xs text-ink">{entry.name}</span>
              <span className={`rounded-full px-2 py-0.5 text-[10px] font-medium ${statusStyles[entry.status]}`}>
                {statusLabel[entry.status]}
                {entry.status === 'work' && entry.start_time && entry.end_time
                  ? ` ${entry.start_time.slice(0, 5)}–${entry.end_time.slice(0, 5)}`
                  : ''}
              </span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
