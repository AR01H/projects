/**
 * Single place to switch environments.
 * Today USE_MOCK is true and everything is served from /src/data/*.json.
 * To go live: set VITE_API_BASE_URL in .env and flip USE_MOCK to false —
 * no component code needs to change, only apiClient's fetch implementation.
 */
const ENV = import.meta.env.MODE;

export const API_CONFIG = {
  env: ENV,
  USE_MOCK: (import.meta.env.VITE_USE_MOCK ?? 'true') === 'true',
  baseUrl: {
    development: import.meta.env.VITE_API_BASE_URL_DEV || 'http://localhost:4000/api',
    test: import.meta.env.VITE_API_BASE_URL_TEST || 'https://staging-api.royalheritagehub.com/api',
    production: import.meta.env.VITE_API_BASE_URL_PROD || 'https://api.royalheritagehub.com/api',
  },
  cdnUrl: import.meta.env.VITE_CDN_URL || '',
  imageUrl: import.meta.env.VITE_IMAGE_URL || '',
  assetsUrl: import.meta.env.VITE_ASSETS_URL || '/assets',
  timeoutMs: 15000,
};

export function getApiBaseUrl(): string {
  if (ENV === 'production') return API_CONFIG.baseUrl.production;
  if (ENV === 'test') return API_CONFIG.baseUrl.test;
  return API_CONFIG.baseUrl.development;
}
