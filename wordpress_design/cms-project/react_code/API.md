# API Documentation — Royal Heritage Hub

> Shared API reference for both **storefront** and **admin panel**.
> All APIs run in mock mode (`USE_MOCK: true`) and read from `src/data/mockData.ts`.
> Switch to live mode by setting `USE_MOCK: false` and configuring the base URL.

---

## Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Storefront    │────▶│   Backend API   │◀────│   Admin Panel   │
│   (Customer)    │     │   (REST)        │     │   (Management)  │
└─────────────────┘     └─────────────────┘     └─────────────────┘
         │                       │                       │
         └───────────────────────┴───────────────────────┘
                     All use same mockData.ts
```

---

## API Client

| Project | File | Methods |
|---------|------|---------|
| Storefront | `src/api/client.ts` | `get`, `post`, `put`, `delete`, `useMock` |
| Admin | `src/api/client.ts` | `get`, `post`, `put`, `delete`, `useMock` |

**Error handling:** Throws `ApiError` with `status` code on non-2xx responses.

---

## Storefront APIs

### Products (`src/api/products.ts`)

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `getAll()` | — | `Product[]` | All products |
| `getBySlug(slug)` | `slug: string` | `Product \| undefined` | Single product by slug |
| `getFiltered(filters)` | `ProductFilters` | `Product[]` | Filtered + sorted |
| `getBestSellers(limit?)` | `limit: number` (default 8) | `Product[]` | `isBestSeller: true` |
| `getNewArrivals(limit?)` | `limit: number` (default 8) | `Product[]` | `isNewArrival: true` |
| `getFeatured(limit?)` | `limit: number` (default 8) | `Product[]` | `isFeatured: true` |
| `getFestive(limit?)` | `limit: number` (default 8) | `Product[]` | `isFestive: true` |
| `getLimitedEdition(limit?)` | `limit: number` (default 8) | `Product[]` | `isLimitedEdition: true` |
| `getRelated(product, limit?)` | `Product, number` (default 4) | `Product[]` | Same category |
| `getByIds(ids)` | `ids: string[]` | `Product[]` | By IDs |
| `getTrending(limit?)` | `limit: number` (default 8) | `Product[]` | By review count |
| `getDealOfTheDay()` | — | `Product \| undefined` | Highest discount |
| `getRecommended(limit?)` | `limit: number` (default 8) | `Product[]` | By rating |
| `getByMaterial(keyword, limit?)` | `string, number` (default 8) | `Product[]` | By material spec |
| `getRecentlyViewed(ids)` | `ids: string[]` | `Product[]` | By view history |
| `getFrequentlyBoughtTogether(product, limit?)` | `Product, number` (default 3) | `Product[]` | Different category |

```typescript
interface ProductFilters {
  categorySlug?: string;
  collectionSlug?: string;
  minPrice?: number;
  maxPrice?: number;
  material?: string;
  tag?: string;
  minRating?: number;
  inStockOnly?: boolean;
  search?: string;
  sortBy?: 'newest' | 'best-selling' | 'featured' | 'price-asc' | 'price-desc' | 'rating';
}
```

### Categories (`src/api/category.ts`)

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `getAll()` | — | `Category[]` | All categories |
| `getFeatured(limit?)` | `limit: number` (default 6) | `Category[]` | Featured only |
| `getBySlug(slug)` | `slug: string` | `Category \| undefined` | By slug |
| `getTopLevel()` | — | `Category[]` | No parentSlug |
| `getChildren(parentSlug)` | `string` | `Category[]` | Direct children |
| `getParent(category)` | `Category` | `Category \| undefined` | Parent category |
| `getAncestors(category)` | `Category` | `Category[]` | Full chain root-first |
| `getTree()` | — | `{ parent, children }[]` | Grouped tree |

### Collections (`src/api/collections.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Collection[]` |
| `getBySlug(slug)` | `slug: string` | `Collection \| undefined` |

### Tags (`src/api/tags.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `TagSummary[]` |
| `getByTag(tag)` | `tag: string` | `Product[]` |
| `getPopular(limit?)` | `limit: number` (default 12) | `TagSummary[]` |
| `getTopLevel()` | — | `TagSummary[]` |
| `getChildren(parentTag)` | `string` | `TagSummary[]` |
| `getLabel(tag)` | `string` | `string` |

