import { Link } from 'react-router-dom';
import { useWishlistStore } from '@/store/useWishlistStore';
import { ProductGrid } from '@/components/product/ProductGrid';
import { EmptyState } from '@/components/common/EmptyState';
import { Button } from '@/components/common/Button';
import { PageHero } from '@/components/common/PageHero';
import { ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';

export default function WishlistPage() {
  const items = useWishlistStore((s) => s.items);
  const isLoading = useWishlistStore((s) => s.isLoading);

  return (
    <div>
      <PageHero pageKey="wishlist" fallbackTitle="My Wishlist" />
      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">My Wishlist</h1>
        <p className="mt-2 text-sm text-[var(--color-text-secondary)]">
          {items.length} {items.length === 1 ? 'item' : 'items'} saved
        </p>

        <div className="mt-8">
          {!isLoading && items.length === 0 ? (
            <EmptyState
              title="Your wishlist is empty"
              description={SITE_CONFIG.microcopy.emptyWishlistDescription}
              action={
                <Link to={ROUTES.shop}>
                  <Button variant="primary">Explore Shop</Button>
                </Link>
              }
            />
          ) : (
            <ProductGrid products={items} isLoading={isLoading} columns={4} />
          )}
        </div>
      </div>
    </div>
  );
}
