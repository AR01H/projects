/**
 * NAVIGATION TREE — builds 4-level hierarchy from categories + tags + collections.
 * Single source of truth for mega menu and mobile nav.
 */
import { MOCK_CATEGORIES, MOCK_TAGS, MOCK_COLLECTIONS } from '@/data/mockData';
import { ROUTES } from './routes';
import { buildRoute } from './routes';

export interface NavItem {
  label: string;
  href: string;
  image?: string;
  children?: NavItem[];
}

/** Map of category slug → relevant tag labels (Level 4) */
const CATEGORY_TAG_MAP: Record<string, string[]> = {
  'kondapalli-bommalu': ['Figurine', 'GI Tagged', 'Hand-Painted'],
  'village-sets': ['Miniature', 'Hand-Painted'],
  'temple-decor': ['Mandir', 'Pooja', 'Jaali Work'],
  'wall-decor': ['Peacock Motif', 'Hand-Painted', 'Wall Art'],
  'jewellery-boxes': ['Brass Inlay', 'Gift'],
  'kitchen-decor': ['Serving Tray', 'Hand-Painted'],
  'brass-decor': ['Ganesha', 'Diya', 'Lost Wax'],
  'gift-items': ['Corporate Gift', 'Personalised', 'Festive'],
};

function buildLevel4Tags(categorySlug: string): NavItem[] {
  const tagLabels = CATEGORY_TAG_MAP[categorySlug] || [];
  return tagLabels
    .map((label) => {
      const tag = MOCK_TAGS.find((t) => t.label === label);
      if (!tag) return null;
      return {
        label: tag.label,
        href: `${ROUTES.shop}?tag=${encodeURIComponent(tag.tag)}`,
      };
    })
    .filter(Boolean) as NavItem[];
}

function buildLevel3Subcategories(parentSlug: string): NavItem[] {
  return MOCK_CATEGORIES
    .filter((c) => c.parentSlug === parentSlug)
    .map((c) => ({
      label: c.name,
      href: buildRoute(ROUTES.category, { categorySlug: c.slug }),
      image: c.image,
      children: buildLevel4Tags(c.slug),
    }));
}

function buildLevel2Categories(): NavItem[] {
  const topLevel = MOCK_CATEGORIES.filter((c) => !c.parentSlug);
  return topLevel.map((c) => ({
    label: c.name,
    href: buildRoute(ROUTES.category, { categorySlug: c.slug }),
    image: c.image,
    children: buildLevel3Subcategories(c.slug),
  }));
}

function buildCollectionsMenu(): NavItem[] {
  return MOCK_COLLECTIONS.map((col) => ({
    label: col.name,
    href: buildRoute(ROUTES.collection, { collectionSlug: col.slug }),
    image: col.image,
  }));
}

export interface MegaMenuData {
  shop: NavItem[];
  collections: NavItem[];
}

export const MEGA_MENU_DATA: MegaMenuData = {
  shop: [
    { label: 'All Categories', href: ROUTES.categories },
    ...buildLevel2Categories(),
  ],
  collections: buildCollectionsMenu(),
};

export const TOP_NAV_LINKS: NavItem[] = [
  { label: 'Shop', href: ROUTES.shop, children: MEGA_MENU_DATA.shop },
  { label: 'Collections', href: ROUTES.collections, children: MEGA_MENU_DATA.collections },
  { label: 'Artisans', href: ROUTES.artisans },
  { label: 'Craft Regions', href: ROUTES.craftRegions },
  { label: 'Reviews', href: ROUTES.reviews },
  { label: 'About', href: ROUTES.about },
  { label: 'Contact', href: ROUTES.contact },
];
