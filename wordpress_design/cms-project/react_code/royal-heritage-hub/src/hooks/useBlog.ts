import { useState, useEffect } from 'react';
import { blogApi } from '@/api/blog';
import type { BlogPost, BlogCategory } from '@/types/product';

export function useBlogPosts(categorySlug?: string) {
  const [data, setData] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    const fetcher = categorySlug ? () => blogApi.getByCategory(categorySlug) : () => blogApi.getAllPosts();
    fetcher().then(setData).finally(() => setLoading(false));
  }, [categorySlug]);

  return { data, loading };
}

export function useBlogPost(slug: string | undefined) {
  const [post, setPost] = useState<BlogPost | null | undefined>(undefined);

  useEffect(() => {
    if (!slug) return;
    setPost(undefined);
    blogApi.getBySlug(slug).then((p) => setPost(p ?? null));
  }, [slug]);

  return post;
}

export function useBlogCategories() {
  const [data, setData] = useState<BlogCategory[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    blogApi.getAllCategories().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

export function useRecentPosts(limit = 5) {
  const [data, setData] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    blogApi.getRecent(limit).then(setData).finally(() => setLoading(false));
  }, [limit]);

  return { data, loading };
}
