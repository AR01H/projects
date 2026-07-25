/**
 * Admin Categories API — Full CRUD
 */

import { apiClient, safe } from './client';
import { MOCK_CATEGORIES } from '@/data/mockData';
import type { Category } from '@/types';

async function getAll(): Promise<Category[]> {
  if (apiClient.useMock) return MOCK_CATEGORIES;
  return apiClient.get<Category[]>('/api/admin/categories');
}

export const categoriesApi = {
  getAll: () => safe(getAll),
  getById: (id: string) => safe(async () => { const all = await getAll(); return all.find((c) => c.id === id) ?? null; }),
  create: (data: Partial<Category>) => safe(async () => {
    if (apiClient.useMock) return { ...data, id: `cat-${Date.now()}` } as Category;
    return apiClient.post<Category>('/api/admin/categories', data);
  }),
  update: (id: string, data: Partial<Category>) => safe(async () => {
    if (apiClient.useMock) return data as Category;
    return apiClient.put<Category>(`/api/admin/categories/${id}`, data);
  }),
  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/api/admin/categories/${id}`);
  }),
};
