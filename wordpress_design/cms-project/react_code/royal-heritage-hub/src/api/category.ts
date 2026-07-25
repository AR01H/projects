import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_CATEGORIES } from '@/data/mockData';
import type { Category } from '@/types/product';

async function getAllCategories(): Promise<Category[]> {
  if (apiClient.useMock) return MOCK_CATEGORIES;
  return apiClient.get<Category[]>(ENDPOINTS.categories.list);
}

export const categoryApi = {
  getAll: getAllCategories,

  getFeatured: async (limit = 6): Promise<Category[]> => {
    const all = await getAllCategories();
    return all.filter((c) => c.featured).slice(0, limit);
  },

  getBySlug: async (slug: string): Promise<Category | undefined> => {
    const all = await getAllCategories();
    return all.find((c) => c.slug === slug);
  },

  /** Top-level categories only (no parentSlug) */
  getTopLevel: async (): Promise<Category[]> => {
    const all = await getAllCategories();
    return all.filter((c) => !c.parentSlug);
  },

  /** Direct children of a given parent category slug */
  getChildren: async (parentSlug: string): Promise<Category[]> => {
    const all = await getAllCategories();
    return all.filter((c) => c.parentSlug === parentSlug);
  },

  /** The parent category of a given category, if any */
  getParent: async (category: Category): Promise<Category | undefined> => {
    if (!category.parentSlug) return undefined;
    const all = await getAllCategories();
    return all.find((c) => c.slug === category.parentSlug);
  },

  /** Full ancestor chain, root-first, for breadcrumb rendering */
  getAncestors: async (category: Category): Promise<Category[]> => {
    const all = await getAllCategories();
    const chain: Category[] = [];
    let current: Category | undefined = category;
    const bySlug = new Map(all.map((c) => [c.slug, c]));
    while (current?.parentSlug) {
      const parent = bySlug.get(current.parentSlug);
      if (!parent) break;
      chain.unshift(parent);
      current = parent;
    }
    return chain;
  },

  /** Category tree grouped as { parent, children[] } for top-level categories that have children */
  getTree: async (): Promise<{ parent: Category; children: Category[] }[]> => {
    const all = await getAllCategories();
    const topLevel = all.filter((c) => !c.parentSlug);
    return topLevel.map((parent) => ({
      parent,
      children: all.filter((c) => c.parentSlug === parent.slug),
    }));
  },
};
