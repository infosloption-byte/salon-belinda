import { useEffect, useState } from 'react';
import { X } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { fetchActivityLog, type ActivityLogEntry, type ActivityLogUser } from '../lib/api';

function formatWhen(iso: string) {
  return new Date(iso).toLocaleString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

export function ActivityLog() {
  const { user } = useAuth();

  const [logs, setLogs] = useState<ActivityLogEntry[]>([]);
  const [users, setUsers] = useState<ActivityLogUser[]>([]);
  const [actions, setActions] = useState<string[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);

  const [userId, setUserId] = useState('');
  const [action, setAction] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [page, setPage] = useState(1);

  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load(targetPage = page) {
    setIsLoading(true);
    fetchActivityLog({
      user_id: userId || undefined,
      action: action || undefined,
      date_from: dateFrom || undefined,
      date_to: dateTo || undefined,
      page: targetPage,
    })
      .then((res) => {
        setLogs(res.logs.data);
        setCurrentPage(res.logs.current_page);
        setLastPage(res.logs.last_page);
        setTotal(res.logs.total);
        setUsers(res.users);
        setActions(res.actions);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load activity log.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(() => load(1), []);

  function handleFilter() {
    setPage(1);
    load(1);
  }

  function handleClear() {
    setUserId('');
    setAction('');
    setDateFrom('');
    setDateTo('');
    setPage(1);
    fetchActivityLog({ page: 1 })
      .then((res) => {
        setLogs(res.logs.data);
        setCurrentPage(res.logs.current_page);
        setLastPage(res.logs.last_page);
        setTotal(res.logs.total);
        setUsers(res.users);
        setActions(res.actions);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load activity log.'));
  }

  function goToPage(p: number) {
    setPage(p);
    load(p);
  }

  const hasFilters = userId || action || dateFrom || dateTo;

  if (user?.role !== 'admin') {
    return <p className="mirror-card p-6 text-center text-sm text-muted">This section is admin-only.</p>;
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
        <label>
          <span className="mb-1 block text-xs text-muted">Admin</span>
          <select
            value={userId}
            onChange={(e) => setUserId(e.target.value)}
            className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
          >
            <option value="">All</option>
            {users.map((u) => (
              <option key={u.id} value={u.id}>
                {u.name}
              </option>
            ))}
          </select>
        </label>
        <label>
          <span className="mb-1 block text-xs text-muted">Action</span>
          <select
            value={action}
            onChange={(e) => setAction(e.target.value)}
            className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
          >
            <option value="">All</option>
            {actions.map((a) => (
              <option key={a} value={a}>
                {a}
              </option>
            ))}
          </select>
        </label>
        <label>
          <span className="mb-1 block text-xs text-muted">From</span>
          <input
            type="date"
            value={dateFrom}
            onChange={(e) => setDateFrom(e.target.value)}
            className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
          />
        </label>
        <label>
          <span className="mb-1 block text-xs text-muted">To</span>
          <input
            type="date"
            value={dateTo}
            onChange={(e) => setDateTo(e.target.value)}
            className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
          />
        </label>
        <button onClick={handleFilter} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
          Filter
        </button>
        {hasFilters && (
          <button onClick={handleClear} className="rounded-lg border border-ink/10 px-4 py-2 text-sm text-ink hover:bg-paper-dim">
            Clear
          </button>
        )}
      </div>

      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : (
        <div className="mirror-card overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-ink/10 text-left">
                <th className="p-4">When</th>
                <th className="p-4">Admin</th>
                <th className="p-4">Action</th>
                <th className="p-4">Details</th>
              </tr>
            </thead>
            <tbody>
              {logs.length === 0 ? (
                <tr>
                  <td colSpan={4} className="p-8 text-center text-sm text-muted">
                    No activity recorded yet.
                  </td>
                </tr>
              ) : (
                logs.map((log) => (
                  <tr key={log.id} className="border-b border-ink/5">
                    <td className="whitespace-nowrap p-4">{formatWhen(log.created_at)}</td>
                    <td className="p-4">{log.user?.name ?? 'Unknown'}</td>
                    <td className="p-4">
                      <span className="rounded-full bg-gold-light/40 px-2 py-1 text-xs font-medium text-ink">
                        {log.action}
                      </span>
                    </td>
                    <td className="p-4 text-muted">{log.description}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      )}

      {lastPage > 1 && (
        <div className="flex items-center justify-center gap-3 text-sm">
          <button
            disabled={currentPage <= 1}
            onClick={() => goToPage(currentPage - 1)}
            className="rounded-lg border border-ink/10 px-3 py-1.5 text-ink disabled:opacity-40"
          >
            Prev
          </button>
          <span className="text-xs text-muted">
            Page {currentPage} of {lastPage} ({total} total)
          </span>
          <button
            disabled={currentPage >= lastPage}
            onClick={() => goToPage(currentPage + 1)}
            className="rounded-lg border border-ink/10 px-3 py-1.5 text-ink disabled:opacity-40"
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
}
