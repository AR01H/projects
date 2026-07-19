<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );
$action = sanitize_key( $_GET['action'] ?? 'list' );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-category"></span> <?php esc_html_e( 'Categories', 'ah-ecommerce' ); ?></h1>
	<?php if ( $action === 'list' ) : ?>
		<div class="ah-table-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<form class="ah-search-form" method="get">
				<input type="hidden" name="page" value="ah-categories">
				<input type="search" name="s" value="" placeholder="Search Categories…">
				<button class="button">Filter</button>
			</form>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-categories&action=add' ) ); ?>" class="button button-primary">+ Add Category</a>
		</div>
		<div class="ah-table-wrap">
			<table class="ah-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Name</th>
						<th>Description</th>
						<th>Slug</th>
						<th>Count</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="5">No categories found. Database integration pending.</td></tr>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="ah-form-wrap" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<form method="post" class="ah-form">
				<div class="ah-form-actions" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
					<h2>Category Details</h2>
					<div>
						<button type="button" class="button button-primary button-large" disabled>Save Category</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-categories' ) ); ?>" class="button button-secondary button-large">Cancel</a>
					</div>
				</div>
				<div class="ah-form-row">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Name</label>
					<input type="text" class="regular-text" style="width:100%; max-width:600px;">
				</div>
				<div class="ah-form-row">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Parent Category</label>
					<select style="width:100%; max-width:600px;">
						<option value="0">None</option>
					</select>
				</div>
				<div class="ah-form-row">
					<label style="display:block; font-weight:bold; margin-bottom:5px;">Description</label>
					<textarea rows="4" style="width:100%; max-width:600px;"></textarea>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>
