<?php

// ===========================
// LOAD SITE TERMINOLOGY FROM JSON
// All client/industry-specific labels live in data/json/terms.json.
// To switch this theme to a different website (e.g. organic shop, law firm),
// edit that file only – no PHP changes needed here.
// ===========================
$_adn_terms = [];
$_adn_custom = ( defined( 'DATA_FILES' ) && DATA_FILES ) ? trim( DATA_FILES, '/' ) . '/' : '';

$_adn_terms_path_candidates = array(
    get_template_directory() . '/data/' . $_adn_custom . 'json/terms.json',
);
foreach ( $_adn_terms_path_candidates as $_adn_terms_path ) {
    if ( file_exists( $_adn_terms_path ) ) {
        $_adn_raw = file_get_contents( $_adn_terms_path );
        $_adn_decoded = json_decode( $_adn_raw, true );
        if ( is_array( $_adn_decoded ) ) {
            $_adn_terms = $_adn_decoded;
            break;
        }
    }
}

/**
 * Helper: read a value from the terms array using dot notation.
 * Falls back to $default if the key is missing.
 *
 * @param string $key      e.g. 'brand.name'
 * @param string $default  Fallback value.
 * @return string
 */
function adn_term( $key, $default = '' ) {
    global $_adn_terms;
    $parts = explode( '.', $key );
    $value = $_adn_terms;
    foreach ( $parts as $part ) {
        if ( is_array( $value ) && array_key_exists( $part, $value ) ) {
            $value = $value[ $part ];
        } else {
            return $default;
        }
    }
    return is_string( $value ) ? $value : $default;
}

// ===========================
// THEME CONSTANTS
// These are defined from terms.json so a single JSON edit re-themes the site.
// ===========================
define( 'ADN_THEME_NAME',    'advaithhomes_new' );
define( 'ADN_THEME_DIR',     get_template_directory() );
define( 'ADN_THEME_URI',     get_template_directory_uri() );
define( 'ADN_THEME_VERSION', '1.0.3' );

// Translation text domain. Must match the "Text Domain:" header in style.css.
define( 'ADN_TEXT_DOMAIN', 'advaithhomes' );

// ===========================
// TAXONOMY TERMS
// ===========================
define( 'PARENT_TERM',  adn_term( 'taxonomy.parent',  'Guide' ) );
define( 'SECTION_TERM', adn_term( 'taxonomy.section', 'Category' ) );
define( 'CONTENT_TERM', adn_term( 'taxonomy.content', 'Article' ) );

// ===========================
// POST META KEYS (used by API models – not site-specific)
// ===========================
define( 'ADN_META_REDIRECT',  'adn_redirect_url' );
define( 'ADN_META_CANONICAL', 'adn_canonical_url' );
define( 'ADN_META_ALT_URL',   'adn_alternate_url' );
define( 'ADN_META_ALT_TITLE', 'adn_alternate_title' );
define( 'ADN_META_ALT_SLUG',  'adn_alternate_slug' );

// ===========================
// SITE IDENTITY
// ===========================
define( 'SITE_BRAND_NAME',     adn_term( 'brand.name',        'MY SITE' ) );
define( 'SITE_BRAND_ICON',     adn_term( 'brand.icon',        '🏠' ) );
define( 'SITE_INDUSTRY',       adn_term( 'brand.industry',    'Industry' ) );
define( 'SITE_LOCATION',       adn_term( 'brand.location',    'UK' ) );
define( 'SITE_DOMAIN_NOUN',    adn_term( 'brand.domain_noun', 'Industry' ) );
define( 'SITE_EXPERT_NOUN',    adn_term( 'brand.expert_noun', 'Expert' ) );
define( 'SITE_COPYRIGHT_YEAR', adn_term( 'brand.copyright_year', date( 'Y' ) ) );