### Banners (`src/api/banners.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `BannersResponse` |
| `getHero()` | — | `Banner[]` |
| `getPromo()` | — | `Banner[]` |
| `getPageHero(pageKey)` | `string` | `Banner \| undefined` |

### Blog (`src/api/blog.ts` + `src/api/blogEnhanced.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAllPosts()` | — | `BlogPost[]` |
| `getAllCategories()` | — | `BlogCategory[]` |
| `getBySlug(slug)` | `string` | `BlogPost \| undefined` |
| `getByCategory(categorySlug)` | `string` | `BlogPost[]` |
| `getRecent(limit?)` | `number` (default 5) | `BlogPost[]` |
| `getRelated(post, limit?)` | `BlogPost, number` (default 3) | `BlogPost[]` |
| `getPaginated(filters, page)` | `BlogFilters, number` | `PaginatedBlog` |
| `search(query)` | `string` | `BlogPost[]` |

### Reviews (`src/api/reviews.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `AggregatedReview[]` |
| `getStats()` | — | `{ totalReviews, avgRating, distribution }` |

### Certifications (`src/api/certifications.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `CertificationEntry[]` |

### Cart (`src/api/cart.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `get()` | — | `CartItem[]` |
| `addItem(product, quantity?, variantId?)` | `Product, number, string?` | `CartItem[]` |
| `updateQuantity(itemId, quantity)` | `string, number` | `CartItem[]` |
| `removeItem(itemId)` | `string` | `CartItem[]` |
| `clear()` | — | `void` |

### Wishlist (`src/api/wishlist.ts` + `src/api/wishlistEnhanced.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `get()` | — | `WishlistItem[]` |
| `add(product, notes?)` | `Product, string?` | `WishlistItem[]` |
| `remove(productId)` | `string` | `WishlistItem[]` |
| `toggle(product)` | `Product` | `{ added, items }` |
| `moveToCart(productId, cartApi)` | `string, any` | `{ wishlist, cart }` |
| `clear()` | — | `void` |
| `generateShareLink()` | — | `string` |
| `loadShared(idsParam)` | `string` | `WishlistItem[]` |

### Coupons (`src/api/coupons.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `validate(code, subtotal, itemCount?)` | `string, number, number?` | `CouponResult` |
| `apply(code)` | `string` | `void` |
| `getAll()` | — | `Coupon[]` |

#### Coupon Types (20 types supported)

| Type | Description | Example |
|------|-------------|---------|
| `percentage` | X% off order | 10% off |
| `fixed` | ₹X off order | ₹500 off |
| `free_shipping` | Free delivery | No shipping charge |
| `buy_x_get_y` | Buy N Get M Free | Buy 1 Get 1 Free |
| `buy_x_get_percent` | Buy N Get M at X% off | Buy 2 Get 3rd at 50% off |
| `bundle` | Bundle discount | Buy set of items for less |
| `first_order` | First order only | New customer discount |
| `referral` | Referral reward | ₹500 for referrer + referee |
| `loyalty` | Loyalty points multiplier | 2x points on all purchases |
| `category_percent` | X% off entire category | 15% off all Brass items |
| `flash_sale` | Time-limited deep discount | 30% off for 24 hours |
| `tiered` | Spend ₹X get Y% off | Spend ₹5000 get 20% off |
| `cart_threshold` | Spend above ₹X get ₹Y off | Orders ₹2999+ get ₹500 off |
| `free_gift` | Buy anything, get free gift | ₹2000+ order gets free Diya |
| `volume` | Buy 3+ get discount | Buy 3+ items get 15% off |
| `repeat_purchase` | Discount on re-order | Returning customer discount |
| `birthday` | Birthday special | 20% off during birthday month |
| `seasonal` | Seasonal/holiday specific | Diwali 20% off |
| `clearance` | Clearance sale | 40% off discontinued items |

