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
        'adn-varaibles-style'  => '/assets/css/variables.css',
        'adn-chrome-style'     => '/assets/css/chrome.css',
        'adn-main-style'       => '/assets/css/main.css',
        'adn-common-style'     => '/assets/css/common.css',
        'adn-components-style' => '/assets/css/components.css',
        'adn-temp-style'       => '/assets/css/temp.css',
        'adn-builded-style'    => '/assets/css/builded.css',
        'adn-utils-style'      => '/assets/css/common_utils.css',
        'adn-fa-style'         => '/assets/css/fastyles.css',
        'adn-premium-style'       => '/assets/css/premium_styles.css',
        'adn-cookie-consent-style' => '/assets/css/cookie-consent.css',
    );
    foreach ( $styles as $handle => $file ) {
        $path = ADN_THEME_DIR . $file;
        $ver  = file_exists( $path ) ? filemtime( $path ) : ADN_THEME_VERSION;
        wp_enqueue_style( $handle, ADN_THEME_URI . $file, array(), $ver );
    }
    // Font Awesome 6 (free) - powers adn_icon() across the theme.
    wp_enqueue_style( 'adn-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
}
function adn_enqueue_common_js() {
    $scripts = array(
        'adn-utils-script'         => '/assets/js/common_utils.js',
        'adn-main-script'          => '/assets/js/main.js',
        'adn-common-script'        => '/assets/js/common.js',
        'adn-scroll-to-top-script' => '/assets/js/scroll-to-top.js',
        'adn-form-builder-script'   => '/assets/js/form-builder.js',
        'adn-cookie-consent-script' => '/assets/js/cookie-consent.js',
    );
    foreach ( $scripts as $handle => $file ) {
        wp_enqueue_script( $handle, ADN_THEME_URI . $file, array( 'jquery' ), ADN_THEME_VERSION, true );
    }
    /* Pass cookie policy page URL + page context to the consent banner. */
    wp_localize_script( 'adn-cookie-consent-script', 'adnConsentCfg', array(
        'policyUrl'          => home_url( '/cookie-policy/' ),
        'isCookiePolicyPage' => is_page( 'cookie-policy' ) ? 1 : 0,
    ) );
    wp_add_inline_script(
        'adn-utils-script',
        'window.adnSite=' . wp_json_encode( array(
            'visitorsUrl' => rest_url( 'adn/v1/visitors' ),
            'pingUrl'     => rest_url( 'adn/v1/visitors/ping' ),
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
        ) ) . ';',
        'before'
    );
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
        'pages/page-guides.php' => array(
            'css' => '/assets/css/guides_listing.css',
            'js'  => '/assets/js/guides_listing.js',
        ),
        'pages/page-tools.php' => array(
            'css' => '/assets/css/tools.css',
            'js'  => '/assets/js/tools.js',
        ),
        'pages/page-guidance.php' => array(
            'css' => '/assets/css/guidance.css',
            'js'  => '/assets/js/guidance.js',
        ),
        'pages/page-ask-expert.php' => array(
            'css' => '/assets/css/ask_expert.css',
            'js'  => '/assets/js/ask_expert.js',
        ),
        'pages/page-faqs.php' => array(
            'css' => '/assets/css/faqs.css',
            'js'  => '/assets/js/faqs.js',
        ),
    );
    $virtual_tpl = (string) get_query_var( 'adn_virtual_template', '' );

    foreach ( $template_assets as $template => $assets ) {
        $base_name = basename( $template, '.php' );
        $is_active = is_page_template( $template ) || ( '' !== $virtual_tpl && $base_name === $virtual_tpl );
        if ( ! $is_active ) {
            continue;
        }
        $handle = 'adn-' . $base_name;
        // Only enqueue files that actually exist, so missing assets never 404.
        if ( ! empty( $assets['css'] ) && file_exists( ADN_THEME_DIR . $assets['css'] ) ) {
            wp_enqueue_style( $handle . '-style', ADN_THEME_URI . $assets['css'], array(), filemtime( ADN_THEME_DIR . $assets['css'] ) );
        }
        if ( ! empty( $assets['js'] ) && file_exists( ADN_THEME_DIR . $assets['js'] ) ) {
            wp_enqueue_script( $handle . '-script', ADN_THEME_URI . $assets['js'], array( 'jquery' ), filemtime( ADN_THEME_DIR . $assets['js'] ), true );
        }
    }
    // Per-page nonce vars for legacy AJAX forms (contact, guidance still use admin-ajax.php).
    $current_template = get_page_template_slug();
    if ( in_array( $current_template, array( 'pages/page-contact.php', 'pages/page-guidance.php' ), true ) ) {
        $enq_handle = 'adn-' . basename( $current_template, '.php' ) . '-script';
        wp_localize_script( $enq_handle, 'adnEnquiry', array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'ah_enquiry_nonce' ),
            'restBase'  => rest_url( ADN_API_NS ),
            'restNonce' => wp_create_nonce( 'wp_rest' ),
        ) );
    }

    // Ask an Expert page: nonces must be localized here (inside wp_enqueue_scripts) because
    // the template calls wp_localize_script before this hook fires, so the handle isn't
    // registered yet and the call silently fails, leaving adnExpert undefined.
    if ( 'pages/page-ask-expert.php' === $current_template ) {
        wp_localize_script( 'adn-page-ask-expert-script', 'adnExpert', array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'adn_expert_contact' ),
            'unlockNonce' => wp_create_nonce( 'adn_expert_unlock' ),
        ) );
    }

    if ( is_single() ) {
        if ( file_exists( ADN_THEME_DIR . '/assets/css/single.css' ) ) {
            wp_enqueue_style( 'adn-single-style', ADN_THEME_URI . '/assets/css/single.css', array(), filemtime( ADN_THEME_DIR . '/assets/css/single.css' ) );
        }
        if ( file_exists( ADN_THEME_DIR . '/assets/css/article.css' ) ) {
            wp_enqueue_style( 'adn-article-style', ADN_THEME_URI . '/assets/css/article.css', array(), filemtime( ADN_THEME_DIR . '/assets/css/article.css' ) );
        }
        if ( file_exists( ADN_THEME_DIR . '/assets/css/article_cardner.css' ) ) {
            wp_enqueue_style( 'adn-cardner-style', ADN_THEME_URI . '/assets/css/article_cardner.css', array(), filemtime( ADN_THEME_DIR . '/assets/css/article_cardner.css' ) );
        }
        if ( file_exists( ADN_THEME_DIR . '/assets/js/single.js' ) ) {
            wp_enqueue_script( 'adn-single-script', ADN_THEME_URI . '/assets/js/single.js', array(), filemtime( ADN_THEME_DIR . '/assets/js/single.js' ), true );
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
        trim( SITE_HOME_URL, '/' ) ?: 'home' => array(
            'title'    => PAGE_TITLE_HOME,
            'template' => 'pages/page-home.php',
        ),
        trim( SITE_CONTACT_URL, '/' ) => array(
            'title'    => PAGE_TITLE_CONTACT,
            'template' => 'pages/page-contact.php',
        ),
        trim( SITE_NEWS_URL, '/' ) => array(
            'title'    => PAGE_TITLE_NEWS,
            'template' => 'pages/page-newsall.php',
        ),
        trim( SITE_GUIDES_URL, '/' ) => array(
            'title'    => PAGE_TITLE_GUIDES,
            'template' => 'pages/page-guides.php',
        ),
        trim( SITE_TOOLS_URL, '/' ) => array(
            'title'    => PAGE_TITLE_TOOLS,
            'template' => 'pages/page-tools.php',
        ),
        trim( SITE_GUIDANCE_URL, '/' ) => array(
            'title'    => PAGE_TITLE_GUIDANCE,
            'template' => 'pages/page-guidance.php',
        ),
        trim( SITE_EXPERT_URL, '/' ) => array(
            'title'    => PAGE_TITLE_EXPERT,
            'template' => 'pages/page-ask-expert.php',
            'aliases'  => array( 'ask-an-expert' ),
        ),
        trim( SITE_FAQS_URL, '/' ) => array(
            'title'    => PAGE_TITLE_FAQS,
            'template' => 'pages/page-faqs.php',
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

        // Build the full list: primary slug + any aliases.
        $all_slugs = array_merge(
            array( $slug ),
            isset( $page['aliases'] ) && is_array( $page['aliases'] ) ? $page['aliases'] : array()
        );

        foreach ( $all_slugs as $_slug ) {
            if ( get_page_by_path( $_slug ) ) {
                continue; // already exists
            }

            $page_id = wp_insert_post( array(
                'post_title'   => $page['title'],
                'post_name'    => $_slug,
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