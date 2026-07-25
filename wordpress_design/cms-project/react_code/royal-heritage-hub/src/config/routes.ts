export const ROUTES = {
  home: '/',
  shop: '/shop',
  category: '/shop/:categorySlug',
  product: '/product/:productSlug',
  collections: '/collections',
  collection: '/collections/:collectionSlug',
  tags: '/tags',
  tag: '/tags/:tag',
  categories: '/categories',
  categoryLanding: '/categories/:categorySlug',
  reviews: '/reviews',
  blog: '/blog',
  blogCategory: '/blog/category/:categorySlug',
  blogPost: '/blog/:postSlug',
  govtCertifications: '/certifications',
  page: '/pages/:pageKey',
  wishlist: '/wishlist',
  cart: '/cart',
  checkout: '/checkout',
  about: '/about',
  artisans: '/artisans',
  craftRegions: '/craft-regions',
  contact: '/contact',
  faqs: '/faqs',
  // ── Auth ──
  login: '/login',
  register: '/register',
  profile: '/profile',
  // ── Orders ──
  orders: '/orders',
  orderDetail: '/orders/:orderId',
} as const;

export function buildRoute(
  route: string,
  params: Record<string, string | number> = {}
): string {
  let path: string = route;
  Object.entries(params).forEach(([key, value]) => {
    path = path.replace(`:${key}`, String(value));
  });
  return path;
}
