import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_BLOG_POSTS, MOCK_BLOG_CATEGORIES } from '@/data/mockData';
import type { BlogCategory, BlogPost } from '@/types/product';

interface ApiResponse<T> { data: T; }

async function getAllPosts(): Promise<BlogPost[]> {
  if (apiClient.useMock) return [...MOCK_BLOG_POSTS].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  try {
    const res = await apiClient.get<ApiResponse<BlogPost[]>>(ENDPOINTS.blog.posts);
    return res.data ?? res as unknown as BlogPost[];
  } catch { return []; }
}

async function getAllCategories(): Promise<BlogCategory[]> {
  if (apiClient.useMock) return MOCK_BLOG_CATEGORIES;
  try {
    const res = await apiClient.get<ApiResponse<BlogCategory[]>>(ENDPOINTS.blog.categories);
    return res.data ?? res as unknown as BlogCategory[];
  } catch { return []; }
}

export const blogApi = {
  getAllPosts,
  getAllCategories,

  getBySlug: async (slug: string): Promise<BlogPost | undefined> => {
    if (apiClient.useMock) {
      const all = await getAllPosts();
      return all.find((p) => p.slug === slug);
    }
    try {
      const res = await apiClient.get<ApiResponse<BlogPost>>(ENDPOINTS.blog.post(slug));
      return res.data ?? res as unknown as BlogPost;
    } catch { return undefined; }
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
