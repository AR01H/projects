<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

global $wpdb;



$table_exists = $wpdb->get_var( $wpdb->prepare(
	'SHOW TABLES LIKE %s', $wpdb->prefix . 'ch_order_requests'
) ) === $wpdb->prefix . 'ch_order_requests';

$statuses = class_exists( 'CH_Order_Data' ) ? CH_Order_Data::statuses() : [];

// ── Route: detail view ────────────────────────────────────────────────────────
$view     = sanitize_key( $_GET['view'] ?? 'list' );
$order_id = absint( $_GET['order_id'] ?? 0 );

if ( $view === 'detail' && $order_id && $table_exists ) {
	$order = class_exists( 'CH_Order_Data' ) ? CH_Order_Data::get_by_id( $order_id ) : null;
	if ( ! $order ) {
		echo '<div class="wrap ch-admin-wrap"><div class="ch-notice ch-notice--warning">Order #' . esc_html( $order_id ) . ' not found.</div></div>';
		return;
	}

	// ── Handle POST: status change or note ───────────────────────────────────
	$saved_msg = '';
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ch_order_action_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ch_order_action_nonce'] ) ), 'ch_order_action_' . $order_id ) ) {
			$saved_msg = '<div class="ch-notice ch-notice--warning">Security check failed.</div>';
		} else {
			$current_user = wp_get_current_user();
			$admin_id     = (int) $current_user->ID;
			$admin_name   = sanitize_text_field( $current_user->display_name ?: $current_user->user_login );
			$action_type  = sanitize_key( $_POST['ch_order_action'] ?? '' );

			if ( $action_type === 'update_status' ) {
				$new_status = sanitize_key( $_POST['new_status'] ?? '' );
				if ( array_key_exists( $new_status, $statuses ) ) {
					CH_Order_Data::update_status( $order_id, $new_status, $admin_id, $admin_name );
					$saved_msg = '<div class="ch-notice ch-notice--success">✅ Status updated to <strong>' . esc_html( $statuses[ $new_status ]['label'] ) . '</strong>.</div>';
					$order = CH_Order_Data::get_by_id( $order_id ); // refresh
				}
			} elseif ( $action_type === 'add_note' ) {
				$note = sanitize_textarea_field( wp_unslash( $_POST['admin_note'] ?? '' ) );
				if ( $note ) {
					CH_Order_Data::add_admin_note( $order_id, $note, $admin_id, $admin_name );
					$saved_msg = '<div class="ch-notice ch-notice--success">✅ Note added.</div>';
					$order = CH_Order_Data::get_by_id( $order_id ); // refresh
				}
			}
		}
	}

	// ── Detail view ───────────────────────────────────────────────────────────
	$items        = json_decode( $order->items ?? '[]', true ) ?: [];
	$logs         = class_exists( 'CH_Order_Data' ) ? CH_Order_Data::get_activity_logs( $order_id ) : [];
	$status_info  = $statuses[ $order->status ] ?? [ 'label' => $order->status, 'color' => '#666' ];
	$list_url     = admin_url( 'admin.php?page=ch-order-requests' );
	?>
	<div class="wrap ch-admin-wrap">
		<h1>
			📋 Order Request #<?php echo esc_html( $order_id ); ?>
			<a href="<?php echo esc_url( $list_url ); ?>" class="page-title-action">← Back to list</a>
		</h1>

		<?php echo $saved_msg; // already escaped above ?>

		<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

			<!-- ── Left column: order info ─────────────────────────────────── -->
			<div>

				<!-- Customer & delivery -->
				<div class="ch-card">
					<h2>Customer &amp; Delivery</h2>
					<table class="ch-detail-table">
						<tr><th>Name</th><td><?php echo esc_html( $order->name ); ?></td></tr>
						<tr><th>Email</th><td><a href="mailto:<?php echo esc_attr( $order->email ); ?>"><?php echo esc_html( $order->email ); ?></a></td></tr>
						<tr><th>Phone</th><td><?php echo esc_html( $order->phone ?: '—' ); ?></td></tr>
						<tr><th>Delivery Address</th><td style="white-space:pre-wrap"><?php echo esc_html( $order->delivery_address ); ?></td></tr>
						<tr><th>Area / City</th><td><?php echo esc_html( $order->delivery_area ?: '—' ); ?></td></tr>
						<tr><th>Preferred Date</th><td><?php echo esc_html( $order->preferred_date ?: '—' ); ?></td></tr>
						<tr><th>Preferred Time</th><td><?php echo esc_html( $order->preferred_time ?: '—' ); ?></td></tr>
						<tr><th>Special Notes</th><td style="white-space:pre-wrap"><?php echo esc_html( $order->special_notes ?: '—' ); ?></td></tr>
						<tr><th>Submitted</th><td><?php echo esc_html( $order->created_at ); ?></td></tr>
						<tr><th>IP Address</th><td><?php echo esc_html( $order->ip_address ?: '—' ); ?></td></tr>
					</table>
				</div>

				<!-- Items ordered -->
				<div class="ch-card">
					<h2>Items Ordered</h2>
					<?php if ( $items ) : ?>
						<table class="widefat striped">
							<thead><tr><th>#</th><th>Item</th><th>Qty</th></tr></thead>
							<tbody>
								<?php foreach ( $items as $idx => $item ) : ?>
									<tr>
										<td><?php echo $idx + 1; ?></td>
										<td><?php echo esc_html( $item['name'] ?? '—' ); ?></td>
										<td><strong><?php echo esc_html( $item['qty'] ?? 1 ); ?></strong></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p>No item data recorded.</p>
					<?php endif; ?>
				</div>

				<!-- Admin notes -->
				<div class="ch-card">
					<h2>Admin Notes</h2>
					<?php if ( $order->admin_notes ) : ?>
						<pre style="white-space:pre-wrap;font-family:inherit;font-size:.85rem;background:#f9f9f9;padding:.8rem;border-radius:4px;border:1px solid #e0e0e0;"><?php echo esc_html( $order->admin_notes ); ?></pre>
					<?php else : ?>
						<p style="color:#999;">No notes yet.</p>
					<?php endif; ?>

					<form method="post" style="margin-top:1rem;">
						<?php wp_nonce_field( 'ch_order_action_' . $order_id, 'ch_order_action_nonce' ); ?>
						<input type="hidden" name="ch_order_action" value="add_note">
						<textarea name="admin_note" rows="3" class="large-text" placeholder="Add an internal note…"></textarea>
						<p><button type="submit" class="button button-secondary">Add Note</button></p>
					</form>
				</div>

				<!-- Activity log -->
				<div class="ch-card">
					<h2>Activity Log</h2>
					<?php if ( $logs ) : ?>
						<table class="widefat striped">
							<thead>
								<tr><th>Date</th><th>Action</th><th>By</th><th>From</th><th>To</th></tr>
							</thead>
							<tbody>
								<?php foreach ( $logs as $log ) :
									$action_label = str_replace( '_', ' ', ucfirst( $log->action ?? '' ) );
								?>
									<tr>
										<td style="white-space:nowrap;"><?php echo esc_html( $log->created_at ); ?></td>
										<td><?php echo esc_html( $action_label ); ?></td>
										<td><?php echo esc_html( $log->admin_user_name ?: 'Customer' ); ?></td>
										<td><?php echo esc_html( $log->old_value ?: '—' ); ?></td>
										<td><?php echo esc_html( $log->new_value ?: '—' ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p style="color:#999;">No activity recorded yet.</p>
					<?php endif; ?>
				</div>

			</div>

			<!-- ── Right column: status / actions ─────────────────────────── -->
			<div>
				<div class="ch-card" style="position:sticky;top:32px;">
					<h2>Status</h2>
					<p>
						<span class="ch-badge" style="background:<?php echo esc_attr( $status_info['color'] ); ?>22;color:<?php echo esc_attr( $status_info['color'] ); ?>;font-size:.9rem;padding:.35rem 1rem;">
							<?php echo esc_html( $status_info['label'] ); ?>
						</span>
					</p>

					<form method="post" style="margin-top:1rem;">
						<?php wp_nonce_field( 'ch_order_action_' . $order_id, 'ch_order_action_nonce' ); ?>
						<input type="hidden" name="ch_order_action" value="update_status">
						<div class="ch-row">
							<label style="min-width:auto;margin-bottom:.3rem;display:block;font-size:.85rem;font-weight:600;">Change status to:</label>
						</div>
						<select name="new_status" class="ch-row" style="width:100%;padding:.5rem;border:1px solid #ddd;border-radius:4px;margin-bottom:.8rem;">
							<?php foreach ( $statuses as $slug => $info ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"
									<?php selected( $order->status, $slug ); ?>>
									<?php echo esc_html( $info['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="button button-primary" style="width:100%;">Update Status</button>
					</form>
				</div>
			</div>

		</div>
	</div>
	<?php
	return;
}

// ── LIST VIEW ─────────────────────────────────────────────────────────────────

$filter_status = sanitize_key( $_GET['status'] ?? '' );
$search        = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$per_page      = 25;
$current_page  = max( 1, absint( $_GET['paged'] ?? 1 ) );
$offset        = ( $current_page - 1 ) * $per_page;

$stats   = ( $table_exists && class_exists( 'CH_Order_Data' ) ) ? CH_Order_Data::count_by_status() : [];
$total   = 0;
$orders  = [];

if ( $table_exists && class_exists( 'CH_Order_Data' ) ) {
	$query_args = [ 'status' => $filter_status, 'search' => $search, 'limit' => $per_page, 'offset' => $offset ];
	$orders     = CH_Order_Data::get_all( $query_args );
	$total      = CH_Order_Data::count( $query_args );
}

$total_all   = array_sum( $stats );
$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

// Build list URL helper
function ch_order_list_url( array $extra = [] ): string {
	return add_query_arg( array_merge( [ 'page' => 'ch-order-requests' ], $extra ), admin_url( 'admin.php' ) );
}
?>
<div class="wrap ch-admin-wrap">
	<h1>📦 Order Requests</h1>

	<?php if ( ! $table_exists ) : ?>
		<div class="ch-notice ch-notice--warning">
			⚠️ The orders table doesn't exist yet.
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ch-theme-mock' ) ); ?>">Run the seeder</a> to create it.
		</div>
	<?php else : ?>

	<!-- Stats row -->
	<div class="ch-stat-grid" style="margin-bottom:1.5rem;">
		<div class="ch-stat"><div class="ch-stat__num"><?php echo esc_html( $total_all ); ?></div><div class="ch-stat__label">Total</div></div>
		<?php foreach ( $statuses as $slug => $info ) : ?>
			<div class="ch-stat" style="background:<?php echo esc_attr( $info['color'] ); ?>;">
				<div class="ch-stat__num"><?php echo esc_html( $stats[ $slug ] ?? 0 ); ?></div>
				<div class="ch-stat__label"><?php echo esc_html( $info['label'] ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Filters: status tabs + search -->
	<div class="ch-card" style="padding:1rem 1.5rem;">
		<div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">

			<!-- Status tab filters -->
			<div style="display:flex;gap:.4rem;flex-wrap:wrap;">
				<a href="<?php echo esc_url( ch_order_list_url() ); ?>"
					class="button<?php echo ! $filter_status ? ' button-primary' : ''; ?>">
					All (<?php echo esc_html( $total_all ); ?>)
				</a>
				<?php foreach ( $statuses as $slug => $info ) : ?>
					<a href="<?php echo esc_url( ch_order_list_url( [ 'status' => $slug ] ) ); ?>"
						class="button<?php echo $filter_status === $slug ? ' button-primary' : ''; ?>">
						<?php echo esc_html( $info['label'] ); ?> (<?php echo esc_html( $stats[ $slug ] ?? 0 ); ?>)
					</a>
				<?php endforeach; ?>
			</div>

			<!-- Search -->
			<form method="get" style="margin-left:auto;">
				<input type="hidden" name="page" value="ch-order-requests">
				<?php if ( $filter_status ) : ?>
					<input type="hidden" name="status" value="<?php echo esc_attr( $filter_status ); ?>">
				<?php endif; ?>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
					placeholder="Search name, email, phone…" style="width:240px;padding:.4rem .6rem;border:1px solid #ddd;border-radius:4px;">
				<button type="submit" class="button">Search</button>
				<?php if ( $search ) : ?>
					<a href="<?php echo esc_url( ch_order_list_url( $filter_status ? [ 'status' => $filter_status ] : [] ) ); ?>" class="button">Clear</a>
				<?php endif; ?>
			</form>

		</div>
	</div>

	<!-- Orders table -->
	<div class="ch-card" style="padding:0;overflow:hidden;">
		<?php if ( empty( $orders ) ) : ?>
			<p style="padding:1.5rem;color:#666;">
				<?php echo $search ? 'No orders matching your search.' : 'No orders received yet.'; ?>
			</p>
		<?php else : ?>
			<table class="widefat striped" style="border:0;">
				<thead>
					<tr>
						<th style="width:50px;">#</th>
						<th>Date</th>
						<th>Customer</th>
						<th>Area</th>
						<th>Items</th>
						<th>Pref. Date</th>
						<th>Status</th>
						<th style="width:80px;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $orders as $row ) :
						$s_info     = $statuses[ $row->status ] ?? [ 'label' => $row->status, 'color' => '#666' ];
						$items_arr  = json_decode( $row->items ?? '[]', true ) ?: [];
						$items_text = implode( ', ', array_map( static fn( $i ) => ( $i['name'] ?? '' ) . ' ×' . ( $i['qty'] ?? 1 ), $items_arr ) );
						$detail_url = ch_order_list_url( [ 'view' => 'detail', 'order_id' => $row->id ] );
					?>
						<tr>
							<td><strong><?php echo esc_html( $row->id ); ?></strong></td>
							<td style="white-space:nowrap;"><?php echo esc_html( substr( $row->created_at, 0, 16 ) ); ?></td>
							<td>
								<strong><?php echo esc_html( $row->name ); ?></strong><br>
								<small><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></small>
								<?php if ( $row->phone ) : ?>
									<br><small><?php echo esc_html( $row->phone ); ?></small>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $row->delivery_area ?: '—' ); ?></td>
							<td style="max-width:220px;word-break:break-word;font-size:.82rem;"><?php echo esc_html( $items_text ?: '—' ); ?></td>
							<td><?php echo esc_html( $row->preferred_date ?: '—' ); ?></td>
							<td>
								<span class="ch-badge" style="background:<?php echo esc_attr( $s_info['color'] ); ?>22;color:<?php echo esc_attr( $s_info['color'] ); ?>;">
									<?php echo esc_html( $s_info['label'] ); ?>
								</span>
							</td>
							<td>
								<a href="<?php echo esc_url( $detail_url ); ?>" class="button button-small">View</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div style="padding:1rem 1.5rem;display:flex;gap:.5rem;align-items:center;">
					<span style="color:#666;font-size:.85rem;">
						Page <?php echo esc_html( $current_page ); ?> of <?php echo esc_html( $total_pages ); ?>
						(<?php echo esc_html( $total ); ?> orders)
					</span>
					<?php if ( $current_page > 1 ) : ?>
						<a href="<?php echo esc_url( ch_order_list_url( array_filter( [ 'status' => $filter_status, 's' => $search, 'paged' => $current_page - 1 ] ) ) ); ?>" class="button">← Prev</a>
					<?php endif; ?>
					<?php if ( $current_page < $total_pages ) : ?>
						<a href="<?php echo esc_url( ch_order_list_url( array_filter( [ 'status' => $filter_status, 's' => $search, 'paged' => $current_page + 1 ] ) ) ); ?>" class="button">Next →</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>
	</div>

	<?php endif; ?>
</div>

<style>
.ch-detail-table { width:100%; border-collapse:collapse; }
.ch-detail-table th { width:160px; text-align:left; font-weight:600; font-size:.85rem; color:#555; padding:.5rem .5rem .5rem 0; vertical-align:top; }
.ch-detail-table td { padding:.5rem 0; font-size:.9rem; border-bottom:1px solid #f0f0f0; }
</style>
