import { useState, useEffect } from 'react';
import { tagsApi, type TagSummary } from '@/api/tags';
import type { Product } from '@/types/product';

export function useTags() {
  const [data, setData] = useState<TagSummary[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    tagsApi.getAll().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

export function usePopularTags(limit = 12) {
  const [data, setData] = useState<TagSummary[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    tagsApi.getPopular(limit).then(setData).finally(() => setLoading(false));
  }, [limit]);

  return { data, loading };
}

export function useProductsByTag(tag: string | undefined) {
  const [data, setData] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!tag) return;
    setLoading(true);
    tagsApi.getByTag(tag).then(setData).finally(() => setLoading(false));
  }, [tag]);

  return { data, loading };
}
