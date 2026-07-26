/**
 * STORE CONFIGURATION — The ONLY file you need to change for a different business.
 *
 * This file controls EVERYTHING about the storefront:
 *   - Brand identity (name, logo, colors)
 *   - Business type (handcrafted goods, food, perfumes, etc.)
 *   - API endpoints and data sources
 *   - Theme colors and fonts
 *   - Currency and shipping
 *   - Social media links
 *
 * TO USE FOR A NEW BUSINESS:
 *   1. Copy this file
 *   2. Change the values below
 *   3. Replace the JSON data files in src/data/
 *   4. Update theme colors in src/theme/themes.ts
 *   5. Done — same UI, different business
 */

export interface StoreConfig {
  // ── Brand Identity ──
  brand: {
    name: string;
    tagline: string;
    shortName: string;
    logo: string;           // path to logo SVG/PNG
    favicon: string;        // path to favicon
    description: string;    // SEO meta description
  };

  // ── Domain & URLs ──
  domain: {
    baseUrl: string;        // e.g., "https://royalheritagehub.com"
    apiBase: string;        // e.g., "https://api.royalheritagehub.com/api"
    cdnUrl: string;         // e.g., "https://cdn.royalheritagehub.com"
    imageUrl: string;       // e.g., "https://images.royalheritagehub.com"
  };

  // ── Business Type ──
  business: {
    type: string;           // e.g., "handcrafted", "food", "perfume", "fashion"
    unit: { singular: string; plural: string };  // e.g., "piece"/"pieces" or "jar"/"jars"
    qualityWord: string;    // e.g., "Handcrafted", "Organic", "Artisanal"
    makerWord: string;      // e.g., "Artisan", "Chef", "Maker"
    craftWord: string;      // e.g., "Craft", "Recipe", "Blend"
  };

  // ── Terminology (optional) ──
  terminology?: {
    qualityAdjective: string;
    makerLabel: string;
    productProcessNoun: string;
    productUnitSingular: string;
    productUnitPlural: string;
    heritageWord: string;
    originWord: string;
    makersPlural: string;
    regionsWord: string;
    certificationWord: string;
    curatedWord: string;
  };

  // ── Theme Colors ──
  colors: {
    primary: string;        // main brand color
    primaryLight: string;
    primaryDark: string;
    secondary: string;      // accent color
    secondaryLight: string;
    secondaryDark: string;
    bg: string;             // page background
    bgLight: string;        // card/surface background
    bgCream: string;        // subtle tint background
    dark: string;           // text dark / dark sections
    textPrimary: string;
    textSecondary: string;
    textMuted: string;
  };

  // ── Currency & Shipping ──
  commerce: {
    currency: { code: string; symbol: string; locale: string };
    shipping: {
      freeThreshold: number;
      defaultCharge: number;
      codCharge: number;
      deliveryMin: number;
      deliveryMax: number;
    };
  };

  // ── Contact ──
  contact: {
    phone: string;
    phoneHref: string;
    email: string;
    emailHref: string;
    whatsapp: string;
    address?: string;
  };

  // ── Social Media ──
  social: {
    instagram: string;
    facebook: string;
    pinterest?: string;
    youtube?: string;
    twitter?: string;
  };

  // ── Navigation ──
  navigation: {
    topLinks: { label: string; href: string }[];
    footerQuickLinks: { label: string; href: string }[];
    footerPolicyLinks: { label: string; href: string }[];
  };

  // ── Page Content ──
  content: {
    heroEyebrow: string;
    aboutHeadline: string;
    aboutParagraphs: string[];
    whyChooseUs: { title: string; description: string; icon: string }[];
    testimonials: { name: string; rating: number; text: string }[];
    faqs: { q: string; a: string }[];
    announcementStrip: string;
    footerDescription: string;
    emptyCartDescription: string;
    emptyWishlistDescription: string;
    searchPlaceholder: string;
    newsletterDescription: string;
  };

