# Royal Heritage Hub — Client Portal

A luxury, production-ready storefront for authentic Indian handcrafted goods —
Kondapalli wooden toys, brass decor, temple items, and heritage gifting —
built as a static, API-driven React app.

## Stack

- **React 19 + TypeScript + Vite** — static build, no SSR, deployable to GitHub Pages for free
- **Tailwind CSS v4** — theme tokens defined once in `src/theme/theme.css`
- **React Router** — all routes centralized in `src/config/routes.ts`
- **Zustand** — cart & wishlist state (`src/store/`)
- **Static JSON mock API** — swappable for a real backend with a one-line config change

## Getting Started

```bash
npm install
cp .env.example .env
npm run dev
```

Build for production:

```bash
npm run build   # outputs to /dist
npm run preview
```

## Deploying to GitHub Pages (free)

1. Push this repo to GitHub.
2. In the repo, go to **Settings -> Pages -> Source** and select **GitHub Actions**.
3. Push to `main` — `.github/workflows/deploy.yml` builds and deploys automatically.
4. Update `base` in `vite.config.ts` to match your repo name if it differs from `royal-heritage-hub`.

## Architecture

```
src/
  api/            # ALL data access goes through here — no component calls fetch() directly
    client.ts     # low-level HTTP client + mock JSON loader, switches on API_CONFIG.USE_MOCK
    endpoints.ts  # every backend path, centralized
    products.ts / category.ts / collections.ts / banners.ts / cart.ts / wishlist.ts
  config/         # every configurable value lives here — change once, apply everywhere
    constants.ts  # app name, contact info, shipping rules, currency
    api.ts        # base URLs per environment (dev/test/prod), CDN/image URLs
    routes.ts     # every route path + a buildRoute() helper
    storage.ts    # localStorage key names
    breakpoints.ts
  theme/
    theme.css       # base tokens + theme-driven texture utilities
    themes.ts        # THEME REGISTRY — change ACTIVE_THEME_KEY here to re-skin the site
    applyTheme.ts     # writes active theme's tokens onto :root at runtime
  data/           # static JSON acting as the mock backend today
    products.json / categories.json / collections.json / banners.json
  store/          # Zustand stores (cart, wishlist)
  components/
    common/       # Button, Badge, Rating, Skeletons, SectionHeading, HorizontalScroller...
    product/      # ProductCard, ProductGrid, CategoryCard
    layout/       # Header, Footer, CartDrawer, Layout
    home/         # Hero, CategoryStrip, ProductRail, WhyChooseUs, Testimonials, FAQ...
  pages/          # one file per route
  types/          # shared TypeScript interfaces (Product, Category, Collection, Banner)
```

## Switching Themes

Five full visual themes are pre-built: **Royal Luxury** (default), **Vintage Heritage**,
**Traditional Indian**, **Modern Minimal**, and **Festive**. Each defines its own
color palette, fonts, corner radii, and a decorative background texture.

To change the site's look, edit **one line** in `src/theme/themes.ts`:

```ts
export const ACTIVE_THEME_KEY: keyof typeof THEMES = 'royal-luxury';
// change to: 'vintage-heritage' | 'traditional-indian' | 'modern-minimal' | 'festive'
```

That's it — every page, component, and section reads from the same CSS custom
properties (applied at runtime in `src/theme/applyTheme.ts`), so nothing else
needs to change. To add a brand-new theme, add another entry to the `THEMES`
object in the same file.

## Switching the Entire Vertical (e.g. handcrafted goods → pickles, perfumes, cookies)

This storefront is built so that **only two things** need to change to re-purpose
it for a completely different kind of product — no component code, ever:

1. **`src/config/site.ts`** — brand name, tagline, terminology, hero/about copy,
   FAQs, testimonials, empty-state microcopy, contact info. Every string a
   component would otherwise hardcode lives here instead.
2. **`src/data/*.json`** — products, categories, collections, banners.

