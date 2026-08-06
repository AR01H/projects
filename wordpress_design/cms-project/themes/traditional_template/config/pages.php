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
 */

defined( 'ABSPATH' ) || exit;

return array(

	'home' => array(
		'title'    => 'Home',
		'template' => 'pages/page-home.php',
		'css'      => array( 'assets/css/pages/home.css' ),
		'js'       => array( 'assets/js/pages/home.js' ),
		'front'    => true,
	),

	'contact' => array(
		'title'    => 'Contact Us',
		'template' => 'pages/page-contact.php',
		'css'      => array( 'assets/css/pages/contact.css' ),
		'js'       => array( 'assets/js/pages/contact.js' ),
		'aliases'  => array( 'contact-us' ),
	),

	// Two ways to list posts, both kept on purpose:
	//   'news' - the bespoke screen (live search + Load More over REST).
	//   'blog' - the STANDARD archive: server-rendered, numbered pagination,
	//            a sidebar, works with JS off. Copy this entry into any site
	//            built on the template and it works immediately.
	'news' => array(
		'title'    => 'News & Updates',
		'template' => 'pages/page-news.php',
		'css'      => array( 'assets/css/pages/news.css' ),
		'js'       => array( 'assets/js/pages/news.js' ),
	),

	'blog' => array(
		'title'    => 'The Journal',
		'template' => 'pages/page-blog.php',
		'aliases'  => array( 'journal', 'articles' ),
	),

	// Slug must equal NT_COMING_SOON_SLUG (config/theme.php) so the
	// coming-soon redirect in core/redirects.php has a landing page.
	'coming-soon' => array(
		'title'    => 'Coming Soon',
		'template' => 'pages/page-coming.php',
		'css'      => array( 'assets/css/pages/coming.css' ),
	),
	
	'about' => array(
		'title'    => 'Our Story',
		'template' => 'pages/page-about.php',
		'aliases'  => array( 'our-journey', 'our-story' ),
	),

	'products' => array(
		'title'    => 'Products',
		'template' => 'pages/page-products.php',
		'aliases'  => array( 'menu', 'flavours', 'cane-gur' ),
	),
	
	'gallery' => array(
		'title'    => 'Gallery',
		'template' => 'pages/page-gallery.php',
	),
	
	'franchise' => array(
		'title'    => 'Franchise',
		'template' => 'pages/page-franchise.php',
	),
	
	'events' => array(
		'title'    => 'Events & Catering',
		'template' => 'pages/page-events.php',
	),
	
	'order' => array(
		'title'    => 'Order',
		'template' => 'pages/page-order.php',
		'aliases'  => array( 'order-rates' ),
	),

	// ── Added so the header/footer nav has no dead links ──────────────────
	// nav.json and footer.json already pointed at /menu/, /our-journey/,
	// /privacy-policy/ and /cookie-policy/. The first two are just other
	// names for pages that exist (added as aliases above/below); the rest
	// are real pages now.

	'services' => array(
		'title'    => 'What We Do',
		'template' => 'pages/page-services.php',
	),

	'careers' => array(
		'title'    => 'Work With Us',
		'template' => 'pages/page-careers.php',
		'aliases'  => array( 'jobs' ),
	),

	'faq' => array(
		'title'    => 'Help & FAQs',
		'template' => 'pages/page-faq.php',
		'aliases'  => array( 'faqs', 'help' ),
	),

	'reviews' => array(
		'title'    => 'Reviews',
		'template' => 'pages/page-reviews.php',
		'aliases'  => array( 'testimonials' ),
	),

	// Three policies, ONE template. Which document each renders is mapped in
	// admin/data/legal.json - adding a fourth needs no new PHP file.
	'privacy-policy' => array(
		'title'    => 'Privacy Policy',
		'template' => 'pages/page-legal.php',
		'aliases'  => array( 'privacy' ),
	),

	'cookie-policy' => array(
		'title'    => 'Cookie Policy',
		'template' => 'pages/page-legal.php',
		'aliases'  => array( 'cookies' ),
	),

	'terms' => array(
		'title'    => 'Terms & Conditions',
		'template' => 'pages/page-legal.php',
		'aliases'  => array( 'terms-conditions' ),
	),
);
