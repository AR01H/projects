/**
 * CENTRALIZED MOCK DATA — All mock data in one file for the admin panel.
 * Every entity has a status field for active/inactive/draft/upcoming control.
 */

import type { Product, Order, Customer, BlogPost, BlogCategory, Category, Banner, Coupon, AdminUser, Collection, TagMeta, CertificationEntry, Review, FooterData, StoreSettings } from '@/types';

// ── Products ──
export const MOCK_PRODUCTS: Product[] = [
  { id: 'p1', slug: 'kondapalli-dancing-couple', name: 'Kondapalli Dancing Couple', shortDescription: 'Hand-carved traditional dancing couple from Kondapalli.', description: 'Hand-carved and hand-painted traditional dancing couple from the historic Kondapalli toy-making village.', categoryId: 'cat-toys', categorySlug: 'handcrafted-toys', collectionIds: ['col-best-sellers'], price: 1499, compareAtPrice: 1899, currency: 'INR', images: ['https://picsum.photos/seed/p1/800/800'], thumbnail: 'https://picsum.photos/seed/p1/400/400', sku: 'KDC-001', specs: [{ key: 'material', label: 'Material', value: 'Tella Poniki Wood', highlight: true }, { key: 'height', label: 'Height', value: '8 inches' }, { key: 'origin', label: 'Origin', value: 'Kondapalli, Andhra Pradesh' }], qualityBadges: ['Handmade', 'Natural Dyes'], makerName: 'Ramu Arts', stock: 14, lowStockThreshold: 5, rating: 4.8, reviewCount: 32, reviews: [], variants: [], tags: ['traditional', 'handmade', 'gift'], isBestSeller: true, isNewArrival: false, isFeatured: true, isLimitedEdition: false, isFestive: false, status: 'active', createdAt: '2026-01-15' },
  { id: 'p2', slug: 'brass-ganesha-idol', name: 'Brass Ganesha Idol', shortDescription: 'Traditional lost-wax cast brass Ganesha.', description: 'Traditional lost-wax cast brass Ganesha idol.', categoryId: 'cat-brass', categorySlug: 'brass-items', collectionIds: [], price: 2799, currency: 'INR', images: ['https://picsum.photos/seed/p2/800/800'], thumbnail: 'https://picsum.photos/seed/p2/400/400', sku: 'BGI-001', specs: [{ key: 'material', label: 'Material', value: 'Brass', highlight: true }, { key: 'height', label: 'Height', value: '6 inches' }], qualityBadges: ['Handmade'], stock: 8, lowStockThreshold: 3, rating: 4.9, reviewCount: 48, reviews: [], variants: [], tags: ['religious', 'brass', 'ganesha'], isBestSeller: true, isNewArrival: false, isFeatured: true, isLimitedEdition: false, isFestive: false, status: 'active', createdAt: '2026-02-10' },
  { id: 'p3', slug: 'wooden-temple-mandir', name: 'Wooden Temple Mandir', shortDescription: 'Intricately carved wooden pooja mandir.', description: 'Intricately carved wooden pooja mandir.', categoryId: 'cat-decor', categorySlug: 'home-decor', collectionIds: [], price: 6499, currency: 'INR', images: ['https://picsum.photos/seed/p3/800/800'], thumbnail: 'https://picsum.photos/seed/p3/400/400', sku: 'WTM-001', specs: [{ key: 'material', label: 'Material', value: 'Teak Wood', highlight: true }], qualityBadges: ['Handmade'], stock: 3, lowStockThreshold: 2, rating: 4.7, reviewCount: 21, reviews: [], variants: [], tags: ['religious', 'wooden', 'mandir'], isBestSeller: false, isNewArrival: false, isFeatured: true, isLimitedEdition: false, isFestive: false, status: 'active', createdAt: '2026-03-05' },
  { id: 'p4', slug: 'brass-diya-set', name: 'Brass Diya Set (5 pcs)', shortDescription: 'Hand-cast brass diyas for festive lighting.', description: 'Hand-cast brass diyas for festive lighting.', categoryId: 'cat-festive', categorySlug: 'festive-items', collectionIds: [], price: 1199, currency: 'INR', images: ['https://picsum.photos/seed/p4/800/800'], thumbnail: 'https://picsum.photos/seed/p4/400/400', sku: 'BDS-001', specs: [{ key: 'material', label: 'Material', value: 'Brass', highlight: true }], qualityBadges: ['Handmade'], stock: 25, lowStockThreshold: 10, rating: 4.6, reviewCount: 56, reviews: [], variants: [], tags: ['diya', 'festive', 'brass'], isBestSeller: true, isNewArrival: false, isFeatured: false, isLimitedEdition: false, isFestive: true, status: 'active', createdAt: '2026-04-12' },
  { id: 'p5', slug: 'painted-wall-panel', name: 'Painted Wall Panel', shortDescription: 'Hand-painted Madhubani art wall panel.', description: 'Hand-painted Madhubani art wall panel.', categoryId: 'cat-wallart', categorySlug: 'wall-art', collectionIds: [], price: 3299, currency: 'INR', images: ['https://picsum.photos/seed/p5/800/800'], thumbnail: 'https://picsum.photos/seed/p5/400/400', sku: 'PWP-001', specs: [{ key: 'technique', label: 'Technique', value: 'Madhubani Painting', highlight: true }], qualityBadges: ['Handmade'], stock: 0, lowStockThreshold: 3, rating: 4.5, reviewCount: 18, reviews: [], variants: [], tags: ['madhubani', 'wall-art'], isBestSeller: false, isNewArrival: false, isFeatured: false, isLimitedEdition: false, isFestive: false, status: 'out_of_stock', createdAt: '2026-05-20' },
  { id: 'p6', slug: 'wooden-toy-elephant', name: 'Wooden Toy Elephant', shortDescription: 'Cute hand-carved wooden elephant toy.', description: 'Cute hand-carved wooden elephant toy.', categoryId: 'cat-toys', categorySlug: 'handcrafted-toys', collectionIds: ['col-best-sellers'], price: 899, currency: 'INR', images: ['https://picsum.photos/seed/p6/800/800'], thumbnail: 'https://picsum.photos/seed/p6/400/400', sku: 'WTE-001', specs: [{ key: 'material', label: 'Material', value: 'Neem Wood', highlight: true }], qualityBadges: ['Handmade', 'Child Safe'], stock: 42, lowStockThreshold: 10, rating: 4.4, reviewCount: 89, reviews: [], variants: [], tags: ['toy', 'elephant', 'wooden'], isBestSeller: true, isNewArrival: false, isFeatured: true, isLimitedEdition: false, isFestive: false, status: 'active', createdAt: '2026-06-01' },
  { id: 'p7', slug: 'bronze-nataraja', name: 'Bronze Nataraja', shortDescription: 'Traditional Chola bronze Nataraja sculpture.', description: 'Traditional Chola bronze Nataraja sculpture.', categoryId: 'cat-bronze', categorySlug: 'bronze-items', collectionIds: [], price: 4599, currency: 'INR', images: ['https://picsum.photos/seed/p7/800/800'], thumbnail: 'https://picsum.photos/seed/p7/400/400', sku: 'BN-001', specs: [{ key: 'material', label: 'Material', value: 'Bronze', highlight: true }], qualityBadges: ['Handmade', 'Museum Quality'], stock: 2, lowStockThreshold: 2, rating: 4.9, reviewCount: 15, reviews: [], variants: [], tags: ['bronze', 'nataraja', 'chola'], isBestSeller: false, isNewArrival: true, isFeatured: true, isLimitedEdition: true, isFestive: false, status: 'active', createdAt: '2026-06-15' },
  { id: 'p8', slug: 'silk-saree-bag', name: 'Silk Saree Bag', shortDescription: 'Handwoven silk saree into a stylish tote bag.', description: 'Handwoven silk saree repurposed into a stylish tote bag.', categoryId: 'cat-bags', categorySlug: 'bags-accessories', collectionIds: [], price: 1899, currency: 'INR', images: ['https://picsum.photos/seed/p8/800/800'], thumbnail: 'https://picsum.photos/seed/p8/400/400', sku: 'SSB-001', specs: [{ key: 'material', label: 'Material', value: 'Silk', highlight: true }], qualityBadges: ['Handmade', 'Eco-Friendly'], stock: 18, lowStockThreshold: 5, rating: 4.3, reviewCount: 27, reviews: [], variants: [], tags: ['silk', 'bag', 'eco'], isBestSeller: false, isNewArrival: true, isFeatured: false, isLimitedEdition: false, isFestive: false, status: 'active', createdAt: '2026-07-01' },
];

