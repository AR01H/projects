import { API_CONFIG, getApiBaseUrl } from '@/config/api';

export class ApiError extends Error {
  status: number;
  constructor(message: string, status: number) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
  }
}

interface RequestOptions extends RequestInit {
  params?: Record<string, string | number | boolean | undefined>;
}

function buildUrl(path: string, params?: RequestOptions['params']): string {
  const url = new URL(`${getApiBaseUrl()}${path}`, window.location.origin);
  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined) url.searchParams.set(key, String(value));
    });
  }
  return url.toString();
}

/**
 * Real HTTP request — used once USE_MOCK is false / a live backend exists.
 */
async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { params, ...init } = options;
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), API_CONFIG.timeoutMs);

  try {
    const res = await fetch(buildUrl(path, params), {
      ...init,
      signal: controller.signal,
      headers: {
        'Content-Type': 'application/json',
        ...init.headers,
      },
    });

    if (!res.ok) {
      throw new ApiError(`Request failed: ${res.statusText}`, res.status);
    }
    return (await res.json()) as T;
  } finally {
    clearTimeout(timeout);
  }
}

/**
 * Mock loader — reads static JSON bundled with the app.
 * Simulates network latency so loading states are exercised in dev.
 */
async function mockRequest<T>(loader: () => Promise<{ default: T }>, delayMs = 350): Promise<T> {
  const [mod] = await Promise.all([
    loader(),
    new Promise((resolve) => setTimeout(resolve, delayMs)),
  ]);
  return mod.default;
}

export const apiClient = {
  get: <T>(path: string, params?: RequestOptions['params']) =>
    request<T>(path, { method: 'GET', params }),
  post: <T>(path: string, body?: unknown) =>
    request<T>(path, { method: 'POST', body: JSON.stringify(body) }),
  put: <T>(path: string, body?: unknown) =>
    request<T>(path, { method: 'PUT', body: JSON.stringify(body) }),
  delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
  mock: mockRequest,
  useMock: API_CONFIG.USE_MOCK,
};
