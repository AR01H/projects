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
	header( 'Content-Disposition: attachment; filename="notification-subscribers-' . date( 'Y-m-d' ) . '.csv"' );
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
	AH_Newsletter::unsubscribe( sanitize_email( wp_unslash( $_GET['unsub'] ) ) );
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
		$notice = 'warning:Could not add - check the email address.';
	}
}

// ── Handle: send notification ────────────────────────────────────────────────
if ( isset( $_POST['ah_nl_send_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_nl_send_nonce'], 'ah_nl_send' ) ) wp_die( 'Security.' );
	$subject    = sanitize_text_field( wp_unslash( isset( $_POST['nl_subject'] )    ? $_POST['nl_subject']    : '' ) );
	$body       = wp_unslash( isset( $_POST['nl_body'] ) ? $_POST['nl_body'] : '' );
	$from_name  = sanitize_text_field( wp_unslash( isset( $_POST['nl_from_name'] )  ? $_POST['nl_from_name'] : '' ) );
	$from_email = sanitize_email( wp_unslash( isset( $_POST['nl_from_email'] )      ? $_POST['nl_from_email']: '' ) );
	if ( $subject || $body || ! empty( $_POST['nl_workflow_rule'] ) ) {
		if ( ! class_exists( 'AH_Workflow_Manager' ) ) {
			$notice = 'warning:Rules Engine is not available - notification could not be sent.';
		} else {
			// Parse custom variables
			$custom_vars_arr = array();
			$custom_vars_raw = isset( $_POST['nl_custom_vars'] ) ? wp_unslash( $_POST['nl_custom_vars'] ) : '';
			if ( ! empty( $custom_vars_raw ) ) {
				$lines = explode( "\n", str_replace( "\r", "", $custom_vars_raw ) );
				foreach ( $lines as $line ) {
					$parts = explode( '|', $line, 2 );
					if ( count( $parts ) === 2 ) {
						$c_key = sanitize_key( trim( $parts[0] ) );
						if ( ! empty( $c_key ) ) {
							$custom_vars_arr[ $c_key ] = sanitize_text_field( trim( $parts[1] ) );
						}
					}
				}
			}

			$extra_args = array(
				'rule_id'       => isset( $_POST['nl_workflow_rule'] ) ? (int) $_POST['nl_workflow_rule'] : 0,
				'custom_vars'   => $custom_vars_arr,
				'delivery_mode' => isset( $_POST['nl_delivery_mode'] ) ? sanitize_key( $_POST['nl_delivery_mode'] ) : 'individual',
			);

			$target_type = isset( $_POST['nl_target_type'] ) ? sanitize_key( $_POST['nl_target_type'] ) : 'all';
			if ( 'test' === $target_type ) {
				$extra_args['test_email'] = sanitize_email( wp_unslash( $_POST['nl_test_email'] ?? '' ) );
				if ( ! is_email( $extra_args['test_email'] ) ) {
					$notice = 'warning:Please enter a valid test recipient email address.';
					$subject = ''; // prevent sending below
				}
			}

			if ( empty( $notice ) ) {
				$result = AH_Newsletter::send_broadcast( $subject, $body, $from_name, $from_email, $extra_args );
				// Log the broadcast using the subject, falling back to rule name if subject is empty
				$log_subject = $subject ?: 'Workflow Rule ID #' . $extra_args['rule_id'];
				AH_Newsletter::log_broadcast( $log_subject, $result['sent'], $result['failed'] );
				$notice = 'success:Notification queued for ' . $result['sent'] . ' subscriber(s) via Rules Engine.';
			}
		}
		$active_tab = 'send';
	} else {
		$notice     = 'warning:Subject, Message Body, or Workflow Rule is required.';
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
$re_url      = admin_url( 'admin.php?page=ah-workflow-manager' );
$page_slug   = 'ah-newsletter';
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
.nl-send-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.nl-send-grid .ah-form-row{margin:0}
.nl-body-wrap{margin-bottom:16px}
.nl-body-wrap textarea{width:100%;min-height:220px;font-size:13.5px;padding:10px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-family:inherit;resize:vertical;box-sizing:border-box;line-height:1.6}
.nl-body-wrap textarea:focus{outline:none;border-color:#2563eb}
.nl-token-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px}
.nl-token{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:5px;padding:3px 10px;font-family:monospace;font-size:12px;color:#334155;cursor:pointer;user-select:all}
.nl-token:hover{background:#dbeafe;border-color:#2563eb}
.nl-re-info{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#166534}
.nl-re-info a{color:#166534;font-weight:600}
.nl-hist-row td{font-size:13px}
.nl-hist-empty{text-align:center;padding:40px;color:var(--ah-muted)}
</style>

<div class="wrap ah-wrap">

  <?php if ( $notice ) : list( $nt, $nm ) = explode( ':', $notice, 2 ); ?>
    <div class="ah-notice ah-notice-<?php echo 'success' === $nt ? 'success' : 'warning'; ?>"><?php echo esc_html( $nm ); ?></div>
  <?php endif; ?>

  <div class="nl-header">
    <h1><span class="dashicons dashicons-bell"></span> Notifications</h1>
    <?php if ( 'subscribers' === $active_tab ) : ?>
    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => $page_slug, 'export_csv' => 1 ), admin_url( 'admin.php' ) ), 'ah_nl_export' ) ); ?>" class="ah-btn ah-btn-secondary">Export CSV</a>
    <button class="ah-btn ah-btn-primary" id="nl-add-btn">+ Add Subscriber</button>
    <?php endif; ?>
  </div>

  <div class="nl-tab-nav">
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => $page_slug, 'tab' => 'subscribers' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'subscribers' === $active_tab ? 'on' : ''; ?>">
      Subscribers <span style="background:#e5e7eb;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700"><?php echo esc_html( $count_all ); ?></span>
    </a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => $page_slug, 'tab' => 'send' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'send' === $active_tab ? 'on' : ''; ?>">
      🔔 Send Notification
    </a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => $page_slug, 'tab' => 'history' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'history' === $active_tab ? 'on' : ''; ?>">
      History <span style="background:#e5e7eb;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700"><?php echo esc_html( count( $bcast_log ) ); ?></span>
    </a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => $page_slug, 'tab' => 'variables' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'variables' === $active_tab ? 'on' : ''; ?>">
      📋 Variables Reference
    </a>
  </div>

  <?php if ( 'subscribers' === $active_tab ) : ?>
  <!-- ═══════════════════════ SUBSCRIBERS ═══════════════════════ -->

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

  <div class="nl-stats">
    <div class="nl-stat-box"><div class="nl-stat-num"><?php echo esc_html( $count_all ); ?></div><div class="nl-stat-lbl">Total</div></div>
    <div class="nl-stat-box"><div class="nl-stat-num" style="color:#16a34a"><?php echo esc_html( $count_act ); ?></div><div class="nl-stat-lbl">Active</div></div>
    <div class="nl-stat-box"><div class="nl-stat-num" style="color:#dc2626"><?php echo esc_html( $count_uns ); ?></div><div class="nl-stat-lbl">Unsubscribed</div></div>
  </div>

  <div class="nl-filter-bar">
    <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Show:</span>
    <?php
    $pill_opts = array( '' => 'All (' . $count_all . ')', 'active' => 'Active (' . $count_act . ')', 'unsubscribed' => 'Unsubscribed (' . $count_uns . ')' );
    foreach ( $pill_opts as $pv => $pl ) :
      $url = add_query_arg( array( 'page' => $page_slug, 'tab' => 'subscribers', 'filter' => $pv, 'paged' => 1 ), admin_url( 'admin.php' ) );
    ?>
    <a href="<?php echo esc_url( $url ); ?>" class="nl-filter-pill <?php echo $filter === $pv ? 'on' : ''; ?>"><?php echo esc_html( $pl ); ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ( $rows ) : ?>
  <div class="ah-table-wrap">
    <table class="ah-table">
      <thead>
        <tr>
          <th>#</th><th>Email</th><th>Name</th><th>Source</th><th>Status</th><th>Subscribed</th><th>Unsubscribed</th><th></th>
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
          <td><small><?php echo $row['unsubscribed_at'] ? esc_html( wp_date( 'M j, Y', strtotime( $row['unsubscribed_at'] ) ) ) : '<span style="color:var(--ah-muted)">-</span>'; ?></small></td>
          <td style="white-space:nowrap">
            <?php if ( $is_active ) : ?>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => $page_slug, 'tab' => 'subscribers', 'unsub' => $row['email'], 'filter' => $filter ), admin_url( 'admin.php' ) ), 'ah_nl_unsub' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" onclick="return confirm('Mark as unsubscribed?')">Unsubscribe</a>
            <?php endif; ?>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => $page_slug, 'tab' => 'subscribers', 'del_sub' => $row['id'], 'filter' => $filter ), admin_url( 'admin.php' ) ), 'ah_nl_del' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Permanently delete this subscriber?')">Delete</a>
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
      $pu = add_query_arg( array( 'page' => $page_slug, 'tab' => 'subscribers', 'filter' => $filter, 'paged' => $p ), admin_url( 'admin.php' ) );
    ?>
    <a href="<?php echo esc_url( $pu ); ?>" class="ah-btn ah-btn-sm <?php echo $p === $paged ? 'ah-btn-primary' : 'ah-btn-secondary'; ?>"><?php echo esc_html( $p ); ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

  <?php else : ?>
    <div class="ah-card" style="text-align:center;padding:48px;color:var(--ah-muted)">
      <p style="font-size:1.1rem;margin:0">No subscribers yet<?php echo $filter ? ' with status <strong>' . esc_html( $filter ) . '</strong>' : ''; ?>.</p>
      <p style="margin:8px 0 0;font-size:13px">Use the notification signup widget on your site, or add subscribers manually above.</p>
    </div>
  <?php endif; ?>

  <?php elseif ( 'send' === $active_tab ) : ?>
  <!-- ═══════════════════════ SEND NOTIFICATION ═══════════════════════ -->
  <div class="ah-card">
    <div class="ah-card-header"><h2>Compose &amp; Send Notification</h2></div>

    <?php if ( $count_act < 1 ) : ?>
      <div class="ah-notice ah-notice-warning" style="margin:0">No active subscribers yet - add some first.</div>
    <?php elseif ( ! class_exists( 'AH_Workflow_Manager' ) ) : ?>
      <div class="ah-notice ah-notice-warning" style="margin:0">Rules Engine is not available. Notifications require it to send.</div>
    <?php else : ?>

    <div class="nl-re-info">
      🔔 Sending fires the <strong>Notification – Send</strong> trigger in the <a href="<?php echo esc_url( $re_url ); ?>">Rules Engine</a> once per subscriber.
      Create a rule with that trigger to choose how to deliver it - <strong>Email, WhatsApp, Webhook</strong>, or any combination.
      Available tokens in your rule actions: <code>{email}</code> <code>{name}</code> <code>{subject}</code> <code>{body}</code> <code>{from_name}</code> <code>{from_email}</code> <code>{unsubscribe_url}</code>
    </div>

    <form method="post" id="nl-send-form">
      <?php wp_nonce_field( 'ah_nl_send', 'ah_nl_send_nonce' ); ?>

      <div class="nl-send-grid" style="margin-bottom:16px; border-bottom: 1px solid #e5e7eb; padding-bottom:16px;">
        <div class="ah-form-row">
          <label>Target Recipients</label>
          <div style="display:flex;gap:20px;margin-top:8px;">
            <label style="font-weight:normal;cursor:pointer;"><input type="radio" name="nl_target_type" value="all" checked id="target-all"> 👥 All Active Subscribers (<?php echo esc_html( $count_act ); ?>)</label>
            <label style="font-weight:normal;cursor:pointer;"><input type="radio" name="nl_target_type" value="test" id="target-test"> 📧 Test Email Only</label>
          </div>
        </div>
        <div class="ah-form-row" id="delivery-mode-wrapper">
          <label>Delivery Mode</label>
          <div style="display:flex;gap:20px;margin-top:8px;">
            <label style="font-weight:normal;cursor:pointer;"><input type="radio" name="nl_delivery_mode" value="individual" checked> 📩 Individual Emails (Personalized per subscriber)</label>
            <label style="font-weight:normal;cursor:pointer;"><input type="radio" name="nl_delivery_mode" value="bcc"> ✉️ Single Group Email (All subscribers in BCC)</label>
          </div>
        </div>
        <div class="ah-form-row" id="test-email-wrapper" style="display:none;">
          <label>Test Recipient Email *</label>
          <input type="email" name="nl_test_email" placeholder="e.g. you@example.com" style="width:100%;">
        </div>
      </div>

      <div class="nl-send-grid" style="margin-bottom:16px;">
        <div class="ah-form-row">
          <label>Trigger Target Rule (Rules Engine)</label>
          <?php
          $all_rules = class_exists( 'AH_Workflow_Manager' ) ? AH_Workflow_Manager::get_all() : array();
          $active_rules = array();
          foreach ( $all_rules as $rule ) {
            if ( 'active' === $rule->status ) {
              $active_rules[] = $rule;
            }
          }
          ?>
          <select name="nl_workflow_rule" style="width:100%;">
            <option value="0">Default (Run all active rules matching trigger 'notification_send')</option>
            <?php foreach ( $active_rules as $ar ) : ?>
              <option value="<?php echo (int) $ar->id; ?>"><?php echo esc_html( $ar->name . ' (ID: ' . $ar->id . ' - Trigger: ' . $ar->trigger_name . ')' ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="ah-form-row" style="margin-bottom:16px">
        <label>Subject</label>
        <input type="text" name="nl_subject" placeholder="e.g. Your update from <?php echo esc_attr( defined( 'COMPANY_NAME' ) ? COMPANY_NAME : 'Your Company' ); ?>" style="font-size:15px;padding:10px 14px">
      </div>

      <div class="ah-form-row" style="margin-bottom:16px;">
        <label>Extra Custom Variables (One per line: <code>key|value</code>)</label>
        <textarea name="nl_custom_vars" style="font-family:monospace;height:80px;" placeholder="discount|20% Off&#10;date|July 31st"></textarea>
        <p class="description" style="margin-top:5px;font-size:12px;color:#6b7280;">Use tokens like <code>{discount}</code> or <code>{date}</code> inside the email subject or body. They will be replaced dynamically before sending.</p>
      </div>

      <div class="nl-body-wrap">
        <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px">Message Body</label>
        <div style="margin-bottom:8px;font-size:12px;color:#6b7280">Tokens replaced before passing to Rules Engine - click to insert:</div>
        <div class="nl-token-bar">
          <span class="nl-token" title="Subscriber name">{name}</span>
          <span class="nl-token" title="Unsubscribe link URL">{unsubscribe_url}</span>
          <span class="nl-token" title="Subscriber email">{email}</span>
        </div>
        <textarea name="nl_body" placeholder="Hi {name},&#10;&#10;Here's your update...&#10;&#10;To unsubscribe: {unsubscribe_url}"></textarea>
        <div style="font-size:12px;color:#6b7280;margin-top:6px">Body is passed as <code>{body}</code> token to the Rules Engine rule. An unsubscribe line is also appended automatically.</div>
      </div>

      <div style="display:flex;align-items:center;gap:14px;margin-top:20px">
        <button type="submit" class="ah-btn ah-btn-primary" style="font-size:15px;padding:10px 28px" onclick="return confirm('Confirm sending this notification via Rules Engine now?')">
          Send Notification →
        </button>
        <span style="font-size:12px;color:#6b7280">This action cannot be undone.</span>
      </div>
    </form>

    <?php endif; ?>
  </div>

  <?php elseif ( 'history' === $active_tab ) : ?>
  <!-- ═══════════════════════ HISTORY ═══════════════════════ -->
  <div class="ah-card">
    <div class="ah-card-header"><h2>Send History</h2><span style="font-size:13px;color:var(--ah-muted)">Last 50 sends</span></div>
    <?php if ( $bcast_log ) : ?>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th style="width:120px;text-align:center">Subscribers</th>
            <th style="width:160px">Date &amp; Time</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $bcast_log as $entry ) : ?>
          <tr class="nl-hist-row">
            <td><?php echo esc_html( $entry['subject'] ); ?></td>
            <td style="text-align:center"><span style="color:#16a34a;font-weight:600"><?php echo esc_html( $entry['sent'] ); ?></span></td>
            <td><small><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $entry['sent_at'] ) ) ); ?></small></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else : ?>
      <div class="nl-hist-empty">No notifications sent yet.</div>
    <?php endif; ?>
  </div>

  <?php endif; ?>

  <?php if ( 'variables' === $active_tab ) : ?>
  <!-- ═══════════════════════ VARIABLES REFERENCE ═══════════════════════ -->
  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px 30px;">
    <h2 style="margin:0 0 6px;font-size:17px;">📋 Variables Reference</h2>
    <p style="color:#6b7280;font-size:13px;margin:0 0 24px;">These variables are available inside your <strong>Workflow Manager</strong> rule actions when triggered by the News Letter sender. Use the <code>{{variable}}</code> syntax in any action field.</p>

    <!-- INDIVIDUAL MODE -->
    <h3 style="font-size:14px;font-weight:700;color:#1f2937;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:14px;">📩 Individual Mode — Available per subscriber</h3>
    <table class="ah-table" style="margin-bottom:28px;">
      <thead>
        <tr><th style="width:220px">Variable</th><th>Description</th><th>Example Value</th></tr>
      </thead>
      <tbody>
        <tr><td><code>{{email}}</code></td><td>The subscriber's email address</td><td><code>alice@example.com</code></td></tr>
        <tr><td><code>{{name}}</code></td><td>The subscriber's name</td><td><code>Alice</code></td></tr>
        <tr><td><code>{{unsubscribe_url}}</code></td><td>Personalized unsubscribe link for this subscriber</td><td><code>https://site.com/?unsub=abc123</code></td></tr>
        <tr><td><code>{{newsletter_subject}}</code></td><td>Subject you typed in the Compose form</td><td><code>July Newsletter</code></td></tr>
        <tr><td><code>{{newsletter_body}}</code></td><td>Message body you typed in the Compose form (tokens already replaced)</td><td><code>Hi Alice, here's your update...</code></td></tr>
        <tr><td><code>{{subject}}</code></td><td>Shorthand for <code>newsletter_subject</code></td><td><code>July Newsletter</code></td></tr>
        <tr><td><code>{{body}}</code></td><td>Shorthand for <code>newsletter_body</code></td><td><code>Hi Alice, here's your update...</code></td></tr>
        <tr><td><code>{{from_name}}</code></td><td>Sender name override (if set in compose form)</td><td><code><?php echo esc_html( get_bloginfo( 'name' ) ); ?></code></td></tr>
        <tr><td><code>{{from_email}}</code></td><td>Sender email override (if set in compose form)</td><td><code><?php echo esc_html( get_option( 'admin_email' ) ); ?></code></td></tr>
        <tr><td><code>{{your_custom_key}}</code></td><td>Any key you added in "Extra Custom Variables" box</td><td><code>20% Off</code></td></tr>
      </tbody>
    </table>

    <!-- BCC / GROUP MODE -->
    <h3 style="font-size:14px;font-weight:700;color:#1f2937;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:14px;">✉️ BCC Group Mode — Available in the single group trigger</h3>
    <table class="ah-table" style="margin-bottom:28px;">
      <thead>
        <tr><th style="width:220px">Variable</th><th>Description</th><th>Example Value</th></tr>
      </thead>
      <tbody>
        <tr><td><code>{{subscriber_emails}}</code></td><td>All active subscriber emails, comma-separated</td><td><code>alice@.., bob@.., carol@..</code></td></tr>
        <tr><td><code>{{subscriber_count}}</code></td><td>Total number of active subscribers being sent to</td><td><code>142</code></td></tr>
        <tr><td><code>{{subscriber_names}}</code></td><td>All subscriber names, comma-separated</td><td><code>Alice, Bob, Carol</code></td></tr>
        <tr><td><code>{{newsletter_subject}}</code></td><td>Subject you typed in the Compose form</td><td><code>July Newsletter</code></td></tr>
        <tr><td><code>{{newsletter_body}}</code></td><td>Message body you typed in the Compose form</td><td><code>Here's your update...</code></td></tr>
        <tr><td><code>{{subject}}</code></td><td>Shorthand for <code>newsletter_subject</code></td><td><code>July Newsletter</code></td></tr>
        <tr><td><code>{{body}}</code></td><td>Shorthand for <code>newsletter_body</code></td><td><code>Here's your update...</code></td></tr>
        <tr><td><code>{{email}}</code></td><td>The "TO" address (sender/admin email) used as the visible recipient</td><td><code>admin@site.com</code></td></tr>
        <tr><td><code>{{your_custom_key}}</code></td><td>Any key you added in "Extra Custom Variables" box</td><td><code>20% Off</code></td></tr>
      </tbody>
    </table>

    <!-- TIPS -->
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px 20px;font-size:13px;color:#166534;">
      <strong>💡 Tips:</strong>
      <ul style="margin:8px 0 0 18px;padding:0;line-height:1.9;">
        <li>Use <code>{{email}}</code> in the <strong>TO</strong> field of your Email Action for Individual Mode.</li>
        <li>Leave the <strong>BCC</strong> field empty in BCC Group Mode — it is filled automatically.</li>
        <li>Use <code>{{newsletter_subject}}</code> and <code>{{newsletter_body}}</code> in your Email Action to pass through what you typed in the Compose form.</li>
        <li>For <strong>HTTP Request / cURL / CODE</strong> actions, use <code>{{subscriber_emails}}</code> to get the full comma-separated list.</li>
        <li>Custom Variables typed in the form (e.g. <code>discount|20% Off</code>) become <code>{{discount}}</code> automatically.</li>
      </ul>
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
jQuery(function ($) {
  $('#nl-add-btn').on('click', function () {
    $('#nl-add-box').slideToggle(180);
  });
  $('.nl-token').on('click', function () {
    var token = $(this).text();
    var ta    = document.querySelector('textarea[name="nl_body"]');
    if (!ta) return;
    var s = ta.selectionStart, e = ta.selectionEnd;
    ta.value = ta.value.substring(0, s) + token + ta.value.substring(e);
    ta.selectionStart = ta.selectionEnd = s + token.length;
    ta.focus();
  });
  $('input[name="nl_target_type"]').on('change', function () {
    if ($('#target-test').is(':checked')) {
      $('#test-email-wrapper').show();
      $('#delivery-mode-wrapper').hide();
      $('input[name="nl_test_email"]').attr('required', true);
    } else {
      $('#test-email-wrapper').hide();
      $('#delivery-mode-wrapper').show();
      $('input[name="nl_test_email"]').removeAttr('required');
    }
  });
});
</script>
