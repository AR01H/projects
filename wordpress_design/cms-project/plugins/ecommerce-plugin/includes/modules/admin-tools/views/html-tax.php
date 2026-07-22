<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Tax\Tax_Service;

$notice = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	check_admin_referer( 'ah_save_tax' );
	$sub = sanitize_text_field( $_POST['sub_action'] ?? '' );

	if ( $sub === 'create_tax_rule' ) {
		$id = Tax_Service::create_rule( array(
			'name'     => sanitize_text_field( $_POST['rule_name'] ),
			'rate'     => (float) $_POST['rate'],
			'type'     => sanitize_key( $_POST['rate_type'] ),
			'country'  => sanitize_text_field( $_POST['country'] ?? '' ),
			'state'    => sanitize_text_field( $_POST['state'] ?? '' ),
			'apply_to' => sanitize_key( $_POST['apply_to'] ),
		) );
		$notice = $id ? 'Tax rule created.' : 'Failed to create rule.';
	}
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_delete_tax' ) ) {
	Tax_Service::delete_rule( (int) $_GET['delete_id'] );
	$notice = 'Tax rule deleted.';
}

$rules = Tax_Service::get_rules();
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e( 'Tax Rules', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<div style="display:grid; grid-template-columns:2fr 1fr; gap:30px;">
		<!-- Rules List -->
		<div>
			<h2>Active Tax Rules</h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr><th>Name</th><th>Rate</th><th>Country</th><th>State</th><th>Applied To</th><th>Status</th><th>Actions</th></tr>
				</thead>
				<tbody>
					<?php if ( empty( $rules ) ) : ?>
						<tr><td colspan="7">No tax rules configured. Add one on the right.</td></tr>
					<?php else : foreach ( $rules as $rule ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $rule->name ); ?></strong></td>
							<td><?php echo $rule->type === 'percent' ? esc_html( $rule->rate ) . '%' : '$' . number_format( $rule->rate, 2 ); ?></td>
							<td><?php echo esc_html( $rule->country ?: 'All' ); ?></td>
							<td><?php echo esc_html( $rule->state ?: 'All' ); ?></td>
							<td><?php echo esc_html( ucfirst( $rule->apply_to ) ); ?></td>
							<td><span style="color:<?php echo $rule->status === 'active' ? '#166534' : '#6b7280'; ?>; font-weight:600;"><?php echo ucfirst( $rule->status ); ?></span></td>
							<td><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ah-tax&delete_id=' . $rule->id ), 'ah_delete_tax' ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete?');">Delete</a></td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Add Rule Form -->
		<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; height:fit-content;">
			<h2>Add Tax Rule</h2>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_tax' ); ?>
				<input type="hidden" name="sub_action" value="create_tax_rule">

				<div style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Rule Name *</label>
					<input type="text" name="rule_name" required class="regular-text" style="width:100%;" placeholder="e.g. State Sales Tax">
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Rate *</label>
						<input type="number" step="0.01" name="rate" required class="regular-text" style="width:100%;" placeholder="8.25">
					</div>
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Type</label>
						<select name="rate_type" style="width:100%;">
							<option value="percent">Percentage (%)</option>
							<option value="fixed">Fixed Amount ($)</option>
						</select>
					</div>
				</div>
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Country</label>
						<input type="text" name="country" class="regular-text" style="width:100%;" placeholder="e.g. US">
					</div>
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">State</label>
						<input type="text" name="state" class="regular-text" style="width:100%;" placeholder="e.g. CA">
					</div>
				</div>
				<div style="margin-bottom:15px;">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Apply To</label>
					<select name="apply_to" style="width:100%;">
						<option value="shipping">Shipping Cost</option>
						<option value="subtotal">Order Subtotal</option>
					</select>
				</div>
				<button type="submit" class="button button-primary" style="width:100%;">Add Rule</button>
			</form>
		</div>
	</div>
</div>
