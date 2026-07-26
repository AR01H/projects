import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';

export interface User {
  id: string;
  name: string;
  email: string;
  phone?: string;
  createdAt: string;
}

interface AuthResponse {
  data: {
    user: User;
    token: string;
  };
}

interface ProfileResponse {
  data: User;
}

const TOKEN_KEY = 'cms_token';
const USER_KEY = 'cms_user';

function readLocalUser(): User | null {
  try {
    const raw = localStorage.getItem(USER_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch { return null; }
}

function writeLocalUser(user: User | null) {
  if (user) localStorage.setItem(USER_KEY, JSON.stringify(user));
  else localStorage.removeItem(USER_KEY);
}

function readLocalToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

function writeLocalToken(token: string | null) {
  if (token) localStorage.setItem(TOKEN_KEY, token);
  else localStorage.removeItem(TOKEN_KEY);
}

export const authApi = {
  getCurrentUser: (): User | null => readLocalUser(),
  getToken: (): string | null => readLocalToken(),
  isAuthenticated: (): boolean => !!readLocalToken(),

  login: async (email: string, password: string): Promise<{ user: User; token: string }> => {
    if (apiClient.useMock) {
      const user: User = { id: `user-${Date.now()}`, name: email.split('@')[0], email, createdAt: new Date().toISOString() };
      const token = `mock-token-${Date.now()}`;
      writeLocalUser(user);
      writeLocalToken(token);
      return { user, token };
    }
    const res = await apiClient.post<AuthResponse>(ENDPOINTS.auth.login, { email, password });
    writeLocalUser(res.data.user);
    writeLocalToken(res.data.token);
    return res.data;
  },

  register: async (name: string, email: string, password: string, phone?: string): Promise<{ user: User; token: string }> => {
    if (apiClient.useMock) {
      const user: User = { id: `user-${Date.now()}`, name, email, phone, createdAt: new Date().toISOString() };
      const token = `mock-token-${Date.now()}`;
      writeLocalUser(user);
      writeLocalToken(token);
      return { user, token };
    }
    const res = await apiClient.post<AuthResponse>(ENDPOINTS.auth.register, { name, email, password, phone });
    writeLocalUser(res.data.user);
    writeLocalToken(res.data.token);
    return res.data;
  },

  getProfile: async (): Promise<User> => {
    if (apiClient.useMock) {
      const user = readLocalUser();
      if (!user) throw new Error('Not logged in');
      return user;
    }
    const res = await apiClient.get<ProfileResponse>(ENDPOINTS.auth.me);
    writeLocalUser(res.data);
    return res.data;
  },

  updateProfile: async (data: Partial<User>): Promise<User> => {
    if (apiClient.useMock) {
      const user = readLocalUser();
      if (!user) throw new Error('Not logged in');
      const updated = { ...user, ...data };
      writeLocalUser(updated);
      return updated;
    }
    const res = await apiClient.post<{ data: User }>(ENDPOINTS.auth.me + '?_method=PUT', data);
    writeLocalUser(res.data);
    return res.data;
  },

  logout: async (): Promise<void> => {
    if (!apiClient.useMock) {
      try { await apiClient.post(ENDPOINTS.auth.logout); } catch { /* ignore */ }
    }
    writeLocalUser(null);
    writeLocalToken(null);
  },
};
