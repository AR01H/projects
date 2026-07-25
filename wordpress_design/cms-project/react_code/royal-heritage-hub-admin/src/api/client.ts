/**
 * Admin API Client — Same mock/API approach as storefront
 */

import { API_CONFIG, getApiBaseUrl } from '@/config/api';

export class ApiError extends Error {
  status: number;
  constructor(message: string, status: number) {
    super(message);
    this.status = status;
  }
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), API_CONFIG.TIMEOUT_MS);

  try {
    const res = await fetch(`${getApiBaseUrl()}${path}`, {
      ...options,
      signal: controller.signal,
      headers: { 'Content-Type': 'application/json', ...options.headers },
    });
    if (!res.ok) throw new ApiError(`Request failed: ${res.statusText}`, res.status);
    return (await res.json()) as T;
  } finally {
    clearTimeout(timeout);
  }
}

async function mockRequest<T>(loader: () => Promise<{ default: T }>, delayMs = 300): Promise<T> {
  const [mod] = await Promise.all([loader(), new Promise((r) => setTimeout(r, delayMs))]);
  return mod.default;
}

export const apiClient = {
  get: <T>(path: string) => request<T>(path, { method: 'GET' }),
  post: <T>(path: string, body?: unknown) => request<T>(path, { method: 'POST', body: JSON.stringify(body) }),
  put: <T>(path: string, body?: unknown) => request<T>(path, { method: 'PUT', body: JSON.stringify(body) }),
  delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
  mock: mockRequest,
  useMock: API_CONFIG.USE_MOCK,
};

async function safe<T>(fn: () => Promise<T>): Promise<{ data: T | null; error: string | null }> {
  try {
    const data = await fn();
    return { data, error: null };
  } catch (err: any) {
    console.error('[Admin API Error]', err?.message);
    return { data: null, error: err?.message || 'An error occurred' };
  }
}

export { safe };
