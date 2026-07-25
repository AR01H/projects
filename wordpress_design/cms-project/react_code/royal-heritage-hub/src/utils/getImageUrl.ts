/**
 * Centralized image resolution.
 * Today: picsum.photos seeded placeholders (no hotlink/rate-limit issues, deterministic per seed).
 * To go live: point IMAGE_BASE at your real CDN/image service — every image in the
 * app is requested through getImageUrl() so this is the only place to change.
 */
const IMAGE_BASE = 'https://picsum.photos';

export function getImageUrl(seed: string, width = 800, height = 800): string {
  return `${IMAGE_BASE}/seed/${seed}/${width}/${height}`;
}
