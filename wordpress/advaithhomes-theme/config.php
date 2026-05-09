<?php
/**
 * ADVAITH HOMES — Master Config File
 * =====================================
 * Edit ALL your site details here. These values flow into
 * SEO tags, contact info, social links, and structured data
 * automatically. You NEVER need to touch any other file.
 *
 * @package AdvaithHomes
 */

// ─────────────────────────────────────────────
// BRAND IDENTITY
// ─────────────────────────────────────────────
define('AH_SITE_NAME',     'Advaith Homes');
define('AH_TAGLINE',       'UK\'s Dedicated Buyer\'s Agent — We Work For You, Not The Seller');
define('AH_SITE_URL',      'https://advaithhomes.co.uk');
define('AH_LOGO_PATH',     get_template_directory_uri() . '/assets/logo.png');

// ─────────────────────────────────────────────
// CONTACT DETAILS
// ─────────────────────────────────────────────
define('AH_PHONE',         '+447887699208');
define('AH_WHATSAPP',      '447887699208');   // No + for wa.me links
define('AH_EMAIL',         'hello@advaithhomes.co.uk');
define('AH_ADDRESS',       'London, United Kingdom');

// ─────────────────────────────────────────────
// SOCIAL MEDIA
// ─────────────────────────────────────────────
define('AH_INSTAGRAM',     'https://instagram.com/advaithhomes');
define('AH_FACEBOOK',      'https://facebook.com/advaithhomes');
define('AH_LINKEDIN',      'https://linkedin.com/company/advaithhomes');

// ─────────────────────────────────────────────
// SEO METADATA (used in <head> and JSON-LD)
// ─────────────────────────────────────────────
define('AH_SEO_TITLE',     'Advaith Homes | UK\'s #1 Dedicated Buyer\'s Agent | Find Your Dream Home');
define('AH_SEO_DESC',      'Advaith Homes is the UK\'s leading buyer\'s agent. We work exclusively for you — not the seller — to find, negotiate, and secure your dream home while saving you thousands.');
define('AH_SEO_KEYWORDS',  'buyer\'s agent UK, property search UK, home buying expert, UK real estate agent, find a home UK, Advaith Homes, London property search, buyer\'s advocate');
define('AH_OG_IMAGE',      'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&h=630&fit=crop');
define('AH_TWITTER_IMAGE', 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&h=630&fit=crop');
define('AH_PRICE_RANGE',   '£££');
define('AH_CITY',          'London');

// ─────────────────────────────────────────────
// BRAND COLOURS (used in Customizer defaults)
// ─────────────────────────────────────────────
define('AH_COLOR_PRIMARY',  '#6d28d9');  // Purple accent
define('AH_COLOR_GOLD',     '#facc15');  // Gold highlight
define('AH_COLOR_DARK',     '#0f172a');  // Slate 900

// ─────────────────────────────────────────────
// HERO SECTION CONTENT
// ─────────────────────────────────────────────
define('AH_HERO_BADGE',        'Trusted UK Buyer\'s Agent');
define('AH_HERO_TITLE_LINE1',  'Find Your Dream Home');
define('AH_HERO_TITLE_LINE2',  'Without the Stress');
define('AH_HERO_DESC',         'We work exclusively for you — not the seller. Our expert agents find, vet and negotiate your perfect UK property.');
define('AH_HERO_CTA_PRIMARY',  'Free Consultation');
define('AH_HERO_CTA_SECONDARY','See Success Stories');

// ─────────────────────────────────────────────
// CONTACT FORM ADMIN SETTINGS
// ─────────────────────────────────────────────
define('AH_ADMIN_NOTIFY_EMAIL', AH_EMAIL);
define('AH_CONTACT_STATUSES', ['New', 'Called', 'In Progress', 'Not Interested', 'Converted']);
