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
add_action( 'template_redirect', 'adn_redirect_home_slug' );
/**
 * Redirect /home/ to / to avoid duplicate content.
 */
function adn_redirect_home_slug() {
	if ( is_admin() ) {
		return;
	}
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path = trim( (string) parse_url( $request_uri, PHP_URL_PATH ), '/' );
	
	// Redirect /home/ to / for SEO (avoid duplicate content)
	if ( 'home' === $path || 'home' === trailingslashit( $path ) ) {
		wp_redirect( home_url( '/' ), 301 );
		exit;
	}
}

add_filter( 'template_include', 'adn_route_page_definitions', 98 );
add_filter( 'template_include', 'adn_route_parent_term_template', 99 );
add_filter( 'template_include', 'adn_route_news_single_slug', 97 );

add_action( 'template_redirect', 'adn_serve_calculators_sitemap', 0 );
/**
 * Serves /calculators-sitemap.xml live, straight from the theme's
 * config/calculators-sitemap.xml (see config/README.md) - a single,
 * version-controlled source of truth instead of a manually-uploaded static
 * file at the server's document root that would drift out of sync every
 * time a calculator is activated/deactivated. Referenced (currently
 * commented out, pending this route existing) from config/robots.txt and
 * config/sitemap-index-reference.xml.
 */
function adn_serve_calculators_sitemap() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path        = trim( (string) parse_url( $request_uri, PHP_URL_PATH ), '/' );
	if ( 'calculators-sitemap.xml' !== $path ) {
		return;
	}

	$file = get_template_directory() . '/config/calculators-sitemap.xml';
	if ( ! is_file( $file ) ) {
		return; // fall through to the normal 404 rather than error.
	}

	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8' );
	readfile( $file );
	exit;
}

/**
 * Single generic router for all entries in adn_get_page_definitions().
 *
 * For each slug → template mapping:
 *   - If it matches a real WP page: return the theme template file.
 *   - If the request is a 404 and the URL path matches the slug: de-flag 404,
 *     set a 200 status, inject a document title, and return the template file.
 *
 * Sets adn_virtual_template query var to the template basename (without .php)
 * so AssetLoader::getCurrentTemplate() can load the right CSS/JS.
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

		$is_page     = false;
		$matched_slug = '';
		foreach ( $slugs as $_s ) {
			if ( is_page( $_s ) ) { $is_page = true; $matched_slug = $_s; break; }
		}
		$is_404 = ! $is_page && $is_global_404 && in_array( $path, $slugs, true );
		if ( $is_404 ) {
			$matched_slug = $path;
		}

		if ( ! $is_page && ! $is_404 ) {
			continue;
		}

		// Aliases exist only so an old/alternate URL still resolves - they are
		// never a second canonical page. Whether the alias is being served as a
		// real WP page (created by an earlier adn_create_default_pages() run,
		// back before aliases stopped getting their own page row) or just as a
		// virtual 404-intercept, always 301 it to the primary slug so there is
		// exactly one indexable, canonical URL for this page.
		if ( ! empty( $aliases ) && in_array( $matched_slug, $aliases, true ) ) {
			wp_redirect( home_url( '/' . $slug . '/' ), 301 );
			exit;
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
 * Pretty permalink for a single news item: /news/<slug>/ instead of the query
 * string form. WordPress 404s this on its own (the "news" WP Page has no real
 * child page named <slug>) - this intercepts that 404 the same way
 * adn_route_page_definitions() does: de-flag it, force a 200, and hand off to
 * PageNewsall.php with the slug already resolved via a query var.
 */
function adn_route_news_single_slug( $template ) {
	if ( ! is_404() || ! defined( 'SITE_NEWS_URL' ) ) {
		return $template;
	}

	$raw    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path   = trim( (string) parse_url( $raw, PHP_URL_PATH ), '/' );
	$prefix = trim( SITE_NEWS_URL, '/' ) . '/';

	if ( '' === $path || 0 !== strpos( $path, $prefix ) ) {
		return $template;
	}

	$item_slug = sanitize_title( substr( $path, strlen( $prefix ) ) );
	if ( '' === $item_slug ) {
		return $template;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'ah_news_bar_items';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return $template;
	}
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s LIMIT 1", $item_slug ) );
	if ( ! $exists ) {
		return $template; // real 404 - no news item has this slug
	}

	$base = realpath( ADN_THEME_DIR . '/pages' );
	$file = realpath( ADN_THEME_DIR . '/pages/PageNewsall.php' );
	if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
		return $template;
	}

	global $wp_query;
	$wp_query->is_404  = false;
	$wp_query->is_page = true;
	status_header( 200 );
	nocache_headers();
	set_query_var( 'ah_news', $item_slug );

	return $file;
}