#### Coupon Features

- **Date/Time Rules**: validFrom, validUntil, validDays (weekdays), validTimeFrom/To (time window)
- **Usage Limits**: total usage limit, per-user limit
- **Product Restrictions**: apply to all, specific products, categories, collections, tags, variants
- **Exclusions**: exclude specific products or categories
- **Customer Restrictions**: first order only, repeat only, min orders, min spend, customer groups
- **Tiered Discounts**: multiple spend thresholds with increasing discounts
- **BOGO**: configurable buy quantity, get quantity, get discount percentage
- **Volume Discounts**: quantity threshold for discount
- **Free Gift**: specific product gifted with qualifying order
- **Referral**: separate rewards for referrer and referee
- **Loyalty**: points multiplier (e.g. 2x points)
- **Display**: badge text, badge colors, banner image
- **Stackable**: can combine with other coupons
- **Priority**: higher priority applied first
- **Analytics**: total discount given, orders affected, avg order value

#### CouponResult Type
```typescript
{
  valid: boolean;
  coupon: Coupon | null;
  discountAmount: number;
  freeShipping: boolean;
  freeGift?: { productId: string; productName: string; quantity: number };
  bogoItems?: { productId: string; name: string; price: number }[];
  error?: string;
  tierLabel?: string;
}
```

### Auth (`src/api/auth.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `login(email, password)` | `string, string` | `{ user, token }` |
| `register(name, email, password, phone?)` | `string, string, string, string?` | `{ user, token }` |
| `getProfile()` | — | `User` |
| `updateProfile(data)` | `Partial<User>` | `User` |
| `logout()` | — | `void` |

### Orders (`src/api/orders.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `create(input)` | `CreateOrderInput` | `Order` |
| `getAll()` | — | `Order[]` |
| `getById(orderId)` | `string` | `Order \| undefined` |
| `getTracking(orderId)` | `string` | `OrderTrackingStep[]` |
| `cancel(orderId)` | `string` | `Order` |

### Payment (`src/api/payment.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getConfig()` | — | `PaymentConfig` |
| `pay(amount, method)` | `number, string` | `PaymentResult` |
| `verify(paymentId, orderId)` | `string, string` | `boolean` |

---

## Admin APIs

### Auth (`src/api/auth.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `login(email, password)` | `string, string` | `{ user, token }` |
| `getProfile()` | — | `AdminUser` |
| `logout()` | — | `void` |

### Products (`src/api/products.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Product[]` |
| `getById(id)` | `string` | `Product \| null` |
| `getBySlug(slug)` | `string` | `Product \| null` |
| `create(data)` | `Partial<Product>` | `Product` |
| `update(id, data)` | `string, Partial<Product>` | `Product` |
| `delete(id)` | `string` | `boolean` |
| `bulkDelete(ids)` | `string[]` | `number` |
| `updateStock(id, stock)` | `string, number` | `{ id, stock }` |

### Orders (`src/api/orders.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Order[]` |
| `getById(id)` | `string` | `Order \| null` |
| `updateStatus(id, status)` | `string, OrderStatus` | `Order` |
| `getStats()` | — | `DashboardStats` |

### Customers (`src/api/customers.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Customer[]` |
| `getById(id)` | `string` | `Customer \| null` |

### Blog (`src/api/blog.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAllPosts()` | — | `BlogPost[]` |
| `getAllCategories()` | — | `BlogCategory[]` |
| `createPost(data)` | `Partial<BlogPost>` | `BlogPost` |
| `updatePost(id, data)` | `string, Partial<BlogPost>` | `BlogPost` |
| `deletePost(id)` | `string` | `boolean` |

