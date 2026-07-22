<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

use CmsSuggestionBot\Readers\ReaderManager;
use CmsSuggestionBot\Services\CacheService;
use CmsSuggestionBot\Services\MaintenanceService;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Tools submenu. Generate/Destroy/Rebuild Cache run over AJAX
 * (Ajax\CacheAjaxController); everything else here is a plain POST + redirect
 * since they're one-off actions that don't need a progress UI.
 */
final class AdminToolsPage {

	public function __construct(
		private readonly ReaderManager $readers,
		private readonly CacheService $cacheService,
		private readonly MaintenanceService $maintenance,
	) {}

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$this->maybeExport(); // Exits directly when triggered.
		$notice = $this->maybeHandlePost();

		$sources = array_map(
			static fn( $reader ) => array( 'type' => $reader->type(), 'label' => $reader->label(), 'available' => $reader->isAvailable() ),
			$this->readers->all()
		);
		$stats = $this->cacheService->stats();

		include CSB_PLUGIN_DIR . '/templates/admin/admin-tools.php';
	}

	private function maybeExport(): void {
		if ( ! isset( $_GET['csb_action'] ) || 'export_cache' !== $_GET['csb_action'] ) {
			return;
		}
		check_admin_referer( 'csb_export_cache' );

		$json = $this->maintenance->exportCache();

		nocache_headers();
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="cms-suggestion-bot-cache-' . gmdate( 'Y-m-d' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	private function maybeHandlePost(): string {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['csb_tool_action'] ) ) {
			return '';
		}

		check_admin_referer( 'csb_admin_tools' );
		$action = sanitize_key( wp_unslash( $_POST['csb_tool_action'] ) );

		return match ( $action ) {
			'clear_knowledge' => sprintf(
				/* translators: %d: number of entries cleared */
				__( 'Cleared %d knowledge base entries.', 'cms-suggestion-bot' ),
				$this->maintenance->clearKnowledge()
			),
			'repair_db' => sprintf(
				/* translators: %d: number of tables repaired */
				__( 'Repaired %d tables.', 'cms-suggestion-bot' ),
				count( $this->maintenance->repairTables() )
			),
			'optimize_tables' => sprintf(
				/* translators: %d: number of tables optimized */
				__( 'Optimized %d tables.', 'cms-suggestion-bot' ),
				count( $this->maintenance->optimizeTables() )
			),
			'import_cache' => $this->handleImport(),
			default => '',
		};
	}

	private function handleImport(): string {
		if ( empty( $_FILES['csb_import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['csb_import_file']['tmp_name'] ) ) {
			return __( 'No file uploaded.', 'cms-suggestion-bot' );
		}

		$json  = (string) file_get_contents( $_FILES['csb_import_file']['tmp_name'] );
		$count = $this->maintenance->importCache( $json );

		/* translators: %d: number of entries imported */
		return sprintf( __( 'Imported %d cache entries.', 'cms-suggestion-bot' ), $count );
	}
}
