import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { STORAGE_KEYS } from '@/config/storage';
import type { Product } from '@/types/product';

export interface CartItem {
  id: string;
  product: Product;
  quantity: number;
  variantId?: string;
}

interface ApiResponse<T> { data: T; }

function readLocalCart(): CartItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.cart);
    return raw ? (JSON.parse(raw) as CartItem[]) : [];
  } catch { return []; }
}

function writeLocalCart(items: CartItem[]) {
  localStorage.setItem(STORAGE_KEYS.cart, JSON.stringify(items));
}

export const cartApi = {
  get: async (): Promise<CartItem[]> => {
    if (apiClient.useMock) return readLocalCart();
    try {
      const res = await apiClient.get<ApiResponse<CartItem[]>>(ENDPOINTS.cart.get);
      return res.data ?? res as unknown as CartItem[];
    } catch { return []; }
  },

  addItem: async (product: Product, quantity = 1, variantId?: string): Promise<CartItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalCart();
      const existing = items.find((i) => i.product.id === product.id && i.variantId === variantId);
      if (existing) existing.quantity += quantity;
      else items.push({ id: `${product.id}-${variantId ?? 'default'}`, product, quantity, variantId });
      writeLocalCart(items);
      return items;
    }
    const res = await apiClient.post<ApiResponse<CartItem[]>>(ENDPOINTS.cart.add, { productId: product.id, quantity, variantId });
    return res.data ?? res as unknown as CartItem[];
  },

  updateQuantity: async (itemId: string, quantity: number): Promise<CartItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalCart().map((i) => (i.id === itemId ? { ...i, quantity } : i)).filter((i) => i.quantity > 0);
      writeLocalCart(items);
      return items;
    }
    const res = await apiClient.put<ApiResponse<CartItem[]>>(ENDPOINTS.cart.update(itemId), { quantity });
    return res.data ?? res as unknown as CartItem[];
  },

  removeItem: async (itemId: string): Promise<CartItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalCart().filter((i) => i.id !== itemId);
      writeLocalCart(items);
      return items;
    }
    const res = await apiClient.delete<ApiResponse<CartItem[]>>(ENDPOINTS.cart.remove(itemId));
    return res.data ?? res as unknown as CartItem[];
  },

  clear: async (): Promise<void> => {
    writeLocalCart([]);
  },
};