### Categories (`src/api/categories.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Category[]` |
| `getById(id)` | `string` | `Category \| null` |
| `create(data)` | `Partial<Category>` | `Category` |
| `update(id, data)` | `string, Partial<Category>` | `Category` |
| `delete(id)` | `string` | `boolean` |

### Banners (`src/api/banners.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Banner[]` |
| `create(data)` | `Partial<Banner>` | `Banner` |
| `update(id, data)` | `string, Partial<Banner>` | `Banner` |
| `delete(id)` | `string` | `boolean` |
| `reorder(ids)` | `string[]` | `boolean` |

### Coupons (`src/api/coupons.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Coupon[]` |
| `create(data)` | `Partial<Coupon>` | `Coupon` |
| `update(code, data)` | `string, Partial<Coupon>` | `Coupon` |
| `delete(code)` | `string` | `boolean` |
| `toggleActive(code)` | `string` | `{ code, active }` |
| `getStats()` | — | `CouponStats` |
| `getUsageLog(code?)` | `string?` | `CouponUsageLog[]` |

#### Admin Coupon Features
- Full CRUD with 20 coupon types
- Visual badge customization (color, text, banner)
- Tiered discount configuration
- BOGO / Buy X Get Y rules
- Product/category/tag restrictions
- Customer group targeting
- Date/time/weekday scheduling
- Usage analytics (total discount, orders affected, avg order value)
- Usage history log

---

## Data Types

### Product
```typescript
{
  id: string; slug: string; name: string;
  shortDescription: string; description: string;
  categoryId: string; categorySlug: string; collectionIds: string[];
  price: number; compareAtPrice?: number; currency: string;
  images: string[]; videoUrl?: string; thumbnail: string; sku: string;
  specs: { key: string; label: string; value: string; highlight?: boolean }[];
  qualityBadges: string[]; makerName?: string;
  stock: number; lowStockThreshold: number;
  rating: number; reviewCount: number;
  reviews: ProductReview[]; variants: ProductVariant[];
  tags: string[];
  isBestSeller: boolean; isNewArrival: boolean; isFeatured: boolean;
  isLimitedEdition: boolean; isFestive: boolean;
  createdAt: string;
}
```

### Category
```typescript
{
  id: string; slug: string; name: string; description: string;
  image: string; icon?: string; productCount: number;
  featured: boolean; parentSlug?: string;
}
```

### Order
```typescript
{
  id: string; userId: string;
  items: { product: Product; quantity: number; variantId?: string }[];
  subtotal: number; shipping: number; discount: number; codCharge: number; total: number;
  address: { name, phone, email, line1, line2?, city, state, pincode };
  paymentMethod: 'upi' | 'card' | 'netbanking' | 'cod';
  paymentId?: string; couponCode?: string;
  status: 'placed' | 'confirmed' | 'processing' | 'shipped' | 'out_for_delivery' | 'delivered' | 'cancelled';
  tracking: { status: string; label: string; date: string | null; completed: boolean }[];
  notes?: string; createdAt: string; updatedAt: string;
}
```

### Customer
```typescript
{
  id: string; name: string; email: string; phone?: string;
  ordersCount: number; totalSpent: number;
  createdAt: string; lastOrderAt?: string;
}
```

### BlogPost
```typescript
{
  id: string; slug: string; title: string; excerpt: string;
  content: string[]; coverImage: string; categorySlug: string;
  author: string; date: string; readMinutes: number; tags: string[];
}
```

### Banner
```typescript
{
  id: string; title: string; subtitle: string;
  ctaLabel: string; ctaLink: string; image: string;
  theme: 'light' | 'dark';
}
```

### Coupon
```typescript
{
  code: string; discount: number;
  minOrder?: number; maxDiscount?: number;
  validFrom?: string; validUntil?: string;
  usageLimit?: number; usedCount: number;
  active: boolean; description?: string;
}
```

