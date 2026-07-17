interface SectionHeadingProps {
  eyebrow: string;
  title: string;
  align?: 'left' | 'center';
  light?: boolean;
  className?: string;
}

export default function SectionHeading({
  eyebrow,
  title,
  align = 'left',
  light = false,
  className = '',
}: SectionHeadingProps) {
  return (
    <div className={`${align === 'center' ? 'text-center' : 'text-left'} ${className}`}>
      <p
        className="eyebrow mb-3"
        style={{ color: light ? 'var(--color-amber-light)' : 'var(--color-magenta)' }}
      >
        {eyebrow}
      </p>
      <h2
        className="font-display text-4xl md:text-5xl leading-tight"
        style={{ color: light ? 'var(--color-ivory)' : 'var(--color-ink)' }}
      >
        {title}
      </h2>
      <span className={`swoosh mt-5 ${align === 'center' ? 'mx-auto' : ''}`} />
    </div>
  );
}
