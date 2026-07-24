<?php
/**
 * admin/tabs/calculators/sub-list.php - per-calculator controls.
 *
 * Lists DB-created calculators with Edit HTML/JS and Delete actions.
 * All per-calculator settings are managed via the Add / Edit page.
 */

defined( 'ABSPATH' ) || exit;

$db_rows = class_exists( 'AH_Calculator_DB' ) ? AH_Calculator_DB::get_all() : array();
$new_url = ADN_Theme_Admin::tab_url( 'calculators', 'new' );
?>

<?php /* ── Custom (DB-stored) calculators ─────────────────────────── */ ?>
<div class="card" style="max-width:none;margin-bottom:24px;">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
		<h2 style="margin:0;"><?php esc_html_e( 'Custom Calculators', ADN_TEXT_DOMAIN ); ?></h2>
		<a href="<?php echo esc_url( $new_url ); ?>" class="button button-primary">
			<?php esc_html_e( '+ Add New Calculator', ADN_TEXT_DOMAIN ); ?>
		</a>
	</div>
	<p class="description" style="margin-bottom:16px;">
		<?php esc_html_e( 'Calculators you created via the admin. Click "Edit HTML/JS" to update the markup, logic and settings.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<?php if ( empty( $db_rows ) ) : ?>
		<p style="color:#6b7280;font-style:italic;">
			<?php esc_html_e( 'No custom calculators yet. Use the "+ Add New Calculator" button above.', ADN_TEXT_DOMAIN ); ?>
		</p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped" style="margin-top:8px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Icon', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Title', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Key', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Shortcode', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Status', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Actions', ADN_TEXT_DOMAIN ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $db_rows as $r ) :
					$edit_url = add_query_arg( 'edit_key', $r['calc_key'], $new_url );
				?>
				<tr>
					<td style="font-size:20px;"><?php echo esc_html( $r['icon'] ); ?></td>
					<td><strong><?php echo esc_html( $r['title'] ); ?></strong></td>
					<td><code><?php echo esc_html( $r['calc_key'] ); ?></code></td>
					<td><code style="font-size:11px;">[ah_calculator key="<?php echo esc_attr( $r['calc_key'] ); ?>"]</code></td>
					<td>
						<?php if ( 'active' === $r['status'] ) : ?>
							<span style="color:#16a34a;font-weight:600;"><?php esc_html_e( 'Active', ADN_TEXT_DOMAIN ); ?></span>
						<?php else : ?>
							<span style="color:#9ca3af;"><?php esc_html_e( 'Inactive', ADN_TEXT_DOMAIN ); ?></span>
						<?php endif; ?>
					</td>
					<td style="display:flex;gap:8px;align-items:center;">
						<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small">
							<?php esc_html_e( 'Edit HTML/JS', ADN_TEXT_DOMAIN ); ?>
						</a>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
							onsubmit="return confirm('<?php echo esc_js( __( 'Delete this calculator? This cannot be undone.', ADN_TEXT_DOMAIN ) ); ?>');"
							style="margin:0;">
							<input type="hidden" name="action"   value="adn_delete_calc">
							<input type="hidden" name="calc_key" value="<?php echo esc_attr( $r['calc_key'] ); ?>">
							<?php wp_nonce_field( 'adn_delete_calc' ); ?>
							<button type="submit" class="button button-small" style="color:#b91c1c;border-color:#b91c1c;">
								<?php esc_html_e( 'Delete', ADN_TEXT_DOMAIN ); ?>
							</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
