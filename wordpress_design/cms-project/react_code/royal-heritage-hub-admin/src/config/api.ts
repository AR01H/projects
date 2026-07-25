/**
 * Admin API Configuration — Same approach as storefront
 * Set VITE_USE_MOCK=true for mock mode, false for real backend
 */

const env = import.meta.env;

export const API_CONFIG = {
  USE_MOCK: (env.VITE_USE_MOCK ?? 'true') === 'true',
  BASE_URL: env.VITE_API_BASE_URL || 'http://localhost:4000/api',
  TIMEOUT_MS: Number(env.VITE_API_TIMEOUT) || 15000,
};

export function getApiBaseUrl(): string {
  return API_CONFIG.BASE_URL;
}
