import { apiClient, safe } from './client';
import { MOCK_REVIEWS } from '@/data/mockData';
import type { Review } from '@/types';

async function getAll(): Promise<Review[]> {
  if (apiClient.useMock) return MOCK_REVIEWS;
  return apiClient.get<Review[]>('/api/admin/reviews');
}

export const reviewsApi = {
  getAll: () => safe(getAll),
  getById: (id: string) => safe(async () => { const all = await getAll(); return all.find((r) => r.id === id) ?? null; }),
  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/api/admin/reviews/${id}`);
  }),
  getStats: () => safe(async () => {
    const all = await getAll();
    const totalReviews = all.length;
    const avgRating = all.reduce((sum, r) => sum + r.rating, 0) / (totalReviews || 1);
    const distribution = [5, 4, 3, 2, 1].map((star) => ({ star, count: all.filter((r) => r.rating === star).length }));
    return { totalReviews, avgRating, distribution };
  }),
};
