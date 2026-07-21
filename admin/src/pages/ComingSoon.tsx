import { Construction } from 'lucide-react';

export function ComingSoon({ title }: { title: string }) {
  return (
    <div className="mirror-card flex flex-col items-center justify-center gap-3 px-6 py-20 text-center">
      <span className="flex h-12 w-12 items-center justify-center rounded-full bg-gold-light/40 text-gold">
        <Construction size={22} strokeWidth={1.75} />
      </span>
      <h2 className="font-display text-xl text-ink">{title}</h2>
      <p className="max-w-sm text-sm text-muted">
        This module hasn't been ported from the Blade admin yet. It'll land here once its
        Api/Admin controller and React page are built.
      </p>
    </div>
  );
}
