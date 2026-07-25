import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_COLLECTIONS } from '@/data/mockData';
import type { Collection } from '@/types/product';

async function getAllCollections(): Promise<Collection[]> {
  if (apiClient.useMock) return MOCK_COLLECTIONS;
  return apiClient.get<Collection[]>(ENDPOINTS.collections.list);
}

export const collectionsApi = {
  getAll: getAllCollections,

  getBySlug: async (slug: string): Promise<Collection | undefined> => {
    const all = await getAllCollections();
    return all.find((c) => c.slug === slug);
  },
};
