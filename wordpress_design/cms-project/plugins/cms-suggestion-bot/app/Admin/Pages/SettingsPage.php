<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Settings submenu - plugin-lifecycle settings, distinct from Configuration
 * (which covers bot behaviour). Currently just the uninstall.php data-wipe
 * opt-in (see the project's "Cleanup" requirement: tables/cache/logs/etc.
 * are only removed on uninstall if this is explicitly enabled).
 */
final class SettingsPage {

	private const OPTION = 'csb_delete_data_on_uninstall';

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$notice = $this->maybeSave();
		$delete_on_uninstall = (bool) get_option( self::OPTION, false );

		include CSB_PLUGIN_DIR . '/templates/admin/settings.php';
	}

	private function maybeSave(): string {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['csb_settings_save'] ) ) {
			return '';
		}

		check_admin_referer( 'csb_settings' );
		update_option( self::OPTION, ! empty( $_POST['delete_on_uninstall'] ) );

		return __( 'Settings saved.', 'cms-suggestion-bot' );
	}
}
