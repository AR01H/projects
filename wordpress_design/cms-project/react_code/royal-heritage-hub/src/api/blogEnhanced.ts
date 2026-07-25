/**
 * Blog API Enhanced — Filtering, Pagination, Search, Tags
 */

import { apiClient } from './client';
import { MOCK_BLOG_POSTS, MOCK_BLOG_CATEGORIES } from '@/data/mockData';
import type { BlogPost, BlogCategory } from '@/types/product';

export interface BlogFilters {
  categorySlug?: string;
  tag?: string;
  search?: string;
  sortBy?: 'newest' | 'oldest' | 'popular';
}

export interface PaginatedBlog {
  posts: BlogPost[];
  total: number;
  page: number;
  pageSize: number;
  totalPages: number;
  hasMore: boolean;
}

const PAGE_SIZE = 9;

async function getAllPosts(): Promise<BlogPost[]> {
  if (apiClient.useMock) {
    return [...MOCK_BLOG_POSTS].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  }
  return apiClient.get<BlogPost[]>('/api/blog/posts');
}

async function getAllCategories(): Promise<BlogCategory[]> {
  if (apiClient.useMock) return MOCK_BLOG_CATEGORIES;
  return apiClient.get<BlogCategory[]>('/api/blog/categories');
}

function applyFilters(posts: BlogPost[], filters: BlogFilters): BlogPost[] {
  let result = [...posts];

  if (filters.categorySlug) {
    result = result.filter((p) => p.categorySlug === filters.categorySlug);
  }

  if (filters.tag) {
    result = result.filter((p) => p.tags.includes(filters.tag!));
  }

  if (filters.search) {
    const q = filters.search.toLowerCase();
    result = result.filter(
      (p) =>
        p.title.toLowerCase().includes(q) ||
        p.excerpt.toLowerCase().includes(q) ||
        p.tags.some((t) => t.toLowerCase().includes(q))
    );
  }

  switch (filters.sortBy) {
    case 'newest':
      result.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
      break;
    case 'oldest':
      result.sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime());
      break;
    case 'popular':
      result.sort((a, b) => b.readMinutes - a.readMinutes); // proxy for popularity
      break;
  }

  return result;
}

export const blogEnhancedApi = {
  // ── Get paginated posts with filters ──
  getPaginated: async (filters: BlogFilters = {}, page = 1): Promise<PaginatedBlog> => {
    const allPosts = await getAllPosts();
    const filtered = applyFilters(allPosts, filters);
    const start = (page - 1) * PAGE_SIZE;
    const posts = filtered.slice(start, start + PAGE_SIZE);

    return {
      posts,
      total: filtered.length,
      page,
      pageSize: PAGE_SIZE,
      totalPages: Math.ceil(filtered.length / PAGE_SIZE),
      hasMore: start + PAGE_SIZE < filtered.length,
    };
  },

  // ── Get all posts (no pagination) ──
  getAll: async (filters?: BlogFilters): Promise<BlogPost[]> => {
    const allPosts = await getAllPosts();
    return filters ? applyFilters(allPosts, filters) : allPosts;
  },

  // ── Get single post ──
  getBySlug: async (slug: string): Promise<BlogPost | undefined> => {
    const all = await getAllPosts();
    return all.find((p) => p.slug === slug);
  },

  // ── Get categories with post counts ──
  getCategories: async (): Promise<(BlogCategory & { count: number })[]> => {
    const [posts, categories] = await Promise.all([getAllPosts(), getAllCategories()]);
    return categories.map((cat) => ({
      ...cat,
      count: posts.filter((p) => p.categorySlug === cat.slug).length,
    }));
  },

  // ── Get all tags with counts ──
  getTags: async (): Promise<{ tag: string; count: number }[]> => {
    const posts = await getAllPosts();
    const tagCounts = new Map<string, number>();
    posts.forEach((p) => p.tags.forEach((t) => tagCounts.set(t, (tagCounts.get(t) || 0) + 1)));
    return Array.from(tagCounts.entries())
      .map(([tag, count]) => ({ tag, count }))
      .sort((a, b) => b.count - a.count);
  },

  // ── Get related posts ──
  getRelated: async (post: BlogPost, limit = 3): Promise<BlogPost[]> => {
    const all = await getAllPosts();
    return all
      .filter((p) => p.id !== post.id && (p.categorySlug === post.categorySlug || p.tags.some((t) => post.tags.includes(t))))
      .slice(0, limit);
  },

  // ── Get recent posts ──
  getRecent: async (limit = 5): Promise<BlogPost[]> => {
    const all = await getAllPosts();
    return all.slice(0, limit);
  },

  // ── Search posts ──
  search: async (query: string): Promise<BlogPost[]> => {
    const all = await getAllPosts();
    const q = query.toLowerCase();
    return all.filter(
      (p) =>
        p.title.toLowerCase().includes(q) ||
        p.excerpt.toLowerCase().includes(q) ||
        p.content.some((c) => c.toLowerCase().includes(q)) ||
        p.tags.some((t) => t.toLowerCase().includes(q))
    );
  },
};
