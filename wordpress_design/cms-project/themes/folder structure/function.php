<?php
/**
 * functions.php - Theme Bootstrap / Loader
 *
 * RULE: This file ONLY loads other files.
 *       NO business logic lives here.
 */

defined( 'ABSPATH' ) || exit;

// ─────────────────────────────────────────────
// 0. Static constants - MUST be first
// ─────────────────────────────────────────────
require_once __DIR__ . '/static/page-sample.php';

// ─────────────────────────────────────────────
// 1. Load central config (returns an array)
// ─────────────────────────────────────────────
$GLOBALS['theme_config'] = require __DIR__ . '/includes/core_settings.php';

// ─────────────────────────────────────────────
// 2. Helper: load every *.php in a folder
// ─────────────────────────────────────────────
function theme_load_dir( string $dir ): void {
    foreach ( glob( $dir . '/*.php' ) ?: [] as $file ) {
        require_once $file;
    }
}

// ─────────────────────────────────────────────
// 3. Load modules in dependency order
// ─────────────────────────────────────────────
theme_load_dir( __DIR__ . '/common' );          // shared helper functions
theme_load_dir( __DIR__ . '/includes' );        // setup, CPTs, hooks, filters
theme_load_dir( __DIR__ . '/middleware' );      // request/auth middleware
theme_load_dir( __DIR__ . '/admin' );           // admin-only features
theme_load_dir( __DIR__ . '/apis' );            // REST routes & AJAX handlers

// ─────────────────────────────────────────────
// 4. Enqueue assets from config map
// ─────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'theme_enqueue_assets' );
function theme_enqueue_assets(): void {
    $cfg = $GLOBALS['theme_config'];
    $uri = get_template_directory_uri();
    $ver = $cfg['version'];

    foreach ( $cfg['assets']['styles'] as $handle => $path ) {
        wp_enqueue_style( $handle, $uri . $path, [], NPT_VERSION );
    }
    foreach ( $cfg['assets']['scripts'] as $handle => $path ) {
        wp_enqueue_script( $handle, $uri . $path, [ 'jquery' ], NPT_VERSION, true );
    }
}
