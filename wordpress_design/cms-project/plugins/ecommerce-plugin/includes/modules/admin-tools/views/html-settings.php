<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_settings_nonce'] ) && wp_verify_nonce( $_POST['ah_settings_nonce'], 'ah_save_settings' ) ) {
	update_option( 'ah_store_address', sanitize_text_field( $_POST['store_address'] ?? '' ) );
	update_option( 'ah_store_currency', sanitize_text_field( $_POST['store_currency'] ?? 'USD' ) );
	update_option( 'ah_enable_guest_checkout', isset( $_POST['enable_guest_checkout'] ) ? '1' : '0' );
	update_option( 'ah_gateway_cod', isset( $_POST['gateway_cod'] ) ? '1' : '0' );
	update_option( 'ah_gateway_bacs', isset( $_POST['gateway_bacs'] ) ? '1' : '0' );
	update_option( 'ah_gateway_stripe', isset( $_POST['gateway_stripe'] ) ? '1' : '0' );
	update_option( 'ah_gateway_paypal', isset( $_POST['gateway_paypal'] ) ? '1' : '0' );
	echo '<div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin: 20px 0;">Settings saved successfully.</div>';
}

$store_address = get_option( 'ah_store_address', '' );
$store_currency = get_option( 'ah_store_currency', 'USD' );
$enable_guest_checkout = get_option( 'ah_enable_guest_checkout', '1' );
$gateway_cod = get_option( 'ah_gateway_cod', '1' );
$gateway_bacs = get_option( 'ah_gateway_bacs', '0' );
$gateway_stripe = get_option( 'ah_gateway_stripe', '0' );
$gateway_paypal = get_option( 'ah_gateway_paypal', '0' );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Platform Settings', 'ah-ecommerce' ); ?></h1>
	
	<style>
		.ah-tabs { display: flex; border-bottom: 1px solid #ccd0d4; margin-bottom: 20px; }
		.ah-tab-btn { padding: 10px 20px; cursor: pointer; border: 1px solid transparent; border-bottom: none; background: #f1f1f1; margin-right: 5px; border-radius: 4px 4px 0 0; font-weight: 600; }
		.ah-tab-btn.active { background: #fff; border-color: #ccd0d4; margin-bottom: -1px; }
		.ah-tab-content { display: none; padding: 10px 0; }
		.ah-tab-content.active { display: block; }
	</style>

	<div class="ah-tabs">
		<div class="ah-tab-btn active" data-target="tab-general">General</div>
		<div class="ah-tab-btn" data-target="tab-products">Products</div>
		<div class="ah-tab-btn" data-target="tab-tax">Tax</div>
		<div class="ah-tab-btn" data-target="tab-emails">Emails</div>
		<div class="ah-tab-btn" data-target="tab-advanced">Advanced</div>
	</div>

	<form method="post" action="">
		<?php wp_nonce_field( 'ah_save_settings', 'ah_settings_nonce' ); ?>
		<div class="ah-form-wrap" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<div class="ah-tab-content active" id="tab-general">
				<h2>General Options</h2>
				<div class="ah-form-row" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Store Address</label>
					<input type="text" name="store_address" value="<?php echo esc_attr( $store_address ); ?>" class="regular-text" style="width:100%; max-width:600px;">
				</div>
				<div class="ah-form-row" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Currency</label>
					<select name="store_currency">
						<option value="USD" <?php selected($store_currency, 'USD'); ?>>US Dollar ($)</option>
						<option value="GBP" <?php selected($store_currency, 'GBP'); ?>>Pound Sterling (£)</option>
						<option value="EUR" <?php selected($store_currency, 'EUR'); ?>>Euro (€)</option>
						<option value="INR" <?php selected($store_currency, 'INR'); ?>>Indian Rupee (₹)</option>
					</select>
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>
			<div class="ah-tab-content" id="tab-products"><h2>Product Settings</h2><p>Coming soon...</p></div>
			<div class="ah-tab-content" id="tab-tax"><h2>Tax Settings</h2><p>Coming soon...</p></div>
			<div class="ah-tab-content" id="tab-emails"><h2>Email Settings</h2><p>Coming soon...</p></div>
			<div class="ah-tab-content" id="tab-advanced">
				<h2>Checkout & Payments</h2>
				<div class="ah-form-row" style="margin-bottom: 15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="enable_guest_checkout" value="1" <?php checked($enable_guest_checkout, '1'); ?>> Enable Guest Checkout (Allow customers to place orders without an account)</label>
				</div>
				<div class="ah-form-row" style="margin-bottom: 15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Enabled Payment Methods</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_cod" value="1" <?php checked($gateway_cod, '1'); ?>> Cash on Delivery (COD)</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_bacs" value="1" <?php checked($gateway_bacs, '1'); ?>> Direct Bank Transfer</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_stripe" value="1" <?php checked($gateway_stripe, '1'); ?>> Stripe (Credit Card)</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_paypal" value="1" <?php checked($gateway_paypal, '1'); ?>> PayPal</label>
				</div>
				<button type="submit" class="button button-primary">Save Settings</button>
			</div>
		</div>
	</form>

	<script>
		document.querySelectorAll('.ah-tab-btn').forEach(btn => {
			btn.addEventListener('click', () => {
				document.querySelectorAll('.ah-tab-btn, .ah-tab-content').forEach(el => el.classList.remove('active'));
				btn.classList.add('active');
				document.getElementById(btn.dataset.target).classList.add('active');
			});
		});
	</script>
</div>
