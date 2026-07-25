export const ENDPOINTS = {
  products: {
    list: '/products',
    detail: (slug: string) => `/products/${slug}`,
    search: '/products/search',
    reviews: (productId: string) => `/products/${productId}/reviews`,
  },
  categories: {
    list: '/categories',
    detail: (slug: string) => `/categories/${slug}`,
  },
  collections: {
    list: '/collections',
    detail: (slug: string) => `/collections/${slug}`,
  },
  banners: {
    list: '/banners',
  },
  cart: {
    get: '/cart',
    add: '/cart/items',
    update: (itemId: string) => `/cart/items/${itemId}`,
    remove: (itemId: string) => `/cart/items/${itemId}`,
    applyCoupon: '/cart/coupon',
  },
  wishlist: {
    get: '/wishlist',
    add: '/wishlist/items',
    remove: (productId: string) => `/wishlist/items/${productId}`,
  },
  auth: {
    login: '/auth/login',
    register: '/auth/register',
    logout: '/auth/logout',
    me: '/auth/me',
  },
  orders: {
    list: '/orders',
    detail: (orderId: string) => `/orders/${orderId}`,
    create: '/orders',
  },
} as const;
