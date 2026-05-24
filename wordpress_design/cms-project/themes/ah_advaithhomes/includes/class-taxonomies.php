<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Taxonomies
 * Registers all WordPress taxonomies used by this theme.
 *
 * Built-in WP taxonomies (no registration needed):
 *   category  - blog post categories (Buying Guides, Finance, Legal, Market Updates)
 *   post_tag  - blog post tags
 *
 * Custom taxonomies registered here:
 *   ah_highlight      - Highlighter Names  (tag content as Featured, Popular, etc.)
 *   ah_data_protected - DataProtected      (GDPR / data-handling classification)
 *
 * Usage in functions.php:
 *   AH_Taxonomies::register();
 */
class AH_Taxonomies {

	public static function register(): void {
		add_action( 'init', [ self::class, 'register_all' ] );
	}

	public static function register_all(): void {
		self::register_highlight();
		self::register_data_protected();
	}

	// ── Highlighter Names ─────────────────────────────────────────────────────

	private static function register_highlight(): void {
		register_taxonomy(
			'ah_highlight',
			[ 'post', 'page' ],
			[
				'labels'             => [
					'name'              => TXT_HIGHLIGHTER_NAMES,
					'singular_name'     => TXT_HIGHLIGHTER_NAME,
					'menu_name'         => TXT_HIGHLIGHTER_NAMES,
					'add_new_item'      => TXT_ADD_HIGHLIGHTER_NAME,
					'new_item_name'     => TXT_NEW_HIGHLIGHTER_NAME,
					'search_items'      => TXT_SEARCH_HIGHLIGHTER_NAMES,
					'all_items'         => TXT_ALL_HIGHLIGHTER_NAMES,
					'edit_item'         => TXT_EDIT_HIGHLIGHTER_NAME,
					'update_item'       => TXT_UPDATE_HIGHLIGHTER_NAME,
					'not_found'         => TXT_NO_HIGHLIGHTER_NAMES_FOUND,
				],
				'hierarchical'       => false,
				'public'             => true,
				'show_ui'            => true,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
				'rewrite'            => [ 'slug' => 'highlight' ],
			]
		);
	}

	// ── DataProtected ─────────────────────────────────────────────────────────

	private static function register_data_protected(): void {
		register_taxonomy(
			'ah_data_protected',
			[ 'post', 'page' ],
			[
				'labels'             => [
					'name'              => TXT_DATAPROTECTED,
					'singular_name'     => TXT_DATAPROTECTED,
					'menu_name'         => TXT_DATAPROTECTED,
					'add_new_item'      => TXT_ADD_DATAPROTECTED_LEVEL,
					'new_item_name'     => TXT_NEW_DATAPROTECTED_LEVEL,
					'search_items'      => TXT_SEARCH_DATAPROTECTED_LEVELS,
					'all_items'         => TXT_ALL_DATAPROTECTED_LEVELS,
					'edit_item'         => TXT_EDIT_DATAPROTECTED_LEVEL,
					'update_item'       => TXT_UPDATE_DATAPROTECTED_LEVEL,
					'not_found'         => TXT_NO_LEVELS_FOUND,
				],
				'hierarchical'       => false,
				'public'             => false,  // internal classification - not in front-end URLs
				'show_ui'            => true,
				'show_in_rest'       => true,
				'show_admin_column'  => false,
				'rewrite'            => false,
			]
		);
	}
}
