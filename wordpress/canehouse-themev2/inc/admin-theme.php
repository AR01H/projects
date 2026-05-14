<?php
/**
 * inc/admin-theme.php
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   WordPress admin looks like default WordPress by default — grey/blue.
 *   This file makes it look like The Cane House brand.
 *
 * WHAT IT CHANGES:
 *   - Admin sidebar: deep green background, lime accents
 *   - Admin top bar: dark green with logo
 *   - Buttons: lime green
 *   - Dashboard overview cards
 *   - Post list table styling
 *   - Fonts: DM Sans throughout admin
 *   - Admin logo: Cane House brand name
 *
 * HOW IT WORKS:
 *   admin_enqueue_scripts → loads our admin.css into every admin page
 *   wp_user_color_scheme → sets the base WP colour scheme (we override it)
 *   admin_head → injects quick CSS/JS directly into <head>
 * ─────────────────────────────────────────────────────────────────────────────
 */

if (!defined('ABSPATH')) exit;


// ── 1. FORCE COLOUR SCHEME + ENQUEUE ADMIN CSS ───────────────────────────────
// WHY: WordPress has built-in colour schemes (fresh, midnight, ocean etc.)
//      We pick 'midnight' as closest base then fully override with our CSS.
// HOW: admin_enqueue_scripts fires on every admin page. We load admin.css here.
add_action('admin_enqueue_scripts', function () {

    // Force the midnight scheme as our base (darkest default = least conflict)
    // This affects colour variables WP uses internally before our CSS loads
    update_user_option(get_current_user_id(), 'admin_color', 'midnight', true);

    // Load our full admin override CSS
    wp_enqueue_style(
        'ch-admin-css',
        get_template_directory_uri() . '/admin/admin.css',
        [],
        '1.0'
    );

    // Load DM Sans in admin too
    wp_enqueue_style(
        'ch-admin-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,300;0,600;1,400&display=swap',
        [],
        null
    );

});


// ── 2. REPLACE WP LOGO IN ADMIN BAR ─────────────────────────────────────────
// WHY: By default the top-left of the admin bar shows the WP logo.
//      We replace it with The Cane House brand mark.
// HOW: admin_bar_menu runs when the admin bar is assembled.
//      We remove the wp-logo node and add our own.
add_action('admin_bar_menu', function ($wp_admin_bar) {

    // Remove default WP logo node
    $wp_admin_bar->remove_node('wp-logo');

    // Add our brand mark
    $wp_admin_bar->add_node([
        'id'    => 'ch-brand',
        'title' => '<span style="font-family:Georgia,serif;font-size:15px;color:#c8e830;letter-spacing:1px;font-weight:400">🌿 The Cane House</span>',
        'href'  => admin_url('admin.php?page=canehouse-dashboard'),
        'meta'  => ['title' => 'Cane House Dashboard'],
    ]);

}, 11);


// ── 3. INLINE ADMIN HEAD STYLES ──────────────────────────────────────────────
// WHY: Some admin UI elements are very difficult to target from an external
//      stylesheet (they render before the page body). We inject a small
//      block of critical styles directly into <head>.
add_action('admin_head', function () { ?>
<style>
/* ── Body + font ──────────────────────────────── */
body.wp-admin,
#wpcontent,
#wpbody-content,
.wrap {
    font-family: 'DM Sans', sans-serif !important;
}

/* ── Sidebar ──────────────────────────────────── */
#adminmenu, #adminmenuback, #adminmenuwrap {
    background: #0d1f08 !important;
}
#adminmenu li a {
    color: rgba(255,255,255,0.65) !important;
    font-family: 'DM Sans', sans-serif !important;
    font-size: 13px !important;
}
#adminmenu li.current a.menu-top,
#adminmenu li a:hover,
#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu {
    background: rgba(200,232,48,0.12) !important;
    color: #c8e830 !important;
}
#adminmenu li.current a.menu-top .wp-menu-arrow {
    background: transparent !important;
}
/* Active state left border */
#adminmenu li.current > a.menu-top,
#adminmenu li.wp-has-current-submenu > a {
    box-shadow: inset 3px 0 0 #c8e830 !important;
}

