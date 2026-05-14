<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ah_theme_register_post_types() {
    $parent_menu = 'advaith-homes';

    // 1. Properties
    register_post_type('property', [
        'labels' => [
            'name' => 'Properties',
            'singular_name' => 'Property',
            'add_new' => 'Add New Property',
            'edit_item' => 'Edit Property',
        ],
        'public' => true,
        'show_in_menu' => $parent_menu,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'menu_icon' => 'dashicons-admin-home',
        'taxonomies' => ['property_type'],
    ]);

    register_taxonomy('property_type', 'property', [
        'labels' => ['name' => 'Property Types', 'singular_name' => 'Type'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_menu' => $parent_menu,
    ]);

    // 2. Testimonials / Reviews
    register_post_type('testimonial', [
        'labels' => [
            'name' => 'Testimonials / Reviews',
            'singular_name' => 'Review',
            'add_new' => 'Add New Review',
            'edit_item' => 'Edit Review',
        ],
        'public' => true,
        'show_in_menu' => $parent_menu,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-format-quote',
    ]);

    // 3. Services / Expertise
    register_post_type('service', [
        'labels' => [
            'name' => 'Services',
            'singular_name' => 'Service',
            'add_new' => 'Add New Service',
            'edit_item' => 'Edit Service',
        ],
        'public' => true,
        'show_in_menu' => $parent_menu,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon' => 'dashicons-hammer',
        'taxonomies' => ['service_cat'],
    ]);

    register_taxonomy('service_cat', 'service', [
        'labels' => ['name' => 'Service Categories', 'singular_name' => 'Category'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_menu' => $parent_menu,
    ]);

    // 4. Benefits (Why Us)
    register_post_type('benefit', [
        'labels' => [
            'name' => 'Benefits',
            'singular_name' => 'Benefit',
            'add_new' => 'Add New Benefit',
            'edit_item' => 'Edit Benefit',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => $parent_menu,
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);

    // 5. Process Steps
    register_post_type('process_step', [
        'labels' => [
            'name' => 'Process Steps',
            'singular_name' => 'Process Step',
            'add_new' => 'Add New Step',
            'edit_item' => 'Edit Step',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => $parent_menu,
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);

    // 6. Contact Messages
    register_post_type('inquiry', [
        'labels' => [
            'name' => 'Contact Messages',
            'singular_name' => 'Message',
            'edit_item' => 'View Message',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => $parent_menu,
        'supports' => ['title', 'editor', 'custom-fields'],
        'capability_type' => 'post',
        'capabilities' => ['create_posts' => false],
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-email-alt',
    ]);
}
add_action('init', 'ah_theme_register_post_types');

/**
 * Add Status Columns and Quick Actions
 */
function ah_theme_add_custom_columns($columns) {
    $new_columns = [];
    foreach($columns as $key => $value) {
        if ($key === 'title') { $new_columns['ah_thumb'] = 'Image'; }
        $new_columns[$key] = $value;
    }
    $new_columns['ah_status'] = 'Status';
    return $new_columns;
}
add_filter('manage_property_posts_columns', 'ah_theme_add_custom_columns');
add_filter('manage_service_posts_columns', 'ah_theme_add_custom_columns');
add_filter('manage_testimonial_posts_columns', 'ah_theme_add_custom_columns');

function ah_theme_display_custom_columns($column, $post_id) {
    if ($column === 'ah_thumb') {
        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, [50, 50], ['style' => 'border-radius:4px;']);
        } else {
            $image_url = get_post_meta($post_id, 'image_url', true);
            if ($image_url) echo '<img src="' . esc_url($image_url) . '" style="width:50px; height:50px; object-fit:cover; border-radius:4px;" />';
            else echo '<div style="width:50px; height:50px; background:#f0f0f0; border-radius:4px;"></div>';
        }
    }
    if ($column === 'ah_status') {
        $status = get_post_meta($post_id, 'display_status', true) ?: 'Active';
        $color = ($status === 'Active') ? '#16a34a' : '#d63638';
        echo '<span style="background: ' . $color . '; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">' . esc_html($status) . '</span>';
        echo '<div style="margin-top:5px;"><a href="' . esc_url(add_query_arg(['action' => 'toggle_ah_status', 'post_id' => $post_id])) . '" style="font-size:10px;">Toggle</a></div>';
    }
}
add_action('manage_property_posts_custom_column', 'ah_theme_display_custom_columns', 10, 2);
add_action('manage_service_posts_custom_column', 'ah_theme_display_custom_columns', 10, 2);
add_action('manage_testimonial_posts_custom_column', 'ah_theme_display_custom_columns', 10, 2);

function ah_theme_handle_status_toggle() {
    if (isset($_GET['action']) && $_GET['action'] === 'toggle_ah_status' && isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
        $current = get_post_meta($post_id, 'display_status', true) ?: 'Active';
        update_post_meta($post_id, 'display_status', ($current === 'Active' ? 'Inactive' : 'Active'));
        wp_redirect(remove_query_arg(['action', 'post_id'])); exit;
    }
}
add_action('admin_init', 'ah_theme_handle_status_toggle');
