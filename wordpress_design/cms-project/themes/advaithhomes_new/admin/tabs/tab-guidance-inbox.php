<?php
defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) { return; }

if ( ! class_exists( 'AH_Enquiry_Model' ) ) {
	echo '<div class="notice notice-warning"><p>Enquiry model not loaded.</p></div>';
	return;
}

AH_Enquiry_Model::install_table();

$status_options = array(
	'new'         => 'New',
	'in-progress' => 'In Progress',
	'replied'     => 'Replied',
	'closed'      => 'Closed',
);

$filter    = isset( $_GET['sf'] ) ? sanitize_key( wp_unslash( $_GET['sf'] ) ) : '';
$page_num  = max( 1, (int) ( isset( $_GET['pn'] ) ? $_GET['pn'] : 1 ) );
$per_page  = 20;
$offset    = ( $page_num - 1 ) * $per_page;

$rows      = AH_Enquiry_Model::get_list( 'guidance', $filter, $per_page, $offset );
$total     = AH_Enquiry_Model::count( 'guidance', $filter );
$all_count = AH_Enquiry_Model::count( 'guidance' );
$pages     = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

$base_url  = admin_url( 'admin.php?page=adn-theme-guidance-inbox' );

$badge_map = array(
	'new'         => '#2563eb',
	'in-progress' => '#d97706',
	'replied'     => '#16a34a',
	'closed'      => '#6b7280',
);

$enq_nonce = wp_create_nonce( 'ah_admin_nonce' );
?>

<ul class="subsubsub" style="margin-bottom:8px;">
	<li>
		<a href="<?php echo esc_url( $base_url ); ?>" <?php echo '' === $filter ? 'class="current"' : ''; ?>>
			All <span class="count">(<?php echo esc_html( $all_count ); ?>)</span>
		</a>
	</li>
	<?php foreach ( $status_options as $key => $label ) : ?>
	<li> |
		<a href="<?php echo esc_url( add_query_arg( 'sf', $key, $base_url ) ); ?>"
		   <?php echo $key === $filter ? 'class="current"' : ''; ?>>
			<?php echo esc_html( $label ); ?>
			<span class="count">(<?php echo esc_html( AH_Enquiry_Model::count( 'guidance', $key ) ); ?>)</span>
		</a>
	</li>
	<?php endforeach; ?>
</ul>

