import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { formatDiscount, useFormatCurrency } from '@/utils/formatCurrency';
import { buildRoute, ROUTES } from '@/config/routes';
import { Button } from '@/components/common/Button';
import { Badge } from '@/components/common/Badge';
import type { Product } from '@/types/product';

function useCountdown(hours = 8) {
  const [remaining, setRemaining] = useState(hours * 3600);

  useEffect(() => {
    const t = setInterval(() => setRemaining((r) => (r > 0 ? r - 1 : hours * 3600)), 1000);
    return () => clearInterval(t);
  }, [hours]);

  const h = Math.floor(remaining / 3600);
  const m = Math.floor((remaining % 3600) / 60);
  const s = remaining % 60;
  return { h, m, s };
}

export function DealOfTheDay() {
  const [product, setProduct] = useState<Product | null | undefined>(undefined);
  const { h, m, s } = useCountdown();
  const formatCurrency = useFormatCurrency();

  useEffect(() => {
    productsApi.getDealOfTheDay().then((p) => setProduct(p ?? null));
  }, []);

  if (!product) return null;

  const discount = formatDiscount(product.price, product.compareAtPrice);

  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <div className="overflow-hidden rounded-[var(--radius-card)] bg-[var(--color-dark)] shadow-[var(--shadow-hover)]">
        <div className="grid grid-cols-1 lg:grid-cols-2">
          <div className="relative aspect-[4/3] lg:aspect-auto">
            <img src={product.images[0] || product.thumbnail} alt={product.name} className="h-full w-full object-cover" />
            {discount && (
              <div className="absolute left-4 top-4">
                <Badge variant="danger">{discount}% OFF</Badge>
              </div>
            )}
          </div>
          <div className="flex flex-col justify-center px-6 py-10 sm:px-12">
            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.25em] text-[var(--color-secondary)]">
              Deal of the Day
            </p>
            <h3 className="font-display text-2xl leading-tight text-[var(--color-bg-light)] sm:text-3xl">
              {product.name}
            </h3>
            <p className="mt-3 text-sm text-[var(--color-bg-light)]/75">{product.shortDescription}</p>

            <div className="mt-5 flex items-baseline gap-3">
              <span className="font-display text-2xl text-[var(--color-secondary)]">
                {formatCurrency(product.price)}
              </span>
              {product.compareAtPrice && (
                <span className="text-sm text-[var(--color-bg-light)]/50 line-through">
                  {formatCurrency(product.compareAtPrice)}
                </span>
              )}
            </div>

            <div className="mt-6 flex gap-3">
              {[
                { label: 'HRS', value: h },
                { label: 'MIN', value: m },
                { label: 'SEC', value: s },
              ].map((unit) => (
                <div key={unit.label} className="flex flex-col items-center rounded-[var(--radius-btn)] bg-[var(--color-bg-light)]/10 px-3 py-2">
                  <span className="font-display text-lg text-[var(--color-bg-light)]">
                    {String(unit.value).padStart(2, '0')}
                  </span>
                  <span className="text-[0.6rem] tracking-widest text-[var(--color-bg-light)]/60">{unit.label}</span>
                </div>
              ))}
            </div>

            <Link to={buildRoute(ROUTES.product, { productSlug: product.slug })} className="mt-7 w-fit">
              <Button variant="secondary" size="lg">
                Shop This Deal
              </Button>
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}
