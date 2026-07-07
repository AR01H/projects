<?php
/**
 * Admin Tools -> Database. Status of every table in config/database.php
 * with a per-row "Install / Repair" button (one-by-one), plus the
 * "Install All" tool card from the registry (group 'database').
 */

defined( 'ABSPATH' ) || exit;

nt_admin_tools_render( 'database' );

$nt_status = nt_db_status();
?>

<h2><?php esc_html_e( 'Registered Tables', NT_TEXT_DOMAIN ); ?></h2>

<?php if ( ! $nt_status ) : ?>
	<p><?php esc_html_e( 'No tables registered yet. Add entries in config/database.php.', NT_TEXT_DOMAIN ); ?></p>
<?php else : ?>
	<table class="widefat striped nt-admin-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Key', NT_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Table', NT_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Description', NT_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Status', NT_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Action', NT_TEXT_DOMAIN ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $nt_status as $nt_key => $nt_row ) : ?>
				<tr>
					<td><code><?php echo esc_html( (string) $nt_key ); ?></code></td>
					<td><code><?php echo esc_html( $nt_row['table'] ); ?></code></td>
					<td><?php echo esc_html( $nt_row['desc'] ); ?></td>
					<td>
						<?php if ( $nt_row['exists'] ) : ?>
							<span style="color:#16a34a;font-weight:600;"><?php esc_html_e( 'Installed', NT_TEXT_DOMAIN ); ?></span>
						<?php else : ?>
							<span style="color:#dc2626;font-weight:600;"><?php esc_html_e( 'MISSING', NT_TEXT_DOMAIN ); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
							<input type="hidden" name="action" value="nt_tool_install_table">
							<input type="hidden" name="table_key" value="<?php echo esc_attr( (string) $nt_key ); ?>">
							<?php nt_admin_location_fields(); ?>
							<?php wp_nonce_field( 'nt_tool_install_table' ); ?>
							<?php submit_button( $nt_row['exists'] ? __( 'Repair', NT_TEXT_DOMAIN ) : __( 'Install', NT_TEXT_DOMAIN ), 'secondary small', 'submit', false ); ?>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
