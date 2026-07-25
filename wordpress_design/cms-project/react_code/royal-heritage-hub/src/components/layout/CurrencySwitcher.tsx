import { useEffect, useRef, useState } from 'react';
import { SUPPORTED_CURRENCIES } from '@/config/currency';
import { useCurrencyStore } from '@/store/useCurrencyStore';
import { cn } from '@/utils/cn';

export function CurrencySwitcher() {
  const [open, setOpen] = useState(false);
  const currencyCode = useCurrencyStore((s) => s.currencyCode);
  const setCurrency = useCurrencyStore((s) => s.setCurrency);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function onClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener('mousedown', onClickOutside);
    return () => document.removeEventListener('mousedown', onClickOutside);
  }, []);

  const active = SUPPORTED_CURRENCIES.find((c) => c.code === currencyCode) ?? SUPPORTED_CURRENCIES[0];

  return (
    <div ref={ref} className="relative">
      <button
        onClick={() => setOpen((v) => !v)}
        className="flex items-center gap-1 rounded-[var(--radius-pill)] border border-[var(--color-border)] px-2.5 py-1.5 text-xs font-medium text-[var(--color-text-secondary)] transition-colors hover:border-[var(--color-secondary)] hover:text-[var(--color-primary)]"
        aria-label="Change currency"
      >
        <span>{active.symbol}</span>
        <span>{active.code}</span>
        <svg
          viewBox="0 0 24 24"
          className={cn('h-3 w-3 transition-transform duration-200', open && 'rotate-180')}
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
        >
          <path d="M6 9l6 6 6-6" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </button>

      {open && (
        <div className="glass-surface animate-scale-in absolute right-0 top-full z-50 mt-2 w-40 origin-top-right rounded-[var(--radius-btn)] py-1 shadow-[var(--shadow-hover)]">
          {SUPPORTED_CURRENCIES.map((c) => (
            <button
              key={c.code}
              onClick={() => {
                setCurrency(c.code);
                setOpen(false);
              }}
              className={cn(
                'flex w-full items-center justify-between px-3 py-2 text-left text-xs transition-colors hover:bg-[var(--color-bg-cream)]',
                c.code === currencyCode ? 'font-semibold text-[var(--color-primary)]' : 'text-[var(--color-text-secondary)]'
              )}
            >
              <span>{c.symbol} {c.code}</span>
              {c.code === currencyCode && (
                <svg viewBox="0 0 24 24" className="h-3.5 w-3.5" fill="none" stroke="currentColor" strokeWidth="2.5">
                  <path d="M20 6L9 17l-5-5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              )}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
