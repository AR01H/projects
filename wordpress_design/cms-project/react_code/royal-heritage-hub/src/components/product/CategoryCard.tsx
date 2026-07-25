import { Link } from 'react-router-dom';
import { buildRoute, ROUTES } from '@/config/routes';
import type { Category } from '@/types/product';

export function CategoryCard({ category }: { category: Category }) {
  return (
    <Link
      to={buildRoute(ROUTES.category, { categorySlug: category.slug })}
      className="group relative flex-shrink-0 overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]"
    >
      <div className="aspect-[3/4] w-44 overflow-hidden sm:w-56">
        <img
          src={category.image}
          alt={category.name}
          loading="lazy"
          className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/80 via-[var(--color-dark)]/10 to-transparent" />
      </div>
      <div className="absolute inset-x-0 bottom-0 p-4">
        <p className="font-display text-base leading-tight text-[var(--color-bg-light)] sm:text-lg">
          {category.name}
        </p>
        <p className="mt-0.5 text-xs text-[var(--color-bg-light)]/75">
          {category.productCount} pieces
        </p>
      </div>
    </Link>
  );
}

export function CategoryPill({ category }: { category: Category }) {
  return (
    <Link
      to={buildRoute(ROUTES.category, { categorySlug: category.slug })}
      className="group flex flex-shrink-0 flex-col items-center gap-2"
    >
      <div className="h-16 w-16 overflow-hidden rounded-full border-2 border-[var(--color-border)] shadow-sm transition-all duration-300 group-hover:border-[var(--color-secondary)] sm:h-20 sm:w-20">
        <img src={category.image} alt={category.name} className="h-full w-full object-cover" loading="lazy" />
      </div>
      <span className="max-w-[80px] text-center text-[0.7rem] font-medium leading-tight text-[var(--color-text-primary)]">
        {category.name}
      </span>
    </Link>
  );
}
