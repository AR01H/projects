<?php
/**
 * inc/cpts.php
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   WordPress has "Post Types" — think of them as database table types.
 *   By default it has: Posts, Pages, Media.
 *   We register our own: Specialties, Reviews, Locations, Events, Enquiries.
 *
 * HOW IT WORKS:
 *   register_post_type() tells WordPress about a new content type.
 *   show_in_menu: 'canehouse-dashboard' groups them under our custom menu.
 *   supports: which editing boxes appear (title, image, etc.)
 *   The data is stored in WordPress's wp_posts table with post_type = 'ch_xxx'
 *
 * HOW TO ADD A NEW POST TYPE:
 *   Copy any block below, change 'ch_xxx' to your new key,
 *   update labels and slug, and it will appear in the admin automatically.
 * ─────────────────────────────────────────────────────────────────────────────
 */

if (!defined('ABSPATH')) exit;


// ════════════════════════════════════════════════════════════════════════════
// 1. CUSTOM ADMIN MENU — "Cane House" parent menu
// WHY: Instead of scattered menu items, everything lives under one
//      "🌿 Cane House" menu in the admin sidebar.
// HOW: add_menu_page() creates a top-level menu item.
//      All CPTs use show_in_menu: 'canehouse-dashboard' to nest under it.
// ════════════════════════════════════════════════════════════════════════════
add_action('admin_menu', function () {

    add_menu_page(
        'The Cane House',           // page <title>
        '🌿 Cane House',           // sidebar label
        'edit_posts',               // who can see it (editors and above)
        'canehouse-dashboard',      // unique slug (used as show_in_menu value)
        'ch_dashboard_page',        // function that renders the page
        'none',                     // icon (none = we use emoji in label)
        2                           // position (2 = near the top)
    );

});

// ── Dashboard page content ────────────────────────────────────────────────────
// WHY: When someone clicks the main "🌿 Cane House" menu, this is shown.
//      cleanup.php redirects the default WP dashboard here.
function ch_dashboard_page()
{
    // Quick counts for overview
    $specialties = wp_count_posts('ch_specialty')->publish ?? 0;
    $reviews     = wp_count_posts('ch_testimonial')->publish ?? 0;
    $locations   = wp_count_posts('ch_location')->publish ?? 0;
    $enquiries   = wp_count_posts('ch_enquiry')->publish ?? 0;

    echo '<div class="wrap ch-dashboard">
        <h1 style="font-family:Georgia,serif;color:#2d5a1b;margin-bottom:24px">
            🌿 The Cane House — Admin Overview
        </h1>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px">
            <div class="ch-dash-card"><span class="ch-dash-num">' . $specialties . '</span><span class="ch-dash-lbl">Specialties</span></div>
            <div class="ch-dash-card"><span class="ch-dash-num">' . $reviews . '</span><span class="ch-dash-lbl">Reviews</span></div>
            <div class="ch-dash-card"><span class="ch-dash-num">' . $locations . '</span><span class="ch-dash-lbl">Locations</span></div>
            <div class="ch-dash-card"><span class="ch-dash-num">' . $enquiries . '</span><span class="ch-dash-lbl">New Enquiries</span></div>
        </div>
        <p style="color:#6a8c50">Use the menu on the left to manage your content. Changes are live immediately.</p>
    </div>';
}


