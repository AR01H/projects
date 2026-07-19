<?php
/**
 * Admin View: Tools Page
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1>CMS Ecommerce - Admin Tools</h1>
	
	<?php settings_errors( 'cms_ecommerce' ); ?>

	<div class="card" style="max-width: 600px; padding: 20px; margin-top: 20px;">
		<h2>Database Management</h2>
		<p>Use these tools to manage the core tables for the ecommerce platform. <strong>Warning: Resetting the schema will delete all ecommerce data!</strong></p>
		
		<form method="post" action="">
			<?php wp_nonce_field( 'ah_ecommerce_tools_action' ); ?>
			
			<table class="form-table">
				<tr>
					<th><label for="update_schema">Initialize / Update Schemas</label></th>
					<td>
						<button type="submit" name="ah_ecommerce_action" value="update_schema" class="button button-primary">Initialize Missing Tables</button>
						<p class="description">Safely creates any missing tables for pending modules (Coupons, Categories, Customers, etc.) without deleting data.</p>
					</td>
				</tr>
				<tr>
					<th><label for="clear_cache">Clear Cache</label></th>
					<td>
						<button type="submit" name="ah_ecommerce_action" value="clear_cache" class="button button-secondary">Clear All Caches</button>
						<p class="description">Flushes the WordPress object cache.</p>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
