import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { useFormatCurrency } from '@/utils/formatCurrency';
import { buildRoute, ROUTES } from '@/config/routes';
import { SectionHeading } from '@/components/common/SectionHeading';
import { Badge } from '@/components/common/Badge';
import { ProductCardSkeleton } from '@/components/common/Skeleton';
import type { Product } from '@/types/product';

export function TrendingMasonry() {
  const [products, setProducts] = useState<Product[] | null>(null);
  const formatCurrency = useFormatCurrency();

  useEffect(() => {
    productsApi.getTrending(6).then(setProducts);
  }, []);

  if (products && products.length === 0) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Right Now" title="Trending Products" description="What everyone's adding to their bag this week." />

      {!products ? (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
          {Array.from({ length: 6 }).map((_, i) => (
            <ProductCardSkeleton key={i} />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:grid-rows-2">
          {products.map((p, i) => {
            const large = i === 0;
            return (
              <Link
                key={p.id}
                to={buildRoute(ROUTES.product, { productSlug: p.slug })}
                className={`group relative overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)] ${
                  large ? 'col-span-2 row-span-2' : ''
                }`}
              >
                <div className={`overflow-hidden ${large ? 'aspect-square sm:aspect-auto sm:h-full' : 'aspect-square'}`}>
                  <img
                    src={p.thumbnail}
                    alt={p.name}
                    loading="lazy"
                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                  />
                </div>
                <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/85 via-[var(--color-dark)]/10 to-transparent" />
                {i < 3 && (
                  <div className="absolute left-3 top-3">
                    <Badge variant="gold">#{i + 1} Trending</Badge>
                  </div>
                )}
                <div className="absolute inset-x-0 bottom-0 p-4">
                  <p className={`font-display text-[var(--color-bg-light)] ${large ? 'text-xl' : 'text-sm'}`}>
                    {p.name}
                  </p>
                  <p className="mt-0.5 text-xs text-[var(--color-secondary)]">{formatCurrency(p.price)}</p>
                </div>
              </Link>
            );
          })}
        </div>
      )}
    </section>
  );
}
