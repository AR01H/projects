<?php
function ahn_include_files() {
    // rules_conditions.php and the core_* files are already loaded early in functions.php.
    $files = array(
        '/common/common_functions.php',
        '/admin/schema-installer.php', // ADN_Schema - needed at REST time too, not only wp-admin
        '/apis/models/post.php',       // API models must load before the routes that use them
        '/apis/fetch_functions.php',   // REST route registration + callbacks
        '/apis/callbacks.php',
    );
    foreach ( $files as $file ) {
        $path = ADN_THEME_DIR . $file;
        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
}
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
function adn_enqueue_common_js() {
    $scripts = array(
        'adn-main-script' => '/assets/js/main.js',
        'adn-common-script' => '/assets/js/common.js',
        'adn-scroll-to-top-script' => '/assets/js/scroll-to-top.js',
        'adn-form-builder-script' => '/assets/js/form-builder.js',
    );
    foreach ( $scripts as $handle => $file ) {
        wp_enqueue_script( $handle, ADN_THEME_URI . $file, array( 'jquery' ), ADN_THEME_VERSION, true );
    }
}
function adn_enqueue_template_specific_assets() {
    // Keys must match the real page-template paths (the same ones used in adn_get_page_definitions()).
    $template_assets = array(
        'pages/page-home.php' => array(
            'css' => '/assets/css/home.css',
            'js'  => '/assets/js/home.js',
        ),
        'pages/page-contact.php' => array(
            'css' => '/assets/css/contact.css',
            'js'  => '/assets/js/contact.js',
        ),
    );
    foreach ( $template_assets as $template => $assets ) {
        if ( ! is_page_template( $template ) ) {
            continue;
        }
        $handle = 'adn-' . basename( $template, '.php' );
        // Only enqueue files that actually exist, so missing assets never 404.
        if ( ! empty( $assets['css'] ) && file_exists( ADN_THEME_DIR . $assets['css'] ) ) {
            wp_enqueue_style( $handle . '-style', ADN_THEME_URI . $assets['css'], array(), ADN_THEME_VERSION );
        }
        if ( ! empty( $assets['js'] ) && file_exists( ADN_THEME_DIR . $assets['js'] ) ) {
            wp_enqueue_script( $handle . '-script', ADN_THEME_URI . $assets['js'], array( 'jquery' ), ADN_THEME_VERSION, true );
        }
    }
    if ( is_single() && file_exists( ADN_THEME_DIR . '/assets/css/single.css' ) ) {
        wp_enqueue_style( 'adn-single-style', ADN_THEME_URI . '/assets/css/single.css', array(), ADN_THEME_VERSION );
    }
}
function adn_get_page_definitions() {
    return array(
        'home' => array(
            'title'    => 'Home',
            'template' => 'pages/page-home.php',
        ),
        'contact' => array(
            'title'    => 'Contact Us',
            'template' => 'pages/page-contact.php',
        ),
        'allinone' => array(
            'title'    => 'All In One (Demo)',
            'template' => 'pages/page-allinone.php',
        ),
        // Slug must match COMING_SOON_PAGE_SLUG so the coming-soon redirect target exists.
        COMING_SOON_PAGE_SLUG => array(
            'title'    => 'Coming Soon',
            'template' => 'pages/page-coming.php',
        ),
    );
}

function adn_create_default_pages() {

    if ( ! is_admin() ) {
        return 0;
    }

    $pages   = adn_get_page_definitions();
    $created = 0;

    foreach ( $pages as $slug => $page ) {

        $existing_page = get_page_by_path( $slug );

        if ( ! $existing_page ) {

            $page_id = wp_insert_post( array(
                'post_title'   => $page['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ) );

            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, '_wp_page_template', $page['template'] );
                $created++;
            }
        }
    }

    return $created;
}

function adn_theme_register() {
    $supports = array(
        'title-tag',
        'post-thumbnails',
        'custom-logo',
    );
    foreach ( $supports as $feature ) {
        add_theme_support( $feature );
    }
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    $menus = array(
        'primary' => __( 'Primary Menu', ADN_TEXT_DOMAIN ),
        'footer'  => __( 'Footer Menu', ADN_TEXT_DOMAIN ),
    );
    register_nav_menus( $menus );
    $image_sizes = array(
        'adn-thumbnail' => array( 400, 300, true ),
    );
    foreach ( $image_sizes as $name => $size ) {
        add_image_size( $name, $size[0], $size[1], $size[2] );
    }
}

function adn_check_coming_soon() {
    if ( ! defined( 'COMING_SOON' ) || COMING_SOON !== true ) {
        return;
    }
    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }
    $coming_soon_url = home_url( '/' . COMING_SOON_PAGE_SLUG . '/' );
    if ( is_page( COMING_SOON_PAGE_SLUG ) ) {
        return;
    }
    wp_redirect( $coming_soon_url, 302 );
    exit;
}