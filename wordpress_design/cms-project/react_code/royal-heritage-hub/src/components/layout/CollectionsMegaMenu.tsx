import { Link } from 'react-router-dom';
import type { NavItem } from '@/config/navigation';

interface CollectionsMegaMenuProps {
  items: NavItem[];
  isOpen: boolean;
  onClose: () => void;
}

export function CollectionsMegaMenu({ items, isOpen, onClose }: CollectionsMegaMenuProps) {
  if (!isOpen) return null;

  return (
    <div
      className="absolute left-0 top-full z-50 w-full border border-[var(--color-border)] border-t-0 rounded-b-[var(--radius-card)] bg-[var(--color-bg-light)] shadow-[var(--shadow-hover)]"
      onMouseLeave={onClose}
    >
      <div className="mx-auto max-w-7xl px-6 py-6">
        {/* Header */}
        <div className="mb-5">
          <h3 className="font-display text-base font-semibold text-[var(--color-text-primary)]">Shop by Collection</h3>
          <p className="mt-1 text-xs text-[var(--color-text-muted)]">Curated groupings by occasion, style, and season</p>
        </div>

        {/* Collection grid */}
        <div className="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-7">
          {items.map((col) => (
            <Link
              key={col.label}
              to={col.href}
              onClick={onClose}
              className="group flex flex-col items-center gap-2.5 rounded-[var(--radius-card)] p-3 transition-colors hover:bg-[var(--color-bg-cream)]"
            >
              <div className="relative h-20 w-20 overflow-hidden rounded-[var(--radius-card)] shadow-sm transition-shadow group-hover:shadow-md">
                {col.image ? (
                  <img
                    src={col.image}
                    alt={col.label}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                ) : (
                  <div className="flex h-full w-full items-center justify-center bg-[var(--color-bg-cream)] text-[var(--color-primary)]">
                    <svg viewBox="0 0 24 24" className="h-8 w-8" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                  </div>
                )}
              </div>
              <span className="text-center text-xs font-medium text-[var(--color-text-secondary)] transition-colors group-hover:text-[var(--color-primary)]">
                {col.label}
              </span>
            </Link>
          ))}
        </div>

        {/* View all */}
        <div className="mt-5 border-t border-[var(--color-border)] pt-4 text-center">
          <Link
            to="/collections"
            onClick={onClose}
            className="inline-flex items-center gap-1.5 text-xs font-medium text-[var(--color-primary)] transition-colors hover:text-[var(--color-primary-dark)]"
          >
            View all collections
            <svg viewBox="0 0 24 24" className="h-3.5 w-3.5" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M5 12h14M12 5l7 7-7 7" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </Link>
        </div>
      </div>
    </div>
  );
}
