import type { ReactNode } from 'react';

type BadgeTone = 'gold' | 'maroon' | 'ivory' | 'muted';

const tones: Record<BadgeTone, { bg: string; color: string }> = {
  gold: { bg: 'var(--color-gold)', color: 'var(--color-ivory)' },
  maroon: { bg: 'var(--color-maroon)', color: 'var(--color-ivory)' },
  ivory: { bg: 'var(--color-ivory)', color: 'var(--color-ink)' },
  muted: { bg: 'rgba(38,34,32,0.85)', color: 'var(--color-ivory)' },
};

export default function Badge({ children, tone = 'gold' }: { children: ReactNode; tone?: BadgeTone }) {
  const t = tones[tone];
  return (
    <span
      className="text-[0.62rem] uppercase tracking-wide px-2 py-1 font-medium"
      style={{ backgroundColor: t.bg, color: t.color }}
    >
      {children}
    </span>
  );
}
