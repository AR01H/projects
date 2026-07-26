import { useState, useEffect } from 'react';
import { productsApi, type ProductFilters } from '@/api/products';
import type { Product } from '@/types/product';

interface UseProductsState {
  data: Product[];
  loading: boolean;
  error: string | null;
}

export function useProducts(filters?: ProductFilters): UseProductsState {
  const [state, setState] = useState<UseProductsState>({ data: [], loading: true, error: null });

  useEffect(() => {
    let cancelled = false;
    setState((s) => ({ ...s, loading: true, error: null }));

    const fetcher = filters ? () => productsApi.getFiltered(filters) : () => productsApi.getAll();

    fetcher()
      .then((data) => { if (!cancelled) setState({ data, loading: false, error: null }); })
      .catch((err) => { if (!cancelled) setState({ data: [], loading: false, error: err.message }); });

    return () => { cancelled = true; };
  }, [JSON.stringify(filters)]);

  return state;
}

export function useProduct(slug: string | undefined) {
  const [product, setProduct] = useState<Product | null | undefined>(undefined);

  useEffect(() => {
    if (!slug) return;
    setProduct(undefined);
    productsApi.getBySlug(slug).then((p) => setProduct(p ?? null));
  }, [slug]);

  return product;
}

export function useBestSellers(limit = 8) {
  const [data, setData] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    productsApi.getBestSellers(limit).then(setData).finally(() => setLoading(false));
  }, [limit]);

  return { data, loading };
}

export function useNewArrivals(limit = 8) {
  const [data, setData] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    productsApi.getNewArrivals(limit).then(setData).finally(() => setLoading(false));
  }, [limit]);

  return { data, loading };
}

export function useFeatured(limit = 8) {
  const [data, setData] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    productsApi.getFeatured(limit).then(setData).finally(() => setLoading(false));
  }, [limit]);

  return { data, loading };
}

export function useTrending(limit = 8) {
  const [data, setData] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    productsApi.getTrending(limit).then(setData).finally(() => setLoading(false));
  }, [limit]);

  return { data, loading };
}

export function useProductFetcher(fetcher: () => Promise<Product[]>) {
  const [data, setData] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    fetcher().then((d) => { if (!cancelled) setData(d); }).finally(() => { if (!cancelled) setLoading(false); });
    return () => { cancelled = true; };
  }, []);

  return { data, loading };
}
