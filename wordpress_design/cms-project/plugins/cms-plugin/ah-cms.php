<?php
/**
 * Plugin Name:  CMS ADMIN
 * Description:  CMS engine - admin portal, database, models, helpers, and form builder.
 *               Install as a plugin and pair with any frontend theme that reads wp_ah_* tables.
 * Version:      1.0.2
 * Author:       Akhilesh Ravuri
 * Text Domain:  ah-theme
 */
defined( 'ABSPATH' ) || exit;

// ── Constants ────────────────────────────────────────────────────────────────
define( 'AH_PLUGIN_VERSION', '1.3.1' );
define( 'AH_DB_VERSION_KEY', 'ah_cms_db_version' );

// Table name infix - all custom tables are named: {wpdb_prefix}ah{TABLE_MID_FIX}{table_suffix}
// e.g. wp_ah_cms_plug_services. Change this only before first install.
define( 'TABLE_MID_FIX', '_cms_plug_' );

// plugin_dir_path() has a trailing slash; strip it so paths match the existing
// AH_THEME_DIR convention (no trailing slash) - autoloader adds its own slash.
define( 'AH_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'AH_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// Backward-compat aliases: every model, admin page, helper, and importer that
// already uses AH_THEME_DIR / AH_THEME_URL continues to work unchanged.
define( 'AH_THEME_DIR',     AH_PLUGIN_DIR );
define( 'AH_THEME_URL',     AH_PLUGIN_URL );
define( 'AH_THEME_VERSION', AH_PLUGIN_VERSION );

// ── Autoloader ───────────────────────────────────────────────────────────────
require_once AH_PLUGIN_DIR . '/inc/class-autoloader.php';
require_once AH_PLUGIN_DIR . '/inc/class-ah-cache.php';
AH_Autoloader::register();

// ── Components ───────────────────────────────────────────────────────────────
require_once AH_PLUGIN_DIR . '/components/toaster/index.php';

// ── Admin portal ─────────────────────────────────────────────────────────────
if ( is_admin() ) {
	AH_Admin_Bootstrap::init();
}

// ── Public AJAX (form builder frontend - works for logged-in and guests) ─────
AH_Ajax_Handlers::init_public();

 

add_action( 'init', static function () {
	add_shortcode( 'ah_form',          array( 'AH_Form_Builder', 'render' ) );
	add_shortcode( 'ah_related_links', 'ah_render_related_links_shortcode' );
	add_shortcode( 'ah_static_page',   'ah_render_static_page_shortcode' );
	add_shortcode( 'ah_resource',      'ah_render_resource_shortcode' );
	add_shortcode( 'ah_resources',     'ah_render_resources_shortcode' );
} );

// ── Plugin page template: register + route ────────────────────────────────────
// Tells WordPress the template exists so it can be assigned to pages.
add_filter( 'theme_page_templates', static function ( $templates ) {
	$templates['template-static-page.php'] = 'Static HTML Page';
	return $templates;
} );

// When WordPress resolves the template for a page, redirect to the plugin file.
add_filter( 'template_include', static function ( $template ) {
	if ( ! is_page() ) {
		return $template;
	}
	$slug = get_page_template_slug( get_queried_object_id() );
	if ( 'template-static-page.php' !== $slug ) {
		return $template;
	}
	$plugin_tpl = AH_PLUGIN_DIR . '/template-static-page.php';
	return file_exists( $plugin_tpl ) ? $plugin_tpl : $template;
} );

/**
 * Shortcode [ah_static_page slug="my-page"].
 * Outputs the raw HTML stored for that static page slug.
 * Safe to use in WP post content, widgets, template files, and email templates.
 */
function ah_render_static_page_shortcode( $atts ): string {
	$atts = shortcode_atts( array( 'slug' => '' ), $atts, 'ah_static_page' );
	$slug = sanitize_file_name( trim( (string) $atts['slug'] ) );
	if ( '' === $slug ) {
		return '';
	}
	if ( ! class_exists( 'AH_Static_Pages_Model' ) ) {
		return '';
	}
	$html = ( new AH_Static_Pages_Model() )->get_html( $slug );
	if ( '' === $html ) {
		return '';
	}
	// Wrap in a scoped container so the page's own inline styles don't leak.
	return '<div class="ah-static-page-embed" data-slug="' . esc_attr( $slug ) . '">' . $html . '</div>';
}

/**
 * Theme helper: grouped related content for a post (or any object).
 * Returns [ [ 'container' => '…', 'items' => [ [label,url,link_type,type_label,icon,target] ] ] ].
 *
 *   $groups = ah_get_related_links( get_the_ID() );
 */
function ah_get_related_links( int $object_id = 0, string $object_type = 'wp_post' ): array {
	if ( ! class_exists( 'AH_Related_Links_Model' ) ) {
		return array();
	}
	$object_id = $object_id ?: (int) get_the_ID();
	if ( ! $object_id ) {
		return array();
	}
	return ( new AH_Related_Links_Model() )->get_grouped( $object_type, $object_id );
}

/**
 * Shortcode [ah_related_links id="123" object="wp_post" container="" title="Tools"].
 * All attrs optional - defaults to the current post and every container.
 */
function ah_render_related_links_shortcode( $atts ): string {
	$atts = shortcode_atts( array(
		'id'        => 0,
		'object'    => 'wp_post',
		'container' => '',   // optional: render only this section
		'title'     => '',   // optional wrapper heading
		'class'     => '',
	), $atts, 'ah_related_links' );

	$object_id = (int) $atts['id'] ?: (int) get_the_ID();
	if ( ! $object_id ) {
		return '';
	}

	$groups = ah_get_related_links( $object_id, sanitize_key( $atts['object'] ) );
	if ( empty( $groups ) ) {
		return '';
	}

	$filter = trim( (string) $atts['container'] );

	ob_start();
	echo '<div class="ah-related-links ' . esc_attr( $atts['class'] ) . '">';
	if ( $atts['title'] !== '' ) {
		echo '<h3 class="ah-related-links__title">' . esc_html( $atts['title'] ) . '</h3>';
	}
	foreach ( $groups as $group ) {
		if ( $filter !== '' && strcasecmp( $group['container'], $filter ) !== 0 ) {
			continue;
		}
		echo '<div class="ah-related-links__group" data-container="' . esc_attr( $group['container'] ) . '">';
		echo '<h4 class="ah-related-links__heading">' . esc_html( $group['container'] ) . '</h4>';
		echo '<ul class="ah-related-links__list">';
		foreach ( $group['items'] as $item ) {
			$rel = ( $item['target'] === '_blank' ) ? ' rel="noopener noreferrer"' : '';
			printf(
				'<li class="ah-related-links__item ah-related-links__item--%1$s">'
					. '<a href="%2$s" target="%3$s"%4$s>'
					. '<span class="ah-related-links__icon" aria-hidden="true">%5$s</span>'
					. '<span class="ah-related-links__label">%6$s</span>'
					. '</a></li>',
				esc_attr( $item['link_type'] ),
				esc_url( $item['url'] ),
				esc_attr( $item['target'] ),
				$rel, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - static literal
				esc_html( $item['icon'] ),
				esc_html( $item['label'] )
			);
		}
		echo '</ul></div>';
	}
	echo '</div>';
	return ob_get_clean();
}

// ── Resources helpers & shortcodes ───────────────────────────────────────────

/**
 * Public helper: fetch active resources.
 * Usage: ah_get_resources( 'category', 'youtube', 3 )
 *
 * @param string $context  category | home | tools | global | '' (all)
 * @param string $type     youtube | shorts | instagram | … | '' (all)
 * @param int    $limit    0 = no limit
 */
function ah_get_resources( string $context = '', string $type = '', int $limit = 0 ): array {
	if ( ! class_exists( 'AH_Resources_Model' ) ) {
		return array();
	}
	return ( new AH_Resources_Model() )->get_active( $context, $type, $limit );
}

/**
 * Shortcode [ah_resource id="1" show_title="0" show_desc="0"]
 * Embeds a single resource by its ID.
 */
function ah_render_resource_shortcode( $atts ): string {
	$atts = shortcode_atts( array(
		'id'         => 0,
		'show_title' => '0',
		'show_desc'  => '0',
		'class'      => '',
	), $atts, 'ah_resource' );

	$id = (int) $atts['id'];
	if ( ! $id || ! class_exists( 'AH_Resources_Model' ) ) {
		return '';
	}

	$item = ( new AH_Resources_Model() )->find( $id );
	if ( ! $item || $item->status !== 'active' ) {
		return '';
	}

	return AH_Resources_Model::render_resource( $item, array(
		'show_title' => ! empty( $atts['show_title'] ) && $atts['show_title'] !== '0',
		'show_desc'  => ! empty( $atts['show_desc'] )  && $atts['show_desc']  !== '0',
		'class'      => sanitize_html_class( $atts['class'] ),
	) );
}

/**
 * Shortcode [ah_resources context="category" type="youtube" limit="3" show_title="1" show_desc="0" class=""]
 * Renders a grid of matching resources.
 */
function ah_render_resources_shortcode( $atts ): string {
	$atts = shortcode_atts( array(
		'context'    => '',
		'type'       => '',
		'limit'      => '0',
		'show_title' => '0',
		'show_desc'  => '0',
		'class'      => '',
		'columns'    => '2',
	), $atts, 'ah_resources' );

	if ( ! class_exists( 'AH_Resources_Model' ) ) {
		return '';
	}

	$items = ah_get_resources(
		sanitize_key( $atts['context'] ),
		sanitize_key( $atts['type'] ),
		(int) $atts['limit']
	);

	if ( empty( $items ) ) {
		return '';
	}

	$opts = array(
		'show_title' => ! empty( $atts['show_title'] ) && $atts['show_title'] !== '0',
		'show_desc'  => ! empty( $atts['show_desc'] )  && $atts['show_desc']  !== '0',
	);

	$cols      = max( 1, min( 4, (int) $atts['columns'] ) );
	$wrap_cls  = 'ah-resources-grid ah-resources-grid--cols-' . $cols;
	if ( $atts['class'] ) {
		$wrap_cls .= ' ' . sanitize_html_class( $atts['class'] );
	}

	ob_start();
	echo '<div class="' . esc_attr( $wrap_cls ) . '" style="display:grid;grid-template-columns:repeat(' . esc_attr( (string) $cols ) . ',1fr);gap:20px;">';
	foreach ( $items as $item ) {
		echo AH_Resources_Model::render_resource( $item, $opts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
	return ob_get_clean();
}

// ── Database ─────────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, array( 'AH_DB_Installer', 'install' ) );
add_action( 'wp_loaded', array( 'AH_DB_Installer', 'maybe_upgrade' ) );

// ── REST API - all routes defined in api/class-rest-routes.php ───────────────
// To add/remove endpoints: edit the $routes array in AH_Rest_Routes::register().
add_action( 'rest_api_init', array( 'AH_Rest_Routes', 'register' ) );

// ── Rules Engine: async cron processor ───────────────────────────────────────
// evaluate() queues actions as 'pending' in ah_trigger_logs; this cron fires
// them in the background every minute (pending + failed retries).

add_filter( 'cron_schedules', static function ( array $s ): array {
	if ( ! isset( $s['ah_every_minute'] ) ) {
		$s['ah_every_minute'] = array(
			'interval' => 60,
			'display'  => 'Every Minute (AH Workflow Manager)',
		);
	}
	return $s;
} );

add_action( 'ah_rules_cron_process', array( 'AH_Workflow_Manager', 'cron_process' ) );

// Schedule on first load; clear any old retry-only hook.
add_action( 'init', static function (): void {
	// Remove old hook name if it exists from a prior version
	$old = wp_next_scheduled( 'ah_rules_cron_retry' );
	if ( $old ) wp_unschedule_event( $old, 'ah_rules_cron_retry' );

	if ( ! wp_next_scheduled( 'ah_rules_cron_process' ) ) {
		wp_schedule_event( time(), 'ah_every_minute', 'ah_rules_cron_process' );
	}
} );

// Clear schedule on plugin deactivation.
register_deactivation_hook( __FILE__, static function (): void {
	$ts = wp_next_scheduled( 'ah_rules_cron_process' );
	if ( $ts ) wp_unschedule_event( $ts, 'ah_rules_cron_process' );
} );

// ── Redirect Rules - front-end enforcement ────────────────────────────────────
// Priority 1 = fires before WordPress's own redirect_canonical (priority 10).
add_action( 'template_redirect', static function (): void {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_redirect_rules';
	// Bail if table doesn't exist yet (pre-upgrade).
	if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) ) return; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
	if ( '' === $path ) return;

	$rule = $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		"SELECT * FROM `{$table}` WHERE source_slug = %s AND is_active = 1 LIMIT 1",
		$path
	) );
	if ( ! $rule ) return;

	// Increment hit counter (non-blocking; ignore errors).
	$wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET hit_count = hit_count + 1 WHERE id = %d", (int) $rule->id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$type   = (string) $rule->type;
	$target = esc_url_raw( (string) $rule->target_url );
	$label  = sanitize_text_field( (string) $rule->notes );

	if ( '410' === $type ) {
		status_header( 410 );
		nocache_headers();
		$site = esc_html( get_bloginfo( 'name' ) );
		echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Page Gone - {$site}</title>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "<style>*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:48px 40px;max-width:440px;text-align:center}.icon{font-size:48px;margin-bottom:16px}.title{font-size:22px;font-weight:700;color:#111827;margin:0 0 10px}.msg{color:#6b7280;font-size:15px;margin:0 0 24px}.back{display:inline-block;padding:10px 24px;background:#1d4ed8;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:14px}</style>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "</head><body><div class='card'><div class='icon'>🗑️</div><h1 class='title'>Page Removed</h1><p class='msg'>This page no longer exists and has been permanently removed.</p><a href='" . esc_url( home_url( '/' ) ) . "' class='back'>← Back to Home</a></div></body></html>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	if ( 'exit' === $type && $target ) {
		nocache_headers();
		$site     = esc_html( get_bloginfo( 'name' ) );
		$t_esc    = esc_url( $target );
		$t_label  = esc_html( $label ?: $target );
		$home_url = esc_url( home_url( '/' ) );
		echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Leaving {$site}</title>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "<meta http-equiv='refresh' content='4;url={$t_esc}'>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "<style>*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:48px 40px;max-width:480px;text-align:center}.icon{font-size:48px;margin-bottom:16px}.title{font-size:20px;font-weight:700;color:#111827;margin:0 0 8px}.site{font-size:13px;color:#6b7280;margin:0 0 16px}.dest{background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px 16px;font-size:13px;color:#0369a1;word-break:break-all;margin-bottom:20px}.bar-wrap{height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden;margin-bottom:20px}.bar{height:100%;background:#1d4ed8;border-radius:2px;animation:fill 4s linear forwards}@keyframes fill{from{width:0}to{width:100%}}.links{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}.btn{display:inline-block;padding:9px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500}.btn-primary{background:#1d4ed8;color:#fff}.btn-secondary{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}</style>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "</head><body><div class='card'><div class='icon'>🔗</div><h1 class='title'>You are leaving {$site}</h1><p class='site'>You will be redirected to an external site in a moment.</p><div class='dest'>{$t_label}</div><div class='bar-wrap'><div class='bar'></div></div><div class='links'><a href='{$t_esc}' class='btn btn-primary'>Continue →</a><a href='{$home_url}' class='btn btn-secondary'>Stay on {$site}</a></div></div></body></html>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	if ( $target && in_array( $type, array( '301', '302' ), true ) ) {
		wp_redirect( $target, (int) $type );
		exit;
	}
} );

