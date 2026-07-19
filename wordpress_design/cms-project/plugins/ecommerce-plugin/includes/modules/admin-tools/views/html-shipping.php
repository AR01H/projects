<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );
$action = sanitize_key( $_GET['action'] ?? 'list' );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-car"></span> <?php esc_html_e( 'Shipping Zones', 'ah-ecommerce' ); ?></h1>
	<?php if ( $action === 'list' ) : ?>
		<div class="ah-table-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<form class="ah-search-form" method="get">
				<input type="hidden" name="page" value="ah-shipping">
				<input type="search" name="s" value="" placeholder="Search Zones…">
				<button class="button">Filter</button>
			</form>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-shipping&action=add' ) ); ?>" class="button button-primary">+ Add Shipping Zone</a>
		</div>
		<div class="ah-table-wrap">
			<table class="ah-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Zone Name</th>
						<th>Regions</th>
						<th>Shipping Methods</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="4">No shipping zones found. Database integration pending.</td></tr>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="ah-form-wrap" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<form method="post" class="ah-form">
				<div class="ah-form-actions" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
					<h2>Shipping Zone Configuration</h2>
					<div>
						<button type="button" class="button button-primary button-large" disabled>Save Zone</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-shipping' ) ); ?>" class="button button-secondary button-large">Cancel</a>
					</div>
				</div>
				<div class="ah-form-row">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Zone Name</label>
					<input type="text" class="regular-text" style="width:100%; max-width:600px;">
				</div>
				<div class="ah-form-row">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Zone Regions</label>
					<select multiple style="width:100%; max-width:600px; height: 100px;">
						<option>United States (US)</option>
						<option>United Kingdom (UK)</option>
						<option>India (IN)</option>
						<option>Everywhere Else</option>
					</select>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>
