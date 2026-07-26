import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_PRODUCTS } from '@/data/mockData';
import type { Product } from '@/types/product';

export interface ProductFilters {
  categorySlug?: string;
  collectionSlug?: string;
  minPrice?: number;
  maxPrice?: number;
  material?: string;
  tag?: string;
  minRating?: number;
  inStockOnly?: boolean;
  search?: string;
  sortBy?: 'newest' | 'best-selling' | 'featured' | 'price-asc' | 'price-desc' | 'rating';
}

interface ProductsResponse { data: Product[]; }
interface ProductResponse { data: Product; }

async function getAllProducts(): Promise<Product[]> {
  if (apiClient.useMock) return MOCK_PRODUCTS;
  const res = await apiClient.get<ProductsResponse>(ENDPOINTS.products.list);
  return res.data ?? res as unknown as Product[];
}

export function filterProducts(products: Product[], filters: ProductFilters): Product[] {
  let result = [...products];

  if (filters.categorySlug) result = result.filter((p) => p.categorySlug === filters.categorySlug);
  if (filters.minPrice !== undefined) result = result.filter((p) => p.price >= filters.minPrice!);
  if (filters.maxPrice !== undefined) result = result.filter((p) => p.price <= filters.maxPrice!);
  if (filters.material) result = result.filter((p) => p.specs.some((s) => s.value.toLowerCase().includes(filters.material!.toLowerCase())));
  if (filters.tag) result = result.filter((p) => p.tags.includes(filters.tag!));
  if (filters.minRating !== undefined) result = result.filter((p) => p.rating >= filters.minRating!);
  if (filters.inStockOnly) result = result.filter((p) => p.stock > 0);
  if (filters.search) {
    const q = filters.search.toLowerCase();
    result = result.filter((p) => p.name.toLowerCase().includes(q) || p.tags.some((t) => t.toLowerCase().includes(q)));
  }

  switch (filters.sortBy) {
    case 'newest': result.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()); break;
    case 'price-asc': result.sort((a, b) => a.price - b.price); break;
    case 'price-desc': result.sort((a, b) => b.price - a.price); break;
    case 'rating': result.sort((a, b) => b.rating - a.rating); break;
    case 'best-selling': result.sort((a, b) => Number(b.isBestSeller) - Number(a.isBestSeller)); break;
    case 'featured': result.sort((a, b) => Number(b.isFeatured) - Number(a.isFeatured)); break;
  }
  return result;
}

export const productsApi = {
  getAll: getAllProducts,

  getBySlug: async (slug: string): Promise<Product | undefined> => {
    if (apiClient.useMock) {
      const all = await getAllProducts();
      return all.find((p) => p.slug === slug);
    }
    try {
      const res = await apiClient.get<ProductResponse>(ENDPOINTS.products.detail(slug));
      return res.data ?? res as unknown as Product;
    } catch { return undefined; }
  },

  getFiltered: async (filters: ProductFilters): Promise<Product[]> => {
    const all = await getAllProducts();
    return filterProducts(all, filters);
  },

  getBestSellers: async (limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.isBestSeller).slice(0, limit);
  },

  getNewArrivals: async (limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.isNewArrival).slice(0, limit);
  },

  getFeatured: async (limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.isFeatured).slice(0, limit);
  },

  getFestive: async (limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.isFestive).slice(0, limit);
  },

  getLimitedEdition: async (limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.isLimitedEdition).slice(0, limit);
  },

  getRelated: async (product: Product, limit = 4): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.id !== product.id && p.categoryId === product.categoryId).slice(0, limit);
  },

  getByIds: async (ids: string[]): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => ids.includes(p.id));
  },

  getTrending: async (limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return [...all].sort((a, b) => b.reviewCount - a.reviewCount).slice(0, limit);
  },

  getDealOfTheDay: async (): Promise<Product | undefined> => {
    const all = await getAllProducts();
    const withDiscount = all.filter((p) => p.compareAtPrice);
    return withDiscount.sort((a, b) => (b.compareAtPrice! - b.price) - (a.compareAtPrice! - a.price))[0];
  },

  getRecommended: async (limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return [...all].sort((a, b) => b.rating - a.rating).slice(0, limit);
  },

  getByMaterial: async (materialKeyword: string, limit = 8): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.specs.some((s) => s.value.toLowerCase().includes(materialKeyword.toLowerCase()))).slice(0, limit);
  },

  getRecentlyViewed: async (ids: string[]): Promise<Product[]> => {
    const all = await getAllProducts();
    const byId = new Map(all.map((p) => [p.id, p]));
    return ids.map((id) => byId.get(id)).filter((p): p is Product => Boolean(p));
  },

  getFrequentlyBoughtTogether: async (product: Product, limit = 3): Promise<Product[]> => {
    const all = await getAllProducts();
    return all.filter((p) => p.id !== product.id && p.categoryId !== product.categoryId).slice(0, limit);
  },
};