### AdminUser
```typescript
{
  id: string; name: string; email: string;
  role: 'super_admin' | 'admin' | 'editor' | 'viewer';
  avatar?: string; lastLogin?: string;
}
```

---

## Backend Endpoints Summary

### Storefront

| Resource | GET | POST | PUT | DELETE |
|----------|-----|------|-----|--------|
| Products | `/products`, `/products/:slug`, `/products/deal-of-day`, `/products/:id/related`, `/products/recently-viewed` | `/products/by-ids` | — | — |
| Categories | `/categories`, `/categories/:slug`, `/categories/:slug/ancestors`, `/categories/tree` | — | — | — |
| Collections | `/collections`, `/collections/:slug` | — | — | — |
| Tags | `/tags`, `/tags/:tag/products`, `/tags/:tag/children`, `/tags/:tag/label` | — | — | — |
| Banners | `/banners`, `/banners/hero`, `/banners/promo`, `/banners/page/:key` | — | — | — |
| Blog | `/blog/posts`, `/blog/posts/:slug`, `/blog/posts/:slug/related`, `/blog/categories`, `/blog/tags` | — | — | — |
| Reviews | `/reviews`, `/reviews/stats` | `/products/:id/reviews` | — | — |
| Cart | `/cart` | `/cart/items` | `/cart/items/:id` | `/cart/items/:id`, `/cart` |
| Wishlist | `/wishlist` | `/wishlist/items`, `/wishlist/toggle`, `/wishlist/shared` | `/wishlist/items/:id` | `/wishlist/items/:id`, `/wishlist` |
| Coupons | `/coupons` | `/coupons/validate`, `/coupons/apply` | — | — |
| Auth | `/auth/me` | `/auth/login`, `/auth/register`, `/auth/logout` | `/auth/me` | — |
| Orders | `/orders`, `/orders/:id`, `/orders/:id/tracking` | `/orders` | `/orders/:id/cancel` | — |
| Payment | — | `/payment/pay`, `/payment/verify` | — | — |

### Admin

| Resource | GET | POST | PUT | DELETE |
|----------|-----|------|-----|--------|
| Auth | `/admin/auth/me` | `/admin/auth/login`, `/admin/auth/logout` | — | — |
| Products | `/admin/products`, `/admin/products/:id` | `/admin/products`, `/admin/products/bulk-delete` | `/admin/products/:id`, `/admin/products/:id/stock` | `/admin/products/:id` |
| Orders | `/admin/orders`, `/admin/orders/:id`, `/admin/orders/stats` | — | `/admin/orders/:id/status` | — |
| Customers | `/admin/customers`, `/admin/customers/:id` | — | — | — |
| Blog | `/admin/blog/posts`, `/admin/blog/categories` | `/admin/blog/posts` | `/admin/blog/posts/:id` | `/admin/blog/posts/:id` |
| Categories | `/admin/categories` | `/admin/categories` | `/admin/categories/:id` | `/admin/categories/:id` |
| Collections | `/admin/collections`, `/admin/collections/:id` | `/admin/collections` | `/admin/collections/:id` | `/admin/collections/:id` |
| Tags | `/admin/tags` | `/admin/tags` | `/admin/tags/:tag` | `/admin/tags/:tag` |
| Banners | `/admin/banners` | `/admin/banners` | `/admin/banners/:id`, `/admin/banners/reorder` | `/admin/banners/:id` |
| Coupons | `/admin/coupons` | `/admin/coupons` | `/admin/coupons/:code`, `/admin/coupons/:code/toggle` | `/admin/coupons/:code` |
| Certifications | `/admin/certifications`, `/admin/certifications/:id` | `/admin/certifications` | `/admin/certifications/:id` | `/admin/certifications/:id` |
| Reviews | `/admin/reviews`, `/admin/reviews/:id`, `/admin/reviews/stats` | — | — | `/admin/reviews/:id` |
| Footer | `/admin/footer` | — | `/admin/footer` | — |
| Settings | `/admin/settings` | — | `/admin/settings` | — |

