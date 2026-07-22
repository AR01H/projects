<?php
/**
 * Frontend Guest Checkout Template with Coupon, Tax, and Recommendations.
 */
use AHEcommerce\Modules\Cart\Cart_Module;
use AHEcommerce\Modules\Products\Product_Repository;
use AHEcommerce\Commerce\Sales\Sale_Service;
use AHEcommerce\Commerce\Recommendations\Recommendation_Service;

$cart_module  = \AH_Ecommerce::container()->get( Cart_Module::class );
$cart         = $cart_module->get_cart();
$product_repo = \AH_Ecommerce::container()->get( Product_Repository::class );
$subtotal     = 0;

foreach ( $cart as $item ) {
	$product = $product_repo->get( $item['id'] );
	if ( $product ) {
		$subtotal += (float) $product->price * (int) $item['qty'];
	}
}

// Get cross-sell products from cart items for "Frequently Bought Together".
$cross_sell_ids = array();
foreach ( $cart as $item ) {
	$cross = Recommendation_Service::get_cross_sells( $item['id'] );
	foreach ( $cross as $cs ) {
		$cross_sell_ids[ $cs->id ] = $cs;
	}
}
$cross_sells = array_slice( array_values( $cross_sell_ids ), 0, 4 );
?>
<div class="ah-checkout-wrap" style="max-width:900px; margin:0 auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
	<h2 style="margin-top:0;">Checkout</h2>

	<?php if ( empty( $cart ) ) : ?>
		<div style="text-align:center; padding:40px; background:#f7fafc; border-radius:8px;">
			<h3>Your cart is empty.</h3>
			<p>Please add some products before checking out.</p>
		</div>
	<?php else : ?>

	<!-- Cart Summary -->
	<div style="margin-bottom:25px; padding:15px; background:#f7fafc; border-radius:8px;">
		<h3 style="margin:0 0 10px; font-size:16px;">Order Summary</h3>
		<table style="width:100%; border-collapse:collapse;">
			<?php foreach ( $cart as $item ) :
				$product = $product_repo->get( $item['id'] );
				if ( ! $product ) continue;
				$sale_price = Sale_Service::get_sale_price( $item['id'] );
				$price      = $sale_price !== null ? $sale_price : (float) $product->price;
			?>
				<tr style="border-bottom:1px solid #eee;">
					<td style="padding:8px 0;"><?php echo esc_html( $product->title ); ?> x <?php echo (int) $item['qty']; ?></td>
					<td style="padding:8px 0; text-align:right; font-weight:bold;">$<?php echo number_format( $price * (int) $item['qty'], 2 ); ?></td>
				</tr>
			<?php endforeach; ?>
		</table>

		<!-- Coupon Code -->
		<div style="margin-top:15px; display:flex; gap:10px;">
			<input type="text" id="ah-coupon-input" placeholder="Coupon code" style="flex:1; padding:8px 12px; border:1px solid #ccc; border-radius:4px;">
			<button type="button" id="ah-apply-coupon-btn" class="button">Apply Coupon</button>
		</div>
		<div id="ah-coupon-msg" style="margin-top:8px; font-weight:bold;"></div>

		<div id="ah-cart-discount" style="display:none; margin-top:10px; padding:8px 12px; background:#dcfce7; border-radius:4px; color:#166534;">
			Discount: -<span id="ah-discount-amount"></span> (<span id="ah-discount-label"></span>)
		</div>

		<div style="margin-top:15px; text-align:right; font-size:18px; font-weight:bold;">
			Total: $<span id="ah-checkout-total"><?php echo number_format( $subtotal, 2 ); ?></span>
		</div>
	</div>

	<form id="ah-checkout-form" method="post">
		<!-- Contact Info -->
		<div style="margin-bottom:25px;">
			<h3 style="border-bottom:2px solid #eee; padding-bottom:10px;">1. Contact Information</h3>
			<div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px;">
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Email Address *</label>
					<input type="email" name="guest_email" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Phone Number</label>
					<input type="text" name="guest_phone" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
			</div>
		</div>

		<!-- Billing Address -->
		<div style="margin-bottom:25px;">
			<h3 style="border-bottom:2px solid #eee; padding-bottom:10px;">2. Billing Details</h3>
			<div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px;">
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">First Name *</label>
					<input type="text" name="billing_first_name" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Last Name *</label>
					<input type="text" name="billing_last_name" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
				<div style="grid-column:1/-1;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Street Address *</label>
					<input type="text" name="billing_address" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">City *</label>
					<input type="text" name="billing_city" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">State</label>
					<input type="text" name="billing_state" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Postcode / ZIP *</label>
					<input type="text" name="billing_postcode" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
				</div>
				<div>
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Country</label>
					<input type="text" name="billing_country" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" placeholder="e.g. US">
				</div>
			</div>
		</div>

		<!-- Payment -->
		<div style="margin-bottom:25px;">
			<h3 style="border-bottom:2px solid #eee; padding-bottom:10px;">3. Payment Method</h3>
			<div style="margin-top:15px;">
				<label style="display:block; margin-bottom:10px; padding:15px; border:1px solid #ccc; border-radius:4px; cursor:pointer;">
					<input type="radio" name="payment_method" value="cod" checked> Cash on Delivery
				</label>
			</div>
		</div>

		<button type="submit" id="ah-place-order-btn" style="width:100%; padding:15px; background:#000; color:#fff; border:none; border-radius:4px; font-size:18px; font-weight:bold; cursor:pointer;">
			Place Order ($<span id="ah-btn-total"><?php echo number_format( $subtotal, 2 ); ?></span>)
		</button>
		<div id="ah-checkout-msg" style="margin-top:15px; text-align:center; font-weight:bold;"></div>
	</form>

	<!-- Frequently Bought Together -->
	<?php if ( ! empty( $cross_sells ) ) : ?>
	<div style="margin-top:30px; padding:20px; background:#f7fafc; border-radius:8px;">
		<h3 style="margin:0 0 15px;">Frequently Bought Together</h3>
		<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:15px;">
			<?php foreach ( $cross_sells as $cs ) :
				$meta = (array) $cs->meta;
				$img  = ! empty( $meta['featured_image'] ) ? $meta['featured_image'] : 'https://via.placeholder.com/180x180?text=Product';
			?>
				<div style="background:#fff; border:1px solid #eee; border-radius:8px; padding:12px; text-align:center;">
					<img src="<?php echo esc_url( $img ); ?>" alt="" style="width:100%; height:120px; object-fit:cover; border-radius:4px; margin-bottom:8px;">
					<div style="font-size:13px; font-weight:600; margin-bottom:4px;"><?php echo esc_html( $cs->title ); ?></div>
					<div style="color:#e53e3e; font-weight:bold;">$<?php echo number_format( (float) $cs->price, 2 ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Coupon Apply
	$('#ah-apply-coupon-btn').on('click', function() {
		var code = $('#ah-coupon-input').val().trim();
		if (!code) return;
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_apply_coupon',
			coupon_code: code,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(response) {
			if (response.success) {
				$('#ah-coupon-msg').css('color', 'green').text(response.data.message);
				$('#ah-cart-discount').show();
				$('#ah-discount-amount').text('$' + parseFloat(response.data.discount).toFixed(2));
				$('#ah-discount-label').text(response.data.label);
				$('#ah-checkout-total, #ah-btn-total').text(parseFloat(response.data.new_total).toFixed(2));
			} else {
				$('#ah-coupon-msg').css('color', 'red').text(response.data.message);
			}
		});
	});

	// Checkout Submit
	$('#ah-checkout-form').on('submit', function(e) {
		e.preventDefault();
		var $form = $(this);
		var $btn  = $('#ah-place-order-btn');
		var $msg  = $('#ah-checkout-msg');

		$btn.prop('disabled', true).text('Processing...');

		$.post('<?php echo admin_url('admin-ajax.php'); ?>', $form.serialize() + '&action=ah_process_checkout', function(response) {
			if (response.success) {
				$msg.css('color', 'green').text(response.data.message);
				$form[0].reset();
				setTimeout(function() { window.location.reload(); }, 2000);
			} else {
				$msg.css('color', 'red').text('Error: ' + response.data.message);
				$btn.prop('disabled', false).text('Place Order');
			}
		});
	});
});
</script>
