<?php
/**
 * THE CANE HOUSE — Master Config File
 * =====================================
 * Edit ALL your site details here. These values flow into
 * SEO tags, contact info, social links, and structured data
 * automatically. You NEVER need to touch any other file.
 *
 * @package TheCanHouse
 */

// ─────────────────────────────────────────────
// BRAND IDENTITY
// ─────────────────────────────────────────────
define('TCH_SITE_NAME',     'The Cane House');
define('TCH_TAGLINE',       'UK\'s Freshest Live-Pressed Sugarcane Juice');
define('TCH_SITE_URL',      'https://thecanehouse.co.uk');
define('TCH_LOGO_PATH',     get_template_directory_uri() . '/assets/thecanehouselogo.png');

// ─────────────────────────────────────────────
// CONTACT DETAILS
// ─────────────────────────────────────────────
define('TCH_PHONE',         '+447887699208');
define('TCH_WHATSAPP',      '447887699208');   // No + for wa.me links
define('TCH_EMAIL',         'hello@thecanehouse.co.uk');
define('TCH_ADDRESS',       'Dunstable, Bedfordshire, UK');

// ─────────────────────────────────────────────
// SOCIAL MEDIA
// ─────────────────────────────────────────────
define('TCH_INSTAGRAM',     'https://instagram.com/thecanehouse');
define('TCH_FACEBOOK',      'https://facebook.com/thecanehouse');
define('TCH_TIKTOK',        'https://tiktok.com/@thecanehouse');

// ─────────────────────────────────────────────
// SEO METADATA (used in <head> and JSON-LD)
// ─────────────────────────────────────────────
define('TCH_SEO_TITLE',     'The Cane House | Fresh Live-Pressed Sugarcane Juice UK');
define('TCH_SEO_DESC',      'Experience the freshest live-pressed sugarcane juice in the UK. 100% natural, healthy, and refreshing. Book our live juice stall for weddings, corporate events, and private parties.');
define('TCH_SEO_KEYWORDS',  'sugarcane juice, fresh juice UK, live pressed cane juice, event catering, healthy drinks, The Cane House, sugarcane juice London, wedding juice bar');
define('TCH_OG_IMAGE',      'https://images.unsplash.com/photo-1546173159-315724a31696?w=1200&h=630&fit=crop');
define('TCH_TWITTER_IMAGE', 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=1200&h=630&fit=crop');

// ─────────────────────────────────────────────
// BRAND COLOURS (used in Customizer defaults)
// ─────────────────────────────────────────────
define('TCH_COLOR_PRIMARY',  '#2d5a1b');  // Deep green
define('TCH_COLOR_ACCENT',   '#c8e830');  // Lime
define('TCH_COLOR_TEXT',     '#1e3a0e');  // Dark green text

// ─────────────────────────────────────────────
// HERO SECTION CONTENT
// ─────────────────────────────────────────────
define('TCH_HERO_BADGE',       '100% Raw · Live Pressed · Naturally Chilled');
define('TCH_HERO_TITLE_LINE1', 'Fresh Live-Pressed');
define('TCH_HERO_TITLE_LINE2', 'Sugarcane Juice');
define('TCH_HERO_SUBTITLE',    'Pure · Natural · Refreshing');
define('TCH_HERO_DESC',        '100% Raw. Naturally Chilled. No Added Sugar. Just Pure Nature in a Cup.');
define('TCH_HERO_CTA_PRIMARY', 'Order on WhatsApp');
define('TCH_HERO_CTA_SECONDARY','Book an Event');

// ─────────────────────────────────────────────
// CONTACT FORM ADMIN SETTINGS
// ─────────────────────────────────────────────
define('TCH_ADMIN_NOTIFY_EMAIL', TCH_EMAIL); // Email that gets notified on new submissions
define('TCH_CONTACT_STATUSES', ['New', 'Called', 'In Progress', 'Not Interested', 'Converted']);
