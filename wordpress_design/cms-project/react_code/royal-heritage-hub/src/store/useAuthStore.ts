/**
 * Auth Store — User login, register, profile, logout
 */

import { create } from 'zustand';
import { authApi, type User } from '@/api/auth';

interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;

  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, phone?: string) => Promise<void>;
  logout: () => Promise<void>;
  updateProfile: (data: Partial<User>) => Promise<void>;
  init: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  isAuthenticated: false,
  isLoading: false,
  error: null,

  init: () => {
    const user = authApi.getCurrentUser();
    const isAuth = authApi.isAuthenticated();
    set({ user, isAuthenticated: isAuth });
  },

  login: async (email, password) => {
    set({ isLoading: true, error: null });
    try {
      const { user } = await authApi.login(email, password);
      set({ user, isAuthenticated: true, isLoading: false });
    } catch (err: any) {
      set({ error: err.message || 'Login failed', isLoading: false });
    }
  },

  register: async (name, email, password, phone) => {
    set({ isLoading: true, error: null });
    try {
      const { user } = await authApi.register(name, email, password, phone);
      set({ user, isAuthenticated: true, isLoading: false });
    } catch (err: any) {
      set({ error: err.message || 'Registration failed', isLoading: false });
    }
  },

  logout: async () => {
    await authApi.logout();
    set({ user: null, isAuthenticated: false });
  },

  updateProfile: async (data) => {
    set({ isLoading: true, error: null });
    try {
      const user = await authApi.updateProfile(data);
      set({ user, isLoading: false });
    } catch (err: any) {
      set({ error: err.message || 'Update failed', isLoading: false });
    }
  },
}));
