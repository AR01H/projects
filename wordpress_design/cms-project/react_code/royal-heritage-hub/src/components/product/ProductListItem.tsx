import { Link } from 'react-router-dom';
import { buildRoute, ROUTES } from '@/config/routes';
import { formatDiscount, useFormatCurrency } from '@/utils/formatCurrency';
import { Badge } from '@/components/common/Badge';
import { Rating } from '@/components/common/Rating';
import { useCartStore } from '@/store/useCartStore';
import type { Product } from '@/types/product';

interface ProductListItemProps {
  product: Product;
}

export function ProductListItem({ product }: ProductListItemProps) {
  const addItem = useCartStore((s) => s.addItem);
  const formatCurrency = useFormatCurrency();
  const discount = formatDiscount(product.price, product.compareAtPrice);
  const isOutOfStock = product.stock === 0;

  return (
    <div className="group flex gap-4 overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4 shadow-[var(--shadow-card)] transition-all duration-300 hover:shadow-[var(--shadow-hover)]">
      <Link
        to={buildRoute(ROUTES.product, { productSlug: product.slug })}
        className="relative h-32 w-32 flex-shrink-0 overflow-hidden rounded-[var(--radius-btn)] bg-[var(--color-bg-cream)]"
      >
        <img
          src={product.thumbnail}
          alt={product.name}
          loading="lazy"
          className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
        />
        <div className="absolute left-2 top-2 flex flex-col gap-1">
          {product.isBestSeller && <Badge variant="dark">Best Seller</Badge>}
          {product.isNewArrival && <Badge variant="gold">New</Badge>}
          {discount && <Badge variant="danger">{discount}% OFF</Badge>}
        </div>
      </Link>

      <div className="flex flex-1 flex-col justify-between">
        <div>
          <p className="text-[0.65rem] uppercase tracking-wider text-[var(--color-text-muted)]">
            {product.specs.find((s) => s.highlight)?.value.split(',')[0] ?? ''}
          </p>
          <Link
            to={buildRoute(ROUTES.product, { productSlug: product.slug })}
            className="mt-0.5 block font-display text-base text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
          >
            {product.name}
          </Link>
          <p className="mt-1 text-xs text-[var(--color-text-secondary)] line-clamp-2">
            {product.shortDescription}
          </p>
          <div className="mt-2">
            <Rating value={product.rating} reviewCount={product.reviewCount} />
          </div>
        </div>

        <div className="mt-2 flex items-center justify-between">
          <div className="flex items-baseline gap-2">
            <span className="font-semibold text-[var(--color-primary)]">
              {formatCurrency(product.price)}
            </span>
            {product.compareAtPrice && (
              <span className="text-xs text-[var(--color-text-muted)] line-through">
                {formatCurrency(product.compareAtPrice)}
              </span>
            )}
          </div>
          <button
            onClick={() => !isOutOfStock && addItem(product)}
            disabled={isOutOfStock}
            className="rounded-[var(--radius-btn)] bg-[var(--color-dark)] px-4 py-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-bg-light)] transition-colors hover:bg-[var(--color-dark-soft)] disabled:cursor-not-allowed disabled:bg-[var(--color-text-muted)]"
          >
            {isOutOfStock ? 'Out of Stock' : 'Add to Bag'}
          </button>
        </div>
      </div>
    </div>
  );
}