// ── Orders ──
export const MOCK_ORDERS: Order[] = [
  { id: 'ORD-001', userId: 'u1', items: [{ product: MOCK_PRODUCTS[0], quantity: 1 }, { product: MOCK_PRODUCTS[3], quantity: 2 }], subtotal: 3897, shipping: 99, discount: 0, codCharge: 0, total: 3996, address: { name: 'Priya Sharma', phone: '9876543210', email: 'priya@email.com', line1: '12 MG Road', city: 'Hyderabad', state: 'Telangana', pincode: '500001' }, paymentMethod: 'upi', status: 'delivered', tracking: [{ status: 'placed', label: 'Order Placed', date: '2026-07-20T10:00:00.000Z', completed: true }, { status: 'delivered', label: 'Delivered', date: '2026-07-23T14:00:00.000Z', completed: true }], createdAt: '2026-07-20T10:00:00.000Z', updatedAt: '2026-07-23T14:00:00.000Z' },
  { id: 'ORD-002', userId: 'u2', items: [{ product: MOCK_PRODUCTS[1], quantity: 1 }], subtotal: 2799, shipping: 0, discount: 0, codCharge: 0, total: 2799, address: { name: 'Rahul Verma', phone: '9876543211', email: 'rahul@email.com', line1: '45 Park Street', city: 'Mumbai', state: 'Maharashtra', pincode: '400001' }, paymentMethod: 'card', status: 'shipped', tracking: [{ status: 'placed', label: 'Order Placed', date: '2026-07-21T08:00:00.000Z', completed: true }, { status: 'shipped', label: 'Shipped', date: '2026-07-22T10:00:00.000Z', completed: true }], createdAt: '2026-07-21T08:00:00.000Z', updatedAt: '2026-07-22T10:00:00.000Z' },
  { id: 'ORD-003', userId: 'u3', items: [{ product: MOCK_PRODUCTS[2], quantity: 1 }], subtotal: 7698, shipping: 99, discount: 0, codCharge: 0, total: 7797, address: { name: 'Ananya Gupta', phone: '9876543212', email: 'ananya@email.com', line1: '78 Civil Lines', city: 'Delhi', state: 'Delhi', pincode: '110001' }, paymentMethod: 'cod', status: 'processing', tracking: [{ status: 'placed', label: 'Order Placed', date: '2026-07-22T12:00:00.000Z', completed: true }], createdAt: '2026-07-22T12:00:00.000Z', updatedAt: '2026-07-22T13:00:00.000Z' },
  { id: 'ORD-004', userId: 'u4', items: [{ product: MOCK_PRODUCTS[5], quantity: 2 }], subtotal: 1798, shipping: 99, discount: 0, codCharge: 0, total: 1897, address: { name: 'Vikram Patel', phone: '9876543213', email: 'vikram@email.com', line1: '123 Anna Salai', city: 'Chennai', state: 'Tamil Nadu', pincode: '600001' }, paymentMethod: 'netbanking', status: 'placed', tracking: [{ status: 'placed', label: 'Order Placed', date: '2026-07-23T15:00:00.000Z', completed: true }], createdAt: '2026-07-23T15:00:00.000Z', updatedAt: '2026-07-23T15:00:00.000Z' },
  { id: 'ORD-005', userId: 'u5', items: [{ product: MOCK_PRODUCTS[4], quantity: 1 }], subtotal: 3299, shipping: 0, discount: 0, codCharge: 0, total: 3299, address: { name: 'Meena Devi', phone: '9876543214', email: 'meena@email.com', line1: '56 Rajpath', city: 'Jaipur', state: 'Rajasthan', pincode: '302001' }, paymentMethod: 'upi', status: 'cancelled', tracking: [{ status: 'placed', label: 'Order Placed', date: '2026-07-24T09:00:00.000Z', completed: true }, { status: 'cancelled', label: 'Cancelled', date: '2026-07-24T11:00:00.000Z', completed: true }], createdAt: '2026-07-24T09:00:00.000Z', updatedAt: '2026-07-24T11:00:00.000Z' },
];

