import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useWishlistStore } from '@/store/useWishlistStore';
import { useCartStore } from '@/store/useCartStore';
import { EmptyState } from '@/components/common/EmptyState';
import { Button } from '@/components/common/Button';
import { PageHero } from '@/components/common/PageHero';
import { Rating } from '@/components/common/Rating';
import { ROUTES, buildRoute } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';
import { texts } from '@/config/texts';
import { useFormatCurrency } from '@/utils/formatCurrency';
import { SEO } from '@/components/common/SEO';

export default function WishlistPage() {
  const items = useWishlistStore((s) => s.items);
  const isLoading = useWishlistStore((s) => s.isLoading);
  const remove = useWishlistStore((s) => s.remove);
  const addItem = useCartStore((s) => s.addItem);
  const [copied, setCopied] = useState(false);
  const [movingId, setMovingId] = useState<string | null>(null);
  const formatCurrency = useFormatCurrency();

  function handleShareWishlist() {
    const ids = items.map((p) => p.id).join(',');
    const url = `${window.location.origin}${ROUTES.wishlist}?ids=${encodeURIComponent(ids)}`;

    if (navigator.share) {
      navigator.share({ title: texts.wishlist.shareTitle, url }).catch(() => {});
    } else {
      navigator.clipboard?.writeText(url).then(() => {
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
      });
    }
  }

  async function handleMoveToCart(productId: string) {
    const product = items.find((p) => p.id === productId);
    if (!product) return;
    setMovingId(productId);
    await addItem(product, 1);
    await remove(productId);
    setMovingId(null);
  }

  return (
    <div>
      <SEO title={texts.wishlist.title} description={`${texts.wishlist.title} — ${SITE_CONFIG.brand.name}`} />
      <PageHero pageKey="wishlist" fallbackTitle={texts.wishlist.title} />
      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">{texts.wishlist.title}</h1>
        <div className="mt-4 flex items-center justify-between">
          <p className="text-sm text-[var(--color-text-secondary)]">
            {texts.wishlist.saved(items.length)}
          </p>
          {items.length > 0 && (
            <Button variant="outline" size="sm" onClick={handleShareWishlist}>
              {copied ? texts.wishlist.copied : texts.wishlist.share}
            </Button>
          )}
        </div>

        <div className="mt-8">
          {!isLoading && items.length === 0 ? (
            <EmptyState
              title={texts.wishlist.empty}
              description={SITE_CONFIG.microcopy.emptyWishlistDescription}
              action={
                <Link to={ROUTES.shop}>
                  <Button variant="primary">{texts.common.exploreShop}</Button>
                </Link>
              }
            />
          ) : (
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {items.map((product) => {
                const isOutOfStock = product.stock === 0;
                const isMoving = movingId === product.id;
                return (
                  <div key={product.id} className="group rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] transition-shadow hover:shadow-[var(--shadow-card)]">
                    {/* Product Image */}
                    <Link to={buildRoute(ROUTES.product, { productSlug: product.slug })} className="block overflow-hidden rounded-t-[var(--radius-card)]">
                      <div className="aspect-[4/5] overflow-hidden bg-[var(--color-bg-cream)]">
                        <img
                          src={product.thumbnail}
                          alt={product.name}
                          className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                      </div>
                    </Link>

                    {/* Product Info */}
                    <div className="p-4">
                      <Link to={buildRoute(ROUTES.product, { productSlug: product.slug })}>
                        <h3 className="font-display text-sm font-semibold text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)] line-clamp-2">
                          {product.name}
                        </h3>
                      </Link>

                      <div className="mt-1.5">
                        <Rating value={product.rating} reviewCount={product.reviewCount} size="xs" />
                      </div>

                      <div className="mt-2 flex items-baseline gap-2">
                        <span className="font-display text-lg font-semibold text-[var(--color-primary)]">
                          {formatCurrency(product.price)}
                        </span>
                        {product.compareAtPrice && (
                          <span className="text-xs text-[var(--color-text-muted)] line-through">
                            {formatCurrency(product.compareAtPrice)}
                          </span>
                        )}
                      </div>

                      {/* Actions */}
                      <div className="mt-4 flex gap-2">
                        <Button
                          variant="primary"
                          size="sm"
                          className="flex-1"
                          disabled={isOutOfStock || isMoving}
                          isLoading={isMoving}
                          onClick={() => handleMoveToCart(product.id)}
                        >
                          {isOutOfStock ? texts.product.outOfStock : 'Move to Cart'}
                        </Button>
                        <button
                          onClick={() => remove(product.id)}
                          className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full border border-[var(--color-border)] text-[var(--color-text-muted)] transition-colors hover:border-[var(--color-danger)] hover:text-[var(--color-danger)]"
                          aria-label="Remove from wishlist"
                        >
                          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                            <path d="M18 6L6 18M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {/* Suggested Products */}
        {items.length > 0 && (
          <div className="mt-16 border-t border-[var(--color-border)] pt-10">
            <h2 className="font-display text-xl text-[var(--color-text-primary)]">You might also like</h2>
            <p className="mt-1 text-sm text-[var(--color-text-muted)]">More {SITE_CONFIG.terminology.qualityAdjective.toLowerCase()} {SITE_CONFIG.terminology.productUnitPlural} from our collection</p>
            <Link to={ROUTES.shop}>
              <Button variant="outline" size="sm" className="mt-4">
                {texts.common.shopNow}
              </Button>
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
