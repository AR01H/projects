/**
 * TEXTS — All UI text in one place.
 * Import `texts` and use anywhere: texts.checkout.placeOrder
 * Change for a new business by overriding in store config.
 */

import { SITE_CONFIG } from './site';

const B = SITE_CONFIG.brand.name;
const Q = SITE_CONFIG.terminology.qualityAdjective;
const M = SITE_CONFIG.terminology.makerLabel;
const MP = SITE_CONFIG.terminology.makersPlural;
const U = SITE_CONFIG.terminology.productUnitSingular;
const UP = SITE_CONFIG.terminology.productUnitPlural;
const H = SITE_CONFIG.terminology.heritageWord;
const O = SITE_CONFIG.terminology.originWord;

export const texts = {
  // ── Common ──
  common: {
    search: 'Search',
    clearAll: 'Clear all',
    clearAllFilters: 'Clear all filters',
    apply: 'Apply',
    loadMore: (n: number) => `Load More (${n} remaining)`,
    showing: 'Showing',
    loading: 'Loading...',
    noResults: 'No results found.',
    readMore: 'Read more',
    showLess: 'Show less',
    minRead: 'min read',
    verified: 'Verified',
    viewAll: 'View all',
    backToHome: 'Back to Home',
    continueShopping: 'Continue Shopping',
    shopNow: 'Shop Now',
    exploreShop: 'Explore Shop',
    sendUsMessage: 'Send us a message',
    popularPages: 'Popular Pages',
    currency: 'Currency',
    free: 'Free',
  },

  // ── Shop Page ──
  shop: {
    title: 'All Products',
    filters: 'Filters',
    sortBy: 'Sort By',
    sort: 'Sort',
    filter: 'Filter',
    category: 'Category',
    allCategories: 'All Categories',
    tags: 'Tags',
    material: 'Material',
    priceRange: 'Price Range',
    inStockOnly: 'In Stock Only',
    inStock: 'In Stock',
    active: 'Active:',
    anyPrice: 'Any Price',
    maxPrice: 'Max Price',
    noProducts: 'No products match your filters.',
    all: 'All',
  },

  // ── Product Detail ──
  product: {
    notFound: 'Product not found',
    notFoundDesc: `This ${U} may have sold out or moved. Explore our full collection instead.`,
    addToBag: 'Add to Bag',
    buyNow: 'Buy Now',
    outOfStock: 'Out of Stock',
    bestSeller: 'Best Seller',
    newArrival: 'New Arrival',
    limitedEdition: 'Limited Edition',
    lowStock: (n: number) => `Only ${n} left in stock — order soon`,
    freeShipping: (threshold: number) => `Free shipping above ${SITE_CONFIG.currency.symbol}${threshold} · Estimated delivery in ${SITE_CONFIG.shipping.estimatedDeliveryMin}–${SITE_CONFIG.shipping.estimatedDeliveryMax} business days`,
    description: 'Description',
    specifications: 'Specifications',
    reviews: 'Reviews',
    shipping: 'Shipping',
    quality: 'Quality',
    noReviews: `No reviews yet — be the first to review this product.`,
    frequentlyBought: 'Frequently Bought Together',
    completeTheLook: 'Complete the Look',
    similarProducts: 'Similar Products',
    youMayAlsoLike: 'You May Also Like',
  },

  // ── Cart ──
  cart: {
    title: 'Shopping Bag',
    empty: 'Your bag is empty',
    subtotal: 'Subtotal',
    discount: 'Discount',
    shipping: 'Shipping',
    codCharge: 'COD Charge',
    total: 'Total',
    proceedToCheckout: 'Proceed to Checkout',
    couponPlaceholder: 'Coupon code',
    couponApplied: (code: string, pct: number) => `"${code}" — ${pct}% off applied`,
    couponInvalid: 'Invalid or expired coupon code',
    remove: 'Remove',
    freeShippingMsg: (n: number) => `Add ${formatCurrency(n)} more for free shipping`,
  },

  // ── Checkout ──
  checkout: {
    title: 'Checkout',
    empty: 'Your bag is empty',
    shippingAddress: 'Shipping Address',
    paymentMethod: 'Payment Method',
    orderNotes: 'Order Notes (Optional)',
    orderNotesPlaceholder: 'Gift message, delivery instructions...',
    orderSummary: 'Order Summary',
    placeOrder: 'Place Order',
    orderPlaced: 'Order Placed!',
    orderConfirmation: `${SITE_CONFIG.microcopy.orderConfirmationMessage} A confirmation has been sent to your email, and your order will arrive in ${SITE_CONFIG.shipping.estimatedDeliveryMin}–${SITE_CONFIG.shipping.estimatedDeliveryMax} business days.`,
    fullName: 'Full Name',
    phone: 'Phone Number',
    addressLine1: 'Address Line 1',
    addressLine2: 'Address Line 2 (Optional)',
    city: 'City',
    state: 'State',
    pincode: 'PIN Code',
    email: 'Email',
  },

  // ── Wishlist ──
  wishlist: {
    title: 'My Wishlist',
    empty: 'Your wishlist is empty',
    shareTitle: `My Wishlist — ${B}`,
    saved: (n: number) => `${n} ${n === 1 ? 'item' : 'items'} saved`,
    share: 'Share Wishlist',
    copied: 'Link Copied!',
  },

  // ── Contact ──
  contact: {
    title: 'Get in Touch',
    description: `Questions about an order, bulk gifting, or a custom ${U}? We'd love to hear from you.`,
    phone: 'Phone',
    email: 'Email',
    thankYou: "Thank you — we'll get back to you within 24 hours.",
    namePlaceholder: 'Your Name',
    emailPlaceholder: 'Your Email',
    messagePlaceholder: 'Your Message',
    sendMessage: 'Send Message',
  },

  // ── Reviews ──
  reviews: {
    heroTitle: 'What Our Customers Say',
    heroSubtitle: `See how our ${Q.toLowerCase()} ${UP} find a place in homes across ${O}.`,
    realStories: 'Real Stories',
    detailedReviews: 'Detailed Reviews',
    whatCustomersThink: 'What Customers Really Think',
    videoReviews: 'Video Reviews',
    watchDecide: 'Watch & Decide',
    customerGallery: 'Customer Gallery',
    howYouStyle: 'How You Style It',
    realConversations: 'Real Conversations',
    messagesHappy: 'Messages from Happy Customers',
    allReviews: 'All Reviews',
    everyVoice: 'Every Voice Matters',
    shareExperience: 'Share Your Experience',
    shareExperienceDesc: `Bought something from us? We'd love to hear about it — and see how you've styled it in your home.`,
    verifiedPurchase: 'Verified Purchase',
    filtered: (n: number) => `Filtered: ${n} star${n > 1 ? 's' : ''}`,
    noReviews: 'No reviews match this filter.',
    satisfaction: '98% Satisfaction',
    satisfactionDesc: 'Based on verified purchases',
    avgRating: '4.8 Average',
    avgRatingDesc: 'Across all products',
    fastReplies: 'Fast Replies',
    fastRepliesDesc: 'Within 2 hours on WhatsApp',
    reviews: (n: number) => `${n} reviews`,
  },

  // ── Categories ──
  categories: {
    title: 'All Categories',
    description: `Browse our full range of ${Q.toLowerCase()} ${O} ${UP} organised by ${SITE_CONFIG.terminology.productProcessNoun.toLowerCase()} tradition.`,
  },

  // ── Collections ──
  collections: {
    title: 'Collections',
    description: `Explore our curated collections of ${Q.toLowerCase()} ${O} art, furniture, ${UP}, and home decor.`,
  },

  // ── Tags ──
  tags: {
    title: 'Browse by Tag',
    description: `Explore our collection through the lens of what makes each ${U} unique.`,
    tagged: 'Tagged',
    narrowDown: 'Narrow down',
    relatedTags: 'Related tags',
  },

  // ── Blog ──
  blog: {
    title: 'The Journal',
    storiesGuides: 'Stories & Guides',
    description: `Discover ${Q.toLowerCase()} stories, ${M.toLowerCase()} insights, and guides from ${O}'s finest ${SITE_CONFIG.terminology.productProcessNoun.toLowerCase()} traditions.`,
    allPosts: 'All Posts',
    relatedReading: 'Related Reading',
    noPosts: 'No posts in this category yet.',
  },

  // ── FAQs ──
  faqs: {
    title: 'Frequently Asked Questions',
    helpCentre: 'Help Centre',
    description: `Everything you need to know about shopping with ${B}.`,
    searchPlaceholder: 'Search questions...',
    noMatching: 'No matching questions found.',
    clearSearch: 'Clear search',
    stillQuestions: 'Still Have Questions?',
    teamResponse: 'Our team typically responds within 2 hours during business hours.',
    callUs: 'Call Us',
    whatsappUs: 'WhatsApp Us',
    contactForm: 'Contact Form',
    categories: {
      orders: 'Orders & Shipping',
      returns: 'Returns & Exchanges',
      products: 'Products & Craft',
      gifting: 'Gifting & Corporate',
      payments: 'Payments',
    },
  },

  // ── About ──
  about: {
    ourStory: 'Our Story',
    missionValues: 'Our Mission & Values',
    heritageCraft: `Heritage ${H} Traditions`,
    villageToHome: 'From Village to Home',
    govtRecognised: 'Government Recognised',
    customerStories: 'What People Say',
    joinMovement: 'Join the Movement',
    joinDesc: `When you buy from ${B}, you're not just getting a ${U} — you're supporting the livelihood of ${M.toLowerCase()} families and keeping centuries-old traditions alive.`,
    preserves: `Every Purchase Preserves a ${H}`,
    craftHeritage: `${O}'s ${H} Craft`,
    craftDesc: `Each region of ${O} has cultivated its own distinct ${SITE_CONFIG.terminology.productProcessNoun.toLowerCase()} identity, passed down through generations.`,
    exploreCollection: 'Explore Our Collection',
    meetMakers: `Meet Our ${MP}`,
  },

  // ── Certifications ──
  certifications: {
    title: 'Government Certifications',
    verified: 'Verified & Compliant',
    description: `${B} operates in full compliance with ${O} government regulations and holds the following certifications.`,
    certified: 'Certified',
    certNo: 'Certificate No: ',
    issued: 'Issued: ',
  },

  // ── NotFound ──
  notFound: {
    title: 'Page Not Found',
    description: "The page you're looking for may have been moved or doesn't exist. Try searching for what you need, or explore our popular pages below.",
    searchPlaceholder: 'Search for products...',
  },

  // ── Shipping ──
  shipping: {
    freeAbove: (n: number) => `Orders above ${formatCurrency(n)} qualify for free shipping.`,
    delivery: (min: number, max: number) => `Estimated delivery time is ${min}–${max} business days.`,
    cod: (n: number) => `Cash on Delivery orders carry an additional charge of ${formatCurrency(n)}.`,
  },

  // ── Footer ──
  footer: {
    copyright: (year: number) => `© ${B} ${year}. All Rights Reserved.`,
    tagline: `Handcrafted with care by artisans across ${O} · Supporting traditional crafts since generations`,
  },

  // ── Product Rails (Homepage) ──
  rails: {
    newArrivals: { eyebrow: 'Just In', title: 'Newly Arrived', desc: SITE_CONFIG.microcopy.newArrivalsDescription },
    bestSellers: { eyebrow: 'Customer Favourites', title: 'Best Sellers', desc: 'The pieces our customers keep coming back for.' },
    limitedEdition: { eyebrow: 'Rare & Small-Batch', title: 'Limited Time Offers', desc: `Once these sell out, they won't be made again.` },
    handpicked: { eyebrow: "Editor's Picks", title: `${Q} Picks`, desc: `${UP.charAt(0).toUpperCase() + UP.slice(1)} our team can't stop recommending.` },
    festive: { eyebrow: 'Celebration Ready', title: 'Festival Collections', desc: `Curated for Diwali, weddings, and every celebration in between.` },
    gifts: { eyebrow: 'Thoughtfully Curated', title: 'Gift Collections', desc: 'Beautifully packaged gifts for every relationship.' },
    recommended: { eyebrow: 'Just for You', title: 'Recommended Products', desc: `${Q}-picked based on our top-rated ${UP}.` },
  },
};

function formatCurrency(n: number): string {
  return `${SITE_CONFIG.currency.symbol}${n.toLocaleString(SITE_CONFIG.currency.locale)}`;
}

export default texts;
