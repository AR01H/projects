import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { STORAGE_KEYS } from '@/config/storage';
import type { Product } from '@/types/product';

interface ApiResponse<T> { data: T; }

interface WishlistItem {
  id: string;
  productId: string;
  notes?: string;
  product: Product | null;
}

function readLocalWishlist(): Product[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.wishlist);
    return raw ? (JSON.parse(raw) as Product[]) : [];
  } catch { return []; }
}

function writeLocalWishlist(items: Product[]) {
  localStorage.setItem(STORAGE_KEYS.wishlist, JSON.stringify(items));
}

export const wishlistApi = {
  get: async (): Promise<Product[]> => {
    if (apiClient.useMock) return readLocalWishlist();
    try {
      const res = await apiClient.get<ApiResponse<WishlistItem[]>>(ENDPOINTS.wishlist.get);
      const items = res.data ?? [];
      return items.filter((i) => i.product).map((i) => i.product!);
    } catch { return []; }
  },

  add: async (product: Product): Promise<Product[]> => {
    if (apiClient.useMock) {
      const items = readLocalWishlist();
      if (!items.some((p) => p.id === product.id)) items.push(product);
      writeLocalWishlist(items);
      return items;
    }
    await apiClient.post(ENDPOINTS.wishlist.add, { productId: product.id });
    return wishlistApi.get();
  },

  remove: async (productId: string): Promise<Product[]> => {
    if (apiClient.useMock) {
      const items = readLocalWishlist().filter((p) => p.id !== productId);
      writeLocalWishlist(items);
      return items;
    }
    await apiClient.delete(ENDPOINTS.wishlist.remove(productId));
    return wishlistApi.get();
  },

  updateNotes: async (productId: string, notes: string): Promise<Product[]> => {
    if (apiClient.useMock) return readLocalWishlist();
    await apiClient.put(ENDPOINTS.wishlist.update(productId), { notes });
    return wishlistApi.get();
  },

  clear: async (): Promise<Product[]> => {
    if (apiClient.useMock) { writeLocalWishlist([]); return []; }
    await apiClient.delete(ENDPOINTS.wishlist.clear);
    return [];
  },

  getShared: async (ids: string[]): Promise<Product[]> => {
    if (apiClient.useMock) {
      const all = readLocalWishlist();
      return all.filter((p) => ids.includes(p.id));
    }
    try {
      const res = await apiClient.post<ApiResponse<WishlistItem[]>>(ENDPOINTS.wishlist.shared, { ids });
      const items = res.data ?? [];
      return items.filter((i) => i.product).map((i) => i.product!);
    } catch { return []; }
  },
};
