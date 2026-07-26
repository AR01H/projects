import { apiClient, safe } from './client';
import { MOCK_FOOTER } from '@/data/mockData';
import type { FooterData } from '@/types';

export const footerApi = {
  getAll: () => safe(async (): Promise<FooterData> => {
    if (apiClient.useMock) return MOCK_FOOTER;
    return apiClient.get<FooterData>('/admin/footer');
  }),
  update: (data: Partial<FooterData>) => safe(async () => {
    if (apiClient.useMock) return { ...MOCK_FOOTER, ...data } as FooterData;
    return apiClient.put<FooterData>('/admin/footer', data);
  }),
};
