<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Abandoned_Cart\Abandoned_Cart_Service;

$stats = Abandoned_Cart_Service::get_stats();
$page  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$tab   = sanitize_key( $_GET['tab'] ?? 'pending' );

$result = Abandoned_Cart_Service::get_carts( $page, 20, $tab !== 'all' ? $tab : '' );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Abandoned Carts', 'ah-ecommerce' ); ?></h1>

	<!-- Stats Cards -->
	<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:15px; margin-bottom:25px;">
		<div class="card" style="padding:15px; border-left:4px solid #e53e3e;">
			<strong>Total Abandoned</strong>
			<div style="font-size:24px; font-weight:bold; margin-top:5px;"><?php echo number_format( $stats['total'] ); ?></div>
		</div>
		<div class="card" style="padding:15px; border-left:4px solid #d69e2e;">
			<strong>Pending Recovery</strong>
			<div style="font-size:24px; font-weight:bold; margin-top:5px;"><?php echo number_format( $stats['pending'] ); ?></div>
		</div>
		<div class="card" style="padding:15px; border-left:4px solid #38a169;">
			<strong>Recovered</strong>
			<div style="font-size:24px; font-weight:bold; margin-top:5px;"><?php echo number_format( $stats['recovered'] ); ?></div>
		</div>
		<div class="card" style="padding:15px; border-left:4px solid #3182ce;">
			<strong>Pending Value</strong>
			<div style="font-size:24px; font-weight:bold; margin-top:5px;">$<?php echo number_format( $stats['total_value'], 2 ); ?></div>
		</div>
	</div>

	<h2 class="nav-tab-wrapper">
		<a href="?page=ah-abandoned&tab=pending" class="nav-tab <?php echo $tab === 'pending' ? 'nav-tab-active' : ''; ?>">Pending</a>
		<a href="?page=ah-abandoned&tab=recovered" class="nav-tab <?php echo $tab === 'recovered' ? 'nav-tab-active' : ''; ?>">Recovered</a>
		<a href="?page=ah-abandoned&tab=all" class="nav-tab <?php echo $tab === 'all' ? 'nav-tab-active' : ''; ?>">All</a>
	</h2>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Email</th>
				<th>Cart Total</th>
				<th>Reminders Sent</th>
				<th>Last Activity</th>
				<th>Status</th>
				<th>Last Reminder</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $result['items'] ) ) : ?>
				<tr><td colspan="6">No abandoned carts found.</td></tr>
			<?php else : foreach ( $result['items'] as $cart ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $cart->email ); ?></strong></td>
					<td>$<?php echo number_format( $cart->cart_total, 2 ); ?></td>
					<td><?php echo (int) $cart->reminder_count; ?></td>
					<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' g:i A', strtotime( $cart->last_activity ) ) ); ?></td>
					<td>
						<?php
						$colors = array( 'pending' => '#fef3c7,#92400e', 'recovered' => '#dcfce7,#166534' );
						list( $bg, $fg ) = explode( ',', $colors[ $cart->status ] ?? '#f3f4f6,#6b7280' );
						?>
						<span style="background:<?php echo $bg; ?>; color:<?php echo $fg; ?>; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:600;"><?php echo ucfirst( $cart->status ); ?></span>
					</td>
					<td><?php echo $cart->last_reminder ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $cart->last_reminder ) ) ) : 'Never'; ?></td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
