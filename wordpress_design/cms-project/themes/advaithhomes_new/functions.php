<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Block direct file access.
}

// ===========================
// THEME CONSTANTS
// ===========================
require_once get_template_directory() . '/includes/core_terms.php';
require_once get_template_directory() . '/includes/core_settings.php';
require_once get_template_directory() . '/includes/core_info.php';
require_once get_template_directory() . '/includes/rules_conditions.php';
require_once get_template_directory() . '/includes/core_routing.php';
require_once get_template_directory() . '/includes/class-category-settings.php';

// ===========================
// LOAD HELPER FUNCTIONS
// ===========================
require_once ADN_THEME_DIR . '/explode_function.php';

// ===========================
// DATA LOADERS (csv / json / html / pdf)
// ===========================
require_once ADN_THEME_DIR . '/includes/data_fetcher/class-real-loader.php';

// ===========================
// ADMIN (tabs + subtabs page)
// ===========================
if ( is_admin() ) {
    require_once ADN_THEME_DIR . '/admin/class-theme-admin.php';
    ADN_Theme_Admin::init();
}

// ===========================
// HOOKS
// ===========================
add_action( 'after_setup_theme', 'ahn_include_files' );
add_action( 'after_setup_theme', 'adn_theme_register' );

// Create default pages only once, when the theme is activated, instead of on every admin load.
add_action( 'after_switch_theme', 'adn_create_default_pages' );

// Install category settings DB table on theme activation and lazily on admin load.
add_action( 'after_switch_theme', array( 'AH_Category_Settings', 'install' ) );
add_action( 'admin_init',         array( 'AH_Category_Settings', 'maybe_install' ) );

// Persist the language choice early, before any template output starts.
add_action( 'init', 'adn_set_language_cookie' );

add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_css' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_js' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_template_specific_assets' );
add_action( 'template_redirect', 'adn_check_coming_soon' );

// ===========================
// SHORTCODES
// ===========================
add_shortcode( 'adn_cat_calculators', 'adn_shortcode_cat_calculators' );

/**
 * [adn_cat_calculators slug="buying"]
 * Renders a grid of calculator cards for the selected keys stored in AH_Category_Settings.
 */
function adn_shortcode_cat_calculators( $atts ) {
	$atts = shortcode_atts( array( 'slug' => '' ), $atts, 'adn_cat_calculators' );
	$slug = sanitize_key( $atts['slug'] );
	if ( ! $slug || ! class_exists( 'AH_Category_Settings' ) || ! function_exists( 'adn_calculators' ) ) {
		return '';
	}
	$calc_d       = AH_Category_Settings::get( $slug, 'calculators' );
	$selected     = ! empty( $calc_d['selected_keys'] ) && is_array( $calc_d['selected_keys'] ) ? $calc_d['selected_keys'] : array();
	if ( empty( $selected ) ) { return ''; }
	$all_calcs    = adn_calculators();
	$calc_meta    = get_option( 'adn_calculators_meta', array() );
	$items        = array();
	foreach ( $selected as $key ) {
		$key = sanitize_key( $key );
		if ( ! isset( $all_calcs[ $key ] ) ) { continue; }
		$reg  = $all_calcs[ $key ];
		$meta = isset( $calc_meta[ $key ] ) && is_array( $calc_meta[ $key ] ) ? $calc_meta[ $key ] : array();
		$items[] = array(
			'icon' => ! empty( $reg['icon'] )  ? (string) $reg['icon']  : '🧮',
			'name' => ! empty( $reg['title'] ) ? (string) $reg['title'] : $key,
			'url'  => ! empty( $meta['guide_url'] ) ? (string) $meta['guide_url'] : '/calculators/?calc=' . rawurlencode( $key ),
		);
	}
	if ( empty( $items ) ) { return ''; }
	$heading = ! empty( $calc_d['heading'] ) ? (string) $calc_d['heading'] : '';
	ob_start();
	if ( $heading ) {
		echo '<div class="adn-cat-calc-heading">' . esc_html( $heading ) . '</div>';
	}
	echo '<div class="calc-grid calc-grid--7col">';
	foreach ( $items as $card ) {
		adn_component( 'cards/calc_card', array( 'card' => $card ) );
	}
	echo '</div>';
	return ob_get_clean();
}
