/**
 * Wishlist API Enhanced — Add, Remove, Toggle, Move to Cart, Share, Bulk operations
 */

import { apiClient } from './client';
import { STORAGE_KEYS } from '@/config/storage';
import { MOCK_PRODUCTS } from '@/data/mockData';
import type { Product } from '@/types/product';

export interface WishlistItem {
  product: Product;
  addedAt: string;
  notes?: string;
}

export interface ShareableWishlist {
  items: WishlistItem[];
  sharedBy: string;
  sharedAt: string;
  url: string;
}

function readLocalWishlist(): WishlistItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.wishlist);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    // Support both old format (Product[]) and new format (WishlistItem[])
    if (parsed.length > 0 && parsed[0].product) return parsed;
    return parsed.map((p: Product) => ({ product: p, addedAt: new Date().toISOString() }));
  } catch { return []; }
}

function writeLocalWishlist(items: WishlistItem[]) {
  localStorage.setItem(STORAGE_KEYS.wishlist, JSON.stringify(items));
}

export const wishlistEnhancedApi = {
  // ── Get all wishlist items ──
  get: async (): Promise<WishlistItem[]> => {
    if (apiClient.useMock) return readLocalWishlist();
    return apiClient.get<WishlistItem[]>('/api/wishlist');
  },

  // ── Get just products (backwards compatible) ──
  getProducts: async (): Promise<Product[]> => {
    const items = await wishlistEnhancedApi.get();
    return items.map((i) => i.product);
  },

  // ── Add to wishlist ──
  add: async (product: Product, notes?: string): Promise<WishlistItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalWishlist();
      if (!items.some((i) => i.product.id === product.id)) {
        items.push({ product, addedAt: new Date().toISOString(), notes });
        writeLocalWishlist(items);
      }
      return items;
    }
    return apiClient.post<WishlistItem[]>('/api/wishlist/items', { productId: product.id, notes });
  },

  // ── Remove from wishlist ──
  remove: async (productId: string): Promise<WishlistItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalWishlist().filter((i) => i.product.id !== productId);
      writeLocalWishlist(items);
      return items;
    }
    return apiClient.delete<WishlistItem[]>(`/api/wishlist/items/${productId}`);
  },

  // ── Toggle wishlist ──
  toggle: async (product: Product): Promise<{ added: boolean; items: WishlistItem[] }> => {
    const items = await wishlistEnhancedApi.get();
    const exists = items.some((i) => i.product.id === product.id);
    const updated = exists
      ? await wishlistEnhancedApi.remove(product.id)
      : await wishlistEnhancedApi.add(product);
    return { added: !exists, items: updated };
  },

  // ── Check if product is wishlisted ──
  isWishlisted: async (productId: string): Promise<boolean> => {
    const items = await wishlistEnhancedApi.get();
    return items.some((i) => i.product.id === productId);
  },

  // ── Get wishlist count ──
  getCount: async (): Promise<number> => {
    const items = await wishlistEnhancedApi.get();
    return items.length;
  },

  // ── Add note to wishlist item ──
  addNote: async (productId: string, notes: string): Promise<WishlistItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalWishlist();
      const idx = items.findIndex((i) => i.product.id === productId);
      if (idx !== -1) {
        items[idx].notes = notes;
        writeLocalWishlist(items);
      }
      return items;
    }
    return apiClient.put<WishlistItem[]>(`/api/wishlist/items/${productId}`, { notes });
  },

  // ── Move item to cart ──
  moveToCart: async (productId: string, cartApi: any): Promise<{ wishlist: WishlistItem[]; cart: any[] }> => {
    const items = await wishlistEnhancedApi.get();
    const item = items.find((i) => i.product.id === productId);
    if (!item) throw new Error('Item not found in wishlist');

    await cartApi.addItem(item.product, 1);
    const updatedWishlist = await wishlistEnhancedApi.remove(productId);
    const updatedCart = await cartApi.get();

    return { wishlist: updatedWishlist, cart: updatedCart };
  },

  // ── Clear entire wishlist ──
  clear: async (): Promise<void> => {
    if (apiClient.useMock) {
      writeLocalWishlist([]);
      return;
    }
    await apiClient.delete('/api/wishlist');
  },

  // ── Generate shareable link ──
  generateShareLink: async (): Promise<string> => {
    const items = await wishlistEnhancedApi.get();
    const ids = items.map((i) => i.product.id).join(',');
    return `${window.location.origin}/wishlist?ids=${encodeURIComponent(ids)}`;
  },

  // ── Load shared wishlist ──
  loadShared: async (idsParam: string): Promise<WishlistItem[]> => {
    const ids = idsParam.split(',').filter(Boolean);
    if (ids.length === 0) return [];

    if (apiClient.useMock) {
      return ids
        .map((id) => MOCK_PRODUCTS.find((p) => p.id === id))
        .filter((p): p is Product => Boolean(p))
        .map((p) => ({ product: p, addedAt: new Date().toISOString() }));
    }
    return apiClient.post<WishlistItem[]>('/api/wishlist/shared', { ids });
  },
};
