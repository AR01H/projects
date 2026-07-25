/**
 * EXAMPLE: Perfume Palace — Luxury perfume store
 * Different brand, different colors, different everything.
 */

import type { StoreConfig } from '../store-config';

export const PERFUME_CONFIG: StoreConfig = {
  // ── Brand ──
  brand: {
    name: 'Perfume Palace',
    tagline: 'Artisan Fragrances from Grasse to India',
    shortName: 'PP',
    logo: '/logo.svg',
    favicon: '/favicon.svg',
    description: 'Luxury handcrafted perfumes made with natural essential oils and traditional distillation techniques.',
  },

  // ── Domain ──
  domain: {
    baseUrl: 'https://perfumepalace.com',
    apiBase: 'https://api.perfumepalace.com/api',
    cdnUrl: '',
    imageUrl: '',
  },

  // ── Business ──
  business: {
    type: 'perfume',
    unit: { singular: 'bottle', plural: 'bottles' },
    qualityWord: 'Artisanal',
    makerWord: 'Perfumer',
    craftWord: 'Blend',
  },

  // ── Terminology ──
  terminology: {
    qualityAdjective: 'Artisanal',
    makerLabel: 'Perfumer',
    productProcessNoun: 'Blend',
    productUnitSingular: 'bottle',
    productUnitPlural: 'bottles',
    heritageWord: 'Tradition',
    originWord: 'Indian',
    makersPlural: 'Perfumers',
    regionsWord: 'Origins',
    certificationWord: 'Natural',
    curatedWord: 'Selected',
  },

  // ── Colors (Purple & Gold theme) ──
  colors: {
    primary: '#4A148C',
    primaryLight: '#7B1FA2',
    primaryDark: '#311B92',
    secondary: '#D4AF37',
    secondaryLight: '#E8D48B',
    secondaryDark: '#B8960C',
    bg: '#FBF8FF',
    bgLight: '#FFFFFF',
    bgCream: '#F3EDF9',
    dark: '#1A0533',
    textPrimary: '#1A0533',
    textSecondary: '#5C4A72',
    textMuted: '#9E8FB0',
  },

  // ── Currency & Shipping ──
  commerce: {
    currency: { code: 'INR', symbol: '₹', locale: 'en-IN' },
    shipping: {
      freeThreshold: 1499,
      defaultCharge: 99,
      codCharge: 20,
      deliveryMin: 3,
      deliveryMax: 7,
    },
  },

  // ── Contact ──
  contact: {
    phone: '91234 56789',
    phoneHref: 'tel:+919123456789',
    email: 'hello@perfumepalace.com',
    emailHref: 'mailto:hello@perfumepalace.com',
    whatsapp: 'https://wa.me/919123456789',
  },

  // ── Social ──
  social: {
    instagram: 'https://instagram.com/perfumepalace',
    facebook: 'https://facebook.com/perfumepalace',
    pinterest: 'https://pinterest.com/perfumepalace',
    youtube: 'https://youtube.com/perfumepalace',
  },

  // ── Navigation ──
  navigation: {
    topLinks: [
      { label: 'Shop', href: '/shop' },
      { label: 'Collections', href: '/collections' },
      { label: 'Our Craft', href: '/about' },
      { label: 'Reviews', href: '/reviews' },
      { label: 'Contact', href: '/contact' },
    ],
    footerQuickLinks: [
      { label: 'All Perfumes', href: '/shop' },
      { label: 'For Her', href: '/shop?category=for-her' },
      { label: 'For Him', href: '/shop?category=for-him' },
      { label: 'Gift Sets', href: '/collections/gift-sets' },
      { label: 'Blog', href: '/blog' },
    ],
    footerPolicyLinks: [
      { label: 'Shipping Policy', href: '/shipping-policy' },
      { label: 'Return Policy', href: '/return-policy' },
      { label: 'Privacy Policy', href: '/privacy-policy' },
      { label: 'Terms & Conditions', href: '/terms' },
    ],
  },

  // ── Content ──
  content: {
    heroEyebrow: 'Perfume Palace',
    aboutHeadline: 'Where Ancient Distillation Meets Modern Elegance',
    aboutParagraphs: [
      'Founded in the fragrance capital of Kannauj, Perfume Palace brings centuries-old Indian attar-making traditions to the modern world.',
      'Each perfume is hand-distilled using the deg-bhapka method, preserving the true essence of natural flowers, herbs, and woods.',
    ],
    whyChooseUs: [
      { title: '100% Natural', description: 'Made with pure essential oils — no synthetic fragrances or alcohol.', icon: 'sparkle' },
      { title: 'Traditional Method', description: 'Hand-distilled using the centuries-old deg-bhapka technique.', icon: 'maker' },
      { title: 'Free Shipping', description: 'Complimentary delivery on orders above ₹1,499.', icon: 'shipping' },
      { title: 'Secure Payments', description: 'UPI, cards, net banking, and COD — all fully secure.', icon: 'secure' },
    ],
    testimonials: [
      { name: 'Meera S., Bengaluru', rating: 5, text: 'The mogra attar is absolutely divine. One drop lasts all day. Pure magic in a bottle.' },
      { name: 'Arjun K., Mumbai', rating: 5, text: 'Bought the sandalwood attar as a gift. The packaging was luxurious and the scent is intoxicating.' },
      { name: 'Priya R., Delhi', rating: 4, text: 'Beautiful handcrafted bottles. The rose attar smells like a garden in full bloom.' },
    ],
    faqs: [
      { q: 'Are your perfumes alcohol-free?', a: 'Yes! All our attars are traditional oil-based perfumes with no alcohol.' },
      { q: 'How long does the fragrance last?', a: 'Our attars last 8-12 hours on skin. They develop beautifully over time.' },
      { q: 'Can I try before buying?', a: 'We offer sample Discovery Kits with 5 mini attars for ₹999.' },
      { q: 'Do you gift wrap?', a: 'Yes! We offer premium gift packaging with handwritten notes.' },
      { q: 'Are these suitable for sensitive skin?', a: 'Yes, our attars are made with natural oils and are gentle on all skin types.' },
    ],
    announcementStrip: 'Free shipping above ₹1,499 · Handcrafted attars since 1952',
    footerDescription: 'Luxury handcrafted perfumes made with natural essential oils and traditional distillation from Kannauj, India.',
    emptyCartDescription: 'Your fragrance collection awaits.',
    emptyWishlistDescription: 'Save scents you love and return anytime.',
    searchPlaceholder: 'Search for attars, perfumes, gift sets...',
    newsletterDescription: 'Subscribe for new fragrance launches, perfumer stories, and exclusive offers.',
  },

  // ── Working Hours ──
  workingHours: {
    label: 'Store Hours',
    days: 'Mon – Sat',
    time: '11:00 AM – 8:00 PM',
    closed: 'Closed on Sundays',
  },

  // ── Certifications ──
  certifications: [
    { name: 'GI Tagged', description: 'Kannauj Attar — Geographical Indication protected' },
    { name: 'ISO Certified', description: 'Quality management system certified' },
    { name: 'Cruelty Free', description: 'No animal testing — vegan friendly' },
  ],

  // ── Payment Methods ──
  paymentMethods: [
    { name: 'UPI', icon: 'upi' },
    { name: 'Visa', icon: 'visa' },
    { name: 'Mastercard', icon: 'mastercard' },
    { name: 'Net Banking', icon: 'netbanking' },
    { name: 'COD', icon: 'cod' },
  ],

  // ── SEO ──
  seo: {
    title: 'Perfume Palace — Artisan Fragrances from Grasse to India',
    description: 'Luxury handcrafted perfumes made with natural essential oils and traditional distillation techniques.',
    keywords: ['attar', 'perfume', 'natural fragrance', 'handmade', 'kannauj', 'luxury'],
  },
};
