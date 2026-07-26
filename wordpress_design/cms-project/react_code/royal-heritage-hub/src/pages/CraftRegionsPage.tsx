import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';
import { PageHero } from '@/components/common/PageHero';
import { Reveal } from '@/components/common/Reveal';
import { formatCurrency } from '@/utils/formatCurrency';
import type { Product } from '@/types/product';
import { SEO } from '@/components/common/SEO';

interface CraftRegion {
  name: string;
  state: string;
  productCount: number;
  products: Product[];
  image: string;
}

const REGION_IMAGES: Record<string, string> = {
  'Kondapalli': 'https://picsum.photos/seed/region-kondapalli/600/400',
  'Saharanpur': 'https://picsum.photos/seed/region-saharanpur/600/400',
  'Jodhpur': 'https://picsum.photos/seed/region-jodhpur/600/400',
  'Udaipur': 'https://picsum.photos/seed/region-udaipur/600/400',
  'Channapatna': 'https://picsum.photos/seed/region-channapatna/600/400',
  'Moradabad': 'https://picsum.photos/seed/region-moradabad/600/400',
  'Mysore': 'https://picsum.photos/seed/region-mysore/600/400',
  'Kerala': 'https://picsum.photos/seed/region-kerala/600/400',
};

export default function CraftRegionsPage() {
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    productsApi.getAll().then((data) => { if (data) setProducts(data); });
  }, []);

  const regions = useMemo<CraftRegion[]>(() => {
    const regionMap = new Map<string, { state: string; products: Product[] }>();
    for (const p of products) {
      const originSpec = p.specs.find((s) => s.key === 'origin');
      if (!originSpec) continue;
      const parts = originSpec.value.split(',').map((s) => s.trim());
      const city = parts[0];
      const state = parts[parts.length - 1] || '';
      if (!regionMap.has(city)) {
        regionMap.set(city, { state, products: [] });
      }
      regionMap.get(city)!.products.push(p);
    }
    return Array.from(regionMap.entries())
      .map(([city, data]) => ({
        name: city,
        state: data.state,
        productCount: data.products.length,
        products: data.products,
        image: REGION_IMAGES[city] || 'https://picsum.photos/seed/region-default/600/400',
      }))
      .sort((a, b) => b.productCount - a.productCount);
  }, [products]);

  return (
    <div>
      <SEO title={`${SITE_CONFIG.terminology.regionsWord} ${SITE_CONFIG.terminology.heritageWord}`} description={`Discover the origins of ${SITE_CONFIG.terminology.originWord}'s finest ${SITE_CONFIG.terminology.qualityAdjective.toLowerCase()} traditions across diverse regions.`} />
      <PageHero
        pageKey="about"
        fallbackTitle={`${SITE_CONFIG.terminology.regionsWord} ${SITE_CONFIG.terminology.heritageWord}`}
        fallbackSubtitle={`Discover the origins of ${SITE_CONFIG.terminology.originWord}'s finest ${SITE_CONFIG.terminology.qualityAdjective.toLowerCase()} traditions.`}
      />

      <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <Reveal className="mb-12 text-center">
          <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">
            {SITE_CONFIG.terminology.originWord}'s {SITE_CONFIG.terminology.productProcessNoun} {SITE_CONFIG.terminology.heritageWord}
          </h1>
          <p className="mx-auto mt-4 max-w-2xl text-sm text-[var(--color-text-secondary)]">
            Each region of {SITE_CONFIG.terminology.originWord} has its own distinct {SITE_CONFIG.terminology.productProcessNoun.toLowerCase()} tradition, passed down through generations.
            Explore the origins of the {SITE_CONFIG.terminology.productUnitPlural} in our collection.
          </p>
        </Reveal>

        <div className="flex flex-col gap-12">
          {regions.map((region, i) => (
            <Reveal key={region.name} delay={i * 80}>
              <div className="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] shadow-[var(--shadow-card)]">
                {/* Region header */}
                <div className="relative aspect-[3/1] overflow-hidden sm:aspect-[4/1]">
                  <img
                    src={region.image}
                    alt={region.name}
                    className="h-full w-full object-cover"
                  />
                  <div className="absolute inset-0 bg-gradient-to-r from-[var(--color-dark)]/80 via-[var(--color-dark)]/40 to-transparent" />
                  <div className="absolute bottom-0 left-0 p-6 sm:p-8">
                    <h2 className="font-display text-2xl text-[var(--color-bg-light)] sm:text-3xl">{region.name}</h2>
                    <p className="text-sm text-[var(--color-bg-light)]/80">{region.state}, India</p>
                    <p className="mt-1 text-xs font-medium text-[var(--color-secondary)]">
                      {region.productCount} {region.productCount === 1 ? 'piece' : 'pieces'} in collection
                    </p>
                  </div>
                </div>

                {/* Products from this region */}
                <div className="p-6">
                  <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                    {region.products.map((p) => (
                      <Link
                        key={p.id}
                        to={`${ROUTES.shop}?search=${encodeURIComponent(region.name)}`}
                        className="group flex gap-3 rounded-[var(--radius-btn)] border border-[var(--color-border)] p-3 transition-colors hover:border-[var(--color-primary)] hover:bg-[var(--color-bg-cream)]"
                      >
                        <img
                          src={p.thumbnail}
                          alt={p.name}
                          className="h-16 w-16 flex-shrink-0 rounded object-cover"
                        />
                        <div className="min-w-0">
                          <p className="truncate text-xs font-medium text-[var(--color-text-primary)] group-hover:text-[var(--color-primary)]">
                            {p.name}
                          </p>
                          <p className="text-[0.6rem] text-[var(--color-text-muted)]">{p.makerName}</p>
                          <p className="mt-0.5 text-xs font-semibold text-[var(--color-primary)]">
                            {formatCurrency(p.price)}
                          </p>
                        </div>
                      </Link>
                    ))}
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
