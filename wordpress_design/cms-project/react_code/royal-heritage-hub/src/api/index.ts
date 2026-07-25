/**
 * Unified API Service — single entry point for all data operations.
 * Every call goes through here. Handles loading, errors, and caching.
 *
 * Usage:
 *   import { api } from '@/api';
 *   const products = await api.products.getAll();
 *   const cart = await api.cart.getItems();
 */

import { productsApi, type ProductFilters } from './products';
import { categoryApi } from './category';
import { collectionsApi } from './collections';
import { tagsApi } from './tags';
import { bannersApi } from './banners';
import { blogApi } from './blog';
import { reviewsApi } from './reviews';
import { certificationsApi } from './certifications';
import { cartApi } from './cart';
import { wishlistApi } from './wishlist';
import type { Product, Category, BlogPost } from '@/types/product';

// ─── Standard response wrapper ───

interface ApiResponse<T> {
  data: T;
  error: string | null;
  loading: boolean;
}

function ok<T>(data: T): ApiResponse<T> {
  return { data, error: null, loading: false };
}

function fail<T>(message: string): ApiResponse<T> {
  return { data: null as unknown as T, error: message, loading: false };
}

// ─── Central error handler ───

async function safe<T>(fn: () => Promise<T>): Promise<ApiResponse<T>> {
  try {
    const data = await fn();
    return ok(data);
  } catch (err: any) {
    console.error(`[API Error]`, err?.message || err);
    return fail(err?.message || 'An unexpected error occurred');
  }
}

// ─── API Service ───

export const api = {
  // ── Products ──
  products: {
    getAll: () => safe(() => productsApi.getAll()),
    getBySlug: (slug: string) => safe(() => productsApi.getBySlug(slug)),
    getFiltered: (filters: ProductFilters) => safe(() => productsApi.getFiltered(filters)),
    getBestSellers: (limit?: number) => safe(() => productsApi.getBestSellers(limit)),
    getNewArrivals: (limit?: number) => safe(() => productsApi.getNewArrivals(limit)),
    getFeatured: (limit?: number) => safe(() => productsApi.getFeatured(limit)),
    getFestive: (limit?: number) => safe(() => productsApi.getFestive(limit)),
    getLimitedEdition: (limit?: number) => safe(() => productsApi.getLimitedEdition(limit)),
    getRelated: (product: Product, limit?: number) => safe(() => productsApi.getRelated(product, limit)),
    getByIds: (ids: string[]) => safe(() => productsApi.getByIds(ids)),
    getTrending: (limit?: number) => safe(() => productsApi.getTrending(limit)),
    getDealOfTheDay: () => safe(() => productsApi.getDealOfTheDay()),
    getRecommended: (limit?: number) => safe(() => productsApi.getRecommended(limit)),
    getByMaterial: (keyword: string, limit?: number) => safe(() => productsApi.getByMaterial(keyword, limit)),
    getRecentlyViewed: (ids: string[]) => safe(() => productsApi.getRecentlyViewed(ids)),
    getFrequentlyBoughtTogether: (product: Product, limit?: number) => safe(() => productsApi.getFrequentlyBoughtTogether(product, limit)),
  },

  // ── Categories ──
  categories: {
    getAll: () => safe(() => categoryApi.getAll()),
    getFeatured: (limit?: number) => safe(() => categoryApi.getFeatured(limit)),
    getBySlug: (slug: string) => safe(() => categoryApi.getBySlug(slug)),
    getTopLevel: () => safe(() => categoryApi.getTopLevel()),
    getChildren: (parentSlug: string) => safe(() => categoryApi.getChildren(parentSlug)),
    getAncestors: (category: Category) => safe(() => categoryApi.getAncestors(category)),
    getTree: () => safe(() => categoryApi.getTree()),
  },

  // ── Collections ──
  collections: {
    getAll: () => safe(() => collectionsApi.getAll()),
    getBySlug: (slug: string) => safe(() => collectionsApi.getBySlug(slug)),
  },

  // ── Tags ──
  tags: {
    getAll: () => safe(() => tagsApi.getAll()),
    getByTag: (tag: string) => safe(() => tagsApi.getByTag(tag)),
    getPopular: (limit?: number) => safe(() => tagsApi.getPopular(limit)),
    getTopLevel: () => safe(() => tagsApi.getTopLevel()),
    getChildren: (parentTag: string) => safe(() => tagsApi.getChildren(parentTag)),
    getLabel: (tag: string) => safe(() => tagsApi.getLabel(tag)),
  },

  // ── Banners ──
  banners: {
    getAll: () => safe(() => bannersApi.getAll()),
    getHero: () => safe(() => bannersApi.getHero()),
    getPromo: () => safe(() => bannersApi.getPromo()),
    getPageHero: (pageKey: string) => safe(() => bannersApi.getPageHero(pageKey)),
  },

  // ── Blog ──
  blog: {
    getAllPosts: (categorySlug?: string) => safe(() => categorySlug ? blogApi.getByCategory(categorySlug) : blogApi.getAllPosts()),
    getBySlug: (slug: string) => safe(() => blogApi.getBySlug(slug)),
    getAllCategories: () => safe(() => blogApi.getAllCategories()),
    getRecent: (limit?: number) => safe(() => blogApi.getRecent(limit)),
    getRelated: (post: BlogPost, limit?: number) => safe(() => blogApi.getRelated(post, limit)),
  },

  // ── Reviews ──
  reviews: {
    getAll: () => safe(() => reviewsApi.getAll()),
    getStats: () => safe(() => reviewsApi.getStats()),
  },

  // ── Certifications ──
  certifications: {
    getAll: () => safe(() => certificationsApi.getAll()),
  },

  // ── Cart ──
  cart: {
    getItems: () => safe(() => cartApi.get()),
    addItem: (product: Product, quantity?: number, variantId?: string) => safe(() => cartApi.addItem(product, quantity, variantId)),
    updateQuantity: (itemId: string, quantity: number) => safe(() => cartApi.updateQuantity(itemId, quantity)),
    removeItem: (itemId: string) => safe(() => cartApi.removeItem(itemId)),
    clear: () => safe(() => cartApi.clear()),
  },

  // ── Wishlist ──
  wishlist: {
    getItems: () => safe(() => wishlistApi.get()),
    add: (product: Product) => safe(() => wishlistApi.add(product)),
    remove: (productId: string) => safe(() => wishlistApi.remove(productId)),
  },
};

// ─── Re-export types ───

export type { ApiResponse };
export type { ProductFilters } from './products';
export type { TagSummary } from './tags';
export type { AggregatedReview } from './reviews';
export type { CartItem } from './cart';
