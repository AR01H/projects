<?php
/**
 * includes/core_routing.php  -  Static page routing + dynamic parent-term URL routing.
 *
 * adn_route_page_definitions() (priority 98): handles all slugs defined in
 * adn_get_page_definitions() - works whether a real WP page exists or not,
 * so pretty permalinks don't need to be configured for every page.
 *
 * adn_route_parent_term_template() (priority 99): intercepts 404s for slugs
 * that match active rows in ah_taxonomy_parent_terms or ah_taxonomies.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'template_include', 'adn_route_page_definitions', 98 );
add_filter( 'template_include', 'adn_route_parent_term_template', 99 );

/**
 * Single generic router for all entries in adn_get_page_definitions().
 *
 * For each slug → template mapping:
 *   - If it matches a real WP page: return the theme template file.
 *   - If the request is a 404 and the URL path matches the slug: de-flag 404,
 *     set a 200 status, inject a document title, and return the template file.
 *
 * Sets adn_virtual_template query var to the template basename (without .php)
 * so adn_enqueue_template_specific_assets() can load the right CSS/JS.
 */
function adn_route_page_definitions( $template ) {
	if ( ! function_exists( 'adn_get_page_definitions' ) ) {
		return $template;
	}

	$raw  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path = trim( (string) parse_url( $raw, PHP_URL_PATH ), '/' );
	$is_global_404 = is_404();

	foreach ( adn_get_page_definitions() as $slug => $def ) {
		if ( '' === $slug ) {
			continue; // skip home - served by WordPress normally
		}

		$template_rel = isset( $def['template'] ) ? (string) $def['template'] : '';
		if ( '' === $template_rel ) {
			continue;
		}

		// Primary slug + any aliases defined in the page definition.
		$aliases = isset( $def['aliases'] ) && is_array( $def['aliases'] ) ? $def['aliases'] : array();
		$slugs   = array_merge( array( $slug ), $aliases );

		$is_page = false;
		foreach ( $slugs as $_s ) {
			if ( is_page( $_s ) ) { $is_page = true; break; }
		}
		$is_404 = ! $is_page && $is_global_404 && in_array( $path, $slugs, true );

		if ( ! $is_page && ! $is_404 ) {
			continue;
		}

		$base = realpath( ADN_THEME_DIR . '/pages' );
		$file = realpath( ADN_THEME_DIR . '/' . $template_rel );
		if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
			continue;
		}

		if ( $is_404 ) {
			global $wp_query;
			$wp_query->is_404  = false;
			$wp_query->is_page = true;
			status_header( 200 );
			nocache_headers();
			$title = isset( $def['title'] ) ? (string) $def['title'] : '';
			if ( '' !== $title ) {
				add_filter( 'document_title_parts', static function ( $parts ) use ( $title ) {
					$parts['title'] = $title;
					return $parts;
				} );
			}
		}

		set_query_var( 'adn_virtual_template', basename( $template_rel, '.php' ) );

		return $file;
	}

	return $template;
}

/**
 * Dynamic CMS term router (priority 99).
 *
 * Intercepts 404s for single-segment slugs that match active rows in
 * ah_taxonomy_parent_terms (→ page-category_guide.php) or
 * ah_taxonomies (→ page-topic_category_guide.php).
 */
function adn_route_parent_term_template( $template ) {
	if ( ! is_404() ) {
		return $template;
	}

	$raw_uri = isset( $_SERVER['REQUEST_URI'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
		: '';
	$path = trim( (string) parse_url( $raw_uri, PHP_URL_PATH ), '/' );

	// Only match single-segment top-level paths like /buying/ - not /buying/step-2/.
	if ( '' === $path || false !== strpos( $path, '/' ) ) {
		return $template;
	}

	$slug = sanitize_title( $path );

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( 'adn_route_parent_term_template: REQUEST_URI="%s" path="%s" slug="%s"', $raw_uri, $path, $slug ) );
	}
	if ( '' === $slug ) {
		return $template;
	}

	if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
		return $template;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'ah_taxonomy_parent_terms';

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return $template;
	}

	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT id FROM {$table} WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );

	if ( $row ) {
		global $wp_query;
		$wp_query->is_404  = false;
		$wp_query->is_page = true;
		status_header( 200 );
		nocache_headers();
		set_query_var( 'adn_cat_slug', $slug );
		return get_template_directory() . '/pages/page-category_guide.php';
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( 'adn_route_parent_term_template: no parent-term row for slug="%s" (table=%s)', $slug, $table ) );
	}

	$tax_table = $wpdb->prefix . 'ah_taxonomies';

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tax_table ) ) !== $tax_table ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'adn_route_parent_term_template: missing tax_table=%s', $tax_table ) );
		}
		return $template;
	}

	$tax_row = $wpdb->get_row( $wpdb->prepare(
		"SELECT id FROM {$tax_table} WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );

	if ( ! $tax_row ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'adn_route_parent_term_template: no taxonomy row for slug="%s" (table=%s)', $slug, $tax_table ) );
		}
		return $template;
	}

	global $wp_query;
	$wp_query->is_404  = false;
	$wp_query->is_page = true;
	status_header( 200 );
	nocache_headers();
	set_query_var( 'adn_guide_term_slug', $slug );

	return get_template_directory() . '/pages/page-topic_category_guide.php';
}
