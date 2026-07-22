<?php
/**
 * Frontend View: Products Grid Shortcode — with sale prices, expiry countdown, and wishlist.
 */
defined( 'ABSPATH' ) || exit;

use AHEcommerce\Modules\Products\Product_Repository;
use AHEcommerce\Commerce\Sales\Sale_Service;
use AHEcommerce\Commerce\Expiry\Expiry_Service;
use AHEcommerce\Commerce\Inventory\Inventory_Service;
use AHEcommerce\Commerce\Wishlist\Wishlist_Service;

$repo    = \AH_Ecommerce::container()->get( Product_Repository::class );
$result  = $repo->get_paginated( 1, 100, '' );
$products = $result['items'];

wp_enqueue_script( 'jquery' );
?>

<style>
.ah-ecommerce-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px; }
.ah-product-card { border:1px solid #eaeaea; border-radius:8px; padding:15px; text-align:center; transition:box-shadow 0.3s; background:#fff; position:relative; }
.ah-product-card:hover { box-shadow:0 4px 15px rgba(0,0,0,0.1); }
.ah-product-image img { max-width:100%; height:auto; border-radius:4px; margin-bottom:15px; }
.ah-product-title { font-size:1.1rem; margin:0 0 8px; color:#333; }
.ah-product-price { font-weight:bold; color:#e53e3e; font-size:1.1rem; margin-bottom:10px; }
.ah-product-original-price { text-decoration:line-through; color:#999; font-size:0.9rem; margin-left:8px; }
.ah-product-badge { position:absolute; top:10px; left:10px; padding:4px 10px; border-radius:4px; font-size:11px; font-weight:700; }
.ah-sale-badge { background:#e53e3e; color:#fff; }
.ah-expiry-badge { background:#f59e0b; color:#fff; }
.ah-out-of-stock-badge { background:#6b7280; color:#fff; }
.ah-wishlist-btn { position:absolute; top:10px; right:10px; background:none; border:none; font-size:20px; cursor:pointer; color:#ccc; transition:color 0.2s; }
.ah-wishlist-btn.active { color:#e53e3e; }
.ah-wishlist-btn:hover { color:#e53e3e; }
.ah-countdown { background:#fef3c7; padding:8px; border-radius:4px; margin-bottom:10px; font-size:12px; color:#92400e; }
.ah-add-to-cart-btn { background:#3182ce; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; width:100%; font-weight:bold; transition:background 0.3s; }
.ah-add-to-cart-btn:hover { background:#2b6cb0; }
.ah-add-to-cart-btn.loading { opacity:0.7; cursor:not-allowed; }
.ah-add-to-cart-btn:disabled { background:#94a3b8; cursor:not-allowed; }
</style>

<div class="ah-ecommerce-wrapper">
	<?php if ( empty( $products ) ) : ?>
		<p>No products available at the moment.</p>
	<?php else : ?>
		<div class="ah-ecommerce-grid">
			<?php foreach ( $products as $product ) :
				$meta     = (array) $product->meta;
				$image    = ! empty( $meta['featured_image'] ) ? $meta['featured_image'] : 'https://via.placeholder.com/300x300?text=No+Image';
				$price    = (float) $product->price;
				$sale     = Sale_Service::get_sale_price( $product->id );
				$expired  = Expiry_Service::is_expired( $product->id );
				$in_stock = Inventory_Service::is_in_stock( $product->id );
				$wished   = Wishlist_Service::is_in_wishlist( $product->id );
				$countdown = Sale_Service::get_countdown( $product->id );
				$expiry_remaining = Expiry_Service::get_time_remaining( $product->id );
			?>
				<div class="ah-product-card">
					<!-- Badges -->
					<?php if ( $sale !== null ) : ?>
						<span class="ah-product-badge ah-sale-badge">SALE</span>
					<?php endif; ?>
					<?php if ( $expired ) : ?>
						<span class="ah-product-badge ah-expiry-badge">EXPIRED</span>
					<?php elseif ( ! $in_stock ) : ?>
						<span class="ah-product-badge ah-out-of-stock-badge">OUT OF STOCK</span>
					<?php endif; ?>

					<!-- Wishlist Button -->
					<button class="ah-wishlist-btn <?php echo $wished ? 'active' : ''; ?>" data-product-id="<?php echo esc_attr( $product->id ); ?>" title="<?php echo $wished ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
						<?php echo $wished ? '&#9829;' : '&#9825;'; ?>
					</button>

					<div class="ah-product-image">
						<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product->title ); ?>">
					</div>
					<h3 class="ah-product-title"><?php echo esc_html( $product->title ); ?></h3>

					<!-- Price with sale -->
					<div class="ah-product-price">
						$<?php echo number_format( $sale !== null ? $sale : $price, 2 ); ?>
						<?php if ( $sale !== null ) : ?>
							<span class="ah-product-original-price">$<?php echo number_format( $price, 2 ); ?></span>
						<?php endif; ?>
					</div>

					<!-- Sale Countdown -->
					<?php if ( $countdown ) : ?>
						<div class="ah-countdown" data-end="<?php echo esc_attr( $countdown['timestamp'] ); ?>">
							Sale ends in: <strong><?php echo $countdown['days']; ?>d <?php echo $countdown['hours']; ?>h <?php echo $countdown['minutes']; ?>m</strong>
						</div>
					<?php endif; ?>

					<!-- Expiry Countdown -->
					<?php if ( ! $expired && ! empty( $expiry_remaining['total_seconds'] ) ) : ?>
						<div class="ah-countdown" style="background:#fee2e2; color:#991b1b;">
							Expires in: <strong><?php echo $expiry_remaining['days']; ?>d <?php echo $expiry_remaining['hours']; ?>h <?php echo $expiry_remaining['minutes']; ?>m</strong>
						</div>
					<?php endif; ?>

					<!-- Add to Cart -->
					<button class="ah-add-to-cart-btn"
						data-product_id="<?php echo esc_attr( $product->id ); ?>"
						<?php echo ( $expired || ! $in_stock ) ? 'disabled' : ''; ?>>
						<?php echo $expired ? 'Expired' : ( ! $in_stock ? 'Out of Stock' : 'Add to Cart' ); ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Add to Cart
	$('.ah-add-to-cart-btn').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		if ($btn.hasClass('loading') || $btn.prop('disabled')) return;
		$btn.addClass('loading').text('Adding...');
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_add_to_cart',
			product_id: $btn.data('product_id'),
			qty: 1,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(response) {
			$btn.removeClass('loading');
			if (response.success) {
				$btn.text('Added!');
				setTimeout(function() { $btn.text('Add to Cart'); }, 2000);
				$(document).trigger('ah_cart_updated', response.data);
			} else {
				alert('Error: ' + response.data.message);
				$btn.text('Add to Cart');
			}
		});
	});

	// Wishlist Toggle
	$('.ah-wishlist-btn').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var pid  = $btn.data('product-id');
		var adding = !$btn.hasClass('active');
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: adding ? 'ah_add_to_wishlist' : 'ah_remove_from_wishlist',
			product_id: pid,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(response) {
			if (response.success) {
				$btn.toggleClass('active');
				$btn.html($btn.hasClass('active') ? '&#9829;' : '&#9825;');
			}
		});
	});

	// Countdown timer
	setInterval(function() {
		$('.ah-countdown[data-end]').each(function() {
			var end = parseInt($(this).data('end')) * 1000;
			var now = Date.now();
			var diff = end - now;
			if (diff <= 0) { $(this).html('<strong>Sale ended</strong>'); return; }
			var d = Math.floor(diff / 86400000);
			var h = Math.floor((diff % 86400000) / 3600000);
			var m = Math.floor((diff % 3600000) / 60000);
			$(this).find('strong').text(d + 'd ' + h + 'h ' + m + 'm');
		});
	}, 60000);
});
</script>
