import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { collectionsApi } from '@/api/collections';
import { PageHero } from '@/components/common/PageHero';
import { SITE_CONFIG } from '@/config/site';
import type { Collection } from '@/types/product';

export default function CollectionsPage() {
  const [collections, setCollections] = useState<Collection[]>([]);

  useEffect(() => {
    collectionsApi.getAll().then(setCollections);
  }, []);

  return (
    <div>
      <PageHero pageKey="collections" fallbackTitle="Collections" />
      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">Collections</h1>
      <p className="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)]">
        {SITE_CONFIG.microcopy.collectionsPageDescription}
      </p>

      <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {collections.map((c) => (
          <Link
            key={c.id}
            to={`/collections/${c.slug}`}
            className="group relative overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]"
          >
            <div className="aspect-[4/3] w-full overflow-hidden">
              <img src={c.image} alt={c.name} className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
              <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/80 via-[var(--color-dark)]/10 to-transparent" />
            </div>
            <div className="absolute inset-x-0 bottom-0 p-5">
              <h3 className="font-display text-xl text-[var(--color-bg-light)]">{c.name}</h3>
              <p className="mt-1 text-xs text-[var(--color-bg-light)]/80">{c.description}</p>
            </div>
          </Link>
        ))}
      </div>
      </div>
    </div>
  );
}