// ── Customers ──
export const MOCK_CUSTOMERS: Customer[] = [
  { id: 'c1', name: 'Priya Sharma', email: 'priya@email.com', phone: '+91 98765 43210', ordersCount: 5, totalSpent: 12500, createdAt: '2026-01-15', lastOrderAt: '2026-07-20', status: 'active' },
  { id: 'c2', name: 'Rahul Verma', email: 'rahul@email.com', phone: '+91 98765 43211', ordersCount: 3, totalSpent: 8299, createdAt: '2026-02-20', lastOrderAt: '2026-07-21', status: 'active' },
  { id: 'c3', name: 'Ananya Gupta', email: 'ananya@email.com', phone: '+91 98765 43212', ordersCount: 7, totalSpent: 28999, createdAt: '2025-11-10', lastOrderAt: '2026-07-22', status: 'active' },
  { id: 'c4', name: 'Vikram Patel', email: 'vikram@email.com', phone: '+91 98765 43213', ordersCount: 2, totalSpent: 3498, createdAt: '2026-06-01', lastOrderAt: '2026-07-23', status: 'active' },
  { id: 'c5', name: 'Meena Devi', email: 'meena@email.com', phone: '+91 98765 43214', ordersCount: 4, totalSpent: 9899, createdAt: '2026-03-15', lastOrderAt: '2026-07-24', status: 'active' },
  { id: 'c6', name: 'Arjun Kumar', email: 'arjun@email.com', phone: '+91 98765 43215', ordersCount: 1, totalSpent: 4599, createdAt: '2026-07-01', lastOrderAt: '2026-07-01', status: 'blocked' },
];

// ── Blog Posts ──
export const MOCK_BLOG_POSTS: BlogPost[] = [
  { id: 'b1', slug: 'art-of-kondapalli', title: 'The Art of Kondapalli Toy Making', excerpt: 'Discover the centuries-old tradition.', content: ['Kondapalli toys originate from Andhra Pradesh...'], coverImage: 'https://picsum.photos/seed/b1/800/400', categorySlug: 'craft-stories', author: 'Admin', date: '2026-07-15', readMinutes: 5, tags: ['kondapalli', 'toys'], status: 'published' },
  { id: 'b2', slug: 'diwali-gift-guide-2026', title: 'Diwali Gift Guide 2026', excerpt: 'Find the perfect handcrafted gift.', content: ['Diwali is the festival of lights...'], coverImage: 'https://picsum.photos/seed/b2/800/400', categorySlug: 'gift-guides', author: 'Admin', date: '2026-07-10', readMinutes: 4, tags: ['diwali', 'gifts'], status: 'published' },
  { id: 'b3', slug: 'behind-scenes-bronze-casting', title: 'Behind the Scenes: Bronze Casting', excerpt: 'Watch our artisans create sculptures.', content: ['Bronze casting is one of India\'s oldest traditions...'], coverImage: 'https://picsum.photos/seed/b3/800/400', categorySlug: 'craft-stories', author: 'Admin', date: '2026-07-05', readMinutes: 6, tags: ['bronze', 'artisan'], status: 'draft' },
];

