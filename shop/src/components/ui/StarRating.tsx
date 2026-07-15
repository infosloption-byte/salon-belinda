import { Star } from 'lucide-react';

export default function StarRating({ rating, reviewCount, size = 13 }: { rating: number; reviewCount?: number; size?: number }) {
  return (
    <div className="flex items-center gap-1.5">
      <div className="flex items-center gap-0.5">
        {Array.from({ length: 5 }).map((_, i) => (
          <Star
            key={i}
            size={size}
            fill={i < Math.round(rating) ? 'var(--color-gold)' : 'none'}
            color="var(--color-gold)"
            strokeWidth={1.5}
          />
        ))}
      </div>
      {reviewCount !== undefined && (
        <span className="text-xs" style={{ color: 'var(--color-ink)', opacity: 0.55 }}>
          ({reviewCount})
        </span>
      )}
    </div>
  );
}
