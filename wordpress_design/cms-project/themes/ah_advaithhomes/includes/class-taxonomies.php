<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Taxonomies
 * Registers all WordPress taxonomies used by this theme.
 *
 * Built-in WP taxonomies (no registration needed):
 *   category  — blog post categories (Buying Guides, Finance, Legal, Market Updates)
 *   post_tag  — blog post tags
 *
 * Custom taxonomies registered here:
 *   ah_highlight      — Highlighter Names  (tag content as Featured, Popular, etc.)
 *   ah_data_protected — DataProtected      (GDPR / data-handling classification)
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
					'name'              => __( 'Highlighter Names', 'ah-theme' ),
					'singular_name'     => __( 'Highlighter Name', 'ah-theme' ),
					'menu_name'         => __( 'Highlighter Names', 'ah-theme' ),
					'add_new_item'      => __( 'Add Highlighter Name', 'ah-theme' ),
					'new_item_name'     => __( 'New Highlighter Name', 'ah-theme' ),
					'search_items'      => __( 'Search Highlighter Names', 'ah-theme' ),
					'all_items'         => __( 'All Highlighter Names', 'ah-theme' ),
					'edit_item'         => __( 'Edit Highlighter Name', 'ah-theme' ),
					'update_item'       => __( 'Update Highlighter Name', 'ah-theme' ),
					'not_found'         => __( 'No highlighter names found.', 'ah-theme' ),
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
					'name'              => __( 'DataProtected', 'ah-theme' ),
					'singular_name'     => __( 'DataProtected', 'ah-theme' ),
					'menu_name'         => __( 'DataProtected', 'ah-theme' ),
					'add_new_item'      => __( 'Add DataProtected Level', 'ah-theme' ),
					'new_item_name'     => __( 'New DataProtected Level', 'ah-theme' ),
					'search_items'      => __( 'Search DataProtected Levels', 'ah-theme' ),
					'all_items'         => __( 'All DataProtected Levels', 'ah-theme' ),
					'edit_item'         => __( 'Edit DataProtected Level', 'ah-theme' ),
					'update_item'       => __( 'Update DataProtected Level', 'ah-theme' ),
					'not_found'         => __( 'No levels found.', 'ah-theme' ),
				],
				'hierarchical'       => false,
				'public'             => false,  // internal classification — not in front-end URLs
				'show_ui'            => true,
				'show_in_rest'       => true,
				'show_admin_column'  => false,
				'rewrite'            => false,
			]
		);
	}
}
