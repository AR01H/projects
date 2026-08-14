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
'css' => array(
		'variables'  => 'assets/css/variables.css',
		'legacy'     => 'assets/css/legacy.css',
		'main'       => 'assets/css/main.css',
		'vintage'    => 'assets/css/vintage.css',
		'components' => 'assets/css/components.css',

		'ui-kit'     => 'assets/css/ui-kit.css',
		'sections'   => 'assets/css/sections.css',
		'utilities'  => 'assets/css/utilities.css',
		'movie-header' => 'components/movie-header/header.css',

		// Component library (assets/css/components/) - one file per reusable
		// UI component, each with its own complete base/variants/sizes/states.
		// Loaded last so a component file is always the final word on its own
		// classes, regardless of what an older rule elsewhere still says.
		'ui-buttons'         => 'assets/css/components/buttons.css',
		'ui-alerts'          => 'assets/css/components/alerts.css',
		'ui-breadcrumbs'     => 'assets/css/components/breadcrumbs.css',
		'ui-stamps'          => 'assets/css/components/stamps.css',
		'ui-section-heading' => 'assets/css/components/section-heading.css',
		'ui-section-placeholder' => 'assets/css/components/section-placeholder.css',
		'ui-tabs'            => 'assets/css/components/tabs.css',
		'ui-pagination'      => 'assets/css/components/pagination.css',
		'ui-accordions'      => 'assets/css/components/accordions.css',
	),

	// Front-end JS - loaded in footer, in this order. 'common' also receives
	// the window.ntSite config object (ajax url, rest url, nonces).
	'js' => array(
		'common'        => 'assets/js/common.js',
		'legacy'        => 'assets/js/legacy.js',
		'main'          => 'assets/js/main.js',
		'ui-kit'         => 'assets/js/ui-kit.js',
		'cookie-consent' => 'assets/js/cookie-consent.js',
		'movie-header'  => 'components/movie-header/header.js',
	),

	// External assets (CDN). handle => array( 'src' => url, 'ver' => string ).
	'external_css' => array(
		'google-fonts' => array(
		    'src' => 'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Jim+Nightshade&family=Jolly+Lodger&family=Mountains+of+Christmas:wght@400;700&family=Lato:ital,wght@0,300;0,400;0,700;1,300;1,400&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap',
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
