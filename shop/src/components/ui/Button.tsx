import type { ButtonHTMLAttributes, ReactNode } from 'react';
import { Link } from 'react-router-dom';

interface BaseProps {
  children: ReactNode;
  variant?: 'primary' | 'outline' | 'ghost';
  className?: string;
}

const variantStyles: Record<string, string> = {
  primary:
    'bg-[var(--color-maroon)] text-[var(--color-ivory)] hover:bg-[var(--color-maroon-light)] border border-transparent',
  outline:
    'bg-transparent text-[var(--color-ink)] border border-[var(--color-ink)]/30 hover:border-[var(--color-gold)] hover:text-[var(--color-maroon)]',
  ghost:
    'bg-transparent text-[var(--color-ivory)] border border-[var(--color-ivory)]/40 hover:border-[var(--color-gold)] hover:text-[var(--color-gold-light)]',
};

const base =
  'inline-flex items-center justify-center gap-2 px-7 py-3 text-sm tracking-wide uppercase transition-colors duration-200 whitespace-nowrap disabled:opacity-30 disabled:pointer-events-none';

export function Button({
  children,
  variant = 'primary',
  className = '',
  ...rest
}: BaseProps & ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button className={`${base} ${variantStyles[variant]} ${className}`} {...rest}>
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
    <Link to={to} className={`${base} ${variantStyles[variant]} ${className}`}>
      {children}
    </Link>
  );
}
