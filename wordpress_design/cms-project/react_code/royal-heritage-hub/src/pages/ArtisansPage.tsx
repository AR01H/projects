import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { buildRoute, ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';
import { PageHero } from '@/components/common/PageHero';
import { Reveal } from '@/components/common/Reveal';
import { formatCurrency } from '@/utils/formatCurrency';
import type { Product } from '@/types/product';
import { SEO } from '@/components/common/SEO';

interface Artisan {
  name: string;
  craft: string;
  location: string;
  state: string;
  products: Product[];
}

export default function ArtisansPage() {
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    productsApi.getAll().then(({ data }) => { if (data) setProducts(data); });
  }, []);

  const artisans = useMemo<Artisan[]>(() => {
    const artisanMap = new Map<string, { craft: string; location: string; state: string; products: Product[] }>();
    for (const p of products) {
      if (!p.makerName) continue;
      const originSpec = p.specs.find((s) => s.key === 'origin');
      const origin = originSpec?.value ?? '';
      const parts = origin.split(',').map((s) => s.trim());
      const location = parts.slice(0, -1).join(', ') || origin;
      const state = parts[parts.length - 1] || '';
      if (!artisanMap.has(p.makerName)) {
        artisanMap.set(p.makerName, {
          craft: p.specs.find((s) => s.key === 'material')?.value?.split(',')[0] ?? 'Handcraft',
          location,
          state,
          products: [],
        });
      }
      artisanMap.get(p.makerName)!.products.push(p);
    }
    return Array.from(artisanMap.entries())
      .map(([name, data]) => ({ name, ...data }))
      .sort((a, b) => b.products.length - a.products.length);
  }, [products]);

  return (
    <div>
      <SEO title={`Our ${SITE_CONFIG.terminology.makersPlural}`} description={`Meet the master ${SITE_CONFIG.terminology.makersPlural.toLowerCase()} behind every ${SITE_CONFIG.terminology.qualityAdjective.toLowerCase()} ${SITE_CONFIG.terminology.productUnitSingular} in our collection.`} />
      <PageHero
        pageKey="about"
        fallbackTitle={`Our ${SITE_CONFIG.terminology.makersPlural}`}
        fallbackSubtitle={`The master ${SITE_CONFIG.terminology.makersPlural.toLowerCase()} behind every ${SITE_CONFIG.terminology.productUnitSingular} in our collection.`}
      />

      <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <Reveal className="mb-12 text-center">
          <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">
            Meet Our {SITE_CONFIG.terminology.makersPlural}
          </h1>
          <p className="mx-auto mt-4 max-w-2xl text-sm text-[var(--color-text-secondary)]">
            Every {SITE_CONFIG.terminology.productUnitSingular} in our collection carries the hand, skill, and {SITE_CONFIG.terminology.heritageWord.toLowerCase()} of {SITE_CONFIG.terminology.originWord}'s master {SITE_CONFIG.terminology.makersPlural.toLowerCase()}.
            These are the {SITE_CONFIG.terminology.makersPlural.toLowerCase()} who keep centuries-old traditions alive.
          </p>
        </Reveal>

        <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
          {artisans.map((artisan, i) => (
            <Reveal key={artisan.name} delay={i * 100}>
              <div className="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] shadow-[var(--shadow-card)]">
                {/* Product gallery */}
                <div className="grid grid-cols-3 gap-1">
                  {artisan.products.slice(0, 3).map((p) => (
                    <Link
                      key={p.id}
                      to={buildRoute(ROUTES.product, { productSlug: p.slug })}
                      className="group relative aspect-square overflow-hidden"
                    >
                      <img
                        src={p.thumbnail}
                        alt={p.name}
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                      />
                      <div className="absolute inset-0 bg-[var(--color-dark)]/0 transition-colors group-hover:bg-[var(--color-dark)]/30" />
                    </Link>
                  ))}
                </div>

                {/* Artisan info */}
                <div className="p-6">
                  <div className="flex items-start justify-between">
                    <div>
                      <h2 className="font-display text-xl text-[var(--color-text-primary)]">{artisan.name}</h2>
                      <p className="mt-1 text-sm text-[var(--color-text-secondary)]">{artisan.location}, {artisan.state}</p>
                    </div>
                    <span className="rounded-full bg-[var(--color-bg-cream)] px-3 py-1 text-xs font-medium text-[var(--color-text-secondary)]">
                      {artisan.craft}
                    </span>
                  </div>

                  <div className="mt-4 border-t border-[var(--color-border)] pt-4">
                    <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
                      {artisan.products.length} {artisan.products.length === 1 ? 'piece' : 'pieces'} in collection
                    </p>
                    <div className="flex flex-wrap gap-2">
                      {artisan.products.map((p) => (
                        <Link
                          key={p.id}
                          to={buildRoute(ROUTES.product, { productSlug: p.slug })}
                          className="group flex items-center gap-2 rounded-[var(--radius-btn)] border border-[var(--color-border)] px-3 py-2 transition-colors hover:border-[var(--color-primary)] hover:bg-[var(--color-bg-cream)]"
                        >
                          <img src={p.thumbnail} alt="" className="h-8 w-8 rounded object-cover" />
                          <div className="min-w-0">
                            <p className="truncate text-xs font-medium text-[var(--color-text-primary)] group-hover:text-[var(--color-primary)]">
                              {p.name}
                            </p>
                            <p className="text-[0.6rem] text-[var(--color-text-muted)]">{formatCurrency(p.price)}</p>
                          </div>
                        </Link>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            </Reveal>
          ))}
        </div>
      </div>
    </div>
  );
}
