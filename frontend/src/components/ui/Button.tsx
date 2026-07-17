import type { ButtonHTMLAttributes, ReactNode } from 'react';
import { Link } from 'react-router-dom';

interface BaseProps {
  children: ReactNode;
  variant?: 'primary' | 'outline' | 'ghost' | 'gradient';
  className?: string;
}

const variantStyles: Record<string, string> = {
  primary:
    'bg-[var(--color-magenta)] text-[var(--color-ivory)] hover:bg-[var(--color-magenta-light)] border border-transparent',
  outline:
    'bg-transparent text-[var(--color-ink)] border border-[var(--color-ink)]/30 hover:border-[var(--color-amber)] hover:text-[var(--color-magenta)]',
  ghost:
    'bg-transparent text-[var(--color-ivory)] border border-[var(--color-ivory)]/40 hover:border-[var(--color-amber)] hover:text-[var(--color-amber-light)]',
  // Reserved for the one or two highest-stakes calls to action per page —
  // the only place the full brand gradient appears as a fill.
  gradient: 'text-[var(--color-ivory)] border border-transparent hover:brightness-110',
};

const base =
  'inline-flex items-center justify-center gap-2 px-7 py-3 text-sm tracking-wide uppercase transition-colors duration-200 whitespace-nowrap';
const gradientStyle = { backgroundImage: 'var(--gradient-brand)' };

export function Button({
  children,
  variant = 'primary',
  className = '',
  style,
  ...rest
}: BaseProps & ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      className={`${base} ${variantStyles[variant]} ${className}`}
      style={variant === 'gradient' ? { ...gradientStyle, ...style } : style}
      {...rest}
    >
      {children}
    </button>
  );
}

export function LinkButton({
  children,
  to,
  variant = 'primary',
  className = '',
}: BaseProps & { to: string }) {
  return (
    <Link
      to={to}
      className={`${base} ${variantStyles[variant]} ${className}`}
      style={variant === 'gradient' ? gradientStyle : undefined}
    >
      {children}
    </Link>
  );
}