// ── Builder page frontend routing ─────────────────────────────────────────────
add_action( 'template_redirect', static function () {
	if ( ! is_404() ) return;
	global $wpdb;
	$table = $wpdb->prefix . 'ah_builder_pages';
	$home_path    = trim( (string) parse_url( home_url(), PHP_URL_PATH ), '/' );
	$request_path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $home_path !== '' && strpos( $request_path, $home_path ) === 0 ) {
		$request_path = ltrim( substr( $request_path, strlen( $home_path ) ), '/' );
	}
	$slug = sanitize_title( trim( $request_path, '/' ) );
	if ( ! $slug ) return;
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$page = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE slug = %s AND status = 'active'", $slug ) );
	if ( ! $page ) return;
	$GLOBALS['ah_builder_page'] = $page;
	// Enqueue the builder block CSS (section, hero, cards, CTA, FAQ, etc.)
	add_action( 'wp_enqueue_scripts', static function () {
		wp_enqueue_style(
			'ah-builder-page',
			AH_PLUGIN_URL . '/assets/css/builder-page.css',
			array( 'ah-variables' ),
			AH_PLUGIN_VERSION
		);
	} );
	// Block renderer functions must be available to both the plugin template and any theme override.
	require_once AH_PLUGIN_DIR . '/inc/builder-block-renderer.php';
	// Theme override: if the active theme ships templates/ah-builder-page.php it takes precedence.
	$_theme_tpl = locate_template( 'templates/ah-builder-page.php' );
	include $_theme_tpl ?: AH_PLUGIN_DIR . '/templates/template-builder-page.php';
	exit;
} );

