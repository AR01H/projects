import { apiClient, safe } from './client';
import { MOCK_CERTIFICATIONS } from '@/data/mockData';
import type { CertificationEntry } from '@/types';

async function getAll(): Promise<CertificationEntry[]> {
  if (apiClient.useMock) return MOCK_CERTIFICATIONS;
  return apiClient.get<CertificationEntry[]>('/admin/certifications');
}

export const certificationsApi = {
  getAll: () => safe(getAll),
  getById: (id: string) => safe(async () => { const all = await getAll(); return all.find((c) => c.id === id) ?? null; }),
  create: (data: Partial<CertificationEntry>) => safe(async () => {
    if (apiClient.useMock) return { ...data, id: `cert-${Date.now()}` } as CertificationEntry;
    return apiClient.post<CertificationEntry>('/admin/certifications', data);
  }),
  update: (id: string, data: Partial<CertificationEntry>) => safe(async () => {
    if (apiClient.useMock) return data as CertificationEntry;
    return apiClient.put<CertificationEntry>(`/admin/certifications/${id}`, data);
  }),
  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/admin/certifications/${id}`);
  }),
};
