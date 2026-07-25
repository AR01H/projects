import { useState, useEffect } from 'react';
import { collectionsApi } from '@/api/collections';
import type { Collection } from '@/types/product';

export function useCollections() {
  const [data, setData] = useState<Collection[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    collectionsApi.getAll().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

export function useCollection(slug: string | undefined) {
  const [collection, setCollection] = useState<Collection | null | undefined>(undefined);

  useEffect(() => {
    if (!slug) return;
    setCollection(undefined);
    collectionsApi.getBySlug(slug).then((c) => setCollection(c ?? null));
  }, [slug]);

  return collection;
}
