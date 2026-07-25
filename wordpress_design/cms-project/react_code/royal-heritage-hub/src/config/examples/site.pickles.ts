/**
 * EXAMPLE: site.config for a gourmet pickle brand.
 * This demonstrates that swapping SITE_CONFIG (plus theme + JSON data)
 * is enough to re-purpose the entire storefront for a different vertical.
 *
 * To actually use this: copy this file's contents into src/config/site.ts,
 * and point src/data/*.json imports at src/data/examples/pickles/*.json
 * (or copy those files over the defaults).
 */
import type { SiteConfig } from '@/config/site';

export const PICKLE_SITE_CONFIG: SiteConfig = {
  brand: {
    name: "Lakshmi's Kitchen",
    tagline: 'Small-Batch Traditional Pickles',
    shortName: 'LK',
  },
  terminology: {
    qualityAdjective: 'Small-Batch',
    makerLabel: 'Maker',
    productProcessNoun: 'Recipe',
    productUnitSingular: 'jar',
    productUnitPlural: 'jars',
  },
  story: {
    heroEyebrow: "Lakshmi's Kitchen",
    aboutHeadline: 'Recipes Passed Down, Never Diluted',
    aboutParagraphs: [
      "Every jar starts with a recipe older than the brand itself — handed down through three generations of home cooks who never once considered adding a preservative. Lakshmi's Kitchen was founded to bring that same small-batch, sun-cured pickle-making tradition to kitchens far from home.",
      'Every batch is made in small quantities, sun-cured the traditional way, and packed the same week it\'s made — so what arrives at your door tastes like it just came out of the kitchen.',
    ],
    whyChooseUs: [
      { title: 'Small-Batch Made', description: 'Every jar is made in small quantities to protect flavor and quality — never mass-produced.', icon: 'sparkle' },
      { title: 'No Preservatives', description: 'Traditional sun-curing and cold-pressed oils mean nothing artificial, ever.', icon: 'maker' },
      { title: 'Free Shipping', description: 'Complimentary shipping across India on all orders above ₹999.', icon: 'shipping' },
      { title: 'Secure Payments', description: 'UPI, cards, net banking, and Cash on Delivery — all fully secure.', icon: 'secure' },
    ],
  },
  microcopy: {
    announcementStrip: 'Free shipping on orders above ₹999 · Small-batch, preservative-free pickles',
    footerDescription:
      'Traditional, small-batch pickles made with sun-cured produce and cold-pressed oils — no preservatives, no shortcuts.',
    emptyCartDescription: 'Your jar shelf is looking empty — add a pickle or two.',
    emptyWishlistDescription: 'Save your favorite jars and come back to them anytime.',
    searchPlaceholder: 'Search for mango, garlic, lemon pickles...',
    featuredCategoriesDescription: 'Each category is its own small-batch tradition, made the same way for generations.',
    shopByOccasionDescription: 'Find the perfect jar for every table and every gift.',
    newsletterDescription: 'Subscribe for early access to new batches, seasonal specials, and kitchen stories.',
    collectionsPageDescription: 'Curated jar sets, organised by flavor, heat level, and occasion.',
    newArrivalsDescription: 'Freshly jarred and just added to the shelf.',
    emptyCheckoutDescription: 'Add a jar or two before checking out.',
    orderConfirmationMessage: 'Thank you for supporting small-batch, traditional pickle-making.',
  },
  testimonials: [
    {
      name: 'Sindhu K., Hyderabad',
      rating: 5,
      text: "Tastes exactly like my grandmother's mango pickle. Genuinely can't tell the difference.",
    },
    {
      name: 'Rahul M., Bengaluru',
      rating: 5,
      text: 'The garlic pickle is dangerously good. Ordered three more jars within a week.',
    },
    {
      name: 'Meera D., Pune',
      rating: 4,
      text: 'Sent the gift jar set to my parents — they loved it, and the packaging was lovely.',
    },
  ],
  faqs: [
    {
      q: 'Are your pickles preservative-free?',
      a: 'Yes — every jar relies on traditional sun-curing, salt, and oil for preservation, with nothing artificial added.',
    },
    {
      q: 'What is the shelf life once opened?',
      a: 'Most jars last 9–12 months unopened. Once opened, refrigerate and use within 2–3 months, always with a dry spoon.',
    },
    {
      q: 'Do you offer Cash on Delivery?',
      a: 'Yes, COD is available across India with an additional charge of ₹10, shown clearly during checkout.',
    },
    {
      q: 'Can I customize the spice level?',
      a: 'Select jars offer mild, medium, and hot variants at checkout — check the product page for available options.',
    },
    {
      q: 'Do you offer bulk or corporate gifting?',
      a: 'Yes — we handle bulk festive and corporate gifting with custom jar labels. Reach out via our Contact page for a quote.',
    },
  ],
  contact: {
    phone: '07887 699 208',
    phoneHref: 'tel:+447887699208',
    email: 'hello@lakshmiskitchen.example',
    emailHref: 'mailto:hello@lakshmiskitchen.example',
  },
  copyrightYear: 2026,
  shipping: {
    freeShippingThreshold: 999,
    defaultShippingCharge: 79,
    codCharge: 10,
    estimatedDeliveryMin: 2,
    estimatedDeliveryMax: 7,
  },
  currency: {
    code: 'INR',
    symbol: '₹',
    locale: 'en-IN',
  },
  social: {
    instagram: 'https://instagram.com/lakshmiskitchen',
    facebook: 'https://facebook.com/lakshmiskitchen',
    pinterest: 'https://pinterest.com/lakshmiskitchen',
  },
};
