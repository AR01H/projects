import { Link } from 'react-router-dom';
import { ROUTES } from '@/config/routes';

export interface BreadcrumbItem {
  label: string;
  href?: string;
}

interface BreadcrumbsProps {
  items: BreadcrumbItem[];
  /** Include the Home crumb at the start. Default true. */
  showHome?: boolean;
}

export function Breadcrumbs({ items, showHome = true }: BreadcrumbsProps) {
  const allItems: BreadcrumbItem[] = showHome ? [{ label: 'Home', href: ROUTES.home }, ...items] : items;

  return (
    <nav aria-label="Breadcrumb" className="mb-6 overflow-x-auto">
      <ol className="flex items-center gap-1.5 whitespace-nowrap text-xs text-[var(--color-text-muted)]">
        {allItems.map((item, i) => {
          const isLast = i === allItems.length - 1;
          return (
            <li key={`${item.label}-${i}`} className="flex items-center gap-1.5">
              {i > 0 && (
                <svg viewBox="0 0 24 24" className="h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M9 6l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              )}
              {item.href && !isLast ? (
                <Link to={item.href} className="transition-colors hover:text-[var(--color-primary)]">
                  {item.label}
                </Link>
              ) : (
                <span className={isLast ? 'font-medium text-[var(--color-text-primary)]' : ''}>
                  {item.label}
                </span>
              )}
            </li>
          );
        })}
      </ol>
    </nav>
  );
}
