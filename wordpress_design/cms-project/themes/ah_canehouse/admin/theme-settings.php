<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
$s = ch_get_settings();
?>
<div class="wrap ch-admin-wrap">
	<h1>🔧 Site Settings</h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="ch-notice ch-notice--success">✅ Settings saved successfully.</div>
	<?php endif; ?>

	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<?php wp_nonce_field( 'ch_theme_settings' ); ?>
		<input type="hidden" name="action" value="ch_theme_settings">

		<div class="ch-card">
			<h2>Contact &amp; Business Info</h2>
			<?php
			$fields = [
				'phone'    => [ 'Phone Number', 'tel', '+44 7887 699 208' ],
				'email'    => [ 'Email Address', 'email', 'hello@thecanehouse.co.uk' ],
				'address'  => [ 'Address / Coverage', 'text', 'Available across the UK' ],
				'website'  => [ 'Website URL', 'text', 'www.thecanehouse.co.uk' ],
				'whatsapp' => [ 'WhatsApp Number (digits only)', 'text', '447887699208' ],
				'tagline'  => [ 'Tagline', 'text', 'Pressed Fresh. Served Cool.' ],
			];
			foreach ( $fields as $key => [ $label, $type, $placeholder ] ) : ?>
				<div class="ch-row">
					<label for="ch-s-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="<?php echo esc_attr( $type ); ?>" id="ch-s-<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $s[ $key ] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>">
				</div>
			<?php endforeach; ?>
		</div>

		<div class="ch-card">
			<h2>Social Media Links</h2>
			<?php foreach ( [ 'instagram_url' => 'Instagram URL', 'facebook_url' => 'Facebook URL', 'tiktok_url' => 'TikTok URL', 'youtube_url' => 'YouTube URL' ] as $key => $label ) : ?>
				<div class="ch-row">
					<label for="ch-s-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="url" id="ch-s-<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $s[ $key ] ?? '' ); ?>"
						placeholder="https://...">
				</div>
			<?php endforeach; ?>
		</div>

		<?php submit_button( 'Save Settings', 'primary', 'submit', false ); ?>
	</form>
</div>
