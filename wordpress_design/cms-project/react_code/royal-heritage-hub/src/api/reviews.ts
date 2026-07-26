import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { productsApi } from './products';
import type { Product, ProductReview } from '@/types/product';

export interface AggregatedReview extends ProductReview {
  product: Pick<Product, 'id' | 'name' | 'slug' | 'thumbnail'>;
}

interface ReviewApiItem {
  id: string;
  productId: string;
  author: string;
  rating: number;
  title?: string;
  comment: string;
  date: string;
  verified: boolean;
  status: string;
}

interface ReviewStats {
  totalReviews: number;
  avgRating: number;
  distribution: { star: number; count: number }[];
}

export const reviewsApi = {
  getAll: async (): Promise<AggregatedReview[]> => {
    if (apiClient.useMock) {
      const products = await productsApi.getAll();
      const all: AggregatedReview[] = [];
      products.forEach((p) => {
        p.reviews.forEach((r) => {
          all.push({ ...r, product: { id: p.id, name: p.name, slug: p.slug, thumbnail: p.thumbnail } });
        });
      });
      return all.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
    }
    try {
      const res = await apiClient.get<{ data: ReviewApiItem[] }>(ENDPOINTS.reviews.list);
      const items = res.data ?? [];
      const products = await productsApi.getAll();
      const productMap = new Map(products.map((p) => [p.id, p]));
      return items.map((r) => {
        const p = productMap.get(r.productId);
        return {
          id: r.id, author: r.author, rating: r.rating, title: r.title || '',
          comment: r.comment, date: r.date, verified: r.verified,
          product: p ? { id: p.id, name: p.name, slug: p.slug, thumbnail: p.thumbnail } : { id: r.productId, name: 'Unknown', slug: '', thumbnail: '' },
        };
      }).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
    } catch { return []; }
  },

  getStats: async (): Promise<ReviewStats> => {
    if (apiClient.useMock) {
      const products = await productsApi.getAll();
      const totalReviews = products.reduce((sum, p) => sum + p.reviewCount, 0);
      const avgRating = products.reduce((sum, p) => sum + p.rating * p.reviewCount, 0) / (totalReviews || 1);
      const distribution = [5, 4, 3, 2, 1].map((star) => ({
        star,
        count: products.flatMap((p) => p.reviews).filter((r) => Math.round(r.rating) === star).length,
      }));
      return { totalReviews, avgRating, distribution };
    }
    try {
      const res = await apiClient.get<{ data: ReviewStats }>(ENDPOINTS.reviews.stats);
      return res.data ?? { totalReviews: 0, avgRating: 0, distribution: [] };
    } catch { return { totalReviews: 0, avgRating: 0, distribution: [] }; }
  },
};
