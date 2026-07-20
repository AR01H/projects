<?php
/**
 * admin/includes/admin-functions.php - Site-specific wp-admin code.
 *
 * Loaded ONLY inside wp-admin (config/files.php 'admin' bucket). Put things
 * like custom list-table columns, dashboard widgets, editor tweaks and extra
 * admin_post handlers here - NOT in /core (engines stay generic).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Example: small "theme is registry-driven" hint on the Pages list screen,
 * so editors know registered pages are managed from the Theme panel.
 */
add_action( 'admin_notices', 'nt_admin_pages_hint' );
function nt_admin_pages_hint() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-page' !== $screen->id ) {
		return;
	}
	echo '<div class="notice notice-info"><p>'
		. esc_html__( 'Pages listed in the theme registry (config/pages.php) are routed and styled automatically - manage them under the "Theme" menu.', NT_TEXT_DOMAIN )
		. '</p></div>';
}
