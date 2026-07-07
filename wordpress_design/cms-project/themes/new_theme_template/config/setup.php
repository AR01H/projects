<?php
/**
 * config/setup.php - Theme supports, nav menus, image sizes.
 *
 * Consumed by nt_setup_theme() in core/bootstrap.php (after_setup_theme).
 * Add an entry here; the engine loops - no extra code needed.
 */

defined( 'ABSPATH' ) || exit;

return array(

	// add_theme_support() - one loop entry each.
	'supports' => array(
		'title-tag',
		'post-thumbnails',
		'custom-logo',
		'automatic-feed-links',
	),

	// add_theme_support( 'html5', [...] ).
	'html5' => array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ),

	// register_nav_menus() - location => label.
	'menus' => array(
		'primary' => 'Primary Menu',
		'footer'  => 'Footer Menu',
	),

	// add_image_size() - name => array( width, height, crop ).
	'image_sizes' => array(
		'nt-card' => array( 400, 300, true ),
		'nt-hero' => array( 1600, 700, true ),
	),
);
