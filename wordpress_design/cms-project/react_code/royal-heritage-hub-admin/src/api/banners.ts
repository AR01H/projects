/**
 * Admin Banners API — Full CRUD
 */

import { apiClient, safe } from './client';
import { MOCK_BANNERS } from '@/data/mockData';
import type { Banner } from '@/types';

export const bannersApi = {
  getAll: () => safe(async (): Promise<Banner[]> => {
    if (apiClient.useMock) return MOCK_BANNERS;
    return apiClient.get<Banner[]>('/api/admin/banners');
  }),
  create: (data: Partial<Banner>) => safe(async () => {
    if (apiClient.useMock) return { ...data, id: `b-${Date.now()}` } as Banner;
    return apiClient.post<Banner>('/api/admin/banners', data);
  }),
  update: (id: string, data: Partial<Banner>) => safe(async () => {
    if (apiClient.useMock) return data as Banner;
    return apiClient.put<Banner>(`/api/admin/banners/${id}`, data);
  }),
  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/api/admin/banners/${id}`);
  }),
  reorder: (ids: string[]) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.put('/api/admin/banners/reorder', { ids });
  }),
};