<table class="wp-list-table widefat fixed striped">
	<thead>
		<tr>
			<th style="width:48px">#</th>
			<th style="width:145px">Date</th>
			<th>Name</th>
			<th>Email</th>
			<th>Help With</th>
			<th style="width:80px">Region</th>
			<th style="width:110px">Status</th>
			<th style="width:100px">Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php if ( empty( $rows ) ) : ?>
		<tr><td colspan="8" style="text-align:center;padding:24px;color:#6b7280;">No submissions found.</td></tr>
	<?php else : ?>
		<?php foreach ( $rows as $row ) :
			$row_data   = json_decode( $row->data, true );
			$row_data   = is_array( $row_data ) ? $row_data : array();
			$badge_col  = $badge_map[ $row->sub_status ] ?? '#6b7280';
			$status_lbl = $status_options[ $row->sub_status ] ?? ucfirst( $row->sub_status );
			$row_id     = esc_attr( (string) $row->id );
		?>
		<tr>
			<td><?php echo esc_html( $row->id ); ?></td>
			<td><?php echo esc_html( date_i18n( 'd M Y H:i', strtotime( $row->created_at ) ) ); ?></td>
			<td><?php echo esc_html( $row->name ); ?></td>
			<td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
			<td><?php echo esc_html( $row->help_topic ?: '—' ); ?></td>
			<td><?php echo esc_html( ! empty( $row->region ) ? $row->region : '—' ); ?></td>
			<td>
				<span style="display:inline-block;padding:2px 9px;border-radius:12px;font-size:0.78rem;font-weight:600;background:<?php echo esc_attr( $badge_col ); ?>;color:#fff;">
					<?php echo esc_html( $status_lbl ); ?>
				</span>
			</td>
			<td>
				<button type="button" class="button button-small enq-toggle" data-row="<?php echo $row_id; ?>">View</button>
			</td>
		</tr>
		<tr class="enq-detail-row" id="enq-r-<?php echo $row_id; ?>" style="display:none;">
			<td colspan="8" style="background:#f0f6fc;padding:20px 24px;">
				<div style="display:grid;grid-template-columns:1fr 300px;gap:28px;align-items:start;">
					<div>
						<h4 style="margin:0 0 12px;font-size:0.85rem;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Submission Details</h4>
						<dl style="display:grid;grid-template-columns:160px 1fr;gap:6px 12px;margin:0;font-size:0.875rem;">
							<?php foreach ( $row_data as $dk => $dv ) :
								if ( is_array( $dv ) ) {
									$dv_str = implode( ', ', array_filter( array_map( 'strval', $dv ) ) );
								} else {
									$dv_str = (string) $dv;
								}
								if ( '' === $dv_str ) { continue; }
							?>
								<dt style="font-weight:600;color:#374151;"><?php echo esc_html( ucwords( str_replace( '_', ' ', $dk ) ) ); ?></dt>
								<dd style="margin:0;color:#111827;"><?php echo nl2br( esc_html( $dv_str ) ); ?></dd>
							<?php endforeach; ?>
							<dt style="font-weight:600;color:#374151;">IP Address</dt>
							<dd style="margin:0;color:#6b7280;"><?php echo esc_html( $row->ip_address ?: '—' ); ?></dd>
							<?php if ( ! empty( $row->region ) ) : ?>
							<dt style="font-weight:600;color:#374151;">Region</dt>
							<dd style="margin:0;color:#6b7280;"><?php echo esc_html( $row->region ); ?></dd>
							<?php endif; ?>
							<?php if ( ! empty( $row->user_agent ) ) : ?>
							<dt style="font-weight:600;color:#374151;">Browser</dt>
							<dd style="margin:0;color:#6b7280;font-size:0.8rem;word-break:break-all;"><?php echo esc_html( $row->user_agent ); ?></dd>
							<?php endif; ?>
						</dl>
					</div>
					<div>
						<h4 style="margin:0 0 12px;font-size:0.85rem;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Update Status</h4>
						<div class="enq-save-wrap" data-id="<?php echo $row_id; ?>">
							<label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Status</label>
							<select class="enq-status-sel" style="width:100%;margin-bottom:12px;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
								<?php foreach ( $status_options as $sk => $sl ) : ?>
									<option value="<?php echo esc_attr( $sk ); ?>"<?php selected( $row->sub_status, $sk ); ?>><?php echo esc_html( $sl ); ?></option>
								<?php endforeach; ?>
							</select>
							<label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Admin Notes</label>
							<textarea class="enq-notes-fld" rows="4"
								style="width:100%;margin-bottom:12px;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:0.875rem;resize:vertical;box-sizing:border-box;"><?php echo esc_textarea( $row->admin_notes ?? '' ); ?></textarea>
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
			$purl = add_query_arg( array( 'sf' => $filter, 'pn' => $p ), $base_url );
		?>
			<?php if ( $p === $page_num ) : ?>
				<span class="page-numbers current"><?php echo esc_html( $p ); ?></span>
			<?php else : ?>
				<a class="page-numbers" href="<?php echo esc_url( $purl ); ?>"><?php echo esc_html( $p ); ?></a>
			<?php endif; ?>
		<?php endfor; ?>
	</div>
</div>
<?php endif; ?>

<script>
( function () {
	var nonce = <?php echo wp_json_encode( $enq_nonce ); ?>;

	document.querySelectorAll( '.enq-toggle' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var row = document.getElementById( 'enq-r-' + btn.getAttribute( 'data-row' ) );
			if ( ! row ) { return; }
			var open = row.style.display !== 'none';
			row.style.display = open ? 'none' : 'table-row';
			btn.textContent   = open ? 'View' : 'Close';
		} );
	} );

	document.querySelectorAll( '.enq-save-btn' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var wrap   = btn.closest( '.enq-save-wrap' );
			var id     = wrap.getAttribute( 'data-id' );
			var status = wrap.querySelector( '.enq-status-sel' ).value;
			var notes  = wrap.querySelector( '.enq-notes-fld' ).value;
			var msgEl  = wrap.querySelector( '.enq-save-msg' );

			btn.disabled    = true;
			btn.textContent = 'Saving…';
			msgEl.style.display = 'none';

			var body = new URLSearchParams( {
				action:      'ah_update_enquiry',
				nonce:       nonce,
				enq_id:      id,
				sub_status:  status,
				admin_notes: notes,
			} );

			fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					btn.disabled        = false;
					btn.textContent     = 'Save';
					msgEl.style.display = 'inline';
					msgEl.style.color   = res.success ? '#16a34a' : '#dc2626';
					msgEl.textContent   = res.data ? res.data.message : ( res.success ? 'Saved.' : 'Error.' );
				} )
				.catch( function () {
					btn.disabled        = false;
					btn.textContent     = 'Save';
					msgEl.style.display = 'inline';
					msgEl.style.color   = '#dc2626';
					msgEl.textContent   = 'Request failed.';
				} );
		} );
	} );
} )();
</script>
