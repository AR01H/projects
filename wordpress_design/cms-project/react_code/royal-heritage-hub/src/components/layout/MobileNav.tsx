import { useState, useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { TOP_NAV_LINKS, type NavItem } from '@/config/navigation';
import { CurrencySwitcher } from './CurrencySwitcher';

interface MobileNavProps {
  isOpen: boolean;
  onClose: () => void;
}

function NavItem({
  item,
  depth,
  onClose,
}: {
  item: NavItem;
  depth: number;
  onClose: () => void;
}) {
  const [expanded, setExpanded] = useState(false);
  const hasChildren = item.children && item.children.length > 0;

  if (!hasChildren) {
    return (
      <Link
        to={item.href}
        onClick={onClose}
        className="flex items-center py-3.5 text-[0.93rem] font-medium text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
        style={{ paddingLeft: `${depth * 20 + 16}px` }}
      >
        {item.label}
      </Link>
    );
  }

  return (
    <div>
      <div className="flex items-center">
        <Link
          to={item.href}
          onClick={onClose}
          className="flex-1 py-3.5 text-[0.93rem] font-medium text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
          style={{ paddingLeft: `${depth * 20 + 16}px` }}
        >
          {item.label}
        </Link>
        <button
          onClick={() => setExpanded(!expanded)}
          className="mr-2 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full transition-colors hover:bg-[var(--color-bg-cream)]"
        >
          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="var(--color-text-muted)" strokeWidth="2" strokeLinecap="round">
            {expanded ? (
              <>
                <line x1="6" y1="6" x2="18" y2="18" />
                <line x1="18" y1="6" x2="6" y2="18" />
              </>
            ) : (
              <>
                <line x1="12" y1="8" x2="12" y2="16" />
                <line x1="8" y1="12" x2="16" y2="12" />
              </>
            )}
          </svg>
        </button>
      </div>
      {expanded && item.children && (
        <div className="animate-fade-in pb-1">
          {item.children.map((child) => (
            <NavItem key={child.label} item={child} depth={depth + 1} onClose={onClose} />
          ))}
        </div>
      )}
    </div>
  );
}

export function MobileNav({ isOpen, onClose }: MobileNavProps) {
  const [mounted, setMounted] = useState(false);
  const [visible, setVisible] = useState(false);
  const timerRef = useRef<ReturnType<typeof setTimeout>>();

  useEffect(() => {
    clearTimeout(timerRef.current);
    if (isOpen) {
      setMounted(true);
      requestAnimationFrame(() => {
        requestAnimationFrame(() => setVisible(true));
      });
    } else {
      setVisible(false);
      timerRef.current = setTimeout(() => setMounted(false), 350);
    }
    return () => clearTimeout(timerRef.current);
  }, [isOpen]);

  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => { document.body.style.overflow = ''; };
  }, [isOpen]);

  if (!mounted) return null;

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 z-[9998] bg-black/40 transition-opacity duration-300 lg:hidden"
        style={{ opacity: visible ? 1 : 0, pointerEvents: visible ? 'auto' : 'none' }}
        onClick={onClose}
      />
      {/* Drawer */}
      <div
        className="fixed inset-y-0 left-0 z-[9999] flex w-full flex-col bg-[var(--color-bg-light)] transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] lg:hidden"
        style={{ transform: visible ? 'translateX(0)' : 'translateX(-100%)' }}
      >
        {/* Header */}
        <div className="flex flex-shrink-0 items-center justify-between border-b border-[var(--color-border)] px-5 py-3">
          <img src="/logo.svg" alt="Royal Heritage Hub" className="h-8" />
          <button
            onClick={onClose}
            aria-label="Close menu"
            className="flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-[var(--color-bg-cream)]"
          >
            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="2" strokeLinecap="round">
              <line x1="6" y1="6" x2="18" y2="18" />
              <line x1="18" y1="6" x2="6" y2="18" />
            </svg>
          </button>
        </div>

        {/* Scrollable nav */}
        <nav className="flex-1 overflow-y-auto overscroll-contain px-5 py-1">
          {TOP_NAV_LINKS.map((item) => (
            <NavItem key={item.label} item={item} depth={0} onClose={onClose} />
          ))}
        </nav>

        {/* Footer */}
        <div className="flex-shrink-0 border-t border-[var(--color-border)] px-5 py-4">
          <p className="mb-2 text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
            Currency
          </p>
          <CurrencySwitcher />
        </div>
      </div>
    </>
  );
}
