<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$log_table = $wpdb->prefix . 'ah_ecommerce_email_log';

$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$offset = ( $page - 1 ) * 20;

$logs   = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$log_table} ORDER BY sent_at DESC LIMIT 20 OFFSET %d",
		$offset
	)
);
$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table}" );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-email"></span> <?php esc_html_e( 'Notification Settings & Log', 'ah-ecommerce' ); ?></h1>

	<!-- Notification Types -->
	<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; margin-bottom:20px;">
		<h2>Active Notification Types</h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr><th>Notification</th><th>Trigger</th><th>Recipient</th><th>Status</th></tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Order Confirmation</strong></td>
					<td>Customer places an order</td>
					<td>Customer email</td>
					<td><span style="color:#166534; font-weight:600;">Active</span></td>
				</tr>
				<tr>
					<td><strong>Order Status Update</strong></td>
					<td>Order status changes</td>
					<td>Customer email</td>
					<td><span style="color:#166534; font-weight:600;">Active</span></td>
				</tr>
				<tr>
					<td><strong>Low Stock Alert</strong></td>
					<td>Stock drops below threshold</td>
					<td>Admin email</td>
					<td><span style="color:#166534; font-weight:600;">Active</span></td>
				</tr>
				<tr>
					<td><strong>Out of Stock Alert</strong></td>
					<td>Stock reaches zero</td>
					<td>Admin email</td>
					<td><span style="color:#166534; font-weight:600;">Active</span></td>
				</tr>
				<tr>
					<td><strong>Abandoned Cart Reminder</strong></td>
					<td>Cart inactive for 24+ hours</td>
					<td>Customer email</td>
					<td><span style="color:#166534; font-weight:600;">Active</span></td>
				</tr>
				<tr>
					<td><strong>New Review Alert</strong></td>
					<td>Customer submits a review</td>
					<td>Admin email</td>
					<td><span style="color:#166534; font-weight:600;">Active</span></td>
				</tr>
			</tbody>
		</table>
		<p class="description" style="margin-top:10px;">Email templates can be overridden in your theme at <code>ah-ecommerce/emails/</code>. Copy any template from <code>wp-content/plugins/ecommerce-plugin/includes/commerce/notifications/templates/</code> to customize.</p>
	</div>

	<!-- Email Log -->
	<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
		<h2>Recent Email Log</h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr><th>To</th><th>Subject</th><th>Template</th><th>Status</th><th>Sent At</th></tr>
			</thead>
			<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="5">No emails sent yet.</td></tr>
				<?php else : foreach ( $logs as $log ) : ?>
					<tr>
						<td><?php echo esc_html( $log->to_email ); ?></td>
						<td><?php echo esc_html( $log->subject ); ?></td>
						<td><?php echo esc_html( $log->template ?: '-' ); ?></td>
						<td><span style="color:<?php echo $log->status === 'sent' ? '#166534' : '#dc2626'; ?>; font-weight:600;"><?php echo ucfirst( $log->status ); ?></span></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' g:i A', strtotime( $log->sent_at ) ) ); ?></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
