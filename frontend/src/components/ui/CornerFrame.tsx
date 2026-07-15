interface CornerFrameProps {
  color?: string;
  size?: number;
  inset?: number;
  className?: string;
}

/**
 * The site's signature motif: a hand-drawn-style botanical corner flourish,
 * echoing the gold-foil corners of a wedding invitation card. Used on the
 * hero image, gallery cards, and testimonial cards.
 */
function Flourish({ color = 'var(--color-gold)' }: { color?: string }) {
  return (
    <svg
      width="72"
      height="72"
      viewBox="0 0 72 72"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <path
        d="M4 4 L4 30"
        stroke={color}
        strokeWidth="1.4"
        strokeLinecap="round"
      />
      <path
        d="M4 4 L30 4"
        stroke={color}
        strokeWidth="1.4"
        strokeLinecap="round"
      />
      <path
        d="M4 18 C 20 16, 24 22, 20 30 C 17 36, 8 34, 10 27"
        stroke={color}
        strokeWidth="1.1"
        strokeLinecap="round"
      />
      <path
        d="M18 4 C 16 20, 22 24, 30 20 C 36 17, 34 8, 27 10"
        stroke={color}
        strokeWidth="1.1"
        strokeLinecap="round"
      />
      <circle cx="12" cy="12" r="1.6" fill={color} />
    </svg>
  );
}

export default function CornerFrame({ color, className = '' }: CornerFrameProps) {
  return (
    <div className={`pointer-events-none absolute inset-0 ${className}`} aria-hidden="true">
      <div className="absolute top-0 left-0">
        <Flourish color={color} />
      </div>
      <div className="absolute top-0 right-0 -scale-x-100">
        <Flourish color={color} />
      </div>
      <div className="absolute bottom-0 left-0 -scale-y-100">
        <Flourish color={color} />
      </div>
      <div className="absolute bottom-0 right-0 -scale-x-100 -scale-y-100">
        <Flourish color={color} />
      </div>
    </div>
  );
}
