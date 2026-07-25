import { apiClient } from './client';
import type { CertificationEntry } from '@/types/product';

export const certificationsApi = {
  getAll: async (): Promise<CertificationEntry[]> => {
    if (apiClient.useMock) {
      return apiClient.mock<CertificationEntry[]>(
        () => import('@/data/certifications.json') as unknown as Promise<{ default: CertificationEntry[] }>
      );
    }
    return [];
  },
};
