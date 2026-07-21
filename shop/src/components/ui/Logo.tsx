import { site } from '../../data/site';

interface LogoProps {
  variant?: 'dark' | 'light';
  className?: string;
}

export default function Logo({ variant = 'dark', className = '' }: LogoProps) {
  const lineColor = variant === 'dark' ? 'rgba(36,26,33,0.35)' : 'rgba(251,247,243,0.4)';

  return (
    <span className={`flex flex-col items-center leading-none ${className}`}>
      <span className="flex items-center gap-2 mb-1">
        <span className="h-px w-5" style={{ backgroundColor: lineColor }} />
        <span className="h-px w-5" style={{ backgroundColor: lineColor }} />
      </span>
      <span
        className={`font-display font-semibold text-2xl tracking-wide ${
          variant === 'dark' ? 'text-gradient-brand' : ''
        }`}
        style={variant === 'light' ? { color: 'var(--color-ivory)' } : undefined}
      >
        {site.name}
      </span>
    </span>
  );
}
