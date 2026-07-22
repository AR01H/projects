<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Recommendations\Recommendation_Service;

global $wpdb;
$meta = $wpdb->prefix . 'ah_ecommerce_product_meta';
$products_table = $wpdb->prefix . 'ah_ecommerce_products';

$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$offset = ( $page - 1 ) * 20;

$all_products = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$products_table} WHERE status = 'published' ORDER BY title ASC LIMIT 20 OFFSET %d",
		$offset
	)
);
$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$products_table} WHERE status = 'published'" );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-slides"></span> <?php esc_html_e( 'Product Recommendations', 'ah-ecommerce' ); ?></h1>
	<p>Configure how products recommend each other. Set upsells, cross-sells, and related products per product (from the Product edit screen).</p>

	<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; margin-bottom:20px;">
		<h2>Recommendation Types</h2>
		<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
			<div class="card" style="padding:15px; border-left:4px solid #3182ce;">
				<h3 style="margin:0 0 5px;">Upsells</h3>
				<p style="margin:0; font-size:13px;">Shown on the product page. "You might also prefer..." — higher-value alternatives.</p>
			</div>
			<div class="card" style="padding:15px; border-left:4px solid #38a169;">
				<h3 style="margin:0 0 5px;">Cross-sells</h3>
				<p style="margin:0; font-size:13px;">Shown in the cart. "Frequently bought together" — complementary products.</p>
			</div>
			<div class="card" style="padding:15px; border-left:4px solid #d69e2e;">
				<h3 style="margin:0 0 5px;">Related</h3>
				<p style="margin:0; font-size:13px;">Auto-detected from shared categories/tags. "Related Products" section.</p>
			</div>
		</div>
	</div>

	<h2>Products with Recommendation Settings</h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Product</th>
				<th>SKU</th>
				<th>Linked Categories</th>
				<th>Upsells</th>
				<th>Cross-sells</th>
				<th>Edit</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $all_products ) ) : ?>
				<tr><td colspan="6">No published products.</td></tr>
			<?php else : foreach ( $all_products as $product ) :
				$cat_meta = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$meta} WHERE product_id = %d AND meta_key = 'linked_categories'", $product->id ) );
				$upsells  = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$meta} WHERE product_id = %d AND meta_key = 'linked_upsells'", $product->id ) );
				$cross    = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$meta} WHERE product_id = %d AND meta_key = 'linked_crosssells'", $product->id ) );
				$related  = Recommendation_Service::get_related( $product->id, 0 );
			?>
				<tr>
					<td><strong><?php echo esc_html( $product->title ); ?></strong></td>
					<td><?php echo esc_html( $product->sku ?: '-' ); ?></td>
					<td><?php echo esc_html( $cat_meta ?: 'None set' ); ?></td>
					<td><?php echo esc_html( $upsells ?: 'None set' ); ?></td>
					<td><?php echo esc_html( $cross ?: 'None set' ); ?></td>
					<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-products&action=edit&id=' . $product->id ) ); ?>" class="button button-small">Edit</a></td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
