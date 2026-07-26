import { apiClient, safe } from './client';
import { MOCK_SETTINGS } from '@/data/mockData';
import type { StoreSettings } from '@/types';

export const settingsApi = {
  getAll: () => safe(async (): Promise<StoreSettings> => {
    if (apiClient.useMock) return MOCK_SETTINGS;
    return apiClient.get<StoreSettings>('/admin/settings');
  }),
  update: (data: Partial<StoreSettings>) => safe(async () => {
    if (apiClient.useMock) return { ...MOCK_SETTINGS, ...data } as StoreSettings;
    return apiClient.put<StoreSettings>('/admin/settings', data);
  }),
};
