<?php

// ===========================
// THEME CONSTANTS
// ===========================
define( 'ADN_THEME_NAME', 'advaithhomes_new' );
define( 'ADN_THEME_DIR', get_template_directory() );
define( 'ADN_THEME_URI', get_template_directory_uri() );
define( 'ADN_THEME_VERSION', '1.0.3' );

// Translation text domain. Must match the "Text Domain:" header in style.css
// (that header is parsed by WP and cannot read a constant). NOTE: using a
// constant here means standard .po/.mo extraction tools can't scan strings -
// fine for this theme because translations come from languages/*.php instead.
define( 'ADN_TEXT_DOMAIN', 'advaithhomes' );

define( 'PARENT_TERM','Guide');
define( 'SECTION_TERM','Category');
define( 'CONTENT_TERM','Article');

// ===========================
// POST META KEYS (used by API models)
// ===========================
define( 'ADN_META_REDIRECT',  'adn_redirect_url' );
define( 'ADN_META_CANONICAL', 'adn_canonical_url' );
define( 'ADN_META_ALT_URL',   'adn_alternate_url' );
define( 'ADN_META_ALT_TITLE', 'adn_alternate_title' );
define( 'ADN_META_ALT_SLUG',  'adn_alternate_slug' );

// ===========================
// SITE IDENTITY — change these to re-theme for a new industry/domain.
// Example swap: real estate → organic farming
// ===========================
define( 'SITE_BRAND_NAME',      'ADVAITH HOMES' );   // e.g. 'THE ORGANIC FARM'
define( 'SITE_BRAND_ICON',      '🏠' );              // e.g. '🌿'
define( 'SITE_INDUSTRY',        'UK Property' );     // e.g. 'Organic Farming'
define( 'SITE_LOCATION',        'UK' );              // e.g. 'Tamil Nadu'
define( 'SITE_DOMAIN_NOUN',     'Property' );        // e.g. 'Farm Produce'
define( 'SITE_EXPERT_NOUN',     'Property Expert' ); // e.g. 'Farm Advisor'
define( 'SITE_COPYRIGHT_YEAR',  '2025' );

// ===========================
// SITE URLS — used in JSON fallbacks, CTAs, sidebar links.
// ===========================
define( 'SITE_HOME_URL',        '/' );
define( 'SITE_GUIDES_URL',      '/guides/' );
define( 'SITE_NEWS_URL',        '/news/' );
define( 'SITE_CALCULATORS_URL', '/calculators/' );
define( 'SITE_EXPERT_URL',      '/ask-expert/' );
define( 'SITE_GUIDANCE_URL',    '/guidance/' );
define( 'SITE_CONTACT_URL',     '/contact/' );

// ===========================
// CTA COPY — default labels used when no DB option is set.
// ===========================
define( 'SITE_NAV_CTA_LABEL',       'Get Guidance' );
define( 'SITE_NAV_CTA_URL',         SITE_EXPERT_URL );
define( 'SITE_HERO_CTA_PRIMARY',    'Start Your Journey →' );
define( 'SITE_HERO_CTA_SECONDARY',  'Ask an Expert' );
define( 'SITE_NEWSLETTER_HEADING',  'Stay informed about ' . SITE_INDUSTRY );

// ===========================
// CONTENT LABELS — how the CMS taxonomy levels are named in UI copy.
// ===========================
// PARENT_TERM  already defined above = 'Guide'   (top-level journey bucket)
// SECTION_TERM already defined above = 'Category' (child topic)
// CONTENT_TERM already defined above = 'Article'  (individual post)
define( 'SITE_CONTENT_PLURAL',  'Guides' );  // e.g. 'Recipes', 'Products'
define( 'SITE_CATEGORY_PLURAL', 'Topics' );  // e.g. 'Categories', 'Sections'

// ===========================
// FEATURE NAMES — rename per site domain without touching page files.
// e.g. Calculators → Tools / Estimators / Remedies
//      News        → Blog / Updates / Harvest News
//      Ask Expert  → Ask a Farmer / Consult an Advisor
// ===========================
define( 'SITE_TOOLS_NOUN',     'Calculator' );        // singular
define( 'SITE_TOOLS_PLURAL',   'Calculators' );       // plural
define( 'SITE_NEWS_NOUN',      'News' );              // tag/category label, e.g. 'Blog', 'Update'
define( 'SITE_NEWS_LABEL',     'News & Insights' );   // page title, e.g. 'Farm Updates & Tips'
define( 'SITE_EXPERT_LABEL',   'Ask an Expert' );     // e.g. 'Ask a Farmer', 'Consult an Advisor'
define( 'SITE_GUIDANCE_LABEL', 'Get Expert Guidance' ); // e.g. 'Get Farm Guidance'
define( 'SITE_CONTACT_LABEL',  'Contact Us' );

// ===========================
// PAGE TITLES — used in adn_get_page_definitions() and breadcrumbs.
// ===========================
define( 'PAGE_TITLE_HOME',       'Home' );
define( 'PAGE_TITLE_CONTACT',    SITE_CONTACT_LABEL );
define( 'PAGE_TITLE_NEWS',       SITE_NEWS_LABEL );
define( 'PAGE_TITLE_GUIDES',     SITE_CONTENT_PLURAL );
define( 'PAGE_TITLE_TOOLS',      SITE_TOOLS_PLURAL );
define( 'PAGE_TITLE_EXPERT',     SITE_EXPERT_LABEL );
define( 'PAGE_TITLE_GUIDANCE',   SITE_GUIDANCE_LABEL );