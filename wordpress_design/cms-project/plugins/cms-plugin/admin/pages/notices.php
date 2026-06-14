<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

require_once AH_THEME_DIR . '/helper/class-notice-helper.php';

$notice = AH_Notice_Helper::get_notice();
$saved = isset( $_GET['saved'] ) ? (int) $_GET['saved'] : 0;
?>

<div class="wrap">
	<h1>📢 Site-Wide Notices</h1>
	<p style="color:#666;margin-bottom:1.5rem;">Create banners that display once per session/day across your site. Managed at the plugin level, displayed in any theme template.</p>

	<?php if ( $saved ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><strong>✅ Notice settings saved successfully.</strong></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="notice-form">
		<?php wp_nonce_field( 'ah_notices_save' ); ?>
		<input type="hidden" name="action" value="ah_save_notice">

		<div style="background:#fff;border:1px solid #ccc;border-radius:8px;padding:2rem;">

			<!-- Enable Toggle -->
			<div style="margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid #e0e0e0;">
				<label style="display:flex;align-items:center;gap:0.8rem;cursor:pointer;">
					<input type="checkbox" name="notice_enabled" value="1" <?php checked( ! empty( $notice['enabled'] ) ); ?> style="width:20px;height:20px;cursor:pointer;">
					<span style="font-weight:600;font-size:1.1rem;">Enable Notice Banner</span>
				</label>
			</div>

			<!-- Title -->
			<div style="margin-bottom:1.2rem;">
				<label style="display:block;font-weight:600;margin-bottom:0.5rem;">Notice Title *</label>
				<input type="text" name="notice_title"
					value="<?php echo esc_attr( $notice['title'] ?? 'Important Update' ); ?>"
					placeholder="e.g. Limited Time Offer"
					style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:4px;font-size:1rem;"
					required>
				<small style="color:#666;">Displayed in green as the headline</small>
			</div>

			<!-- Message -->
			<div style="margin-bottom:1.2rem;">
				<label style="display:block;font-weight:600;margin-bottom:0.5rem;">Message</label>
				<input type="text" name="notice_message"
					value="<?php echo esc_attr( $notice['message'] ?? '' ); ?>"
					placeholder="e.g. Get 20% off all event bookings this weekend"
					style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:4px;font-size:1rem;">
				<small style="color:#666;">Optional description text</small>
			</div>

			<!-- Image URL -->
			<div style="margin-bottom:1.2rem;">
				<label style="display:block;font-weight:600;margin-bottom:0.5rem;">Banner Image URL</label>
				<input type="url" name="notice_image"
					value="<?php echo esc_attr( $notice['image'] ?? '' ); ?>"
					placeholder="https://example.com/image.jpg"
					style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:4px;font-size:1rem;">
				<small style="color:#666;">Optional image on left side (recommended: 120×100px)</small>
			</div>

			<!-- Button Label -->
			<div style="margin-bottom:1.2rem;">
				<label style="display:block;font-weight:600;margin-bottom:0.5rem;">Button Label</label>
				<input type="text" name="notice_button_label"
					value="<?php echo esc_attr( $notice['button_label'] ?? '' ); ?>"
					placeholder="e.g. Book Now"
					style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:4px;font-size:1rem;">
				<small style="color:#666;">If blank, no button appears</small>
			</div>

			<!-- Button URL -->
			<div style="margin-bottom:1.2rem;">
				<label style="display:block;font-weight:600;margin-bottom:0.5rem;">Button URL</label>
				<input type="text" name="notice_button_url"
					value="<?php echo esc_attr( $notice['button_url'] ?? '' ); ?>"
					placeholder="https://example.com"
					style="width:100%;padding:0.6rem;border:1px solid #ddd;border-radius:4px;font-size:1rem;">
				<small style="color:#666;">Where the button links to</small>
			</div>

			<!-- Notice ID (read-only) -->
			<div style="margin-bottom:1.5rem;padding:1rem;background:#f5f5f5;border-radius:4px;">
				<label style="display:block;font-weight:600;margin-bottom:0.5rem;">Notice ID</label>
				<input type="text" value="<?php echo esc_attr( $notice['id'] ?? 'default' ); ?>" readonly
					style="width:100%;padding:0.6rem;background:#e8e8e8;border:1px solid #ddd;border-radius:4px;color:#999;">
				<small style="color:#666;">Auto-generated tracking ID</small>
			</div>

			<!-- Info Box -->
			<div style="background:#f0f8ff;border-left:4px solid #0073aa;padding:1rem;border-radius:4px;margin-bottom:1.5rem;">
				<p style="margin:0;font-size:0.95rem;color:#333;">
					<strong>How it works:</strong><br>
					✓ Popup appears on every page of the site<br>
					✓ Shows once per visitor per day (via localStorage)<br>
					✓ Visitors can dismiss with ✕ button or Escape key<br>
					✓ If you change the title/message/button, the popup reappears even for visitors who already dismissed it today<br>
					✓ Same content on the same day = never repeats
				</p>
			</div>

		</div>

		<div style="margin-top:1.5rem;">
			<?php submit_button( '💾 Save Notice Settings', 'primary', 'submit' ); ?>
		</div>
	</form>

</div>

<style>
.notice-form input[type="text"],
.notice-form input[type="url"] {
	box-sizing: border-box;
}

.notice-form input[type="checkbox"] {
	accent-color: #0073aa;
}
</style>
