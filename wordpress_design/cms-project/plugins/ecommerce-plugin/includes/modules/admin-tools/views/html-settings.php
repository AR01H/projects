<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_settings_nonce'] ) && wp_verify_nonce( $_POST['ah_settings_nonce'], 'ah_save_settings' ) ) {
	$fields = array(
		'store_address', 'store_currency', 'enable_guest_checkout',
		'gateway_cod', 'gateway_bacs', 'gateway_stripe', 'gateway_paypal',
		'low_stock_threshold', 'enable_expiry', 'enable_wishlist', 'enable_reviews',
		'enable_abandoned_cart', 'abandoned_cart_delay_hours', 'abandoned_cart_max_reminders',
		'enable_recommendations', 'tax_enabled', 'shipping_enabled',
	);
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_option( 'ah_' . $field, sanitize_text_field( $_POST[ $field ] ) );
		} elseif ( isset( $_POST[ "enable_{$field}" ] ) || in_array( $field, array( 'gateway_cod', 'gateway_bacs', 'gateway_stripe', 'gateway_paypal', 'enable_guest_checkout', 'enable_expiry', 'enable_wishlist', 'enable_reviews', 'enable_abandoned_cart', 'enable_recommendations', 'tax_enabled', 'shipping_enabled' ), true ) ) {
			update_option( 'ah_' . $field, '0' );
		}
	}
	$notice = 'Settings saved.';
}

