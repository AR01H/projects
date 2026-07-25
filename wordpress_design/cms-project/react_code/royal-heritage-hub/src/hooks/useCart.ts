import { useState, useCallback } from 'react';
import { cartApi, type CartItem } from '@/api/cart';
import type { Product } from '@/types/product';

export function useCart() {
  const [items, setItems] = useState<CartItem[]>([]);
  const [loading, setLoading] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    const data = await cartApi.get();
    setItems(data);
    setLoading(false);
  }, []);

  const addItem = useCallback(async (product: Product, quantity = 1, variantId?: string) => {
    const data = await cartApi.addItem(product, quantity, variantId);
    setItems(data);
  }, []);

  const updateQuantity = useCallback(async (itemId: string, quantity: number) => {
    const data = await cartApi.updateQuantity(itemId, quantity);
    setItems(data);
  }, []);

  const removeItem = useCallback(async (itemId: string) => {
    const data = await cartApi.removeItem(itemId);
    setItems(data);
  }, []);

  const clear = useCallback(async () => {
    await cartApi.clear();
    setItems([]);
  }, []);

  const subtotal = items.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
  const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);

  return { items, loading, load, addItem, updateQuantity, removeItem, clear, subtotal, itemCount };
}
