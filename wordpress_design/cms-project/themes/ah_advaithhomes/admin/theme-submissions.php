<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table = $wpdb->prefix . 'ah_contact_form_submissions';

// Check table exists (requires the CMS plugin to have run at least once)
$table_exists = $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
	DB_NAME, $table
) );

if ( ! $table_exists ) : ?>
<div class="wrap ah-admin-wrap">
  <div class="ah-admin-header">
    <div class="ah-admin-logo">✉</div>
    <div><h1>Contact Submissions</h1></div>
  </div>
  <div class="ah-admin-box">
    <p>The contact submissions table does not exist yet. Make sure the CMS plugin is active and load any admin page once to create it.</p>
  </div>
</div>
<?php return; endif;

// ── Labels / helpers ──────────────────────────────────────────────────────────
$type_labels = [
	'general'   => 'General',
	'complaint' => 'Complaint',
	'sales'     => 'Sales',
	'support'   => 'Support',
	'media'     => 'Media/Press',
	'other'     => 'Other',
];
$type_colors = [
	'general'   => '#3b82f6',
	'complaint' => '#ef4444',
	'sales'     => '#22c55e',
	'support'   => '#f59e0b',
	'media'     => '#8b5cf6',
	'other'     => '#94a3b8',
];
$status_badge = [
	'new'         => 'background:#fef3c7;color:#92400e',
	'in_progress' => 'background:#dbeafe;color:#1e40af',
	'resolved'    => 'background:#dcfce7;color:#15803d',
	'spam'        => 'background:#fee2e2;color:#991b1b',
];

function ahts_type_badge( string $type, array $labels, array $colors ): string {
	$label = esc_html( $labels[ $type ] ?? ucfirst( $type ) );
	$color = esc_attr( $colors[ $type ] ?? '#94a3b8' );
	return "<span style='display:inline-block;padding:2px 9px;border-radius:4px;font-size:11px;font-weight:600;color:white;background:{$color}'>{$label}</span>";
}

// ── Actions ───────────────────────────────────────────────────────────────────
$notice  = '';
$view_id = (int) ( $_GET['id'] ?? 0 );
$sub     = null;

