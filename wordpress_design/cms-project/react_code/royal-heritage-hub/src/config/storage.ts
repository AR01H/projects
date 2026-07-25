export const STORAGE_KEYS = {
  // ── Auth ──
  user: 'rhh_user',
  token: 'rhh_token',

  // ── Shopping ──
  cart: 'rhh_cart',
  wishlist: 'rhh_wishlist',
  orders: 'rhh_orders',
  coupons: 'rhh_coupons',

  // ── Preferences ──
  theme: 'rhh_theme',
  language: 'rhh_language',
  currency: 'rhh_currency',
  recentlyViewed: 'rhh_recently_viewed',
} as const;
