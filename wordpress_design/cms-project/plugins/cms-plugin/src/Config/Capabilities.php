<?php
namespace Ah\Cms\Config;

defined( 'ABSPATH' ) || exit;

/**
 * Feature Capabilities Configuration
 *
 * Defines read/view/edit/delete capabilities for each feature and sub-feature.
 * WordPress roles can be assigned these capabilities for granular access control.
 *
 * @package Ah\Cms\Config
 */

class Capabilities {

	/**
	 * All feature capabilities.
	 * Format: 'ah_{feature}_{action}' => 'Description'
	 *
	 * Actions: view (read), edit, delete
	 * Admin always has full access via 'manage_options'.
	 */
	public static function getAll(): array {
		return [
			// ── Pages ──────────────────────────────────────────────
			'ah_pages_view'          => 'View Pages',
			'ah_pages_edit'          => 'Edit Pages',
			'ah_pages_delete'        => 'Delete Pages',
			'ah_pages_builder_view'  => 'View Page Builder',
			'ah_pages_builder_edit'  => 'Edit Page Builder',
			'ah_pages_static_view'   => 'View Static Pages',
			'ah_pages_static_edit'   => 'Edit Static Pages',

			// ── Posts ──────────────────────────────────────────────
			'ah_posts_view'          => 'View Blog Posts',
			'ah_posts_edit'          => 'Edit Blog Posts',
			'ah_posts_delete'        => 'Delete Blog Posts',

			// ── Taxonomy ──────────────────────────────────────────
			'ah_taxonomy_view'       => 'View Taxonomy',
			'ah_taxonomy_edit'       => 'Edit Taxonomy',
			'ah_taxonomy_delete'     => 'Delete Taxonomy',

			// ── Navigation ────────────────────────────────────────
			'ah_navigation_view'     => 'View Navigation',
			'ah_navigation_edit'     => 'Edit Navigation',

			// ── Reviews ───────────────────────────────────────────
			'ah_reviews_view'        => 'View Reviews',
			'ah_reviews_edit'        => 'Edit Reviews',
			'ah_reviews_delete'      => 'Delete Reviews',

			// ── FAQs ──────────────────────────────────────────────
			'ah_faqs_view'           => 'View FAQs',
			'ah_faqs_edit'           => 'Edit FAQs',
			'ah_faqs_delete'         => 'Delete FAQs',

			// ── Resources ─────────────────────────────────────────
			'ah_resources_view'      => 'View Resources',
			'ah_resources_edit'      => 'Edit Resources',
			'ah_resources_delete'    => 'Delete Resources',

			// ── Spotlights ────────────────────────────────────────
			'ah_spotlights_view'     => 'View Spotlights',
			'ah_spotlights_edit'     => 'Edit Spotlights',
			'ah_spotlights_delete'   => 'Delete Spotlights',

			// ── Banners ───────────────────────────────────────────
			'ah_banners_view'        => 'View Banners',
			'ah_banners_edit'        => 'Edit Banners',
			'ah_banners_delete'      => 'Delete Banners',

			// ── Events ────────────────────────────────────────────
			'ah_events_view'         => 'View Events',
			'ah_events_edit'         => 'Edit Events',
			'ah_events_delete'       => 'Delete Events',

			// ── Site Notices ──────────────────────────────────────
			'ah_notices_view'        => 'View Site Notices',
			'ah_notices_edit'        => 'Edit Site Notices',
			'ah_notices_delete'      => 'Delete Site Notices',

			// ── News Bar ──────────────────────────────────────────
			'ah_news_bar_view'       => 'View News Bar',
			'ah_news_bar_edit'       => 'Edit News Bar',
			'ah_news_bar_delete'     => 'Delete News Bar',

			// ── Featured In ───────────────────────────────────────
			'ah_featured_in_view'    => 'View Featured In',
			'ah_featured_in_edit'    => 'Edit Featured In',
			'ah_featured_in_delete'  => 'Delete Featured In',

			// ── Media ─────────────────────────────────────────────
			'ah_media_view'          => 'View Media',
			'ah_media_edit'          => 'Edit Media',
			'ah_media_delete'        => 'Delete Media',

			// ── File Links ────────────────────────────────────────
			'ah_file_links_view'     => 'View File Links',
			'ah_file_links_edit'     => 'Edit File Links',
			'ah_file_links_delete'   => 'Delete File Links',

			// ── Visitors ──────────────────────────────────────────
			'ah_visitors_view'       => 'View Visitor Stats',

			// ── Analytics ─────────────────────────────────────────
			'ah_analytics_view'      => 'View Analytics Reports',

			// ── Audit ─────────────────────────────────────────────
			'ah_audit_view'          => 'View Audit Log',

			// ── Form Builder ──────────────────────────────────────
			'ah_forms_view'          => 'View Forms',
			'ah_forms_edit'          => 'Edit Forms',
			'ah_forms_delete'        => 'Delete Forms',
			'ah_forms_submissions'   => 'View Form Submissions',

			// ── Newsletter ────────────────────────────────────────
			'ah_newsletter_view'     => 'View Newsletter',
			'ah_newsletter_edit'     => 'Edit Newsletter',
			'ah_newsletter_subscribers' => 'View Newsletter Subscribers',

			// ── Workflow ──────────────────────────────────────────
			'ah_workflow_view'       => 'View Workflow Rules',
			'ah_workflow_edit'       => 'Edit Workflow Rules',
			'ah_workflow_delete'     => 'Delete Workflow Rules',

			// ── Redirects ─────────────────────────────────────────
			'ah_redirects_view'      => 'View Redirect Rules',
			'ah_redirects_edit'      => 'Edit Redirect Rules',
			'ah_redirects_delete'    => 'Delete Redirect Rules',

			// ── Custom Code ───────────────────────────────────────
			'ah_custom_code_view'    => 'View Custom Code',
			'ah_custom_code_edit'    => 'Edit Custom Code',
			'ah_custom_code_delete'  => 'Delete Custom Code',

			// ── Settings ──────────────────────────────────────────
			'ah_settings_view'       => 'View Settings',
			'ah_settings_edit'       => 'Edit Settings',

			// ── Import ────────────────────────────────────────────
			'ah_import_view'         => 'View Import',
			'ah_import_edit'         => 'Import Data',

			// ── Cache ─────────────────────────────────────────────
			'ah_cache_view'          => 'View Cache',
			'ah_cache_edit'          => 'Manage Cache',

			// ── Admin Tools ───────────────────────────────────────
			'ah_admin_tools_view'    => 'View Admin Tools',
			'ah_admin_tools_edit'    => 'Use Admin Tools',
		];
	}

