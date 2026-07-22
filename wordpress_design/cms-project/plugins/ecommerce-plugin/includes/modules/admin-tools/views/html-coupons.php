<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Coupons\Coupon_Engine;

$notice = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	check_admin_referer( 'ah_save_coupon' );
	$sub = sanitize_text_field( $_POST['sub_action'] ?? '' );

	if ( $sub === 'create_coupon' ) {
		$id = Coupon_Engine::create_coupon( array(
			'code'          => sanitize_text_field( $_POST['code'] ),
			'discount_type' => sanitize_key( $_POST['discount_type'] ),
			'amount'        => (float) $_POST['amount'],
			'usage_limit'   => $_POST['usage_limit'] !== '' ? (int) $_POST['usage_limit'] : null,
			'expiry_date'   => sanitize_text_field( $_POST['expiry_date'] ?? '' ),
			'minimum_spend' => (float) ( $_POST['minimum_spend'] ?? 0 ),
		) );
		$notice = $id ? 'Coupon created.' : 'Failed. Code may already exist.';
	}
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_delete_coupon' ) ) {
	Coupon_Engine::delete_coupon( (int) $_GET['delete_id'] );
	$notice = 'Coupon deleted.';
}

$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$result = Coupon_Engine::get_coupons( $page, 20 );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-tickets-alt"></span> <?php esc_html_e( 'Coupons & Discounts', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<div style="display:grid; grid-template-columns:2fr 1fr; gap:30px;">
		<!-- Coupons List -->
		<div>
			<h2>Active Coupons</h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr><th>Code</th><th>Type</th><th>Amount</th><th>Min Spend</th><th>Usage</th><th>Expiry</th><th>Status</th><th>Actions</th></tr>
				</thead>
				<tbody>
					<?php if ( empty( $result['items'] ) ) : ?>
						<tr><td colspan="8">No coupons yet.</td></tr>
					<?php else : foreach ( $result['items'] as $coupon ) :
						$expired = $coupon->expiry_date && strtotime( $coupon->expiry_date ) < time();
					?>
						<tr>
							<td><strong><?php echo esc_html( $coupon->code ); ?></strong></td>
							<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $coupon->discount_type ) ) ); ?></td>
							<td><?php echo $coupon->discount_type === 'percent' ? esc_html( $coupon->amount ) . '%' : '$' . number_format( $coupon->amount, 2 ); ?></td>
							<td><?php echo $coupon->minimum_spend > 0 ? '$' . number_format( $coupon->minimum_spend, 2 ) : '-'; ?></td>
							<td><?php echo $coupon->usage_count . ( $coupon->usage_limit ? ' / ' . $coupon->usage_limit : ' / ∞' ); ?></td>
							<td><?php echo $coupon->expiry_date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $coupon->expiry_date ) ) ) : 'Never'; ?></td>
							<td>
								<?php if ( $expired ) : ?>
									<span style="color:#6b7280; font-weight:600;">Expired</span>
								<?php else : ?>
									<span style="color:#166534; font-weight:600;">Active</span>
								<?php endif; ?>
							</td>
							<td><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ah-coupons&delete_id=' . $coupon->id ), 'ah_delete_coupon' ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete?');">Delete</a></td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Add Coupon Form -->
		<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; height:fit-content;">
			<h2>Add Coupon</h2>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_coupon' ); ?>
				<input type="hidden" name="sub_action" value="create_coupon">

				<div style="margin-bottom:12px;">
					<label style="display:block; font-weight:bold; font-size:12px;">Coupon Code *</label>
					<input type="text" name="code" required class="regular-text" style="width:100%; text-transform:uppercase;" placeholder="e.g. SAVE20">
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Type</label>
						<select name="discount_type" style="width:100%;">
							<option value="percent">Percentage</option>
							<option value="fixed_cart">Fixed Cart</option>
							<option value="fixed_product">Fixed Product</option>
							<option value="free_shipping">Free Shipping</option>
						</select>
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Amount *</label>
						<input type="number" step="0.01" name="amount" required class="regular-text" style="width:100%;">
					</div>
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Min Spend ($)</label>
						<input type="number" step="0.01" name="minimum_spend" value="0" class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Usage Limit</label>
						<input type="number" name="usage_limit" class="regular-text" style="width:100%;" placeholder="Unlimited">
					</div>
				</div>
				<div style="margin-bottom:12px;">
					<label style="display:block; font-weight:bold; font-size:12px;">Expiry Date</label>
					<input type="datetime-local" name="expiry_date" class="regular-text" style="width:100%;">
				</div>
				<button type="submit" class="button button-primary" style="width:100%;">Create Coupon</button>
			</form>
		</div>
	</div>
</div>
