<?php
/**
 * admin/tabs/calculators/sub-list.php - per-calculator controls.
 *
 * Top section  : DB-created calculators (editable HTML/JS, deletable).
 * Bottom section: all registered calculators (both file + DB) - settings form.
 */

defined( 'ABSPATH' ) || exit;

$calcs    = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
$db_rows  = class_exists( 'AH_Calculator_DB' ) ? AH_Calculator_DB::get_all() : array();
$db_keys  = array();
foreach ( $db_rows as $r ) { $db_keys[] = $r['calc_key']; }

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
		<?php esc_html_e( 'Calculators you created via the admin. Click "Edit HTML/JS" to update the markup and logic.', ADN_TEXT_DOMAIN ); ?>
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

<?php /* ── All calculators: settings (enabled, label, help, URLs) ──── */ ?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Calculator Settings', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Enable / disable each calculator, set its display label, help text, card link and guide link. Applies to both built-in and custom calculators.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<?php if ( empty( $calcs ) ) : ?>
		<p><?php esc_html_e( 'No calculators are registered yet.', ADN_TEXT_DOMAIN ); ?></p>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="adn_save_calc_list">
			<?php wp_nonce_field( 'adn_save_calc_list' ); ?>

			<?php
			$_cat_labels = function_exists( 'adn_calculator_categories' ) ? adn_calculator_categories() : array(
				'buying'        => __( 'Buying',        ADN_TEXT_DOMAIN ),
				'selling'       => __( 'Selling',       ADN_TEXT_DOMAIN ),
				'moving'        => __( 'Moving Home',   ADN_TEXT_DOMAIN ),
				'mortgage'      => __( 'Mortgage',      ADN_TEXT_DOMAIN ),
				'tax'           => __( 'Tax',           ADN_TEXT_DOMAIN ),
				'affordability' => __( 'Affordability', ADN_TEXT_DOMAIN ),
			);
			foreach ( $calcs as $key => $calc ) :
				$meta       = adn_calculator_meta( $key );
				$is_db      = in_array( $key, $db_keys, true );
				$edit_url   = add_query_arg( 'edit_key', $key, $new_url );
				$saved_cats = isset( $meta['categories'] ) && is_array( $meta['categories'] ) ? $meta['categories'] : array();
			?>
				<div class="card" style="max-width:none;margin:14px 0;background:#fafafa;">
					<h3 style="margin-top:0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
						<span><?php echo esc_html( isset( $calc['icon'] ) ? $calc['icon'] : '🧮' ); ?></span>
						<span><?php echo esc_html( isset( $calc['label'] ) ? $calc['label'] : $key ); ?></span>
						<code style="font-weight:normal;font-size:12px;">[ah_calculator key="<?php echo esc_attr( $key ); ?>"]</code>
						<?php if ( $is_db ) : ?>
							<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small" style="margin-left:auto;">
								<?php esc_html_e( 'Edit HTML/JS', ADN_TEXT_DOMAIN ); ?>
							</a>
						<?php else : ?>
							<span style="margin-left:auto;font-size:11px;color:#9ca3af;font-weight:normal;">
								<?php esc_html_e( 'Built-in', ADN_TEXT_DOMAIN ); ?>
							</span>
						<?php endif; ?>
					</h3>
					<table class="form-table" role="presentation"><tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Enabled', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="calc[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( ! empty( $meta['enabled'] ) ); ?>>
									<?php esc_html_e( 'Show this calculator (shortcode renders nothing when disabled)', ADN_TEXT_DOMAIN ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Label', ADN_TEXT_DOMAIN ); ?></th>
							<td><input type="text" class="regular-text" name="calc[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $meta['label'] ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Description', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<textarea class="large-text" rows="2" name="calc[<?php echo esc_attr( $key ); ?>][desc]"><?php echo esc_textarea( isset( $meta['desc'] ) ? $meta['desc'] : '' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Short description shown in the /calculators/ page listing.', ADN_TEXT_DOMAIN ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Thumbnail Image', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<?php
								$_thumb_id  = isset( $meta['thumbnail_id'] ) ? (int) $meta['thumbnail_id'] : 0;
								$_thumb_url = $_thumb_id ? wp_get_attachment_image_url( $_thumb_id, 'thumbnail' ) : false;
								?>
								<div style="display:flex;gap:10px;align-items:flex-start;">
									<div id="thumb_prev_<?php echo esc_attr( $key ); ?>" style="min-width:80px;">
										<?php if ( $_thumb_url ) : ?>
											<img src="<?php echo esc_url( $_thumb_url ); ?>" style="width:80px;height:60px;object-fit:cover;border-radius:4px;display:block;" alt="">
										<?php else : ?>
											<div style="width:80px;height:60px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#9ca3af;"><?php esc_html_e( 'No image', ADN_TEXT_DOMAIN ); ?></div>
										<?php endif; ?>
									</div>
									<div>
										<input type="hidden"
											id="thumb_id_<?php echo esc_attr( $key ); ?>"
											name="calc[<?php echo esc_attr( $key ); ?>][thumbnail_id]"
											value="<?php echo esc_attr( (string) $_thumb_id ); ?>">
										<button type="button" class="button adn-media-select"
											data-target="thumb_id_<?php echo esc_attr( $key ); ?>"
											data-preview="thumb_prev_<?php echo esc_attr( $key ); ?>">
											<?php esc_html_e( 'Select / Change Image', ADN_TEXT_DOMAIN ); ?>
										</button>
										<?php if ( $_thumb_url ) : ?>
											<button type="button" class="button adn-media-remove"
												data-target="thumb_id_<?php echo esc_attr( $key ); ?>"
												data-preview="thumb_prev_<?php echo esc_attr( $key ); ?>"
												style="margin-left:4px;">
												<?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?>
											</button>
										<?php endif; ?>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Optional card thumbnail. Shown instead of the emoji icon when set.', ADN_TEXT_DOMAIN ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Highlight Badge', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<input type="text" class="regular-text"
									name="calc[<?php echo esc_attr( $key ); ?>][highlight]"
									value="<?php echo esc_attr( isset( $meta['highlight'] ) ? $meta['highlight'] : '' ); ?>"
									placeholder="<?php esc_attr_e( 'e.g. Popular, New, Tax Saving', ADN_TEXT_DOMAIN ); ?>">
								<p class="description"><?php esc_html_e( 'Small badge shown on the calculator card. Leave blank for none.', ADN_TEXT_DOMAIN ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Popular Calculator', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<label>
									<input type="checkbox"
										name="calc[<?php echo esc_attr( $key ); ?>][is_popular]"
										value="1"
										<?php checked( ! empty( $meta['is_popular'] ) ); ?>>
									<?php esc_html_e( 'Feature in the "Popular Calculators" section on the /calculators/ page and home page', ADN_TEXT_DOMAIN ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Hide from Listing', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<label>
									<input type="checkbox"
										name="calc[<?php echo esc_attr( $key ); ?>][hidden_from_listing]"
										value="1"
										<?php checked( ! empty( $meta['hidden_from_listing'] ) ); ?>>
									<?php esc_html_e( 'Hide from the /calculators/ listing page (still usable via shortcode and direct URL)', ADN_TEXT_DOMAIN ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Categories', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<div style="display:flex;flex-wrap:wrap;gap:8px 20px;">
									<?php foreach ( $_cat_labels as $_ck => $_cl ) : ?>
										<label style="white-space:nowrap;">
											<input type="checkbox"
												name="calc[<?php echo esc_attr( $key ); ?>][categories][]"
												value="<?php echo esc_attr( $_ck ); ?>"
												<?php checked( in_array( $_ck, $saved_cats, true ) ); ?>>
											<?php echo esc_html( $_cl ); ?>
										</label>
									<?php endforeach; ?>
								</div>
								<p class="description"><?php esc_html_e( 'Which filter tab(s) this calculator appears under on the /calculators/ page.', ADN_TEXT_DOMAIN ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Help text', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<textarea class="large-text" rows="2" name="calc[<?php echo esc_attr( $key ); ?>][help]"><?php echo esc_textarea( $meta['help'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Optional note shown under the calculator widget itself.', ADN_TEXT_DOMAIN ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Card link URL', ADN_TEXT_DOMAIN ); ?></th>
							<td>
								<input type="text" class="regular-text" name="calc[<?php echo esc_attr( $key ); ?>][card_url]" value="<?php echo esc_attr( $meta['card_url'] ); ?>" placeholder="/calculators/stamp-duty/">
								<p class="description"><?php esc_html_e( 'Where clicking this calculator\'s card on category/guide pages takes the user.', ADN_TEXT_DOMAIN ); ?></p>
							</td>
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

			<?php submit_button( __( 'Save Calculator Settings', ADN_TEXT_DOMAIN ) ); ?>
		</form>
	<?php endif; ?>
</div>

<script>
(function($) {
	'use strict';
	var _frame;
	$(document).on('click', '.adn-media-select', function(e) {
		e.preventDefault();
		var btn       = $(this);
		var targetId  = btn.data('target');
		var previewId = btn.data('preview');
		if (_frame) { _frame.off('select'); }
		_frame = wp.media({
			title   : '<?php echo esc_js( __( 'Select Calculator Thumbnail', ADN_TEXT_DOMAIN ) ); ?>',
			button  : { text: '<?php echo esc_js( __( 'Use this image', ADN_TEXT_DOMAIN ) ); ?>' },
			multiple: false,
			library : { type: 'image' }
		});
		_frame.on('select', function() {
			var att = _frame.state().get('selection').first().toJSON();
			$('#' + targetId).val(att.id);
			var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
			$('#' + previewId).html('<img src="' + src + '" style="width:80px;height:60px;object-fit:cover;border-radius:4px;display:block;" alt="">');
			btn.next('.adn-media-remove').show();
		});
		_frame.open();
	});
	$(document).on('click', '.adn-media-remove', function(e) {
		e.preventDefault();
		var targetId  = $(this).data('target');
		var previewId = $(this).data('preview');
		$('#' + targetId).val('0');
		$('#' + previewId).html('<div style="width:80px;height:60px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#9ca3af;"><?php echo esc_js( __( 'No image', ADN_TEXT_DOMAIN ) ); ?></div>');
	});
}(jQuery));
</script>
