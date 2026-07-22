<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Shipping\Shipping_Service;

$notice = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	check_admin_referer( 'ah_save_shipping' );
	$sub = sanitize_text_field( $_POST['sub_action'] ?? '' );

	if ( $sub === 'create_zone' ) {
		$id = Shipping_Service::create_zone(
			sanitize_text_field( $_POST['zone_name'] ),
			array_filter( array_map( 'trim', explode( ',', $_POST['regions'] ?? '' ) ) )
		);
		$notice = $id ? 'Shipping zone created.' : 'Failed to create zone.';
	}

	if ( $sub === 'add_method' ) {
		$id = Shipping_Service::add_method( array(
			'zone_id'      => (int) $_POST['zone_id'],
			'method_type'  => sanitize_key( $_POST['method_type'] ),
			'method_title' => sanitize_text_field( $_POST['method_title'] ),
			'cost'         => (float) $_POST['cost'],
			'min_order'    => (float) ( $_POST['min_order'] ?? 0 ),
			'max_order'    => (float) ( $_POST['max_order'] ?? 0 ),
		) );
		$notice = $id ? 'Shipping method added.' : 'Failed to add method.';
	}
}

if ( isset( $_GET['delete_zone'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_delete_zone' ) ) {
	Shipping_Service::delete_zone( (int) $_GET['delete_zone'] );
	$notice = 'Zone deleted.';
}

if ( isset( $_GET['delete_method'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_delete_method' ) ) {
	Shipping_Service::delete_method( (int) $_GET['delete_method'] );
	$notice = 'Method deleted.';
}

$zones = Shipping_Service::get_zones();
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-car"></span> <?php esc_html_e( 'Shipping Zones & Methods', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<?php if ( empty( $zones ) ) : ?>
		<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; text-align:center;">
			<h2>No Shipping Zones</h2>
			<p>Create your first shipping zone to define where you ship and at what cost.</p>
		</div>
	<?php endif; ?>

	<?php foreach ( $zones as $zone ) :
		$methods = Shipping_Service::get_methods( $zone->id );
	?>
		<div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; margin-bottom:20px;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
				<h2 style="margin:0;"><?php echo esc_html( $zone->name ); ?></h2>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ah-shipping&delete_zone=' . $zone->id ), 'ah_delete_zone' ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete zone?');">Delete Zone</a>
			</div>
			<p><strong>Regions:</strong> <?php echo esc_html( implode( ', ', $zone->regions ) ?: 'All locations' ); ?></p>

			<table class="wp-list-table widefat fixed striped" style="margin-top:10px;">
				<thead><tr><th>Method</th><th>Type</th><th>Cost</th><th>Min Order</th><th>Max Order</th><th>Status</th><th>Actions</th></tr></thead>
				<tbody>
					<?php if ( empty( $methods ) ) : ?>
						<tr><td colspan="7">No methods in this zone.</td></tr>
					<?php else : foreach ( $methods as $m ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $m->method_title ); ?></strong></td>
							<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $m->method_type ) ) ); ?></td>
							<td>$<?php echo number_format( $m->cost, 2 ); ?></td>
							<td><?php echo $m->min_order > 0 ? '$' . number_format( $m->min_order, 2 ) : '-'; ?></td>
							<td><?php echo $m->max_order > 0 ? '$' . number_format( $m->max_order, 2 ) : '-'; ?></td>
							<td><?php echo $m->enabled ? '<span style="color:green;">Enabled</span>' : '<span style="color:gray;">Disabled</span>'; ?></td>
							<td><a href="<?php echo esc_url( wp_nonce_url( admin_url( "admin.php?page=ah-shipping&delete_method={$m->id}" ), 'ah_delete_method' ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete?');">Delete</a></td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>

			<!-- Add Method Form -->
			<div style="margin-top:15px; padding:15px; background:#f9f9f9; border-radius:4px;">
				<h3>Add Method to this Zone</h3>
				<form method="post" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:10px; align-items:end;">
					<?php wp_nonce_field( 'ah_save_shipping' ); ?>
					<input type="hidden" name="sub_action" value="add_method">
					<input type="hidden" name="zone_id" value="<?php echo esc_attr( $zone->id ); ?>">
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Method Name</label>
						<input type="text" name="method_title" required class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Type</label>
						<select name="method_type" style="width:100%;">
							<option value="flat_rate">Flat Rate</option>
							<option value="free_shipping">Free Shipping</option>
							<option value="local_pickup">Local Pickup</option>
							<option value="weight_based">Weight Based</option>
						</select>
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Cost ($)</label>
						<input type="number" step="0.01" name="cost" value="0" class="regular-text" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:bold; font-size:12px;">Min Order ($)</label>
						<input type="number" step="0.01" name="min_order" value="0" class="regular-text" style="width:100%;">
					</div>
					<button type="submit" class="button button-primary">Add</button>
				</form>
			</div>
		</div>
	<?php endforeach; ?>

	<!-- Add Zone Form -->
	<div style="background:#fff; padding:20px; border:2px dashed #ccd0d4; border-radius:4px; text-align:center;">
		<form method="post" style="display:inline-flex; gap:10px; align-items:end;">
			<?php wp_nonce_field( 'ah_save_shipping' ); ?>
			<input type="hidden" name="sub_action" value="create_zone">
			<div>
				<label style="display:block; font-weight:bold; font-size:12px;">Zone Name *</label>
				<input type="text" name="zone_name" required class="regular-text" placeholder="e.g. United States">
			</div>
			<div>
				<label style="display:block; font-weight:bold; font-size:12px;">Regions (comma-separated)</label>
				<input type="text" name="regions" class="regular-text" placeholder="e.g. US, CA, UK">
			</div>
			<button type="submit" class="button button-primary">Create Zone</button>
		</form>
	</div>
</div>
