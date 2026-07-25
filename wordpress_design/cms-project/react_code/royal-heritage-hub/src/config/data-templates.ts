/**
 * Data Templates — Generate empty JSON templates for a new business.
 *
 * Run this to get blank templates you can fill in:
 *   npx tsx src/config/data-templates.ts
 *
 * Or just copy these structures into your JSON files.
 */

// ══════════════════════════════════════════════════════════════
// PRODUCT TEMPLATE
// ══════════════════════════════════════════════════════════════

export const PRODUCT_TEMPLATE = {
  id: "prod-001",
  slug: "your-product-slug",
  name: "Product Name",
  shortDescription: "Short description for cards and listings.",
  description: "Full detailed description of the product. Multiple sentences explaining features, materials, craftsmanship, etc.",
  categoryId: "cat-001",
  categorySlug: "your-category",
  collectionIds: ["col-001"],
  price: 999,
  compareAtPrice: null,                    // set to show strikethrough price
  currency: "INR",
  images: [
    "https://picsum.photos/seed/prod-001/900/900",
    "https://picsum.photos/seed/prod-001-alt/900/900"
  ],
  videoUrl: null,                           // YouTube, Vimeo, or .mp4 URL
  thumbnail: "https://picsum.photos/seed/prod-001/700/875",
  sku: "YOUR-SKU-001",
  specs: [
    { key: "material", label: "Material", value: "Your Material", highlight: true },
    { key: "dimensions", label: "Dimensions", value: "20cm x 15cm", highlight: true },
    { key: "weight", label: "Weight", value: "500g", highlight: false },
    { key: "origin", label: "Origin", value: "Your City, India", highlight: false }
  ],
  qualityBadges: ["Handmade"],             // e.g., ["Organic", "Vegan", "Small-Batch"]
  makerName: "Maker Name",                 // null if not applicable
  stock: 20,
  lowStockThreshold: 5,
  rating: 4.8,
  reviewCount: 15,
  reviews: [
    {
      id: "rev-001",
      author: "Customer Name",
      rating: 5,
      title: "Review title",
      comment: "Review comment text.",
      date: "2026-01-15",
      verified: true,
      photos: []
    }
  ],
  variants: [],                            // see VARIANT_TEMPLATE below
  tags: ["tag1", "tag2"],
  isBestSeller: false,
  isNewArrival: true,
  isFeatured: false,
  isLimitedEdition: false,
  isFestive: false,
  createdAt: "2026-01-15"
};

// ══════════════════════════════════════════════════════════════
// VARIANT TEMPLATE
// ══════════════════════════════════════════════════════════════

export const VARIANT_TEMPLATE = {
  id: "v-small",
  label: "Small",                          // display name
  type: "size",                            // "size" | "color" | "weight" | "set" | custom
  value: "Small",                          // the actual value
  priceModifier: -200,                     // price adjustment (0 = no change)
  inStock: true
};

// ══════════════════════════════════════════════════════════════
// CATEGORY TEMPLATE
// ══════════════════════════════════════════════════════════════

export const CATEGORY_TEMPLATE = {
  id: "cat-001",
  slug: "your-category",
  name: "Category Name",
  description: "Category description for the landing page.",
  image: "https://picsum.photos/seed/cat-001/600/400",
  icon: null,                              // optional emoji or icon name
  productCount: 0,                         // auto-calculated or manual
  featured: true,
  parentSlug: null                         // set to parent slug for subcategories
};

// ══════════════════════════════════════════════════════════════
// COLLECTION TEMPLATE
// ══════════════════════════════════════════════════════════════

export const COLLECTION_TEMPLATE = {
  id: "col-001",
  slug: "your-collection",
  name: "Collection Name",
  description: "Collection description.",
  image: "https://picsum.photos/seed/col-001/600/400",
  productIds: ["prod-001", "prod-002"]     // product IDs in this collection
};

