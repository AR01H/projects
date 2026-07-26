import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_COLLECTIONS } from '@/data/mockData';
import type { Collection } from '@/types/product';

interface CollectionsResponse { data: Collection[]; }

async function getAllCollections(): Promise<Collection[]> {
  if (apiClient.useMock) return MOCK_COLLECTIONS;
  const res = await apiClient.get<CollectionsResponse>(ENDPOINTS.collections.list);
  return res.data ?? res as unknown as Collection[];
}

export const collectionsApi = {
  getAll: getAllCollections,

  getBySlug: async (slug: string): Promise<Collection | undefined> => {
    const all = await getAllCollections();
    return all.find((c) => c.slug === slug);
  },
};
