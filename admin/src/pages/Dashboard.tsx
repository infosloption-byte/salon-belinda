import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { CalendarCheck, Users, ShoppingBag, Wallet, AlertTriangle } from 'lucide-react';
import { StatCard } from '../components/ui/StatCard';
import { fetchDashboard, type DashboardActivity, type DashboardAlert, type DashboardStats } from '../lib/api';

function formatCurrency(n: number) {
  return `LKR ${n.toLocaleString('en-US')}`;
}

function formatTime(iso: string) {
  const d = new Date(iso);
  const isToday = d.toDateString() === new Date().toDateString();
  return isToday
    ? d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
    : d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

export function Dashboard() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [alerts, setAlerts] = useState<DashboardAlert[]>([]);
  const [activity, setActivity] = useState<DashboardActivity[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchDashboard()
      .then((res) => {
        // Staff logins get a lighter payload with no stats — they belong on
        // the Jobs area, not this admin-only dashboard. (Jobs page ports in
        // a later Phase 2 step; for now just avoid rendering stat cards
        // with data staff shouldn't see.)
        if (res.role === 'staff') {
          setError('This dashboard is admin-only. Jobs area is coming in a later step.');
          return;
        }
        setStats(res.stats ?? null);
        setAlerts(res.alerts ?? []);
        setActivity(res.recentActivity ?? []);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load dashboard.'))
      .finally(() => setIsLoading(false));
  }, []);

  if (isLoading) {
    return <p className="text-sm text-muted">Loading dashboard…</p>;
  }

  if (error) {
    return <p className="mirror-card p-5 text-sm text-danger">{error}</p>;
  }

  if (!stats) {
    return null;
  }

  const cards = [
    {
      label: "Today's Appointments",
      value: String(stats.todayAppointments),
      icon: CalendarCheck,
      trend: `${stats.pendingAppointments} pending`,
      trendDirection: 'neutral' as const,
    },
    {
      label: 'Active Customers',
      value: stats.activeCustomers.toLocaleString('en-US'),
      icon: Users,
      trendDirection: 'neutral' as const,
    },
    {
      label: 'Pending Orders',
      value: String(stats.pendingOrders),
      icon: ShoppingBag,
      trendDirection: 'neutral' as const,
    },
    {
      label: "This Month's Revenue",
      value: formatCurrency(stats.monthRevenue),
      icon: Wallet,
      trend: `${formatCurrency(stats.todayRevenue)} today`,
      trendDirection: 'up' as const,
    },
  ];

  return (
    <div className="space-y-6">
      {alerts.length > 0 && (
        <div className="space-y-2">
          {alerts.map((alert) => (
            <Link
              key={alert.type}
              to="/reports"
              className="mirror-card flex items-center gap-3 border border-amber-500/30 bg-amber-50 p-4 text-sm text-amber-900 hover:bg-amber-100"
            >
              <AlertTriangle size={18} className="shrink-0 text-amber-600" />
              <span>{alert.message}</span>
              <span className="ml-auto whitespace-nowrap text-xs underline">View report &rarr;</span>
            </Link>
          ))}
        </div>
      )}

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {cards.map((c) => (
          <StatCard key={c.label} {...c} />
        ))}
      </div>

      <div className="mirror-card p-5">
        <h2 className="font-display text-lg text-ink">Recent Activity</h2>
        <div className="mt-3 divide-y divide-ink/5">
          {activity.length === 0 && <p className="py-3 text-sm text-muted">No activity yet.</p>}
          {activity.map((item) => (
            <div key={item.id} className="flex items-center justify-between gap-4 py-3">
              <div>
                <p className="text-sm text-ink">{item.event}</p>
                {item.user && <p className="text-xs text-muted">{item.user}</p>}
              </div>
              <span className="whitespace-nowrap text-xs text-muted">{formatTime(item.time)}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
