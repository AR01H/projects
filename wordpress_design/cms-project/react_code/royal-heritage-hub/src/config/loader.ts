/**
 * Config Loader — switches between store configurations.
 *
 * HOW TO USE:
 *   1. Create your config file in src/config/examples/store.yourbusiness.ts
 *   2. Import it below and add to the STORES map
 *   3. Set STORE_KEY env var or change the default
 *
 * .env:
 *   VITE_STORE_KEY=pickles
 */

import { STORE_CONFIG, type StoreConfig } from './store-config';
import { PICKLE_CONFIG } from './examples/store.pickles';
import { PERFUME_CONFIG } from './examples/store.perfume';

// ── Register your store configs here ──
const STORES: Record<string, StoreConfig> = {
  default: STORE_CONFIG,       // Royal Heritage Hub (brown & gold)
  pickles: PICKLE_CONFIG,      // Pickle Paradise (red & orange)
  perfume: PERFUME_CONFIG,     // Perfume Palace (purple & gold)
  // Add more:  fashion: FASHION_CONFIG, food: FOOD_CONFIG, etc.
};

// ── Get active store key ──
function getStoreKey(): string {
  return import.meta.env.VITE_STORE_KEY || 'default';
}

// ── Export the active config ──
export function getStoreConfig(): StoreConfig {
  const key = getStoreKey();
  return STORES[key] || STORES.default;
}

export const store = getStoreConfig();
