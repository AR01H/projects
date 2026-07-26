import { useState, useRef, useEffect } from 'react';
import { Link } from 'react-router-dom';
import type { NavItem } from '@/config/navigation';

interface MegaMenuProps {
  items: NavItem[];
  isOpen: boolean;
  onClose: () => void;
}

export function MegaMenu({ items, isOpen, onClose }: MegaMenuProps) {
  const [activeL2, setActiveL2] = useState<string | null>(null);
  const timeoutRef = useRef<ReturnType<typeof setTimeout>>(undefined);

  useEffect(() => {
    if (!isOpen) {
      setActiveL2(null);
    }
  }, [isOpen]);

  function handleL2Enter(label: string) {
    clearTimeout(timeoutRef.current);
    setActiveL2(label);
  }

  if (!isOpen) return null;

  const l2Items = items.filter((item) => item.label !== 'All Categories');
  const allCategoriesItem = items.find((item) => item.label === 'All Categories');
  const activeL2Data = l2Items.find((item) => item.label === activeL2);

  return (
    <div
      className="absolute left-0 top-full z-50 w-full border border-[var(--color-border)] border-t-0 rounded-b-[var(--radius-card)] bg-[var(--color-bg-light)] shadow-[var(--shadow-hover)]"
      onMouseLeave={onClose}
    >
      <div className="mx-auto flex max-w-7xl">
        {/* Level 2 — Left column */}
        <div className="w-64 flex-shrink-0 border-r border-[var(--color-border-soft)] py-6 pl-6 pr-4">
          {allCategoriesItem && (
            <Link
              to={allCategoriesItem.href}
              onClick={onClose}
              className="mb-3 block rounded-[var(--radius-btn)] bg-[var(--color-bg-cream)] px-4 py-2.5 text-sm font-semibold text-[var(--color-primary)] transition-colors hover:bg-[var(--color-primary)] hover:text-[var(--color-bg-light)]"
            >
              {allCategoriesItem.label}
            </Link>
          )}
          <nav className="flex flex-col">
            {l2Items.map((item) => (
              <button
                key={item.label}
                onMouseEnter={() => handleL2Enter(item.label)}
                onClick={onClose}
                className={`flex items-center justify-between rounded-[var(--radius-btn)] px-4 py-2.5 text-left text-sm transition-colors ${
                  activeL2 === item.label
                    ? 'bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                    : 'text-[var(--color-text-primary)] hover:bg-[var(--color-bg-cream)]'
                }`}
              >
                <span>{item.label}</span>
                {item.children && item.children.length > 0 && (
                  <svg
                    viewBox="0 0 24 24"
                    className={`h-4 w-4 transition-transform ${activeL2 === item.label ? 'rotate-90' : ''}`}
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                  >
                    <path d="M9 6l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                )}
              </button>
            ))}
          </nav>
        </div>

        {/* Level 3 — Middle column */}
        <div className="flex-1 py-6 pl-8 pr-4">
          {activeL2Data?.children && activeL2Data.children.length > 0 ? (
            <div className="grid grid-cols-2 gap-x-8 gap-y-1">
              {activeL2Data.children.map((l3) => (
                <div
                  key={l3.label}
                  onMouseEnter={() => handleL2Enter(activeL2!)}
                >
                  <Link
                    to={l3.href}
                    onClick={onClose}
                    className="group mb-1 block rounded-[var(--radius-btn)] px-3 py-2 text-sm font-medium text-[var(--color-text-primary)] transition-colors hover:bg-[var(--color-bg-cream)] hover:text-[var(--color-primary)]"
                  >
                    {l3.label}
                  </Link>
                  {/* Level 4 — Tags within L3 */}
                  {l3.children && l3.children.length > 0 && (
                    <div className="mb-3 flex flex-wrap gap-1.5 pl-3">
                      {l3.children.map((l4) => (
                        <Link
                          key={l4.label}
                          to={l4.href}
                          onClick={onClose}
                          className="rounded-full border border-[var(--color-border)] bg-[var(--color-bg-cream)] px-2.5 py-1 text-[0.65rem] font-medium text-[var(--color-text-secondary)] transition-colors hover:border-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-[var(--color-bg-light)]"
                        >
                          {l4.label}
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </div>
          ) : (
            <div className="flex h-full items-center justify-center text-sm text-[var(--color-text-muted)]">
              <p>Select a category to explore subcategories</p>
            </div>
          )}
        </div>

        {/* Level 4 preview / promo — Right column */}
        {activeL2Data?.image && (
          <div className="hidden w-64 flex-shrink-0 py-6 pl-4 pr-6 lg:block">
            <div className="overflow-hidden rounded-[var(--radius-card)]">
              <img
                src={activeL2Data.image}
                alt={activeL2Data.label}
                className="aspect-[4/5] w-full object-cover transition-transform duration-500 hover:scale-105"
              />
            </div>
            <p className="mt-3 text-center text-xs text-[var(--color-text-muted)]">
              Explore {activeL2Data.label}
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