Products no longer have fixed fields like `material`/`origin`/`dimensions`.
Instead every product has a generic `specs: ProductSpec[]` array —
`{ key, label, value, highlight }` — so a wooden toy can have "Material" and
"Dimensions" while a pickle jar has "Spice Level" and "Net Weight", using the
exact same `Product` type and the exact same detail-page rendering code.
There's also a generic `qualityBadges: string[]` (e.g. "Handmade" vs.
"Preservative-Free") and an optional `makerName` (artisan vs. brand/kitchen name).

**Proof this works:** `src/data/examples/pickles/` and
`src/config/examples/site.pickles.ts` contain a complete second dataset for a
gourmet pickle brand. Swapping them in (copying over `src/data/*.json` and the
`SITE_CONFIG` export in `src/config/site.ts`) builds and runs the *entire* site
— hero, product pages, specs, reviews, tags, everything — as a pickle storefront
with zero changes to any `.tsx` file. See `src/config/examples/site.pickles.ts`
for the full example.

One manual step outside this system: `index.html`'s `<title>` and meta
description are static HTML and aren't wired to `SITE_CONFIG` (since plain HTML
can't import TypeScript) — update those two lines by hand when rebranding.

## New Pages & Discovery Features

- **`/categories`** and **`/categories/:categorySlug`** — a browsable, editorial-style
  category index and landing page (distinct layout from the filter-heavy `/shop`).
- **`/tags`** and **`/tags/:tag`** — every product's `tags[]` array is browsable;
  tag pills throughout the product detail page link into these.
- **`/reviews`** — a site-wide reviews page aggregating every product's reviews,
  with a rating distribution chart and star-based filtering.

## Mobile Shop Filters

On mobile, `/shop` uses a horizontally-scrollable category chip row (a glass,
blurred bar that sticks under the header) instead of stacking a full filter
sidebar above the products. A sticky bottom bar (Flipkart-style) offers
**Sort** and **Filter**, each opening a bottom sheet. Desktop keeps the
traditional sidebar layout.

## Animations

- `src/hooks/useScrollReveal.ts` + `<Reveal>` component — fade/slide-up on
  scroll into view, used across section headings, story blocks, and cards.
- `theme.css` defines reusable keyframes (`fadeInUp`, `scaleIn`, `slideInRight/Left`,
  `slideUpSheet`, `shimmer`, `floatY`) as utility classes so any component can
  animate in consistently.
- `.glass-surface` / `.glass-surface-dark` — blurred, translucent surfaces used
  for the sticky mobile filter bar and bottom sheets.
- Micro-interactions: product card hover-lift, button active-scale, cart badge
  pop-in on quantity change, image zoom-on-hover on the product detail gallery.

## Multi-Currency

Five currencies are supported out of the box: INR (base), USD, EUR, GBP, AED.
All product prices in the data files are stored in the **base currency**
(`SITE_CONFIG.currency`, INR by default). A currency switcher lives in the
header (desktop: icon row; mobile: inside the menu drawer) and persists the
user's choice to `localStorage`.

- **`src/config/currency.ts`** — the list of supported currencies and their
  conversion rate from the base currency. Add a currency or change a rate here.
- **`src/store/useCurrencyStore.ts`** — holds the active currency, persisted.
- **`src/utils/formatCurrency.ts`** — exposes `useFormatCurrency()`, a hook
  every price-displaying component uses so prices convert and format live when
  the user switches currency. A non-hook `formatCurrency()` also exists for
  contexts without React (always renders in the base currency).

To wire up real exchange rates instead of static ones, replace the rates in
`SUPPORTED_CURRENCIES` with values from a live rates API on an interval or at
app startup.

## Breadcrumbs

`src/components/common/Breadcrumbs.tsx` renders a `Home / ... / Current Page`
trail and is used on the Shop, Product Detail, Category Landing, Collection
Detail, and Tag Collection pages. Each page builds its own `items` array,
usually by walking the category or tag ancestor chain (see below).

## Category & Tag Hierarchy

Both categories and tags support parent/child relationships, using the same
pattern so a subcategory or sub-tag "belongs to" something broader:

