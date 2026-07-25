import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { categoryApi } from '@/api/category';
import { SectionHeading } from '@/components/common/SectionHeading';
import { Reveal } from '@/components/common/Reveal';
import { buildRoute, ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';
import type { Category } from '@/types/product';

export function FeaturedCategories() {
  const [categories, setCategories] = useState<Category[]>([]);

  useEffect(() => {
    categoryApi.getFeatured(8).then(setCategories);
  }, []);

  if (categories.length === 0) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <SectionHeading
        eyebrow="Curated Selections"
        title="Shop by Category"
        description={SITE_CONFIG.microcopy.featuredCategoriesDescription}
        viewAllLink={ROUTES.categories}
      />

      {/* Grid layout — 2 cols mobile, 3 tablet, 4 desktop */}
      <div className="mt-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {categories.map((cat, i) => (
          <Reveal key={cat.id} delay={i * 80}>
            <Link
              to={buildRoute(ROUTES.category, { categorySlug: cat.slug })}
              className="group relative block overflow-hidden rounded-[var(--radius-card)] bg-[var(--color-bg-cream)] shadow-[var(--shadow-card)] transition-all duration-500 hover:shadow-[var(--shadow-hover)]"
            >
              {/* Image */}
              <div className="aspect-[3/4] overflow-hidden">
                <img
                  src={cat.image}
                  alt={cat.name}
                  loading="lazy"
                  className="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                />
              </div>

              {/* Gradient overlay */}
              <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/85 via-[var(--color-dark)]/20 to-transparent opacity-80 transition-opacity duration-500 group-hover:opacity-90" />

              {/* Content */}
              <div className="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                <h3 className="font-display text-base leading-tight text-[var(--color-bg-light)] sm:text-lg">
                  {cat.name}
                </h3>
                <p className="mt-1 text-xs text-[var(--color-bg-light)]/70">
                  {cat.productCount} {cat.productCount === 1 ? 'piece' : 'pieces'}
                </p>

                {/* Hover arrow */}
                <div className="mt-3 flex items-center gap-1.5 text-xs font-medium text-[var(--color-secondary)] opacity-0 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0 translate-y-2">
                  <span>Explore</span>
                  <svg viewBox="0 0 16 16" className="h-3.5 w-3.5" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M3 8h10M9 4l4 4-4 4" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </div>
              </div>
            </Link>
          </Reveal>
        ))}
      </div>
    </section>
  );
}
