import type { ReactNode } from 'react';
import { useRef, useState, useEffect } from 'react';

interface HorizontalScrollerProps {
  children: ReactNode;
  /** Show arrows always, not just on hover */
  showArrows?: boolean;
  /** Gap between items in px */
  gap?: number;
}

export function HorizontalScroller({ children, showArrows = true, gap = 16 }: HorizontalScrollerProps) {
  const ref = useRef<HTMLDivElement>(null);
  const [canScrollLeft, setCanScrollLeft] = useState(false);
  const [canScrollRight, setCanScrollRight] = useState(true);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const check = () => {
      setCanScrollLeft(el.scrollLeft > 2);
      setCanScrollRight(el.scrollLeft < el.scrollWidth - el.clientWidth - 2);
    };
    check();
    el.addEventListener('scroll', check, { passive: true });
    window.addEventListener('resize', check);
    return () => {
      el.removeEventListener('scroll', check);
      window.removeEventListener('resize', check);
    };
  }, []);

  function scroll(dir: 'left' | 'right') {
    const el = ref.current;
    if (!el) return;
    const scrollAmount = el.clientWidth * 0.7;
    el.scrollBy({ left: dir === 'left' ? -scrollAmount : scrollAmount, behavior: 'smooth' });
  }

  return (
    <div className="group/scroller relative">
      {/* Left arrow */}
      {showArrows && (
        <button
          onClick={() => scroll('left')}
          disabled={!canScrollLeft}
          aria-label="Scroll left"
          className="absolute -left-2 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-[var(--color-border)] bg-[var(--color-bg-light)]/90 shadow-md backdrop-blur-sm transition-all duration-200 hover:bg-[var(--color-bg-light)] hover:shadow-lg disabled:cursor-default disabled:opacity-0 disabled:pointer-events-none sm:-left-4 sm:h-10 sm:w-10"
        >
          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="var(--color-text-primary)" strokeWidth="2.5">
            <path d="M15 18l-6-6 6-6" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
      )}

      <div
        ref={ref}
        className="flex items-stretch overflow-x-auto scroll-smooth pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        style={{ gap: `${gap}px` }}
      >
        {children}
      </div>

      {/* Right arrow */}
      {showArrows && (
        <button
          onClick={() => scroll('right')}
          disabled={!canScrollRight}
          aria-label="Scroll right"
          className="absolute -right-2 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-[var(--color-border)] bg-[var(--color-bg-light)]/90 shadow-md backdrop-blur-sm transition-all duration-200 hover:bg-[var(--color-bg-light)] hover:shadow-lg disabled:cursor-default disabled:opacity-0 disabled:pointer-events-none sm:-right-4 sm:h-10 sm:w-10"
        >
          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="var(--color-text-primary)" strokeWidth="2.5">
            <path d="M9 18l6-6-6-6" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
      )}

      {/* Fade edges */}
      {showArrows && canScrollLeft && (
        <div className="pointer-events-none absolute left-0 top-0 bottom-2 w-8 bg-gradient-to-r from-[var(--color-bg)] to-transparent sm:w-12" />
      )}
      {showArrows && canScrollRight && (
        <div className="pointer-events-none absolute right-0 top-0 bottom-2 w-8 bg-gradient-to-l from-[var(--color-bg)] to-transparent sm:w-12" />
      )}
    </div>
  );
}
