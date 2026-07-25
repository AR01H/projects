import { useState, useCallback } from 'react';
import { wishlistApi } from '@/api/wishlist';
import type { Product } from '@/types/product';

export function useWishlist() {
  const [items, setItems] = useState<Product[]>([]);
  const [loading, setLoading] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    const data = await wishlistApi.get();
    setItems(data);
    setLoading(false);
  }, []);

  const add = useCallback(async (product: Product) => {
    const data = await wishlistApi.add(product);
    setItems(data);
  }, []);

  const remove = useCallback(async (productId: string) => {
    const data = await wishlistApi.remove(productId);
    setItems(data);
  }, []);

  const toggle = useCallback(async (product: Product) => {
    const isIn = items.some((p) => p.id === product.id);
    const data = isIn ? await wishlistApi.remove(product.id) : await wishlistApi.add(product);
    setItems(data);
  }, [items]);

  const isWishlisted = useCallback((productId: string) => {
    return items.some((p) => p.id === productId);
  }, [items]);

  return { items, loading, load, add, remove, toggle, isWishlisted, count: items.length };
}
