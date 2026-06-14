<?php
function ahn_include_files() {
    // rules_conditions.php and the core_* files are already loaded early in functions.php.
    $files = array(
        '/common/common_functions.php',
        '/admin/schema-installer.php', // ADN_Schema - needed at REST time too, not only wp-admin
        '/apis/services.php',          // data services (JSON today, real API later) + adn_link()
        '/apis/services_cms.php',      // read-only services backed by the CMS plugin DB (taxonomy tree + posts)
        '/calculators/calculators.php',// [ah_calculator] shortcode + isolated (iframe) calculator renderer
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
        'adn-chrome-style' => '/assets/css/chrome.css', // design tokens + header/nav/search/footer
        'adn-main-style' => '/assets/css/main.css',
        'adn-common-style' => '/assets/css/common.css',
        'adn-components-style' => '/assets/css/components.css',
        'adn-temp-style' => '/assets/css/temp.css',
        'adn-builded-style' => '/assets/css/builded.css',
    );
    foreach ( $styles as $handle => $file ) {
        wp_enqueue_style( $handle, ADN_THEME_URI . $file, array(), ADN_THEME_VERSION );
    }
    // Font Awesome 6 (free) - powers adn_icon() across the theme.
    wp_enqueue_style( 'adn-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
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
        'pages/page-newsall.php' => array(
            'css' => '/assets/css/news.css',
            'js'  => '/assets/js/news.js',
        ),
        'pages/page-guides_listing.php' => array(
            'css' => '/assets/css/guides_listing.css',
            'js'  => '/assets/js/guides_listing.js',
        ),
        'pages/page-calculator.php' => array(
            'css' => '/assets/css/calculators.css',
            'js'  => '/assets/js/calculators.js',
        ),
        'pages/page-guidance.php' => array(
            'css' => '/assets/css/guidance.css',
        ),
        'pages/page-ask-expert.php' => array(
            'css' => '/assets/css/ask_expert.css',
            'js'  => '/assets/js/ask_expert.js',
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
    if ( is_single() ) {
        if ( file_exists( ADN_THEME_DIR . '/assets/css/single.css' ) ) {
            wp_enqueue_style( 'adn-single-style', ADN_THEME_URI . '/assets/css/single.css', array(), ADN_THEME_VERSION );
        }
        if ( file_exists( ADN_THEME_DIR . '/assets/js/single.js' ) ) {
            wp_enqueue_script( 'adn-single-script', ADN_THEME_URI . '/assets/js/single.js', array(), ADN_THEME_VERSION, true );
            wp_localize_script( 'adn-single-script', 'adnComments', array(
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'submitNonce'   => wp_create_nonce( 'adn_comment_nonce' ),
                'loadNonce'     => wp_create_nonce( 'adn_load_comments' ),
                'postId'        => (int) get_queried_object_id(),
                'perPage'       => 10,
                'i18n'          => array(
                    'posting'   => __( 'Posting…', ADN_TEXT_DOMAIN ),
                    'loading'   => __( 'Loading…', ADN_TEXT_DOMAIN ),
                    'loadMore'  => __( 'Load more comments', ADN_TEXT_DOMAIN ),
                    'noMore'    => __( 'All comments loaded', ADN_TEXT_DOMAIN ),
                ),
            ) );
        }
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
        'buying' => array(
            'title'    => 'Buying',
            'template' => 'pages/page-category_guide.php',
        ),
        'news' => array(
            'title'    => 'News & Insights',
            'template' => 'pages/page-newsall.php',
        ),
        'buying-guides' => array(
            'title'    => 'Buying Guides',
            'template' => 'pages/page-guides_listing.php',
        ),
        'calculators' => array(
            'title'    => 'Calculators',
            'template' => 'pages/page-calculator.php',
        ),
        'guidance' => array(
            'title'    => 'Get Expert Guidance',
            'template' => 'pages/page-contact.php',
            // 'template' => 'pages/page-guidance.php',
        ),
        'ask-an-expert' => array(
            'title'    => 'Ask an Expert',
            'template' => 'pages/page-ask-expert.php',
        ),
        'ask-expert' => array(
            'title'    => 'Ask an Expert',
            'template' => 'pages/page-ask-expert.php',
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

    // Make the Home page the site's default landing page (static front page).
    adn_set_home_as_front_page();

    return $created;
}

/**
 * Set the "Home" page as the WordPress static front page, so visiting the site
 * root ("/") shows it (rendered with its pages/page-home.php template) instead
 * of the latest-posts blog index.
 *
 * Idempotent: only writes the reading options when they are not already set,
 * so it is safe to call on every theme (re)activation.
 */
function adn_set_home_as_front_page() {
    $home = get_page_by_path( 'home' );
    if ( ! ( $home instanceof WP_Post ) ) {
        return;
    }
    if ( 'page' !== get_option( 'show_on_front' ) ) {
        update_option( 'show_on_front', 'page' );
    }
    if ( (int) get_option( 'page_on_front' ) !== (int) $home->ID ) {
        update_option( 'page_on_front', (int) $home->ID );
    }
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