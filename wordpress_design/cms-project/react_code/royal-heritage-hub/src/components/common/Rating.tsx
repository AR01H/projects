import { cn } from '@/utils/cn';

interface RatingProps {
  value: number;
  reviewCount?: number;
  size?: 'sm' | 'md';
  className?: string;
}

export function Rating({ value, reviewCount, size = 'sm', className }: RatingProps) {
  const starSize = size === 'sm' ? 'w-3.5 h-3.5' : 'w-4.5 h-4.5';
  return (
    <div className={cn('flex items-center gap-1', className)}>
      <div className="flex items-center">
        {[1, 2, 3, 4, 5].map((star) => (
          <svg
            key={star}
            viewBox="0 0 20 20"
            className={cn(starSize, star <= Math.round(value) ? 'fill-[var(--color-secondary)]' : 'fill-[var(--color-border)]')}
          >
            <path d="M10 1.5l2.6 5.6 6.1.7-4.5 4.2 1.2 6.1L10 15l-5.4 3.1 1.2-6.1L1.3 7.8l6.1-.7L10 1.5z" />
          </svg>
        ))}
      </div>
      {reviewCount !== undefined && (
        <span className="text-xs text-[var(--color-text-muted)]">({reviewCount})</span>
      )}
    </div>
  );
}
