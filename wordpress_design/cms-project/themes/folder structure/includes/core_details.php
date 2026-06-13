<?php
/**
 * includes/core_details.php - Theme Setup (Supports, Menus, Sidebars)
 *
 * RULE: One add_action per concern.
 *       Everything driven by $cfg arrays - no hardcoded values.
 */

defined( 'ABSPATH' ) || exit;

$cfg = $GLOBALS['theme_config'];

// ── 1. Theme supports & textdomain ────────────────────────────────
add_action( 'after_setup_theme', function () use ( $cfg ): void {
    load_theme_textdomain( $cfg['textdomain'], get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style',
    ] );
} );

// ── 2. Register navigation menus (loop) ──────────────────────────
add_action( 'init', function () use ( $cfg ): void {
    register_nav_menus( $cfg['menus'] );
} );

// ── 3. Register sidebars (loop) ───────────────────────────────────
add_action( 'widgets_init', function () use ( $cfg ): void {
    foreach ( $cfg['sidebars'] as $sidebar ) {
        register_sidebar( $sidebar );
    }
} );
