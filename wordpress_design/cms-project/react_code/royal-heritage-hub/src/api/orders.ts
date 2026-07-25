/**
 * Orders API — Create, List, Detail, Tracking
 */

import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { STORAGE_KEYS } from '@/config/storage';
import type { Product } from '@/types/product';

export interface OrderAddress {
  name: string;
  phone: string;
  email: string;
  line1: string;
  line2?: string;
  city: string;
  state: string;
  pincode: string;
}

export interface OrderItem {
  product: Product;
  quantity: number;
  variantId?: string;
}

export type OrderStatus = 'placed' | 'confirmed' | 'processing' | 'shipped' | 'out_for_delivery' | 'delivered' | 'cancelled';

export interface OrderTrackingStep {
  status: OrderStatus;
  label: string;
  date: string | null;
  completed: boolean;
}

export interface Order {
  id: string;
  userId: string;
  items: OrderItem[];
  subtotal: number;
  shipping: number;
  discount: number;
  codCharge: number;
  total: number;
  address: OrderAddress;
  paymentMethod: 'upi' | 'card' | 'netbanking' | 'cod';
  paymentId?: string;
  couponCode?: string;
  status: OrderStatus;
  tracking: OrderTrackingStep[];
  notes?: string;
  createdAt: string;
  updatedAt: string;
}

export interface CreateOrderInput {
  address: OrderAddress;
  paymentMethod: 'upi' | 'card' | 'netbanking' | 'cod';
  couponCode?: string;
  notes?: string;
  items: OrderItem[];
  subtotal: number;
  shipping: number;
  discount: number;
  codCharge: number;
  total: number;
}

function readLocalOrders(): Order[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.orders);
    return raw ? JSON.parse(raw) : [];
  } catch { return []; }
}

function writeLocalOrders(orders: Order[]) {
  localStorage.setItem(STORAGE_KEYS.orders, JSON.stringify(orders));
}

function buildTracking(status: OrderStatus, createdAt: string): OrderTrackingStep[] {
  const steps: { status: OrderStatus; label: string }[] = [
    { status: 'placed', label: 'Order Placed' },
    { status: 'confirmed', label: 'Order Confirmed' },
    { status: 'processing', label: 'Processing' },
    { status: 'shipped', label: 'Shipped' },
    { status: 'out_for_delivery', label: 'Out for Delivery' },
    { status: 'delivered', label: 'Delivered' },
  ];
  const statusOrder = steps.map((s) => s.status);
  const currentIdx = statusOrder.indexOf(status);
  return steps.map((step, i) => ({
    ...step,
    date: i <= currentIdx ? (i === 0 ? createdAt : new Date(Date.now() - (currentIdx - i) * 86400000).toISOString()) : null,
    completed: i <= currentIdx,
  }));
}

export const ordersApi = {
  // ── Create order ──
  create: async (input: CreateOrderInput): Promise<Order> => {
    if (apiClient.useMock) {
      const order: Order = {
        id: `ORD-${Date.now()}`,
        userId: 'current-user',
        items: input.items,
        subtotal: input.subtotal,
        shipping: input.shipping,
        discount: input.discount,
        codCharge: input.codCharge,
        total: input.total,
        address: input.address,
        paymentMethod: input.paymentMethod,
        couponCode: input.couponCode,
        status: 'placed',
        tracking: buildTracking('placed', new Date().toISOString()),
        notes: input.notes,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString(),
      };
      const orders = readLocalOrders();
      orders.unshift(order);
      writeLocalOrders(orders);
      return order;
    }
    return apiClient.post<Order>(ENDPOINTS.orders.create, input);
  },

  // ── List orders ──
  getAll: async (): Promise<Order[]> => {
    if (apiClient.useMock) return readLocalOrders();
    return apiClient.get<Order[]>(ENDPOINTS.orders.list);
  },

  // ── Get single order ──
  getById: async (orderId: string): Promise<Order | undefined> => {
    if (apiClient.useMock) {
      return readLocalOrders().find((o) => o.id === orderId);
    }
    return apiClient.get<Order>(ENDPOINTS.orders.detail(orderId));
  },

  // ── Get tracking ──
  getTracking: async (orderId: string): Promise<OrderTrackingStep[]> => {
    const order = await ordersApi.getById(orderId);
    return order?.tracking || [];
  },

  // ── Cancel order ──
  cancel: async (orderId: string): Promise<Order> => {
    if (apiClient.useMock) {
      const orders = readLocalOrders();
      const idx = orders.findIndex((o) => o.id === orderId);
      if (idx === -1) throw new Error('Order not found');
      orders[idx].status = 'cancelled';
      orders[idx].updatedAt = new Date().toISOString();
      writeLocalOrders(orders);
      return orders[idx];
    }
    return apiClient.put<Order>(`${ENDPOINTS.orders.detail(orderId)}/cancel`, {});
  },
};
