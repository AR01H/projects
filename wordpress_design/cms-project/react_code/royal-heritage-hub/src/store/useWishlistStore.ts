import { create } from 'zustand';
import { wishlistApi } from '@/api/wishlist';
import type { Product } from '@/types/product';

interface WishlistState {
  items: Product[];
  isLoading: boolean;
  init: () => Promise<void>;
  toggle: (product: Product) => Promise<void>;
  isWishlisted: (productId: string) => boolean;
}

export const useWishlistStore = create<WishlistState>((set, get) => ({
  items: [],
  isLoading: false,

  init: async () => {
    set({ isLoading: true });
    const items = await wishlistApi.get();
    set({ items, isLoading: false });
  },

  toggle: async (product) => {
    const isIn = get().items.some((p) => p.id === product.id);
    const items = isIn ? await wishlistApi.remove(product.id) : await wishlistApi.add(product);
    set({ items });
  },

  isWishlisted: (productId) => get().items.some((p) => p.id === productId),
}));
