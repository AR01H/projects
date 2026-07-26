/**
 * Admin Blog API — Full CRUD
 */

import { apiClient, safe } from './client';
import { MOCK_BLOG_POSTS, MOCK_BLOG_CATEGORIES } from '@/data/mockData';
import type { BlogPost, BlogCategory } from '@/types';

async function getAllPosts(): Promise<BlogPost[]> {
  if (apiClient.useMock) return MOCK_BLOG_POSTS;
  return apiClient.get<BlogPost[]>('/admin/blog/posts');
}

async function getAllCategories(): Promise<BlogCategory[]> {
  if (apiClient.useMock) return MOCK_BLOG_CATEGORIES;
  return apiClient.get<BlogCategory[]>('/admin/blog/categories');
}

export const blogApi = {
  getAllPosts: () => safe(getAllPosts),
  getAllCategories: () => safe(getAllCategories),
  createPost: (data: Partial<BlogPost>) => safe(async () => {
    if (apiClient.useMock) return { ...data, id: `post-${Date.now()}` } as BlogPost;
    return apiClient.post<BlogPost>('/admin/blog/posts', data);
  }),
  updatePost: (id: string, data: Partial<BlogPost>) => safe(async () => {
    if (apiClient.useMock) return data as BlogPost;
    return apiClient.put<BlogPost>(`/admin/blog/posts/${id}`, data);
  }),
  deletePost: (id: string) => safe(async () => {
    if (apiClient.useMock) return true;
    return apiClient.delete<boolean>(`/admin/blog/posts/${id}`);
  }),
};
