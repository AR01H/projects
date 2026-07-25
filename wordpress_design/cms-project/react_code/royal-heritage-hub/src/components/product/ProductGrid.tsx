import { ProductCard } from './ProductCard';
import { ProductCardSkeleton } from '@/components/common/Skeleton';
import { EmptyState } from '@/components/common/EmptyState';
import type { Product } from '@/types/product';

interface ProductGridProps {
  products: Product[];
  isLoading?: boolean;
  columns?: 2 | 3 | 4;
}

const COLS: Record<number, string> = {
  2: 'grid-cols-2',
  3: 'grid-cols-2 md:grid-cols-3',
  4: 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
};

export function ProductGrid({ products, isLoading, columns = 4 }: ProductGridProps) {
  if (isLoading) {
    return (
      <div className={`grid ${COLS[columns]} gap-x-5 gap-y-9`}>
        {Array.from({ length: columns * 2 }).map((_, i) => (
          <ProductCardSkeleton key={i} />
        ))}
      </div>
    );
  }

  if (products.length === 0) {
    return (
      <EmptyState
        title="No products found"
        description="Try adjusting your filters or explore our full collection instead."
      />
    );
  }

  return (
    <div className={`grid ${COLS[columns]} gap-x-5 gap-y-9`}>
      {products.map((product) => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}
