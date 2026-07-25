import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { categoryApi } from '@/api/category';
import { PageHero } from '@/components/common/PageHero';
import { Reveal } from '@/components/common/Reveal';
import { buildRoute, ROUTES } from '@/config/routes';
import { SEO } from '@/components/common/SEO';
import type { Category } from '@/types/product';

export default function CategoriesPage() {
  const [tree, setTree] = useState<{ parent: Category; children: Category[] }[]>([]);

  useEffect(() => {
    categoryApi.getTree().then(setTree);
  }, []);

  return (
    <div>
      <SEO title="Categories" description="Browse our full range of handcrafted Indian products organised by craft tradition." />
      <PageHero pageKey="collections" fallbackTitle="All Categories" />
      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">All Categories</h1>
        <p className="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)]">
          Browse the full range, organised by craft tradition.
        </p>

        <div className="mt-10 flex flex-col gap-12">
          {tree.map(({ parent, children }, groupIndex) => (
            <Reveal key={parent.id} delay={groupIndex * 80}>
              <div className="mb-4 flex items-center gap-4">
                <Link
                  to={buildRoute(ROUTES.categoryLanding, { categorySlug: parent.slug })}
                  className="group flex items-center gap-3"
                >
                  <div className="h-14 w-14 flex-shrink-0 overflow-hidden rounded-full shadow-[var(--shadow-soft)]">
                    <img src={parent.image} alt={parent.name} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                  </div>
                  <div>
                    <h2 className="font-display text-xl text-[var(--color-text-primary)] transition-colors group-hover:text-[var(--color-primary)]">
                      {parent.name}
                    </h2>
                    <p className="text-xs text-[var(--color-text-muted)]">{parent.productCount} products</p>
                  </div>
                </Link>
              </div>

              {children.length > 0 ? (
                <div className="ml-4 grid grid-cols-2 gap-3 border-l-2 border-[var(--color-border)] pl-6 sm:grid-cols-3 lg:grid-cols-4">
                  {children.map((c) => (
                    <Link
                      key={c.id}
                      to={buildRoute(ROUTES.categoryLanding, { categorySlug: c.slug })}
                      className="group flex items-center gap-2.5 rounded-[var(--radius-btn)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-2.5 transition-colors hover:border-[var(--color-secondary)]"
                    >
                      <div className="h-10 w-10 flex-shrink-0 overflow-hidden rounded-full">
                        <img src={c.image} alt={c.name} className="h-full w-full object-cover" />
                      </div>
                      <div>
                        <p className="text-sm text-[var(--color-text-primary)]">{c.name}</p>
                        <p className="text-[0.65rem] text-[var(--color-text-muted)]">{c.productCount} products</p>
                      </div>
                    </Link>
                  ))}
                </div>
              ) : (
                <p className="ml-4 border-l-2 border-[var(--color-border)] pl-6 text-xs text-[var(--color-text-muted)]">
                  {parent.description}
                </p>
              )}
            </Reveal>
          ))}
        </div>
      </div>
    </div>
  );
}