// ── Global Styles injection ──────────────────────────────────────────────────

add_action( 'wp_head', static function (): void {
	if ( is_admin() ) return;
	$css = trim( (string) get_option( 'ah_global_styles_css', '' ) );
	if ( '' === $css || ! get_option( 'ah_global_styles_active', 0 ) ) return;
	echo "\n<style id=\"ah-global-styles\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 98 );

add_action( 'wp_footer', static function (): void {
	if ( is_admin() ) return;
	$js = trim( (string) get_option( 'ah_global_styles_js', '' ) );
	if ( '' === $js || ! get_option( 'ah_global_styles_active', 0 ) ) return;
	echo "\n<script id=\"ah-global-scripts\">\n" . $js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 98 );

add_action( 'wp_ajax_ah_save_global_styles', static function (): void {
	if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ) );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Access denied.' ) );
	}
	update_option( 'ah_global_styles_css',    wp_unslash( $_POST['css']    ?? '' ) );
	update_option( 'ah_global_styles_js',     wp_unslash( $_POST['js']     ?? '' ) );
	update_option( 'ah_global_styles_active', (int) ( $_POST['active'] ?? 0 ) );
	wp_send_json_success( array( 'message' => 'Global styles saved.' ) );
} );

// ── Per-slug Custom CSS / JS ─────────────────────────────────────────────────

