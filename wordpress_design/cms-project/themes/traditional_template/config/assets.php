<?php
/**
 * config/assets.php - GLOBAL asset registry (loaded on every front-end page).
 *
 * Page-specific css/js does NOT belong here - declare it on the page's own
 * entry in config/pages.php (or config/routes.php for dynamic routes).
 *
 * core/assets.php loops these arrays:
 *   - handle prefix 'nt-' is added automatically,
 *   - version = filemtime() so browsers re-fetch the moment you save a file,
 *   - missing files are skipped silently (never a 404).
 */

defined( 'ABSPATH' ) || exit;

return array(

	// Front-end CSS - loaded in this exact order.
	'css' => array(
		'variables'  => 'assets/css/variables.css',
		'main'       => 'assets/css/main.css',
		'vintage'    => 'assets/css/vintage.css',
		'components' => 'assets/css/components.css',
		'legacy'     => 'assets/css/legacy.css',
		'utilities'  => 'assets/css/utilities.css',
	),

	// Front-end JS - loaded in footer, in this order. 'common' also receives
	// the window.ntSite config object (ajax url, rest url, nonces).
	'js' => array(
		'common'        => 'assets/js/common.js',
		'legacy'        => 'assets/js/legacy.js',
		'main'          => 'assets/js/main.js',
		// scroll-to-top.js removed: the footer's #nt-scroll-to-top button
		// (driven by initScrollToTop() in legacy.js) is the single back-to-top.
	),

	// External assets (CDN). handle => array( 'src' => url, 'ver' => string ).
	'external_css' => array(
		'google-fonts' => array(
		    'src' => 'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Lato:ital,wght@0,300;0,400;0,700;1,300;1,400&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap',
		    'ver' => null,
		),
	),

	// Admin-side assets (live in /admin/assets/), enqueued ONLY on the
	// theme's own admin page.
	'admin_css' => array(
		'admin' => 'admin/assets/admin.css',
	),
	'admin_js' => array(
		'admin' => 'admin/assets/admin.js',
	),
);
