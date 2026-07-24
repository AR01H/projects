<?php

namespace Ah\Cms\Feature\Pages\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Asset Loader — conditionally loads CSS/JS for admin pages.
 * Only loads assets when the current admin page needs them.
 */
class AdminAssetLoader {

	/** Map of page slugs to their required assets. */
	private static array $pageAssets = [
		'ah-dashboard'      => [ 'css' => [], 'js' => [] ],
		'ah-settings'       => [ 'css' => [ 'settings' ], 'js' => [] ],
		'ah-pages'          => [ 'css' => [ 'pages' ], 'js' => [] ],
		'ah-page-builder'   => [ 'css' => [ 'pages', 'builder' ], 'js' => [ 'pages' ] ],
		'ah-static-pages'   => [ 'css' => [ 'pages' ], 'js' => [] ],
		'ah-posts'          => [ 'css' => [ 'posts' ], 'js' => [] ],
		'ah-taxonomy'       => [ 'css' => [ 'taxonomy' ], 'js' => [] ],
		'ah-navigation'     => [ 'css' => [ 'navigation' ], 'js' => [] ],
		'ah-reviews'        => [ 'css' => [ 'reviews' ], 'js' => [] ],
		'ah-faqs'           => [ 'css' => [ 'faqs' ], 'js' => [] ],
		'ah-resources'      => [ 'css' => [ 'resources' ], 'js' => [] ],
		'ah-spotlights'     => [ 'css' => [ 'spotlights' ], 'js' => [] ],
		'ah-banners'        => [ 'css' => [ 'banners' ], 'js' => [] ],
		'ah-events'         => [ 'css' => [ 'events' ], 'js' => [] ],
		'ah-notices'        => [ 'css' => [ 'notices' ], 'js' => [] ],
		'ah-news-bar'       => [ 'css' => [ 'news-bar' ], 'js' => [] ],
		'ah-featured-in'    => [ 'css' => [ 'featured-in' ], 'js' => [] ],
		'ah-media'          => [ 'css' => [ 'media' ], 'js' => [] ],
		'ah-file-links'     => [ 'css' => [ 'file-links' ], 'js' => [] ],
		'ah-visitors'       => [ 'css' => [ 'visitors' ], 'js' => [] ],
		'ah-analytics'      => [ 'css' => [ 'analytics' ], 'js' => [ 'analytics' ] ],
		'ah-audit'          => [ 'css' => [ 'audit' ], 'js' => [] ],
		'ah-import'         => [ 'css' => [ 'import' ], 'js' => [ 'import' ] ],
		'ah-admin-tools'    => [ 'css' => [ 'admin-tools' ], 'js' => [] ],
		'ah-custom-code'    => [ 'css' => [ 'custom-code' ], 'js' => [ 'custom-code' ] ],
		'ah-redirects'      => [ 'css' => [ 'redirects' ], 'js' => [] ],
		'ah-form-builder'   => [ 'css' => [ 'form-builder' ], 'js' => [ 'form-builder' ] ],
		'ah-newsletter'     => [ 'css' => [ 'newsletter' ], 'js' => [] ],
		'ah-workflow'       => [ 'css' => [ 'workflow' ], 'js' => [ 'workflow' ] ],
		'ah-cache'          => [ 'css' => [ 'cache' ], 'js' => [] ],
	];

	/**
	 * Enqueue assets for the current admin page.
	 */
	public static function enqueue( string $hook ): void {
		$slug = self::getSlug( $hook );
		if ( ! $slug || ! isset( self::$pageAssets[ $slug ] ) ) {
			return;
		}

		$assets = self::$pageAssets[ $slug ];

		// Enqueue CSS
		foreach ( $assets['css'] as $name ) {
			wp_enqueue_style(
				"ah-{$name}",
				AH_PLUGIN_URL . "/assets/css/admin/{$name}.css",
				[ 'wp-color-picker' ],
				AH_PLUGIN_VERSION
			);
		}

		// Enqueue JS
		foreach ( $assets['js'] as $name ) {
			wp_enqueue_script(
				"ah-{$name}",
				AH_PLUGIN_URL . "/assets/js/admin/{$name}.js",
				[ 'jquery' ],
				AH_PLUGIN_VERSION,
				true
			);
			wp_localize_script( "ah-{$name}", 'ahAdmin', [
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ah_admin_nonce' ),
			] );
		}
	}

	/**
	 * Extract the page slug from the admin hook.
	 */
	private static function getSlug( string $hook ): ?string {
		// Match patterns like: toplevel_page_ah-settings, ah-cms_page_ah-pages
		if ( preg_match( '/[_]page_(ah-[a-z-]+)/', $hook, $m ) ) {
			return $m[1];
		}
		return null;
	}
}
