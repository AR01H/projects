import { productsApi } from './products';
import type { Product, ProductReview } from '@/types/product';

export interface AggregatedReview extends ProductReview {
  product: Pick<Product, 'id' | 'name' | 'slug' | 'thumbnail'>;
}

export const reviewsApi = {
  getAll: async (): Promise<AggregatedReview[]> => {
    const products = await productsApi.getAll();
    const all: AggregatedReview[] = [];
    products.forEach((p) => {
      p.reviews.forEach((r) => {
        all.push({
          ...r,
          product: { id: p.id, name: p.name, slug: p.slug, thumbnail: p.thumbnail },
        });
      });
    });
    return all.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  },

  getStats: async () => {
    const products = await productsApi.getAll();
    const totalReviews = products.reduce((sum, p) => sum + p.reviewCount, 0);
    const avgRating =
      products.reduce((sum, p) => sum + p.rating * p.reviewCount, 0) / (totalReviews || 1);
    const distribution = [5, 4, 3, 2, 1].map((star) => {
      const count = products
        .flatMap((p) => p.reviews)
        .filter((r) => Math.round(r.rating) === star).length;
      return { star, count };
    });
    return { totalReviews, avgRating, distribution };
  },
};