/**
 * Suppress WordPress canonical redirects for URLs that are managed CMS term pages.
 * Without this, WP posts whose slug matches a term slug cause a redirect before
 * adn_route_parent_term_template (template_include, priority 99) can intercept.
 */
add_filter( 'redirect_canonical', 'adn_suppress_canonical_for_term_slugs', 1, 2 );
function adn_suppress_canonical_for_term_slugs( $redirect_url, $requested_url ) {
	if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
		return $redirect_url;
	}
	$path = trim( (string) parse_url( $requested_url, PHP_URL_PATH ), '/' );
	if ( '' === $path || false !== strpos( $path, '/' ) ) {
		return $redirect_url;
	}
	$slug = sanitize_title( $path );
	if ( '' === $slug ) {
		return $redirect_url;
	}
	global $wpdb;
	$is_term = (bool) $wpdb->get_var( $wpdb->prepare(
		"SELECT 1 FROM `{$wpdb->prefix}ah_taxonomy_parent_terms` WHERE slug = %s AND status = 'active'
		 UNION ALL
		 SELECT 1 FROM `{$wpdb->prefix}ah_taxonomies` WHERE slug = %s AND status = 'active'
		 LIMIT 1",
		$slug,
		$slug
	) );
	return $is_term ? false : $redirect_url;
}

/**
 * Dynamic CMS term router (priority 99).
 *
 * Intercepts single-segment slugs that match active rows in
 * ah_taxonomy_parent_terms (→ PageCategoryGuide.php) or
 * ah_taxonomies (→ PageTopicCategoryGuide.php).
 *
 * Runs for both 404s and non-404s so that WP posts whose slug matches a
 * CMS term slug are correctly overridden rather than served as single posts.
 */
function adn_route_parent_term_template( $template ) {
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
		"SELECT id, name FROM {$table} WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );

	if ( $row ) {
		global $wp_query;
		if ( $wp_query->is_404 ) {
			status_header( 200 );
			nocache_headers();
		}
		$wp_query->is_404     = false;
		$wp_query->is_page    = true;
		$wp_query->is_single  = false;
		$wp_query->is_singular = false;
		$wp_query->is_archive = false;
		$_pt_title = isset( $row->name ) && '' !== (string) $row->name ? (string) $row->name : get_bloginfo( 'name' );
		add_filter( 'document_title_parts', static function ( $parts ) use ( $_pt_title ) {
			$parts['title'] = $_pt_title;
			return $parts;
		} );
		set_query_var( 'adn_cat_slug', $slug );
		return get_template_directory() . '/pages/PageCategoryGuide.php';
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
		"SELECT id, name FROM {$tax_table} WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );

	if ( ! $tax_row ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'adn_route_parent_term_template: no taxonomy row for slug="%s" (table=%s)', $slug, $tax_table ) );
		}
		return $template;
	}

	global $wp_query;
	if ( $wp_query->is_404 ) {
		status_header( 200 );
		nocache_headers();
	}
	$wp_query->is_404      = false;
	$wp_query->is_page     = true;
	$wp_query->is_single   = false;
	$wp_query->is_singular = false;
	$wp_query->is_archive  = false;
	$_tx_title = isset( $tax_row->name ) && '' !== (string) $tax_row->name ? (string) $tax_row->name : get_bloginfo( 'name' );
	add_filter( 'document_title_parts', static function ( $parts ) use ( $_tx_title ) {
		$parts['title'] = $_tx_title;
		return $parts;
	} );
	set_query_var( 'adn_guide_term_slug', $slug );

	return get_template_directory() . '/pages/PageTopicCategoryGuide.php';
}
