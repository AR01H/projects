import { create } from 'zustand';
import { cartApi, type CartItem } from '@/api/cart';
import type { Product } from '@/types/product';

interface CartState {
  items: CartItem[];
  isLoading: boolean;
  isOpen: boolean;
  init: () => Promise<void>;
  addItem: (product: Product, quantity?: number, variantId?: string) => Promise<void>;
  updateQuantity: (itemId: string, quantity: number) => Promise<void>;
  removeItem: (itemId: string) => Promise<void>;
  toggleCart: (open?: boolean) => void;
  subtotal: () => number;
  itemCount: () => number;
}

export const useCartStore = create<CartState>((set, get) => ({
  items: [],
  isLoading: false,
  isOpen: false,

  init: async () => {
    set({ isLoading: true });
    const items = await cartApi.get();
    set({ items, isLoading: false });
  },

  addItem: async (product, quantity = 1, variantId) => {
    const items = await cartApi.addItem(product, quantity, variantId);
    set({ items, isOpen: true });
  },

  updateQuantity: async (itemId, quantity) => {
    const items = await cartApi.updateQuantity(itemId, quantity);
    set({ items });
  },

  removeItem: async (itemId) => {
    const items = await cartApi.removeItem(itemId);
    set({ items });
  },

  toggleCart: (open) => set((state) => ({ isOpen: open ?? !state.isOpen })),

  subtotal: () =>
    get().items.reduce((sum, item) => sum + item.product.price * item.quantity, 0),

  itemCount: () => get().items.reduce((sum, item) => sum + item.quantity, 0),
}));
