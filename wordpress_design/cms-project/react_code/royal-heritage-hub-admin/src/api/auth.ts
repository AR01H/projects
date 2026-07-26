/**
 * Admin Auth API
 */

import { apiClient } from './client';
import { MOCK_ADMIN_USERS } from '@/data/mockData';
import { STORAGE_KEYS } from '@/config/storage';
import type { AdminUser } from '@/types';

function readLocal(): AdminUser | null {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEYS.adminUser) || 'null'); } catch { return null; }
}
function writeLocal(user: AdminUser | null) {
  if (user) localStorage.setItem(STORAGE_KEYS.adminUser, JSON.stringify(user));
  else localStorage.removeItem(STORAGE_KEYS.adminUser);
}
function readToken(): string | null { return localStorage.getItem(STORAGE_KEYS.adminToken); }
function writeToken(t: string | null) { if (t) localStorage.setItem(STORAGE_KEYS.adminToken, t); else localStorage.removeItem(STORAGE_KEYS.adminToken); }

export const authApi = {
  getCurrentUser: (): AdminUser | null => readLocal(),
  getToken: (): string | null => readToken(),
  isAuthenticated: (): boolean => !!readToken(),

  login: async (email: string, password: string): Promise<{ user: AdminUser; token: string }> => {
    if (apiClient.useMock) {
      const mockUser = MOCK_ADMIN_USERS[0];
      const user = { ...mockUser, email, name: email.split('@')[0] };
      const token = `admin-token-${Date.now()}`;
      writeLocal(user); writeToken(token);
      return { user, token };
    }
    const res = await apiClient.post<{ user: AdminUser; token: string }>('/admin/auth/login', { email, password });
    writeLocal(res.user); writeToken(res.token);
    return res;
  },

  logout: async () => { writeLocal(null); writeToken(null); },

  getProfile: async (): Promise<AdminUser> => {
    if (apiClient.useMock) { const u = readLocal(); if (!u) throw new Error('Not logged in'); return u; }
    return apiClient.get<AdminUser>('/admin/auth/me');
  },
};
