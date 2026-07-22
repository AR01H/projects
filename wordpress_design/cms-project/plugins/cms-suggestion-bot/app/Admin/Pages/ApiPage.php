<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

use CmsSuggestionBot\Services\ApiKeyService;
use CmsSuggestionBot\Services\SettingsService;

defined( 'ABSPATH' ) || exit;

/**
 * API submenu - enable/inspect the API status (actual toggle lives on
 * Configuration -> API) and manage API keys for app/API callers.
 */
final class ApiPage {

	private ?string $newlyIssuedKey = null;

	public function __construct(
		private readonly ApiKeyService $apiKeys,
		private readonly SettingsService $settings,
	) {}

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$notice = $this->maybeHandlePost();

		$api_enabled = (bool) $this->settings->get( 'api', 'enabled', false );
		$keys        = $this->apiKeys->all();
		$new_key     = $this->newlyIssuedKey ?? null;

		include CSB_PLUGIN_DIR . '/templates/admin/api.php';
	}

	private function maybeHandlePost(): string {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return '';
		}

		if ( isset( $_POST['csb_api_issue'] ) ) {
			check_admin_referer( 'csb_api_keys' );
			$label = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
			$this->newlyIssuedKey = $this->apiKeys->issue( '' !== $label ? $label : __( 'Untitled key', 'cms-suggestion-bot' ) );

			return __( 'New API key issued - copy it now, it will not be shown again in full.', 'cms-suggestion-bot' );
		}

		if ( isset( $_POST['csb_api_revoke'] ) ) {
			check_admin_referer( 'csb_api_keys' );
			$this->apiKeys->revoke( (int) ( $_POST['id'] ?? 0 ) );

			return __( 'Key revoked.', 'cms-suggestion-bot' );
		}

		if ( isset( $_POST['csb_api_delete'] ) ) {
			check_admin_referer( 'csb_api_keys' );
			$this->apiKeys->delete( (int) ( $_POST['id'] ?? 0 ) );

			return __( 'Key deleted.', 'cms-suggestion-bot' );
		}

		return '';
	}
}
