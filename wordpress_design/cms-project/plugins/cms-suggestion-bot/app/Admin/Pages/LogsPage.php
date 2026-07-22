<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

use CmsSuggestionBot\Logger\Logger;
use CmsSuggestionBot\Repositories\LogRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Logs submenu - browse/filter cms_sug_bot_logs, and purge old entries.
 */
final class LogsPage {

	public function __construct( private readonly LogRepository $logs ) {}

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$notice = $this->maybePurge();

		$channel  = isset( $_GET['channel'] ) ? sanitize_key( wp_unslash( $_GET['channel'] ) ) : '';
		$entries  = $this->logs->recent( 100, $channel );
		$channels = array(
			Logger::CHANNEL_READER, Logger::CHANNEL_CACHE, Logger::CHANNEL_API,
			Logger::CHANNEL_ERROR, Logger::CHANNEL_WARNING, Logger::CHANNEL_PERFORMANCE,
			Logger::CHANNEL_CRON, Logger::CHANNEL_MANUAL,
		);

		include CSB_PLUGIN_DIR . '/templates/admin/logs.php';
	}

	private function maybePurge(): string {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['csb_purge_logs'] ) ) {
			return '';
		}

		check_admin_referer( 'csb_purge_logs' );
		$days  = max( 1, (int) ( $_POST['days'] ?? 30 ) );
		$count = $this->logs->purgeOlderThan( $days );

		/* translators: %d: number of log entries removed */
		return sprintf( __( 'Purged %d log entries.', 'cms-suggestion-bot' ), $count );
	}
}