// ── Blog Categories ──
export const MOCK_BLOG_CATEGORIES: BlogCategory[] = [
  { id: 'bc1', slug: 'craft-stories', name: 'Craft Stories', status: 'active' },
  { id: 'bc2', slug: 'gift-guides', name: 'Gift Guides', status: 'active' },
  { id: 'bc3', slug: 'artisan-spotlight', name: 'Artisan Spotlight', status: 'active' },
  { id: 'bc4', slug: 'festival-culture', name: 'Festival & Culture', status: 'inactive' },
];

// ── Categories ──
export const MOCK_CATEGORIES: Category[] = [
  { id: 'cat-toys', slug: 'handcrafted-toys', name: 'Handcrafted Toys', description: 'Traditional wooden toys', image: 'https://picsum.photos/seed/cat1/400/300', productCount: 12, featured: true, status: 'active' },
  { id: 'cat-brass', slug: 'brass-items', name: 'Brass Items', description: 'Handcrafted brass decor', image: 'https://picsum.photos/seed/cat2/400/300', productCount: 8, featured: true, status: 'active' },
  { id: 'cat-decor', slug: 'home-decor', name: 'Home Decor', description: 'Handcrafted items for your home', image: 'https://picsum.photos/seed/cat3/400/300', productCount: 15, featured: false, status: 'active' },
  { id: 'cat-festive', slug: 'festive-items', name: 'Festive Items', description: 'Special items for festivals', image: 'https://picsum.photos/seed/cat4/400/300', productCount: 20, featured: true, status: 'active' },
  { id: 'cat-wallart', slug: 'wall-art', name: 'Wall Art', description: 'Hand-painted wall panels', image: 'https://picsum.photos/seed/cat5/400/300', productCount: 6, featured: false, status: 'active' },
  { id: 'cat-bronze', slug: 'bronze-items', name: 'Bronze Items', description: 'Traditional Chola bronze sculptures', image: 'https://picsum.photos/seed/cat6/400/300', productCount: 5, featured: false, status: 'active' },
  { id: 'cat-bags', slug: 'bags-accessories', name: 'Bags & Accessories', description: 'Handwoven bags', image: 'https://picsum.photos/seed/cat7/400/300', productCount: 9, featured: false, status: 'inactive' },
];

// ── Banners ──
export const MOCK_BANNERS: Banner[] = [
  { id: 'ban1', title: 'New Arrivals', subtitle: 'Fresh handcrafted treasures', ctaLabel: 'Shop Now', ctaLink: '/shop?sort=newest', image: 'https://picsum.photos/seed/ban1/1200/400', theme: 'dark', position: 'hero', sortOrder: 1, status: 'active' },
  { id: 'ban2', title: 'Festival Collection', subtitle: 'Celebrate with authentic crafts', ctaLabel: 'Explore', ctaLink: '/collections/festival', image: 'https://picsum.photos/seed/ban2/1200/400', theme: 'light', position: 'hero', sortOrder: 2, status: 'active' },
  { id: 'ban3', title: 'Gift Guide', subtitle: 'Find the perfect gift', ctaLabel: 'View Guide', ctaLink: '/collections/gifts', image: 'https://picsum.photos/seed/ban3/1200/400', theme: 'dark', position: 'promo', sortOrder: 1, status: 'active' },
  { id: 'ban4', title: 'Free Shipping', subtitle: 'On orders above 999', ctaLabel: 'Shop Now', ctaLink: '/shop', image: 'https://picsum.photos/seed/ban4/1200/400', theme: 'light', position: 'promo', sortOrder: 2, status: 'inactive' },
  { id: 'ban5', title: 'Diwali Special', subtitle: 'Coming soon', ctaLabel: 'Notify Me', ctaLink: '/shop', image: 'https://picsum.photos/seed/ban5/1200/400', theme: 'dark', position: 'sidebar', sortOrder: 1, status: 'draft', startDate: '2026-10-01', endDate: '2026-11-15' },
];

