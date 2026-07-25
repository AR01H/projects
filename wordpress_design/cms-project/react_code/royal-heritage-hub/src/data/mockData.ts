/**
 * CENTRALIZED MOCK DATA — All mock data in one file for the storefront.
 * Import from here: import { MOCK_PRODUCTS, MOCK_CATEGORIES, etc } from '@/data/mockData';
 *
 * When creating a new company, replace this file with your company's data.
 * Everything else stays the same — the storefront reads from this file in mock mode.
 */

import type { Product, Category, Collection, Banner, BlogPost, BlogCategory, CertificationEntry } from '@/types/product';
import type { TagMeta } from '@/api/tags';

// ── Products ──
import productsJson from './products.json';
export const MOCK_PRODUCTS: Product[] = productsJson as unknown as Product[];

// ── Categories ──
import categoriesJson from './categories.json';
export const MOCK_CATEGORIES: Category[] = categoriesJson as unknown as Category[];

// ── Banners ──
import bannersJson from './banners.json';
export const MOCK_BANNERS: { hero: Banner[]; promo: Banner[]; pageHeroes: Record<string, Banner> } = bannersJson as unknown as { hero: Banner[]; promo: Banner[]; pageHeroes: Record<string, Banner> };

// ── Collections ──
import collectionsJson from './collections.json';
export const MOCK_COLLECTIONS: Collection[] = collectionsJson as unknown as Collection[];

// ── Tags ──
import tagsJson from './tags.json';
export const MOCK_TAGS: TagMeta[] = tagsJson as unknown as TagMeta[];

// ── Certifications ──
import certificationsJson from './certifications.json';
export const MOCK_CERTIFICATIONS: CertificationEntry[] = certificationsJson as unknown as CertificationEntry[];

// ── Blog Posts ──
import blogPostsJson from './blogPosts.json';
export const MOCK_BLOG_POSTS: BlogPost[] = blogPostsJson as unknown as BlogPost[];

// ── Blog Categories ──
import blogCategoriesJson from './blogCategories.json';
export const MOCK_BLOG_CATEGORIES: BlogCategory[] = blogCategoriesJson as unknown as BlogCategory[];

// ── Footer ──
import footerJson from './footer.json';
export const MOCK_FOOTER: typeof footerJson = footerJson as typeof footerJson;

// ── Storefront Coupons ──
export type StorefrontCouponType =
  | 'percentage' | 'fixed' | 'free_shipping' | 'buy_x_get_y'
  | 'buy_x_get_percent' | 'first_order' | 'flash_sale' | 'tiered'
  | 'cart_threshold' | 'free_gift' | 'volume' | 'birthday'
  | 'seasonal' | 'clearance' | 'referral' | 'loyalty';

export interface StorefrontCoupon {
  code: string;
  description: string;
  type: StorefrontCouponType;
  discount: number;
  discountType: 'percentage' | 'fixed';
  minOrder: number;
  maxDiscount: number;
  buyQuantity: number;
  getQuantity: number;
  getDiscount: number;
  usageLimit: number;
  usedCount: number;
  perUserLimit: number;
  validFrom: string;
  validUntil: string;
  validDays: string[];
  validTimeFrom: string;
  validTimeTo: string;
  volumeThreshold: number;
  giftProductId: string;
  giftProductName: string;
  giftQuantity: number;
  referralReward: number;
  refereeDiscount: number;
  loyaltyPointsMultiplier: number;
  badge: string;
  bgColor: string;
  textColor: string;
  active: boolean;
  stackable: boolean;
  tiers: { minAmount: number; discount: number; label: string }[];
}

