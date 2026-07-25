/**
 * Coupons API — Validate, Apply, Remove coupons
 * Supports: percentage, fixed, free_shipping, buy_x_get_y, flash_sale, tiered, etc.
 */

import { apiClient } from './client';
import { STORAGE_KEYS } from '@/config/storage';
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

function readLocalCoupons(): Coupon[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.coupons);
    return raw ? JSON.parse(raw) : MOCK_STOREFRONT_COUPONS;
  } catch { return MOCK_STOREFRONT_COUPONS; }
}

function writeLocalCoupons(coupons: Coupon[]) {
  localStorage.setItem(STORAGE_KEYS.coupons, JSON.stringify(coupons));
}

function isValidDateRange(coupon: Coupon): boolean {
  const now = new Date();
  if (coupon.validFrom && new Date(coupon.validFrom) > now) return false;
  if (coupon.validUntil && new Date(coupon.validUntil) < now) return false;
  return true;
}

function isValidTime(coupon: Coupon): boolean {
  if (!coupon.validTimeFrom || !coupon.validTimeTo) return true;
  const now = new Date();
  const hours = now.getHours();
  const minutes = now.getMinutes();
  const current = hours * 60 + minutes;
  const [fromH, fromM] = coupon.validTimeFrom.split(':').map(Number);
  const [toH, toM] = coupon.validTimeTo.split(':').map(Number);
  return current >= fromH * 60 + fromM && current <= toH * 60 + toM;
}

function isValidDay(coupon: Coupon): boolean {
  if (!coupon.validDays || coupon.validDays.length === 0) return true;
  const days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
  const today = days[new Date().getDay()];
  return coupon.validDays.includes(today);
}

function calculateDiscount(coupon: Coupon, subtotal: number, itemCount: number): number {
  switch (coupon.type) {
    case 'percentage':
    case 'first_order':
    case 'birthday':
    case 'flash_sale':
    case 'seasonal':
    case 'clearance':
    case 'category_percent':
      return Math.min(subtotal * coupon.discount, coupon.maxDiscount || Infinity);

    case 'fixed':
    case 'cart_threshold':
    case 'referral':
      return Math.min(coupon.discount, subtotal);

    case 'tiered':
      if (coupon.tiers && coupon.tiers.length > 0) {
        const sorted = [...coupon.tiers].sort((a, b) => b.minAmount - a.minAmount);
        for (const tier of sorted) {
          if (subtotal >= tier.minAmount) {
            return Math.min(subtotal * tier.discount, coupon.maxDiscount || Infinity);
          }
        }
      }
      return subtotal * coupon.discount;

    case 'volume':
      if (itemCount >= (coupon.volumeThreshold || 3)) {
        return Math.min(subtotal * coupon.discount, coupon.maxDiscount || Infinity);
      }
      return 0;

    default:
      return 0;
  }
}

export const couponsApi = {
  // ── Validate a coupon code ──
  validate: async (code: string, subtotal: number, itemCount = 1): Promise<CouponResult> => {
    if (apiClient.useMock) {
      const coupons = readLocalCoupons();
      const coupon = coupons.find((c) => c.code === code.toUpperCase() && c.active);

      if (!coupon) {
        return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: 'Invalid coupon code' };
      }

      if (!isValidDateRange(coupon)) {
        return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: 'This coupon has expired or is not yet active' };
      }

      if (!isValidTime(coupon)) {
        return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: 'This coupon is not valid at this time' };
      }

      if (!isValidDay(coupon)) {
        return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: 'This coupon is not valid on this day' };
      }

      if (coupon.minOrder && subtotal < coupon.minOrder) {
        return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: `Minimum order of ₹${coupon.minOrder} required` };
      }

      if (coupon.usageLimit && coupon.usedCount >= coupon.usageLimit) {
        return { valid: false, coupon: null, discountAmount: 0, freeShipping: false, error: 'Coupon usage limit reached' };
      }

      // Free shipping
      if (coupon.type === 'free_shipping') {
        return { valid: true, coupon, discountAmount: 0, freeShipping: true };
      }

      // Free gift
      if (coupon.type === 'free_gift') {
        return {
          valid: true, coupon, discountAmount: 0, freeShipping: false,
          freeGift: { productId: coupon.giftProductId, productName: coupon.giftProductName, quantity: coupon.giftQuantity || 1 },
        };
      }

      // Calculate discount
      const discountAmount = calculateDiscount(coupon, subtotal, itemCount);

      return { valid: true, coupon, discountAmount: Math.round(discountAmount), freeShipping: false };
    }

    const res = await apiClient.post<CouponResult>('/api/coupons/validate', { code, subtotal, itemCount });
    return res;
  },

  // ── Apply coupon (mark as used) ──
  apply: async (code: string): Promise<void> => {
    if (apiClient.useMock) {
      const coupons = readLocalCoupons();
      const idx = coupons.findIndex((c) => c.code === code.toUpperCase());
      if (idx !== -1) {
        coupons[idx].usedCount++;
        writeLocalCoupons(coupons);
      }
      return;
    }
    await apiClient.post('/api/coupons/apply', { code });
  },

  // ── List all active coupons ──
  getAll: async (): Promise<Coupon[]> => {
    if (apiClient.useMock) {
      return readLocalCoupons().filter((c) => c.active);
    }
    return apiClient.get<Coupon[]>('/api/coupons');
  },
};
