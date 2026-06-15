<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

AH_Enquiry_Model::install_table();

$status_options = array(
	'new'         => 'New',
	'in-progress' => 'In Progress',
	'replied'     => 'Replied',
	'closed'      => 'Closed',
);

$status_filter = sanitize_key( isset( $_GET['status_filter'] ) ? wp_unslash( $_GET['status_filter'] ) : '' );
$page_num      = max( 1, (int) ( isset( $_GET['page_num'] ) ? $_GET['page_num'] : 1 ) );
$per_page      = 20;
$offset        = ( $page_num - 1 ) * $per_page;

$rows  = AH_Enquiry_Model::get_list( 'contact', $status_filter, $per_page, $offset );
$total = AH_Enquiry_Model::count( 'contact', $status_filter );
$pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

$base_url = admin_url( 'admin.php?page=ah-contact' );
?>
<div class="wrap">
	<h1>Contact Submissions</h1>

	<?php
	// ── Status filter tabs ───────────────────────────────────────────────────
	$all_count = AH_Enquiry_Model::count( 'contact' );
	echo '<ul class="subsubsub">';
	$tab_url = add_query_arg( array( 'page' => 'ah-contact', 'status_filter' => '' ), admin_url( 'admin.php' ) );
	$active  = '' === $status_filter ? ' class="current"' : '';
	echo '<li><a href="' . esc_url( $tab_url ) . '"' . $active . '>All <span class="count">(' . esc_html( $all_count ) . ')</span></a> |</li>';
	$last_key = array_key_last( $status_options );
	foreach ( $status_options as $key => $label ) {
		$cnt     = AH_Enquiry_Model::count( 'contact', $key );
		$tab_url = add_query_arg( array( 'page' => 'ah-contact', 'status_filter' => $key ), admin_url( 'admin.php' ) );
		$active  = $status_filter === $key ? ' class="current"' : '';
		$sep     = $key !== $last_key ? ' |' : '';
		echo '<li><a href="' . esc_url( $tab_url ) . '"' . $active . '>' . esc_html( $label ) . ' <span class="count">(' . esc_html( $cnt ) . ')</span></a>' . $sep . '</li>';
	}
	echo '</ul>';
	?>

	<table class="wp-list-table widefat fixed striped" style="margin-top:16px;">
		<thead>
			<tr>
				<th style="width:48px">#</th>
				<th style="width:140px">Date</th>
				<th>Name</th>
				<th>Email</th>
				<th>Enquiry Type</th>
				<th style="width:110px">Status</th>
				<th style="width:110px">Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $rows ) ) : ?>
			<tr><td colspan="7" style="text-align:center;padding:24px;color:#6b7280;">No submissions found.</td></tr>
		<?php else : ?>
			<?php foreach ( $rows as $row ) :
				$row_data   = json_decode( $row->data, true );
				$row_data   = is_array( $row_data ) ? $row_data : array();
				$status_lbl = $status_options[ $row->sub_status ] ?? ucfirst( esc_html( $row->sub_status ) );
				$badge_map  = array(
					'new'         => '#2563eb',
					'in-progress' => '#d97706',
					'replied'     => '#16a34a',
					'closed'      => '#6b7280',
				);
				$badge_col  = $badge_map[ $row->sub_status ] ?? '#6b7280';
			?>
			<tr>
				<td><?php echo esc_html( $row->id ); ?></td>
				<td><?php echo esc_html( date_i18n( 'd M Y H:i', strtotime( $row->created_at ) ) ); ?></td>
				<td><?php echo esc_html( $row->name ); ?></td>
				<td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
				<td><?php echo esc_html( $row->help_topic ); ?></td>
				<td>
					<span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:0.78rem;font-weight:600;background:<?php echo esc_attr( $badge_col ); ?>;color:#fff;">
						<?php echo esc_html( $status_lbl ); ?>
					</span>
				</td>
				<td>
					<button type="button" class="button button-small enq-toggle-btn" data-row="<?php echo esc_attr( $row->id ); ?>">
						View / Edit
					</button>
				</td>
			</tr>
			<tr class="enq-detail-row" id="enq-detail-<?php echo esc_attr( $row->id ); ?>" style="display:none;">
				<td colspan="7" style="background:#f9fafb;padding:20px 24px;">
					<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
						<div>
							<h4 style="margin:0 0 12px;font-size:0.9rem;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Submission Details</h4>
							<dl style="display:grid;grid-template-columns:140px 1fr;gap:6px 12px;margin:0;font-size:0.875rem;">
								<?php foreach ( $row_data as $dk => $dv ) :
									if ( '' === (string) $dv ) continue;
									$dk_label = ucwords( str_replace( '_', ' ', $dk ) );
									$dv_str   = is_array( $dv ) ? implode( ', ', $dv ) : (string) $dv;
								?>
									<dt style="font-weight:600;color:#374151;"><?php echo esc_html( $dk_label ); ?></dt>
									<dd style="margin:0;color:#111827;"><?php echo nl2br( esc_html( $dv_str ) ); ?></dd>
								<?php endforeach; ?>
								<dt style="font-weight:600;color:#374151;">IP Address</dt>
								<dd style="margin:0;color:#6b7280;"><?php echo esc_html( $row->ip_address ?: '—' ); ?></dd>
							</dl>
						</div>
						<div>
							<h4 style="margin:0 0 12px;font-size:0.9rem;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Update Status</h4>
							<div class="enq-save-wrap" data-id="<?php echo esc_attr( $row->id ); ?>">
								<div style="margin-bottom:10px;">
									<label for="enq-status-<?php echo esc_attr( $row->id ); ?>" style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Status</label>
									<select id="enq-status-<?php echo esc_attr( $row->id ); ?>" class="enq-status-select" style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
										<?php foreach ( $status_options as $sk => $sl ) : ?>
											<option value="<?php echo esc_attr( $sk ); ?>"<?php selected( $row->sub_status, $sk ); ?>><?php echo esc_html( $sl ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div style="margin-bottom:10px;">
									<label for="enq-notes-<?php echo esc_attr( $row->id ); ?>" style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Admin Notes</label>
									<textarea id="enq-notes-<?php echo esc_attr( $row->id ); ?>" class="enq-notes-field" rows="4" style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:0.875rem;resize:vertical;box-sizing:border-box;"><?php echo esc_textarea( $row->admin_notes ); ?></textarea>
								</div>
								<button type="button" class="button button-primary enq-save-btn">Save</button>
								<span class="enq-save-msg" style="margin-left:10px;font-size:0.82rem;display:none;"></span>
							</div>
						</div>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $pages > 1 ) : ?>
	<div class="tablenav bottom" style="margin-top:12px;">
		<div class="tablenav-pages">
			<?php for ( $p = 1; $p <= $pages; $p++ ) :
				$purl = add_query_arg( array( 'page' => 'ah-contact', 'status_filter' => $status_filter, 'page_num' => $p ), admin_url( 'admin.php' ) );
				if ( $p === $page_num ) :
			?>
				<span class="page-numbers current"><?php echo esc_html( $p ); ?></span>
			<?php else : ?>
				<a class="page-numbers" href="<?php echo esc_url( $purl ); ?>"><?php echo esc_html( $p ); ?></a>
			<?php endif; endfor; ?>
		</div>
	</div>
	<?php endif; ?>
