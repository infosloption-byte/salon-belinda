import type { LucideIcon } from 'lucide-react';

interface StatCardProps {
  label: string;
  value: string;
  icon: LucideIcon;
  trend?: string;
  trendDirection?: 'up' | 'down' | 'neutral';
}

export function StatCard({ label, value, icon: Icon, trend, trendDirection = 'neutral' }: StatCardProps) {
  const trendColor =
    trendDirection === 'up'
      ? 'text-success'
      : trendDirection === 'down'
        ? 'text-danger'
        : 'text-muted';

  return (
    <div className="mirror-card p-5">
      <div className="flex items-start justify-between">
        <p className="text-sm text-muted">{label}</p>
        <span className="flex h-9 w-9 items-center justify-center rounded-full bg-rose-light text-rose">
          <Icon size={17} strokeWidth={1.75} />
        </span>
      </div>
      <p className="mt-3 font-display text-2xl text-ink">{value}</p>
      {trend && <p className={`mt-1 text-xs ${trendColor}`}>{trend}</p>}
    </div>
  );
}
