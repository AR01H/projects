import { apiClient } from './client';
import { MOCK_CERTIFICATIONS } from '@/data/mockData';
import type { CertificationEntry } from '@/types/product';

export const certificationsApi = {
  getAll: async (): Promise<CertificationEntry[]> => {
    if (apiClient.useMock) return MOCK_CERTIFICATIONS;
    return [];
  },
};
