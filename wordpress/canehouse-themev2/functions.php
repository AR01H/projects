<?php
/**
 * The Cane House — functions.php
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   WordPress requires functions.php. It is the "bootstrap" of the theme.
 *   It sets up core features and loads all other module files.
 *
 * WHAT IT DOES:
 *   1. Sets up WordPress theme features (menus, logo, thumbnails)
 *   2. Enqueues CSS and JS files
 *   3. Loads all module files from /inc/ folder
 *
 * MODULE FILES (in /inc/):
 *   db-schema.php      — Creates all database tables + seeds default data
 *   site-settings.php  — Global site settings (phone, social, footer etc.)
 *   content-manager.php— Tabbed admin UI to manage Reviews, FAQs, Events etc.
 *   contact-leads.php  — Contact form submissions + lead management
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── 1. THEME SETUP ────────────────────────────────────────────────────────────
function canehouse_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height' => 80,
        'width' => 80,
        'flex-height' => true,
        'flex-width' => true,
    ));
    add_theme_support('html5', array('search-form', 'comment-form', 'gallery', 'caption'));
    register_nav_menus(array(
        'primary' => 'Primary Navigation',
        'footer' => 'Footer Navigation',
    ));
}
add_action('after_setup_theme', 'canehouse_setup');

// ── 2. ENQUEUE SCRIPTS & STYLES ───────────────────────────────────────────────
function canehouse_enqueue()
{
    // Google Fonts
    wp_enqueue_style(
        'ch-fonts',
        'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap',
        array(),
        null
    );
    // Main CSS
    wp_enqueue_style('ch-main', get_template_directory_uri() . '/assets/css/main.css', array(), '2.0');
    wp_enqueue_style('ch-style', get_stylesheet_uri(), array(), '2.0');
    // Main JS
    wp_enqueue_script(
        'ch-script',
        get_template_directory_uri() . '/assets/js/script.js',
        array(),
        '2.0',
        true
    );
    // Contact form AJAX JS
    wp_enqueue_script(
        'ch-contact',
        get_template_directory_uri() . '/assets/js/contact-form.js',
        array('ch-script'),
        '2.0',
        true
    );
    // Pass AJAX URL + nonce to JS
    wp_localize_script('ch-script', 'CH_FORM', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ch_lead_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'canehouse_enqueue');

// ── 3. HELPER: read global site option ───────────────────────────────────────
// (Full implementation in inc/site-settings.php — this stub ensures
//  footer.php works even if site-settings loads after footer)
if (!function_exists('ch_opt')) {
    function ch_opt($key, $fallback = '')
    {
        $val = get_option('ch_site_' . $key, null);
        return ($val !== null && $val !== '') ? $val : $fallback;
    }
}

// ── 4. HELPER: announcement banner ───────────────────────────────────────────
function ch_announcement_banner()
{
    if (ch_opt('header_notice_on') !== '1')
        return;
    $text = ch_opt('header_notice', '');
    if (!$text)
        return;
    echo '<div class="ch-announcement-bar">' . esc_html($text) . '</div>';
}
add_action('wp_body_open', 'ch_announcement_banner');

// ── 5. LOAD MODULES ───────────────────────────────────────────────────────────
require_once get_template_directory() . '/inc/db-schema.php';
require_once get_template_directory() . '/inc/site-settings.php';
require_once get_template_directory() . '/inc/content-manager.php';
require_once get_template_directory() . '/inc/contact-leads.php';
require_once get_template_directory() . '/inc/legal-pages.php';


// ── 6. ANNOUNCEMENT BAR CSS ───────────────────────────────────────────────────
add_action('wp_head', function () {
    echo '<style>.ch-announcement-bar{background:var(--lime,#c8e830);color:#1a3a0a;text-align:center;padding:8px 16px;font-size:13px;font-weight:700;position:relative;z-index:1001;}</style>';
});

// ── FALLBACK NAV (shown when no menu is assigned) ─────────────────────────────
// WHY: If no menu is set in Appearance → Menus, this shows basic links
function ch_fallback_nav()
{
    echo '<ul class="nav-links" id="nav-links">
      <li><a href="' . home_url('/#how-to-order') . '">How to Order</a></li>
      <li><a href="' . home_url('/#reviews') . '">Reviews</a></li>
      <li><a href="' . home_url('/#build') . '">Our Juices</a></li>
      <li><a href="' . home_url('/#faq') . '">FAQ</a></li>
      <li><a href="' . home_url('/#hire') . '">Events</a></li>
      <li><a href="' . home_url('/#franchise') . '">Franchise</a></li>
      <li><a href="' . home_url('/#contact') . '" class="nav-cta-btn">Contact</a></li>
    </ul>';
}

// ── FIX POLICY PAGE URLs ──────────────────────────────────────────────────────
// WHY: get_page_by_title is deprecated in WP 6.2+
//      This helper safely finds a page URL by its title using WP_Query
function ch_get_page_url_by_title($title)
{
    $q = new WP_Query(array(
        'post_type' => 'page',
        'title' => $title,
        'posts_per_page' => 1,
        'no_found_rows' => true,
        'post_status' => 'publish',
    ));
    if ($q->have_posts()) {
        return get_permalink($q->posts[0]->ID);
    }
    return '#';
}

// ── REMOVE WP DEFAULT ADMIN BAR ON FRONTEND ──────────────────────────────────
add_filter('show_admin_bar', '__return_false');
