import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_BANNERS } from '@/data/mockData';
import type { Banner } from '@/types/product';

interface BannersResponse {
  hero: Banner[];
  promo: Banner[];
  pageHeroes: Record<string, Banner>;
}

interface BannerApiItem {
  id: string;
  title: string;
  subtitle?: string;
  ctaLabel?: string;
  ctaLink?: string;
  image?: string;
  theme?: string;
  position?: string;
  sortOrder?: number;
}

async function getBanners(): Promise<BannersResponse> {
  if (apiClient.useMock) return MOCK_BANNERS;

  try {
    const res = await apiClient.get<{ data: BannerApiItem[] } | BannerApiItem[]>(ENDPOINTS.banners.list);
    const items = (res as any).data ?? res;

    if (Array.isArray(items)) {
      const hero = items.filter((b) => b.position === 'hero').map(mapBanner);
      const promo = items.filter((b) => b.position === 'promo').map(mapBanner);
      return { hero: hero.length ? hero : MOCK_BANNERS.hero, promo, pageHeroes: {} };
    }
  } catch { /* fallback */ }

  return MOCK_BANNERS;
}

function mapBanner(b: BannerApiItem): Banner {
  return {
    id: b.id, title: b.title, subtitle: b.subtitle || '',
    ctaText: b.ctaLabel || b.ctaText || '', ctaLink: b.ctaLink || '',
    image: b.image || '', theme: (b.theme || 'light') as 'light' | 'dark',
  } as Banner;
}

export const bannersApi = {
  getAll: getBanners,
  getHero: async (): Promise<Banner[]> => (await getBanners()).hero,
  getPromo: async (): Promise<Banner[]> => (await getBanners()).promo,
  getPageHero: async (pageKey: string): Promise<Banner | undefined> =>
    (await getBanners()).pageHeroes[pageKey],
};
