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
define( 'AH_PLUGIN_VERSION', '1.0.6' );
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
AH_Autoloader::register();

// ── Components ───────────────────────────────────────────────────────────────
require_once AH_PLUGIN_DIR . '/components/toaster/index.php';

// ── Admin portal ─────────────────────────────────────────────────────────────
if ( is_admin() ) {
	AH_Admin_Bootstrap::init();
}

// ── Public AJAX (form builder frontend - works for logged-in and guests) ─────
AH_Ajax_Handlers::init_public();

// ── Shortcodes ───────────────────────────────────────────────────────────────
add_action( 'init', static function () {
	add_shortcode( 'ah_form', array( 'AH_Form_Builder', 'render' ) );
	add_shortcode( 'ah_related_links', 'ah_render_related_links_shortcode' );
	add_shortcode( 'ah_static_page', 'ah_render_static_page_shortcode' );
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

// ── Database ─────────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, array( 'AH_DB_Installer', 'install' ) );
add_action( 'wp_loaded', array( 'AH_DB_Installer', 'maybe_upgrade' ) );

// ── Rules Engine: async cron processor ───────────────────────────────────────
// evaluate() queues actions as 'pending' in ah_trigger_logs; this cron fires
// them in the background every minute (pending + failed retries).

add_filter( 'cron_schedules', static function ( array $s ): array {
	if ( ! isset( $s['ah_every_minute'] ) ) {
		$s['ah_every_minute'] = array(
			'interval' => 60,
			'display'  => 'Every Minute (AH Rules Engine)',
		);
	}
	return $s;
} );

add_action( 'ah_rules_cron_process', array( 'AH_Rules_Engine', 'cron_process' ) );

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