// ===========================
// SITE URLS
// ===========================
define( 'SITE_HOME_URL',        adn_term( 'urls.home',        '/' ) );
define( 'SITE_GUIDES_URL',      adn_term( 'urls.guides',      '/guides/' ) );
define( 'SITE_NEWS_URL',        adn_term( 'urls.news',        '/news/' ) );
define( 'SITE_TOOLS_URL',       adn_term( 'urls.tools',       '/calculators/' ) );
define( 'SITE_CALCULATORS_URL', SITE_TOOLS_URL ); // backward-compat alias
define( 'SITE_EXPERT_URL',      adn_term( 'urls.expert',      '/ask-expert/' ) );
define( 'SITE_GUIDANCE_URL',    adn_term( 'urls.guidance',    '/guidance/' ) );
define( 'SITE_CONTACT_URL',     adn_term( 'urls.contact',     '/contact/' ) );
define( 'SITE_FAQS_URL',        adn_term( 'urls.faqs',        '/faqs/' ) );

// ===========================
// CTA COPY
// ===========================
define( 'SITE_NAV_CTA_LABEL',      adn_term( 'cta.nav_label',        'Get Guidance' ) );
define( 'SITE_NAV_CTA_URL',        SITE_EXPERT_URL );
define( 'SITE_HERO_CTA_PRIMARY',   adn_term( 'cta.hero_primary',     'Get Started →' ) );
define( 'SITE_HERO_CTA_SECONDARY', adn_term( 'cta.hero_secondary',   'Ask an Expert' ) );
define( 'SITE_NEWSLETTER_HEADING', adn_term( 'cta.newsletter_heading', 'Stay informed about ' . SITE_INDUSTRY ) );

// ===========================
// CONTENT LABELS
// ===========================
define( 'SITE_CONTENT_PLURAL',  adn_term( 'taxonomy.parent_plural',  'Guides' ) );
define( 'SITE_CATEGORY_PLURAL', adn_term( 'taxonomy.section_plural', 'Topics' ) );

// ===========================
// FEATURE NAMES
// ===========================
define( 'SITE_TOOLS_NOUN',     adn_term( 'features.tools_noun',     'Calculator' ) );
define( 'SITE_TOOLS_PLURAL',   adn_term( 'features.tools_plural',   'Calculators' ) );
define( 'SITE_NEWS_NOUN',      adn_term( 'features.news_noun',      'News' ) );
define( 'SITE_NEWS_LABEL',     adn_term( 'features.news_label',     'News & Insights' ) );
define( 'SITE_EXPERT_LABEL',   adn_term( 'features.expert_label',   'Ask an Expert' ) );
define( 'SITE_GUIDANCE_LABEL', adn_term( 'features.guidance_label', 'Get Expert Guidance' ) );
define( 'SITE_CONTACT_LABEL',  adn_term( 'features.contact_label',  'Contact Us' ) );

// ===========================
// PAGE TITLES
// ===========================
define( 'PAGE_TITLE_HOME',     adn_term( 'page_titles.home',     'Home' ) );
define( 'PAGE_TITLE_CONTACT',  adn_term( 'page_titles.contact',  SITE_CONTACT_LABEL ) );
define( 'PAGE_TITLE_FAQS',     adn_term( 'page_titles.faqs',     'FAQs' ) );
define( 'PAGE_TITLE_NEWS',     adn_term( 'page_titles.news',     SITE_NEWS_LABEL ) );
define( 'PAGE_TITLE_GUIDES',   adn_term( 'page_titles.guides',   SITE_CONTENT_PLURAL ) );
define( 'PAGE_TITLE_TOOLS',    adn_term( 'page_titles.tools',    SITE_TOOLS_PLURAL ) );
define( 'PAGE_TITLE_EXPERT',   adn_term( 'page_titles.expert',   SITE_EXPERT_LABEL ) );
define( 'PAGE_TITLE_GUIDANCE', adn_term( 'page_titles.guidance', SITE_GUIDANCE_LABEL ) );

// ===========================
// PLUGIN TERM INTEGRATION
// Hook into the CMS plugin's terminology filter and supply this theme's
// terms from data/json/terms.json. The plugin knows nothing about the theme.
// ===========================
add_filter( 'cms_plugin_terms', function ( array $terms ) {
    global $_adn_terms;
    if ( ! empty( $_adn_terms ) && is_array( $_adn_terms ) ) {
        $terms = array_merge( $terms, $_adn_terms );
    }
    return $terms;
} );
