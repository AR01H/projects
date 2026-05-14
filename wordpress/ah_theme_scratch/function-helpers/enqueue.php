<?php

function mytheme_enqueue() {
    // Original theme style
    wp_enqueue_style(
        'mytheme-style',
        get_stylesheet_uri(),
        [],
        filemtime(get_stylesheet_directory() . '/style.css')
    );
    
    // Check and enqueue app.css if it exists
    if (file_exists(get_template_directory() . '/assets/css/app.css')) {
        wp_enqueue_style(
            'theme-app',
            mytheme_css('app.css'),
            [],
            filemtime(get_template_directory() . '/assets/css/app.css')
        );
    }

    // Main static site CSS
    wp_enqueue_style(
        'ah-main-css',
        mytheme_css('main.css'),
        [],
        filemtime(get_template_directory() . '/assets/css/main.css')
    );

    // Common standard CSS
    wp_enqueue_style(
        'ah-common-css',
        mytheme_css('commonstandard.css'),
        [],
        filemtime(get_template_directory() . '/assets/css/commonstandard.css')
    );

    // Main JS components
    wp_enqueue_script(
        'ah-components-js',
        mytheme_js('components.js'),
        [],
        filemtime(get_template_directory() . '/assets/js/components.js'),
        true // Load in footer
    );
}

add_action('wp_enqueue_scripts', 'mytheme_enqueue');

function mytheme_admin_scripts($hook) {
    if (strpos($hook, 'advaith-homes') === false && strpos($hook, 'ah-settings') === false && strpos($hook, 'ah-manager') === false) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style(
        'ah-admin-style',
        get_template_directory_uri() . '/assets/css/admin-style.css',
        [],
        filemtime(get_template_directory() . '/assets/css/admin-style.css')
    );
}
add_action('admin_enqueue_scripts', 'mytheme_admin_scripts');