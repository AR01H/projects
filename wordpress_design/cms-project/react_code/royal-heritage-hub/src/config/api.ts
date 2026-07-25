/**
 * API Configuration — change these values to point to your backend.
 *
 * Option 1: Edit this file directly
 * Option 2: Set environment variables in .env file
 *
 * .env.example:
 *   VITE_API_BASE_URL=http://localhost:4000/api
 *   VITE_CDN_URL=https://cdn.yourdomain.com
 *   VITE_IMAGE_URL=https://images.yourdomain.com
 *   VITE_USE_MOCK=false
 */

const env = import.meta.env;

// ══════════════════════════════════════════════════════════════
// CHANGE THESE VALUES FOR YOUR BACKEND
// ══════════════════════════════════════════════════════════════

export const API_CONFIG = {
  // Set to false when your backend is ready
  USE_MOCK: (env.VITE_USE_MOCK ?? 'true') === 'true',

  // Your backend API base URL
  BASE_URL: env.VITE_API_BASE_URL || 'http://localhost:4000/api',

  // CDN for product images (leave empty to use product.thumbnail directly)
  CDN_URL: env.VITE_CDN_URL || '',

  // Image optimization service (e.g., Cloudinary, imgix)
  IMAGE_URL: env.VITE_IMAGE_URL || '',

  // Static assets URL
  ASSETS_URL: env.VITE_ASSETS_URL || '/assets',

  // Request timeout in ms
  TIMEOUT_MS: Number(env.VITE_API_TIMEOUT) || 15000,
};

// ══════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ══════════════════════════════════════════════════════════════

export function getApiBaseUrl(): string {
  return API_CONFIG.BASE_URL;
}

export function getCdnUrl(path: string): string {
  if (!API_CONFIG.CDN_URL) return path;
  return `${API_CONFIG.CDN_URL}${path}`;
}

export function getImageUrl(path: string): string {
  if (!API_CONFIG.IMAGE_URL) return path;
  return `${API_CONFIG.IMAGE_URL}${path}`;
}
