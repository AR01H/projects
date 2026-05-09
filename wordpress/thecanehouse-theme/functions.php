<?php
/**
 * The Cane House — functions.php
 * ================================
 * Core theme bootstrap: loads config, enqueues assets,
 * registers Customizer, sets up admin contacts dashboard.
 *
 * @package TheCanHouse
 */

// ── Load core config (all editable details) ──────────────
require_once get_template_directory() . '/config.php';

// ── Load sub-modules ─────────────────────────────────────
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/contact-form.php';

/**
 * Helper: get theme_mod with config.php constant fallback.
 */
function get_customizer_val( $key, $default ) {
    return get_theme_mod( $key, $default );
}

// ── Theme setup ──────────────────────────────────────────
function thecanehouse_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'width'  => 200,
        'height' => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ]);
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption']);
    register_nav_menus(['primary' => __('Primary Navigation', 'thecanehouse')]);
}
add_action('after_setup_theme', 'thecanehouse_setup');

// ── Enqueue styles & scripts ──────────────────────────────
function thecanehouse_scripts() {
    wp_enqueue_style('tch-google-fonts',
        'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap',
        [], null
    );
    wp_enqueue_style('tch-theme', get_template_directory_uri() . '/css/theme.css', [], '1.0.0');
    wp_enqueue_script('tch-main', get_template_directory_uri() . '/js/main.js', [], '1.0.0', true);

    // Pass PHP config values to JS
    wp_localize_script('tch-main', 'TCH', [
        'whatsapp' => get_theme_mod('tch_whatsapp', TCH_WHATSAPP),
        'phone'    => get_theme_mod('tch_phone',    TCH_PHONE),
        'ajaxurl'  => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'thecanehouse_scripts');

// ── Create contacts table on theme activation ─────────────
function thecanehouse_activate() {
    theme_create_contacts_table();
}
add_action('after_switch_theme', 'thecanehouse_activate');

// ── Register Admin Contacts Dashboard ────────────────────
function thecanehouse_admin_menu() {
    add_menu_page(
        'Contact Submissions',
        '📋 Contacts',
        'manage_options',
        'theme-contacts',
        'theme_render_contacts_admin',
        'dashicons-email-alt',
        30
    );
}
add_action('admin_menu', 'thecanehouse_admin_menu');

// ── Handle AJAX contact form (non-logged-in users) ───────
function thecanehouse_ajax_contact() {
    $result = theme_process_contact_form();
    wp_send_json( $result );
}
add_action('wp_ajax_tch_contact',        'thecanehouse_ajax_contact');
add_action('wp_ajax_nopriv_tch_contact', 'thecanehouse_ajax_contact');
