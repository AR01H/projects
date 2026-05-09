<?php
/**
 * Advaith Homes — functions.php
 * ================================
 * Core theme bootstrap: loads config, enqueues assets,
 * registers Customizer, sets up admin contacts dashboard.
 *
 * @package AdvaithHomes
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
function advaithhomes_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'width'  => 200,
        'height' => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ]);
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption']);
    register_nav_menus(['primary' => __('Primary Navigation', 'advaithhomes')]);
}
add_action('after_setup_theme', 'advaithhomes_setup');

// ── Enqueue styles & scripts ──────────────────────────────
function advaithhomes_scripts() {
    wp_enqueue_style('ah-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap',
        [], null
    );
    wp_enqueue_style('ah-theme', get_template_directory_uri() . '/css/theme.css', [], '1.0.0');
    wp_enqueue_script('ah-main', get_template_directory_uri() . '/js/main.js', [], '1.0.0', true);

    // Pass PHP config values to JS
    wp_localize_script('ah-main', 'AH', [
        'whatsapp' => get_theme_mod('ah_whatsapp', AH_WHATSAPP),
        'phone'    => get_theme_mod('ah_phone',    AH_PHONE),
        'ajaxurl'  => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'advaithhomes_scripts');

// ── Create contacts table on theme activation ─────────────
function advaithhomes_activate() {
    theme_create_contacts_table();
}
add_action('after_switch_theme', 'advaithhomes_activate');

// ── Register Admin Contacts Dashboard ────────────────────
function advaithhomes_admin_menu() {
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
add_action('admin_menu', 'advaithhomes_admin_menu');

// ── Handle AJAX contact form ──────────────────────────────
function advaithhomes_ajax_contact() {
    $result = theme_process_contact_form();
    wp_send_json( $result );
}
add_action('wp_ajax_tch_contact',        'advaithhomes_ajax_contact');
add_action('wp_ajax_nopriv_tch_contact', 'advaithhomes_ajax_contact');
