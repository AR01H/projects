import { create } from 'zustand';
import { couponsApi, type CouponResult } from '@/api/coupons';
import type { StorefrontCoupon } from '@/data/mockData';

interface CouponState {
  appliedCoupon: StorefrontCoupon | null;
  discountAmount: number;
  freeShipping: boolean;
  freeGift: { productId: string; productName: string; quantity: number } | null;
  tierLabel: string;
  error: string;
  loading: boolean;

  validate: (code: string, subtotal: number, itemCount: number) => Promise<void>;
  remove: () => void;
}

export const useCouponStore = create<CouponState>((set) => ({
  appliedCoupon: null,
  discountAmount: 0,
  freeShipping: false,
  freeGift: null,
  tierLabel: '',
  error: '',
  loading: false,

  validate: async (code: string, subtotal: number, itemCount: number) => {
    if (!code.trim()) {
      set({ error: 'Please enter a coupon code' });
      return;
    }

    set({ loading: true, error: '' });

    try {
      const result: CouponResult = await couponsApi.validate(code.trim(), subtotal, itemCount);

      if (result.valid && result.coupon) {
        set({
          appliedCoupon: result.coupon as unknown as StorefrontCoupon,
          discountAmount: result.discountAmount,
          freeShipping: result.freeShipping,
          freeGift: result.freeGift || null,
          tierLabel: result.tierLabel || '',
          error: '',
          loading: false,
        });
      } else {
        set({
          appliedCoupon: null,
          discountAmount: 0,
          freeShipping: false,
          freeGift: null,
          tierLabel: '',
          error: result.error || 'Invalid coupon code',
          loading: false,
        });
      }
    } catch {
      set({
        appliedCoupon: null,
        discountAmount: 0,
        freeShipping: false,
        freeGift: null,
        tierLabel: '',
        error: 'Failed to validate coupon. Please try again.',
        loading: false,
      });
    }
  },

  remove: () => set({
    appliedCoupon: null,
    discountAmount: 0,
    freeShipping: false,
    freeGift: null,
    tierLabel: '',
    error: '',
  }),
}));
