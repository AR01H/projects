<?php

/**
 * Include required theme files (custom post types, widgets, helpers, etc.)
 */
function ahn_include_files() {

    $files = array(
        '/includes/rules_conditions.php',
        '/common/common_functions.php'
    );

    foreach ( $files as $file ) {
        $path = ADN_THEME_DIR . $file;

        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
}

/**
 * Enqueue common CSS used across the entire site
 */
function adn_enqueue_common_css() {

    $styles = array(
        'adn-varaibles-style' => '/assets/css/variables.css',
        'adn-main-style' => '/assets/css/main.css',
        'adn-common-style' => '/assets/css/common.css',
        'adn-components-style' => '/assets/css/components.css',
    );

    foreach ( $styles as $handle => $file ) {
        wp_enqueue_style( $handle, ADN_THEME_URI . $file, array(), ADN_THEME_VERSION );
    }
}

/**
 * Enqueue common JS used across the entire site
 */
function adn_enqueue_common_js() {

    $scripts = array(
        'adn-main-script' => '/assets/js/main.js',
        'adn-common-script' => '/assets/js/common.js',
        'adn-scroll-to-top-script' => '/assets/js/scroll-to-top.js',
    );

    foreach ( $scripts as $handle => $file ) {
        wp_enqueue_script( $handle, ADN_THEME_URI . $file, array( 'jquery' ), ADN_THEME_VERSION, true );
    }
}

/**
 * Enqueue template-specific CSS and JS based on current page/template
 */
function adn_enqueue_template_specific_assets() {

    $template_assets = array(
        'pages/home.php' => array(
            'css' => '/assets/css/home.css',
            'js'  => '/assets/js/home.js',
        ),
        'pages/about.php' => array(
            'css' => '/assets/css/about.css',
            'js'  => '',
        ),
        'pages/contact.php' => array(
            'css' => '/assets/css/contact.css',
            'js'  => '/assets/js/contact.js',
        ),
    );

    foreach ( $template_assets as $template => $assets ) {

        if ( is_page_template( $template ) ) {

            if ( ! empty( $assets['css'] ) ) {
                wp_enqueue_style( 'adn-' . basename( $template, '.php' ) . '-style', ADN_THEME_URI . $assets['css'], array(), ADN_THEME_VERSION );
            }

            if ( ! empty( $assets['js'] ) ) {
                wp_enqueue_script( 'adn-' . basename( $template, '.php' ) . '-script', ADN_THEME_URI . $assets['js'], array( 'jquery' ), ADN_THEME_VERSION, true );
            }
        }
    }

    // Single post specific assets
    if ( is_single() ) {
        wp_enqueue_style( 'adn-single-style', ADN_THEME_URI . '/assets/css/single.css', array(), ADN_THEME_VERSION );
    }
}

/**
 * Page template definitions (used for reference / mapping)
 */
function adn_get_page_definitions() {

    return array(
        'home'    => 'pages/home.php',
        'about'   => 'pages/about.php',
        'contact' => 'pages/contact.php',
    );
}

/**
 * Register theme supports, menus, image sizes, etc.
 */
function adn_theme_register() {

    // Theme supports
    $supports = array(
        'title-tag',
        'post-thumbnails',
        'custom-logo',
    );

    foreach ( $supports as $feature ) {
        add_theme_support( $feature );
    }

    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

    // Nav menus
    $menus = array(
        'primary' => __( 'Primary Menu', ADN_THEME_NAME ),
        'footer'  => __( 'Footer Menu', ADN_THEME_NAME ),
    );

    register_nav_menus( $menus );

    // Image sizes
    $image_sizes = array(
        'adn-thumbnail' => array( 400, 300, true ),
    );

    foreach ( $image_sizes as $name => $size ) {
        add_image_size( $name, $size[0], $size[1], $size[2] );
    }
}