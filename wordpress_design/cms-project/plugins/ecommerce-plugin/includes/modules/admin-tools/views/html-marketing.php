<?php
/**
 * Admin View: Marketing
 */
defined( 'ABSPATH' ) || exit;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_marketing_nonce'] ) && wp_verify_nonce( $_POST['ah_marketing_nonce'], 'ah_save_marketing' ) ) {
	update_option( 'ah_enable_affiliate', isset( $_POST['enable_affiliate'] ) ? '1' : '0' );
	update_option( 'ah_affiliate_commission', sanitize_text_field( $_POST['affiliate_commission'] ?? '5' ) );
	update_option( 'ah_affiliate_cookie', sanitize_text_field( $_POST['affiliate_cookie'] ?? '30' ) );
	update_option( 'ah_fcm_server_key', sanitize_text_field( $_POST['fcm_server_key'] ?? '' ) );
	echo '<div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin: 20px 0;">Marketing settings saved successfully.</div>';
}

$enable_affiliate = get_option( 'ah_enable_affiliate', '1' );
$affiliate_commission = get_option( 'ah_affiliate_commission', '5' );
$affiliate_cookie = get_option( 'ah_affiliate_cookie', '30' );
$fcm_server_key = get_option( 'ah_fcm_server_key', '' );

?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'Marketing Campaigns', 'ah-ecommerce' ); ?></h1>
	
	<form method="post" action="">
		<?php wp_nonce_field( 'ah_save_marketing', 'ah_marketing_nonce' ); ?>
		<div class="ah-tabs">
		<div class="ah-tab-nav" style="display:flex; gap:15px; margin-bottom:20px; border-bottom:1px solid #ccc;">
			<div class="ah-tab-btn active" data-target="tab-email" style="padding:10px; cursor:pointer;">Email & SMS</div>
			<div class="ah-tab-btn" data-target="tab-affiliate" style="padding:10px; cursor:pointer;">Affiliate Program</div>
			<div class="ah-tab-btn" data-target="tab-banners" style="padding:10px; cursor:pointer;">Banners & Popups</div>
			<div class="ah-tab-btn" data-target="tab-push" style="padding:10px; cursor:pointer;">Push Notifications</div>
		</div>

		<div class="ah-tab-content active" id="tab-email">
			<h2>Campaigns</h2>
			<div class="ah-grid-2" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
				<div class="card" style="padding:20px; border:1px solid #ccd0d4; background:#fff;">
					<h3>Email Campaigns</h3>
					<p>Broadcast emails to customer groups (e.g., Abandoned Cart, Newsletters).</p>
					<button class="button button-primary">Create Email Campaign</button>
				</div>
				<div class="card" style="padding:20px; border:1px solid #ccd0d4; background:#fff;">
					<h3>SMS / WhatsApp</h3>
					<p>Send instant promotional texts via Twilio/WhatsApp API.</p>
					<button class="button button-primary">Create SMS Campaign</button>
				</div>
			</div>
		</div>

		<div class="ah-tab-content" id="tab-affiliate" style="display:none;">
			<h2>Affiliate & Influencer Program</h2>
			<div class="ah-form-row">
				<label><input type="checkbox" name="enable_affiliate" value="1" <?php checked($enable_affiliate, '1'); ?>> Enable Affiliate Program</label>
			</div>
			<div class="ah-form-row" style="margin-top:10px;">
				<label>Default Affiliate Commission (%)</label><br>
				<input type="number" step="1" name="affiliate_commission" value="<?php echo esc_attr( $affiliate_commission ); ?>" class="regular-text">
			</div>
			<div class="ah-form-row" style="margin-top:10px;">
				<label>Cookie Duration (Days)</label><br>
				<input type="number" step="1" name="affiliate_cookie" value="<?php echo esc_attr( $affiliate_cookie ); ?>" class="regular-text">
			</div>
			<button type="submit" class="button button-primary" style="margin-top:10px;">Save Settings</button>
		</div>

		<div class="ah-tab-content" id="tab-banners" style="display:none;">
			<h2>Banners & Popups Manager</h2>
			<p>Create global banners (e.g., "Free Shipping over $50") or exit-intent popups.</p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Banner / Popup Name</th>
						<th>Type</th>
						<th>Location</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>Summer Sale Header</strong></td>
						<td>Sticky Banner</td>
						<td>Site-wide Header</td>
						<td><span style="color:green; font-weight:bold;">Active</span></td>
						<td><a href="#" class="button button-small">Edit</a></td>
					</tr>
					<tr>
						<td><strong>Newsletter 10% Off</strong></td>
						<td>Exit-Intent Popup</td>
						<td>Cart Page</td>
						<td><span style="color:gray; font-weight:bold;">Draft</span></td>
						<td><a href="#" class="button button-small">Edit</a></td>
					</tr>
				</tbody>
			</table>
			<button class="button button-primary" style="margin-top:15px;">Add New Banner/Popup</button>
		</div>

		<div class="ah-tab-content" id="tab-push" style="display:none;">
			<h2>Web Push Notifications</h2>
			<p>Send instant browser notifications to subscribed users.</p>
			<div class="ah-form-row">
				<label>FCM Server Key / Web Push API Key</label><br>
				<input type="text" name="fcm_server_key" value="<?php echo esc_attr( $fcm_server_key ); ?>" placeholder="AIzaSyC..." class="regular-text">
			</div>
			<button type="submit" class="button button-primary" style="margin-top:10px;">Save Keys</button>
		</div>
	</div>
	</form>

	<script>
		document.querySelectorAll('.ah-tab-btn').forEach(btn => {
			btn.addEventListener('click', () => {
				document.querySelectorAll('.ah-tab-btn').forEach(el => el.classList.remove('active'));
				document.querySelectorAll('.ah-tab-content').forEach(el => el.style.display = 'none');
				btn.classList.add('active');
				document.getElementById(btn.dataset.target).style.display = 'block';
			});
		});
	</script>
</div>