/* ── Submenu ──────────────────────────────────── */
#adminmenu .wp-submenu {
    background: #162b0e !important;
}
#adminmenu .wp-submenu a {
    color: rgba(255,255,255,0.5) !important;
}
#adminmenu .wp-submenu a:hover,
#adminmenu .wp-submenu li.current a {
    color: #c8e830 !important;
    background: rgba(200,232,48,0.08) !important;
}

/* ── Admin bar (top strip) ────────────────────── */
#wpadminbar {
    background: #0d1f08 !important;
    border-bottom: 1px solid rgba(200,232,48,0.15) !important;
}
#wpadminbar .ab-item,
#wpadminbar a.ab-item {
    color: rgba(255,255,255,0.7) !important;
}
#wpadminbar .ab-item:hover { color: #c8e830 !important; }

/* ── Page titles ──────────────────────────────── */
.wrap h1 {
    font-family: 'Cormorant Garamond', serif !important;
    color: #2d5a1b !important;
    font-weight: 300 !important;
}

/* ── Primary action buttons ───────────────────── */
.button-primary,
#publish,
#save-post {
    background: #2d5a1b !important;
    border-color: #2d5a1b !important;
    color: #c8e830 !important;
    box-shadow: none !important;
    font-family: 'DM Sans', sans-serif !important;
    font-weight: 500 !important;
    border-radius: 4px !important;
}
.button-primary:hover {
    background: #1a3a0a !important;
    border-color: #1a3a0a !important;
    color: #c8e830 !important;
}

/* ── Secondary buttons ────────────────────────── */
.button-secondary, .button {
    border-color: #a8d96e !important;
    color: #2d5a1b !important;
    border-radius: 4px !important;
}

