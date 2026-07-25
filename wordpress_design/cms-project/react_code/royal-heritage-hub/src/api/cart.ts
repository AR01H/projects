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

function readLocalCart(): CartItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.cart);
    return raw ? (JSON.parse(raw) as CartItem[]) : [];
  } catch {
    return [];
  }
}

function writeLocalCart(items: CartItem[]) {
  localStorage.setItem(STORAGE_KEYS.cart, JSON.stringify(items));
}

export const cartApi = {
  get: async (): Promise<CartItem[]> => {
    if (apiClient.useMock) return readLocalCart();
    return apiClient.get<CartItem[]>(ENDPOINTS.cart.get);
  },

  addItem: async (product: Product, quantity = 1, variantId?: string): Promise<CartItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalCart();
      const existing = items.find(
        (i) => i.product.id === product.id && i.variantId === variantId
      );
      if (existing) {
        existing.quantity += quantity;
      } else {
        items.push({ id: `${product.id}-${variantId ?? 'default'}`, product, quantity, variantId });
      }
      writeLocalCart(items);
      return items;
    }
    return apiClient.post<CartItem[]>(ENDPOINTS.cart.add, { productId: product.id, quantity, variantId });
  },

  updateQuantity: async (itemId: string, quantity: number): Promise<CartItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalCart()
        .map((i) => (i.id === itemId ? { ...i, quantity } : i))
        .filter((i) => i.quantity > 0);
      writeLocalCart(items);
      return items;
    }
    return apiClient.put<CartItem[]>(ENDPOINTS.cart.update(itemId), { quantity });
  },

  removeItem: async (itemId: string): Promise<CartItem[]> => {
    if (apiClient.useMock) {
      const items = readLocalCart().filter((i) => i.id !== itemId);
      writeLocalCart(items);
      return items;
    }
    return apiClient.delete<CartItem[]>(ENDPOINTS.cart.remove(itemId));
  },

  clear: async (): Promise<void> => {
    writeLocalCart([]);
  },
};
