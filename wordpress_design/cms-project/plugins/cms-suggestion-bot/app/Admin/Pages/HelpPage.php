<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Help submenu - static reference material. No data dependencies, so this
 * class only enforces capability and includes its template.
 */
final class HelpPage {

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		include CSB_PLUGIN_DIR . '/templates/admin/help.php';
	}
}
