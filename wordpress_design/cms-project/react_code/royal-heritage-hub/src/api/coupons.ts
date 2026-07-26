import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_STOREFRONT_COUPONS } from '@/data/mockData';
import type { StorefrontCoupon as Coupon } from '@/data/mockData';

export interface CouponResult {
  valid: boolean;
  coupon: Coupon | null;
  discountAmount: number;
  freeShipping: boolean;
  freeGift?: { productId: string; productName: string; quantity: number };
  bogoItems?: { productId: string; name: string; price: number }[];
  error?: string;
  tierLabel?: string;
}

interface ApiResponse<T> { data: T; }

function calculateDiscount(coupon: Coupon, subtotal: number, itemCount: number): number {
  switch (coupon.type) {
    case 'percentage': case 'first_order': case 'birthday': case 'flash_sale':
    case 'seasonal': case 'clearance': case 'category_percent':
      return Math.min(subtotal * coupon.discount, coupon.maxDiscount || Infinity);
    case 'fixed': case 'cart_threshold': case 'referral':
      return Math.min(coupon.discount, subtotal);
    case 'tiered':
      if (coupon.tiers?.length) {
        const sorted = [...coupon.tiers].sort((a, b) => b.minAmount - a.minAmount);
        for (const tier of sorted) {
          if (subtotal >= tier.minAmount) return Math.min(subtotal * tier.discount, coupon.maxDiscount || Infinity);
        }
      }
      return subtotal * coupon.discount;
    case 'volume':
      if (itemCount >= (coupon.volumeThreshold || 3)) return Math.min(subtotal * coupon.discount, coupon.maxDiscount || Infinity);
      return 0;
    default: return 0;
  }
}

export const couponsApi = {
  validate: async (code: string, subtotal: number, itemCount = 1): Promise<CouponResult> => {
    if (apiClient.useMock) {
      const coupon = MOCK_STOREFRONT_COUPONS.find((c) => c.code === code.toUpperCase() && c.active);
      if (!coupon) return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: 'Invalid coupon code' };
      if (coupon.minOrder && subtotal < coupon.minOrder) return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: `Minimum order of ₹${coupon.minOrder} required` };
      if (coupon.usageLimit && coupon.usedCount >= coupon.usageLimit) return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: 'Coupon usage limit reached' };
      if (coupon.type === 'free_shipping') return { valid: true, coupon, discountAmount: 0, freeShipping: true };
      return { valid: true, coupon, discountAmount: Math.round(calculateDiscount(coupon, subtotal, itemCount)), freeShipping: false };
    }
    try {
      const res = await apiClient.post<ApiResponse<CouponResult>>(ENDPOINTS.coupons.validate, { code, subtotal, itemCount });
      return res.data ?? res as unknown as CouponResult;
    } catch (e: any) {
      return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: e.message || 'Validation failed' };
    }
  },

  apply: async (code: string): Promise<void> => {
    if (apiClient.useMock) return;
    await apiClient.post(ENDPOINTS.coupons.apply, { code });
  },

  getAll: async (): Promise<Coupon[]> => {
    if (apiClient.useMock) return MOCK_STOREFRONT_COUPONS.filter((c) => c.active);
    try {
      const res = await apiClient.get<ApiResponse<Coupon[]>>(ENDPOINTS.coupons.list);
      return res.data ?? res as unknown as Coupon[];
    } catch { return []; }
  },
};
