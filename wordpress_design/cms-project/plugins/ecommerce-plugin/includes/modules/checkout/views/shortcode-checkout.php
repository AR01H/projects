<?php
/**
 * Frontend Guest Checkout Template
 */
use AHEcommerce\Modules\Cart\Cart_Module;
use AHEcommerce\Database\Product_Repository;

$cart_module = \AH_Ecommerce::container()->get( Cart_Module::class );
$cart = $cart_module->get_cart();
$product_repo = new Product_Repository();
$subtotal = 0;

foreach ( $cart as $item ) {
	$product = $product_repo->get( $item['id'] );
	if ( $product ) {
		$subtotal += (float) $product->price * (int) $item['qty'];
	}
}

?>
<div class="ah-checkout-wrap" style="max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
	<h2 style="margin-top: 0;">Checkout</h2>
	
	<?php if ( empty( $cart ) ) : ?>
		<div style="text-align:center; padding: 40px; background: #f7fafc; border-radius: 8px;">
			<h3>Your cart is empty.</h3>
			<p>Please add some products to your cart before checking out.</p>
		</div>
	<?php else : ?>
		<form id="ah-checkout-form" method="post">
		
		<!-- Contact Info -->
		<div style="margin-bottom: 25px;">
			<h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">1. Contact Information (Guest)</h3>
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Email Address *</label>
					<input type="email" name="guest_email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Phone Number</label>
					<input type="text" name="guest_phone" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
				</div>
			</div>
		</div>

		<!-- Billing Address -->
		<div style="margin-bottom: 25px;">
			<h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">2. Billing Details</h3>
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">First Name *</label>
					<input type="text" name="billing_first_name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Last Name *</label>
					<input type="text" name="billing_last_name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
				</div>
				<div style="grid-column: 1 / -1;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Street Address *</label>
					<input type="text" name="billing_address" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">City *</label>
					<input type="text" name="billing_city" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Postcode / ZIP *</label>
					<input type="text" name="billing_postcode" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
				</div>
			</div>
		</div>

		<!-- Payment -->
		<div style="margin-bottom: 25px;">
			<h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">3. Payment Method</h3>
			<div style="margin-top: 15px;">
				<label style="display:block; margin-bottom: 10px; padding: 15px; border: 1px solid #ccc; border-radius: 4px; cursor:pointer;">
					<input type="radio" name="payment_method" value="cod" checked> Cash on Delivery (Pay upon arrival)
				</label>
			</div>
		</div>

		<button type="submit" id="ah-place-order-btn" style="width: 100%; padding: 15px; background: #000; color: #fff; border: none; border-radius: 4px; font-size: 18px; font-weight: bold; cursor: pointer;">
			Place Order ($<?php echo number_format( $subtotal, 2 ); ?>)
		</button>
		<div id="ah-checkout-msg" style="margin-top: 15px; text-align: center; font-weight: bold;"></div>
	</form>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('#ah-checkout-form').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		var $btn = $('#ah-place-order-btn');
		var $msg = $('#ah-checkout-msg');
		
		$btn.prop('disabled', true).text('Processing...');
		
		var data = $form.serialize() + '&action=ah_process_checkout';
		
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
			if (response.success) {
				$msg.css('color', 'green').text(response.data.message);
				$form[0].reset();
				setTimeout(function() {
					window.location.reload();
				}, 2000);
			} else {
				$msg.css('color', 'red').text('Error: ' + response.data.message);
				$btn.prop('disabled', false).text('Place Order ($<?php echo number_format( $subtotal, 2 ); ?>)');
			}
		});
	});
});
</script>
