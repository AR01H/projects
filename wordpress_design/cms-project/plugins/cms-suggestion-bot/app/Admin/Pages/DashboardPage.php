<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

use CmsSuggestionBot\Repositories\KnowledgeRepository;
use CmsSuggestionBot\Services\CacheService;
use CmsSuggestionBot\Services\ReaderService;
use CmsSuggestionBot\Services\SettingsService;

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard submenu - a read-only snapshot of bot/cache/reader/API status.
 * All data comes from existing services/repositories; this class only shapes
 * it for the template.
 */
final class DashboardPage {

	public function __construct(
		private readonly SettingsService $settings,
		private readonly CacheService $cacheService,
		private readonly ReaderService $readerService,
		private readonly KnowledgeRepository $knowledge,
	) {}

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$stats       = $this->cacheService->stats();
		$runs        = $this->readerService->recentRuns( 1 );
		$last_run    = $runs[0] ?? null;
		$bot_enabled = (bool) $this->settings->get( 'general', 'enabled', true );
		$api_enabled = (bool) $this->settings->get( 'api', 'enabled', false );

		$cached_pages = 0;
		$cached_posts = 0;
		foreach ( $stats as $row ) {
			if ( 'page' === $row['source_type'] ) { $cached_pages = (int) $row['total']; }
			if ( 'post' === $row['source_type'] ) { $cached_posts = (int) $row['total']; }
		}

		$cache_total = array_sum( array_column( $stats, 'total' ) );

		$cards = array(
			array(
				'title' => __( 'Bot Status', 'cms-suggestion-bot' ),
				'value' => $bot_enabled ? '🟢 ' . __( 'Active', 'cms-suggestion-bot' ) : '⚪ ' . __( 'Disabled', 'cms-suggestion-bot' ),
			),
			array(
				'title' => __( 'API Status', 'cms-suggestion-bot' ),
				'value' => $api_enabled ? '🟢 ' . __( 'Enabled', 'cms-suggestion-bot' ) : '⚪ ' . __( 'Disabled', 'cms-suggestion-bot' ),
			),
			array( 'title' => __( 'Cache Status', 'cms-suggestion-bot' ), 'value' => number_format_i18n( $cache_total ), 'sub' => __( 'entries total', 'cms-suggestion-bot' ) ),
			array( 'title' => __( 'Cached Pages', 'cms-suggestion-bot' ), 'value' => number_format_i18n( $cached_pages ) ),
			array( 'title' => __( 'Cached Posts', 'cms-suggestion-bot' ), 'value' => number_format_i18n( $cached_posts ) ),
			array( 'title' => __( 'Knowledge Size', 'cms-suggestion-bot' ), 'value' => number_format_i18n( $this->knowledge->count() ), 'sub' => __( 'entries', 'cms-suggestion-bot' ) ),
			array(
				'title' => __( 'Last Reader Scan', 'cms-suggestion-bot' ),
				'value' => $last_run ? esc_html( $last_run['reader_type'] . ' - ' . $last_run['status'] ) : __( 'Never run yet.', 'cms-suggestion-bot' ),
				'sub'   => $last_run ? (string) ( $last_run['finished_at'] ?? $last_run['started_at'] ) : '',
			),
			array( 'title' => __( 'Database Size', 'cms-suggestion-bot' ), 'value' => $this->databaseSizeMb() . ' MB' ),
			array( 'title' => __( 'Memory Usage', 'cms-suggestion-bot' ), 'value' => round( memory_get_usage( true ) / 1048576, 2 ) . ' MB' ),
		);

		$cache_by_type = $stats;

		extract( compact( 'cards', 'cache_by_type' ) ); // phpcs:ignore WordPress.PHP.DontExtract
		include CSB_PLUGIN_DIR . '/templates/admin/dashboard.php';
	}

	private function databaseSizeMb(): float {
		global $wpdb;
		$prefix = $wpdb->prefix . CSB_TABLE_PREFIX;
		$size   = $wpdb->get_var( $wpdb->prepare(
			"SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2)
			 FROM information_schema.TABLES
			 WHERE table_schema = %s AND table_name LIKE %s",
			DB_NAME,
			$wpdb->esc_like( $prefix ) . '%'
		) );

		return null === $size ? 0.0 : (float) $size;
	}
}
