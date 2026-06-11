<?php
/**
 * includes/core_settings.php — Central Configuration
 *
 * RULE: All "what" is defined here as arrays.
 *       Loaders read this and register everything.
 *       Never put hooks or logic here.
 *
 * @return array
 */

defined( 'ABSPATH' ) || exit;

return [

    // ── Theme meta ────────────────────────────────────────────────
    'name'       => 'New Project Theme',
    'version'    => '1.0.0',
    'textdomain' => 'npt',

    // ── Front-end assets ──────────────────────────────────────────
    // key = wp_enqueue handle, value = path relative to theme root
    'assets' => [
        'styles' => [
            'npt-main'  => '/assets/css/main.css',
            'npt-icons' => '/assets/css/icons.css',
        ],
        'scripts' => [
            'npt-main'   => '/assets/js/main.js',
            'npt-vendor' => '/assets/js/vendor.js',
        ],
    ],

    // ── Navigation menus ──────────────────────────────────────────
    'menus' => [
        'primary' => 'Primary Navigation',
        'footer'  => 'Footer Links',
        'social'  => 'Social Media Links',
    ],

    // ── Sidebar / widget areas ────────────────────────────────────
    'sidebars' => [
        [
            'id'          => 'sidebar-main',
            'name'        => 'Main Sidebar',
            'description' => 'Widgets shown in the right column.',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ],
    ],

    // ── Custom Post Types ─────────────────────────────────────────
    'cpt' => [
        [
            'slug'        => 'portfolio',
            'singular'    => 'Portfolio',
            'plural'      => 'Portfolios',
            'icon'        => 'dashicons-portfolio',
            'supports'    => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'has_archive' => true,
            'public'      => true,
        ],
        // add more CPTs here …
    ],

    // ── Taxonomies ────────────────────────────────────────────────
    'taxonomies' => [
        [
            'slug'        => 'portfolio-category',
            'singular'    => 'Portfolio Category',
            'plural'      => 'Portfolio Categories',
            'post_types'  => [ 'portfolio' ],
            'hierarchical' => true,
        ],
        // add more taxonomies here …
    ],

    // ── REST API namespace ────────────────────────────────────────
    'api_namespace' => 'npt/v1',

    // ── AJAX actions map  (action_name => handler_function) ───────
    'ajax_actions' => [
        'npt_load_more'  => 'npt_ajax_load_more',
        'npt_submit_form' => 'npt_ajax_submit_form',
    ],

];