- **Categories** — `Category.parentSlug` in `src/data/categories.json` points
  to another category's slug. `categoryApi` exposes `getTopLevel()`,
  `getChildren()`, `getParent()`, `getAncestors()` (full chain, root-first —
  what breadcrumbs use), and `getTree()` (grouped parent+children, used by
  `/categories`).
- **Tags** — `src/data/tags.json` is a small metadata file mapping each raw
  tag string to a display `label` and an optional `parentTag`. `tagsApi`
  mirrors the same shape: `getTopLevel()`, `getChildren()`, `getParent()`.
  Products themselves still just have a flat `tags: string[]` — the hierarchy
  lives in the metadata file, not on the product, so re-tagging a product
  never requires touching the hierarchy.

Both hierarchies are entirely optional — a category or tag with no parent is
simply top-level, and the pickle example dataset (`src/data/examples/pickles/`)
intentionally ships with a flatter hierarchy to prove the system degrades
gracefully when a vertical doesn't need deep nesting.

## Blog

- **`/blog`** — post listing with category filter pills; **`/blog/category/:categorySlug`**
  filters to one category; **`/blog/:postSlug`** is the full post view with
  related posts and tags.
- A **Blog dropdown** in the header nav (hover or click) shows all categories
  plus the 3 most recent posts with thumbnails — no page load needed to browse.
- Data lives in `src/data/blogPosts.json` and `src/data/blogCategories.json`.
  Add a post by adding an entry to `blogPosts.json`; no code changes needed.

## Government Certifications Page

**`/certifications`** renders a repeating image + text block for each entry in
`src/data/certifications.json` — alternating left/right layout on desktop,
stacked on mobile. Add a new certificate by adding a JSON entry:

```json
{
  "id": "cert-005",
  "title": "Your Certificate Title",
  "issuedBy": "Issuing Authority",
  "certificateNumber": "Optional",
  "date": "Optional, e.g. 2026-01-01",
  "description": "What this certification means.",
  "image": "URL to a photo/scan of the certificate",
  "imageSide": "left"
}
```

## Reusable Custom Content Page (iframe support)

**`/pages/:pageKey`** is a single, reusable page component
(`src/pages/CustomContentPage.tsx`) driven entirely by
`src/config/customContentPages.ts`. Add an entry there to create a new page
without writing a new component — each entry can embed an `iframeUrl` (Google
Maps, YouTube, a Google Form, any third-party embed), raw `html`, or just a
title/description. Example:

```ts
{
  pageKey: 'my-new-page',
  title: 'My New Page',
  description: 'Optional subtitle',
  iframeUrl: 'https://example.com/embed',
  iframeHeight: 500,
}
```

That page is then live at `/pages/my-new-page` — link to it from the header,
footer, or anywhere else with `buildRoute(ROUTES.page, { pageKey: 'my-new-page' })`.

## Switching from mock data to a real API

Everything currently reads from `src/data/*.json` via each API module's
`apiClient.mock(...)` branch. To go live:

1. Set `VITE_USE_MOCK=false` in `.env`.
2. Set `VITE_API_BASE_URL_PROD` (and dev/test as needed) to your real API.
3. Make sure your backend responses match the shapes in `src/types/product.ts`.

No component code needs to change — every page calls `productsApi.getX()`,
`categoryApi.getX()`, etc., which already branch on `USE_MOCK`.

## Notes

- Cart and wishlist currently persist to `localStorage` (see `STORAGE_KEYS` in
  `src/config/storage.ts`) so they survive refreshes even before a backend exists.
- Product and banner images use `picsum.photos` seeded placeholders (deterministic,
  hotlink-friendly, no rate-limit issues) rather than Unsplash — swap the `image`
  fields in the JSON files under `src/data/` for real photography whenever ready,
  or point `getImageUrl()` in `src/utils/getImageUrl.ts` at a real CDN.
- The homepage Hero always renders a dark gradient background behind slide images,
  so headline text stays legible even if a slide image is slow to load or fails.
- This build covers the client/shopper experience only, per spec — no admin portal.
