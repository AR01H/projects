import { apiClient } from './client';
import { MOCK_BLOG_POSTS, MOCK_BLOG_CATEGORIES } from '@/data/mockData';
import type { BlogCategory, BlogPost } from '@/types/product';

async function getAllPosts(): Promise<BlogPost[]> {
  if (apiClient.useMock) {
    return [...MOCK_BLOG_POSTS].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  }
  return [];
}

async function getAllCategories(): Promise<BlogCategory[]> {
  if (apiClient.useMock) return MOCK_BLOG_CATEGORIES;
  return [];
}

export const blogApi = {
  getAllPosts,
  getAllCategories,

  getBySlug: async (slug: string): Promise<BlogPost | undefined> => {
    const all = await getAllPosts();
    return all.find((p) => p.slug === slug);
  },

  getByCategory: async (categorySlug: string): Promise<BlogPost[]> => {
    const all = await getAllPosts();
    return all.filter((p) => p.categorySlug === categorySlug);
  },

  getRecent: async (limit = 5): Promise<BlogPost[]> => {
    const all = await getAllPosts();
    return all.slice(0, limit);
  },

  getRelated: async (post: BlogPost, limit = 3): Promise<BlogPost[]> => {
    const all = await getAllPosts();
    return all.filter((p) => p.id !== post.id && p.categorySlug === post.categorySlug).slice(0, limit);
  },

  getCategoryBySlug: async (slug: string): Promise<BlogCategory | undefined> => {
    const all = await getAllCategories();
    return all.find((c) => c.slug === slug);
  },
};