$s = function ( $key, $default = '' ) {
	return get_option( 'ah_' . $key, $default );
};
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Platform Settings', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<style>
		.ah-tabs { display:flex; border-bottom:1px solid #ccd0d4; margin-bottom:20px; flex-wrap:wrap; }
		.ah-tab-btn { padding:10px 16px; cursor:pointer; border:1px solid transparent; border-bottom:none; background:#f1f1f1; margin-right:3px; border-radius:4px 4px 0 0; font-weight:600; font-size:13px; }
		.ah-tab-btn.active { background:#fff; border-color:#ccd0d4; margin-bottom:-1px; }
		.ah-tab-content { display:none; padding:10px 0; }
		.ah-tab-content.active { display:block; }
	</style>

	<form method="post" action="">
		<?php wp_nonce_field( 'ah_save_settings', 'ah_settings_nonce' ); ?>
		<div class="ah-tabs">
			<div class="ah-tab-btn active" data-target="tab-general">General</div>
			<div class="ah-tab-btn" data-target="tab-products">Products</div>
			<div class="ah-tab-btn" data-target="tab-checkout">Checkout</div>
			<div class="ah-tab-btn" data-target="tab-features">Features</div>
			<div class="ah-tab-btn" data-target="tab-tax">Tax</div>
			<div class="ah-tab-btn" data-target="tab-shipping">Shipping</div>
			<div class="ah-tab-btn" data-target="tab-emails">Emails</div>
		</div>

		<div class="ah-form-wrap" style="background:#fff; padding:20px; border:1px solid #ccd0d4; box-shadow:0 1px 1px rgba(0,0,0,.04);">

			<!-- General -->
			<div class="ah-tab-content active" id="tab-general">
				<h2>General Options</h2>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Store Address</label>
					<input type="text" name="store_address" value="<?php echo esc_attr( $s( 'store_address' ) ); ?>" class="regular-text" style="width:100%; max-width:600px;">
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Currency</label>
					<select name="store_currency" style="max-width:300px;">
						<option value="USD" <?php selected( $s( 'store_currency', 'USD' ), 'USD' ); ?>>US Dollar ($)</option>
						<option value="GBP" <?php selected( $s( 'store_currency', 'USD' ), 'GBP' ); ?>>Pound Sterling (£)</option>
						<option value="EUR" <?php selected( $s( 'store_currency', 'USD' ), 'EUR' ); ?>>Euro (€)</option>
						<option value="INR" <?php selected( $s( 'store_currency', 'USD' ), 'INR' ); ?>>Indian Rupee (₹)</option>
					</select>
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>

			<!-- Products -->
			<div class="ah-tab-content" id="tab-products">
				<h2>Product Settings</h2>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="enable_expiry" value="1" <?php checked( $s( 'enable_expiry' ), '1' ); ?>> Enable Product Expiry (products can have an expiration date)</label>
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Low Stock Threshold</label>
					<input type="number" name="low_stock_threshold" value="<?php echo esc_attr( $s( 'low_stock_threshold', '5' ) ); ?>" class="regular-text" style="max-width:200px;">
					<p class="description">Alert when stock drops below this number.</p>
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>

			<!-- Checkout -->
			<div class="ah-tab-content" id="tab-checkout">
				<h2>Checkout & Payments</h2>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="enable_guest_checkout" value="1" <?php checked( $s( 'enable_guest_checkout', '1' ), '1' ); ?>> Enable Guest Checkout</label>
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Enabled Payment Methods</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_cod" value="1" <?php checked( $s( 'gateway_cod', '1' ), '1' ); ?>> Cash on Delivery (COD)</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_bacs" value="1" <?php checked( $s( 'gateway_bacs' ), '1' ); ?>> Direct Bank Transfer</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_stripe" value="1" <?php checked( $s( 'gateway_stripe' ), '1' ); ?>> Stripe (Credit Card)</label>
					<label style="font-weight:normal; display:block;"><input type="checkbox" name="gateway_paypal" value="1" <?php checked( $s( 'gateway_paypal' ), '1' ); ?>> PayPal</label>
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>

			<!-- Features -->
			<div class="ah-tab-content" id="tab-features">
				<h2>Feature Toggles</h2>
				<p style="color:#666;">Enable or disable platform features. All are theme-independent.</p>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="enable_wishlist" value="1" <?php checked( $s( 'enable_wishlist', '1' ), '1' ); ?>> Wishlist (customers can save products)</label>
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="enable_reviews" value="1" <?php checked( $s( 'enable_reviews', '1' ), '1' ); ?>> Product Reviews & Ratings</label>
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="enable_recommendations" value="1" <?php checked( $s( 'enable_recommendations', '1' ), '1' ); ?>> Product Recommendations (upsells, cross-sells, related)</label>
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="enable_abandoned_cart" value="1" <?php checked( $s( 'enable_abandoned_cart', '1' ), '1' ); ?>> Abandoned Cart Recovery</label>
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Abandoned Cart Reminder Delay (hours)</label>
					<input type="number" name="abandoned_cart_delay_hours" value="<?php echo esc_attr( $s( 'abandoned_cart_delay_hours', '24' ) ); ?>" class="regular-text" style="max-width:200px;">
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Max Reminders per Cart</label>
					<input type="number" name="abandoned_cart_max_reminders" value="<?php echo esc_attr( $s( 'abandoned_cart_max_reminders', '3' ) ); ?>" class="regular-text" style="max-width:200px;">
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>

			<!-- Tax -->
			<div class="ah-tab-content" id="tab-tax">
				<h2>Tax Settings</h2>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="tax_enabled" value="1" <?php checked( $s( 'tax_enabled' ), '1' ); ?>> Enable Tax Calculations</label>
					<p class="description">Tax rules are configured in <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-tax' ) ); ?>">Tax Rules</a>.</p>
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>

			<!-- Shipping -->
			<div class="ah-tab-content" id="tab-shipping">
				<h2>Shipping Settings</h2>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<label style="font-weight:normal;"><input type="checkbox" name="shipping_enabled" value="1" <?php checked( $s( 'shipping_enabled' ), '1' ); ?>> Enable Shipping Calculations</label>
					<p class="description">Shipping zones and methods are configured in <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-shipping' ) ); ?>">Shipping</a>.</p>
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>

			<!-- Emails -->
			<div class="ah-tab-content" id="tab-emails">
				<h2>Email Notifications</h2>
				<p>All transactional emails are active by default. Templates can be overridden in your theme.</p>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<p><strong>Override location:</strong> <code>your-theme/ah-ecommerce/emails/</code></p>
					<p class="description">Copy any template from the plugin's <code>includes/commerce/notifications/templates/</code> directory to customize the look and content.</p>
				</div>
				<div class="ah-form-row" style="margin-bottom:15px;">
					<p><strong>Available templates:</strong></p>
					<ul style="margin:5px 0 0 20px;">
						<li><code>order-confirmation.php</code> — Order placed successfully</li>
						<li><code>order-status-update.php</code> — Order status changed</li>
						<li><code>abandoned-cart.php</code> — Cart recovery reminder</li>
						<li><code>low-stock-alert.php</code> — Admin: low stock warning</li>
						<li><code>out-of-stock-alert.php</code> — Admin: out of stock warning</li>
						<li><code>new-review.php</code> — Admin: new review submitted</li>
					</ul>
				</div>
				<button type="submit" class="button button-primary">Save Changes</button>
			</div>

		</div>
	</form>

	<script>
	document.querySelectorAll('.ah-tab-btn').forEach(function(btn) {
		btn.addEventListener('click', function() {
			document.querySelectorAll('.ah-tab-btn, .ah-tab-content').forEach(function(el) { el.classList.remove('active'); });
			btn.classList.add('active');
			document.getElementById(btn.dataset.target).classList.add('active');
		});
	});
	</script>
</div>
