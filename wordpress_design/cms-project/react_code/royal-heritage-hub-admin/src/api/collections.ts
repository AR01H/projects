import { apiClient, safe } from './client';
import { MOCK_COLLECTIONS } from '@/data/mockData';
import type { Collection } from '@/types';

async function getAll(): Promise<Collection[]> {
  if (apiClient.useMock) return MOCK_COLLECTIONS;
  return apiClient.get<Collection[]>('/api/admin/collections');
}

export const collectionsApi = {
  getAll: () => safe(getAll),
  getById: (id: string) => safe(async () => { const all = await getAll(); return all.find((c) => c.id === id) ?? null; }),
  getBySlug: (slug: string) => safe(async () => { const all = await getAll(); return all.find((c) => c.slug === slug) ?? null; }),
  create: (data: Partial<Collection>) => safe(async () => {
    if (apiClient.useMock) return { ...data, id: `col-${Date.now()}` } as Collection;
    return apiClient.post<Collection>('/api/admin/collections', data);
  }),
  update: (id: string, data: Partial<Collection>) => safe(async () => {
    if (apiClient.useMock) return data as Collection;
    return apiClient.put<Collection>(`/api/admin/collections/${id}`, data);
  }),
  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/api/admin/collections/${id}`);
  }),
};
