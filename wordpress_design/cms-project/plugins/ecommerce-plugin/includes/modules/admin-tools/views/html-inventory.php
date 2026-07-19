<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );
$action = sanitize_key( $_GET['action'] ?? 'list' );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Inventory Management', 'ah-ecommerce' ); ?></h1>
	<?php if ( $action === 'list' ) : ?>
		<div class="ah-table-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<form class="ah-search-form" method="get">
				<input type="hidden" name="page" value="ah-inventory">
				<input type="search" name="s" value="" placeholder="Search Inventory…">
				<button class="button">Filter</button>
			</form>
		</div>
		<div class="ah-table-wrap">
			<table class="ah-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Product</th>
						<th>SKU</th>
						<th>Stock Status</th>
						<th>Quantity</th>
						<th>Warehouse</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="6">No inventory data found. Database integration pending.</td></tr>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
