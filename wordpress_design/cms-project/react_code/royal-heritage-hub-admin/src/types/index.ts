// ── Universal Status Types ──
export type EntityStatus = 'active' | 'inactive' | 'draft';
export type ProductStatus = 'active' | 'draft' | 'upcoming' | 'out_of_stock' | 'discontinued';
export type OrderStatus = 'placed' | 'confirmed' | 'processing' | 'shipped' | 'out_for_delivery' | 'delivered' | 'cancelled';
export type ReviewStatus = 'approved' | 'pending' | 'rejected';
export type CouponStatus = 'active' | 'inactive' | 'expired' | 'scheduled';
export type BannerPosition = 'hero' | 'promo' | 'sidebar' | 'popup' | 'page_hero';

export type AdminRole = 'super_admin' | 'admin' | 'editor' | 'viewer';

export interface AdminUser {
  id: string;
  name: string;
  email: string;
  role: AdminRole;
  avatar?: string;
  lastLogin?: string;
}

export interface Product {
  id: string;
  slug: string;
  name: string;
  shortDescription: string;
  description: string;
  categoryId: string;
  categorySlug: string;
  collectionIds: string[];
  price: number;
  compareAtPrice?: number;
  currency: string;
  images: string[];
  videoUrl?: string;
  thumbnail: string;
  sku: string;
  specs: { key: string; label: string; value: string; highlight?: boolean }[];
  qualityBadges: string[];
  makerName?: string;
  stock: number;
  lowStockThreshold: number;
  rating: number;
  reviewCount: number;
  reviews: ProductReview[];
  variants: ProductVariant[];
  tags: string[];
  isBestSeller: boolean;
  isNewArrival: boolean;
  isFeatured: boolean;
  isLimitedEdition: boolean;
  isFestive: boolean;
  status: ProductStatus;
  createdAt: string;
}

export interface ProductReview {
  id: string;
  author: string;
  rating: number;
  title: string;
  comment: string;
  date: string;
  verified: boolean;
  photos?: string[];
}

export interface ProductVariant {
  id: string;
  label: string;
  type: string;
  value: string;
  priceModifier?: number;
  inStock: boolean;
}

export interface Category {
  id: string;
  slug: string;
  name: string;
  description: string;
  image: string;
  icon?: string;
  productCount: number;
  featured: boolean;
  parentSlug?: string;
  status: EntityStatus;
}

export interface Collection {
  id: string;
  slug: string;
  name: string;
  description: string;
  image: string;
  productIds: string[];
  status: EntityStatus;
}

export interface Banner {
  id: string;
  title: string;
  subtitle: string;
  ctaLabel: string;
  ctaLink: string;
  image: string;
  theme: 'light' | 'dark';
  position: BannerPosition;
  sortOrder: number;
  status: EntityStatus;
  startDate?: string;
  endDate?: string;
}

export interface BlogPost {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  content: string[];
  coverImage: string;
  categorySlug: string;
  author: string;
  date: string;
  readMinutes: number;
  tags: string[];
  status: 'published' | 'draft' | 'archived';
}

export interface BlogCategory {
  id: string;
  slug: string;
  name: string;
  status: EntityStatus;
}

export type CouponType =
  | 'percentage'           // 10% off
  | 'fixed'               // ₹500 off
  | 'free_shipping'       // Free delivery
  | 'buy_x_get_y'         // Buy 1 Get 1, Buy 2 Get 1, etc.
  | 'buy_x_get_percent'   // Buy 2 Get 50% off third
  | 'bundle'              // Bundle discount (buy set of items for less)
  | 'first_order'         // First order only
  | 'referral'            // Referral reward
  | 'loyalty'             // Loyalty points multiplier
  | 'category_percent'    // X% off entire category
  | 'category_fixed'      // ₹X off entire category
  | 'flash_sale'          // Time-limited deep discount
  | 'tiered'              // Spend ₹X get Y% off
  | 'cart_threshold'      // Spend above ₹X get ₹Y off
  | 'free_gift'           // Buy anything, get free gift item
  | 'volume'              // Buy 3+ get discount
  | 'repeat_purchase'     // Discount on re-order
  | 'birthday'            // Birthday special
  | 'seasonal'            // Seasonal/holiday specific
  | 'clearance';          // Clearance sale

