<?php
/**
 * config/pages.php - THE page registry. One array entry = one complete page.
 *
 * Everything about a page lives in its single entry: URL slug, title,
 * template file, its CSS/JS, aliases and whether WP should auto-create it.
 * core/router.php + core/assets.php loop over this array - you never write
 * routing or enqueue code for a page again.
 *
 * ADD A PAGE (3 steps, no hooks):
 *   1. Add an entry below.
 *   2. Create the template file in /pages/.
 *   3. (optional) Create its css/js files in /assets/.
 *   Then run Admin -> Theme -> Admin Tools -> Pages -> "Sync Now" (or
 *   re-activate the theme). Done - the URL works even BEFORE the WP page
 *   row exists (virtual routing intercepts the 404).
 *
 * Entry keys:
 *   title    (string)  Page + document title.
 *   template (string)  Template path relative to theme root. Required.
 *   css      (array)   Page-specific stylesheets (theme-relative paths).
 *   js       (array)   Page-specific scripts (theme-relative paths).
 *   aliases  (array)   Extra slugs that serve the same template.
 *   create   (bool)    Auto-create the WP page on activation/sync. Default true.
 *   front    (bool)    Make this the static front page. One entry only.
 *
 * NOTE: this theme was reset to a clean component-development foundation -
 * every real content page was removed on purpose ('testing' below is what
 * remained). Real pages are being rebuilt one at a time from the components
 * shown there - see admin/data/page_sections.json for each page's section list.
 */

defined( 'ABSPATH' ) || exit;

return array(

	'home' => array(
		'title'    => 'Home',
		'template' => 'pages/page-home.php',
		'front'    => true,
	),

	'history' => array(
		'title'    => 'History',
		'template' => 'pages/page-history.php',
	),

	'testing' => array(
		'title'    => 'Component Showcase',
		'template' => 'pages/page-testing.php',
		'css'      => array( 'assets/css/pages/testing.css' ),
		'js'       => array( 'assets/js/pages/testing.js' ),
		'aliases'  => array( 'component-showcase', 'style-guide' ),
	),

);
