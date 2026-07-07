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
		'components' => 'assets/css/components.css',
		'utilities'  => 'assets/css/utilities.css',
	),

	// Front-end JS - loaded in footer, in this order. 'common' also receives
	// the window.ntSite config object (ajax url, rest url, nonces).
	'js' => array(
		'common'        => 'assets/js/common.js',
		'main'          => 'assets/js/main.js',
		'scroll-to-top' => 'assets/js/scroll-to-top.js',
	),

	// External assets (CDN). handle => array( 'src' => url, 'ver' => string ).
	'external_css' => array(
		// 'fontawesome' => array(
		//     'src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
		//     'ver' => '6.5.2',
		// ),
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
