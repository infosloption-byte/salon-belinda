import { Star } from 'lucide-react';

interface StarRatingProps {
  value: number;
  onChange?: (v: number) => void;
  size?: number;
}

export default function StarRating({ value, onChange, size = 18 }: StarRatingProps) {
  const interactive = !!onChange;
  return (
    <div className="flex gap-1" role={interactive ? 'radiogroup' : undefined} aria-label="Rating">
      {[1, 2, 3, 4, 5].map((n) => (
        <button
          key={n}
          type="button"
          disabled={!interactive}
          onClick={() => onChange?.(n)}
          aria-label={`${n} star${n > 1 ? 's' : ''}`}
          className={interactive ? 'cursor-pointer' : 'cursor-default'}
        >
          <Star
            size={size}
            fill={n <= value ? 'var(--color-gold)' : 'transparent'}
            stroke="var(--color-gold)"
            strokeWidth={1.4}
          />
        </button>
      ))}
    </div>
  );
}