  // ── Working Hours ──
  workingHours: {
    label: string;
    days: string;
    time: string;
    closed: string;
  };

  // ── Certifications ──
  certifications: { name: string; description: string }[];

  // ── Payment Methods ──
  paymentMethods: { name: string; icon: string }[];

  // ── SEO ──
  seo: {
    title: string;
    description: string;
    keywords: string[];
  };
}

// ══════════════════════════════════════════════════════════════
// DEFAULT CONFIG — Royal Heritage Hub
// Change this entire object for a different business
// ══════════════════════════════════════════════════════════════

export const STORE_CONFIG: StoreConfig = {
  brand: {
    name: 'Royal Heritage Hub',
    tagline: 'Authentic Indian Handcrafted Treasures',
    shortName: 'RHH',
    logo: '/logo.svg',
    favicon: '/favicon.svg',
    description: 'Authentic Indian handcrafted treasures — wooden toys, decor, and heritage crafts made by master artisans across India.',
  },

  domain: {
    baseUrl: 'https://royalheritagehub.com',
    apiBase: 'https://api.royalheritagehub.com/api',
    cdnUrl: '',
    imageUrl: '',
  },

  business: {
    type: 'handcrafted',
    unit: { singular: 'piece', plural: 'pieces' },
    qualityWord: 'Handcrafted',
    makerWord: 'Artisan',
    craftWord: 'Craft',
  },

  colors: {
    primary: '#8B4513',
    primaryLight: '#A0623A',
    primaryDark: '#6B3410',
    secondary: '#C89B3C',
    secondaryLight: '#D9B968',
    secondaryDark: '#A67F2C',
    bg: '#FAF8F3',
    bgLight: '#FFFDF9',
    bgCream: '#F5F0E6',
    dark: '#2D2A26',
    textPrimary: '#2D2A26',
    textSecondary: '#6B6259',
    textMuted: '#9C9186',
  },

  commerce: {
    currency: { code: 'INR', symbol: '₹', locale: 'en-IN' },
    shipping: {
      freeThreshold: 999,
      defaultCharge: 79,
      codCharge: 10,
      deliveryMin: 2,
      deliveryMax: 7,
    },
  },

  contact: {
    phone: '07887 699 208',
    phoneHref: 'tel:+447887699208',
    email: 'royalheritagehub@gmail.com',
    emailHref: 'mailto:royalheritagehub@gmail.com',
    whatsapp: 'https://wa.me/917887699208',
  },

  social: {
    instagram: 'https://instagram.com/royalheritagehub',
    facebook: 'https://facebook.com/royalheritagehub',
    pinterest: 'https://pinterest.com/royalheritagehub',
  },

  navigation: {
    topLinks: [
      { label: 'Shop', href: '/shop' },
      { label: 'Collections', href: '/collections' },
      { label: 'Artisans', href: '/artisans' },
      { label: 'Craft Regions', href: '/craft-regions' },
      { label: 'Reviews', href: '/reviews' },
      { label: 'About', href: '/about' },
      { label: 'Contact', href: '/contact' },
    ],
    footerQuickLinks: [
      { label: 'Shop All', href: '/shop' },
      { label: 'Categories', href: '/categories' },
      { label: 'Collections', href: '/collections' },
      { label: 'Browse by Tag', href: '/tags' },
      { label: 'Blog', href: '/blog' },
    ],
    footerPolicyLinks: [
      { label: 'Shipping Policy', href: '/shipping-policy' },
      { label: 'Return Policy', href: '/return-policy' },
      { label: 'Privacy Policy', href: '/privacy-policy' },
      { label: 'Terms & Conditions', href: '/terms' },
    ],
  },

  content: {
    heroEyebrow: 'Royal Heritage Hub',
    aboutHeadline: 'Four Hundred Years of Craft, Carried Forward by Hand',
    aboutParagraphs: [
      "In the village of Kondapalli, artisan families have shaped Tella Poniki softwood into figures of dance, devotion, and daily life for generations. Royal Heritage Hub was founded to bring this craft, and others like it from across India, directly into modern homes — without losing the hand of the maker in the process.",
      'Every piece we sell is sourced directly from artisan families, at fair prices, so the tradition has a reason to continue into the next generation.',
    ],
    whyChooseUs: [
      { title: '100% Handcrafted', description: 'Every piece is shaped, carved, and painted entirely by hand — no machine shortcuts.', icon: 'sparkle' },
      { title: 'Direct from Artisans', description: 'We work directly with craft villages, ensuring fair pay and preserved traditions.', icon: 'maker' },
      { title: 'Free Shipping', description: 'Complimentary shipping across India on all orders above ₹999.', icon: 'shipping' },
      { title: 'Secure Payments', description: 'UPI, cards, net banking, and Cash on Delivery — all fully secure.', icon: 'secure' },
    ],
    testimonials: [
      { name: 'Ananya R., Bengaluru', rating: 5, text: 'The Kondapalli dancing couple sits proudly in our living room now. The detail is unreal for something entirely hand-painted.' },
      { name: 'Vikram S., Mumbai', rating: 5, text: 'Ordered brass diyas for Diwali gifting — every single recipient asked where I bought them from. Beautiful packaging too.' },
      { name: 'Priya M., Delhi', rating: 4, text: 'Fast delivery, careful packaging, and the wooden temple looks even better in person than in photos.' },
    ],
    faqs: [
      { q: 'Are your products genuinely handmade?', a: 'Yes. Every product is hand-carved, hand-painted, or hand-cast by artisans we work with directly across India.' },
      { q: 'What are your shipping charges?', a: 'Shipping is free on all orders above ₹999. Below that, charges are calculated at checkout.' },
      { q: 'Do you offer Cash on Delivery?', a: 'Yes, COD is available across India with an additional charge of ₹10.' },
      { q: 'Can I return or exchange a product?', a: 'Yes, most items are eligible for return within our return window.' },
      { q: 'Do you offer bulk or corporate gifting?', a: 'Absolutely — we handle bulk corporate and wedding gifting with custom packaging.' },
    ],
    announcementStrip: 'Free shipping on orders above ₹999 · Handcrafted with heritage since generations',
    footerDescription: 'Authentic Indian handcrafted treasures — wooden toys, decor, and heritage crafts made by master artisans across India.',
    emptyCartDescription: 'Discover handcrafted pieces waiting to find a home.',
    emptyWishlistDescription: 'Save pieces you love and come back to them anytime.',
    searchPlaceholder: 'Search for wooden toys, decor, gifts...',
    newsletterDescription: 'Subscribe for early access to new collections, festive offers, and stories from our artisan partners.',
  },

  workingHours: {
    label: 'Working Hours',
    days: 'Mon – Sat',
    time: '10:00 AM – 6:00 PM',
    closed: 'Closed on Sundays & Public Holidays',
  },

  certifications: [
    { name: 'GI Tagged', description: 'Geographical Indication protected products' },
    { name: 'MSME Certified', description: 'Micro, Small & Medium Enterprise' },
    { name: 'EPCH Member', description: 'Export Promotion Council for Handicrafts' },
  ],

  paymentMethods: [
    { name: 'UPI', icon: 'upi' },
    { name: 'Visa', icon: 'visa' },
    { name: 'Mastercard', icon: 'mastercard' },
    { name: 'RuPay', icon: 'rupay' },
    { name: 'Net Banking', icon: 'netbanking' },
    { name: 'COD', icon: 'cod' },
  ],

  seo: {
    title: 'Royal Heritage Hub — Authentic Indian Handcrafted Treasures',
    description: 'Authentic Indian handcrafted treasures — wooden toys, decor, and heritage crafts made by master artisans across India.',
    keywords: ['handcrafted', 'indian crafts', 'wooden toys', 'heritage', 'artisan', 'home decor'],
  },
};
