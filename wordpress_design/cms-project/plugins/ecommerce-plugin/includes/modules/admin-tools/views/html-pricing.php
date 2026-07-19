<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );
$action = sanitize_key( $_GET['action'] ?? 'list' );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-discount"></span> <?php esc_html_e( 'Pricing Rules', 'ah-ecommerce' ); ?></h1>
	<?php if ( $action === 'list' ) : ?>
		<div class="ah-table-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<form class="ah-search-form" method="get">
				<input type="hidden" name="page" value="ah-pricing">
				<input type="search" name="s" value="" placeholder="Search Rules…">
				<button class="button">Filter</button>
			</form>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-pricing&action=add' ) ); ?>" class="button button-primary">+ Add Pricing Rule</a>
		</div>
		<div class="ah-table-wrap">
			<table class="ah-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Rule Name</th>
						<th>Type</th>
						<th>Conditions</th>
						<th>Priority</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="6">No rules found. Database integration pending.</td></tr>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="ah-form-wrap" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<form method="post" class="ah-form">
				<div class="ah-form-actions" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
					<h2>Pricing Rule Configuration</h2>
					<div>
						<button type="button" class="button button-primary button-large" disabled>Save Rule</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-pricing' ) ); ?>" class="button button-secondary button-large">Cancel</a>
					</div>
				</div>
				<div class="ah-form-row">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Rule Name</label>
					<input type="text" class="regular-text" style="width:100%; max-width:600px;" placeholder="e.g. Buy 2 Get 1 Free">
				</div>
				<div class="ah-form-row ah-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Rule Type</label>
						<select style="width:100%;">
							<option>Bulk Pricing</option>
							<option>Tier Pricing</option>
							<option>Dynamic Pricing</option>
							<option>Role Based Pricing</option>
						</select>
					</div>
					<div>
						<label style="display:block; font-weight:bold; margin-bottom:5px;">Discount Amount</label>
						<input type="number" step="0.01" class="regular-text" style="width:100%;">
					</div>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>
