/**
 * Legacy constants — re-exported from SITE_CONFIG so existing imports keep working.
 * For new code, prefer importing SITE_CONFIG directly from '@/config/site'.
 */
import { SITE_CONFIG } from './site';

export const APP_NAME = SITE_CONFIG.brand.name;
export const APP_TAGLINE = SITE_CONFIG.brand.tagline;

export const CONTACT = {
  phone: SITE_CONFIG.contact.phone,
  phoneHref: SITE_CONFIG.contact.phoneHref,
  email: SITE_CONFIG.contact.email,
  emailHref: SITE_CONFIG.contact.emailHref,
};

export const COPYRIGHT_YEAR = SITE_CONFIG.copyrightYear;

export const SHIPPING = SITE_CONFIG.shipping;

export const CURRENCY = SITE_CONFIG.currency;

export const PAGINATION = {
  productsPerPage: 12,
  reviewsPerPage: 5,
};

export const SOCIAL_LINKS = SITE_CONFIG.social;
