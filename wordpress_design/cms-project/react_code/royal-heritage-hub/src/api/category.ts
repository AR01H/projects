import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_CATEGORIES } from '@/data/mockData';
import type { Category } from '@/types/product';

interface CategoriesResponse { data: Category[]; }

async function getAllCategories(): Promise<Category[]> {
  if (apiClient.useMock) return MOCK_CATEGORIES;
  const res = await apiClient.get<CategoriesResponse>(ENDPOINTS.categories.list);
  return res.data ?? res as unknown as Category[];
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

  getTopLevel: async (): Promise<Category[]> => {
    const all = await getAllCategories();
    return all.filter((c) => !c.parentSlug);
  },

  getChildren: async (parentSlug: string): Promise<Category[]> => {
    const all = await getAllCategories();
    return all.filter((c) => c.parentSlug === parentSlug);
  },

  getParent: async (category: Category): Promise<Category | undefined> => {
    if (!category.parentSlug) return undefined;
    const all = await getAllCategories();
    return all.find((c) => c.slug === category.parentSlug);
  },

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

  getTree: async (): Promise<{ parent: Category; children: Category[] }[]> => {
    const all = await getAllCategories();
    const topLevel = all.filter((c) => !c.parentSlug);
    return topLevel.map((parent) => ({
      parent,
      children: all.filter((c) => c.parentSlug === parent.slug),
    }));
  },
};