export const MOCK_STOREFRONT_COUPONS: StorefrontCoupon[] = [
  {
    code: 'WELCOME10', description: '10% off on your first order', type: 'first_order',
    discount: 0.1, discountType: 'percentage', minOrder: 500, maxDiscount: 200,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 500, usedCount: 0, perUserLimit: 1,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'NEW USER', bgColor: '#22c55e', textColor: '#ffffff', active: true, stackable: false, tiers: [],
  },
  {
    code: 'BOGO1FREE', description: 'Buy 1 Wooden Toy, Get 1 Free', type: 'buy_x_get_y',
    discount: 0, discountType: 'fixed', minOrder: 0, maxDiscount: 1500,
    buyQuantity: 1, getQuantity: 1, getDiscount: 0, usageLimit: 200, usedCount: 0, perUserLimit: 2,
    validFrom: '2026-07-01', validUntil: '2026-08-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'BOGO', bgColor: '#f59e0b', textColor: '#000000', active: true, stackable: false, tiers: [],
  },
  {
    code: 'FREESHIP', description: 'Free shipping on any order', type: 'free_shipping',
    discount: 0, discountType: 'fixed', minOrder: 0, maxDiscount: 0,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 0, usedCount: 0, perUserLimit: 0,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FREE SHIP', bgColor: '#22c55e', textColor: '#ffffff', active: true, stackable: true, tiers: [],
  },
  {
    code: 'FLAT500', description: 'Flat ₹500 off on orders above ₹2999', type: 'cart_threshold',
    discount: 500, discountType: 'fixed', minOrder: 2999, maxDiscount: 500,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 300, usedCount: 0, perUserLimit: 3,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FLAT ₹500', bgColor: '#ef4444', textColor: '#ffffff', active: true, stackable: true, tiers: [],
  },
  {
    code: 'FLASH30', description: '24-hour flash sale — 30% off everything', type: 'flash_sale',
    discount: 0.3, discountType: 'percentage', minOrder: 999, maxDiscount: 1500,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 100, usedCount: 0, perUserLimit: 1,
    validFrom: '2026-07-25', validUntil: '2026-07-26', validDays: [], validTimeFrom: '00:00', validTimeTo: '23:59',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FLASH SALE', bgColor: '#dc2626', textColor: '#ffffff', active: true, stackable: false, tiers: [],
  },
  {
    code: 'TIERED20', description: 'Spend more, save more — tiered discounts', type: 'tiered',
    discount: 0.1, discountType: 'percentage', minOrder: 1000, maxDiscount: 2000,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 0, usedCount: 0, perUserLimit: 0,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'SAVE MORE', bgColor: '#06b6d4', textColor: '#ffffff', active: true, stackable: false,
    tiers: [
      { minAmount: 1000, discount: 0.1, label: 'Spend ₹1000+ Get 10% off' },
      { minAmount: 3000, discount: 0.15, label: 'Spend ₹3000+ Get 15% off' },
      { minAmount: 5000, discount: 0.2, label: 'Spend ₹5000+ Get 20% off' },
    ],
  },
  {
    code: 'BULK15', description: 'Buy 3+ items, get 15% off', type: 'volume',
    discount: 0.15, discountType: 'percentage', minOrder: 0, maxDiscount: 1000,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 0, usedCount: 0, perUserLimit: 0,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 3, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'BULK SAVE', bgColor: '#7c3aed', textColor: '#ffffff', active: true, stackable: true, tiers: [],
  },
  {
    code: 'FREEGIFT', description: 'Buy any ₹2000+ order, get a free Brass Diya', type: 'free_gift',
    discount: 0, discountType: 'fixed', minOrder: 2000, maxDiscount: 0,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 100, usedCount: 0, perUserLimit: 1,
    validFrom: '2026-07-01', validUntil: '2026-08-15', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: 'p4', giftProductName: 'Brass Diya Set', giftQuantity: 1,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FREE GIFT', bgColor: '#ec4899', textColor: '#ffffff', active: true, stackable: false, tiers: [],
  },
  {
    code: 'REFER500', description: 'Refer a friend — both get ₹500 off', type: 'referral',
    discount: 500, discountType: 'fixed', minOrder: 1999, maxDiscount: 500,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 0, usedCount: 0, perUserLimit: 10,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 500, refereeDiscount: 500, loyaltyPointsMultiplier: 1,
    badge: 'REFER & EARN', bgColor: '#10b981', textColor: '#ffffff', active: true, stackable: false, tiers: [],
  },
  {
    code: 'BDAY20', description: '20% off on your birthday month', type: 'birthday',
    discount: 0.2, discountType: 'percentage', minOrder: 0, maxDiscount: 800,
    buyQuantity: 0, getQuantity: 0, getDiscount: 0, usageLimit: 0, usedCount: 0, perUserLimit: 1,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    volumeThreshold: 0, giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 3,
    badge: 'HBD', bgColor: '#f472b6', textColor: '#ffffff', active: true, stackable: false, tiers: [],
  },
];
