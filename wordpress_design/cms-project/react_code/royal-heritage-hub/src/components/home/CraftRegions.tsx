import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { ROUTES } from '@/config/routes';
import { Reveal } from '@/components/common/Reveal';
import type { Product } from '@/types/product';

interface CraftRegion {
  name: string;
  state: string;
  productCount: number;
  slug: string;
  image: string;
}

const REGION_IMAGES: Record<string, string> = {
  'Kondapalli': 'https://picsum.photos/seed/region-kondapalli/400/300',
  'Saharanpur': 'https://picsum.photos/seed/region-saharanpur/400/300',
  'Jodhpur': 'https://picsum.photos/seed/region-jodhpur/400/300',
  'Udaipur': 'https://picsum.photos/seed/region-udaipur/400/300',
  'Channapatna': 'https://picsum.photos/seed/region-channapatna/400/300',
  'Moradabad': 'https://picsum.photos/seed/region-moradabad/400/300',
  'Mysore': 'https://picsum.photos/seed/region-mysore/400/300',
  'Kerala': 'https://picsum.photos/seed/region-kerala/400/300',
};

export function CraftRegions() {
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    productsApi.getAll().then((data) => { if (data) setProducts(data); });
  }, []);

  const regions = useMemo<CraftRegion[]>(() => {
    const regionMap = new Map<string, { state: string; count: number }>();
    for (const p of products) {
      const originSpec = p.specs.find((s) => s.key === 'origin');
      if (!originSpec) continue;
      const parts = originSpec.value.split(',').map((s) => s.trim());
      const city = parts[0];
      const state = parts[1] || '';
      if (!regionMap.has(city)) {
        regionMap.set(city, { state, count: 0 });
      }
      regionMap.get(city)!.count++;
    }
    return Array.from(regionMap.entries())
      .map(([city, data]) => ({
        name: city,
        state: data.state,
        productCount: data.count,
        slug: city.toLowerCase().replace(/\s+/g, '-'),
        image: REGION_IMAGES[city] || 'https://picsum.photos/seed/region-default/400/300',
      }))
      .sort((a, b) => b.productCount - a.productCount);
  }, [products]);

  if (regions.length === 0) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <Reveal className="mb-10 text-center">
        <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
          Across India
        </p>
        <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
          Craft Regions
        </h2>
        <p className="mx-auto mt-3 max-w-xl text-sm text-[var(--color-text-secondary)]">
          From the toy-makers of Kondapalli to the brass casters of Moradabad — discover the origins of our craft.
        </p>
      </Reveal>

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        {regions.map((region, i) => (
          <Reveal key={region.name} delay={i * 80}>
            <Link
              to={`${ROUTES.shop}?search=${encodeURIComponent(region.name)}`}
              className="group relative block overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)] transition-shadow duration-300 hover:shadow-[var(--shadow-hover)]"
            >
              <div className="aspect-[4/3] overflow-hidden">
                <img
                  src={region.image}
                  alt={region.name}
                  className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
              </div>
              <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/80 via-[var(--color-dark)]/20 to-transparent" />
              <div className="absolute bottom-0 left-0 right-0 p-4">
                <h3 className="font-display text-base text-[var(--color-bg-light)]">{region.name}</h3>
                <p className="text-xs text-[var(--color-bg-light)]/70">{region.state}</p>
                <p className="mt-1 text-[0.65rem] font-medium text-[var(--color-secondary)]">
                  {region.productCount} {region.productCount === 1 ? 'piece' : 'pieces'}
                </p>
              </div>
            </Link>
          </Reveal>
        ))}
      </div>
    </section>
  );
}
