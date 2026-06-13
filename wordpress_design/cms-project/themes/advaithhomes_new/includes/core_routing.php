<?php
/**
 * includes/core_routing.php  –  Dynamic parent-term URL routing.
 *
 * Intercepts WordPress 404 responses for top-level URL slugs that match an
 * active row in ah_taxonomy_parent_terms.  Serves pages/page-category_guide.php
 * for any such URL without requiring a WordPress page per term.
 *
 * Filter priority 99: runs after all default WordPress template selectors so
 * existing WP pages (e.g. a hand-created /buying/ page) still take precedence.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'template_include', 'adn_route_parent_term_template', 99 );

/**
 * Route /<parent-term-slug>/ to the category guide template.
 *
 * Only fires when WordPress has already concluded the request is a 404 and the
 * CMS plugin tables are available.  No-ops for multi-segment paths.
 *
 * @param string $template Current template path chosen by WordPress.
 * @return string          Category guide template path, or original $template.
 */
function adn_route_parent_term_template( $template ) {
	if ( ! is_404() ) {
		return $template;
	}

	// Extract the first (and only expected) path segment.
	$raw_uri = isset( $_SERVER['REQUEST_URI'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
		: '';
	$path = trim( (string) parse_url( $raw_uri, PHP_URL_PATH ), '/' );

	// Only match single-segment top-level paths like /buying/ — not /buying/step-2/.
	if ( '' === $path || false !== strpos( $path, '/' ) ) {
		return $template;
	}

	$slug = sanitize_key( $path );
	if ( '' === $slug ) {
		return $template;
	}

	// CMS plugin must be active.
	if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
		return $template;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'ah_taxonomy_parent_terms';

	// Guard: table must exist.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return $template;
	}

	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT id FROM {$table} WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );

	if ( ! $row ) {
		return $template; // No matching active term — keep 404.
	}

	// Valid active parent term found — de-flag 404 and serve category guide.
	global $wp_query;
	$wp_query->is_404  = false;
	$wp_query->is_page = true;
	status_header( 200 );
	nocache_headers();

	// Pass the slug to adn_category_get_context() via query var.
	set_query_var( 'adn_cat_slug', $slug );

	return get_template_directory() . '/pages/page-category_guide.php';
}
