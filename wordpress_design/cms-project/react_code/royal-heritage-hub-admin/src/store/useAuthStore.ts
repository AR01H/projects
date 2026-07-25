import { create } from 'zustand';
import { authApi } from '@/api/auth';
import type { AdminUser } from '@/types';

interface AuthState {
  user: AdminUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  init: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null, isAuthenticated: false, isLoading: false, error: null,
  init: () => {
    const user = authApi.getCurrentUser();
    set({ user, isAuthenticated: !!user });
  },
  login: async (email, password) => {
    set({ isLoading: true, error: null });
    try {
      const { user } = await authApi.login(email, password);
      set({ user, isAuthenticated: true, isLoading: false });
    } catch (err: any) {
      set({ error: err.message, isLoading: false });
    }
  },
  logout: async () => {
    await authApi.logout();
    set({ user: null, isAuthenticated: false });
  },
}));
