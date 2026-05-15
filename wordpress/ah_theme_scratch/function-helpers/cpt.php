<?php
/**
 * scratch/function-helpers/cpt.php
 * Cleaning up the sidebar: Moving CPT management exclusively to the Portal Grid.
 */

function ah_register_custom_post_types() {
    $cpts = [
        'ah_review'  => ['singular' => 'Review', 'plural' => 'Reviews', 'icon' => 'star-filled'],
        'ah_post'    => ['singular' => 'Article', 'plural' => 'Blogs / News / Articles', 'icon' => 'admin-post'],
        'ah_project' => ['singular' => 'Project', 'plural' => 'Client Projects', 'icon' => 'admin-home'],
        'ah_guide'   => ['singular' => 'Guide', 'plural' => 'Guides', 'icon' => 'book-alt'],
        'ah_lead'    => ['singular' => 'Lead', 'plural' => 'Form Submissions', 'icon' => 'email-alt'],
        'ah_log'     => ['singular' => 'Log', 'plural' => 'Price Calculation Logs', 'icon' => 'clipboard'],
        'ah_report'  => ['singular' => 'Report', 'plural' => 'My Reports', 'icon' => 'chart-area'],
    ];

    foreach ($cpts as $slug => $data) {
        register_post_type($slug, [
            'label'         => $data['plural'],
            'labels'        => [
                'name'               => $data['plural'],
                'singular_name'      => $data['singular'],
                'add_new'            => 'Add New',
                'all_items'          => 'All ' . $data['plural'],
            ],
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => false, // HIDDEN FROM SIDEBAR
            'menu_icon'     => 'dashicons-' . $data['icon'],
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'show_in_rest'  => true,
            'has_archive'   => true,
        ]);
    }
}
add_action('init', 'ah_register_custom_post_types');

function ah_register_custom_taxonomies() {
    $cpt_slugs = ['ah_review', 'ah_post', 'ah_project', 'ah_guide', 'ah_lead'];

    register_taxonomy('ah_tag', $cpt_slugs, [
        'label'        => 'Tags',
        'hierarchical' => false,
        'show_ui'      => true,
        'show_in_menu' => false, // HIDDEN
        'show_in_rest' => true,
    ]);

    register_taxonomy('ah_group', $cpt_slugs, [
        'label'        => 'Groups',
        'hierarchical' => true,
        'show_ui'      => true,
        'show_in_menu' => false, // HIDDEN
        'show_in_rest' => true,
    ]);
}
add_action('init', 'ah_register_custom_taxonomies');
