import { cn } from '@/utils/cn';

export function Skeleton({ className }: { className?: string }) {
  return (
    <div
      className={cn(
        'animate-pulse rounded-[var(--radius-card)] bg-[var(--color-border-soft)]',
        className
      )}
    />
  );
}

export function ProductCardSkeleton() {
  return (
    <div className="flex flex-col gap-3">
      <Skeleton className="aspect-[4/5] w-full" />
      <Skeleton className="h-4 w-3/4" />
      <Skeleton className="h-4 w-1/2" />
    </div>
  );
}

export function PageLoader() {
  return (
    <div className="flex min-h-[50vh] w-full items-center justify-center">
      <div className="flex flex-col items-center gap-4">
        <div className="h-10 w-10 animate-spin rounded-full border-2 border-[var(--color-secondary)] border-t-transparent" />
        <p className="font-display text-sm tracking-widest text-[var(--color-text-muted)]">LOADING</p>
      </div>
    </div>
  );
}
