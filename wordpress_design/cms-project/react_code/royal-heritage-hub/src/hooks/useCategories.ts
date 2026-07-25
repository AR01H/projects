import { useState, useEffect } from 'react';
import { categoryApi } from '@/api/category';
import type { Category } from '@/types/product';

export function useCategories() {
  const [data, setData] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    categoryApi.getAll().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

export function useFeaturedCategories(limit = 6) {
  const [data, setData] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    categoryApi.getFeatured(limit).then(setData).finally(() => setLoading(false));
  }, [limit]);

  return { data, loading };
}

export function useCategory(slug: string | undefined) {
  const [category, setCategory] = useState<Category | null | undefined>(undefined);

  useEffect(() => {
    if (!slug) return;
    setCategory(undefined);
    categoryApi.getBySlug(slug).then((c) => setCategory(c ?? null));
  }, [slug]);

  return category;
}

export function useCategoryTree() {
  const [data, setData] = useState<{ parent: Category; children: Category[] }[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    categoryApi.getTree().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}
