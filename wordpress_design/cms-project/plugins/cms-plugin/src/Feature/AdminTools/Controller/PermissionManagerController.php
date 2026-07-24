<?php
namespace Ah\Cms\Feature\AdminTools\Controller;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Config\Capabilities;
use Ah\Cms\Support\PermissionService;

/**
 * Permission Manager Controller
 *
 * Admin UI for managing feature permissions per role.
 *
 * @package Ah\Cms\Feature\AdminTools\Controller
 */

class PermissionManagerController {

	public static function render(): void {
		PermissionService::enforce( 'admin_tools', 'edit' );

		$roles = Capabilities::getRoles();
		$grouped = Capabilities::getGrouped();
		$updated = false;

		// Handle save
		if ( isset( $_POST['ah_save_permissions'] ) && check_admin_referer( 'ah_permissions_nonce' ) ) {
			global $wp_roles;
			$permissions = $_POST['permissions'] ?? [];

			foreach ( $roles as $role_name => $role_label ) {
				// Remove all CMS capabilities first
				$all_caps = array_keys( Capabilities::getAll() );
				foreach ( $all_caps as $cap ) {
					$wp_roles->remove_cap( $role_name, $cap );
				}

				// Add only checked capabilities
				if ( isset( $permissions[ $role_name ] ) && is_array( $permissions[ $role_name ] ) ) {
					foreach ( $permissions[ $role_name ] as $cap => $val ) {
						if ( $val === '1' ) {
							$wp_roles->add_cap( $role_name, $cap );
						}
					}
				}
			}
			$updated = true;
		}
		?>
		<div class="wrap">
			<h1>Feature Permissions</h1>
			<p>Manage which roles can access each feature. <strong>Administrator</strong> always has full access.</p>

			<?php if ( $updated ): ?>
				<div class="notice notice-success"><p>Permissions saved.</p></div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'ah_permissions_nonce' ); ?>

				<table class="widefat striped">
					<thead>
						<tr>
							<th>Feature</th>
							<th>Capability</th>
							<?php foreach ( $roles as $role_name => $role_label ): ?>
								<th><?php echo esc_html( $role_label ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $grouped as $feature => $caps ): ?>
							<?php foreach ( $caps as $cap => $desc ): ?>
								<tr>
									<td><strong><?php echo esc_html( ucfirst( $feature ) ); ?></strong></td>
									<td><?php echo esc_html( $desc ); ?></td>
									<?php foreach ( $roles as $role_name => $role_label ): ?>
										<td>
											<input type="checkbox"
												name="permissions[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $cap ); ?>]"
												value="1"
												<?php checked( current_user_can( $cap ) ); ?>
												<?php disabled( $role_name === 'administrator' ); ?>
											>
										</td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" name="ah_save_permissions" class="button-primary" value="Save Permissions">
				</p>
			</form>
		</div>
		<?php
	}
}
