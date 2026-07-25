import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { buildRoute, ROUTES } from '@/config/routes';
import { Reveal } from '@/components/common/Reveal';
import type { Product } from '@/types/product';

interface Artisan {
  name: string;
  craft: string;
  location: string;
  featuredProduct: Product;
}

export function ArtisanSpotlight() {
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    productsApi.getAll().then(({ data }) => { if (data) setProducts(data); });
  }, []);

  const artisans = useMemo<Artisan[]>(() => {
    const artisanMap = new Map<string, { craft: string; location: string; product: Product }>();
    for (const p of products) {
      if (!p.makerName) continue;
      const origin = p.specs.find((s) => s.key === 'origin')?.value ?? '';
      const location = origin.split(',').slice(-2).join(',').trim();
      if (!artisanMap.has(p.makerName)) {
        artisanMap.set(p.makerName, {
          craft: p.specs.find((s) => s.key === 'material')?.value?.split(',')[0] ?? 'Handcraft',
          location,
          product: p,
        });
      }
    }
    return Array.from(artisanMap.entries()).map(([name, data]) => ({
      name,
      craft: data.craft,
      location: data.location,
      featuredProduct: data.product,
    }));
  }, [products]);

  if (artisans.length === 0) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <Reveal className="mb-10 text-center">
        <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
          Meet the Makers
        </p>
        <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
          Artisan Spotlight
        </h2>
        <p className="mx-auto mt-3 max-w-xl text-sm text-[var(--color-text-secondary)]">
          Every piece carries the hand of its maker. These are the artisans behind our collection.
        </p>
      </Reveal>

      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {artisans.map((artisan, i) => (
          <Reveal key={artisan.name} delay={i * 100}>
            <div className="group overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] shadow-[var(--shadow-card)] transition-all duration-300 hover:shadow-[var(--shadow-hover)]">
              {/* Product image as artisan feature */}
              <div className="relative aspect-[4/3] overflow-hidden">
                <img
                  src={artisan.featuredProduct.thumbnail}
                  alt={`Work by ${artisan.name}`}
                  className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/70 to-transparent" />
                <div className="absolute bottom-0 left-0 right-0 p-4">
                  <h3 className="font-display text-lg text-[var(--color-bg-light)]">{artisan.name}</h3>
                  <p className="text-xs text-[var(--color-bg-light)]/80">{artisan.location}</p>
                </div>
              </div>

              {/* Info */}
              <div className="p-4">
                <div className="mb-3 flex items-center gap-2">
                  <span className="rounded-full bg-[var(--color-bg-cream)] px-2.5 py-0.5 text-[0.65rem] font-medium text-[var(--color-text-secondary)]">
                    {artisan.craft}
                  </span>
                </div>
                <Link
                  to={buildRoute(ROUTES.product, { productSlug: artisan.featuredProduct.slug })}
                  className="text-sm font-medium text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
                >
                  {artisan.featuredProduct.name}
                </Link>
                <p className="mt-1 text-xs text-[var(--color-text-muted)]">
                  by {artisan.name}
                </p>
              </div>
            </div>
          </Reveal>
        ))}
      </div>
    </section>
  );
}
