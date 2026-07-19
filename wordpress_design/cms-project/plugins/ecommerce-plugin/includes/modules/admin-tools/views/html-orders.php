<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );
$action = sanitize_key( $_GET['action'] ?? 'list' );

use AHEcommerce\Database\Order_Repository;
$repo = new Order_Repository();
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Orders', 'ah-ecommerce' ); ?></h1>
	<?php if ( $action === 'list' ) : 
		$search = sanitize_text_field( $_GET['s'] ?? '' );
		$paged  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$result = $repo->get_paginated( $paged, 20, $search );
		$items  = $result['items'];
	?>
		<div class="ah-table-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<form class="ah-search-form" method="get">
				<input type="hidden" name="page" value="ah-orders">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search Orders…">
				<button class="button">Filter</button>
			</form>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-orders&action=add' ) ); ?>" class="button button-primary">+ Create Order</a>
		</div>
		<div class="ah-table-wrap">
			<table class="ah-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Order ID</th>
						<th>Date</th>
						<th>Status</th>
						<th>Billing Name</th>
						<th>Total</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $items ) ) : ?>
						<tr><td colspan="6">No orders found.</td></tr>
					<?php else : foreach ( $items as $order ) : ?>
						<tr>
							<td><strong>#<?php echo esc_html( $order->id ); ?></strong></td>
							<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $order->created_at ) ) ); ?></td>
							<td><span class="ah-badge" style="background: #e2e8f0; padding:3px 8px; border-radius:4px; font-size:12px;"><?php echo esc_html( ucfirst( $order->status ) ); ?></span></td>
							<td><?php echo esc_html( $order->billing_first_name . ' ' . $order->billing_last_name ); ?></td>
							<td>$<?php echo number_format( (float) $order->total, 2 ); ?></td>
							<td><a href="#" class="button button-small">View</a></td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="ah-form-wrap" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<form method="post" class="ah-form">
				<div class="ah-form-actions" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
					<h2>Order Details</h2>
					<div>
						<button type="button" class="button button-primary button-large" disabled>Save Order</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-orders' ) ); ?>" class="button button-secondary button-large">Cancel</a>
					</div>
				</div>
				<div class="ah-form-row ah-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Customer</label>
						<input type="text" class="regular-text" style="width:100%;" placeholder="Search customer...">
					</div>
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Status</label>
						<select style="width:100%;">
							<option>Pending Payment</option>
							<option>Processing</option>
							<option>Completed</option>
							<option>Cancelled</option>
						</select>
					</div>
				</div>
				<div class="ah-form-row">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Order Notes</label>
					<textarea rows="4" style="width:100%; max-width:600px;"></textarea>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>
