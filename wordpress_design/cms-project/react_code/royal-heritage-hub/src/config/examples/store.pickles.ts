/**
 * EXAMPLE CONFIG — Pickle Paradise (food business)
 * Shows how to reconfigure the same UI for a different business.
 *
 * To use: import this as STORE_CONFIG in your entry point.
 */

import type { StoreConfig } from '../store-config';

export const PICKLE_CONFIG: StoreConfig = {
  brand: {
    name: 'Pickle Paradise',
    tagline: 'Authentic Homemade Pickles from Indian Kitchens',
    shortName: 'PP',
    logo: '/logo-pickles.svg',
    favicon: '/favicon-pickles.svg',
    description: 'Authentic homemade pickles made with traditional recipes, fresh ingredients, and no preservatives.',
  },

  domain: {
    baseUrl: 'https://pickleparadise.in',
    apiBase: 'https://api.pickleparadise.in/api',
    cdnUrl: '',
    imageUrl: '',
  },

  business: {
    type: 'food',
    unit: { singular: 'jar', plural: 'jars' },
    qualityWord: 'Homemade',
    makerWord: 'Chef',
    craftWord: 'Recipe',
  },

  colors: {
    primary: '#D32F2F',
    primaryLight: '#E57373',
    primaryDark: '#B71C1C',
    secondary: '#FF8F00',
    secondaryLight: '#FFB74D',
    secondaryDark: '#FF6F00',
    bg: '#FFF8F0',
    bgLight: '#FFFFFF',
    bgCream: '#FFF3E0',
    dark: '#3E2723',
    textPrimary: '#3E2723',
    textSecondary: '#6D4C41',
    textMuted: '#A1887F',
  },

  commerce: {
    currency: { code: 'INR', symbol: '₹', locale: 'en-IN' },
    shipping: {
      freeThreshold: 499,
      defaultCharge: 59,
      codCharge: 15,
      deliveryMin: 3,
      deliveryMax: 7,
    },
  },

  contact: {
    phone: '98765 43210',
    phoneHref: 'tel:+919876543210',
    email: 'hello@pickleparadise.in',
    emailHref: 'mailto:hello@pickleparadise.in',
    whatsapp: 'https://wa.me/919876543210',
  },

  social: {
    instagram: 'https://instagram.com/pickleparadise',
    facebook: 'https://facebook.com/pickleparadise',
    youtube: 'https://youtube.com/pickleparadise',
  },

  navigation: {
    topLinks: [
      { label: 'Shop', href: '/shop' },
      { label: 'Collections', href: '/collections' },
      { label: 'Recipes', href: '/blog' },
      { label: 'About', href: '/about' },
      { label: 'Contact', href: '/contact' },
    ],
    footerQuickLinks: [
      { label: 'All Pickles', href: '/shop' },
      { label: 'Mango Pickles', href: '/shop?category=mango' },
      { label: 'Chili Pickles', href: '/shop?category=chili' },
      { label: 'Gift Boxes', href: '/collections/gift-boxes' },
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
    heroEyebrow: 'Pickle Paradise',
    aboutHeadline: 'From Our Kitchen to Yours — Since 1985',
    aboutParagraphs: [
      'What started as Amma\'s kitchen recipes in a small Andhra village has grown into a beloved brand delivering authentic Indian pickles to doorsteps across the country.',
      'Every jar is made in small batches using traditional recipes, cold-pressed oils, and sun-dried spices — no preservatives, no shortcuts.',
    ],
    whyChooseUs: [
      { title: '100% Homemade', description: 'Made in small batches using traditional family recipes passed down through generations.', icon: 'sparkle' },
      { title: 'No Preservatives', description: 'We use only natural ingredients — mustard oil, salt, and sun-dried spices.', icon: 'maker' },
      { title: 'Free Shipping', description: 'Free delivery on orders above ₹499 across India.', icon: 'shipping' },
      { title: 'Secure Payments', description: 'UPI, cards, net banking, and COD — all fully secure.', icon: 'secure' },
    ],
    testimonials: [
      { name: 'Lakshmi R., Chennai', rating: 5, text: 'This mango pickle tastes exactly like my grandmother\'s. The spice level is perfect!' },
      { name: 'Rahul K., Delhi', rating: 5, text: 'Ordered the gift box for my mother-in-law. She loved every single jar. Will order again!' },
      { name: 'Priya S., Mumbai', rating: 4, text: 'Fresh, authentic, and well-packed. The garlic pickle is addictive!' },
    ],
    faqs: [
      { q: 'Are your pickles really homemade?', a: 'Yes! Every pickle is made in small batches using traditional recipes and natural ingredients.' },
      { q: 'How long do the pickles last?', a: 'Unopened jars last 9 months. Once opened, refrigerate and consume within 2 months.' },
      { q: 'Do you use preservatives?', a: 'No. We use only natural preservatives like salt, mustard oil, and vinegar.' },
      { q: 'Can I order in bulk for events?', a: 'Yes! We offer bulk orders for weddings, festivals, and corporate gifting.' },
    ],
    announcementStrip: 'Free shipping on orders above ₹499 · Made with love in Andhra Pradesh',
    footerDescription: 'Authentic homemade pickles made with traditional recipes from Indian kitchens.',
    emptyCartDescription: 'Your jar collection is empty. Let\'s fix that!',
    emptyWishlistDescription: 'Save your favourite pickles and come back to them anytime.',
    searchPlaceholder: 'Search for mango pickle, garlic pickle...',
    newsletterDescription: 'Subscribe for new flavour launches, recipes, and festive offers.',
  },

  workingHours: {
    label: 'Working Hours',
    days: 'Mon – Sat',
    time: '9:00 AM – 7:00 PM',
    closed: 'Closed on Sundays',
  },

  certifications: [
    { name: 'FSSAI Certified', description: 'Food Safety and Standards Authority of India' },
    { name: 'No Preservatives', description: '100% natural ingredients' },
    { name: 'GI Products', description: 'Geographical Indication protected recipes' },
  ],

  paymentMethods: [
    { name: 'UPI', icon: 'upi' },
    { name: 'Visa', icon: 'visa' },
    { name: 'Mastercard', icon: 'mastercard' },
    { name: 'COD', icon: 'cod' },
  ],

  seo: {
    title: 'Pickle Paradise — Authentic Homemade Pickles from Indian Kitchens',
    description: 'Authentic homemade pickles made with traditional recipes, fresh ingredients, and no preservatives.',
    keywords: ['homemade pickles', 'indian pickles', 'mango pickle', 'traditional recipes', 'no preservatives'],
  },
};