function ah_custom_code_current_slug(): string {
	$qv = (string) get_query_var( 'adn_cat_slug', '' );
	if ( '' !== $qv ) { return sanitize_key( $qv ); }
	$obj = get_queried_object();
	if ( $obj instanceof WP_Post ) { return sanitize_key( $obj->post_name ); }
	$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
	$seg  = explode( '/', $path );
	return sanitize_key( $seg[0] ?? '' );
}

function ah_custom_code_get( string $slug ) {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_custom_code';
	return $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM `{$table}` WHERE slug = %s AND is_active = 1 LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$slug
	) );
}

add_action( 'wp_head', static function (): void {
	if ( is_admin() ) return;
	$slug = ah_custom_code_current_slug();
	if ( '' === $slug ) return;
	$row = ah_custom_code_get( $slug );
	if ( ! $row ) return;
	$css = trim( (string) ( $row->css ?? '' ) );
	if ( '' !== $css ) {
		echo "\n<style id=\"ah-custom-css-" . esc_attr( $slug ) . "\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}, 99 );

add_action( 'wp_footer', static function (): void {
	if ( is_admin() ) return;
	$slug = ah_custom_code_current_slug();
	if ( '' === $slug ) return;
	$row = ah_custom_code_get( $slug );
	if ( ! $row ) return;
	$js = trim( (string) ( $row->js ?? '' ) );
	if ( '' !== $js ) {
		echo "\n<script id=\"ah-custom-js-" . esc_attr( $slug ) . "\">\n(function(){\n" . $js . "\n})();\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}, 99 );

// ── Custom Code AJAX handlers (must be in main file - admin-ajax.php doesn't load admin pages) ──
add_action( 'wp_ajax_ah_save_custom_code', static function (): void {
	if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ) );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Access denied.' ) );
	}

	global $wpdb;
	$table  = $wpdb->prefix . 'ah_custom_code';
	$id     = (int) ( $_POST['entry_id'] ?? 0 );
	$slug   = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
	$css    = wp_unslash( $_POST['css'] ?? '' );
	$js     = wp_unslash( $_POST['js']  ?? '' );

	if ( '' === $slug ) {
		wp_send_json_error( array( 'message' => 'Page slug is required.' ) );
	}

	if ( 0 === $id ) {
		// Check uniqueness
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s LIMIT 1", $slug ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $exists ) {
			wp_send_json_error( array( 'message' => 'A rule for "' . $slug . '" already exists - edit it instead.' ) );
		}
		$wpdb->insert( $table, array( 'slug' => $slug, 'css' => $css, 'js' => $js, 'is_active' => 1 ),
			array( '%s', '%s', '%s', '%d' ) );
		$id = (int) $wpdb->insert_id;
	} else {
		$wpdb->update( $table, array( 'slug' => $slug, 'css' => $css, 'js' => $js ),
			array( 'id' => $id ), array( '%s', '%s', '%s' ), array( '%d' ) );
	}

	wp_send_json_success( array(
		'message'  => 'Saved.',
		'id'       => $id,
		'redirect' => admin_url( 'admin.php?page=ah-custom-code&edit=' . $id ),
	) );
} );

add_action( 'wp_ajax_ah_delete_custom_code', static function (): void {
	if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ) );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Access denied.' ) );
	}
	global $wpdb;
	$table = $wpdb->prefix . 'ah_custom_code';
	$id    = (int) ( $_POST['entry_id'] ?? 0 );
	$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
	wp_send_json_success( array( 'message' => 'Deleted.' ) );
} );

