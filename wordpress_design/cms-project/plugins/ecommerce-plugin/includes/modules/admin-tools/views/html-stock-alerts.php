<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Stock_Alerts\Stock_Alert_Service;

$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$result = Stock_Alert_Service::get_subscribers( 0, $page );
$notice = '';

if ( isset( $_GET['unsubscribe_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_stock_unsub' ) ) {
	Stock_Alert_Service::unsubscribe( (int) $_GET['unsubscribe_id'] );
	$notice = 'Subscriber removed.';
}
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Stock Alert Subscribers', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<p>Customers who requested to be notified when products are back in stock.</p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Product</th>
				<th>Email</th>
				<th>Status</th>
				<th>Subscribed</th>
				<th>Notified</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $result['items'] ) ) : ?>
				<tr><td colspan="6">No stock alert subscribers yet.</td></tr>
			<?php else : foreach ( $result['items'] as $sub ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $sub->product_name ?? '#' . $sub->product_id ); ?></strong></td>
					<td><?php echo esc_html( $sub->email ); ?></td>
					<td>
						<?php
						$st = $sub->status;
						$st_colors = array( 'pending' => '#fef3c7,#92400e', 'notified' => '#dcfce7,#166534' );
						list( $bg, $fg ) = explode( ',', $st_colors[ $st ] ?? '#f3f4f6,#6b7280' );
						?>
						<span style="background:<?php echo $bg; ?>; color:<?php echo $fg; ?>; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:600;"><?php echo ucfirst( $st ); ?></span>
					</td>
					<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub->created_at ) ) ); ?></td>
					<td><?php echo $sub->notified_at ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sub->notified_at ) ) ) : '—'; ?></td>
					<td><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ah-stock-alerts&unsubscribe_id=' . $sub->id ), 'ah_stock_unsub' ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Remove?');">Remove</a></td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
