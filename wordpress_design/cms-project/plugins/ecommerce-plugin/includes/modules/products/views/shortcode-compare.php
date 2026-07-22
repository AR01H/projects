<?php
/**
 * Shortcode: [ah_product_compare]
 * Side-by-side product comparison table.
 */
defined( 'ABSPATH' ) || exit;

use AHEcommerce\Commerce\Compare\Compare_Service;
use AHEcommerce\Commerce\Inventory\Inventory_Service;

$products = Compare_Service::get_comparison_data();
$attributes = Compare_Service::get_compare_attributes();
$count = Compare_Service::count();
wp_enqueue_script( 'jquery' );
?>
<style>
.ah-compare-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; overflow-x:auto; }
.ah-compare-table { width:100%; border-collapse:collapse; min-width:600px; }
.ah-compare-table th, .ah-compare-table td { padding:12px 15px; border:1px solid #e5e7eb; text-align:center; vertical-align:top; }
.ah-compare-table th { background:#f9fafb; font-weight:600; color:#374151; text-align:left; width:180px; }
.ah-compare-table .ah-compare-header { background:#111827; color:#fff; padding:20px; }
.ah-compare-table .ah-compare-img { width:100%; max-width:200px; height:auto; border-radius:8px; }
.ah-compare-table .ah-compare-title { font-size:16px; font-weight:700; margin:10px 0 5px; }
.ah-compare-table .ah-compare-price { font-size:20px; font-weight:700; color:#dc2626; }
.ah-compare-remove { background:none; border:none; color:#ef4444; cursor:pointer; font-size:12px; margin-top:8px; text-decoration:underline; }
.ah-compare-empty { text-align:center; padding:60px 20px; color:#9ca3af; }
.ah-compare-empty h2 { color:#374151; }
</style>

<div class="ah-compare-wrap">
	<?php if ( empty( $products ) ) : ?>
		<div class="ah-compare-empty">
			<h2>No Products to Compare</h2>
			<p>Add products to your comparison list from the product listing page.</p>
			<a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" style="display:inline-block; padding:12px 24px; background:#111827; color:#fff; border-radius:6px; text-decoration:none; margin-top:10px;">Browse Products</a>
		</div>
	<?php else : ?>
		<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
			<h2 style="margin:0;">Compare Products (<?php echo $count; ?>)</h2>
			<button onclick="if(confirm('Clear all?')){jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',{action:'ah_compare_clear',nonce:'<?php echo wp_create_nonce("ah_cart_nonce"); ?>'},function(){window.location.reload();});}" style="padding:8px 16px; background:#fee2e2; color:#dc2626; border:none; border-radius:6px; cursor:pointer;">Clear All</button>
		</div>

		<table class="ah-compare-table">
			<!-- Header row: product images + titles + prices -->
			<tr>
				<th></th>
				<?php foreach ( $products as $p ) :
					$img = ! empty( $p->meta['featured_image'] ) ? $p->meta['featured_image'] : 'https://via.placeholder.com/200x200?text=No+Image';
				?>
					<td class="ah-compare-header">
						<img src="<?php echo esc_url( $img ); ?>" class="ah-compare-img" alt="">
						<div class="ah-compare-title"><?php echo esc_html( $p->title ); ?></div>
						<div class="ah-compare-price">$<?php echo number_format( (float) $p->price, 2 ); ?></div>
						<button class="ah-compare-remove" data-id="<?php echo esc_attr( $p->id ); ?>">Remove</button>
					</td>
				<?php endforeach; ?>
			</tr>

			<!-- Attribute rows -->
			<?php foreach ( $attributes as $label => $key ) : ?>
				<tr>
					<th><?php echo esc_html( $label ); ?></th>
					<?php foreach ( $products as $p ) :
						$value = '';
						if ( isset( $p->$key ) ) {
							$value = $p->$key;
						} elseif ( isset( $p->meta[ $key ] ) ) {
							$value = $p->meta[ $key ];
						}
						if ( is_array( $value ) ) {
							$value = implode( ', ', $value );
						}
						$value = trim( (string) $value );
					?>
						<td><?php echo $value ? esc_html( wp_trim_words( $value, 20 ) ) : '<span style="color:#d1d5db;">—</span>'; ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>

			<!-- Stock status row -->
			<tr>
				<th>Stock Status</th>
				<?php foreach ( $products as $p ) :
					$status = Inventory_Service::get_stock_status( $p->id );
					$labels = array( 'instock' => 'In Stock', 'lowstock' => 'Low Stock', 'outofstock' => 'Out of Stock', 'onbackorder' => 'Backorder' );
					$colors = array( 'instock' => '#16a34a', 'lowstock' => '#ca8a04', 'outofstock' => '#dc2626', 'onbackorder' => '#2563eb' );
				?>
					<td style="font-weight:600; color:<?php echo $colors[ $status ] ?? '#6b7280'; ?>;"><?php echo $labels[ $status ] ?? 'Unknown'; ?></td>
				<?php endforeach; ?>
			</tr>

			<!-- Add to Cart row -->
			<tr>
				<th></th>
				<?php foreach ( $products as $p ) : ?>
					<td>
						<button class="ah-add-to-cart-btn" data-product_id="<?php echo esc_attr( $p->id ); ?>" style="width:100%; padding:10px; background:#111827; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Add to Cart</button>
					</td>
				<?php endforeach; ?>
			</tr>
		</table>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.ah-compare-remove').on('click', function() {
		var id = $(this).data('id');
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_compare_remove', product_id: id,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function() { window.location.reload(); });
	});
	$('.ah-add-to-cart-btn').on('click', function() {
		var $btn = $(this);
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_add_to_cart', product_id: $btn.data('product_id'), qty: 1,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(r) { if (r.success) { $btn.text('Added!'); setTimeout(function(){ $btn.text('Add to Cart'); }, 2000); } });
	});
});
</script>
