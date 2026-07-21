import { CalendarCheck, Users, ShoppingBag, Wallet } from 'lucide-react';
import { StatCard } from '../components/ui/StatCard';

// Placeholder data — wire this up to /api/admin/dashboard once the
// Api/Admin/DashboardController is ported in Phase 2.
const stats = [
  { label: "Today's Appointments", value: '8', icon: CalendarCheck, trend: '+2 vs yesterday', trendDirection: 'up' as const },
  { label: 'Active Customers', value: '346', icon: Users, trend: '+12 this month', trendDirection: 'up' as const },
  { label: 'Pending Orders', value: '5', icon: ShoppingBag, trend: '2 awaiting payment', trendDirection: 'neutral' as const },
  { label: "This Month's Revenue", value: 'LKR 284,500', icon: Wallet, trend: '+8.4%', trendDirection: 'up' as const },
];

const recentActivity = [
  { time: '09:12 AM', event: 'New appointment booked', detail: 'Bridal package — Nadeesha P.' },
  { time: '08:47 AM', event: 'Order marked shipped', detail: 'Order #1042 — hair care bundle' },
  { time: 'Yesterday', event: 'New testimonial submitted', detail: 'Awaiting approval' },
  { time: 'Yesterday', event: 'Staff schedule updated', detail: 'Kasuni — Thursday shift' },
];

export function Dashboard() {
  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((s) => (
          <StatCard key={s.label} {...s} />
        ))}
      </div>

      <div className="mirror-card p-5">
        <h2 className="font-display text-lg text-ink">Recent Activity</h2>
        <div className="mt-3 divide-y divide-ink/5">
          {recentActivity.map((item, i) => (
            <div key={i} className="flex items-center justify-between gap-4 py-3">
              <div>
                <p className="text-sm text-ink">{item.event}</p>
                <p className="text-xs text-muted">{item.detail}</p>
              </div>
              <span className="whitespace-nowrap text-xs text-muted">{item.time}</span>
            </div>
          ))}
        </div>
      </div>

      <p className="text-center text-xs text-muted">
        Showing placeholder data — connect this page to the API once the dashboard endpoint is ported.
      </p>
    </div>
  );
}
