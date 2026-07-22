<?php
/**
 * Frontend View: Cart Shortcode
 */
defined( 'ABSPATH' ) || exit;

use AHEcommerce\Modules\Cart\Cart_Module;
use AHEcommerce\Modules\Products\Product_Repository;

$cart_module = \AH_Ecommerce::container()->get( Cart_Module::class );
$cart_items = $cart_module->get_cart();
$product_repo = \AH_Ecommerce::container()->get( Product_Repository::class );

wp_enqueue_script( 'jquery' );
?>

<style>
.ah-cart-wrapper {
	max-width: 800px;
	margin: 0 auto;
	font-family: sans-serif;
}
.ah-cart-table {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 20px;
}
.ah-cart-table th, .ah-cart-table td {
	padding: 15px;
	border-bottom: 1px solid #eaeaea;
	text-align: left;
}
.ah-cart-table th {
	background: #f7fafc;
	color: #4a5568;
}
.ah-cart-item-image {
	width: 60px;
	height: 60px;
	object-fit: cover;
	border-radius: 4px;
}
.ah-cart-remove-btn {
	color: #e53e3e;
	cursor: pointer;
	border: none;
	background: none;
	font-size: 0.9rem;
	padding: 0;
}
.ah-cart-remove-btn:hover {
	text-decoration: underline;
}
.ah-cart-totals {
	background: #f7fafc;
	padding: 20px;
	border-radius: 8px;
	text-align: right;
}
.ah-cart-totals h3 {
	margin: 0 0 15px;
	font-size: 1.5rem;
}
.ah-checkout-btn {
	background: #38a169;
	color: #fff;
	padding: 12px 25px;
	text-decoration: none;
	border-radius: 4px;
	font-weight: bold;
	display: inline-block;
	transition: background 0.3s;
}
.ah-checkout-btn:hover {
	background: #2f855a;
	color: #fff;
}
</style>

<div class="ah-cart-wrapper" id="ah-cart-container">
	<?php if ( empty( $cart_items ) ) : ?>
		<div style="text-align:center; padding: 40px; background: #f7fafc; border-radius: 8px;">
			<h2>Your cart is empty.</h2>
			<p>Looks like you haven't added anything yet.</p>
		</div>
	<?php else : 
		$total_price = 0;
	?>
		<table class="ah-cart-table">
			<thead>
				<tr>
					<th colspan="2">Product</th>
					<th>Price</th>
					<th>Quantity</th>
					<th>Total</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $cart_items as $item ) : 
					$product = $product_repo->get( $item['id'] );
					if ( ! $product ) continue;
					
					$meta = (array) $product->meta;
					$image = ! empty( $meta['featured_image'] ) ? $meta['featured_image'] : 'https://via.placeholder.com/60x60?text=No+Image';
					
					$item_price = (float) $product->price;
					$item_total = $item_price * $item['qty'];
					$total_price += $item_total;
				?>
					<tr id="cart-item-<?php echo esc_attr( $product->id ); ?>">
						<td style="width: 80px;">
							<img src="<?php echo esc_url( $image ); ?>" class="ah-cart-item-image" alt="">
						</td>
						<td>
							<strong><?php echo esc_html( $product->title ); ?></strong>
						</td>
						<td>$<?php echo number_format( $item_price, 2 ); ?></td>
						<td><?php echo (int) $item['qty']; ?></td>
						<td><strong>$<?php echo number_format( $item_total, 2 ); ?></strong></td>
						<td>
							<button class="ah-cart-remove-btn" data-product_id="<?php echo esc_attr( $product->id ); ?>">Remove</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<div class="ah-cart-totals">
			<h3>Subtotal: $<?php echo number_format( $total_price, 2 ); ?></h3>
			<p>Taxes and shipping calculated at checkout.</p>
			<!-- We assume the checkout page has the shortcode [ah_ecommerce_checkout] -->
			<!-- We will just link to a hypothetical /checkout/ slug, or ideally it should be configurable -->
			<a href="/checkout/" class="ah-checkout-btn">Proceed to Checkout</a>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.ah-cart-remove-btn').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var product_id = $btn.data('product_id');
		
		$btn.text('Removing...');
		
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_remove_from_cart',
			product_id: product_id,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(response) {
			if (response.success) {
				// Simply reload the page to refresh the cart UI for now
				window.location.reload();
			} else {
				alert('Error: ' + response.data.message);
				$btn.text('Remove');
			}
		});
	});
});
</script>