	/**
	 * Get capabilities for a specific feature.
	 */
	public static function getFeature( string $feature ): array {
		$all = self::getAll();
		$prefix = 'ah_' . $feature . '_';
		return array_filter(
			$all,
			fn( $key ) => str_starts_with( $key, $prefix ),
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Get all capability keys grouped by feature.
	 */
	public static function getGrouped(): array {
		$all = self::getAll();
		$grouped = [];
		foreach ( $all as $cap => $desc ) {
			// Extract feature name: ah_{feature}_{action} → {feature}
			$parts = explode( '_', $cap );
			$feature = $parts[1] ?? 'unknown';
			$grouped[ $feature ][ $cap ] = $desc;
		}
		return $grouped;
	}

	/**
	 * Register all CMS capabilities with WordPress.
	 * Called once on plugin activation.
	 */
	public static function register(): void {
		global $wp_roles;
		if ( ! $wp_roles ) {
			return;
		}

		$all = self::getAll();
		foreach ( $wp_roles->roles as $role_name => $role ) {
			foreach ( array_keys( $all ) as $cap ) {
				if ( ! isset( $role['capabilities'][ $cap ] ) ) {
					$wp_roles->add_cap( $role_name, $cap, false );
				}
			}
		}
	}

	/**
	 * Get all WordPress roles.
	 */
	public static function getRoles(): array {
		global $wp_roles;
		if ( ! $wp_roles ) {
			return [];
		}
		return $wp_roles->get_names();
	}
}
