<?php
/**
 * admin/tabs/calculators/sub-list.php - per-calculator controls.
 *
 * Lists every registered calculator (calculators/registry.php) and lets the
 * admin set: enabled, display label, help text and a "read guide" link.
 * Saved to the adn_calculators_meta option by ADN_Theme_Admin::handle_save_calc_list().
 */

defined( 'ABSPATH' ) || exit;

$calcs = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Calculator List', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Every registered calculator. Enable it, give it a label, help text and a "read guide" link. Embed any calculator on a page or post with the shortcode shown on each card.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<?php if ( empty( $calcs ) ) : ?>
		<p><?php esc_html_e( 'No calculators are registered yet.', ADN_TEXT_DOMAIN ); ?></p>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="adn_save_calc_list">
			<?php wp_nonce_field( 'adn_save_calc_list' ); ?>

			<?php foreach ( $calcs as $key => $calc ) : ?>
				<?php $meta = adn_calculator_meta( $key ); ?>
				<div class="card" style="max-width:none;margin:14px 0;background:#fafafa;">
					<h3 style="margin-top:0;">
						<?php echo esc_html( isset( $calc['icon'] ) ? $calc['icon'] : '🧮' ); ?>
						<?php echo esc_html( isset( $calc['label'] ) ? $calc['label'] : $key ); ?>
						<code style="font-weight:normal;font-size:12px;">[ah_calculator key="<?php echo esc_attr( $key ); ?>"]</code>
					</h3>
					<table class="form-table" role="presentation"><tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Enabled', ADN_TEXT_DOMAIN ); ?></th>
							<td><label><input type="checkbox" name="calc[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( ! empty( $meta['enabled'] ) ); ?>> <?php esc_html_e( 'Show this calculator (the shortcode renders nothing when disabled)', ADN_TEXT_DOMAIN ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Label', ADN_TEXT_DOMAIN ); ?></th>
							<td><input type="text" class="regular-text" name="calc[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $meta['label'] ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Help text', ADN_TEXT_DOMAIN ); ?></th>
							<td><textarea class="large-text" rows="2" name="calc[<?php echo esc_attr( $key ); ?>][help]"><?php echo esc_textarea( $meta['help'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Optional note shown under the calculator.', ADN_TEXT_DOMAIN ); ?></p></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Read-guide label', ADN_TEXT_DOMAIN ); ?></th>
							<td><input type="text" class="regular-text" name="calc[<?php echo esc_attr( $key ); ?>][guide_label]" value="<?php echo esc_attr( $meta['guide_label'] ); ?>" placeholder="Read the guide →"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Read-guide URL', ADN_TEXT_DOMAIN ); ?></th>
							<td><input type="text" class="regular-text" name="calc[<?php echo esc_attr( $key ); ?>][guide_url]" value="<?php echo esc_attr( $meta['guide_url'] ); ?>" placeholder="/guides/stamp-duty/"></td>
						</tr>
					</tbody></table>
				</div>
			<?php endforeach; ?>

			<?php submit_button( __( 'Save Calculator List', ADN_TEXT_DOMAIN ) ); ?>
		</form>
	<?php endif; ?>
</div>
