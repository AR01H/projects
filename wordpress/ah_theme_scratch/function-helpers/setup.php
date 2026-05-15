<?php
/**
 * scratch/function-helpers/setup.php
 */

function mytheme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
}
add_action('after_setup_theme', 'mytheme_setup');

// Add specific body classes
function ah_theme_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'has-fixed-ticker';
    }
    return $classes;
}
add_filter('body_class', 'ah_theme_body_classes');

/**
 * Output Header/Footer Scripts from Settings
 */
function ah_scratch_output_header_scripts() {
    echo get_option('ah_header_code', '');
}
add_action('wp_head', 'ah_scratch_output_header_scripts', 100);

function ah_scratch_output_footer_scripts() {
    echo get_option('ah_footer_code', '');
}
add_action('wp_footer', 'ah_scratch_output_footer_scripts', 100);