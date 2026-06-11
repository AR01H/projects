<?php

// ===========================
// THEME CONSTANTS
// ===========================
define( 'ADN_THEME_NAME', 'advaithhomes_new' );
define( 'ADN_THEME_DIR', get_template_directory() );
define( 'ADN_THEME_URI', get_template_directory_uri() );
define( 'ADN_THEME_VERSION', '1.0.0' );

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