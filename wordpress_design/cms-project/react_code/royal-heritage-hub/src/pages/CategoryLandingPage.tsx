import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { categoryApi } from '@/api/category';
import { productsApi } from '@/api/products';
import { ProductGrid } from '@/components/product/ProductGrid';
import { Reveal } from '@/components/common/Reveal';
import { Button } from '@/components/common/Button';
import { PageLoader } from '@/components/common/Skeleton';
import { Breadcrumbs, type BreadcrumbItem } from '@/components/common/Breadcrumbs';
import { buildRoute, ROUTES } from '@/config/routes';
import type { Category, Product } from '@/types/product';

export default function CategoryLandingPage() {
  const { categorySlug } = useParams();
  const [category, setCategory] = useState<Category | null | undefined>(undefined);
  const [ancestors, setAncestors] = useState<Category[]>([]);
  const [subcategories, setSubcategories] = useState<Category[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [bestSellers, setBestSellers] = useState<Product[]>([]);

  useEffect(() => {
    if (!categorySlug) return;
    setCategory(undefined);
    categoryApi.getBySlug(categorySlug).then(async (c) => {
      setCategory(c ?? null);
      if (c) {
        const [all, ancestorChain, children] = await Promise.all([
          productsApi.getFiltered({ categorySlug: c.slug }),
          categoryApi.getAncestors(c),
          categoryApi.getChildren(c.slug),
        ]);
        setProducts(all);
        setBestSellers(all.filter((p) => p.isBestSeller).slice(0, 4));
        setAncestors(ancestorChain);
        setSubcategories(children);
      }
    });
  }, [categorySlug]);

  if (category === undefined) return <PageLoader />;
  if (category === null) {
    return (
      <div className="mx-auto max-w-2xl px-4 py-24 text-center">
        <p className="font-display text-2xl text-[var(--color-text-primary)]">Category not found</p>
      </div>
    );
  }

  return (
    <div>
      {/* Full-bleed editorial hero, unique per category */}
      <section className="relative aspect-[4/3] w-full overflow-hidden sm:aspect-[16/7]">
        <img src={category.image} alt={category.name} className="h-full w-full object-cover" />
        <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/90 via-[var(--color-dark)]/30 to-transparent" />
        <div className="absolute inset-0 flex flex-col justify-end px-6 pb-12 sm:px-12 lg:px-20">
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--color-secondary)]">
            Category Spotlight
          </p>
          <h1 className="max-w-xl font-display text-4xl leading-tight text-[var(--color-bg-light)] sm:text-5xl">
            {category.name}
          </h1>
          <p className="mt-4 max-w-lg text-sm text-[var(--color-bg-light)]/85 sm:text-base">
            {category.description}
          </p>
        </div>
      </section>

      <div className="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <Breadcrumbs
          items={[
            { label: 'Categories', href: ROUTES.categories },
            ...ancestors.map((a): BreadcrumbItem => ({
              label: a.name,
              href: buildRoute(ROUTES.categoryLanding, { categorySlug: a.slug }),
            })),
            { label: category.name },
          ]}
        />
      </div>

      {/* Subcategories, if this category has children */}
      {subcategories.length > 0 && (
        <section className="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
          <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
            Shop by Subcategory
          </p>
          <div className="flex flex-wrap gap-3">
            {subcategories.map((sub) => (
              <Link
                key={sub.id}
                to={buildRoute(ROUTES.categoryLanding, { categorySlug: sub.slug })}
                className="flex items-center gap-2 rounded-[var(--radius-pill)] border border-[var(--color-border)] bg-[var(--color-bg-light)] py-2 pl-2 pr-4 text-sm text-[var(--color-text-secondary)] transition-colors hover:border-[var(--color-secondary)] hover:text-[var(--color-primary)]"
              >
                <img src={sub.image} alt="" className="h-8 w-8 rounded-full object-cover" />
                {sub.name}
              </Link>
            ))}
          </div>
        </section>
      )}

      {/* Editorial split: story + best sellers spotlight */}
      {bestSellers.length > 0 && (
        <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
          <Reveal>
            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
              Where to Start
            </p>
            <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
              Best of {category.name}
            </h2>
          </Reveal>
          <div className="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            {bestSellers.map((p, i) => (
              <Reveal key={p.id} delay={i * 100}>
                <Link
                  to={buildRoute(ROUTES.product, { productSlug: p.slug })}
                  className="group block overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]"
                >
                  <div className="aspect-[4/5] overflow-hidden">
                    <img
                      src={p.thumbnail}
                      alt={p.name}
                      className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                    />
                  </div>
                  <div className="p-3">
                    <p className="font-display text-sm text-[var(--color-text-primary)]">{p.name}</p>
                  </div>
                </Link>
              </Reveal>
            ))}
          </div>
        </section>
      )}

      {/* Full grid of the category, with a link to the filterable Shop view */}
      <section className="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div className="mb-8 flex items-center justify-between">
          <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
            All {category.name}
          </h2>
          <Link to={`${ROUTES.shop}?category=${category.slug}`}>
            <Button variant="outline" size="sm">Filter &amp; Sort</Button>
          </Link>
        </div>
        <ProductGrid products={products} columns={4} />
      </section>
    </div>
  );
}
