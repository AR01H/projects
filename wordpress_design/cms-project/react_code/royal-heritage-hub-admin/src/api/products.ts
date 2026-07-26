/**
 * Admin Products API — Full CRUD
 */

import { apiClient, safe } from './client';
import { MOCK_PRODUCTS } from '@/data/mockData';
import type { Product } from '@/types';

async function getAll(): Promise<Product[]> {
  if (apiClient.useMock) return MOCK_PRODUCTS;
  return apiClient.get<Product[]>('/admin/products');
}

export const productsApi = {
  getAll: () => safe(getAll),
  getById: (id: string) => safe(async () => { const all = await getAll(); return all.find((p) => p.id === id) ?? null; }),
  getBySlug: (slug: string) => safe(async () => { const all = await getAll(); return all.find((p) => p.slug === slug) ?? null; }),
  create: (data: Partial<Product>) => safe(async () => {
    if (apiClient.useMock) { const p = { ...data, id: `prod-${Date.now()}` } as Product; return p; }
    return apiClient.post<Product>('/admin/products', data);
  }),
  update: (id: string, data: Partial<Product>) => safe(async () => {
    if (apiClient.useMock) { return data as Product; }
    return apiClient.put<Product>(`/admin/products/${id}`, data);
  }),
  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/admin/products/${id}`);
  }),
  bulkDelete: (ids: string[]) => safe(async () => {
    if (apiClient.useMock) return ids.length;
    return apiClient.post<number>('/admin/products/bulk-delete', { ids });
  }),
  updateStock: (id: string, stock: number) => safe(async () => {
    if (apiClient.useMock) return { id, stock };
    return apiClient.put(`/admin/products/${id}/stock`, { stock });
  }),
};
