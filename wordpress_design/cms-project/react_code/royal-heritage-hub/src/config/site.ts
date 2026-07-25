/**
 * SITE CONFIG — the single file to edit when re-purposing this storefront
 * for a different vertical (handcrafted goods → pickles, perfumes, cookies, etc).
 *
 * Combined with:
 *   - src/theme/themes.ts   (colors, fonts, textures)
 *   - src/data/*.json        (products, categories, collections, banners)
 * ...this file is the ONLY place with vertical-specific COPY and TERMINOLOGY.
 * No component should hardcode brand language — always read from SITE_CONFIG.
 */

export interface SiteConfig {
  /** Brand identity */
  brand: {
    name: string;
    tagline: string;
    shortName: string; // used in tight spaces (mobile nav, favicons)
  };

  /** Terminology — the vocabulary this vertical uses instead of generic e-commerce terms */
  terminology: {
    /** e.g. "Handcrafted" / "Freshly Made" / "Cold-Pressed" */
    qualityAdjective: string;
    /** e.g. "Artisan" / "Maker" / "Chef" / "Perfumer" */
    makerLabel: string;
    /** e.g. "Craft" / "Recipe" / "Blend" */
    productProcessNoun: string;
    /** e.g. "piece" / "jar" / "bottle" / "batch" — used in "12 {unit}" copy */
    productUnitSingular: string;
    productUnitPlural: string;
    /** e.g. "Heritage" / "Tradition" / "Legacy" / "Art" */
    heritageWord: string;
    /** e.g. "Indian" / "Local" / "Regional" — origin descriptor */
    originWord: string;
    /** e.g. "Artisans" / "Makers" / "Craftspeople" / "Chefs" */
    makersPlural: string;
    /** e.g. "Regions" / "Origins" / "Sources" */
    regionsWord: string;
    /** e.g. "GI Tagged" / "Organic" / "Certified" */
    certificationWord: string;
    /** e.g. "Handpicked" / "Curated" / "Selected" */
    curatedWord: string;
  };

  /** Homepage hero fallback + brand story copy, used across Hero/About/Footer */
  story: {
    heroEyebrow: string;
    aboutHeadline: string;
    aboutParagraphs: string[];
    whyChooseUs: { title: string; description: string; icon: 'sparkle' | 'maker' | 'shipping' | 'secure' }[];
  };

  /** Small, reused snippets of UI copy that reference the vertical's language */
  microcopy: {
    /** Announcement strip in header, e.g. "Free shipping above ₹999 · Handcrafted with heritage" */
    announcementStrip: string;
    /** Footer brand description paragraph */
    footerDescription: string;
    /** Empty cart/wishlist description */
    emptyCartDescription: string;
    emptyWishlistDescription: string;
    /** Search placeholder in header */
    searchPlaceholder: string;
    featuredCategoriesDescription: string;
    shopByOccasionDescription: string;
    newsletterDescription: string;
    collectionsPageDescription: string;
    newArrivalsDescription: string;
    emptyCheckoutDescription: string;
    orderConfirmationMessage: string;
  };

  testimonials: { name: string; rating: number; text: string }[];

  faqs: { q: string; a: string }[];

  /** Contact + legal */
  contact: {
    phone: string;
    phoneHref: string;
    email: string;
    emailHref: string;
  };

  copyrightYear: number;

  shipping: {
    freeShippingThreshold: number;
    defaultShippingCharge: number;
    codCharge: number;
    estimatedDeliveryMin: number;
    estimatedDeliveryMax: number;
  };

  currency: {
    code: string;
    symbol: string;
    locale: string;
  };

  social: {
    instagram: string;
    facebook: string;
    pinterest: string;
  };
}

export const SITE_CONFIG: SiteConfig = {
  brand: {
    name: 'Royal Heritage Hub',
    tagline: 'Authentic Indian Handcrafted Treasures',
    shortName: 'RHH',
  },
  terminology: {
    qualityAdjective: 'Handcrafted',
    makerLabel: 'Artisan',
    productProcessNoun: 'Craft',
    productUnitSingular: 'piece',
    productUnitPlural: 'pieces',
    heritageWord: 'Heritage',
    originWord: 'Indian',
    makersPlural: 'Artisans',
    regionsWord: 'Regions',
    certificationWord: 'GI Tagged',
    curatedWord: 'Handpicked',
  },
  story: {
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
  },
  microcopy: {
    announcementStrip: 'Free shipping on orders above ₹999 · Handcrafted with heritage since generations',
    footerDescription:
      'Authentic Indian handcrafted treasures — wooden toys, decor, and heritage crafts made by master artisans across India.',
    emptyCartDescription: 'Discover handcrafted pieces waiting to find a home.',
    emptyWishlistDescription: 'Save pieces you love and come back to them anytime.',
    searchPlaceholder: 'Search for wooden toys, decor, gifts...',
    featuredCategoriesDescription: 'Each category tells the story of a distinct craft tradition and the artisans who keep it alive.',
    shopByOccasionDescription: 'Find the perfect handcrafted piece for every moment worth celebrating.',
    newsletterDescription:
      "Subscribe for early access to new collections, festive offers, and stories from our artisan partners.",
    collectionsPageDescription: 'Curated groupings of our handcrafted pieces, organised by occasion, style, and season.',
    newArrivalsDescription: "Fresh from the artisan's workbench this season.",
    emptyCheckoutDescription: 'Add some handcrafted pieces before checking out.',
    orderConfirmationMessage: 'Thank you for supporting Indian craftsmanship.',
  },
  testimonials: [
    {
      name: 'Ananya R., Bengaluru',
      rating: 5,
      text: 'The Kondapalli dancing couple sits proudly in our living room now. The detail is unreal for something entirely hand-painted.',
    },
    {
      name: 'Vikram S., Mumbai',
      rating: 5,
      text: 'Ordered brass diyas for Diwali gifting — every single recipient asked where I bought them from. Beautiful packaging too.',
    },
    {
      name: 'Priya M., Delhi',
      rating: 4,
      text: 'Fast delivery, careful packaging, and the wooden temple looks even better in person than in photos.',
    },
  ],
  faqs: [
    {
      q: 'Are your products genuinely handmade?',
      a: 'Yes. Every product is hand-carved, hand-painted, or hand-cast by artisans we work with directly across India. Minor variations in color and finish are part of the handmade character, not a defect.',
    },
    {
      q: 'What are your shipping charges?',
      a: 'Shipping is free on all orders above ₹999. Below that, charges are calculated at checkout based on your location. Estimated delivery is 2–7 business days.',
    },
    {
      q: 'Do you offer Cash on Delivery?',
      a: 'Yes, COD is available across India with an additional charge of ₹10, shown clearly during checkout.',
    },
    {
      q: 'Can I return or exchange a product?',
      a: 'Yes, most items are eligible for return within our return window. Please see our Return Policy page for full details and exceptions for customised items.',
    },
    {
      q: 'Do you offer bulk or corporate gifting?',
      a: 'Absolutely — we handle bulk corporate and wedding gifting with custom packaging and personalised gift messages. Reach out via our Contact page for a quote.',
    },
  ],
  contact: {
    phone: '07887 699 208',
    phoneHref: 'tel:+447887699208',
    email: 'royalheritagehub@gmail.com',
    emailHref: 'mailto:royalheritagehub@gmail.com',
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
    instagram: 'https://instagram.com/royalheritagehub',
    facebook: 'https://facebook.com/royalheritagehub',
    pinterest: 'https://pinterest.com/royalheritagehub',
  },
};