export type CouponAppliesTo = 'all' | 'products' | 'categories' | 'collections' | 'tags' | 'variants';

export interface CouponTier {
  minAmount: number;       // Minimum spend for this tier
  discount: number;        // Discount value for this tier
  label: string;           // e.g. "Spend ₹5000 get 15% off"
}

export interface Coupon {
  // ── Core ──
  code: string;
  description: string;
  type: CouponType;
  status: CouponStatus;
  active: boolean;

  // ── Discount Value ──
  discount: number;         // Percentage (0.1 = 10%) or fixed amount
  discountType: 'percentage' | 'fixed';

  // ── BOGO / Buy X Get Y ──
  buyQuantity: number;      // How many to buy (for BOGO)
  getQuantity: number;      // How many to get free/discounted
  getDiscount: number;      // Discount on the "get" items (0 = free, 0.5 = 50% off)

  // ── Order Rules ──
  minOrder: number;         // Minimum order value
  maxDiscount: number;      // Maximum discount cap
  maxOrder: number;         // Maximum order value (for tiered)

  // ── Usage Limits ──
  usageLimit: number;       // Total usage limit (0 = unlimited)
  usedCount: number;        // How many times used
  perUserLimit: number;     // Per-user usage limit (0 = unlimited)

  // ── Date/Time Rules ──
  validFrom: string;        // Start date
  validUntil: string;       // End date
  validDays: string[];      // Days of week: ['mon','tue','wed','thu','fri','sat','sun']
  validTimeFrom: string;    // Time window start: "09:00"
  validTimeTo: string;      // Time window end: "17:00"
  isSeasonal: boolean;      // Seasonal coupon flag
  seasonTag: string;        // e.g. "diwali", "christmas", "eid"

  // ── Product/Category Restrictions ──
  appliesTo: CouponAppliesTo;
  productIds: string[];       // Specific product IDs
  categoryIds: string[];      // Specific category IDs
  collectionIds: string[];    // Specific collection IDs
  tags: string[];             // Specific tags
  excludeProductIds: string[]; // Excluded products
  excludeCategoryIds: string[]; // Excluded categories

  // ── Customer Restrictions ──
  customerEmails: string[];   // Specific customers
  customerGroups: string[];   // e.g. ['first_time', 'loyal', 'vip']
  isFirstOrderOnly: boolean;  // First order only
  isRepeatOnly: boolean;      // Repeat purchase only
  minCustomerOrders: number;  // Minimum orders to use
  minCustomerSpent: number;   // Minimum lifetime spend

  // ── Tiered Discounts ──
  tiers: CouponTier[];

  // ── Volume Discounts ──
  volumeThreshold: number;    // Min quantity for volume discount

  // ── Free Gift ──
  giftProductId: string;      // Product to give free
  giftProductName: string;
  giftQuantity: number;

  // ── Referral ──
  referralReward: number;     // Reward for referrer
  refereeDiscount: number;    // Discount for referee

  // ── Loyalty ──
  loyaltyPointsMultiplier: number; // e.g. 2 = double points

  // ── Display ──
  badge: string;              // e.g. "HOT", "LIMITED", "NEW USER"
  bgColor: string;            // Badge background color
  textColor: string;          // Badge text color
  bannerImage: string;        // Promotional banner image

  // ── Stacking ──
  stackable: boolean;         // Can combine with other coupons
  priority: number;           // Higher = applied first

  // ── Analytics ──
  totalDiscountGiven: number;
  totalOrdersAffected: number;
  avgOrderValue: number;
}

export interface CouponUsageLog {
  id: string;
  couponCode: string;
  userId: string;
  userName: string;
  orderId: string;
  orderTotal: number;
  discountAmount: number;
  usedAt: string;
}

