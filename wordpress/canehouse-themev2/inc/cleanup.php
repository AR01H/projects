<?php
/**
 * inc/cleanup.php
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   WordPress installs with a lot of default stuff — Posts, Comments, Tags,
 *   Categories, Widgets, XML-RPC, emoji scripts etc. We don't need any of
 *   it for The Cane House. This file removes all of it cleanly.
 *
 * HOW IT WORKS:
 *   WordPress has "hooks" — points in the code where you can add or remove
 *   things. We use remove_menu_page() to hide admin menus, remove_action()
 *   to stop default scripts loading, and filters to disable features.
 *
 * SAFE TO USE:
 *   Nothing here deletes data. It only hides/disables things in the admin UI
 *   and stops unnecessary code from running on the frontend.
 * ─────────────────────────────────────────────────────────────────────────────
 */

if (!defined('ABSPATH')) exit;


// ── 1. REMOVE DEFAULT ADMIN MENU ITEMS ───────────────────────────────────────
// WHY: We use our own custom CPT menus. Default Posts, Comments, Pages
//      are not used in The Cane House theme.
// HOW: admin_menu hook fires when the admin sidebar is built.
//      Priority 999 = runs last, after everything else is registered.
add_action('admin_menu', function () {

    remove_menu_page('edit.php');                   // Posts
    remove_menu_page('edit-comments.php');          // Comments
    remove_menu_page('edit.php?post_type=page');    // Pages (we use /pages/ folder)

}, 999);


// ── 2. REMOVE ALL DEFAULT DASHBOARD WIDGETS ───────────────────────────────────
// WHY: Default widgets (Activity, Quick Draft, WP News) are irrelevant.
//      We replace the dashboard with our own Cane House overview.
// HOW: wp_dashboard_setup fires when dashboard is initialised.
//      We clear the entire $wp_meta_boxes['dashboard'] array.
add_action('wp_dashboard_setup', function () {
    global $wp_meta_boxes;
    $wp_meta_boxes['dashboard'] = [];
});


// ── 3. CLEAN UP ADMIN BAR ─────────────────────────────────────────────────────
// WHY: Admin bar shows WP logo, comment counter, new-content shortcuts.
//      None of these are relevant for this site.
// HOW: admin_bar_menu fires when the admin bar is built.
//      remove_node() hides specific items by their ID.
add_action('admin_bar_menu', function ($bar) {
    $bar->remove_node('wp-logo');       // WordPress logo + menu
    $bar->remove_node('comments');      // Comments counter
    $bar->remove_node('new-content');   // + New dropdown
    $bar->remove_node('updates');       // Updates notice
}, 999);


// ── 4. STRIP FRONTEND <head> JUNK ────────────────────────────────────────────
// WHY: WordPress adds many things to <head> that slow down the page
//      and expose version info. We remove them all.
// HOW: remove_action() unhooks functions that WP registers by default.
add_action('init', function () {
    remove_action('wp_head', 'wp_generator');               // removes <?php echo WP version 
    remove_action('wp_head', 'wlwmanifest_link');           // Windows Live Writer link
    remove_action('wp_head', 'rsd_link');                   // Really Simple Discovery link
    remove_action('wp_head', 'wp_shortlink_wp_head');       // short URL meta
    remove_action('wp_head', 'print_emoji_detection_script', 7); // emoji JS
    remove_action('wp_print_styles', 'print_emoji_styles'); // emoji CSS
    remove_action('wp_head', 'feed_links', 2);              // RSS feed links
    remove_action('wp_head', 'feed_links_extra', 3);        // extra RSS feed links
    remove_action('wp_head', 'rest_output_link_wp_head');   // REST API link
    remove_action('wp_head', 'wp_oembed_add_discovery_links'); // oEmbed links
});


// ── 5. DISABLE COMMENTS ENTIRELY ─────────────────────────────────────────────
// WHY: The Cane House does not use comments on any post type.
// HOW: admin_init removes comment support from all post types.
//      Filters close comments and return empty arrays.
add_action('admin_init', function () {
    foreach (get_post_types() as $pt) {
        if (post_type_supports($pt, 'comments')) {
            remove_post_type_support($pt, 'comments');
            remove_post_type_support($pt, 'trackbacks');
        }
    }
});
add_filter('comments_open',  '__return_false', 20, 2);
add_filter('pings_open',     '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);


// ── 6. REMOVE DEFAULT POST TYPE + TAXONOMIES ─────────────────────────────────
// WHY: We don't use WP Posts, Categories, or Tags anywhere.
//      Removing them cleans up the admin and database queries.
// HOW: After init runs (priority 99), we unset them from global arrays.
//      This does NOT delete data — just removes them from the UI.
add_action('init', function () {
    global $wp_post_types, $wp_taxonomies;
    unset($wp_post_types['post']);          // default Blog Posts
    unset($wp_taxonomies['category']);      // default Categories
    unset($wp_taxonomies['post_tag']);      // default Tags
}, 99);


// ── 7. DISABLE XML-RPC ───────────────────────────────────────────────────────
// WHY: XML-RPC is a remote publishing protocol. We don't use it and
//      it's a common attack vector (brute force, DDoS amplification).
add_filter('xmlrpc_enabled', '__return_false');


// ── 8. REMOVE JQUERY MIGRATE ─────────────────────────────────────────────────
// WHY: jQuery Migrate is only needed for old jQuery code (pre-1.9).
//      Our theme uses modern JS so this is dead weight on the frontend.
// HOW: wp_default_scripts fires when WP registers its scripts.
//      We remove 'jquery-migrate' from jQuery's dependency list.
add_action('wp_default_scripts', function ($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            ['jquery-migrate']
        );
    }
});


// ── 9. REMOVE DEFAULT WIDGETS ─────────────────────────────────────────────────
// WHY: We don't use the widget system (no sidebars, no widget areas).
// HOW: widgets_init runs when widgets are registered. Priority 99 = last.
//      We clear the widget factory completely.
add_action('widgets_init', function () {
    global $wp_widget_factory;
    $wp_widget_factory->widgets = [];
}, 99);


// ── 10. CUSTOM ADMIN DASHBOARD REDIRECT ──────────────────────────────────────
// WHY: Default WP dashboard is useless for us. When someone visits
//      /wp-admin/ they should land on our Cane House overview page.
// HOW: load-index.php fires when the default dashboard page loads.
//      We redirect to our custom page immediately.
// NOTE: The canehouse-dashboard page is registered in inc/cpts.php
add_action('load-index.php', function () {
    wp_redirect(admin_url('admin.php?page=canehouse-dashboard'));
    exit;
});
