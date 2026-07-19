<?php
/**
 * Frontend View: Products Grid Shortcode
 */
defined( 'ABSPATH' ) || exit;

use AHEcommerce\Database\Product_Repository;

$repo = new Product_Repository();
$result = $repo->get_paginated( 1, 100, '' ); // Get up to 100 products for now
$products = $result['items'];

wp_enqueue_script( 'jquery' );
?>

<style>
.ah-ecommerce-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
	gap: 20px;
}
.ah-product-card {
	border: 1px solid #eaeaea;
	border-radius: 8px;
	padding: 15px;
	text-align: center;
	transition: box-shadow 0.3s;
	background: #fff;
}
.ah-product-card:hover {
	box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.ah-product-image img {
	max-width: 100%;
	height: auto;
	border-radius: 4px;
	margin-bottom: 15px;
}
.ah-product-title {
	font-size: 1.2rem;
	margin: 0 0 10px;
	color: #333;
}
.ah-product-price {
	font-weight: bold;
	color: #e53e3e;
	font-size: 1.1rem;
	margin-bottom: 15px;
}
.ah-add-to-cart-btn {
	background: #3182ce;
	color: #fff;
	border: none;
	padding: 10px 20px;
	border-radius: 4px;
	cursor: pointer;
	width: 100%;
	font-weight: bold;
	transition: background 0.3s;
}
.ah-add-to-cart-btn:hover {
	background: #2b6cb0;
}
.ah-add-to-cart-btn.loading {
	opacity: 0.7;
	cursor: not-allowed;
}
</style>

<div class="ah-ecommerce-wrapper">
	<?php if ( empty( $products ) ) : ?>
		<p>No products available at the moment.</p>
	<?php else : ?>
		<div class="ah-ecommerce-grid">
			<?php foreach ( $products as $product ) : 
				$meta = (array) $product->meta;
				$image = ! empty( $meta['featured_image'] ) ? $meta['featured_image'] : 'https://via.placeholder.com/300x300?text=No+Image';
				$price = $product->price ? '$' . number_format( $product->price, 2 ) : 'Free';
			?>
				<div class="ah-product-card">
					<div class="ah-product-image">
						<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product->title ); ?>">
					</div>
					<h3 class="ah-product-title"><?php echo esc_html( $product->title ); ?></h3>
					<div class="ah-product-price"><?php echo esc_html( $price ); ?></div>
					<button class="ah-add-to-cart-btn" data-product_id="<?php echo esc_attr( $product->id ); ?>">
						Add to Cart
					</button>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.ah-add-to-cart-btn').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var product_id = $btn.data('product_id');
		
		if ($btn.hasClass('loading')) return;
		
		$btn.addClass('loading').text('Adding...');
		
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_add_to_cart',
			product_id: product_id,
			qty: 1,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(response) {
			$btn.removeClass('loading');
			if (response.success) {
				$btn.text('Added!');
				setTimeout(function() {
					$btn.text('Add to Cart');
				}, 2000);
				// Optionally trigger a custom event that the cart shortcode can listen to
				$(document).trigger('ah_cart_updated', response.data);
			} else {
				alert('Error: ' + response.data.message);
				$btn.text('Add to Cart');
			}
		});
	});
});
</script>