/* ── Post list table ──────────────────────────── */
.wp-list-table thead th,
.wp-list-table tfoot th {
    background: #eef8e0 !important;
    color: #2d5a1b !important;
    font-size: 11px !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
}
.wp-list-table tbody tr:hover td { background: #f5fcea !important; }
.wp-list-table .row-actions a { color: #4a8c2a !important; }
.wp-list-table .row-actions .trash a { color: #c0392b !important; }

/* ── Admin notices ────────────────────────────── */
.notice-success { border-left-color: #6abf3a !important; }
.notice-error   { border-left-color: #c0392b !important; }

/* ── ACF field labels ─────────────────────────── */
.acf-label label {
    color: #2d5a1b !important;
    font-weight: 500 !important;
}
.acf-field:focus-within .acf-label label { color: #4a8c2a !important; }

/* ── Focus rings ──────────────────────────────── */
input:focus, textarea:focus, select:focus {
    border-color: #6abf3a !important;
    box-shadow: 0 0 0 1px #6abf3a !important;
}

/* ── Dashboard cards (from cpts.php ch_dashboard_page) ─── */
.ch-dash-card {
    background: #eef8e0;
    border: 1px solid #d8f0b0;
    border-top: 3px solid #c8e830;
    border-radius: 4px;
    padding: 28px 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: box-shadow 0.2s;
}
.ch-dash-card:hover { box-shadow: 0 4px 16px rgba(45,90,27,0.15); }
.ch-dash-num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 48px;
    font-weight: 600;
    color: #2d5a1b;
    line-height: 1;
}
.ch-dash-lbl {
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #6a8c50;
}
</style>
<?php });


// ── 4. REMOVE SCREEN OPTIONS + HELP TAB ──────────────────────────────────────
// WHY: "Screen Options" and "Help" tabs in the top-right of every admin page
//      are confusing for non-technical admins and not needed.
add_filter('screen_options_show_screen', '__return_false');
add_filter('contextual_help', '__return_empty_string');


// ── 5. CUSTOM ADMIN FOOTER TEXT ──────────────────────────────────────────────
// WHY: Default footer says "Thank you for creating with WordPress" — unnecessary.
//      We replace it with the Cane House brand message.
add_filter('admin_footer_text', function () {
    return '<span style="font-family:Georgia,serif;color:#6a8c50">🌿 The Cane House Admin · Crafted Fresh, Every Day</span>';
});
add_filter('update_footer', '__return_empty_string', 99);


// ── 6. POST LIST TABLE: ADD CUSTOM COLUMNS ───────────────────────────────────
// WHY: Default WP post list only shows Title and Date.
//      We add useful columns for each post type so admins see key info at a glance.

// ── Specialties columns ───────────────────────────────────────────────────────
add_filter('manage_ch_specialty_posts_columns', function ($cols) {
    return [
        'cb'            => $cols['cb'],         // checkbox
        'title'         => 'Specialty Name',
        'spec_icon'     => 'Icon',
        'spec_category' => 'Category',
        'spec_price'    => 'Price',
        'spec_order'    => 'Order',
        'date'          => 'Added',
    ];
});
add_action('manage_ch_specialty_posts_custom_column', function ($col, $post_id) {
    if ($col === 'spec_icon')     echo get_post_meta($post_id, 'spec_icon', true);
    if ($col === 'spec_category') echo ucfirst(get_post_meta($post_id, 'spec_category', true));
    if ($col === 'spec_price')    echo get_post_meta($post_id, 'spec_price', true);
    if ($col === 'spec_order')    echo get_post_meta($post_id, 'spec_order', true);
}, 10, 2);

// ── Reviews columns ───────────────────────────────────────────────────────────
add_filter('manage_ch_testimonial_posts_columns', function ($cols) {
    return [
        'cb'             => $cols['cb'],
        'title'          => 'Reviewer Name',
        'testi_stars'    => '⭐ Stars',
        'testi_type'     => 'Type',
        'testi_featured' => 'On Homepage?',
        'date'           => 'Added',
    ];
});
add_action('manage_ch_testimonial_posts_custom_column', function ($col, $post_id) {
    if ($col === 'testi_stars')    echo get_post_meta($post_id, 'testi_stars', true);
    if ($col === 'testi_type')     echo get_post_meta($post_id, 'testi_type', true);
    if ($col === 'testi_featured') echo get_post_meta($post_id, 'testi_featured', true) ? '✅ Yes' : '—';
}, 10, 2);

// ── Enquiry columns ───────────────────────────────────────────────────────────
add_filter('manage_ch_enquiry_posts_columns', function ($cols) {
    return [
        'cb'            => $cols['cb'],
        'title'         => 'Name',
        'enq_email'     => 'Email',
        'enq_city'      => 'City',
        'enq_type'      => 'Type',
        'enq_status'    => 'Status',
        'date'          => 'Submitted',
    ];
});
add_action('manage_ch_enquiry_posts_custom_column', function ($col, $post_id) {
    if ($col === 'enq_email')  echo get_post_meta($post_id, 'enq_email', true);
    if ($col === 'enq_city')   echo get_post_meta($post_id, 'enq_city', true);
    if ($col === 'enq_type')   echo get_post_meta($post_id, 'enq_type', true);
    if ($col === 'enq_status') echo get_post_meta($post_id, 'enq_status', true);
}, 10, 2);

// ── Locations columns ─────────────────────────────────────────────────────────
add_filter('manage_ch_location_posts_columns', function ($cols) {
    return [
        'cb'         => $cols['cb'],
        'title'      => 'Location Name',
        'loc_city'   => 'City',
        'loc_hours'  => 'Hours',
        'loc_status' => 'Status',
        'loc_order'  => 'Order',
        'date'       => 'Added',
    ];
});
add_action('manage_ch_location_posts_custom_column', function ($col, $post_id) {
    if ($col === 'loc_city')   echo get_post_meta($post_id, 'loc_city', true);
    if ($col === 'loc_hours')  echo get_post_meta($post_id, 'loc_hours', true);
    if ($col === 'loc_status') echo ucfirst(get_post_meta($post_id, 'loc_status', true));
    if ($col === 'loc_order')  echo get_post_meta($post_id, 'loc_order', true);
}, 10, 2);
