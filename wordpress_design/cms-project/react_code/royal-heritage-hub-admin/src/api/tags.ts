import { apiClient, safe } from './client';
import { MOCK_TAGS } from '@/data/mockData';
import type { TagMeta } from '@/types';

async function getAll(): Promise<TagMeta[]> {
  if (apiClient.useMock) return MOCK_TAGS;
  return apiClient.get<TagMeta[]>('/admin/tags');
}

export const tagsApi = {
  getAll: () => safe(getAll),
  create: (data: Partial<TagMeta>) => safe(async () => {
    if (apiClient.useMock) return { ...data } as TagMeta;
    return apiClient.post<TagMeta>('/admin/tags', data);
  }),
  update: (tag: string, data: Partial<TagMeta>) => safe(async () => {
    if (apiClient.useMock) return data as TagMeta;
    return apiClient.put<TagMeta>(`/admin/tags/${tag}`, data);
  }),
  delete: (tag: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/admin/tags/${tag}`);
  }),
};
