/**
 * Order Store — Create orders, view history, track orders
 */

import { create } from 'zustand';
import { ordersApi, type Order, type CreateOrderInput } from '@/api/orders';

interface OrderState {
  orders: Order[];
  currentOrder: Order | null;
  isLoading: boolean;
  error: string | null;

  loadOrders: () => Promise<void>;
  createOrder: (input: CreateOrderInput) => Promise<Order | null>;
  getOrder: (orderId: string) => Promise<void>;
  cancelOrder: (orderId: string) => Promise<void>;
}

export const useOrderStore = create<OrderState>((set) => ({
  orders: [],
  currentOrder: null,
  isLoading: false,
  error: null,

  loadOrders: async () => {
    set({ isLoading: true, error: null });
    try {
      const orders = await ordersApi.getAll();
      set({ orders, isLoading: false });
    } catch (err: any) {
      set({ error: err.message, isLoading: false });
    }
  },

  createOrder: async (input) => {
    set({ isLoading: true, error: null });
    try {
      const order = await ordersApi.create(input);
      set((s) => ({ orders: [order, ...s.orders], isLoading: false }));
      return order;
    } catch (err: any) {
      set({ error: err.message, isLoading: false });
      return null;
    }
  },

  getOrder: async (orderId) => {
    set({ isLoading: true, error: null });
    try {
      const order = await ordersApi.getById(orderId);
      set({ currentOrder: order ?? null, isLoading: false });
    } catch (err: any) {
      set({ error: err.message, isLoading: false });
    }
  },

  cancelOrder: async (orderId) => {
    set({ isLoading: true, error: null });
    try {
      const order = await ordersApi.cancel(orderId);
      set((s) => ({
        orders: s.orders.map((o) => (o.id === orderId ? order : o)),
        currentOrder: s.currentOrder?.id === orderId ? order : s.currentOrder,
        isLoading: false,
      }));
    } catch (err: any) {
      set({ error: err.message, isLoading: false });
    }
  },
}));