// ── Coupons ──
export const MOCK_COUPONS: Coupon[] = [
  // 1. Welcome discount — 10% off first order
  {
    code: 'WELCOME10', description: '10% off on your first order', type: 'first_order', status: 'active', active: true,
    discount: 0.1, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 500, maxDiscount: 200, maxOrder: 0,
    usageLimit: 500, usedCount: 45, perUserLimit: 1,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: true, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'NEW USER', bgColor: '#22c55e', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 10,
    totalDiscountGiven: 4500, totalOrdersAffected: 45, avgOrderValue: 1800,
  },
  // 2. Buy 1 Get 1 Free — on wooden toys
  {
    code: 'BOGO1FREE', description: 'Buy 1 Wooden Toy, Get 1 Free', type: 'buy_x_get_y', status: 'active', active: true,
    discount: 0, discountType: 'fixed',
    buyQuantity: 1, getQuantity: 1, getDiscount: 0,
    minOrder: 0, maxDiscount: 1500, maxOrder: 0,
    usageLimit: 200, usedCount: 34, perUserLimit: 2,
    validFrom: '2026-07-01', validUntil: '2026-08-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'categories', productIds: [], categoryIds: ['cat-toys'], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'BOGO', bgColor: '#f59e0b', textColor: '#000000', bannerImage: '',
    stackable: false, priority: 20,
    totalDiscountGiven: 23800, totalOrdersAffected: 34, avgOrderValue: 2200,
  },
  // 3. Buy 2 Get 50% off third — on brass items
  {
    code: 'BRASS3RD50', description: 'Buy 2 Brass Items, Get 3rd at 50% off', type: 'buy_x_get_percent', status: 'active', active: true,
    discount: 0.5, discountType: 'percentage',
    buyQuantity: 2, getQuantity: 1, getDiscount: 0.5,
    minOrder: 0, maxDiscount: 2500, maxOrder: 0,
    usageLimit: 100, usedCount: 18, perUserLimit: 3,
    validFrom: '2026-07-01', validUntil: '2026-09-30', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'categories', productIds: [], categoryIds: ['cat-brass'], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: '50% OFF 3RD', bgColor: '#8b5cf6', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 15,
    totalDiscountGiven: 12600, totalOrdersAffected: 18, avgOrderValue: 5400,
  },
  // 4. Flat ₹500 off — cart threshold
  {
    code: 'FLAT500', description: 'Flat ₹500 off on orders above ₹2999', type: 'cart_threshold', status: 'active', active: true,
    discount: 500, discountType: 'fixed',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 2999, maxDiscount: 500, maxOrder: 0,
    usageLimit: 300, usedCount: 67, perUserLimit: 3,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FLAT ₹500', bgColor: '#ef4444', textColor: '#ffffff', bannerImage: '',
    stackable: true, priority: 5,
    totalDiscountGiven: 33500, totalOrdersAffected: 67, avgOrderValue: 4200,
  },
  // 5. Tiered discount — spend more save more
  {
    code: 'TIERED20', description: 'Spend more, save more — tiered discounts', type: 'tiered', status: 'active', active: true,
    discount: 0.1, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 1000, maxDiscount: 2000, maxOrder: 0,
    usageLimit: 0, usedCount: 120, perUserLimit: 0,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [
      { minAmount: 1000, discount: 0.1, label: 'Spend ₹1000+ Get 10% off' },
      { minAmount: 3000, discount: 0.15, label: 'Spend ₹3000+ Get 15% off' },
      { minAmount: 5000, discount: 0.2, label: 'Spend ₹5000+ Get 20% off' },
    ],
    volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'SAVE MORE', bgColor: '#06b6d4', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 8,
    totalDiscountGiven: 48000, totalOrdersAffected: 120, avgOrderValue: 3500,
  },
  // 6. Free shipping — no minimum
  {
    code: 'FREESHIP', description: 'Free shipping on any order', type: 'free_shipping', status: 'active', active: true,
    discount: 0, discountType: 'fixed',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 0, maxDiscount: 0, maxOrder: 0,
    usageLimit: 0, usedCount: 234, perUserLimit: 0,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FREE SHIP', bgColor: '#22c55e', textColor: '#ffffff', bannerImage: '',
    stackable: true, priority: 2,
    totalDiscountGiven: 0, totalOrdersAffected: 234, avgOrderValue: 1800,
  },
  // 7. Flash sale — 24hr 30% off
  {
    code: 'FLASH30', description: '24-hour flash sale — 30% off everything', type: 'flash_sale', status: 'active', active: true,
    discount: 0.3, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 999, maxDiscount: 1500, maxOrder: 0,
    usageLimit: 100, usedCount: 67, perUserLimit: 1,
    validFrom: '2026-07-25', validUntil: '2026-07-26', validDays: [], validTimeFrom: '00:00', validTimeTo: '23:59',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FLASH SALE', bgColor: '#dc2626', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 50,
    totalDiscountGiven: 45600, totalOrdersAffected: 67, avgOrderValue: 2800,
  },
  // 8. Diwali festive — 20% off
  {
    code: 'DIWALI20', description: 'Diwali festive season — 20% off', type: 'seasonal', status: 'scheduled', active: false,
    discount: 0.2, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 1000, maxDiscount: 1000, maxOrder: 0,
    usageLimit: 500, usedCount: 0, perUserLimit: 2,
    validFrom: '2026-10-01', validUntil: '2026-11-15', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: true, seasonTag: 'diwali',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 2,
    badge: 'DIWALI', bgColor: '#f59e0b', textColor: '#000000', bannerImage: '',
    stackable: false, priority: 30,
    totalDiscountGiven: 0, totalOrdersAffected: 0, avgOrderValue: 0,
  },
  // 9. Referral reward
  {
    code: 'REFER500', description: 'Refer a friend — both get ₹500 off', type: 'referral', status: 'active', active: true,
    discount: 500, discountType: 'fixed',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 1999, maxDiscount: 500, maxOrder: 0,
    usageLimit: 0, usedCount: 28, perUserLimit: 10,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 500, refereeDiscount: 500, loyaltyPointsMultiplier: 1,
    badge: 'REFER & EARN', bgColor: '#10b981', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 12,
    totalDiscountGiven: 28000, totalOrdersAffected: 28, avgOrderValue: 3200,
  },
  // 10. Volume discount — buy 3+ get 15% off
  {
    code: 'BULK15', description: 'Buy 3+ items, get 15% off', type: 'volume', status: 'active', active: true,
    discount: 0.15, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 0, maxDiscount: 1000, maxOrder: 0,
    usageLimit: 0, usedCount: 42, perUserLimit: 0,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 3,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'BULK SAVE', bgColor: '#7c3aed', textColor: '#ffffff', bannerImage: '',
    stackable: true, priority: 6,
    totalDiscountGiven: 18900, totalOrdersAffected: 42, avgOrderValue: 4800,
  },
  // 11. Free gift with purchase
  {
    code: 'FREEGIFT', description: 'Buy any ₹2000+ order, get a free Brass Diya', type: 'free_gift', status: 'active', active: true,
    discount: 0, discountType: 'fixed',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 2000, maxDiscount: 0, maxOrder: 0,
    usageLimit: 100, usedCount: 23, perUserLimit: 1,
    validFrom: '2026-07-01', validUntil: '2026-08-15', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: 'p4', giftProductName: 'Brass Diya Set (5 pcs)', giftQuantity: 1,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'FREE GIFT', bgColor: '#ec4899', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 18,
    totalDiscountGiven: 0, totalOrdersAffected: 23, avgOrderValue: 3100,
  },
  // 12. Birthday special
  {
    code: 'BDAY20', description: '20% off on your birthday month', type: 'birthday', status: 'active', active: true,
    discount: 0.2, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 0, maxDiscount: 800, maxOrder: 0,
    usageLimit: 0, usedCount: 15, perUserLimit: 1,
    validFrom: '2026-01-01', validUntil: '2026-12-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 3,
    badge: 'HBD 🎂', bgColor: '#f472b6', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 25,
    totalDiscountGiven: 6000, totalOrdersAffected: 15, avgOrderValue: 2000,
  },
  // 13. Clearance — 40% off discontinued
  {
    code: 'CLEAR40', description: 'Clearance sale — 40% off selected items', type: 'clearance', status: 'active', active: true,
    discount: 0.4, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 0, maxDiscount: 2000, maxOrder: 0,
    usageLimit: 50, usedCount: 31, perUserLimit: 2,
    validFrom: '2026-07-01', validUntil: '2026-07-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'tags', productIds: [], categoryIds: [], collectionIds: [], tags: ['clearance', 'discontinued'],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 1,
    badge: 'CLEARANCE', bgColor: '#6b7280', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 35,
    totalDiscountGiven: 24600, totalOrdersAffected: 31, avgOrderValue: 1600,
  },
  // 14. Loyalty points multiplier
  {
    code: 'LOYAL2X', description: 'Double loyalty points on all purchases', type: 'loyalty', status: 'active', active: true,
    discount: 0, discountType: 'fixed',
    buyQuantity: 0, getQuantity: 0, getDiscount: 0,
    minOrder: 0, maxDiscount: 0, maxOrder: 0,
    usageLimit: 0, usedCount: 89, perUserLimit: 0,
    validFrom: '2026-07-01', validUntil: '2026-07-31', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all', productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: ['loyal', 'vip'], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 3, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 0,
    giftProductId: '', giftProductName: '', giftQuantity: 0,
    referralReward: 0, refereeDiscount: 0, loyaltyPointsMultiplier: 2,
    badge: '2X POINTS', bgColor: '#d97706', textColor: '#ffffff', bannerImage: '',
    stackable: true, priority: 4,
    totalDiscountGiven: 0, totalOrdersAffected: 89, avgOrderValue: 2600,
  },
];

