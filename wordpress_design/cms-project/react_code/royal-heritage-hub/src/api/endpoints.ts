export const ENDPOINTS = {
  auth: {
    login: '/auth/login',
    register: '/auth/register',
    logout: '/auth/logout',
    me: '/auth/me',
  },
  products: {
    list: '/products',
    detail: (slug: string) => `/products/${slug}`,
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
  tags: {
    list: '/tags',
  },
  blog: {
    posts: '/blog/posts',
    post: (slug: string) => `/blog/posts/${slug}`,
    categories: '/blog/categories',
  },
  reviews: {
    list: '/reviews',
    stats: '/reviews/stats',
  },
  certifications: {
    list: '/certifications',
  },
  footer: {
    get: '/footer',
  },
  cart: {
    get: '/cart',
    add: '/cart/items',
    update: (itemId: string) => `/cart/items/${itemId}`,
    remove: (itemId: string) => `/cart/items/${itemId}`,
  },
  wishlist: {
    get: '/wishlist',
    add: '/wishlist/items',
    update: (productId: string) => `/wishlist/items/${productId}`,
    remove: (productId: string) => `/wishlist/items/${productId}`,
    clear: '/wishlist',
    shared: '/wishlist/shared',
  },
  coupons: {
    list: '/coupons',
    validate: '/coupons/validate',
    apply: '/coupons/apply',
  },
  orders: {
    list: '/orders',
    detail: (orderId: string) => `/orders/${orderId}`,
    create: '/orders',
    cancel: (orderId: string) => `/orders/${orderId}/cancel`,
  },
  payment: {
    createIntent: '/payment/create-intent',
    verify: '/payment/verify',
  },
} as const;
