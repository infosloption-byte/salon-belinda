interface LogoProps {
  variant?: 'dark' | 'light';
  className?: string;
}

/**
 * Coded recreation of the "SALON — BELINDA" wordmark: a small letter-spaced
 * eyebrow flanked by dashes, with the salon name in large serif below.
 * Built in CSS rather than an image so it stays crisp at any size and can
 * pick up the brand gradient.
 */
export default function Logo({ variant = 'dark', className = '' }: LogoProps) {
  const lineColor = variant === 'dark' ? 'rgba(36,26,33,0.35)' : 'rgba(251,247,243,0.4)';
  const eyebrowColor = variant === 'dark' ? 'var(--color-magenta)' : 'var(--color-amber-light)';

  return (
    <span className={`flex flex-col items-center leading-none ${className}`}>
      <span className="flex items-center gap-2 mb-1">
        <span className="h-px w-5" style={{ backgroundColor: lineColor }} />
        <span className="eyebrow" style={{ color: eyebrowColor, fontSize: '0.6rem', letterSpacing: '0.32em' }}>
          Salon
        </span>
        <span className="h-px w-5" style={{ backgroundColor: lineColor }} />
      </span>
      <span
        className={`font-display font-semibold text-2xl tracking-wide ${
          variant === 'dark' ? 'text-gradient-brand' : ''
        }`}
        style={variant === 'light' ? { color: 'var(--color-ivory)' } : undefined}
      >
        Belinda
      </span>
    </span>
  );
}
