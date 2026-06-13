<?php
/**
 * admin/page-sample.php - Admin Module Sample
 *
 * Covers:
 *   - Custom admin page (Settings panel)
 *   - Admin action hooks (array-driven)
 *   - Meta box registration (loop)
 *   - Dashboard widget
 *
 * RULE: One file per admin feature area.
 *       Settings are stored via Options API; always use npt_ prefix.
 */

defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════════
// ── 1. Admin hooks definition (array-driven) ────────────────────────
// ═══════════════════════════════════════════════════════════════════

$npt_admin_hooks = [
    [ 'admin_menu',         'npt_admin_register_pages'   ],
    [ 'add_meta_boxes',     'npt_admin_register_metaboxes' ],
    [ 'save_post',          'npt_admin_save_metabox', 10, 2 ],
    [ 'wp_dashboard_setup', 'npt_admin_register_dashboard_widget' ],
];

foreach ( $npt_admin_hooks as $hook ) {
    [ $action, $callback ] = $hook;
    $priority = $hook[2] ?? 10;
    $args     = $hook[3] ?? 1;
    add_action( $action, $callback, $priority, $args );
}

// ═══════════════════════════════════════════════════════════════════
// ── 2. Admin Pages ──────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Register theme admin pages.
 * Add more pages to the $pages array - no extra add_menu_page() calls.
 */
function npt_admin_register_pages(): void {
    $pages = [
        [
            'type'       => 'menu',
            'page_title' => 'Theme Settings',
            'menu_title' => 'Theme Settings',
            'capability' => 'manage_options',
            'slug'       => 'npt-settings',
            'callback'   => 'npt_admin_settings_page',
            'icon'       => 'dashicons-admin-customizer',
            'position'   => 80,
        ],
        // add sub-menu pages as type => 'submenu' here …
    ];

    foreach ( $pages as $p ) {
        if ( $p['type'] === 'menu' ) {
            add_menu_page(
                $p['page_title'], $p['menu_title'], $p['capability'],
                $p['slug'], $p['callback'], $p['icon'], $p['position']
            );
        }
    }
}

/** Render the settings page */
function npt_admin_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    // Handle save
    if ( isset( $_POST['npt_save'] ) && check_admin_referer( 'npt_settings_save' ) ) {
        update_option( 'npt_example_option', sanitize_text_field( $_POST['npt_example_option'] ?? '' ) );
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }
    $option = get_option( 'npt_example_option', '' );
    ?>
    <div class="wrap">
        <h1>Theme Settings</h1>
        <form method="post">
            <?php wp_nonce_field( 'npt_settings_save' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="npt_example_option">Example Option</label></th>
                    <td>
                        <input type="text" id="npt_example_option" name="npt_example_option"
                               value="<?php echo esc_attr( $option ); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Save Settings', 'primary', 'npt_save' ); ?>
        </form>
    </div>
    <?php
}

// ═══════════════════════════════════════════════════════════════════
// ── 3. Meta Boxes ───────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

/**
 * Register meta boxes (loop over config array).
 */
function npt_admin_register_metaboxes(): void {
    $metaboxes = [
        [
            'id'         => 'npt-portfolio-details',
            'title'      => 'Portfolio Details',
            'callback'   => 'npt_metabox_portfolio_details',
            'post_types' => [ 'portfolio' ],
            'context'    => 'normal',
            'priority'   => 'high',
        ],
        // add more metaboxes here …
    ];

    foreach ( $metaboxes as $mb ) {
        foreach ( $mb['post_types'] as $post_type ) {
            add_meta_box(
                $mb['id'], $mb['title'], $mb['callback'],
                $post_type, $mb['context'], $mb['priority']
            );
        }
    }
}

/** Render the portfolio details metabox */
function npt_metabox_portfolio_details( WP_Post $post ): void {
    wp_nonce_field( 'npt_portfolio_metabox', 'npt_portfolio_nonce' );
    $client = get_post_meta( $post->ID, 'npt_client_name', true );
    ?>
    <p>
        <label for="npt_client_name"><strong>Client Name</strong></label><br>
        <input type="text" id="npt_client_name" name="npt_client_name"
               value="<?php echo esc_attr( $client ); ?>" style="width:100%">
    </p>
    <?php
}

/** Save meta box data */
function npt_admin_save_metabox( int $post_id, WP_Post $post ): void {
    if (
        ! isset( $_POST['npt_portfolio_nonce'] ) ||
        ! wp_verify_nonce( $_POST['npt_portfolio_nonce'], 'npt_portfolio_metabox' ) ||
        ! current_user_can( 'edit_post', $post_id ) ||
        defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE
    ) {
        return;
    }

    if ( isset( $_POST['npt_client_name'] ) ) {
        update_post_meta( $post_id, 'npt_client_name', sanitize_text_field( $_POST['npt_client_name'] ) );
    }
}

// ═══════════════════════════════════════════════════════════════════
// ── 4. Dashboard Widget ─────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════

function npt_admin_register_dashboard_widget(): void {
    wp_add_dashboard_widget(
        'npt_dashboard_widget',
        'Theme Status',
        'npt_render_dashboard_widget'
    );
}

function npt_render_dashboard_widget(): void {
    echo '<p>Theme version: <strong>' . esc_html( $GLOBALS['theme_config']['version'] ) . '</strong></p>';
    // stub - show quick stats, recent activity, etc.
}