export interface CouponStats {
  totalCoupons: number;
  activeCoupons: number;
  expiredCoupons: number;
  scheduledCoupons: number;
  totalUsage: number;
  totalDiscountGiven: number;
  topCoupons: { code: string; usageCount: number; discount: number }[];
  usageByDay: { date: string; count: number }[];
}

export interface Order {
  id: string;
  userId: string;
  items: { product: Product; quantity: number; variantId?: string }[];
  subtotal: number;
  shipping: number;
  discount: number;
  codCharge: number;
  total: number;
  address: {
    name: string;
    phone: string;
    email: string;
    line1: string;
    line2?: string;
    city: string;
    state: string;
    pincode: string;
  };
  paymentMethod: 'upi' | 'card' | 'netbanking' | 'cod';
  paymentId?: string;
  couponCode?: string;
  status: OrderStatus;
  tracking: { status: string; label: string; date: string | null; completed: boolean }[];
  notes?: string;
  createdAt: string;
  updatedAt: string;
}

export interface Customer {
  id: string;
  name: string;
  email: string;
  phone?: string;
  ordersCount: number;
  totalSpent: number;
  createdAt: string;
  lastOrderAt?: string;
  status: 'active' | 'blocked';
}

export interface DashboardStats {
  totalProducts: number;
  totalOrders: number;
  totalCustomers: number;
  totalRevenue: number;
  revenueChange: number;
  orderChange: number;
  customerChange: number;
  productChange: number;
  recentOrders: Order[];
  topProducts: { product: Product; sold: number }[];
  ordersByStatus: { status: string; count: number }[];
  revenueByMonth: { month: string; revenue: number }[];
}

export interface ApiResponse<T> {
  data: T;
  error: string | null;
}

export interface TagMeta {
  tag: string;
  label: string;
  parentTag: string | null;
  status: EntityStatus;
}

export interface CertificationEntry {
  id: string;
  title: string;
  issuedBy: string;
  certificateNumber?: string;
  date?: string;
  description: string;
  image: string;
  imageSide?: 'left' | 'right';
  status: EntityStatus;
}

export interface Review {
  id: string;
  productId: string;
  productName: string;
  productSlug: string;
  productThumbnail: string;
  author: string;
  rating: number;
  title: string;
  comment: string;
  date: string;
  verified: boolean;
  status: ReviewStatus;
}

export interface FooterData {
  quickLinks: { label: string; href: string; icon?: string; status?: EntityStatus }[];
  policyLinks: { label: string; href: string }[];
  socialLinks: { platform: string; url: string; icon: string }[];
  paymentMethods: { name: string; icon: string }[];
  trustBadges: { label: string; description: string; icon: string }[];
  certifications: { name: string; description: string }[];
  workingHours: { label: string; days: string; time: string; closed: string };
}

export interface StoreSettings {
  brand: { name: string; tagline: string; shortName: string; logo: string; favicon: string };
  contact: { phone: string; email: string; address: string };
  shipping: { freeShippingThreshold: number; defaultShippingCharge: number; codCharge: number; estimatedDeliveryMin: number; estimatedDeliveryMax: number };
  currency: { code: string; symbol: string; locale: string };
  social: { instagram: string; facebook: string; pinterest: string; youtube: string; whatsapp: string };
}

export interface UploadedFile {
  id: string;
  name: string;
  url: string;
  type: 'image' | 'video' | 'document';
  mimeType: string;
  size: number;
  uploadedAt: string;
}

export interface ProductCertificate {
  id: string;
  productId: string;
  title: string;
  issuer: string;
  certificateNumber?: string;
  issueDate?: string;
  expiryDate?: string;
  description: string;
  imageUrl?: string;
  documentUrl?: string;
  verified: boolean;
}

export interface ProductExternalAttribute {
  id: string;
  key: string;
  label: string;
  value: string;
  type: 'text' | 'number' | 'boolean' | 'url' | 'date' | 'select';
  options?: string[];
  section: string;
  highlighted: boolean;
}

export interface ProductSection {
  id: string;
  title: string;
  content: string;
  imageUrl?: string;
  videoUrl?: string;
  type: 'text' | 'image' | 'video' | 'gallery' | 'specs';
  order: number;
}
