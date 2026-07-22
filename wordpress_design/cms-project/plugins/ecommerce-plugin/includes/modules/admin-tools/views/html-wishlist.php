<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table    = $wpdb->prefix . 'ah_ecommerce_wishlist';
$products = $wpdb->prefix . 'ah_ecommerce_products';

$page    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$offset  = ( $page - 1 ) * 20;
$search  = sanitize_text_field( $_GET['s'] ?? '' );

$where  = '1=1';
$args   = array();
if ( $search ) {
	$where .= ' AND w.user_id IN (SELECT ID FROM ' . $wpdb->users . ' WHERE user_login LIKE %s OR user_email LIKE %s)';
	$like   = '%' . $wpdb->esc_like( $search ) . '%';
	$args[] = $like;
	$args[] = $like;
}

$args[] = $offset;

$items = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT w.*, u.user_login, u.user_email, p.title AS product_name
		FROM {$table} w
		LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID
		LEFT JOIN {$products} p ON w.product_id = p.id
		WHERE {$where}
		ORDER BY w.created_at DESC
		LIMIT 20 OFFSET %d",
		...$args
	)
);

$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} w WHERE {$where}" );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-heart"></span> <?php esc_html_e( 'Wishlist', 'ah-ecommerce' ); ?></h1>
	<p>Track which products customers have wishlisted. Useful for marketing and restock alerts.</p>

	<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
		<form method="get" style="display:flex; gap:5px;">
			<input type="hidden" name="page" value="ah-wishlist">
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search by user...">
			<button class="button">Filter</button>
		</form>
		<div>
			<strong>Total Wishlisted Items:</strong> <?php echo number_format( $total ); ?>
		</div>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>User</th>
				<th>Email</th>
				<th>Product</th>
				<th>Date Added</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $items ) ) : ?>
				<tr><td colspan="4">No wishlist items found.</td></tr>
			<?php else : foreach ( $items as $item ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $item->user_login ?? 'Guest' ); ?></strong></td>
					<td><?php echo esc_html( $item->user_email ); ?></td>
					<td><?php echo esc_html( $item->product_name ?? '#' . $item->product_id ); ?></td>
					<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->created_at ) ) ); ?></td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
