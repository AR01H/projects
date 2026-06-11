<?php

// ===========================
// THEME CONSTANTS
// ===========================
require_once get_template_directory() . '/includes/core_terms.php';
require_once get_template_directory() . '/includes/core_settings.php';
require_once get_template_directory() . '/includes/core_info.php';
require_once get_template_directory() . '/includes/rules_conditions.php';

// ===========================
// LOAD HELPER FUNCTIONS
// ===========================
require_once ADN_THEME_DIR . '/explode_function.php';

// ===========================
// HOOKS
// ===========================
add_action( 'after_setup_theme', 'ahn_include_files' );
add_action( 'after_setup_theme', 'adn_theme_register' );

add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_css' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_common_js' );
add_action( 'wp_enqueue_scripts', 'adn_enqueue_template_specific_assets' );