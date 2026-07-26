import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_CERTIFICATIONS } from '@/data/mockData';
import type { CertificationEntry } from '@/types/product';

export const certificationsApi = {
  getAll: async (): Promise<CertificationEntry[]> => {
    if (apiClient.useMock) return MOCK_CERTIFICATIONS;
    try {
      const res = await apiClient.get<{ data: CertificationEntry[] }>(ENDPOINTS.certifications.list);
      return res.data ?? res as unknown as CertificationEntry[];
    } catch { return []; }
  },
};
