import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import type { Collection } from '@/types/product';

async function getAllCollections(): Promise<Collection[]> {
  if (apiClient.useMock) {
    return apiClient.mock<Collection[]>(() => import('@/data/collections.json'));
  }
  return apiClient.get<Collection[]>(ENDPOINTS.collections.list);
}

export const collectionsApi = {
  getAll: getAllCollections,

  getBySlug: async (slug: string): Promise<Collection | undefined> => {
    const all = await getAllCollections();
    return all.find((c) => c.slug === slug);
  },
};