add_action( 'wp_ajax_ah_toggle_custom_code', static function (): void {
	if ( ! check_ajax_referer( 'ah_custom_code', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ) );
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Access denied.' ) );
	}
	global $wpdb;
	$table = $wpdb->prefix . 'ah_custom_code';
	$id    = (int) ( $_POST['entry_id'] ?? 0 );
	$row   = $wpdb->get_row( $wpdb->prepare( "SELECT is_active FROM `{$table}` WHERE id = %d LIMIT 1", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	if ( ! $row ) { wp_send_json_error( array( 'message' => 'Not found.' ) ); }
	$new = $row->is_active ? 0 : 1;
	$wpdb->update( $table, array( 'is_active' => $new ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );
	wp_send_json_success( array( 'active' => $new ) );
} );

// ── REST API Endpoint for Analytics Reports ────────────────────────────────────
add_action( 'rest_api_init', function () {
	register_rest_route( 'ah-analytics/v1', '/report/(?P<id>\d+)', array(
		'methods'             => 'GET',
		'callback'            => function ( WP_REST_Request $request ) {
			$id = (int) $request->get_param( 'id' );
			$report = ( new AH_Analytics_Report_Model() )->find( $id );

			if ( ! $report ) {
				return new WP_Error( 'not_found', 'Report not found.', array( 'status' => 404 ) );
			}

			if ( ( $report->api_visibility ?? 'private' ) === 'private' ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					return new WP_Error( 'forbidden', 'You do not have permission to view this report.', array( 'status' => 403 ) );
				}
			}

			global $wpdb;
			$results = [];

			if ( ( $report->report_type ?? 'sql' ) === 'sql' ) {
				$sql = trim( $report->query_sql ?? '' );
				if ( ! $sql ) {
					return new WP_Error( 'empty_query', 'SQL query is empty.', array( 'status' => 500 ) );
				}
				
				// Apply same validation as the AJAX runner for safety
				$err = AH_Analytics_Ajax::validate_query( $sql );
				if ( $err ) {
					return new WP_Error( 'invalid_query', $err, array( 'status' => 400 ) );
				}

				$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				if ( $wpdb->last_error ) {
					return new WP_Error( 'db_error', $wpdb->last_error, array( 'status' => 500 ) );
				}
			} else {
				$php = trim( $report->query_php ?? '' );
				if ( ! $php ) {
					return new WP_Error( 'empty_query', 'PHP code is empty.', array( 'status' => 500 ) );
				}
				try {
					ob_start();
					$php_res = eval( $php );
					ob_end_clean();
					if ( is_array( $php_res ) ) {
						$results = $php_res;
					} else {
						return new WP_Error( 'invalid_return', 'PHP code must return an array.', array( 'status' => 500 ) );
					}
				} catch ( \Throwable $e ) {
					if ( ob_get_level() ) ob_end_clean();
					return new WP_Error( 'php_error', 'PHP Error: ' . $e->getMessage(), array( 'status' => 500 ) );
				}
			}

			// Do not log REST API runs in analytics_results to prevent bloating DB from frequent API hits
			( new AH_Analytics_Report_Model() )->bump_run_count( $id );

			return rest_ensure_response( array(
				'report_name' => $report->name,
				'row_count'   => count( $results ?: [] ),
				'data'        => $results ?: [],
			) );
		},
		'permission_callback' => '__return_true', // We handle permissions in the callback based on report visibility
	) );
} );

// ── Global Settings: Disable Optimized Images ────────────────────────────────
add_filter( 'big_image_size_threshold', function ( $threshold, $imagesize, $file, $attachment_id ) {
	if ( get_option( 'ah_disable_optimized_images', '0' ) === '1' ) {
		return false; // Return false to disable scaling down large images
	}
	return $threshold;
}, 10, 4 );


