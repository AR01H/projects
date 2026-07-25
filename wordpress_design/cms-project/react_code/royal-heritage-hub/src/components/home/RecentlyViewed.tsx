import { useEffect, useState } from 'react';
import { productsApi } from '@/api/products';
import { useRecentlyViewedStore } from '@/store/useRecentlyViewedStore';
import { SectionHeading } from '@/components/common/SectionHeading';
import { HorizontalScroller } from '@/components/common/HorizontalScroller';
import { ProductCard } from '@/components/product/ProductCard';
import type { Product } from '@/types/product';

export function RecentlyViewed() {
  const ids = useRecentlyViewedStore((s) => s.ids);
  const init = useRecentlyViewedStore((s) => s.init);
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    init();
  }, [init]);

  useEffect(() => {
    if (ids.length === 0) {
      setProducts([]);
      return;
    }
    productsApi.getRecentlyViewed(ids).then(setProducts);
  }, [ids]);

  if (products.length === 0) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Pick Up Where You Left Off" title="Recently Viewed" />
      <HorizontalScroller>
        {products.map((p) => (
          <div key={p.id} className="w-44 flex-shrink-0 sm:w-56">
            <ProductCard product={p} />
          </div>
        ))}
      </HorizontalScroller>
    </section>
  );
}
