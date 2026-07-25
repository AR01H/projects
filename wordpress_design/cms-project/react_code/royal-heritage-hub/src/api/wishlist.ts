import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { STORAGE_KEYS } from '@/config/storage';
import type { Product } from '@/types/product';

function readLocalWishlist(): Product[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.wishlist);
    return raw ? (JSON.parse(raw) as Product[]) : [];
  } catch {
    return [];
  }
}

function writeLocalWishlist(items: Product[]) {
  localStorage.setItem(STORAGE_KEYS.wishlist, JSON.stringify(items));
}

export const wishlistApi = {
  get: async (): Promise<Product[]> => {
    if (apiClient.useMock) return readLocalWishlist();
    return apiClient.get<Product[]>(ENDPOINTS.wishlist.get);
  },

  add: async (product: Product): Promise<Product[]> => {
    if (apiClient.useMock) {
      const items = readLocalWishlist();
      if (!items.some((p) => p.id === product.id)) items.push(product);
      writeLocalWishlist(items);
      return items;
    }
    return apiClient.post<Product[]>(ENDPOINTS.wishlist.add, { productId: product.id });
  },

  remove: async (productId: string): Promise<Product[]> => {
    if (apiClient.useMock) {
      const items = readLocalWishlist().filter((p) => p.id !== productId);
      writeLocalWishlist(items);
      return items;
    }
    return apiClient.delete<Product[]>(ENDPOINTS.wishlist.remove(productId));
  },
};
