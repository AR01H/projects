<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table  = $wpdb->prefix . 'ah_ecommerce_price_rules';
$notice = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	check_admin_referer( 'ah_save_pricing' );
	$sub = sanitize_text_field( $_POST['sub_action'] ?? '' );

	if ( $sub === 'create_price_rule' ) {
		$wpdb->insert( $table, array(
			'name'           => sanitize_text_field( $_POST['rule_name'] ),
			'rule_type'      => sanitize_key( $_POST['rule_type'] ),
			'min_qty'        => (int) $_POST['min_qty'],
			'max_qty'        => $_POST['max_qty'] !== '' ? (int) $_POST['max_qty'] : null,
			'discount_type'  => sanitize_key( $_POST['discount_type'] ),
			'discount_value' => (float) $_POST['discount_value'],
			'user_role'      => sanitize_text_field( $_POST['user_role'] ?? '' ),
			'start_date'     => sanitize_text_field( $_POST['start_date'] ?? '' ),
			'end_date'       => sanitize_text_field( $_POST['end_date'] ?? '' ),
			'priority'       => (int) ( $_POST['priority'] ?? 10 ),
			'status'         => 'active',
		) );
		$notice = 'Pricing rule created.';
	}
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_delete_rule' ) ) {
	$wpdb->delete( $table, array( 'id' => (int) $_GET['delete_id'] ) );
	$notice = 'Rule deleted.';
}

$rules = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY priority ASC, created_at DESC" );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-discount"></span> <?php esc_html_e( 'Pricing Rules', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<div style="display:grid; grid-template-columns:2fr 1fr; gap:30px;">
		<!-- Rules List -->
		<div>
			<h2>Active Rules</h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr><th>Name</th><th>Type</th><th>Min Qty</th><th>Discount</th><th>Role</th><th>Priority</th><th>Actions</th></tr>
				</thead>
				<tbody>
					<?php if ( empty( $rules ) ) : ?>
						<tr><td colspan="7">No pricing rules. Add one on the right.</td></tr>
					<?php else : foreach ( $rules as $rule ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $rule->name ); ?></strong></td>
							<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $rule->rule_type ) ) ); ?></td>
							<td><?php echo (int) $rule->min_qty; ?>+</td>
							<td><?php echo $rule->discount_type === 'percent' ? esc_html( $rule->discount_value ) . '%' : '$' . number_format( $rule->discount_value, 2 ); ?></td>
							<td><?php echo esc_html( $rule->user_role ?: 'All' ); ?></td>
							<td><?php echo (int) $rule->priority; ?></td>
							<td><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ah-pricing&delete_id=' . $rule->id ), 'ah_delete_rule' ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete?');">Delete</a></td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Add Rule Form -->
		<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; height:fit-content;">
			<h2>Add Pricing Rule</h2>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_pricing' ); ?>
				<input type="hidden" name="sub_action" value="create_price_rule">

				<div style="margin-bottom:12px;">
					<label style="display:block; font-weight:bold; font-size:12px;">Rule Name *</label>
					<input type="text" name="rule_name" required class="regular-text" style="width:100%;">
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Type</label>
						<select name="rule_type" style="width:100%;">
							<option value="bulk">Bulk Pricing</option>
							<option value="tiered">Tiered Pricing</option>
							<option value="role_based">Role Based</option>
							<option value="wholesale">Wholesale</option>
						</select>
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">User Role</label>
						<select name="user_role" style="width:100%;">
							<option value="">All Roles</option>
							<option value="subscriber">Subscriber</option>
							<option value="customer">Customer</option>
							<option value="wholesale_customer">Wholesale Customer</option>
							<option value="vendor">Vendor</option>
						</select>
					</div>
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Min Qty *</label>
						<input type="number" name="min_qty" value="1" required class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Max Qty</label>
						<input type="number" name="max_qty" class="regular-text" style="width:100%;" placeholder="No limit">
					</div>
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Discount Type</label>
						<select name="discount_type" style="width:100%;">
							<option value="percent">Percentage</option>
							<option value="fixed">Fixed Amount</option>
						</select>
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Discount Value *</label>
						<input type="number" step="0.01" name="discount_value" required class="regular-text" style="width:100%;">
					</div>
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Start Date</label>
						<input type="datetime-local" name="start_date" class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">End Date</label>
						<input type="datetime-local" name="end_date" class="regular-text" style="width:100%;">
					</div>
				</div>
				<div style="margin-bottom:12px;">
					<label style="display:block; font-weight:bold; font-size:12px;">Priority (lower = higher)</label>
					<input type="number" name="priority" value="10" class="regular-text" style="width:100%;">
				</div>
				<button type="submit" class="button button-primary" style="width:100%;">Add Rule</button>
			</form>
		</div>
	</div>
</div>
