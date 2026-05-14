<?php

function mytheme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
}

add_action('after_setup_theme', 'mytheme_setup');

// Add specific body classes
function ah_theme_body_classes($classes) {
    // Add class if it's the front page and we have the fixed ticker
    if (is_front_page()) {
        $classes[] = 'has-fixed-ticker';
    }
    return $classes;
}
add_filter('body_class', 'ah_theme_body_classes');