// ══════════════════════════════════════════════════════════════
// TAG TEMPLATE
// ══════════════════════════════════════════════════════════════

export const TAG_TEMPLATE = {
  tag: "your-tag-slug",                    // URL-safe identifier
  label: "Tag Label",                      // display name
  parentTag: null                          // set to parent tag slug for hierarchy
};

// ══════════════════════════════════════════════════════════════
// BANNER TEMPLATE
// ══════════════════════════════════════════════════════════════

export const BANNER_TEMPLATE = {
  id: "banner-001",
  title: "Banner Title",
  subtitle: "Banner subtitle text",
  ctaLabel: "Shop Now",
  ctaLink: "/shop",
  image: "https://picsum.photos/seed/banner-001/1800/600",
  theme: "dark"                            // "light" | "dark"
};

// ══════════════════════════════════════════════════════════════
// BLOG POST TEMPLATE
// ══════════════════════════════════════════════════════════════

export const BLOG_POST_TEMPLATE = {
  id: "post-001",
  slug: "your-blog-post-slug",
  title: "Blog Post Title",
  excerpt: "Short excerpt for listing pages.",
  content: [
    "First paragraph of the blog post.",
    "Second paragraph with more detail.",
    "Third paragraph wrapping up."
  ],
  coverImage: "https://picsum.photos/seed/post-001/800/400",
  categorySlug: "your-blog-category",
  author: "Author Name",
  date: "2026-01-15",
  readMinutes: 5,
  tags: ["tag1", "tag2"]
};

// ══════════════════════════════════════════════════════════════
// BLOG CATEGORY TEMPLATE
// ══════════════════════════════════════════════════════════════

export const BLOG_CATEGORY_TEMPLATE = {
  id: "bcat-001",
  slug: "your-category",
  name: "Category Name"
};

// ══════════════════════════════════════════════════════════════
// CERTIFICATION TEMPLATE
// ══════════════════════════════════════════════════════════════

export const CERTIFICATION_TEMPLATE = {
  id: "cert-001",
  title: "Certification Name",
  issuedBy: "Issuing Authority",
  certificateNumber: "CERT-12345",
  date: "2025-06-15",
  description: "Description of the certification.",
  image: "https://picsum.photos/seed/cert-001/400/300",
  imageSide: "left"                        // "left" | "right" for alternating layout
};

// ══════════════════════════════════════════════════════════════
// FOOTER DATA TEMPLATE
// ══════════════════════════════════════════════════════════════

export const FOOTER_TEMPLATE = {
  trustBadges: [
    { label: "Quality Badge", description: "Description text", icon: "handcrafted" },
    { label: "Certified", description: "Description text", icon: "certified" },
    { label: "Fast Shipping", description: "Description text", icon: "shipping" },
    { label: "Secure", description: "Description text", icon: "secure" }
  ],
  quickLinks: [
    { label: "Shop All", href: "/shop" },
    { label: "Categories", href: "/categories" }
  ],
  policyLinks: [
    { label: "Shipping Policy", href: "/shipping-policy" },
    { label: "Return Policy", href: "/return-policy" },
    { label: "Privacy Policy", href: "/privacy-policy" },
    { label: "Terms & Conditions", href: "/terms" }
  ],
  socialLinks: [
    { platform: "Instagram", icon: "instagram", url: "https://instagram.com/yourbrand" },
    { platform: "Facebook", icon: "facebook", url: "https://facebook.com/yourbrand" }
  ],
  certifications: [
    { name: "Cert 1", description: "Description" }
  ],
  paymentMethods: [
    { name: "UPI", icon: "upi" },
    { name: "Visa", icon: "visa" },
    { name: "COD", icon: "cod" }
  ],
  workingHours: {
    label: "Working Hours",
    days: "Mon – Sat",
    time: "10:00 AM – 6:00 PM",
    closed: "Closed on Sundays"
  }
};
