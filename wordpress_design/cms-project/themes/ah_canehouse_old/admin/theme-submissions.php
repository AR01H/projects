<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

global $wpdb;

CH_Schema::create_all();

$table_exists = $wpdb->get_var( $wpdb->prepare(
	'SHOW TABLES LIKE %s', $wpdb->prefix . 'ch_contact_submissions'
) ) === $wpdb->prefix . 'ch_contact_submissions';

$statuses = class_exists( 'CH_Contact_Data' ) ? CH_Contact_Data::statuses() : [];

// ── Route: detail view ────────────────────────────────────────────────────────
$view = sanitize_key( $_GET['view'] ?? 'list' );
$sub_id = absint( $_GET['sub_id'] ?? 0 );

if ( $view === 'detail' && $sub_id && $table_exists ) {
	$sub = class_exists( 'CH_Contact_Data' ) ? CH_Contact_Data::get_by_id( $sub_id ) : null;
	if ( ! $sub ) {
		echo '<div class="wrap ch-admin-wrap"><div class="ch-notice ch-notice--warning">Submission #' . esc_html( $sub_id ) . ' not found.</div></div>';
		return;
	}

	// Auto-mark as read on first open
	$current_user = wp_get_current_user();
	$admin_id     = (int) $current_user->ID;
	$admin_name   = sanitize_text_field( $current_user->display_name ?: $current_user->user_login );
	if ( class_exists( 'CH_Contact_Data' ) ) CH_Contact_Data::mark_read( $sub_id, $admin_id, $admin_name );

	// ── Handle POST ───────────────────────────────────────────────────────────
	$saved_msg = '';
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ch_contact_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ch_contact_nonce'] ) ), 'ch_contact_action_' . $sub_id ) ) {
			$saved_msg = '<div class="ch-notice ch-notice--warning">Security check failed.</div>';
		} else {
			$action_type = sanitize_key( $_POST['ch_contact_action'] ?? '' );

			if ( $action_type === 'update_status' ) {
				$new_status = sanitize_key( $_POST['new_status'] ?? '' );
				if ( array_key_exists( $new_status, $statuses ) ) {
					CH_Contact_Data::update_status( $sub_id, $new_status, $admin_id, $admin_name );
					$saved_msg = '<div class="ch-notice ch-notice--success">✅ Status updated to <strong>' . esc_html( $statuses[ $new_status ]['label'] ) . '</strong>.</div>';
					$sub = CH_Contact_Data::get_by_id( $sub_id );
				}
			} elseif ( $action_type === 'add_note' ) {
				$note = sanitize_textarea_field( wp_unslash( $_POST['admin_note'] ?? '' ) );
				if ( $note ) {
					CH_Contact_Data::add_admin_note( $sub_id, $note, $admin_id, $admin_name );
					$saved_msg = '<div class="ch-notice ch-notice--success">✅ Note added.</div>';
					$sub = CH_Contact_Data::get_by_id( $sub_id );
				}
			}
		}
	}

	$logs        = class_exists( 'CH_Contact_Data' ) ? CH_Contact_Data::get_logs( $sub_id ) : [];
	$status_info = $statuses[ $sub->status ?? 'new' ] ?? [ 'label' => $sub->status ?? 'new', 'color' => '#666' ];
	$list_url    = admin_url( 'admin.php?page=ch-theme-submissions' );
	?>
	<div class="wrap ch-admin-wrap">
		<h1>
			📥 Enquiry #<?php echo esc_html( $sub_id ); ?>
			<a href="<?php echo esc_url( $list_url ); ?>" class="page-title-action">← Back to list</a>
		</h1>

		<?php echo $saved_msg; // already escaped above ?>

		<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

			<div>
				<!-- Submission details -->
				<div class="ch-card">
					<h2>Enquiry Details</h2>
					<table class="ch-detail-table">
						<tr><th>Name</th><td><?php echo esc_html( $sub->name ?? '' ); ?></td></tr>
						<tr><th>Email</th><td><a href="mailto:<?php echo esc_attr( $sub->email ?? '' ); ?>"><?php echo esc_html( $sub->email ?? '' ); ?></a></td></tr>
						<tr><th>Phone</th><td><?php echo esc_html( $sub->phone ?: '-' ); ?></td></tr>
						<tr><th>Enquiry Type</th><td>
							<span class="ch-badge ch-badge--green"><?php echo esc_html( $sub->enquiry_type ?? 'general' ); ?></span>
						</td></tr>
						<tr><th>Message</th><td style="white-space:pre-wrap"><?php echo esc_html( $sub->message ?? '' ); ?></td></tr>
						<tr><th>Submitted</th><td><?php echo esc_html( $sub->created_at ?? '' ); ?></td></tr>
						<tr><th>IP Address</th><td><?php echo esc_html( $sub->ip_address ?: '-' ); ?></td></tr>
					</table>
				</div>

				<!-- Quick reply link -->
				<?php if ( ! empty( $sub->email ) ) : ?>
				<div class="ch-card" style="padding:1rem 1.5rem;">
					<a href="mailto:<?php echo esc_attr( $sub->email ); ?>?subject=Re: Your Enquiry - The Cane House"
						class="button button-primary">
						✉️ Reply by Email
					</a>
				</div>
				<?php endif; ?>

				<!-- Admin notes -->
				<div class="ch-card">
					<h2>Admin Notes</h2>
					<?php if ( ! empty( $sub->admin_notes ) ) : ?>
						<pre style="white-space:pre-wrap;font-family:inherit;font-size:.85rem;background:#f9f9f9;padding:.8rem;border-radius:4px;border:1px solid #e0e0e0;"><?php echo esc_html( $sub->admin_notes ); ?></pre>
					<?php else : ?>
						<p style="color:#999;">No notes yet.</p>
					<?php endif; ?>
					<form method="post" style="margin-top:1rem;">
						<?php wp_nonce_field( 'ch_contact_action_' . $sub_id, 'ch_contact_nonce' ); ?>
						<input type="hidden" name="ch_contact_action" value="add_note">
						<textarea name="admin_note" rows="3" class="large-text" placeholder="Add an internal note…"></textarea>
						<p><button type="submit" class="button button-secondary">Add Note</button></p>
					</form>
				</div>

				<!-- Activity log -->
				<div class="ch-card">
					<h2>Activity Log</h2>
					<?php if ( $logs ) : ?>
						<table class="widefat striped">
							<thead><tr><th>Date</th><th>Action</th><th>By</th><th>From</th><th>To</th></tr></thead>
							<tbody>
								<?php foreach ( $logs as $log ) : ?>
									<tr>
										<td style="white-space:nowrap;"><?php echo esc_html( $log->created_at ); ?></td>
										<td><?php echo esc_html( str_replace( '_', ' ', ucfirst( $log->action ?? '' ) ) ); ?></td>
										<td><?php echo esc_html( $log->admin_user_name ?: 'Visitor' ); ?></td>
										<td><?php echo esc_html( $log->old_value ?: '-' ); ?></td>
										<td><?php echo esc_html( $log->new_value ?: '-' ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p style="color:#999;">No activity recorded yet.</p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Status panel -->
			<div>
				<div class="ch-card" style="position:sticky;top:32px;">
					<h2>Status</h2>
					<p>
						<span class="ch-badge" style="background:<?php echo esc_attr( $status_info['color'] ); ?>22;color:<?php echo esc_attr( $status_info['color'] ); ?>;font-size:.9rem;padding:.35rem 1rem;">
							<?php echo esc_html( $status_info['label'] ); ?>
						</span>
					</p>
					<form method="post" style="margin-top:1rem;">
						<?php wp_nonce_field( 'ch_contact_action_' . $sub_id, 'ch_contact_nonce' ); ?>
						<input type="hidden" name="ch_contact_action" value="update_status">
						<label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem;">Change status to:</label>
						<select name="new_status" style="width:100%;padding:.5rem;border:1px solid #ddd;border-radius:4px;margin-bottom:.8rem;">
							<?php foreach ( $statuses as $slug => $info ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $sub->status ?? 'new', $slug ); ?>>
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
	<style>
	.ch-detail-table { width:100%; border-collapse:collapse; }
	.ch-detail-table th { width:160px; text-align:left; font-weight:600; font-size:.85rem; color:#555; padding:.5rem .5rem .5rem 0; vertical-align:top; }
	.ch-detail-table td { padding:.5rem 0; font-size:.9rem; border-bottom:1px solid #f0f0f0; }
	</style>
	<?php
	return;
}

// ── LIST VIEW ─────────────────────────────────────────────────────────────────

$filter_status = sanitize_key( $_GET['status'] ?? '' );
$search        = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$per_page      = 25;
$current_page  = max( 1, absint( $_GET['paged'] ?? 1 ) );
$offset        = ( $current_page - 1 ) * $per_page;

$stats       = ( $table_exists && class_exists( 'CH_Contact_Data' ) ) ? CH_Contact_Data::count_by_status() : [];
$total_all   = array_sum( $stats );
$total       = 0;
$submissions = [];

if ( $table_exists && class_exists( 'CH_Contact_Data' ) ) {
	$query_args  = [ 'status' => $filter_status, 'search' => $search, 'limit' => $per_page, 'offset' => $offset ];
	$submissions = CH_Contact_Data::get_all( $query_args );
	$total       = CH_Contact_Data::count( $query_args );
}

$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

function ch_contact_list_url( array $extra = [] ): string {
	return add_query_arg( array_merge( [ 'page' => 'ch-theme-submissions' ], $extra ), admin_url( 'admin.php' ) );
}
?>
<div class="wrap ch-admin-wrap">
	<h1>📥 Enquiry Submissions</h1>

	<?php if ( ! $table_exists ) : ?>
		<div class="ch-notice ch-notice--warning">
			⚠️ The submissions table doesn't exist yet.
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ch-theme-mock' ) ); ?>">Run the seeder</a> to create it.
		</div>
	<?php else : ?>

	<!-- Stats -->
	<div class="ch-stat-grid" style="margin-bottom:1.5rem;">
		<div class="ch-stat"><div class="ch-stat__num"><?php echo esc_html( $total_all ); ?></div><div class="ch-stat__label">Total</div></div>
		<?php foreach ( $statuses as $slug => $info ) : ?>
			<div class="ch-stat" style="background:<?php echo esc_attr( $info['color'] ); ?>;">
				<div class="ch-stat__num"><?php echo esc_html( $stats[ $slug ] ?? 0 ); ?></div>
				<div class="ch-stat__label"><?php echo esc_html( $info['label'] ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Filters -->
	<div class="ch-card" style="padding:1rem 1.5rem;">
		<div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
			<div style="display:flex;gap:.4rem;flex-wrap:wrap;">
				<a href="<?php echo esc_url( ch_contact_list_url() ); ?>"
					class="button<?php echo ! $filter_status ? ' button-primary' : ''; ?>">
					All (<?php echo esc_html( $total_all ); ?>)
				</a>
				<?php foreach ( $statuses as $slug => $info ) :
					$count = $stats[ $slug ] ?? 0;
				?>
					<a href="<?php echo esc_url( ch_contact_list_url( [ 'status' => $slug ] ) ); ?>"
						class="button<?php echo $filter_status === $slug ? ' button-primary' : ''; ?>"
						style="<?php echo $slug === 'new' && $count > 0 ? 'font-weight:700;' : ''; ?>">
						<?php echo esc_html( $info['label'] ); ?> (<?php echo esc_html( $count ); ?>)
						<?php if ( $slug === 'new' && $count > 0 ) : ?>
							<span style="display:inline-block;background:#d63638;color:#fff;border-radius:10px;padding:0 6px;font-size:.75rem;margin-left:3px;"><?php echo esc_html( $count ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
			<form method="get" style="margin-left:auto;">
				<input type="hidden" name="page" value="ch-theme-submissions">
				<?php if ( $filter_status ) : ?>
					<input type="hidden" name="status" value="<?php echo esc_attr( $filter_status ); ?>">
				<?php endif; ?>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
					placeholder="Search name, email, message…" style="width:240px;padding:.4rem .6rem;border:1px solid #ddd;border-radius:4px;">
				<button type="submit" class="button">Search</button>
				<?php if ( $search ) : ?>
					<a href="<?php echo esc_url( ch_contact_list_url( $filter_status ? [ 'status' => $filter_status ] : [] ) ); ?>" class="button">Clear</a>
				<?php endif; ?>
			</form>
		</div>
	</div>

	<!-- Table -->
	<div class="ch-card" style="padding:0;overflow:hidden;">
		<?php if ( empty( $submissions ) ) : ?>
			<p style="padding:1.5rem;color:#666;">
				<?php echo $search ? 'No submissions matching your search.' : 'No enquiries received yet.'; ?>
			</p>
		<?php else : ?>
			<table class="widefat striped" style="border:0;">
				<thead>
					<tr>
						<th style="width:50px;">#</th>
						<th>Date</th>
						<th>Name</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Type</th>
						<th>Message</th>
						<th>Status</th>
						<th style="width:80px;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $submissions as $row ) :
						$s_info     = $statuses[ $row->status ?? 'new' ] ?? [ 'label' => $row->status ?? 'new', 'color' => '#666' ];
						$detail_url = ch_contact_list_url( [ 'view' => 'detail', 'sub_id' => $row->id ] );
						$is_new     = ( ( $row->status ?? 'new' ) === 'new' );
					?>
						<tr style="<?php echo $is_new ? 'font-weight:600;' : ''; ?>">
							<td><?php echo esc_html( $row->id ); ?></td>
							<td style="white-space:nowrap;font-size:.82rem;"><?php echo esc_html( substr( $row->created_at, 0, 16 ) ); ?></td>
							<td><?php echo esc_html( $row->name ); ?></td>
							<td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
							<td><?php echo esc_html( $row->phone ?: '-' ); ?></td>
							<td><span class="ch-badge ch-badge--green" style="font-size:.75rem;"><?php echo esc_html( $row->enquiry_type ?? 'general' ); ?></span></td>
							<td style="max-width:280px;word-break:break-word;font-size:.82rem;color:#555;">
								<?php echo esc_html( mb_strimwidth( $row->message ?? '', 0, 120, '…' ) ); ?>
							</td>
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
						(<?php echo esc_html( $total ); ?> submissions)
					</span>
					<?php if ( $current_page > 1 ) : ?>
						<a href="<?php echo esc_url( ch_contact_list_url( array_filter( [ 'status' => $filter_status, 's' => $search, 'paged' => $current_page - 1 ] ) ) ); ?>" class="button">← Prev</a>
					<?php endif; ?>
					<?php if ( $current_page < $total_pages ) : ?>
						<a href="<?php echo esc_url( ch_contact_list_url( array_filter( [ 'status' => $filter_status, 's' => $search, 'paged' => $current_page + 1 ] ) ) ); ?>" class="button">Next →</a>
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
