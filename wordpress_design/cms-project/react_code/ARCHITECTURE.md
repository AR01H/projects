# Architecture & Code Rules — Royal Heritage Hub

> Shared architecture reference for both **storefront** and **admin panel**.
> Both projects follow the same patterns and rules.

---

## Project Structure

```
react_code/
├── royal-heritage-hub/          # Storefront (customer-facing)
│   ├── src/
│   │   ├── api/                 # API layer (products, cart, auth, etc.)
│   │   ├── config/              # Store config, routes, navigation, theme
│   │   ├── data/                # Mock data + JSON files
│   │   │   ├── mockData.ts      # CENTRALIZED mock data (single source)
│   │   │   └── *.json           # Raw data files
│   │   ├── hooks/               # React hooks (useCallbacks, useCart, etc.)
│   │   ├── store/               # Zustand stores
│   │   ├── types/               # TypeScript interfaces
│   │   ├── components/          # Reusable UI components
│   │   └── pages/               # Route pages
│   └── ...
│
├── royal-heritage-hub-admin/    # Admin panel (management)
│   ├── src/
│   │   ├── api/                 # Admin API layer (products, orders, etc.)
│   │   ├── config/              # Admin config (API, storage)
│   │   ├── data/                # Mock data
│   │   │   └── mockData.ts      # CENTRALIZED mock data (single source)
│   │   ├── types/               # TypeScript interfaces
│   │   ├── components/          # Admin UI components
│   │   └── pages/               # Admin pages
│   └── ...
│
├── API.md                       # API documentation (this folder)
└── ARCHITECTURE.md              # This file
```

---

## Core Rules

### 1. Single Source of Truth for Mock Data

**Rule:** All mock data MUST live in `src/data/mockData.ts`. No inline mock data in API files.

```typescript
// ✅ CORRECT — Import from centralized mock data
import { MOCK_PRODUCTS } from '@/data/mockData';

async function getAll(): Promise<Product[]> {
  if (apiClient.useMock) return MOCK_PRODUCTS;
  return apiClient.get<Product[]>('/api/products');
}

// ❌ WRONG — Inline mock data
const MOCK_PRODUCTS = [...]; // Don't do this
```

### 2. API Layer Pattern

**Rule:** Every API file follows the same structure:

```typescript
import { apiClient } from './client';
import { MOCK_DATA } from '@/data/mockData';
import type { DataType } from '@/types';

async function getAll(): Promise<DataType[]> {
  if (apiClient.useMock) return MOCK_DATA;
  return apiClient.get<DataType[]>(ENDPOINT);
}

export const dataApi = {
  getAll: () => safe(getAll),
  getById: (id: string) => safe(async () => { ... }),
  create: (data: Partial<DataType>) => safe(async () => { ... }),
  update: (id: string, data: Partial<DataType>) => safe(async () => { ... }),
  delete: (id: string) => safe(async () => { ... }),
};
```

### 3. No Cross-Project Imports

**Rule:** Storefront and admin must NEVER import from each other.

```typescript
// ❌ WRONG — Admin importing from storefront
import { MOCK_PRODUCTS } from '../../../royal-heritage-hub/src/data/products.json';

// ✅ CORRECT — Each project has its own mockData.ts
import { MOCK_PRODUCTS } from '@/data/mockData';
```

### 4. Callbacks Pattern (Storefront)

**Rule:** All callbacks are centralized in `src/hooks/useCallbacks.ts`:

```typescript
import { useCartCallbacks, useWishlistCallbacks, useCouponCallbacks } from '@/hooks/useCallbacks';

function ProductCard({ product }) {
  const { handleAddToCart } = useCartCallbacks();
  const { handleToggleWishlist, isWishlisted } = useWishlistCallbacks();

  return (
    <button onClick={() => handleAddToCart(product)}>Add to Cart</button>
  );
}
```

### 5. Config-Driven Architecture (Storefront)

**Rule:** Brand identity, colors, and content are controlled via config files:

| Config | File | Controls |
|--------|------|----------|
| Store | `src/config/store-config.ts` | Brand, colors, commerce, contact, social, content |
| Loader | `src/config/loader.ts` | Multi-company registry |
| Texts | `src/config/texts.ts` | All UI text strings |
| Theme | `src/theme/themes.ts` | Color palette |

### 6. State Management

**Rule:** Use Zustand for global state with localStorage persistence:

| Store | Purpose | Persistence |
|-------|---------|-------------|
| `useCartStore` | Cart items, open/close | localStorage |
| `useWishlistStore` | Wishlist items | localStorage |
| `useRecentlyViewedStore` | View history | localStorage |
| `useCurrencyStore` | Currency preference | localStorage |
| `useCouponStore` | Applied coupon | In-memory |

---

## Creating a New Company

### Quick Start (3 Steps)

**Step 1:** Create company config
```typescript
// src/config/store.yourcompany.ts
export const YOUR_CONFIG: StoreConfig = {
  brand: { name: 'Your Company', tagline: 'Your Tagline', ... },
  colors: { primary: '#YOUR_COLOR', ... },
  // ... fill all fields
};
```

**Step 2:** Register in loader
```typescript
// src/config/loader.ts
const STORES = {
  default: STORE_CONFIG,
  yourcompany: YOUR_CONFIG,  // ← Add here
};
```

**Step 3:** Set environment variable
```
VITE_STORE_KEY=yourcompany
```

### What to Change

| File | What |
|------|------|
| `src/config/store.yourcompany.ts` | Brand, colors, copy, contact, social |
| `src/config/loader.ts` | Register your config |
| `src/data/mockData.ts` | Products, orders, customers, blog, categories, banners, coupons |
| `src/theme/themes.ts` | Color palette |
| `public/logo.svg` | Your logo |
| `public/favicon.svg` | Your favicon |
| `.env` | `VITE_STORE_KEY=yourcompany` |

### What Stays the Same

- All React components
- All page layouts
- All animations
- All hooks
- All API logic
- All routing
- All responsive design

---

## File Naming Conventions

| Pattern | Example | Usage |
|---------|---------|-------|
| `camelCase.ts` | `useCartStore.ts` | Hooks, stores, utilities |
| `PascalCase.tsx` | `ProductCard.tsx` | React components |
| `kebab-case.json` | `products.json` | Data files |
| `UPPER_CASE` | `MOCK_PRODUCTS` | Constants, mock data exports |

---

## TypeScript Rules

1. **All API functions** must be typed with explicit return types
2. **All mock data** must match the TypeScript interfaces in `src/types/`
3. **No `any` types** — use proper interfaces
4. **Optional fields** use `?` suffix
5. **Union types** for status fields: `'active' | 'inactive' | 'out_of_stock'`

---

## Multi-Company Architecture

```
.env                          → VITE_STORE_KEY=yourcompany
src/config/loader.ts          → Reads env, selects config
src/config/store-config.ts    → Company-specific config
src/config/texts.ts           → Company-specific UI text
src/data/mockData.ts          → Company-specific data
src/theme/themes.ts           → Company-specific colors
```

Each company runs from the same codebase with different config and data files. No code changes needed.

---

*Last updated: 2026-07-25*
