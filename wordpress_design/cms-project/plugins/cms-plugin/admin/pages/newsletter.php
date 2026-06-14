<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

AH_Newsletter::maybe_install();

$notice     = '';
$active_tab = sanitize_key( isset( $_GET['tab'] ) ? $_GET['tab'] : 'subscribers' );

// ── Handle: export CSV ───────────────────────────────────────────────────────
if ( isset( $_GET['export_csv'] ) && isset( $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_nl_export' ) ) wp_die( 'Security.' );
	$csv = AH_Newsletter::export_csv();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="newsletter-subscribers-' . date( 'Y-m-d' ) . '.csv"' );
	header( 'Pragma: no-cache' );
	echo $csv;
	exit;
}

// ── Handle: delete subscriber ────────────────────────────────────────────────
if ( isset( $_GET['del_sub'] ) && isset( $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_nl_del' ) ) wp_die( 'Security.' );
	AH_Newsletter::delete( (int) $_GET['del_sub'] );
	$notice = 'success:Subscriber removed.';
}

// ── Handle: unsubscribe ──────────────────────────────────────────────────────
if ( isset( $_GET['unsub'] ) && isset( $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_nl_unsub' ) ) wp_die( 'Security.' );
	$email = sanitize_email( wp_unslash( $_GET['unsub'] ) );
	AH_Newsletter::unsubscribe( $email );
	$notice = 'success:Subscriber marked as unsubscribed.';
}

// ── Handle: add subscriber ───────────────────────────────────────────────────
if ( isset( $_POST['ah_nl_add_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_nl_add_nonce'], 'ah_nl_add' ) ) wp_die( 'Security.' );
	$email  = sanitize_email( wp_unslash( isset( $_POST['nl_email'] ) ? $_POST['nl_email'] : '' ) );
	$name   = sanitize_text_field( wp_unslash( isset( $_POST['nl_name'] ) ? $_POST['nl_name'] : '' ) );
	$result = AH_Newsletter::subscribe( $email, $name, 'admin' );
	if ( 'subscribed' === $result ) {
		$notice = 'success:Subscriber added.';
	} elseif ( 'already_subscribed' === $result ) {
		$notice = 'warning:That email is already subscribed.';
	} else {
		$notice = 'warning:Could not add — check the email address.';
	}
}

// ── Handle: send broadcast ───────────────────────────────────────────────────
if ( isset( $_POST['ah_nl_send_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_nl_send_nonce'], 'ah_nl_send' ) ) wp_die( 'Security.' );
	$subject    = sanitize_text_field( wp_unslash( isset( $_POST['nl_subject'] )    ? $_POST['nl_subject']    : '' ) );
	$body       = sanitize_textarea_field( wp_unslash( isset( $_POST['nl_body'] )   ? $_POST['nl_body']      : '' ) );
	$from_name  = sanitize_text_field( wp_unslash( isset( $_POST['nl_from_name'] )  ? $_POST['nl_from_name'] : '' ) );
	$from_email = sanitize_email( wp_unslash( isset( $_POST['nl_from_email'] )      ? $_POST['nl_from_email']: '' ) );
	if ( $subject && $body ) {
		$result = AH_Newsletter::send_broadcast( $subject, $body, $from_name, $from_email );
		AH_Newsletter::log_broadcast( $subject, $result['sent'], $result['failed'] );
		$notice = 'success:Sent to ' . $result['sent'] . ' subscriber(s).' . ( $result['failed'] ? ' (' . $result['failed'] . ' failed)' : '' );
		$active_tab = 'send';
	} else {
		$notice     = 'warning:Subject and message body are required.';
		$active_tab = 'send';
	}
}

$filter      = isset( $_GET['filter'] ) ? sanitize_key( $_GET['filter'] ) : '';
$paged       = max( 1, (int) ( isset( $_GET['paged'] ) ? $_GET['paged'] : 1 ) );
$per_page    = 50;
$offset      = ( $paged - 1 ) * $per_page;
$total       = AH_Newsletter::count( $filter );
$rows        = AH_Newsletter::get_all( $filter, $per_page, $offset );
$count_all   = AH_Newsletter::count();
$count_act   = AH_Newsletter::count( 'active' );
$count_uns   = AH_Newsletter::count( 'unsubscribed' );
$total_pages = max( 1, (int) ceil( $total / $per_page ) );
$bcast_log   = AH_Newsletter::get_broadcast_log();
?>
<style>
.nl-header{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.nl-header h1{margin:0;flex:1}
.nl-tab-nav{display:flex;gap:2px;border-bottom:2px solid #e5e7eb;margin-bottom:24px}
.nl-tab-nav a{padding:10px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#6b7280;border-radius:6px 6px 0 0;border:1px solid transparent;border-bottom:none;margin-bottom:-2px}
.nl-tab-nav a.on{color:#2563eb;background:#fff;border-color:#e5e7eb;border-bottom-color:#fff}
.nl-tab-nav a:hover:not(.on){color:#1f2937;background:#f9fafb}
.nl-stats{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px}
.nl-stat-box{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 22px;min-width:130px;text-align:center}
.nl-stat-num{font-size:2rem;font-weight:700;line-height:1;color:#1f2937}
.nl-stat-lbl{font-size:12px;color:#6b7280;margin-top:4px;text-transform:uppercase;letter-spacing:.5px}
.nl-filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:18px}
.nl-filter-pill{padding:6px 14px;border-radius:20px;font-size:13px;font-weight:500;border:2px solid #e5e7eb;background:#fff;color:#6b7280;text-decoration:none;transition:all .15s}
.nl-filter-pill:hover{border-color:#2563eb;color:#2563eb}
.nl-filter-pill.on{background:#2563eb;color:#fff;border-color:#2563eb}
.nl-status-badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:11.5px;font-weight:600}
.nlsb-active{background:#d1fae5;color:#065f46}
.nlsb-unsubscribed{background:#fee2e2;color:#991b1b}
.nl-add-box{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 22px;margin-bottom:20px;display:none}
.nl-add-box h3{margin:0 0 12px;font-size:15px}
.nl-add-row{display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end}
.nl-add-row input{padding:8px 12px;border:1.5px solid #d1d5db;border-radius:6px;font-size:13px;width:100%;box-sizing:border-box}
/* Send form */
.nl-send-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.nl-send-grid .ah-form-row{margin:0}
.nl-body-wrap{margin-bottom:16px}
.nl-body-wrap textarea{width:100%;min-height:220px;font-size:13.5px;padding:10px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-family:inherit;resize:vertical;box-sizing:border-box;line-height:1.6}
.nl-body-wrap textarea:focus{outline:none;border-color:#2563eb}
.nl-token-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px}
.nl-token{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:5px;padding:3px 10px;font-family:monospace;font-size:12px;color:#334155;cursor:pointer;user-select:all}
.nl-token:hover{background:#dbeafe;border-color:#2563eb}
.nl-preview-box{background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:14px 18px;margin-top:16px;font-size:13px}
.nl-preview-box strong{color:#1f2937}
/* History */
.nl-hist-row td{font-size:13px}
.nl-hist-empty{text-align:center;padding:40px;color:var(--ah-muted)}
</style>

<div class="wrap ah-wrap">

  <?php if ( $notice ) : list( $nt, $nm ) = explode( ':', $notice, 2 ); ?>
    <div class="ah-notice ah-notice-<?php echo 'success' === $nt ? 'success' : 'warning'; ?>"><?php echo esc_html( $nm ); ?></div>
  <?php endif; ?>

  <div class="nl-header">
    <h1><span class="dashicons dashicons-email-alt"></span> Newsletter</h1>
    <?php if ( 'subscribers' === $active_tab ) : ?>
    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-newsletter', 'export_csv' => 1 ), admin_url( 'admin.php' ) ), 'ah_nl_export' ) ); ?>" class="ah-btn ah-btn-secondary">Export CSV</a>
    <button class="ah-btn ah-btn-primary" id="nl-add-btn">+ Add Subscriber</button>
    <?php endif; ?>
  </div>

  <!-- Tab nav -->
  <div class="nl-tab-nav">
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-newsletter', 'tab' => 'subscribers' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'subscribers' === $active_tab ? 'on' : ''; ?>">
      Subscribers <span style="background:#e5e7eb;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700"><?php echo esc_html( $count_all ); ?></span>
    </a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-newsletter', 'tab' => 'send' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'send' === $active_tab ? 'on' : ''; ?>">
      ✉ Send Newsletter
    </a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-newsletter', 'tab' => 'history' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'history' === $active_tab ? 'on' : ''; ?>">
      History <span style="background:#e5e7eb;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700"><?php echo esc_html( count( $bcast_log ) ); ?></span>
    </a>
  </div>

  <?php if ( 'subscribers' === $active_tab ) : ?>
  <!-- ═══════════════════════ SUBSCRIBERS ═══════════════════════ -->

  <!-- Add subscriber panel -->
  <div class="nl-add-box" id="nl-add-box">
    <h3>Add Subscriber Manually</h3>
    <form method="post">
      <?php wp_nonce_field( 'ah_nl_add', 'ah_nl_add_nonce' ); ?>
      <div class="nl-add-row">
        <div><label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Email *</label><input type="email" name="nl_email" required placeholder="email@example.com"></div>
        <div><label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px">Name</label><input type="text" name="nl_name" placeholder="Full name (optional)"></div>
        <div style="padding-bottom:1px"><button type="submit" class="ah-btn ah-btn-primary">Add</button></div>
      </div>
    </form>
  </div>

  <!-- Stats row -->
  <div class="nl-stats">
    <div class="nl-stat-box"><div class="nl-stat-num"><?php echo esc_html( $count_all ); ?></div><div class="nl-stat-lbl">Total</div></div>
    <div class="nl-stat-box"><div class="nl-stat-num" style="color:#16a34a"><?php echo esc_html( $count_act ); ?></div><div class="nl-stat-lbl">Active</div></div>
    <div class="nl-stat-box"><div class="nl-stat-num" style="color:#dc2626"><?php echo esc_html( $count_uns ); ?></div><div class="nl-stat-lbl">Unsubscribed</div></div>
  </div>

  <!-- Filter pills -->
  <div class="nl-filter-bar">
    <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Show:</span>
    <?php
    $pill_opts = array( '' => 'All (' . $count_all . ')', 'active' => 'Active (' . $count_act . ')', 'unsubscribed' => 'Unsubscribed (' . $count_uns . ')' );
    foreach ( $pill_opts as $pv => $pl ) :
      $url = add_query_arg( array( 'page' => 'ah-newsletter', 'tab' => 'subscribers', 'filter' => $pv, 'paged' => 1 ), admin_url( 'admin.php' ) );
    ?>
    <a href="<?php echo esc_url( $url ); ?>" class="nl-filter-pill <?php echo $filter === $pv ? 'on' : ''; ?>"><?php echo esc_html( $pl ); ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ( $rows ) : ?>
  <div class="ah-table-wrap">
    <table class="ah-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Email</th>
          <th>Name</th>
          <th>Source</th>
          <th>Status</th>
          <th>Subscribed</th>
          <th>Unsubscribed</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $rows as $row ) :
          $is_active = 'active' === $row['status'];
        ?>
        <tr>
          <td style="color:var(--ah-muted);font-size:12px"><?php echo esc_html( $row['id'] ); ?></td>
          <td><strong><?php echo esc_html( $row['email'] ); ?></strong></td>
          <td><?php echo esc_html( $row['name'] ); ?></td>
          <td><span style="background:#f3f4f6;border-radius:4px;padding:2px 8px;font-size:12px"><?php echo esc_html( $row['source'] ); ?></span></td>
          <td><span class="nl-status-badge nlsb-<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( ucfirst( $row['status'] ) ); ?></span></td>
          <td><small><?php echo esc_html( wp_date( 'M j, Y', strtotime( $row['created_at'] ) ) ); ?></small></td>
          <td><small><?php echo $row['unsubscribed_at'] ? esc_html( wp_date( 'M j, Y', strtotime( $row['unsubscribed_at'] ) ) ) : '<span style="color:var(--ah-muted)">—</span>'; ?></small></td>
          <td style="white-space:nowrap">
            <?php if ( $is_active ) : ?>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-newsletter', 'tab' => 'subscribers', 'unsub' => $row['email'], 'filter' => $filter ), admin_url( 'admin.php' ) ), 'ah_nl_unsub' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" onclick="return confirm('Mark as unsubscribed?')">Unsubscribe</a>
            <?php endif; ?>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-newsletter', 'tab' => 'subscribers', 'del_sub' => $row['id'], 'filter' => $filter ), admin_url( 'admin.php' ) ), 'ah_nl_del' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Permanently delete this subscriber?')">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ( $total_pages > 1 ) : ?>
  <div style="margin-top:16px;display:flex;gap:6px;align-items:center;flex-wrap:wrap">
    <span style="font-size:13px;color:#6b7280">Page <?php echo esc_html( $paged ); ?> of <?php echo esc_html( $total_pages ); ?> (<?php echo esc_html( $total ); ?> total)</span>
    <?php for ( $p = 1; $p <= $total_pages; $p++ ) :
      $pu = add_query_arg( array( 'page' => 'ah-newsletter', 'tab' => 'subscribers', 'filter' => $filter, 'paged' => $p ), admin_url( 'admin.php' ) );
    ?>
    <a href="<?php echo esc_url( $pu ); ?>" class="ah-btn ah-btn-sm <?php echo $p === $paged ? 'ah-btn-primary' : 'ah-btn-secondary'; ?>"><?php echo esc_html( $p ); ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

  <?php else : ?>
    <div class="ah-card" style="text-align:center;padding:48px;color:var(--ah-muted)">
      <p style="font-size:1.1rem;margin:0">No subscribers yet<?php echo $filter ? ' with status <strong>' . esc_html( $filter ) . '</strong>' : ''; ?>.</p>
      <p style="margin:8px 0 0;font-size:13px">Use the newsletter signup widget on your site, or add subscribers manually above.</p>
    </div>
  <?php endif; ?>

  <?php elseif ( 'send' === $active_tab ) : ?>
  <!-- ═══════════════════════ SEND NEWSLETTER ═══════════════════════ -->
  <div class="ah-card">
    <div class="ah-card-header"><h2>Compose &amp; Send</h2></div>

    <?php if ( $count_act < 1 ) : ?>
      <div class="ah-notice ah-notice-warning" style="margin:0">No active subscribers yet — add some first.</div>
    <?php else : ?>

    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13.5px;color:#1e40af">
      This will send to <strong><?php echo esc_html( $count_act ); ?> active subscriber(s)</strong>. Every email includes an automatic unsubscribe link at the bottom.
    </div>

    <form method="post" id="nl-send-form">
      <?php wp_nonce_field( 'ah_nl_send', 'ah_nl_send_nonce' ); ?>

      <div class="nl-send-grid">
        <div class="ah-form-row">
          <label>From Name</label>
          <input type="text" name="nl_from_name" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
        </div>
        <div class="ah-form-row">
          <label>From Email</label>
          <input type="email" name="nl_from_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
        </div>
      </div>

      <div class="ah-form-row" style="margin-bottom:16px">
        <label>Subject *</label>
        <input type="text" name="nl_subject" required placeholder="e.g. Your monthly update from Advaith Homes" style="font-size:15px;padding:10px 14px">
      </div>

      <div class="nl-body-wrap">
        <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px">Message Body *</label>
        <div style="margin-bottom:8px;font-size:12px;color:#6b7280">You can use these tokens — click to copy:</div>
        <div class="nl-token-bar">
          <span class="nl-token" title="Replaced with subscriber's first name">{name}</span>
          <span class="nl-token" title="Replaced with the unsubscribe link URL">{unsubscribe_url}</span>
        </div>
        <textarea name="nl_body" required placeholder="Hi {name},&#10;&#10;Here's your update...&#10;&#10;Best regards,&#10;The Advaith Homes Team"></textarea>
        <div style="font-size:12px;color:#6b7280;margin-top:6px">Plain text only. An unsubscribe line is automatically appended to every email.</div>
      </div>

      <div style="display:flex;align-items:center;gap:14px;margin-top:20px">
        <button type="submit" class="ah-btn ah-btn-primary" style="font-size:15px;padding:10px 28px" onclick="return confirm('Send this newsletter to <?php echo esc_js( $count_act ); ?> subscriber(s) now?')">
          Send to <?php echo esc_html( $count_act ); ?> Subscriber<?php echo $count_act !== 1 ? 's' : ''; ?> →
        </button>
        <span style="font-size:12px;color:#6b7280">This action cannot be undone.</span>
      </div>
    </form>

    <?php endif; ?>
  </div>

  <?php elseif ( 'history' === $active_tab ) : ?>
  <!-- ═══════════════════════ SEND HISTORY ═══════════════════════ -->
  <div class="ah-card">
    <div class="ah-card-header"><h2>Broadcast History</h2><span style="font-size:13px;color:var(--ah-muted)">Last 50 sends</span></div>
    <?php if ( $bcast_log ) : ?>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th style="width:100px;text-align:center">Sent</th>
            <th style="width:100px;text-align:center">Failed</th>
            <th style="width:160px">Date &amp; Time</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $bcast_log as $entry ) : ?>
          <tr class="nl-hist-row">
            <td><?php echo esc_html( $entry['subject'] ); ?></td>
            <td style="text-align:center"><span style="color:#16a34a;font-weight:600"><?php echo esc_html( $entry['sent'] ); ?></span></td>
            <td style="text-align:center"><?php echo $entry['failed'] > 0 ? '<span style="color:#dc2626;font-weight:600">' . esc_html( $entry['failed'] ) . '</span>' : '<span style="color:var(--ah-muted)">0</span>'; ?></td>
            <td><small><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $entry['sent_at'] ) ) ); ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else : ?>
      <div class="nl-hist-empty">No newsletters sent yet.</div>
    <?php endif; ?>
  </div>

  <?php endif; ?>

</div>

<script>
jQuery(function ($) {
  $('#nl-add-btn').on('click', function () {
    $('#nl-add-box').slideToggle(180);
  });

  // Click token to insert at cursor in textarea
  $('.nl-token').on('click', function () {
    var token = $(this).text();
    var ta    = document.querySelector('textarea[name="nl_body"]');
    if (!ta) return;
    var s = ta.selectionStart, e = ta.selectionEnd;
    var v = ta.value;
    ta.value = v.substring(0, s) + token + v.substring(e);
    ta.selectionStart = ta.selectionEnd = s + token.length;
    ta.focus();
  });
});
</script>
