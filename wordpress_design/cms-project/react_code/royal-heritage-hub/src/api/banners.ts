import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_BANNERS } from '@/data/mockData';
import type { Banner } from '@/types/product';

interface BannersResponse {
  hero: Banner[];
  promo: Banner[];
  pageHeroes: Record<string, Banner>;
}

async function getBanners(): Promise<BannersResponse> {
  if (apiClient.useMock) return MOCK_BANNERS;
  return apiClient.get<BannersResponse>(ENDPOINTS.banners.list);
}

export const bannersApi = {
  getAll: getBanners,
  getHero: async (): Promise<Banner[]> => (await getBanners()).hero,
  getPromo: async (): Promise<Banner[]> => (await getBanners()).promo,
  getPageHero: async (pageKey: string): Promise<Banner | undefined> =>
    (await getBanners()).pageHeroes[pageKey],
};
