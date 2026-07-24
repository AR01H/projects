<?php
/**
 * admin/tabs/experts/sub-banner.php - Expert section global banner settings.
 *
 * Configures a site-wide banner displayed at the top of the Ask an Expert page.
 * Fields: enabled toggle, heading, info text, scrolling marquee items.
 * Stored as option: adn_expert_banner { enabled, heading, info, marquee_items }.
 */

defined( 'ABSPATH' ) || exit;

$saved               = get_option( 'adn_expert_banner', array() );
$enabled             = ! empty( $saved['enabled'] );
$heading             = isset( $saved['heading'] ) ? (string) $saved['heading'] : '';
$info                = isset( $saved['info'] )    ? (string) $saved['info']    : '';
$marquee_items       = ( isset( $saved['marquee_items'] ) && is_array( $saved['marquee_items'] ) )
    ? $saved['marquee_items']
    : array();
$fi_section          = isset( $saved['featured_in_section'] ) ? (string) $saved['featured_in_section'] : '';
$unlock_password     = isset( $saved['unlock_password'] )     ? (string) $saved['unlock_password']     : '';
$_fi_raw             = get_option( 'ah_featured_in_sections', '' );
$_fi_all             = ( $_fi_raw ? json_decode( $_fi_raw, true ) : array() );
$_fi_all             = is_array( $_fi_all ) ? $_fi_all : array();
?>
<div class="card" style="max-width:none;">
	<h2 style="margin-top:0;"><?php esc_html_e( 'Expert Banner', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description" style="margin-bottom:20px;">
		<?php esc_html_e( 'A banner shown at the top of the Ask an Expert page. Use it to add a heading and short info message to introduce your expert team.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_save_expert_banner">
		<?php wp_nonce_field( 'adn_save_expert_banner' ); ?>

		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<table class="form-table" role="presentation"><tbody>

				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Banner', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="banner_enabled" value="1" <?php checked( $enabled ); ?>>
							<?php esc_html_e( 'Show this banner on the Ask an Expert page', ADN_TEXT_DOMAIN ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="banner_heading"><?php esc_html_e( 'Heading', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="banner_heading" name="banner_heading"
							class="large-text"
							value="<?php echo esc_attr( $heading ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Find the Right Expert for Your Property Journey', ADN_TEXT_DOMAIN ); ?>">
						<p class="description">
							<?php esc_html_e( 'Main heading shown in the banner. Keep it short and welcoming.', ADN_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="banner_info"><?php esc_html_e( 'Info / Description', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<textarea id="banner_info" name="banner_info"
							class="large-text" rows="4"
							placeholder="<?php esc_attr_e( 'e.g. Our team of trusted specialists are here to guide you - from planning to execution.', ADN_TEXT_DOMAIN ); ?>"><?php echo esc_textarea( $info ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Supporting text shown below the heading. One or two sentences works best.', ADN_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>

			</tbody></table>
		</div>

		<?php if ( $enabled && ( '' !== $heading || '' !== $info ) ) : ?>
		<div style="background:#f0f9f4;border:1px solid #c6e8d2;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
			<p style="font-size:0.82rem;color:#166534;margin:0 0 6px;font-weight:600;">
				<?php esc_html_e( 'Preview', ADN_TEXT_DOMAIN ); ?>
			</p>
			<?php if ( '' !== $heading ) : ?>
				<h3 style="margin:0 0 6px;font-size:1.15rem;"><?php echo esc_html( $heading ); ?></h3>
			<?php endif; ?>
			<?php if ( '' !== $info ) : ?>
				<p style="margin:0;color:#374151;font-size:0.9rem;"><?php echo esc_html( $info ); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="card" style="max-width:none;background:#fafafa;margin-top:8px;">
			<h3 style="margin-top:0;margin-bottom:4px;"><?php esc_html_e( 'Scrolling Stats Marquee', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:16px;">
				<?php esc_html_e( 'Items that scroll across the bottom of the hero. Each item: icon · value (e.g. "500+") · label. Leave empty to auto-generate from live DB counts.', ADN_TEXT_DOMAIN ); ?>
			</p>

			<div style="display:flex;gap:6px;margin-bottom:8px;font-size:0.8rem;font-weight:600;color:#6b7280;padding:0 2px;">
				<span style="width:64px;"><?php esc_html_e( 'Icon', ADN_TEXT_DOMAIN ); ?></span>
				<span style="flex:1;"><?php esc_html_e( 'Value', ADN_TEXT_DOMAIN ); ?></span>
				<span style="flex:1;"><?php esc_html_e( 'Label', ADN_TEXT_DOMAIN ); ?></span>
				<span style="width:80px;"></span>
			</div>

			<div id="marquee-items-wrap">
				<?php foreach ( $marquee_items as $_mi => $_m ) :
					$_m_icon  = esc_attr( isset( $_m['icon'] )  ? (string) $_m['icon']  : '' );
					$_m_label = esc_attr( isset( $_m['label'] ) ? (string) $_m['label'] : '' );
					$_m_note  = esc_attr( isset( $_m['note'] )  ? (string) $_m['note']  : '' );
				?>
				<div class="marquee-item-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
					<input type="text" name="marquee_items[<?php echo (int) $_mi; ?>][icon]"
						placeholder="icon" maxlength="4" class="small-text"
						value="<?php echo $_m_icon; ?>">
					<input type="text" name="marquee_items[<?php echo (int) $_mi; ?>][label]"
						placeholder="500+" class="regular-text"
						value="<?php echo $_m_label; ?>">
					<input type="text" name="marquee_items[<?php echo (int) $_mi; ?>][note]"
						placeholder="<?php esc_attr_e( 'Verified Experts', ADN_TEXT_DOMAIN ); ?>" class="regular-text"
						value="<?php echo $_m_note; ?>">
					<button type="button" class="button button-small remove-marq-item"><?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?></button>
				</div>
				<?php endforeach; ?>
			</div>

			<div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
				<button type="button" id="add-marquee-item" class="button">+ <?php esc_html_e( 'Add Item', ADN_TEXT_DOMAIN ); ?></button>
			</div>
		</div>

		<div class="card" style="max-width:none;background:#fafafa;margin-top:8px;">
			<h3 style="margin-top:0;margin-bottom:4px;"><?php esc_html_e( 'Featured In Strip', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:16px;">
				<?php esc_html_e( 'Logo strip to display below the expert listing. Manage strips in CMS Plugin → Featured In.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<?php if ( empty( $_fi_all ) ) : ?>
				<p style="color:#d63638;font-size:13px;"><?php esc_html_e( 'No strips yet - create one in CMS Plugin → Featured In.', ADN_TEXT_DOMAIN ); ?></p>
			<?php else : ?>
				<select name="featured_in_section" style="min-width:280px;">
					<option value=""><?php esc_html_e( '- None (hide strip) -', ADN_TEXT_DOMAIN ); ?></option>
					<?php foreach ( $_fi_all as $_fs ) :
						$_fid    = isset( $_fs['id'] ) ? (string) $_fs['id'] : '';
						$_flabel = ( isset( $_fs['heading'] ) && '' !== $_fs['heading'] ) ? $_fs['heading'] : $_fid;
						$_fcnt   = count( isset( $_fs['logos'] ) && is_array( $_fs['logos'] ) ? $_fs['logos'] : array() );
						if ( '' === $_fid ) { continue; }
					?>
						<option value="<?php echo esc_attr( $_fid ); ?>" <?php selected( $fi_section, $_fid ); ?>>
							<?php echo esc_html( $_flabel . '  [' . $_fid . ']  ·  ' . $_fcnt . ' logo' . ( 1 !== $_fcnt ? 's' : '' ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		</div>

		<div class="card" style="max-width:none;background:#fafafa;margin-top:8px;">
			<h3 style="margin-top:0;margin-bottom:4px;"><?php esc_html_e( 'Profile Unlock Password', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:16px;">
				<?php esc_html_e( 'Visitors must enter this password to view locked expert profiles on the Ask an Expert page. Leave empty to disable locking for everyone.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<table class="form-table" role="presentation"><tbody>
				<tr>
					<th scope="row">
						<label for="unlock_password"><?php esc_html_e( 'Unlock Password', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="unlock_password" name="unlock_password"
							class="regular-text"
							value="<?php echo esc_attr( $unlock_password ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. team2024', ADN_TEXT_DOMAIN ); ?>"
							autocomplete="off">
						<p class="description">
							<?php esc_html_e( 'Case-sensitive. Visitors who enter this on the Ask an Expert page unlock all locked profiles. Unlock state persists in their browser for 7 days.', ADN_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>
			</tbody></table>
		</div>

		<?php submit_button( __( 'Save Banner', ADN_TEXT_DOMAIN ), 'primary', 'submit', false ); ?>
	</form>
</div>

<script>
(function () {
	var marqIdx = <?php echo absint( count( $marquee_items ) ); ?>;
	var wrap    = document.getElementById( 'marquee-items-wrap' );

	function bindRemove( row ) {
		row.querySelector( '.remove-marq-item' ).addEventListener( 'click', function () {
			row.remove();
		} );
	}

	document.querySelectorAll( '.marquee-item-row' ).forEach( bindRemove );

	document.getElementById( 'add-marquee-item' ).addEventListener( 'click', function () {
		var row = document.createElement( 'div' );
		row.className = 'marquee-item-row';
		row.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;';
		row.innerHTML =
			'<input type="text" name="marquee_items[' + marqIdx + '][icon]" placeholder="icon" maxlength="4" class="small-text">' +
			'<input type="text" name="marquee_items[' + marqIdx + '][label]" placeholder="500+" class="regular-text">' +
			'<input type="text" name="marquee_items[' + marqIdx + '][note]" placeholder="Verified Experts" class="regular-text">' +
			'<button type="button" class="button button-small remove-marq-item">Remove</button>';
		wrap.appendChild( row );
		bindRemove( row );
		marqIdx++;
	} );
}());
</script>