---

## Admin API Methods

### Collections (`src/api/collections.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Collection[]` |
| `getById(id)` | `string` | `Collection \| null` |
| `getBySlug(slug)` | `string` | `Collection \| null` |
| `create(data)` | `Partial<Collection>` | `Collection` |
| `update(id, data)` | `string, Partial<Collection>` | `Collection` |
| `delete(id)` | `string` | `boolean` |

### Tags (`src/api/tags.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `TagMeta[]` |
| `create(data)` | `Partial<TagMeta>` | `TagMeta` |
| `update(tag, data)` | `string, Partial<TagMeta>` | `TagMeta` |
| `delete(tag)` | `string` | `boolean` |

### Certifications (`src/api/certifications.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `CertificationEntry[]` |
| `getById(id)` | `string` | `CertificationEntry \| null` |
| `create(data)` | `Partial<CertificationEntry>` | `CertificationEntry` |
| `update(id, data)` | `string, Partial<CertificationEntry>` | `CertificationEntry` |
| `delete(id)` | `string` | `boolean` |

### Reviews (`src/api/reviews.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `Review[]` |
| `getById(id)` | `string` | `Review \| null` |
| `delete(id)` | `string` | `boolean` |
| `getStats()` | — | `{ totalReviews, avgRating, distribution }` |

### Footer (`src/api/footer.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `getAll()` | — | `FooterData` |
| `update(data)` | `Partial<FooterData>` | `FooterData` |

### Settings (`src/api/settings.ts`)

| Method | Parameters | Returns |
|--------|-----------|---------|
| `get()` | — | `StoreSettings` |
| `update(data)` | `Partial<StoreSettings>` | `StoreSettings` |

---

## Admin Pages

| Page | Route | Features |
|------|-------|----------|
| Dashboard | `/dashboard` | Stats, charts, recent orders, top products |
| Products | `/products` | CRUD, stock update, bulk delete, search |
| Orders | `/orders` | List, status update, stats |
| Customers | `/customers` | List, view details |
| Blog | `/blog` | CRUD posts, categories |
| Categories | `/categories` | CRUD, hierarchy |
| Collections | `/collections` | CRUD, assign products |
| Tags | `/tags` | CRUD, parent-child hierarchy |
| Banners | `/banners` | CRUD, reorder |
| Coupons | `/coupons` | CRUD, toggle active |
| Certifications | `/certifications` | CRUD, image positioning |
| Reviews | `/reviews` | List, stats, delete |
| Settings | `/settings` | Brand, contact, shipping, social, currency |

---

## Mock Data

All mock data is centralized in `src/data/mockData.ts` for each project:

**Storefront:** `MOCK_PRODUCTS`, `MOCK_CATEGORIES`, `MOCK_BANNERS`, `MOCK_COLLECTIONS`, `MOCK_TAGS`, `MOCK_CERTIFICATIONS`, `MOCK_BLOG_POSTS`, `MOCK_BLOG_CATEGORIES`, `MOCK_STOREFRONT_COUPONS`, `MOCK_FOOTER`

**Admin:** `MOCK_PRODUCTS`, `MOCK_ORDERS`, `MOCK_CUSTOMERS`, `MOCK_BLOG_POSTS`, `MOCK_BLOG_CATEGORIES`, `MOCK_CATEGORIES`, `MOCK_BANNERS`, `MOCK_COUPONS`, `MOCK_DASHBOARD_STATS`, `MOCK_ADMIN_USERS`, `MOCK_COLLECTIONS`, `MOCK_TAGS`, `MOCK_CERTIFICATIONS`, `MOCK_REVIEWS`, `MOCK_FOOTER`, `MOCK_SETTINGS`

---

*Total: 120+ endpoints across 16 resource groups*