if ( isset( $_GET['mark_read'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ahts_mark_read' ) ) {
	$wpdb->update( $table, [ 'is_read' => 1 ], [ 'id' => (int) $_GET['mark_read'] ], [ '%d' ], [ '%d' ] );
	$notice = 'Marked as read.';
}

if ( isset( $_GET['mark_status'] ) && isset( $_GET['status'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ahts_mark_status' ) ) {
	$allowed_statuses = [ 'new', 'in_progress', 'resolved', 'spam' ];
	$new_status       = sanitize_key( $_GET['status'] );
	if ( in_array( $new_status, $allowed_statuses, true ) ) {
		$wpdb->update( $table, [ 'status' => $new_status ], [ 'id' => (int) $_GET['mark_status'] ], [ '%s' ], [ '%d' ] );
		$notice = 'Status updated.';
	}
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ahts_delete' ) ) {
	$wpdb->delete( $table, [ 'id' => (int) $_GET['delete_id'] ], [ '%d' ] );
	wp_safe_redirect( admin_url( 'admin.php?page=ah-theme-submissions&deleted=1' ) );
	exit;
}
if ( isset( $_GET['deleted'] ) ) $notice = 'Submission deleted.';

// Add admin note (inline POST)
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ahts_add_note'] ) && $view_id ) {
	if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'ahts_note_' . $view_id ) ) wp_die( 'Security check failed.' );
	$note_text = sanitize_textarea_field( $_POST['note_text'] ?? '' );
	if ( $note_text ) {
		$existing  = $wpdb->get_var( $wpdb->prepare( "SELECT admin_notes FROM `{$table}` WHERE id = %d", $view_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$timestamp = wp_date( 'M j, Y g:i a' );
		$user_name = wp_get_current_user()->display_name ?: 'Admin';
		$new_entry = "[{$timestamp} - {$user_name}] " . $note_text;
		$combined  = $new_entry . ( $existing ? "\n\n" . $existing : '' );
		$wpdb->update( $table, [ 'admin_notes' => $combined ], [ 'id' => $view_id ], [ '%s' ], [ '%d' ] );
		$notice = 'Note saved.';
	}
}

// Load single submission and auto-mark read
if ( $view_id ) {
	$sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $view_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	if ( $sub && ! $sub->is_read ) {
		$wpdb->update( $table, [ 'is_read' => 1 ], [ 'id' => $view_id ], [ '%d' ], [ '%d' ] );
	}
}

// Unread count for heading badge
$unread_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE is_read = 0 AND status = 'new'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
?>

<style>
.ahts-badge-status { display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600; }
.ahts-tbl { width:100%;border-collapse:collapse;font-size:13.5px; }
.ahts-tbl th { text-align:left;font-weight:600;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.06em;padding:10px 12px;border-bottom:1px solid #f1f5f9;background:#fafafa; }
.ahts-tbl td { padding:10px 12px;border-bottom:1px solid #f5f5f5;vertical-align:middle; }
.ahts-tbl tr:hover td { background:#fafafa; }
.ahts-tbl tr.is-unread td { font-weight:600; }
.ahts-filter-bar { display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:18px; }
.ahts-filter-bar input[type="search"],.ahts-filter-bar select { padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:13px; }
.ahts-filter-bar button { padding:8px 18px;background:#b7791f;color:white;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer; }
.ahts-filter-bar button:hover { background:#92400e; }
.ahts-action-btn { display:inline-block;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;cursor:pointer;border:1.5px solid #e2e8f0;color:#374151;background:white; }
.ahts-action-btn:hover { border-color:#b7791f;color:#b7791f; }
.ahts-action-btn--danger { border-color:#fca5a5;color:#dc2626;background:#fff; }
.ahts-action-btn--danger:hover { background:#fee2e2; }
.ahts-detail-row { display:grid;grid-template-columns:160px 1fr;gap:4px 12px;padding:7px 0;border-bottom:1px solid #f5f5f5; }
.ahts-detail-row:last-child { border-bottom:none; }
.ahts-detail-lbl { color:#94a3b8;font-size:13px; }
.ahts-detail-val { font-size:13px;color:#1e293b;word-break:break-word; }
.ahts-msg-box { background:#f8fafc;border-radius:8px;padding:16px 18px;white-space:pre-wrap;font-size:14px;line-height:1.7;border:1px solid #e2e8f0; }
.ahts-notes-box { background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;white-space:pre-wrap;font-size:13px;line-height:1.7; }
.ahts-note-form textarea { width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;resize:vertical;font-family:inherit; }
.ahts-note-form textarea:focus { outline:none;border-color:#b7791f; }
.ahts-pager { display:flex;gap:8px;align-items:center;margin-top:16px;font-size:13px; }
.ahts-pager a { padding:6px 12px;border:1.5px solid #e2e8f0;border-radius:6px;text-decoration:none;color:#374151; }
.ahts-pager a:hover { border-color:#b7791f;color:#b7791f; }
.ahts-pager .current-pg { padding:6px 12px;background:#b7791f;color:white;border-radius:6px;font-weight:700; }
</style>

<div class="wrap ah-admin-wrap">
  <div class="ah-admin-header">
    <div class="ah-admin-logo">✉</div>
    <div>
      <h1>Contact Submissions
        <?php if ( $unread_count ) : ?>
          <span style="display:inline-block;margin-left:8px;background:#dc2626;color:white;border-radius:20px;padding:2px 10px;font-size:13px;font-weight:700;vertical-align:middle"><?php echo $unread_count; ?> new</span>
        <?php endif; ?>
      </h1>
      <p>All enquiries submitted via the contact form.</p>
    </div>
  </div>

  <?php if ( $notice ) : ?>
    <div class="ah-admin-notice ah-admin-notice--success"><?php echo esc_html( $notice ); ?></div>
  <?php endif; ?>

<?php
// ══════════════════════════════════════════════════════════════════════════════
// SINGLE VIEW
// ══════════════════════════════════════════════════════════════════════════════
if ( $view_id && $sub ) :
	$sub_type   = $sub->enquiry_type ?? 'general';
	$sub_status = $sub->status ?? 'new';
	$st_style   = $status_badge[ $sub_status ] ?? 'background:#e5e7eb;color:#374151';
?>
  <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-theme-submissions' ) ); ?>" class="ahts-action-btn" style="margin-bottom:16px;display:inline-block;">&larr; Back to list</a>

  <div class="ah-admin-box" style="margin-bottom:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <?php echo ahts_type_badge( $sub_type, $type_labels, $type_colors ); ?>
        <h2 style="margin:0;font-size:1.1rem;">Message from <?php echo esc_html( $sub->full_name ); ?></h2>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach ( [ 'new', 'in_progress', 'resolved', 'spam' ] as $st ) : ?>
          <?php if ( $st !== $sub_status ) : ?>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'page' => 'ah-theme-submissions', 'id' => $sub->id, 'mark_status' => $sub->id, 'status' => $st ], admin_url( 'admin.php' ) ), 'ahts_mark_status' ) ); ?>" class="ahts-action-btn">
              Mark <?php echo esc_html( ucfirst( str_replace( '_', ' ', $st ) ) ); ?>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="ahts-detail-row"><span class="ahts-detail-lbl">Name</span><span class="ahts-detail-val" style="font-weight:600"><?php echo esc_html( $sub->full_name ); ?></span></div>
    <div class="ahts-detail-row"><span class="ahts-detail-lbl">Email</span><span class="ahts-detail-val"><a href="mailto:<?php echo esc_attr( $sub->email ); ?>"><?php echo esc_html( $sub->email ); ?></a></span></div>
    <div class="ahts-detail-row"><span class="ahts-detail-lbl">Phone</span><span class="ahts-detail-val"><?php echo esc_html( $sub->phone ?: '-' ); ?></span></div>
    <div class="ahts-detail-row"><span class="ahts-detail-lbl">Enquiry Type</span><span class="ahts-detail-val"><?php echo ahts_type_badge( $sub_type, $type_labels, $type_colors ); ?></span></div>
    <?php if ( ! empty( $sub->short_quote ) ) : ?>
    <div class="ahts-detail-row"><span class="ahts-detail-lbl">In a sentence</span><span class="ahts-detail-val" style="font-style:italic"><?php echo esc_html( $sub->short_quote ); ?></span></div>
    <?php endif; ?>
    <div class="ahts-detail-row"><span class="ahts-detail-lbl">Status</span><span class="ahts-detail-val"><span class="ahts-badge-status" style="<?php echo esc_attr( $st_style ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $sub_status ) ) ); ?></span></span></div>
    <div class="ahts-detail-row"><span class="ahts-detail-lbl">Submitted</span><span class="ahts-detail-val"><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $sub->submitted_at ) ) ); ?></span></div>
    <div class="ahts-detail-row">
      <span class="ahts-detail-lbl">Email Sent</span>
      <span class="ahts-detail-val">
        <?php if ( ! empty( $sub->email_sent ) ) : ?>
          <span style="color:#22c55e;font-weight:600">&#10003; Sent</span>
          <?php if ( ! empty( $sub->email_sent_at ) ) : ?>
            <span style="color:#94a3b8;font-size:12px;margin-left:6px"><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $sub->email_sent_at ) ) ); ?></span>
          <?php endif; ?>
        <?php else : ?>
          <span style="color:#ef4444;font-weight:600">&#10007; Not sent</span>
        <?php endif; ?>
      </span>
    </div>

    <!-- Message -->
    <div style="margin-top:18px">
      <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:6px">Message</div>
      <div class="ahts-msg-box"><?php echo esc_html( $sub->message ); ?></div>
    </div>

    <!-- Attachment -->
    <?php if ( ! empty( $sub->attachment_path ) ) : ?>
    <div style="margin-top:16px">
      <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:6px">Attachment</div>
      <a href="<?php echo esc_url( $sub->attachment_path ); ?>" target="_blank" rel="noopener" class="ahts-action-btn">
        &#128206; <?php echo esc_html( $sub->attachment_name ?: 'Download file' ); ?>
      </a>
    </div>
    <?php endif; ?>

    <!-- Technical details -->
    <?php if ( ! empty( $sub->page_url ) || ! empty( $sub->user_agent ) || ! empty( $sub->ip_address ) ) : ?>
    <details style="margin-top:20px;">
      <summary style="cursor:pointer;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;user-select:none;">Technical Details</summary>
      <div style="margin-top:10px;">
        <div class="ahts-detail-row"><span class="ahts-detail-lbl">IP Address</span><span class="ahts-detail-val" style="color:#94a3b8"><?php echo esc_html( $sub->ip_address ?: '-' ); ?></span></div>
        <div class="ahts-detail-row">
          <span class="ahts-detail-lbl">Page URL</span>
          <span class="ahts-detail-val">
            <?php echo $sub->page_url ? '<a href="' . esc_url( $sub->page_url ) . '" target="_blank" rel="noopener">' . esc_html( $sub->page_url ) . '</a>' : '-'; ?>
          </span>
        </div>
        <div class="ahts-detail-row"><span class="ahts-detail-lbl">User Agent</span><span class="ahts-detail-val" style="color:#94a3b8;font-size:12px"><?php echo esc_html( $sub->user_agent ?: '-' ); ?></span></div>
      </div>
    </details>
    <?php endif; ?>
  </div>

  <!-- Admin Notes -->
  <div class="ah-admin-box">
    <h2 style="font-size:1rem;font-weight:700;margin:0 0 14px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Admin Notes</h2>
    <?php if ( ! empty( $sub->admin_notes ) ) : ?>
      <div class="ahts-notes-box" style="margin-bottom:16px;"><?php echo esc_html( $sub->admin_notes ); ?></div>
    <?php else : ?>
      <p style="color:#94a3b8;font-size:13px;margin-bottom:16px;">No notes yet.</p>
    <?php endif; ?>
    <form method="post" class="ahts-note-form" action="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-theme-submissions', 'id' => $sub->id ], admin_url( 'admin.php' ) ) ); ?>">
      <?php wp_nonce_field( 'ahts_note_' . $sub->id ); ?>
      <input type="hidden" name="ahts_add_note" value="1">
      <div style="margin-bottom:10px;">
        <textarea name="note_text" rows="3" placeholder="<?php echo esc_attr( TXT_ADD_AN_INTERNAL_NOTE ); ?>" required></textarea>
      </div>
      <button type="submit" class="ahts-action-btn" style="background:#b7791f;color:white;border-color:#b7791f;">Add Note</button>
    </form>
  </div>

<?php
// ══════════════════════════════════════════════════════════════════════════════
// LIST VIEW
// ══════════════════════════════════════════════════════════════════════════════
else :
	$status_f  = sanitize_key( $_GET['status'] ?? '' );
	$type_f    = sanitize_key( $_GET['type']   ?? '' );
	$search    = sanitize_text_field( $_GET['s'] ?? '' );
	$paged     = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	$per_page  = 20;
	$offset    = ( $paged - 1 ) * $per_page;

	// Build WHERE
	$where_parts  = [];
	$where_values = [];
	if ( $status_f ) { $where_parts[] = 'status = %s';       $where_values[] = $status_f; }
	if ( $type_f )   { $where_parts[] = 'enquiry_type = %s'; $where_values[] = $type_f; }
	if ( $search ) {
		$like           = '%' . $wpdb->esc_like( $search ) . '%';
		$where_parts[]  = '(full_name LIKE %s OR email LIKE %s OR message LIKE %s OR short_quote LIKE %s)';
		$where_values   = array_merge( $where_values, [ $like, $like, $like, $like ] );
	}
	$where_sql = $where_parts ? 'WHERE ' . implode( ' AND ', $where_parts ) : '';

	// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	if ( $where_values ) {
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` {$where_sql}", ...$where_values ) );
		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` {$where_sql} ORDER BY submitted_at DESC LIMIT %d OFFSET %d", ...array_merge( $where_values, [ $per_page, $offset ] ) ) );
	} else {
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` ORDER BY submitted_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
	}
	// phpcs:enable

	$total_pages = $total ? (int) ceil( $total / $per_page ) : 1;
	$base_url    = admin_url( 'admin.php' );
?>
  <!-- Filter bar -->
  <form method="get" class="ahts-filter-bar">
    <input type="hidden" name="page" value="ah-theme-submissions">
    <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( TXT_SEARCH_NAME_EMAIL_MESSAGE ); ?>">
    <select name="status">
      <option value="">All Status</option>
      <?php foreach ( [ 'new', 'in_progress', 'resolved', 'spam' ] as $st ) : ?>
        <option value="<?php echo esc_attr( $st ); ?>" <?php selected( $status_f, $st ); ?>><?php echo esc_html( ucfirst( str_replace( '_', ' ', $st ) ) ); ?></option>
      <?php endforeach; ?>
    </select>
    <select name="type">
      <option value="">All Types</option>
      <?php foreach ( $type_labels as $tv => $tl ) : ?>
        <option value="<?php echo esc_attr( $tv ); ?>" <?php selected( $type_f, $tv ); ?>><?php echo esc_html( $tl ); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
    <?php if ( $search || $status_f || $type_f ) : ?>
      <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-theme-submissions' ], $base_url ) ); ?>" class="ahts-action-btn">Clear</a>
    <?php endif; ?>
  </form>

  <div class="ah-admin-box" style="padding:0;overflow:hidden;">
    <table class="ahts-tbl">
      <thead>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Email</th>
          <th>Status</th>
          <th style="text-align:center">Email Sent</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ( empty( $items ) ) : ?>
          <tr><td colspan="7" style="text-align:center;padding:36px;color:#94a3b8;">No submissions found.</td></tr>
        <?php endif; ?>
        <?php foreach ( $items as $sub ) :
          $s_type   = $sub->enquiry_type ?? 'general';
          $s_status = $sub->status ?? 'new';
          $ss_style = $status_badge[ $s_status ] ?? 'background:#e5e7eb;color:#374151';
        ?>
          <tr class="<?php echo ! $sub->is_read ? 'is-unread' : ''; ?>">
            <td>
              <?php echo esc_html( $sub->full_name ); ?>
              <?php if ( ! $sub->is_read ) : ?><span style="display:inline-block;background:#dc2626;color:white;border-radius:10px;font-size:10px;font-weight:700;padding:1px 6px;margin-left:4px;vertical-align:middle">New</span><?php endif; ?>
              <?php if ( ! empty( $sub->short_quote ) ) : ?>
                <br><span style="font-size:11px;color:#94a3b8;font-weight:400;font-style:italic"><?php echo esc_html( wp_trim_words( $sub->short_quote, 7 ) ); ?></span>
              <?php endif; ?>
            </td>
            <td><?php echo ahts_type_badge( $s_type, $type_labels, $type_colors ); ?></td>
            <td style="font-size:13px"><?php echo esc_html( $sub->email ); ?></td>
            <td><span class="ahts-badge-status" style="<?php echo esc_attr( $ss_style ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $s_status ) ) ); ?></span></td>
            <td style="text-align:center">
              <?php echo ! empty( $sub->email_sent )
                ? '<span style="color:#22c55e;font-size:16px" title="<?php echo esc_attr( TXT_EMAIL_SENT ); ?>">&#10003;</span>'
                : '<span style="color:#cbd5e1;font-size:16px" title="<?php echo esc_attr( TXT_NOT_SENT ); ?>">&#10007;</span>'; ?>
            </td>
            <td style="font-size:12px;color:#64748b;white-space:nowrap"><?php echo esc_html( wp_date( 'M j, Y', strtotime( $sub->submitted_at ) ) ); ?></td>
            <td style="white-space:nowrap">
              <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-theme-submissions', 'id' => $sub->id ], $base_url ) ); ?>" class="ahts-action-btn">View</a>
              <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'page' => 'ah-theme-submissions', 'delete_id' => $sub->id ], $base_url ), 'ahts_delete' ) ); ?>" class="ahts-action-btn ahts-action-btn--danger" onclick="return confirm('Delete this submission?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ( $total_pages > 1 ) : ?>
    <div class="ahts-pager">
      <span style="color:#64748b">Showing <?php echo esc_html( ( ( $paged - 1 ) * $per_page ) + 1 ); ?>-<?php echo esc_html( min( $paged * $per_page, $total ) ); ?> of <?php echo esc_html( $total ); ?></span>
      <?php if ( $paged > 1 ) : ?>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-theme-submissions', 'paged' => $paged - 1, 's' => $search, 'status' => $status_f, 'type' => $type_f ], $base_url ) ); ?>">&larr; Prev</a>
      <?php endif; ?>
      <span class="current-pg"><?php echo esc_html( $paged ); ?></span>
      <?php if ( $paged < $total_pages ) : ?>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-theme-submissions', 'paged' => $paged + 1, 's' => $search, 'status' => $status_f, 'type' => $type_f ], $base_url ) ); ?>">Next &rarr;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>
</div>
