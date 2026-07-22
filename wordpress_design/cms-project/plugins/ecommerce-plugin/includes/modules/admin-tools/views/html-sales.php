<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Sales\Sale_Service;

$action = sanitize_key( $_GET['action'] ?? 'list' );
$notice = '';

// Handle form submissions.
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	check_admin_referer( 'ah_save_sale' );
	$sub = sanitize_text_field( $_POST['sub_action'] ?? '' );

	if ( $sub === 'create_sale' ) {
		$id = Sale_Service::create_sale( array(
			'product_id' => (int) $_POST['product_id'],
			'sale_price' => (float) $_POST['sale_price'],
			'start_date' => sanitize_text_field( $_POST['start_date'] ),
			'end_date'   => sanitize_text_field( $_POST['end_date'] ),
		) );
		$notice = $id ? 'Sale scheduled successfully.' : 'Failed to create sale.';
		$action = 'list';
	}
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_delete_sale' ) ) {
	Sale_Service::delete_sale( (int) $_GET['delete_id'] );
	$notice = 'Sale deleted.';
	$action = 'list';
}

$sales = Sale_Service::get_sales( max( 1, (int) ( $_GET['paged'] ?? 1 ) ), 20 );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Sales & Promotions', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<?php if ( $action === 'list' ) : ?>
		<div class="ah-table-top" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
			<h2>Active & Scheduled Sales</h2>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-sales&action=add' ) ); ?>" class="button button-primary">+ Schedule New Sale</a>
		</div>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>Product</th>
					<th>Sale Price</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Status</th>
					<th>Countdown</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $sales['items'] ) ) : ?>
					<tr><td colspan="7">No sales scheduled. Click "Schedule New Sale" to create one.</td></tr>
				<?php else : foreach ( $sales['items'] as $sale ) :
					$now     = time();
					$start   = strtotime( $sale->start_date );
					$end     = strtotime( $sale->end_date );
					$active  = $now >= $start && $now <= $end;
					$expired = $now > $end;
					$status  = $expired ? 'expired' : ( $active ? 'active' : 'scheduled' );
					$colors  = array( 'active' => '#166534', 'scheduled' => '#92400e', 'expired' => '#6b7280' );
					$bg      = array( 'active' => '#dcfce7', 'scheduled' => '#fef3c7', 'expired' => '#f3f4f6' );
				?>
					<tr>
						<td><strong><?php echo esc_html( $sale->product_name ?? '#' . $sale->product_id ); ?></strong></td>
						<td>$<?php echo number_format( $sale->sale_price, 2 ); ?></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' g:i A', $start ) ); ?></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' g:i A', $end ) ); ?></td>
						<td><span style="background:<?php echo $bg[ $status ]; ?>; color:<?php echo $colors[ $status ]; ?>; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:600;"><?php echo ucfirst( $status ); ?></span></td>
						<td>
							<?php if ( $active ) :
								$diff = $end - $now;
								printf( '%dd %dh %dm', floor( $diff / 86400 ), floor( ( $diff % 86400 ) / 3600 ), floor( ( $diff % 3600 ) / 60 ) );
							elseif ( $expired ) :
								echo '<span style="color:#6b7280;">Ended</span>';
							else :
								$diff = $start - $now;
								printf( 'Starts in %dd %dh', floor( $diff / 86400 ), floor( ( $diff % 86400 ) / 3600 ) );
							endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ah-sales&delete_id=' . $sale->id ), 'ah_delete_sale' ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete this sale?');">Delete</a>
						</td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>

	<?php elseif ( $action === 'add' ) : ?>
		<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
			<h2>Schedule a New Sale</h2>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_sale' ); ?>
				<input type="hidden" name="sub_action" value="create_sale">

				<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; max-width:800px; margin:20px 0;">
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Product ID *</label>
						<input type="number" name="product_id" required class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Sale Price ($) *</label>
						<input type="number" step="0.01" name="sale_price" required class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Start Date & Time *</label>
						<input type="datetime-local" name="start_date" required class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">End Date & Time *</label>
						<input type="datetime-local" name="end_date" required class="regular-text" style="width:100%;">
					</div>
				</div>
				<p class="description">The sale will automatically activate at the start date and expire at the end date. A countdown timer will be shown on the product page.</p>
				<div style="margin-top:20px;">
					<button type="submit" class="button button-primary button-large">Schedule Sale</button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-sales' ) ); ?>" class="button button-secondary button-large">Cancel</a>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>
