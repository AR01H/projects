<?php
/**
 * core/router.php - Template routing driven by config/pages.php + config/routes.php.
 *
 * STATIC PAGES (priority 98):
 *   Every entry in config/pages.php is served whether or not a real WP page
 *   row exists. If WP resolved a matching page -> its template is returned.
 *   If WP 404'd but the URL path matches a slug/alias -> the 404 is unset,
 *   a 200 is sent and the template renders as a "virtual page". This means a
 *   new page works the moment its array entry + template file exist.
 *
 * DYNAMIC ROUTES (priority 99):
 *   Single-segment URLs are offered to each matcher in config/routes.php
 *   (DB-driven slugs like /buying/). First matcher that returns an array wins.
 *
 * Templates resolve through realpath containment so a registry entry can
 * never point outside the theme directory.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve a theme-relative template path safely. Returns absolute path or ''.
 */
function nt_resolve_template( $rel ) {
	$base = realpath( NT_THEME_DIR . '/pages' );
	$file = realpath( NT_THEME_DIR . '/' . ltrim( (string) $rel, '/' ) );
	if ( $base && $file && 0 === strpos( $file, $base ) && is_file( $file ) ) {
		return $file;
	}
	return '';
}

/**
 * Mark the current request as a successful page render (used when we rescue
 * a 404 into a virtual page) and set the document title.
 */
function nt_router_force_page( $title = '' ) {
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

	$title = (string) $title;
	if ( '' !== $title ) {
		add_filter( 'document_title_parts', static function ( $parts ) use ( $title ) {
			$parts['title'] = $title;
			return $parts;
		} );
	}
}

/**
 * Static page router - one generic loop over config/pages.php.
 */
function nt_router_static_pages( $template ) {
	$path          = nt_request_path();
	$is_global_404 = is_404();

	foreach ( nt_config( 'pages' ) as $slug => $def ) {
		$file = nt_resolve_template( $def['template'] ?? '' );
		if ( '' === $file ) {
			continue;
		}

		$slugs = array_merge( array( (string) $slug ), (array) ( $def['aliases'] ?? array() ) );

		// Real WP page (including the static front page)?
		$is_page = false;
		foreach ( $slugs as $s ) {
			if ( is_page( $s ) ) {
				$is_page = true;
				break;
			}
		}

		// Virtual page: WP 404'd but the raw path matches this entry.
		$is_virtual = ! $is_page && $is_global_404 && in_array( $path, $slugs, true );

		if ( ! $is_page && ! $is_virtual ) {
			continue;
		}

		if ( $is_virtual ) {
			nt_router_force_page( (string) ( $def['title'] ?? '' ) );
		}

		// Let core/assets.php know which registry entry is rendering.
		set_query_var( 'nt_active_page', (string) $slug );

		return $file;
	}

	return $template;
}

/**
 * Dynamic route router - loops config/routes.php for single-segment paths.
 */
function nt_router_dynamic_routes( $template ) {
	$path = nt_request_path();

	// Only single-segment top-level paths: /buying/ yes, /buying/step-2/ no.
	if ( '' === $path || false !== strpos( $path, '/' ) ) {
		return $template;
	}

	$slug = sanitize_title( $path );
	if ( '' === $slug ) {
		return $template;
	}

	foreach ( nt_config( 'routes' ) as $route_key => $rule ) {
		if ( empty( $rule['match'] ) || ! is_callable( $rule['match'] ) ) {
			continue;
		}
		$vars = call_user_func( $rule['match'], $slug );
		if ( false === $vars || ! is_array( $vars ) ) {
			continue;
		}

		$file = nt_resolve_template( $rule['template'] ?? '' );
		if ( '' === $file ) {
			continue;
		}

		$title = $rule['title'] ?? '';
		if ( is_callable( $title ) ) {
			$title = (string) call_user_func( $title, $slug );
		}
		nt_router_force_page( $title );

		foreach ( $vars as $var => $value ) {
			set_query_var( $var, $value );
		}
		set_query_var( 'nt_active_route', (string) $route_key );

		return $file;
	}

	return $template;
}

/**
 * Stop WP canonical redirects from hijacking URLs owned by a dynamic route
 * (e.g. a WP post slug colliding with a DB term slug). Keep matchers cheap -
 * they can run here and again at template_include on the same request.
 */
function nt_router_suppress_canonical( $redirect_url, $requested_url ) {
	$routes = nt_config( 'routes' );
	if ( empty( $routes ) ) {
		return $redirect_url;
	}
	$path = trim( (string) parse_url( (string) $requested_url, PHP_URL_PATH ), '/' );
	if ( '' === $path || false !== strpos( $path, '/' ) ) {
		return $redirect_url;
	}
	$slug = sanitize_title( $path );
	foreach ( $routes as $rule ) {
		if ( ! empty( $rule['match'] ) && is_callable( $rule['match'] ) && false !== call_user_func( $rule['match'], $slug ) ) {
			return false; // Our URL - keep it.
		}
	}
	return $redirect_url;
}

/**
 * Create WP page rows for every registry entry (idempotent), assign the
 * template meta, and set the 'front' => true entry as the static front page.
 * Runs on theme activation and from the admin Tools tab ("Sync Pages").
 *
 * @return int Number of pages created.
 */
function nt_sync_pages() {
	$created = 0;

	foreach ( nt_config( 'pages' ) as $slug => $def ) {
		if ( isset( $def['create'] ) && false === $def['create'] ) {
			continue;
		}

		$all_slugs = array_merge( array( (string) $slug ), (array) ( $def['aliases'] ?? array() ) );

		foreach ( $all_slugs as $s ) {
			if ( get_page_by_path( $s ) ) {
				continue;
			}
			$page_id = wp_insert_post( array(
				'post_title'   => (string) ( $def['title'] ?? ucwords( str_replace( '-', ' ', $s ) ) ),
				'post_name'    => $s,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			) );
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_wp_page_template', (string) ( $def['template'] ?? '' ) );
				$created++;
			}
		}

		// Static front page.
		if ( ! empty( $def['front'] ) ) {
			$front = get_page_by_path( (string) $slug );
			if ( $front instanceof WP_Post ) {
				if ( 'page' !== get_option( 'show_on_front' ) ) {
					update_option( 'show_on_front', 'page' );
				}
				if ( (int) get_option( 'page_on_front' ) !== (int) $front->ID ) {
					update_option( 'page_on_front', (int) $front->ID );
				}
			}
		}
	}

	return $created;
}
