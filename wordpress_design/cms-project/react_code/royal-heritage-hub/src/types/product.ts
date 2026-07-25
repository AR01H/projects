export interface ProductVariant {
  id: string;
  label: string;
  type: string; // e.g. 'size' | 'color' | 'flavor' | 'weight' — vertical-defined
  value: string;
  priceModifier?: number;
  inStock: boolean;
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

/** A single labelled fact about a product — e.g. {key:"material", label:"Material", value:"Mango Wood"}
 *  or {key:"spiceLevel", label:"Spice Level", value:"Medium"}. Fully vertical-agnostic. */
export interface ProductSpec {
  key: string;
  label: string;
  value: string;
  /** Show in the compact specifications summary shown in the info panel (vs. specs tab only) */
  highlight?: boolean;
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
  /** Optional product video URL (.mp4 or YouTube/Vimeo embed) */
  videoUrl?: string;
  thumbnail: string;
  sku: string;
  /** Dynamic, vertical-defined facts — replaces fixed material/origin/dimensions fields */
  specs: ProductSpec[];
  /** e.g. "Handmade", "Small-Batch", "Cold-Pressed", "Organic" — vertical-defined quality badges */
  qualityBadges: string[];
  /** e.g. "Ramesh Naidu" (artisan) or "Amma's Kitchen" (maker/brand) — generic maker credit */
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
  createdAt: string;
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
  /** slug of the parent category, if this is a subcategory. Omit for top-level categories. */
  parentSlug?: string;
}

export interface Collection {
  id: string;
  slug: string;
  name: string;
  description: string;
  image: string;
  productIds: string[];
}

export interface Banner {
  id: string;
  title: string;
  subtitle: string;
  ctaLabel: string;
  ctaLink: string;
  image: string;
  theme: 'light' | 'dark';
}

export interface BlogCategory {
  id: string;
  slug: string;
  name: string;
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
}

export interface CertificationEntry {
  id: string;
  title: string;
  issuedBy: string;
  certificateNumber?: string;
  date?: string;
  description: string;
  image: string;
  /** Which side the image sits on for this entry when in alternating layout */
  imageSide?: 'left' | 'right';
}
