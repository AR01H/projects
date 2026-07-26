/**
 * Admin Orders API — List, Update Status, Details
 */

import { apiClient, safe } from './client';
import { MOCK_ORDERS, MOCK_DASHBOARD_STATS } from '@/data/mockData';
import type { Order } from '@/types';

async function getAll(): Promise<Order[]> {
  if (apiClient.useMock) return MOCK_ORDERS;
  return apiClient.get<Order[]>('/admin/orders');
}

export const ordersApi = {
  getAll: () => safe(getAll),
  getById: (id: string) => safe(async () => {
    if (apiClient.useMock) { const all = await getAll(); return all.find((o) => o.id === id) ?? null; }
    return apiClient.get<Order>(`/admin/orders/${id}`);
  }),
  updateStatus: (id: string, status: Order['status']) => safe(async () => {
    if (apiClient.useMock) return { id, status, updatedAt: new Date().toISOString() } as Order;
    return apiClient.put<Order>(`/admin/orders/${id}/status`, { status });
  }),
  getStats: () => safe(async () => {
    if (apiClient.useMock) return MOCK_DASHBOARD_STATS;
    return apiClient.get('/admin/orders/stats');
  }),
};
