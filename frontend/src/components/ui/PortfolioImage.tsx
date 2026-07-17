import { useState } from 'react';
import { Sparkles } from 'lucide-react';

interface PortfolioImageProps {
  src: string;
  alt: string;
  className?: string;
}

/**
 * Wraps <img> with a graceful fallback. Real portfolio photography should
 * replace the `src` values in src/data/gallery.ts — until then, or if a
 * link ever breaks, this shows a soft branded placeholder instead of a
 * broken-image icon.
 */
export default function PortfolioImage({ src, alt, className = '' }: PortfolioImageProps) {
  const [failed, setFailed] = useState(false);

  if (failed) {
    return (
      <div
        className={`flex flex-col items-center justify-center gap-2 ${className}`}
        style={{
          background: 'linear-gradient(135deg, var(--color-rose-light), var(--color-ivory-dim))',
        }}
      >
        <Sparkles size={22} color="var(--color-amber)" />
        <span className="eyebrow" style={{ color: 'var(--color-magenta)' }}>
          Photo coming soon
        </span>
      </div>
    );
  }

  return (
    <img
      src={src}
      alt={alt}
      loading="lazy"
      onError={() => setFailed(true)}
      className={className}
    />
  );
}
