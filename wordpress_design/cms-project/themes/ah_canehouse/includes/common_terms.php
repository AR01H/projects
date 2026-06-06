<?php
/**
 * The Cane House - Common Terms
 * Single source of truth for all brand labels, strings, and UI text.
 * Change here and it updates everywhere.
 */
defined( 'ABSPATH' ) || exit;


define('CONTACT_NUMBER', '+44 7887 699 208');
define('WHATASPP_CONTACT_NUMBER','+447887699208');
define('CONTACT_EMAIL','thecanehouseuk@gmail.com');
define('WEBSITE_URL','thecanehouse.co.uk');

// ── Brand ───────────────────────────────────────────────────────────────────
define( 'CH_BRAND_NAME',    'The Cane House' );
define( 'CH_BRAND_TAGLINE', 'Freshly Pressed. Naturally Refreshing' );
define( 'CH_BRAND_DESC',    'Fresh sugarcane juice pressed live, served cool. No added sugar, no preservatives - pure natural refreshment wherever you are.' );

// ── Nav Labels ───────────────────────────────────────────────────────────────
define( 'CH_NAV_HOME',      'Home' );
define( 'CH_NAV_HOW',       'How to Order' );
define( 'CH_NAV_JUICE',     'Our Juices' );
define( 'CH_NAV_EVENTS',    'Events & Hire' );
define( 'CH_NAV_FRANCHISE', 'Franchise' );
define( 'CH_NAV_FAQ',       'FAQ' );
define( 'CH_NAV_BLOG',      'Blog' );
define( 'CH_NAV_ABOUT',     'About' );
define( 'CH_NAV_SERVICES',  'Services' );
define( 'CH_NAV_CONTACT',   'Hire Us' );

// ── Section Tags ─────────────────────────────────────────────────────────────
define( 'CH_TAG_HOW',       'Simple & Easy' );
define( 'CH_TAG_REVIEWS',   'Happy Customers' );
define( 'CH_TAG_BUILD',     'Full Menu' );
define( 'CH_TAG_BENEFITS',  'Good for You' );
define( 'CH_TAG_STORY',     'Story of Sugarcane' );
define( 'CH_TAG_HIRE',      'Live Juice Stall Hire' );
define( 'CH_TAG_FRANCHISE', 'Grow With Us' );
define( 'CH_TAG_FAQ',       'Questions?' );
define( 'CH_TAG_CONTACT',   'Say Hello' );

// ── Section Headings ─────────────────────────────────────────────────────────
define( 'CH_H_HOW',         'How to Order' );
define( 'CH_H_REVIEWS',     'What Our Fans Say' );
define( 'CH_H_BUILD',       'Build Your Juice' );
define( 'CH_H_BENEFITS',    'Why Sugarcane Juice is Loved Worldwide' );
define( 'CH_H_HIRE',        'Bring Us to Your Event' );
define( 'CH_H_FRANCHISE',   'Franchise Opportunities' );
define( 'CH_H_FAQ',         'Common Queries' );
define( 'CH_H_CONTACT',     'Get in Touch' );

// ── CTA Labels ───────────────────────────────────────────────────────────────
define( 'CH_CTA_BUILD',     '🥤 Build Your Juice' );
define( 'CH_CTA_HIRE',      'Hire for Events →' );
define( 'CH_CTA_CONTACT',   'Get a Custom Quote →' );
define( 'CH_CTA_WHATSAPP',  'Chat with us! 🌿' );
define( 'CH_CTA_READ',      'Read Article →' );
define( 'CH_CTA_SEND',      'Send Message 🌿' );

// ── Footer ────────────────────────────────────────────────────────────────────
define( 'CH_FOOTER_COL1_TITLE', 'Our Juice' );
define( 'CH_FOOTER_COL2_TITLE', 'Services' );
define( 'CH_FOOTER_COL3_TITLE', 'Company' );

// ── Fallback default navigation (used when DB has no nav data) ────────────────
function ch_default_nav(): array {
	return [];
}

// ── Fallback default footer ───────────────────────────────────────────────────
function ch_default_footer_data( array $settings = [] ): array {
	$year = date( 'Y' );
	return [];
}