// ════════════════════════════════════════════════════════════════════════════
// 2. REGISTER ALL CUSTOM POST TYPES
// ════════════════════════════════════════════════════════════════════════════
add_action('init', function () {


    // ── SPECIALTIES ──────────────────────────────────────────────────────────
    // WHAT: The juice menu items (Classic Cane, Ginger Twist etc.)
    // WHERE USED: pages/our-specialties.php
    // FIELDS: see inc/acf-fields.php → group_specialty
    register_post_type('ch_specialty', [
        'labels' => [
            'name'          => '🥤 Specialties',
            'singular_name' => 'Specialty',
            'add_new_item'  => 'Add New Specialty',
            'edit_item'     => 'Edit Specialty',
            'all_items'     => 'All Specialties',
        ],
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => 'canehouse-dashboard',  // nests under 🌿 Cane House
        'supports'     => ['title', 'thumbnail'],  // title = juice name, thumbnail = card image
        'show_in_rest' => true,
        'rewrite'      => ['slug' => 'specialties'],
        'menu_icon'    => 'dashicons-food',
    ]);


    // ── TESTIMONIALS / REVIEWS ────────────────────────────────────────────────
    // WHAT: Customer reviews shown on the reviews page + homepage
    // WHERE USED: pages/reviews-gallery.php
    // FIELDS: see inc/acf-fields.php → group_testimonial
    // NOTE: public=false means no frontend URL — only shown via our templates
    register_post_type('ch_testimonial', [
        'labels' => [
            'name'          => '⭐ Reviews',
            'singular_name' => 'Review',
            'add_new_item'  => 'Add New Review',
            'edit_item'     => 'Edit Review',
            'all_items'     => 'All Reviews',
        ],
        'public'       => false,    // no /reviews/slug URL needed
        'show_ui'      => true,
        'show_in_menu' => 'canehouse-dashboard',
        'supports'     => ['title'],  // title = reviewer name (for admin list)
        'menu_icon'    => 'dashicons-format-quote',
    ]);


    // ── LOCATIONS ─────────────────────────────────────────────────────────────
    // WHAT: Physical store locations (London, Manchester etc.)
    // WHERE USED: pages/contact-us.php (store locator cards)
    // FIELDS: see inc/acf-fields.php → group_location
    register_post_type('ch_location', [
        'labels' => [
            'name'          => '📍 Locations',
            'singular_name' => 'Location',
            'add_new_item'  => 'Add New Location',
            'edit_item'     => 'Edit Location',
            'all_items'     => 'All Locations',
        ],
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => 'canehouse-dashboard',
        'supports'     => ['title', 'thumbnail'],  // title = city name
        'rewrite'      => ['slug' => 'locations'],
        'menu_icon'    => 'dashicons-location-alt',
    ]);


    // ── EVENTS / CATERING TYPES ───────────────────────────────────────────────
    // WHAT: Types of events we cater (Weddings, Corporate, Parties etc.)
    // WHERE USED: pages/events.php
    // FIELDS: see inc/acf-fields.php → group_event
    register_post_type('ch_event', [
        'labels' => [
            'name'          => '🎪 Events',
            'singular_name' => 'Event Type',
            'add_new_item'  => 'Add Event Type',
            'edit_item'     => 'Edit Event Type',
            'all_items'     => 'All Event Types',
        ],
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => 'canehouse-dashboard',
        'supports'     => ['title', 'thumbnail'],
        'rewrite'      => ['slug' => 'events'],
        'menu_icon'    => 'dashicons-calendar-alt',
    ]);


    // ── FRANCHISE ENQUIRIES ───────────────────────────────────────────────────
    // WHAT: Leads submitted via the franchise enquiry form
    // WHERE USED: admin only — not shown on frontend
    // HOW CREATED: contact-leads.php saves form submissions as this post type
    // NOTE: create_posts = do_not_allow → admin cannot manually add, only forms do
    register_post_type('ch_enquiry', [
        'labels' => [
            'name'          => '🤝 Franchise Enquiries',
            'singular_name' => 'Enquiry',
            'all_items'     => 'All Enquiries',
            'edit_item'     => 'View Enquiry',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'canehouse-dashboard',
        'supports'     => ['title'],    // title = enquirer's name
        'capabilities' => [
            'create_posts' => 'do_not_allow',   // no manual creation in admin
        ],
        'map_meta_cap' => true,
        'menu_icon'    => 'dashicons-groups',
    ]);


    // ── GALLERY IMAGES ────────────────────────────────────────────────────────
    // WHAT: Gallery images categorised by type (Drinks, Interiors etc.)
    // WHERE USED: pages/reviews-gallery.php gallery section
    // FIELDS: see inc/acf-fields.php → group_gallery
    register_post_type('ch_gallery', [
        'labels' => [
            'name'          => '🖼️ Gallery',
            'singular_name' => 'Gallery Image',
            'add_new_item'  => 'Add Gallery Image',
            'edit_item'     => 'Edit Gallery Image',
            'all_items'     => 'All Gallery Images',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'canehouse-dashboard',
        'supports'     => ['title', 'thumbnail'],   // thumbnail = the actual image
        'menu_icon'    => 'dashicons-format-gallery',
    ]);

});
