<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Inventory\Inventory_Service;

global $wpdb;
$products = $wpdb->prefix . 'ah_ecommerce_products';
$meta     = $wpdb->prefix . 'ah_ecommerce_product_meta';
$notice   = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	check_admin_referer( 'ah_save_inventory' );
	$sub = sanitize_text_field( $_POST['sub_action'] ?? '' );

	if ( $sub === 'update_stock' ) {
		Inventory_Service::set_stock( (int) $_POST['product_id'], (int) $_POST['quantity'] );
		$notice = 'Stock updated.';
	}
}

$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$offset = ( $page - 1 ) * 20;
$search = sanitize_text_field( $_GET['s'] ?? '' );

$where = 'p.status = "published"';
$args  = array();
if ( $search ) {
	$where .= ' AND (p.title LIKE %s OR p.sku LIKE %s)';
	$like   = '%' . $wpdb->esc_like( $search ) . '%';
	$args[] = $like;
	$args[] = $like;
}

$args[] = $offset;

$items = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT p.*, m_stock.meta_value AS stock_qty, m_backorder.meta_value AS allow_backorders, m_low.meta_value AS low_threshold
		FROM {$products} p
		LEFT JOIN {$meta} m_stock ON p.id = m_stock.product_id AND m_stock.meta_key = 'stock_quantity'
		LEFT JOIN {$meta} m_backorder ON p.id = m_backorder.product_id AND m_backorder.meta_key = 'allow_backorders'
		LEFT JOIN {$meta} m_low ON p.id = m_low.product_id AND m_low.meta_key = 'low_stock_threshold'
		WHERE {$where}
		ORDER BY p.title ASC
		LIMIT 20 OFFSET %d",
		...$args
	)
);

$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$products} p WHERE {$where}" );

$low_stock = Inventory_Service::get_low_stock_products( 5 );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Inventory Management', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<?php if ( ! empty( $low_stock ) ) : ?>
		<div style="background:#fef3c7; border:1px solid #fbbf24; border-radius:4px; padding:15px; margin-bottom:20px;">
			<strong style="color:#92400e;">⚠ Low Stock Alert (<?php echo count( $low_stock ); ?> products below threshold)</strong>
			<ul style="margin:5px 0 0 20px;">
				<?php foreach ( $low_stock as $ls ) : ?>
					<li><?php echo esc_html( $ls->title ); ?> — <strong style="color:#dc2626;"><?php echo (int) $ls->stock_quantity; ?> left</strong></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
		<form method="get" style="display:flex; gap:5px;">
			<input type="hidden" name="page" value="ah-inventory">
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search products...">
			<button class="button">Filter</button>
		</form>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Product</th>
				<th>SKU</th>
				<th>Stock</th>
				<th>Status</th>
				<th>Backorders</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $items ) ) : ?>
				<tr><td colspan="6">No products found.</td></tr>
			<?php else : foreach ( $items as $p ) :
				$stock = $p->stock_qty !== null ? (int) $p->stock_qty : -1;
				$status = Inventory_Service::get_stock_status( $p->id );
				$stock_label = array(
					'instock'     => array( '#dcfce7', '#166534', 'In Stock' ),
					'lowstock'    => array( '#fef3c7', '#92400e', 'Low Stock' ),
					'outofstock'  => array( '#fee2e2', '#991b1b', 'Out of Stock' ),
					'onbackorder' => array( '#e0e7ff', '#3730a3', 'Backorder' ),
				);
				list( $bg, $fg, $label ) = $stock_label[ $status ] ?? array( '#f3f4f6', '#6b7280', 'Unknown' );
			?>
				<tr>
					<td><strong><?php echo esc_html( $p->title ); ?></strong></td>
					<td><?php echo esc_html( $p->sku ?: '-' ); ?></td>
					<td>
						<?php if ( $stock === -1 ) : ?>
							<em>Unlimited</em>
						<?php else : ?>
							<strong style="color:<?php echo $fg; ?>;"><?php echo $stock; ?></strong>
						<?php endif; ?>
					</td>
					<td><span style="background:<?php echo $bg; ?>; color:<?php echo $fg; ?>; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:600;"><?php echo $label; ?></span></td>
					<td><?php echo $p->allow_backorders === 'yes' ? 'Yes' : 'No'; ?></td>
					<td>
						<form method="post" style="display:inline-flex; gap:5px; align-items:center;">
							<?php wp_nonce_field( 'ah_save_inventory' ); ?>
							<input type="hidden" name="sub_action" value="update_stock">
							<input type="hidden" name="product_id" value="<?php echo esc_attr( $p->id ); ?>">
							<input type="number" name="quantity" value="<?php echo esc_attr( $stock === -1 ? '' : $stock ); ?>" min="0" class="small-text" style="width:80px;" placeholder="Qty">
							<button type="submit" class="button button-small">Update</button>
						</form>
					</td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
