/**
 * Auth API — Login, Register, Profile, Logout
 */

import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { STORAGE_KEYS } from '@/config/storage';

export interface User {
  id: string;
  name: string;
  email: string;
  phone?: string;
  createdAt: string;
}

interface AuthResponse {
  user: User;
  token: string;
}

function readLocalUser(): User | null {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.user);
    return raw ? JSON.parse(raw) : null;
  } catch { return null; }
}

function writeLocalUser(user: User | null) {
  if (user) localStorage.setItem(STORAGE_KEYS.user, JSON.stringify(user));
  else localStorage.removeItem(STORAGE_KEYS.user);
}

function readLocalToken(): string | null {
  return localStorage.getItem(STORAGE_KEYS.token);
}

function writeLocalToken(token: string | null) {
  if (token) localStorage.setItem(STORAGE_KEYS.token, token);
  else localStorage.removeItem(STORAGE_KEYS.token);
}

export const authApi = {
  // ── Current user state ──
  getCurrentUser: (): User | null => readLocalUser(),
  getToken: (): string | null => readLocalToken(),
  isAuthenticated: (): boolean => !!readLocalToken(),

  // ── Login ──
  login: async (email: string, password: string): Promise<AuthResponse> => {
    if (apiClient.useMock) {
      // Mock: any email/password combo works, create user on the fly
      const user: User = {
        id: `user-${Date.now()}`,
        name: email.split('@')[0],
        email,
        createdAt: new Date().toISOString(),
      };
      const token = `mock-token-${Date.now()}`;
      writeLocalUser(user);
      writeLocalToken(token);
      return { user, token };
    }
    const res = await apiClient.post<AuthResponse>(ENDPOINTS.auth.login, { email, password });
    writeLocalUser(res.user);
    writeLocalToken(res.token);
    return res;
  },

  // ── Register ──
  register: async (name: string, email: string, password: string, phone?: string): Promise<AuthResponse> => {
    if (apiClient.useMock) {
      const user: User = {
        id: `user-${Date.now()}`,
        name,
        email,
        phone,
        createdAt: new Date().toISOString(),
      };
      const token = `mock-token-${Date.now()}`;
      writeLocalUser(user);
      writeLocalToken(token);
      return { user, token };
    }
    const res = await apiClient.post<AuthResponse>(ENDPOINTS.auth.register, { name, email, password, phone });
    writeLocalUser(res.user);
    writeLocalToken(res.token);
    return res;
  },

  // ── Get profile ──
  getProfile: async (): Promise<User> => {
    if (apiClient.useMock) {
      const user = readLocalUser();
      if (!user) throw new Error('Not logged in');
      return user;
    }
    const user = await apiClient.get<User>(ENDPOINTS.auth.me);
    writeLocalUser(user);
    return user;
  },

  // ── Update profile ──
  updateProfile: async (data: Partial<User>): Promise<User> => {
    if (apiClient.useMock) {
      const user = readLocalUser();
      if (!user) throw new Error('Not logged in');
      const updated = { ...user, ...data };
      writeLocalUser(updated);
      return updated;
    }
    const user = await apiClient.put<User>(ENDPOINTS.auth.me, data);
    writeLocalUser(user);
    return user;
  },

  // ── Logout ──
  logout: async (): Promise<void> => {
    if (!apiClient.useMock) {
      await apiClient.post(ENDPOINTS.auth.logout);
    }
    writeLocalUser(null);
    writeLocalToken(null);
  },
};
