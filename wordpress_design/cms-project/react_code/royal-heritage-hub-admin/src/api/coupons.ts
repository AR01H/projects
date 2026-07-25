/**
 * Admin Coupons API — Full CRUD
 */

import { apiClient, safe } from './client';
import { MOCK_COUPONS } from '@/data/mockData';
import type { Coupon } from '@/types';

export const couponsApi = {
  getAll: () => safe(async (): Promise<Coupon[]> => {
    if (apiClient.useMock) return MOCK_COUPONS;
    return apiClient.get<Coupon[]>('/api/admin/coupons');
  }),
  create: (data: Partial<Coupon>) => safe(async () => {
    if (apiClient.useMock) return { ...data, usedCount: 0, active: true } as Coupon;
    return apiClient.post<Coupon>('/api/admin/coupons', data);
  }),
  update: (code: string, data: Partial<Coupon>) => safe(async () => {
    if (apiClient.useMock) return data as Coupon;
    return apiClient.put<Coupon>(`/api/admin/coupons/${code}`, data);
  }),
  delete: (code: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/api/admin/coupons/${code}`);
  }),
  toggleActive: (code: string) => safe(async () => {
    if (apiClient.useMock) return { code, active: true };
    return apiClient.put(`/api/admin/coupons/${code}/toggle`);
  }),
};
