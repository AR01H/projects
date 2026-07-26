/**
 * Admin Banners API — Full CRUD
 */

import { apiClient, safe } from './client';
import { MOCK_BANNERS } from '@/data/mockData';
import type { Banner } from '@/types';

export const bannersApi = {
  getAll: () => safe(async (): Promise<Banner[]> => {
    if (apiClient.useMock) return MOCK_BANNERS;
    return apiClient.get<Banner[]>('/admin/banners');
  }),
  create: (data: Partial<Banner>) => safe(async () => {
    if (apiClient.useMock) return { ...data, id: `b-${Date.now()}` } as Banner;
    return apiClient.post<Banner>('/admin/banners', data);
  }),
  update: (id: string, data: Partial<Banner>) => safe(async () => {
    if (apiClient.useMock) return data as Banner;
    return apiClient.put<Banner>(`/admin/banners/${id}`, data);
  }),
  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/admin/banners/${id}`);
  }),
  reorder: (ids: string[]) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.put('/admin/banners/reorder', { ids });
  }),
};
