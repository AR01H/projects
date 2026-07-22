<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

use CmsSuggestionBot\Services\ReaderService;

defined( 'ABSPATH' ) || exit;

/**
 * Reader submenu - what content types can be read, and a log of past scans.
 * Actually running a scan happens via the Generate Cache action on Admin
 * Tools (Reader + CacheBuilder are one pipeline); this page is informational.
 */
final class ReaderPage {

	public function __construct( private readonly ReaderService $readerService ) {}

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$sources = $this->readerService->sources();
		$runs    = $this->readerService->recentRuns( 30 );

		include CSB_PLUGIN_DIR . '/templates/admin/reader.php';
	}
}
