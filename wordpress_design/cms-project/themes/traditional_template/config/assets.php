<?php
/**
 * config/assets.php - GLOBAL asset registry (loaded on every front-end page).
 *
 * Page-specific css/js does NOT belong here - declare it on the page's own
 * entry in config/pages.php (or config/routes.php for dynamic routes).
 *
 * core/assets.php loops these arrays:
 *   - handle prefix 'app-' is added automatically,
 *   - version = filemtime() so browsers re-fetch the moment you save a file,
 *   - missing files are skipped silently (never a 404).
 */

defined( 'ABSPATH' ) || exit;

return array(

	// Front-end CSS - loaded in this exact order.
	// legacy.css is a low-priority base from the previous theme; it loads BEFORE
	// the vintage design system so vintage/components win on shared selectors
	// (otherwise the old theme clobbers the reskin - e.g. it was resetting
	// .app-stats-bar back to a pale background).
	'css' => array(
		'variables'  => 'assets/css/variables.css',
		'legacy'     => 'assets/css/legacy.css',
		'main'       => 'assets/css/main.css',
		'vintage'    => 'assets/css/vintage.css',
		'components' => 'assets/css/components.css',
		// The shared UI layer (dialogs, alerts, toasts, notice bar,
		// breadcrumbs, drifting leaves) and the reusable portal sections.
		// Both load AFTER vintage.css so they win on any shared selector
		// without needing !important.
		'ui-kit'     => 'assets/css/ui-kit.css',
		'sections'   => 'assets/css/sections.css',
		'utilities'  => 'assets/css/utilities.css',
		// Component-scoped sheet. Lives beside its own markup rather than in
		// assets/css/ because the movie header is a self-contained prop - its
		// ~400 rules would swamp vintage.css and are useless without it.
		'movie-header' => 'components/movie-header/header.css',
	),

	// Front-end JS - loaded in footer, in this order. 'common' also receives
	// the window.ntSite config object (ajax url, rest url, nonces).
	'js' => array(
		'common'        => 'assets/js/common.js',
		'legacy'        => 'assets/js/legacy.js',
		'main'          => 'assets/js/main.js',
		// The UI kit RENDERS the dialogs, the notice strip and the cookie
		// banner in the browser from data PHP ships as window.ntUi /
		// window.ntConsent - so none of that markup is in the document for
		// anyone who never opens it. Must load after 'common' (it extends
		// window.NT and reuses its AJAX form binder).
		'ui-kit'         => 'assets/js/ui-kit.js',
		'cookie-consent' => 'assets/js/cookie-consent.js',
		// Fits the movie header's editable headline to the board. No-ops on
		// pages without one.
		'movie-header'  => 'components/movie-header/header.js',
		// scroll-to-top.js removed: the footer's #app-scroll-to-top button
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