</div>

<script>
( function () {
	'use strict';

	// Toggle detail rows
	document.querySelectorAll( '.enq-toggle-btn' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var rowId  = btn.getAttribute( 'data-row' );
			var detail = document.getElementById( 'enq-detail-' + rowId );
			if ( detail ) {
				detail.style.display = detail.style.display === 'none' ? 'table-row' : 'none';
				btn.textContent = detail.style.display === 'none' ? 'View / Edit' : 'Close';
			}
		} );
	} );

	// Save enquiry meta via AJAX
	document.querySelectorAll( '.enq-save-btn' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var wrap   = btn.closest( '.enq-save-wrap' );
			var id     = wrap.getAttribute( 'data-id' );
			var status = wrap.querySelector( '.enq-status-select' ).value;
			var notes  = wrap.querySelector( '.enq-notes-field' ).value;
			var msgEl  = wrap.querySelector( '.enq-save-msg' );

			var vars = ( typeof window.ahAdmin !== 'undefined' ) ? window.ahAdmin : {};
			if ( ! vars.nonce ) { return; }

			btn.disabled    = true;
			btn.textContent = 'Saving…';
			msgEl.style.display = 'none';

			var body = new URLSearchParams( {
				action:     'ah_update_enquiry',
				nonce:      vars.nonce,
				enq_id:     id,
				sub_status: status,
				admin_notes: notes,
			} );

			fetch( vars.ajaxUrl || ajaxurl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					btn.disabled    = false;
					btn.textContent = 'Save';
					msgEl.style.display  = 'inline';
					msgEl.style.color    = res.success ? '#16a34a' : '#dc2626';
					msgEl.textContent    = res.data ? res.data.message : ( res.success ? 'Saved.' : 'Error.' );
				} )
				.catch( function () {
					btn.disabled    = false;
					btn.textContent = 'Save';
					msgEl.style.display = 'inline';
					msgEl.style.color   = '#dc2626';
					msgEl.textContent   = 'Request failed.';
				} );
		} );
	} );
} )();
</script>