// ── Collections ──
export const MOCK_COLLECTIONS: Collection[] = [
  { id: 'col-best-sellers', slug: 'best-sellers', name: 'Best Sellers', description: 'Most-loved pieces.', image: 'https://picsum.photos/seed/col1/800/600', productIds: ['p1', 'p2', 'p6'], status: 'active' },
  { id: 'col-new-arrivals', slug: 'new-arrivals', name: 'New Arrivals', description: 'Freshly added.', image: 'https://picsum.photos/seed/col2/800/600', productIds: ['p7', 'p8'], status: 'active' },
  { id: 'col-festive', slug: 'festive-collections', name: 'Festive Collections', description: 'Curated for Diwali.', image: 'https://picsum.photos/seed/col3/800/600', productIds: ['p4'], status: 'active' },
  { id: 'col-gifts', slug: 'gift-collections', name: 'Gift Collections', description: 'Thoughtfully packaged.', image: 'https://picsum.photos/seed/col4/800/600', productIds: ['p1', 'p3'], status: 'active' },
  { id: 'col-limited', slug: 'limited-edition', name: 'Limited Edition', description: 'Rare, small-batch.', image: 'https://picsum.photos/seed/col5/800/600', productIds: ['p7'], status: 'draft' },
];

// ── Tags ──
export const MOCK_TAGS: TagMeta[] = [
  { tag: 'traditional', label: 'Traditional', parentTag: null, status: 'active' },
  { tag: 'handmade', label: 'Handmade', parentTag: null, status: 'active' },
  { tag: 'gift', label: 'Gift', parentTag: null, status: 'active' },
  { tag: 'religious', label: 'Religious', parentTag: null, status: 'active' },
  { tag: 'brass', label: 'Brass', parentTag: null, status: 'active' },
  { tag: 'ganesha', label: 'Ganesha', parentTag: 'brass', status: 'active' },
  { tag: 'wooden', label: 'Wooden', parentTag: null, status: 'active' },
  { tag: 'festive', label: 'Festive', parentTag: null, status: 'active' },
  { tag: 'wall-art', label: 'Wall Art', parentTag: null, status: 'active' },
  { tag: 'bronze', label: 'Bronze', parentTag: null, status: 'active' },
  { tag: 'silk', label: 'Silk', parentTag: null, status: 'active' },
  { tag: 'eco', label: 'Eco-Friendly', parentTag: null, status: 'inactive' },
];

