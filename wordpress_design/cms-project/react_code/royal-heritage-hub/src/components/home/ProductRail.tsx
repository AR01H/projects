import { useEffect, useState } from 'react';
import { SectionHeading } from '@/components/common/SectionHeading';
import { HorizontalScroller } from '@/components/common/HorizontalScroller';
import { ProductCard } from '@/components/product/ProductCard';
import { ProductCardSkeleton } from '@/components/common/Skeleton';
import type { Product } from '@/types/product';

interface ProductRailProps {
  eyebrow?: string;
  title: string;
  description?: string;
  viewAllLink?: string;
  fetcher: () => Promise<Product[]>;
}

export function ProductRail({ eyebrow, title, description, viewAllLink, fetcher }: ProductRailProps) {
  const [products, setProducts] = useState<Product[] | null>(null);

  useEffect(() => {
    let mounted = true;
    fetcher().then((data) => {
      if (mounted) setProducts(data);
    });
    return () => {
      mounted = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (products && products.length === 0) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow={eyebrow} title={title} description={description} viewAllLink={viewAllLink} />
      <HorizontalScroller>
        {(products ?? Array.from({ length: 4 })).map((p, i) =>
          p ? (
            <div key={(p as Product).id} className="w-48 flex-shrink-0 sm:w-60">
              <ProductCard product={p as Product} />
            </div>
          ) : (
            <div key={i} className="w-48 flex-shrink-0 sm:w-60">
              <ProductCardSkeleton />
            </div>
          )
        )}
      </HorizontalScroller>
    </section>
  );
}
