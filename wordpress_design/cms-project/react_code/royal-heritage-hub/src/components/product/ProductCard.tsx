import { Link } from 'react-router-dom';
import { buildRoute, ROUTES } from '@/config/routes';
import { formatDiscount, useFormatCurrency } from '@/utils/formatCurrency';
import { Badge } from '@/components/common/Badge';
import { Rating } from '@/components/common/Rating';
import { useCartStore } from '@/store/useCartStore';
import { useWishlistStore } from '@/store/useWishlistStore';
import type { Product } from '@/types/product';

interface ProductCardProps {
  product: Product;
}

export function ProductCard({ product }: ProductCardProps) {
  const addItem = useCartStore((s) => s.addItem);
  const toggleWishlist = useWishlistStore((s) => s.toggle);
  const isWishlisted = useWishlistStore((s) => s.isWishlisted(product.id));
  const formatCurrency = useFormatCurrency();

  const discount = formatDiscount(product.price, product.compareAtPrice);
  const isLowStock = product.stock > 0 && product.stock <= product.lowStockThreshold;
  const isOutOfStock = product.stock === 0;

  return (
    <div className="group relative flex flex-col transition-transform duration-300 hover:-translate-y-1">
      <Link
        to={buildRoute(ROUTES.product, { productSlug: product.slug })}
        className="relative block overflow-hidden rounded-[var(--radius-card)] bg-[var(--color-bg-cream)] shadow-[var(--shadow-card)] transition-shadow duration-300 group-hover:shadow-[var(--shadow-hover)]"
      >
        <div className="aspect-[4/5] w-full overflow-hidden">
          <img
            src={product.thumbnail}
            alt={product.name}
            loading="lazy"
            className="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.08]"
          />
        </div>

        <div className="absolute left-3 top-3 flex flex-col gap-1.5">
          {product.isBestSeller && <Badge variant="dark">Best Seller</Badge>}
          {product.isNewArrival && <Badge variant="gold">New</Badge>}
          {discount && <Badge variant="danger">{discount}% OFF</Badge>}
        </div>

        {isLowStock && !isOutOfStock && (
          <div className="absolute bottom-3 left-3">
            <Badge variant="outline">Only {product.stock} left</Badge>
          </div>
        )}
        {isOutOfStock && (
          <div className="absolute inset-0 flex items-center justify-center bg-[var(--color-dark)]/40 backdrop-blur-[1px]">
            <Badge variant="dark">Out of Stock</Badge>
          </div>
        )}

        <button
          type="button"
          onClick={(e) => {
            e.preventDefault();
            toggleWishlist(product);
          }}
          aria-label={isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'}
          className="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-[var(--color-bg-light)]/90 shadow-sm transition-transform duration-200 hover:scale-110"
        >
          <svg
            viewBox="0 0 24 24"
            className="h-4 w-4"
            fill={isWishlisted ? 'var(--color-primary)' : 'none'}
            stroke="var(--color-primary)"
            strokeWidth="1.8"
          >
            <path d="M12 21s-7.5-4.6-10-9.1C.5 8.3 2.2 4.8 5.8 4.2c2-.3 3.9.7 5 2.3 1.1-1.6 3-2.6 5-2.3 3.6.6 5.3 4.1 3.8 7.7C19.5 16.4 12 21 12 21z" />
          </svg>
        </button>

        <button
          type="button"
          onClick={(e) => {
            e.preventDefault();
            if (!isOutOfStock) addItem(product);
          }}
          disabled={isOutOfStock}
          className="absolute inset-x-3 bottom-3 translate-y-[130%] rounded-[var(--radius-btn)] bg-[var(--color-dark)] py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-bg-light)] opacity-0 transition-all duration-300 ease-out group-hover:translate-y-0 group-hover:opacity-100 disabled:cursor-not-allowed disabled:bg-[var(--color-text-muted)] md:block hidden"
        >
          Quick Add
        </button>
      </Link>

      <div className="mt-3.5 flex flex-col gap-1">
        <p className="text-[0.7rem] uppercase tracking-wider text-[var(--color-text-muted)]">
          {product.specs.find((s) => s.highlight)?.value.split(',')[0] ?? product.specs[0]?.value ?? ''}
        </p>
        <Link
          to={buildRoute(ROUTES.product, { productSlug: product.slug })}
          className="font-display text-[0.98rem] leading-snug text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
        >
          {product.name}
        </Link>
        <Rating value={product.rating} reviewCount={product.reviewCount} />
        <div className="mt-0.5 flex items-baseline gap-2">
          <span className="font-semibold text-[var(--color-primary)]">
            {formatCurrency(product.price)}
          </span>
          {product.compareAtPrice && (
            <span className="text-xs text-[var(--color-text-muted)] line-through">
              {formatCurrency(product.compareAtPrice)}
            </span>
          )}
        </div>
      </div>
    </div>
  );
}