// ── Certifications ──
export const MOCK_CERTIFICATIONS: CertificationEntry[] = [
  { id: 'cert-001', title: 'GI Tag Certification', issuedBy: 'Government of India', certificateNumber: 'GI-210', date: '2013-08-05', description: 'Kondapalli Bommalu GI recognition.', image: 'https://picsum.photos/seed/cert1/900/700', imageSide: 'left', status: 'active' },
  { id: 'cert-002', title: 'MSME Udyam Registration', issuedBy: 'Ministry of MSME', certificateNumber: 'UDYAM-AP-XX', date: '2024-01-10', description: 'Registered MSME enterprise.', image: 'https://picsum.photos/seed/cert2/900/700', imageSide: 'right', status: 'active' },
  { id: 'cert-003', title: 'EPCH Membership', issuedBy: 'Export Promotion Council', date: '2024-03-18', description: 'Export council member.', image: 'https://picsum.photos/seed/cert3/900/700', imageSide: 'left', status: 'active' },
  { id: 'cert-004', title: 'GST Registration', issuedBy: 'GST Network', date: '2023-11-02', description: 'Fully GST registered.', image: 'https://picsum.photos/seed/cert4/900/700', imageSide: 'right', status: 'active' },
];

// ── Reviews ──
export const MOCK_REVIEWS: Review[] = [
  { id: 'rev-001', productId: 'p1', productName: 'Kondapalli Dancing Couple', productSlug: 'kondapalli-dancing-couple', productThumbnail: 'https://picsum.photos/seed/p1/400/400', author: 'Ananya R.', rating: 5, title: 'Stunning', comment: 'Incredible detailing.', date: '2026-05-12', verified: true, status: 'approved' },
  { id: 'rev-002', productId: 'p1', productName: 'Kondapalli Dancing Couple', productSlug: 'kondapalli-dancing-couple', productThumbnail: 'https://picsum.photos/seed/p1/400/400', author: 'Vikram S.', rating: 4, title: 'Beautiful', comment: 'Minor paint chip but happy.', date: '2026-04-02', verified: true, status: 'approved' },
  { id: 'rev-003', productId: 'p2', productName: 'Brass Ganesha Idol', productSlug: 'brass-ganesha-idol', productThumbnail: 'https://picsum.photos/seed/p2/400/400', author: 'Deepa S.', rating: 5, title: 'Gorgeous', comment: 'Even more beautiful in person.', date: '2026-05-30', verified: true, status: 'approved' },
  { id: 'rev-004', productId: 'p2', productName: 'Brass Ganesha Idol', productSlug: 'brass-ganesha-idol', productThumbnail: 'https://picsum.photos/seed/p2/400/400', author: 'Arjun N.', rating: 5, title: 'Exceeded expectations', comment: 'Heavy, well-cast.', date: '2026-02-14', verified: true, status: 'pending' },
  { id: 'rev-005', productId: 'p6', productName: 'Wooden Toy Elephant', productSlug: 'wooden-toy-elephant', productThumbnail: 'https://picsum.photos/seed/p6/400/400', author: 'Meera J.', rating: 5, title: 'Toddler loves these', comment: 'Safe, colorful.', date: '2026-06-01', verified: true, status: 'approved' },
  { id: 'rev-006', productId: 'p4', productName: 'Brass Diya Set', productSlug: 'brass-diya-set', productThumbnail: 'https://picsum.photos/seed/p4/400/400', author: 'Anita K.', rating: 4, title: 'Beautiful for Diwali', comment: 'Still look brand new.', date: '2026-06-05', verified: true, status: 'rejected' },
];

