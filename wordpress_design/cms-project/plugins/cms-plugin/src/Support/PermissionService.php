<?php
namespace Ah\Cms\Support;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Config\Capabilities;

/**
 * Permission Service
 *
 * Central permission checker for all CMS features.
 * Admin (manage_options) always has full access.
 * Others must have the specific capability.
 *
 * @package Ah\Cms\Support
 */

class PermissionService {

	/**
	 * Check if current user can perform an action on a feature.
	 *
	 * @param string $feature  Feature name (e.g., 'pages', 'posts', 'reviews')
	 * @param string $action   Action name: 'view', 'edit', 'delete'
	 * @return bool
	 */
	public static function can( string $feature, string $action = 'view' ): bool {
		// Admin always has full access
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$cap = 'ah_' . $feature . '_' . $action;
		return current_user_can( $cap );
	}

	/**
	 * Check if current user can view a feature.
	 */
	public static function canView( string $feature ): bool {
		return self::can( $feature, 'view' );
	}

	/**
	 * Check if current user can edit a feature.
	 */
	public static function canEdit( string $feature ): bool {
		return self::can( $feature, 'edit' );
	}

	/**
	 * Check if current user can delete a feature.
	 */
	public static function canDelete( string $feature ): bool {
		return self::can( $feature, 'delete' );
	}

	/**
	 * Get the required capability string for a feature action.
	 * Useful for add_menu_page / add_submenu_page $capability parameter.
	 */
	public static function getMenuCapability( string $feature ): string {
		// Admin sees everything
		if ( current_user_can( 'manage_options' ) ) {
			return 'manage_options';
		}
		return 'ah_' . $feature . '_view';
	}

	/**
	 * Enforce permission or die with 403.
	 */
	public static function enforce( string $feature, string $action = 'view' ): void {
		if ( ! self::can( $feature, $action ) ) {
			wp_die(
				sprintf(
					'You do not have permission to %s %s.',
					$action,
					$feature
				),
				'Permission Denied',
				[ 'response' => 403 ]
			);
		}
	}

	/**
	 * Get all capabilities the current user has for a feature.
	 */
	public static function getUserCapabilities( string $feature ): array {
		$all = Capabilities::getFeature( $feature );
		$user_caps = [];
		foreach ( array_keys( $all ) as $cap ) {
			$user_caps[ $cap ] = current_user_can( $cap );
		}
		return $user_caps;
	}

	/**
	* Get the current user's CMS admin role (from ah_admin_roles table).
	* Returns null if user is a standard WP admin.
	*/
	public static function getCmsRole(): ?object {
		if ( current_user_can( 'manage_options' ) ) {
			return null; // Standard WP admin — no custom CMS role
		}

		global $wpdb;
		$user_id = get_current_user_id();
		$role = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.* FROM {$wpdb->prefix}ah_admin_roles r
			 INNER JOIN {$wpdb->prefix}ah_admin_users u ON u.role_id = r.id
			 WHERE u.wp_user_id = %d",
			$user_id
		) );

		return $role ?: null;
	}
}
