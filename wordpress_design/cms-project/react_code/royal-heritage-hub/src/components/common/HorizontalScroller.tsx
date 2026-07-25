import type { ReactNode } from 'react';
import { useRef } from 'react';

export function HorizontalScroller({ children }: { children: ReactNode }) {
  const ref = useRef<HTMLDivElement>(null);

  function scroll(dir: 'left' | 'right') {
    ref.current?.scrollBy({ left: dir === 'left' ? -320 : 320, behavior: 'smooth' });
  }

  return (
    <div className="group/scroller relative">
      <button
        onClick={() => scroll('left')}
        aria-label="Scroll left"
        className="absolute -left-3 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-[var(--color-bg-light)] shadow-[var(--shadow-card)] opacity-0 transition-opacity group-hover/scroller:opacity-100 lg:flex"
      >
        <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="var(--color-dark)" strokeWidth="2">
          <path d="M15 18l-6-6 6-6" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </button>
      <div
        ref={ref}
        className="flex gap-4 overflow-x-auto scroll-smooth pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      >
        {children}
      </div>
      <button
        onClick={() => scroll('right')}
        aria-label="Scroll right"
        className="absolute -right-3 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-[var(--color-bg-light)] shadow-[var(--shadow-card)] opacity-0 transition-opacity group-hover/scroller:opacity-100 lg:flex"
      >
        <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="var(--color-dark)" strokeWidth="2">
          <path d="M9 18l6-6-6-6" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </button>
    </div>
  );
}
