import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { collectionsApi } from '@/api/collections';
import { productsApi } from '@/api/products';
import { ProductGrid } from '@/components/product/ProductGrid';
import { PageLoader } from '@/components/common/Skeleton';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { ROUTES } from '@/config/routes';
import { SEO } from '@/components/common/SEO';
import type { Collection, Product } from '@/types/product';

export default function CollectionDetailPage() {
  const { collectionSlug } = useParams();
  const [collection, setCollection] = useState<Collection | null | undefined>(undefined);
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    if (!collectionSlug) return;
    collectionsApi.getBySlug(collectionSlug).then(async (c) => {
      setCollection(c ?? null);
      if (c) setProducts(await productsApi.getByIds(c.productIds));
    });
  }, [collectionSlug]);

  if (collection === undefined) return <PageLoader />;
  if (collection === null) return <div className="py-24 text-center">Collection not found.</div>;

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SEO title={collection.name} description={collection.description} />
      <Breadcrumbs items={[{ label: 'Collections', href: ROUTES.collections }, { label: collection.name }]} />
      <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">{collection.name}</h1>
      <p className="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)]">{collection.description}</p>
      <div className="mt-8">
        <ProductGrid products={products} columns={4} />
      </div>
    </div>
  );
}
