/**
 * Admin Customers API
 */

import { apiClient, safe } from './client';
import { MOCK_CUSTOMERS } from '@/data/mockData';
import type { Customer } from '@/types';

export const customersApi = {
  getAll: () => safe(async (): Promise<Customer[]> => {
    if (apiClient.useMock) return MOCK_CUSTOMERS;
    return apiClient.get<Customer[]>('/api/admin/customers');
  }),
  getById: (id: string) => safe(async () => {
    const { data } = await customersApi.getAll();
    return data?.find((c) => c.id === id) ?? null;
  }),
};
