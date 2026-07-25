import { useState, useEffect } from 'react';
import { bannersApi } from '@/api/banners';
import type { Banner } from '@/types/product';

export function useHeroBanners() {
  const [data, setData] = useState<Banner[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    bannersApi.getHero().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

export function usePromoBanners() {
  const [data, setData] = useState<Banner[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    bannersApi.getPromo().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

export function usePageHero(pageKey: string) {
  const [banner, setBanner] = useState<Banner | null | undefined>(undefined);

  useEffect(() => {
    bannersApi.getPageHero(pageKey).then((b) => setBanner(b ?? null));
  }, [pageKey]);

  return banner;
}