// ── Dashboard Stats ──
export const MOCK_DASHBOARD_STATS = {
  totalRevenue: 124580, totalOrders: 156, totalCustomers: 89, totalProducts: 45,
  revenueChange: 12.5, orderChange: 8.3, customerChange: 15.2, productChange: 4,
  recentOrders: MOCK_ORDERS,
  topProducts: MOCK_PRODUCTS.slice(0, 5).map(p => ({ product: p, sold: Math.floor(Math.random() * 50) + 10 })),
  ordersByStatus: [{ status: 'delivered', count: 98 }, { status: 'shipped', count: 25 }, { status: 'processing', count: 18 }, { status: 'placed', count: 10 }, { status: 'cancelled', count: 5 }],
  revenueByMonth: [{ month: 'Jan', revenue: 15200 }, { month: 'Feb', revenue: 18900 }, { month: 'Mar', revenue: 22100 }, { month: 'Apr', revenue: 19800 }, { month: 'May', revenue: 25600 }, { month: 'Jun', revenue: 23000 }],
};

// ── Admin Users ──
export const MOCK_ADMIN_USERS: AdminUser[] = [
  { id: 'a1', name: 'Super Admin', email: 'admin@royalheritagehub.com', role: 'super_admin', lastLogin: '2026-07-24 10:30 AM' },
  { id: 'a2', name: 'Store Manager', email: 'manager@royalheritagehub.com', role: 'admin', lastLogin: '2026-07-23 09:15 AM' },
  { id: 'a3', name: 'Content Editor', email: 'editor@royalheritagehub.com', role: 'editor', lastLogin: '2026-07-22 02:45 PM' },
];

// ── Footer ──
export const MOCK_FOOTER: FooterData = {
  quickLinks: [{ label: 'Shop', href: '/shop', icon: 'shop', status: 'active' }, { label: 'Categories', href: '/categories', icon: 'categories', status: 'active' }, { label: 'Collections', href: '/collections', icon: 'collections', status: 'active' }, { label: 'Blog', href: '/blog', icon: 'blog', status: 'active' }, { label: 'About', href: '/about', icon: 'about', status: 'active' }, { label: 'Contact', href: '/contact', icon: 'contact', status: 'active' }],
  policyLinks: [{ label: 'Shipping Policy', href: '/shipping-policy' }, { label: 'Return Policy', href: '/return-policy' }, { label: 'Privacy Policy', href: '/privacy-policy' }, { label: 'Terms & Conditions', href: '/terms' }, { label: 'FAQs', href: '/faqs' }],
  socialLinks: [{ platform: 'Instagram', url: 'https://instagram.com/royalheritagehub', icon: 'instagram' }, { platform: 'Facebook', url: 'https://facebook.com/royalheritagehub', icon: 'facebook' }, { platform: 'YouTube', url: 'https://youtube.com/@royalheritagehub', icon: 'youtube' }, { platform: 'WhatsApp', url: 'https://wa.me/917887699208', icon: 'whatsapp' }],
  paymentMethods: [{ name: 'UPI', icon: 'upi' }, { name: 'Visa', icon: 'visa' }, { name: 'Mastercard', icon: 'mastercard' }, { name: 'COD', icon: 'cod' }],
  trustBadges: [{ label: '100% Handcrafted', description: 'Every piece made by hand', icon: 'handcrafted' }, { label: 'GI Tag Certified', description: 'Geographical Indication protected', icon: 'certified' }, { label: 'Free Shipping', description: 'On orders above 999', icon: 'shipping' }, { label: 'Secure Payments', description: 'UPI, Cards & COD', icon: 'secure' }],
  certifications: [{ name: 'GI Tag', description: 'Geographical Indication' }, { name: 'MSME', description: 'Udyam Registered' }, { name: 'GST', description: 'Tax Compliant' }],
  workingHours: { label: 'Business Hours', days: 'Monday - Saturday', time: '10:00 AM - 6:00 PM IST', closed: 'Closed on Sundays & Public Holidays' },
};

// ── Store Settings ──
export const MOCK_SETTINGS: StoreSettings = {
  brand: { name: 'Royal Heritage Hub', tagline: 'Authentic Indian Handcrafted Treasures', shortName: 'RHH', logo: '/logo.svg', favicon: '/favicon.svg' },
  contact: { phone: '07887 699 208', email: 'royalheritagehub@gmail.com', address: 'India' },
  shipping: { freeShippingThreshold: 999, defaultShippingCharge: 79, codCharge: 10, estimatedDeliveryMin: 2, estimatedDeliveryMax: 7 },
  currency: { code: 'INR', symbol: '\u20B9', locale: 'en-IN' },
  social: { instagram: 'https://instagram.com/royalheritagehub', facebook: 'https://facebook.com/royalheritagehub', pinterest: 'https://pinterest.com/royalheritagehub', youtube: 'https://youtube.com/@royalheritagehub', whatsapp: 'https://wa.me/917887699208' },
};
