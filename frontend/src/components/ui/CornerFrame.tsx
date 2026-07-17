import { useId } from 'react';

interface CornerFrameProps {
  className?: string;
}

/**
 * The site's signature motif on portfolio imagery: a dotted arc in the
 * brand gradient, echoing the ring around the logo's monogram. Quieter
 * than a full frame — it marks a corner rather than boxing the photo in.
 */
function Arc({ gradientId }: { gradientId: string }) {
  const dots = Array.from({ length: 7 });
  const cx = 56;
  const cy = 56;
  const r = 40;
  return (
    <svg width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
      <defs>
        <linearGradient id={gradientId} x1="0" y1="56" x2="56" y2="0">
          <stop offset="0%" stopColor="#7A2560" />
          <stop offset="50%" stopColor="#C23056" />
          <stop offset="100%" stopColor="#F5A623" />
        </linearGradient>
      </defs>
      {dots.map((_, i) => {
        const angle = Math.PI * (1 + 0.5 * (i / (dots.length - 1))); // 180deg -> 270deg
        const x = cx + r * Math.cos(angle);
        const y = cy + r * Math.sin(angle);
        return <circle key={i} cx={x} cy={y} r="1.7" fill={`url(#${gradientId})`} />;
      })}
    </svg>
  );
}

export default function CornerFrame({ className = '' }: CornerFrameProps) {
  const id = useId();
  return (
    <div className={`pointer-events-none absolute inset-0 ${className}`} aria-hidden="true">
      <div className="absolute bottom-0 right-0">
        <Arc gradientId={`corner-arc-${id}`} />
      </div>
    </div>
  );
}
