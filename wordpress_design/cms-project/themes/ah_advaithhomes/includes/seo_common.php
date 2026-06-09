<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * SEO‑specific constants - keep them separate from UI strings.
 * These are referenced by seo_helpers.php and can be overridden per client.
 */
handle_defined( 'TXT_SITE_TITLE',          'Advaith Homes - UK Property Guides & Listings' );
handle_defined( 'TXT_SITE_DESCRIPTION',    'Your trusted source for buying, selling and renting homes in the UK. Expert advice, market data and step‑by‑step guides.' );
handle_defined( 'TXT_OG_DEFAULT_IMAGE',  get_theme_file_uri( 'assets/img/og-default.jpg' ) );
handle_defined( 'TXT_LOGO_URL',            get_theme_file_uri( 'assets/img/logo.svg' ) );
?>
