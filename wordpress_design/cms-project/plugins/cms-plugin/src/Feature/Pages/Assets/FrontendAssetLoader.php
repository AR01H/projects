<?php

namespace Ah\Cms\Feature\Pages\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Asset Loader — loads CSS/JS only when needed on the frontend.
 * Replaces the current approach of loading everything on every page.
 */
class FrontendAssetLoader {

	/**
	 * Register shared (critical) assets — loaded on every page.
	 */
	public static function registerCritical(): void {
		wp_enqueue_style(
			'ah-variables',
			AH_PLUGIN_URL . '/assets/css/variables.css',
			[],
			AH_PLUGIN_VERSION
		);

		wp_enqueue_style(
			'ah-animations',
			AH_PLUGIN_URL . '/assets/css/animations.css',
			[ 'ah-variables' ],
			AH_PLUGIN_VERSION
		);

		wp_enqueue_style(
			'ah-main',
			AH_PLUGIN_URL . '/assets/css/main.css',
			[ 'ah-variables', 'ah-animations' ],
			AH_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'ah-main',
			AH_PLUGIN_URL . '/assets/js/main.js',
			[ 'jquery' ],
			AH_PLUGIN_VERSION,
			true
		);

		wp_localize_script( 'ah-main', 'ahTheme', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ah_frontend_nonce' ),
		] );
	}

	/**
	 * Enqueue page-specific assets.
	 */
	public static function enqueuePageSpecific( string $template ): void {
		$templateName = self::getTemplateName( $template );

		// Builder page
		if ( 'TemplateBuilderPage' === $templateName ) {
			wp_enqueue_style(
				'ah-builder-page',
				AH_PLUGIN_URL . '/assets/css/builder-page.css',
				[ 'ah-variables' ],
				AH_PLUGIN_VERSION
			);
		}
	}

	/**
	 * Extract template name from path.
	 */
	private static function getTemplateName( string $template ): string {
		$filename = basename( $template, '.php' );
		return $filename;
	}
}
