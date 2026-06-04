<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

AH_Rules_Engine::install_tables();

$notice  = '';
$rule_id = (int) ( $_GET['rule_id'] ?? 0 );
$view    = sanitize_key( $_GET['view'] ?? 'list' );

// ── Handle: save config ───────────────────────────────────────────────────────
if ( isset( $_POST['ah_re_cfg_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_re_cfg_nonce'], 'ah_save_re_config' ) ) wp_die( 'Security.' );
	AH_Rules_Engine::save_config( array(
		'email_from_name'    => $_POST['cfg_email_from_name']    ?? '',
		'email_from_email'   => $_POST['cfg_email_from_email']   ?? '',
		'email_bcc'          => $_POST['cfg_email_bcc']          ?? '',
		'wa_api_url'         => $_POST['cfg_wa_api_url']         ?? '',
		'wa_auth_token'      => $_POST['cfg_wa_auth_token']      ?? '',
		'retry_max_attempts' => $_POST['cfg_retry_max_attempts'] ?? '3',
		'cron_enabled'       => $_POST['cfg_cron_enabled']       ?? '0',
	) );
	$raw_vars     = json_decode( wp_unslash( $_POST['cfg_custom_vars_json'] ?? '[]' ), true ) ?: array();
	$raw_channels = json_decode( wp_unslash( $_POST['cfg_channels_json']    ?? '[]' ), true ) ?: array();
	AH_Rules_Engine::save_custom_vars( $raw_vars );
	AH_Rules_Engine::save_email_channels( $raw_channels );
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-rules-engine&view=config&notice=cfg_saved' ) );
}

// ── Handle: test fire trigger ─────────────────────────────────────────────────
if ( isset( $_POST['ah_re_test_fire_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_re_test_fire_nonce'], 'ah_test_fire' ) ) wp_die( 'Security.' );
	$test_trigger = sanitize_text_field( $_POST['test_trigger_name'] ?? '' );
	if ( $test_trigger ) {
		AH_Rules_Engine::evaluate( $test_trigger, array(
			'full_name' => 'Test User',
			'email'     => get_option( 'admin_email' ),
			'phone'     => '0000000000',
			'message'   => 'This is a test fire from the Rules Engine Config page.',
		) );
	}
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-rules-engine&view=config&notice=test_fired&tf=' . urlencode( $test_trigger ) ) );
}

// ── Handle: manual run now ────────────────────────────────────────────────────
if ( isset( $_POST['ah_re_run_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_re_run_nonce'], 'ah_run_now' ) ) wp_die( 'Security.' );
	AH_Rules_Engine::cron_process();
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-rules-engine&view=config&notice=run_now_ok' ) );
}

// ── Handle: delete log entry ──────────────────────────────────────────────────
if ( isset( $_GET['del_log'], $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_log' ) ) wp_die( 'Security.' );
	AH_Rules_Engine::delete_log( (int) $_GET['del_log'] );
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-rules-engine&view=logs&notice=log_deleted' ) );
}

// ── Handle: mark log unsent ───────────────────────────────────────────────────
if ( isset( $_GET['unsent_log'], $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_unsent_log' ) ) wp_die( 'Security.' );
	AH_Rules_Engine::mark_log_unsent( (int) $_GET['unsent_log'] );
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-rules-engine&view=logs&notice=log_unsent' ) );
}

// ── Handle: retry log entry ───────────────────────────────────────────────────
if ( isset( $_GET['retry_log'], $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_retry_log' ) ) wp_die( 'Security.' );
	$ok = AH_Rules_Engine::retry_log( (int) $_GET['retry_log'] );
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-rules-engine&view=logs&notice=' . ( $ok ? 'retry_ok' : 'retry_fail' ) ) );
}

// ── Handle delete ──────────────────────────────────────────────────────────────
if ( isset( $_GET['delete'], $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_rule' ) ) wp_die( 'Security.' );
	AH_Rules_Engine::delete( (int) $_GET['delete'] );
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-rules-engine&notice=deleted' ) );
}

// ── Handle save ────────────────────────────────────────────────────────────────
if ( isset( $_POST['ah_re_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_re_nonce'], 'ah_save_rule' ) ) wp_die( 'Security.' );

	$raw_conditions = json_decode( wp_unslash( $_POST['re_conditions_json'] ?? '[]' ), true ) ?: array();
	$raw_actions    = json_decode( wp_unslash( $_POST['re_actions_json']    ?? '[]' ), true ) ?: array();
	$raw_settings   = json_decode( wp_unslash( $_POST['re_settings_json']   ?? '{}' ), true ) ?: array();

	$saved_id = AH_Rules_Engine::save( $rule_id, array(
		'name'             => sanitize_text_field( $_POST['re_name']             ?? '' ),
		'trigger_name'     => sanitize_text_field( $_POST['re_trigger_name']     ?? 'form_submit' ),
		'conditions_match' => sanitize_key(        $_POST['re_conditions_match'] ?? 'all' ),
		'conditions'       => $raw_conditions,
		'actions'          => $raw_actions,
		'status'           => sanitize_key(        $_POST['re_status']           ?? 'active' ),
		'settings'         => $raw_settings,
	) );

	AH_Admin_Bootstrap::redirect( add_query_arg( array(
		'page'    => 'ah-rules-engine',
		'view'    => 'edit',
		'rule_id' => $saved_id,
		'notice'  => 'saved',
	), admin_url( 'admin.php' ) ) );
}

// ── Load data ──────────────────────────────────────────────────────────────────
if ( isset( $_GET['notice'] ) ) {
	$n_map = array(
		'saved'       => 'success:Rule saved.',
		'deleted'     => 'success:Rule deleted.',
		'cfg_saved'   => 'success:Configuration saved.',
		'log_deleted' => 'success:Log entry deleted.',
		'log_unsent'  => 'success:Marked as cancelled.',
		'retry_ok'    => 'success:Action retried successfully.',
		'retry_fail'  => 'warning:Retry failed - check the error column.',
		'run_now_ok'  => 'success:Cron batch processed - check Trigger Logs for updated statuses.',
		'test_fired'  => 'success:Test trigger fired - check Trigger Logs to confirm a new entry was created.',
	);
	$notice = $n_map[ sanitize_key( $_GET['notice'] ) ] ?? '';
}

$all_rules       = AH_Rules_Engine::get_all();
$trigger_presets = AH_Rules_Engine::trigger_presets();
$operators       = AH_Rules_Engine::operators();

$blank_rule = (object) array(
	'id' => 0, 'name' => '', 'trigger_name' => 'form_submit',
	'conditions_match' => 'all', 'conditions' => array(), 'actions' => array(), 'status' => 'active',
);

$editing = null;
if ( 'edit' === $view ) {
	$editing = $rule_id ? AH_Rules_Engine::get( $rule_id ) : $blank_rule;
	if ( ! $editing ) $editing = $blank_rule;
}
?>
<style>
.re-header{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px}
.re-header h1{margin:0;flex:1;font-size:1.4rem}
.re-tbl{border-collapse:collapse;width:100%}
.re-tbl th{font-size:11.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;padding:10px 12px;background:#f9fafb;border-bottom:1px solid #e5e7eb;white-space:nowrap}
.re-tbl td{padding:10px 12px;border-bottom:1px solid #f3f4f6;vertical-align:middle}
.re-tbl tr:hover td{background:#fafafa}
.re-section{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:20px 22px;margin-bottom:16px}
.re-section-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#6b7280;margin:0 0 14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.re-section-title>span{flex:1}
.re-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap}
.re-row input,.re-row select{padding:7px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:13px;font-family:inherit;background:#fff;box-sizing:border-box}
.re-row input:focus,.re-row select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.1)}
.re-act-card{border:1.5px solid #e5e7eb;border-radius:8px;padding:14px 16px;margin-bottom:10px}
.re-act-card[data-type="send_email"]{border-top:3px solid #3b82f6}
.re-act-card[data-type="whatsapp"]{border-top:3px solid #22c55e}
.re-act-card[data-type="http_request"]{border-top:3px solid #8b5cf6}
.re-act-card-head{display:flex;align-items:center;gap:8px;margin-bottom:12px;font-weight:600;font-size:14px}
.re-act-card-head .re-rm{margin-left:auto}
.re-field-group{display:flex;flex-direction:column;gap:4px;margin-bottom:10px}
.re-field-group:last-child{margin-bottom:0}
.re-field-group label{font-size:11.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px}
.re-field-group label small{text-transform:none;font-weight:400;letter-spacing:0;font-size:11px}
.re-field-group input,.re-field-group select,.re-field-group textarea{padding:7px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:13px;font-family:inherit;width:100%;box-sizing:border-box;background:#fff}
.re-field-group input:focus,.re-field-group select:focus,.re-field-group textarea:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.1)}
.re-field-group textarea{resize:vertical;min-height:60px}
.re-act-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.re-act-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.re-act-grid-7030{display:grid;grid-template-columns:70% calc(30% - 10px);gap:10px}
@media(max-width:900px){.re-act-grid-2,.re-act-grid-3,.re-act-grid-7030{grid-template-columns:1fr}}
.re-rm{background:none;border:none;color:#9ca3af;cursor:pointer;font-size:16px;padding:3px 5px;line-height:1;border-radius:4px}
.re-rm:hover{color:#ef4444;background:#fef2f2}
.re-preset-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;align-items:center}
.re-preset-chip{display:inline-block;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:3px 10px;font-size:11.5px;font-weight:500;color:#334155;cursor:pointer;transition:background .15s}
.re-preset-chip:hover{background:#dbeafe;border-color:#93c5fd;color:#1d4ed8}
.re-trigger-pill{display:inline-flex;align-items:center;gap:5px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:999px;padding:3px 10px;font-size:12px;font-weight:600}
.re-code-box{background:#1e293b;color:#e2e8f0;border-radius:8px;padding:12px 16px;font-size:12px;font-family:monospace;margin:0;overflow-x:auto;line-height:1.6}
.re-code-hl{color:#7dd3fc}
.re-st-active{color:#16a34a;font-weight:600;font-size:12px}
.re-st-inactive{color:#9ca3af;font-weight:500;font-size:12px}
.re-empty{text-align:center;padding:48px 24px;color:#9ca3af}
details.re-adv{border:1px solid #e5e7eb;border-radius:6px;margin-top:8px}
details.re-adv summary{padding:8px 12px;font-size:12px;font-weight:600;color:#6b7280;cursor:pointer;list-style:none;user-select:none}
details.re-adv summary::-webkit-details-marker{display:none}
details.re-adv[open] summary{border-bottom:1px solid #e5e7eb}
details.re-adv .re-adv-body{padding:12px}
.re-html-row{display:flex;align-items:center;gap:7px;font-size:13px;color:#374151;padding:4px 0;cursor:pointer}
.re-html-row input[type=checkbox]{width:15px;height:15px;cursor:pointer;margin:0}
/* Channel cards */
.re-ch-card{border:1.5px solid #e5e7eb;border-radius:8px;padding:14px 16px;margin-bottom:10px;background:#fff}
.re-ch-card-head{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.re-ch-card-head strong{font-size:13px;font-weight:600;color:#1e293b;flex:1}
.re-ch-id-badge{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:999px;padding:2px 8px;font-size:11px;font-weight:600;font-family:monospace}
/* Custom variable rows */
.re-var-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap}
.re-var-row input{padding:7px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:13px;font-family:inherit;background:#fff;box-sizing:border-box}
.re-var-row input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.1)}
.re-var-key-badge{font-family:monospace;font-size:11.5px;color:#6b7280;white-space:nowrap}
/* Condition groups */
.re-cond-group{border:1.5px solid #e2e8f0;border-radius:8px;padding:12px 14px;margin-bottom:10px;background:#fafafa}
.re-cond-group-head{display:flex;align-items:center;gap:8px;margin-bottom:10px;font-size:12px;font-weight:600;color:#374151}
.re-cond-group-head select{font-size:12px;padding:3px 8px;border:1.5px solid #d1d5db;border-radius:6px;font-weight:600;background:#fff}
.re-cond-group-head .re-rm{margin-left:auto}
/* Wait action card */
.re-act-card[data-type="wait"]{border-top:3px solid #f59e0b}
/* Update option action card */
.re-act-card[data-type="update_option"]{border-top:3px solid #10b981}
</style>

<div class="wrap ah-wrap">

<?php if ( $notice ) :
	list( $nt, $nm ) = explode( ':', $notice, 2 ); ?>
	<div class="ah-notice ah-notice-<?php echo 'success' === $nt ? 'success' : 'warning'; ?>"><?php echo esc_html( $nm ); ?></div>
<?php endif; ?>

<div class="re-header">
	<h1><span class="dashicons dashicons-randomize" style="font-size:1.4rem;vertical-align:middle;margin-right:4px"></span>Triggers Maker</h1>
	<?php if ( ! $editing ) : ?>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'view' => 'edit', 'rule_id' => '0' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ New Rule</a>
	<?php endif; ?>
	<?php if ( $editing ) : ?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules-engine' ) ); ?>" class="ah-btn ah-btn-secondary">← All Rules</a>
	<?php endif; ?>
</div>

<?php if ( ! $editing ) : ?>
<!-- Top-level tab nav -->
<div style="display:flex;gap:2px;border-bottom:2px solid #e5e7eb;margin-bottom:24px">
	<?php
	$tabs = array( 'list' => '⚡ Rules', 'logs' => '📋 Trigger Logs', 'config' => '⚙️ Config' );
	foreach ( $tabs as $tslug => $tlabel ) :
		$active = ( $view === $tslug || ( $tslug === 'list' && $view === 'edit' ) );
	?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules-engine&view=' . $tslug ) ); ?>"
	   style="padding:10px 20px;text-decoration:none;font-weight:600;font-size:13px;color:<?php echo $active ? '#1d4ed8' : '#6b7280'; ?>;border-bottom:<?php echo $active ? '2px solid #1d4ed8' : '2px solid transparent'; ?>;margin-bottom:-2px;transition:color .15s">
		<?php echo $tlabel; ?>
	</a>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ( ! in_array( $view, array( 'config', 'logs' ), true ) ) : ?>
<p style="color:#6b7280;font-size:13px;margin:-8px 0 20px">
	Automate anything. Define a <strong>trigger name</strong>, set optional <strong>conditions</strong>, and run <strong>actions</strong> - send emails, WhatsApp messages, or call any API. Use <code>{field_key}</code> tokens in action text; global defaults are available as <code>{config_email_from_name}</code>, <code>{config_wa_api_url}</code>, etc.
</p>
<?php endif; ?>

<?php if ( $editing ) : /* ════════ RULE EDITOR ════════ */ ?>

<form method="post" id="re-form">
<?php wp_nonce_field( 'ah_save_rule', 'ah_re_nonce' ); ?>
<input type="hidden" name="re_conditions_json" id="re-cond-json">
<input type="hidden" name="re_actions_json"    id="re-act-json">
<input type="hidden" name="re_settings_json"   id="re-settings-json">

<!-- Rule Details -->
<div class="re-section">
	<div class="re-section-title"><span>⚡ Rule Details</span></div>
	<div style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:end">
		<div class="re-field-group" style="margin:0">
			<label>Rule Name</label>
			<input type="text" name="re_name" value="<?php echo esc_attr( $editing->name ); ?>" placeholder="e.g. New enquiry → notify team" required>
		</div>
		<div class="re-field-group" style="margin:0">
			<label>Status</label>
			<select name="re_status">
				<option value="active"   <?php selected( $editing->status, 'active' ); ?>>✅ Active</option>
				<option value="inactive" <?php selected( $editing->status, 'inactive' ); ?>>⏸ Inactive</option>
			</select>
		</div>
	</div>
</div>

<!-- Trigger -->
<div class="re-section">
	<div class="re-section-title"><span>🎯 Trigger - fire when this event happens</span></div>

	<div class="re-field-group" style="max-width:440px;margin-bottom:8px">
		<label>Trigger Name <small>(must match what you pass to <code>evaluate()</code>)</small></label>
		<input type="text" name="re_trigger_name" id="re-trigger-name"
		       value="<?php echo esc_attr( $editing->trigger_name ); ?>"
		       placeholder="e.g. form_submit, order_placed, lead_created" required>
	</div>

	<div class="re-preset-chips">
		<span style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px">Quick fill:</span>
		<?php foreach ( $trigger_presets as $k => $label ) : if ( 'custom' === $k ) continue; ?>
		<span class="re-preset-chip" data-val="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></span>
		<?php endforeach; ?>
	</div>

	<div style="margin-top:14px">
		<div style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">PHP - fire this trigger from anywhere in your code:</div>
		<pre class="re-code-box">AH_Rules_Engine::evaluate( '<span class="re-code-hl" id="re-code-trigger"><?php echo esc_html( $editing->trigger_name ); ?></span>', [
    'field_key' =&gt; $value,
    'email'     =&gt; $email,
    <span style="color:#64748b">// ... any key =&gt; value pairs become {tokens} in actions</span>
] );</pre>
	</div>
</div>

<!-- Form Field Reference Helper (visible when trigger = form_submit) -->
<?php $_ffh_forms = class_exists( 'AH_Form_Builder' ) ? AH_Form_Builder::get_all() : array(); ?>
<?php if ( $_ffh_forms ) : ?>
<div class="re-section" id="re-form-field-helper" style="<?php echo 'form_submit' !== $editing->trigger_name ? 'display:none;' : ''; ?>background:#f0f9ff;border-color:#bae6fd">
	<div class="re-section-title" style="color:#0369a1"><span>📋 Form Field Reference</span></div>
	<p style="font-size:12px;color:#0c4a6e;margin:-4px 0 12px">
		Pick a form to see its field keys. <strong>Click any chip</strong> to insert it into the last focused condition field - or copies to clipboard. Use <code>{field_key}</code> as tokens in action body/subject. Add condition <code>form_id = N</code> to target only one specific form.
	</p>
	<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px">
		<select id="re-form-picker" style="padding:7px 12px;border:1.5px solid #7dd3fc;border-radius:6px;font-size:13px;background:#fff;min-width:220px">
			<option value="">-- Select a form to inspect --</option>
			<?php foreach ( $_ffh_forms as $_ffh_f ) : ?>
			<option value="<?php echo (int) $_ffh_f->id; ?>"><?php echo esc_html( $_ffh_f->name ); ?> (ID #<?php echo (int) $_ffh_f->id; ?>)</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div id="re-field-chips" style="display:flex;flex-wrap:wrap;gap:6px;min-height:28px;align-items:center">
		<span style="font-size:12px;color:#7dd3fc;font-style:italic">Select a form above to see its field keys...</span>
	</div>
</div>
<?php endif; ?>

<!-- Conditions -->
<div class="re-section">
	<div class="re-section-title">
		<span>🔍 Conditions - IF</span>
		<select name="re_conditions_match" id="re-match" style="font-size:12px;padding:4px 8px;border:1.5px solid #d1d5db;border-radius:6px;font-weight:600;background:#fff">
			<option value="all" <?php selected( $editing->conditions_match, 'all' ); ?>>ALL groups match</option>
			<option value="any" <?php selected( $editing->conditions_match, 'any' ); ?>>ANY group matches</option>
		</select>
		<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" id="re-add-group">+ Add Group</button>
	</div>
	<div id="re-conds"></div>
	<p id="re-conds-empty" style="color:#9ca3af;font-size:13px;margin:0">
		No conditions - rule fires on <em>every</em> matching trigger. Click <strong>+ Add Group</strong> to filter.
	</p>
	<p style="font-size:12px;color:#6b7280;margin:8px 0 0">
		Each group combines its own conditions with AND/ANY. Groups themselves are then combined by the top-level ALL/ANY above.
	</p>
</div>

<!-- Rule Settings (dedup / cooldown) -->
<details class="re-adv re-section" style="padding:0">
	<summary style="padding:14px 18px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;list-style:none;user-select:none;display:flex;align-items:center;gap:8px">
		⚙️ Rule Settings <span style="font-weight:400;font-size:12px;color:#9ca3af">(deduplication &amp; cooldown)</span>
	</summary>
	<div style="padding:0 18px 16px;border-top:1px solid #e5e7eb;margin-top:0">
		<p style="font-size:12px;color:#6b7280;margin:12px 0 14px">Prevent this rule from firing too often. Use <code>{field_key}</code> in Dedup Key to deduplicate per-contact (e.g. <code>{email}</code>).</p>
		<div class="re-act-grid-3">
			<div class="re-field-group">
				<label>Dedup Key <small>(token to deduplicate by)</small></label>
				<input type="text" id="re-s-dedup-key" placeholder="{email}" value="<?php echo esc_attr( $editing->settings['dedup_key'] ?? '' ); ?>">
			</div>
			<div class="re-field-group">
				<label>Dedup Window <small>(hours - 0 = disabled)</small></label>
				<input type="number" id="re-s-dedup-win" min="0" max="8760" placeholder="24" value="<?php echo esc_attr( $editing->settings['dedup_window_hours'] ?? '0' ); ?>">
			</div>
			<div class="re-field-group">
				<label>Cooldown <small>(minutes between any fires - 0 = off)</small></label>
				<input type="number" id="re-s-cooldown" min="0" max="10080" placeholder="0" value="<?php echo esc_attr( $editing->settings['cooldown_minutes'] ?? '0' ); ?>">
			</div>
		</div>
	</div>
</details>

<!-- Actions -->
<div class="re-section">
	<div class="re-section-title">
		<span>⚙️ Actions - THEN do this…</span>
		<div style="display:flex;gap:6px;flex-wrap:wrap">
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="send_email">📧 Email</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="whatsapp">💬 WhatsApp</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="http_request">🌐 HTTP Request</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="wait">⏱ Wait / Delay</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="update_option">🔧 Update WP Option</button>
		</div>
	</div>
	<div id="re-actions"></div>
	<p id="re-actions-empty" style="color:#9ca3af;font-size:13px;margin:0">No actions yet. Use the buttons above to add one.</p>
</div>

<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
	<button type="submit" class="ah-btn ah-btn-primary">Save Rule</button>
	<?php if ( $editing->id ) : ?>
	<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'delete' => $editing->id ), admin_url( 'admin.php' ) ), 'ah_del_rule' ) ); ?>"
	   class="ah-btn ah-btn-danger"
	   onclick="return confirm('Delete this rule permanently?')">Delete Rule</a>
	<?php endif; ?>
</div>
</form>

<?php else : /* ════════ RULES LIST ════════ */ ?>

<?php if ( $all_rules ) : ?>
<div class="ah-card">
	<div class="ah-table-wrap">
		<table class="re-tbl">
			<thead>
				<tr>
					<th>#</th><th>Rule Name</th><th>Trigger</th><th>Conditions</th>
					<th>Actions</th><th style="text-align:center">Runs</th>
					<th>Last Run</th><th style="text-align:center">Status</th><th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $all_rules as $r ) :
				$preset_label = $trigger_presets[ $r->trigger_name ] ?? null;
				$action_pills = array_map( static function ( $a ) {
					return match ( $a['type'] ?? '' ) {
						'send_email'   => '📧 Email',
						'whatsapp'     => '💬 WhatsApp',
						'http_request' => '🌐 HTTP',
						default        => ucfirst( str_replace( '_', ' ', $a['type'] ?? '' ) ),
					};
				}, $r->actions );
			?>
			<tr>
				<td style="color:var(--ah-muted,#9ca3af);font-size:12px">#<?php echo esc_html( $r->id ); ?></td>
				<td style="font-weight:600"><?php echo esc_html( $r->name ?: '(untitled)' ); ?></td>
				<td>
					<span class="re-trigger-pill"><?php echo esc_html( $preset_label ?? $r->trigger_name ); ?></span>
					<?php if ( $preset_label ) : ?>
					<br><code style="font-size:10px;color:#9ca3af"><?php echo esc_html( $r->trigger_name ); ?></code>
					<?php endif; ?>
				</td>
				<td style="font-size:12px;color:#6b7280">
					<?php $cc = count( $r->conditions );
					echo $cc ? esc_html( $cc . ' condition' . ( 1 !== $cc ? 's' : '' ) . ' (' . $r->conditions_match . ')' ) : '-'; ?>
				</td>
				<td style="font-size:12px">
					<?php foreach ( $action_pills as $ap ) : ?>
					<span style="display:inline-block;background:#f1f5f9;border-radius:4px;padding:2px 7px;margin:1px;font-size:11px"><?php echo esc_html( $ap ); ?></span>
					<?php endforeach;
					if ( ! $action_pills ) echo '-'; ?>
				</td>
				<td style="text-align:center;font-size:13px"><?php echo esc_html( number_format( (int) $r->run_count ) ); ?></td>
				<td style="font-size:12px;color:#6b7280">
					<?php echo $r->last_run ? esc_html( wp_date( 'M j, Y g:i a', strtotime( $r->last_run ) ) ) : '-'; ?>
				</td>
				<td style="text-align:center">
					<span class="re-st-<?php echo esc_attr( $r->status ); ?>"><?php echo 'active' === $r->status ? 'Active' : 'Inactive'; ?></span>
				</td>
				<td>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'view' => 'edit', 'rule_id' => $r->id ), admin_url( 'admin.php' ) ) ); ?>"
					   class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php else : ?>
<div class="ah-card re-empty">
	<div style="font-size:3rem;margin-bottom:12px">⚙️</div>
	<h2 style="font-family:inherit;font-size:1.1rem;margin:0 0 8px;color:#374151">No rules yet</h2>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'view' => 'edit', 'rule_id' => '0' ), admin_url( 'admin.php' ) ) ); ?>"
	   class="ah-btn ah-btn-primary">+ Create First Rule</a>
</div>
<?php endif; ?>

<?php endif; ?>

<?php
// ════════════════════════════════════════════════════════════
// CONFIG VIEW
// ════════════════════════════════════════════════════════════
if ( 'config' === $view ) :
	$cfg          = AH_Rules_Engine::get_config();
	$custom_vars  = AH_Rules_Engine::get_custom_vars();
	$ch_list      = AH_Rules_Engine::get_email_channels();
	$next_cron    = wp_next_scheduled( 'ah_rules_cron_process' );
?>
<form method="post" id="re-cfg-form">
<?php wp_nonce_field( 'ah_save_re_config', 'ah_re_cfg_nonce' ); ?>
<input type="hidden" name="cfg_custom_vars_json" id="cfg-vars-json" value="<?php echo esc_attr( wp_json_encode( $custom_vars ) ); ?>">
<input type="hidden" name="cfg_channels_json"    id="cfg-channels-json" value="<?php echo esc_attr( wp_json_encode( $ch_list ) ); ?>">

<div class="re-section">
	<div class="re-section-title"><span>📧 Email Defaults</span></div>
	<p style="font-size:12px;color:#6b7280;margin:-4px 0 14px">
		Global email sender defaults. Use as tokens in any action: <code>{config_email_from_name}</code>, <code>{config_email_from_email}</code>, <code>{config_email_cc}</code>, <code>{config_email_bcc}</code>.
	</p>

	<!-- Sender Info -->
	<div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #e5e7eb">
		<h3 style="margin:0 0 12px;font-size:13px;font-weight:600;color:#374151">Sender Information</h3>
		<div class="re-act-grid-2">
			<div class="re-field-group">
				<label>From Name</label>
				<input type="text" name="cfg_email_from_name" value="<?php echo esc_attr( $cfg['email_from_name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</div>
			<div class="re-field-group">
				<label>From Email</label>
				<input type="email" name="cfg_email_from_email" value="<?php echo esc_attr( $cfg['email_from_email'] ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
			</div>
		</div>
	</div>

	<!-- CC Recipients -->
	<div style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #e5e7eb">
		<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
			<h3 style="margin:0;font-size:13px;font-weight:600;color:#374151">CC Recipients</h3>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" onclick="document.getElementById('re-email-cc-list').innerHTML += '<div style=display:flex;gap:6px;margin-bottom:6px><input type=email placeholder=cc@example.com style=flex:1;padding:6px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:12px;background:#fff><button type=button class=ah-btn ah-btn-danger ah-btn-sm onclick=this.parentElement.remove()>Remove</button></div>'; return false">+ Add CC</button>
		</div>
		<div id="re-email-cc-list">
			<?php
			$cc_list = array_filter( array_map( 'trim', explode( ',', $cfg['email_cc'] ?? '' ) ) );
			foreach ( $cc_list as $cc ) :
			?>
			<div style="display:flex;gap:6px;margin-bottom:6px">
				<input type="email" value="<?php echo esc_attr( $cc ); ?>" placeholder="cc@example.com" style="flex:1;padding:6px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:12px;background:#fff" onchange="updateEmailList('cc')">
				<button type="button" class="ah-btn ah-btn-danger ah-btn-sm" onclick="this.parentElement.remove(); updateEmailList('cc')">Remove</button>
			</div>
			<?php endforeach; ?>
		</div>
		<p style="font-size:11px;color:#9ca3af;margin:6px 0 0">Automatically CC all outgoing emails to these addresses (comma-separated in storage)</p>
	</div>

	<!-- BCC Recipients -->
	<div>
		<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
			<h3 style="margin:0;font-size:13px;font-weight:600;color:#374151">BCC Recipients</h3>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" onclick="document.getElementById('re-email-bcc-list').innerHTML += '<div style=display:flex;gap:6px;margin-bottom:6px><input type=email placeholder=bcc@example.com style=flex:1;padding:6px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:12px;background:#fff><button type=button class=ah-btn ah-btn-danger ah-btn-sm onclick=this.parentElement.remove()>Remove</button></div>'; return false">+ Add BCC</button>
		</div>
		<div id="re-email-bcc-list">
			<?php
			$bcc_list = array_filter( array_map( 'trim', explode( ',', $cfg['email_bcc'] ?? '' ) ) );
			foreach ( $bcc_list as $bcc ) :
			?>
			<div style="display:flex;gap:6px;margin-bottom:6px">
				<input type="email" value="<?php echo esc_attr( $bcc ); ?>" placeholder="bcc@example.com" style="flex:1;padding:6px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:12px;background:#fff" onchange="updateEmailList('bcc')">
				<button type="button" class="ah-btn ah-btn-danger ah-btn-sm" onclick="this.parentElement.remove(); updateEmailList('bcc')">Remove</button>
			</div>
			<?php endforeach; ?>
		</div>
		<p style="font-size:11px;color:#9ca3af;margin:6px 0 0">Automatically BCC all outgoing emails to these addresses (comma-separated in storage)</p>
	</div>

	<input type="hidden" name="cfg_email_cc" id="cfg-email-cc" value="<?php echo esc_attr( $cfg['email_cc'] ?? '' ); ?>">
	<input type="hidden" name="cfg_email_bcc" id="cfg-email-bcc" value="<?php echo esc_attr( $cfg['email_bcc'] ?? '' ); ?>">
</div>

<script>
function updateEmailList(type) {
	const listId = type === 'cc' ? 're-email-cc-list' : 're-email-bcc-list';
	const inputId = type === 'cc' ? 'cfg-email-cc' : 'cfg-email-bcc';
	const list = document.getElementById(listId);
	const emails = Array.from(list.querySelectorAll('input[type="email"]'))
		.map(el => el.value.trim())
		.filter(v => v);
	document.getElementById(inputId).value = emails.join(', ');
}
</script>

<!-- Email Channels ─────────────────────────────────────────────────────────── -->
<div class="re-section">
	<div class="re-section-title">
		<span>📨 Email Channels / SMTP Profiles</span>
		<button type="button" id="re-add-channel" class="ah-btn ah-btn-secondary ah-btn-sm">+ Add Channel</button>
	</div>
	<p style="font-size:12px;color:#6b7280;margin:-4px 0 14px">
		Define named SMTP senders (Gmail, Microsoft 365, Mailgun, etc.). Each email action can select a channel via the
		<strong>Send via Channel</strong> dropdown. Leave action channel blank to use the site default SMTP.
	</p>
	<div id="re-channels"></div>
	<p id="re-channels-empty" style="color:#9ca3af;font-size:13px;margin:0">No channels yet - click <strong>+ Add Channel</strong> to add one.</p>
</div>

<!-- WhatsApp Defaults ────────────────────────────────────────────────────── -->
<div class="re-section">
	<div class="re-section-title"><span>💬 WhatsApp Defaults</span></div>
	<p style="font-size:12px;color:#6b7280;margin:-4px 0 14px">
		Global WhatsApp API credentials. Leave the API URL / token blank in a rule action to use these.
		Reference as <code>{config_wa_api_url}</code> and <code>{config_wa_auth_token}</code>.
	</p>
	<div class="re-act-grid-2">
		<div class="re-field-group">
			<label>Default API URL</label>
			<input type="url" name="cfg_wa_api_url" value="<?php echo esc_attr( $cfg['wa_api_url'] ); ?>" placeholder="https://api.wati.io/api/v1/sendMessage">
		</div>
		<div class="re-field-group">
			<label>Default Auth Token <small>(Bearer prefix auto-added)</small></label>
			<input type="text" name="cfg_wa_auth_token" value="<?php echo esc_attr( $cfg['wa_auth_token'] ); ?>" placeholder="eyJhbGci…">
		</div>
	</div>
</div>

<!-- Custom Variables ────────────────────────────────────────────────────── -->
<div class="re-section">
	<div class="re-section-title">
		<span>🔑 Custom Config Variables</span>
		<button type="button" id="re-add-var" class="ah-btn ah-btn-secondary ah-btn-sm">+ Add Variable</button>
	</div>
	<p style="font-size:12px;color:#6b7280;margin:-4px 0 10px">
		Define reusable key-value pairs available as <code>{config_key}</code> tokens in all rule actions.
		Use for: email addresses, phone numbers, API endpoints, names - anything you want to configure once and reuse everywhere.
	</p>
	<div style="display:grid;grid-template-columns:160px 1fr 1fr auto;gap:6px;margin-bottom:8px">
		<div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;padding:4px 0">Key (slug)</div>
		<div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;padding:4px 0">Label</div>
		<div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;padding:4px 0">Value</div>
		<div></div>
	</div>
	<div id="re-custom-vars"></div>
	<p id="re-vars-empty" style="color:#9ca3af;font-size:13px;margin:0">No variables yet - click <strong>+ Add Variable</strong> to add one.</p>
</div>

<!-- Cron / Retry ────────────────────────────────────────────────────────── -->
<div class="re-section">
	<div class="re-section-title"><span>🔁 Cron / Retry</span></div>
	<div class="re-act-grid-2">
		<div class="re-field-group">
			<label>Max Retry Attempts <small>(per failed action, 1–10)</small></label>
			<input type="number" name="cfg_retry_max_attempts" min="1" max="10" value="<?php echo esc_attr( $cfg['retry_max_attempts'] ); ?>">
		</div>
		<div class="re-field-group">
			<label>Cron Processing Enabled</label>
			<label class="re-html-row" style="margin-top:8px">
				<input type="checkbox" name="cfg_cron_enabled" value="1"<?php checked( $cfg['cron_enabled'], '1' ); ?>>
				Process pending &amp; retry failed actions every minute
			</label>
		</div>
	</div>
	<p style="font-size:12px;color:#6b7280;margin:8px 0 0">
		<?php if ( $next_cron ) : ?>
		✅ Cron is scheduled - next run: <strong><?php echo esc_html( wp_date( 'M j, Y g:i:s a', $next_cron ) ); ?></strong>
		<?php else : ?>
		⚠️ Cron not yet scheduled. It will be registered on next page load.
		<?php endif; ?>
	</p>
</div>

<!-- Token reference ────────────────────────────────────────────────────── -->
<div class="re-section" style="background:#f0fdf4;border-color:#86efac">
	<div class="re-section-title" style="color:#15803d"><span>📌 Available {config_xxx} tokens</span></div>
	<p style="font-size:12px;color:#166534;margin:0 0 10px">Use any of these in action templates (To, Subject, Body, URL, etc.)</p>
	<div style="display:flex;flex-wrap:wrap;gap:6px">
		<?php foreach ( AH_Rules_Engine::get_config() as $k => $v ) : ?>
		<code style="background:#dcfce7;border:1px solid #86efac;border-radius:4px;padding:3px 8px;font-size:12px;color:#166534">{config_<?php echo esc_html( $k ); ?>}</code>
		<?php endforeach; ?>
		<?php foreach ( $custom_vars as $cv ) : if ( empty( $cv['key'] ) ) continue; ?>
		<code style="background:#dbeafe;border:1px solid #93c5fd;border-radius:4px;padding:3px 8px;font-size:12px;color:#1d4ed8"
		      title="<?php echo esc_attr( $cv['label'] ?? $cv['key'] ); ?>">{config_<?php echo esc_html( $cv['key'] ); ?>}</code>
		<?php endforeach; ?>
	</div>
</div>

<button type="submit" class="ah-btn ah-btn-primary">Save Configuration</button>
</form>

<!-- Manual Trigger ─────────────────────────────────────────────────────────── -->
<?php
global $wpdb;
$_lg   = AH_Rules_Engine::logs_table();
$_max  = max( 1, (int) ( $cfg['retry_max_attempts'] ?? 3 ) );
$_pend = (int) $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM `{$_lg}` WHERE is_done = 0 AND is_unsent = 0
	   AND ( status = 'pending' OR ( status = 'failed' AND attempts < %d ) )",
	$_max
) );
?>
<div class="re-section" style="border-color:#fbbf24;background:#fffbeb">
	<div class="re-section-title" style="color:#92400e"><span>▶ Manual Trigger - Run Pending Now</span></div>
	<p style="font-size:13px;color:#78350f;margin:-4px 0 14px">
		Bypass the cron schedule and process all queued actions right now.
		<?php if ( $_pend > 0 ) : ?>
		<strong style="color:#b45309"><?php echo number_format( $_pend ); ?> item<?php echo 1 !== $_pend ? 's' : ''; ?> waiting</strong> (pending + retryable failed).
		<?php else : ?>
		<span style="color:#16a34a">✅ Nothing pending - queue is empty.</span>
		<?php endif; ?>
	</p>
	<form method="post">
		<?php wp_nonce_field( 'ah_run_now', 'ah_re_run_nonce' ); ?>
		<button type="submit" class="ah-btn ah-btn-primary"
		        <?php echo 0 === $_pend ? 'disabled style="opacity:.5;cursor:not-allowed"' : ''; ?>
		        onclick="return confirm('Process all <?php echo esc_attr( number_format( $_pend ) ); ?> pending action<?php echo 1 !== $_pend ? 's' : ''; ?> now?')">
			▶ Run All Pending Now<?php echo $_pend > 0 ? ' (' . number_format( $_pend ) . ')' : ''; ?>
		</button>
	</form>
</div>

<!-- Diagnostics ──────────────────────────────────────────────────────────── -->
<?php
global $wpdb;
$_diag_rules_tbl = AH_Rules_Engine::table();
$_diag_logs_tbl  = AH_Rules_Engine::logs_table();
$_logs_exists    = ( $wpdb->get_var( "SHOW TABLES LIKE '{$_diag_logs_tbl}'" ) === $_diag_logs_tbl );
$_rules_exists   = ( $wpdb->get_var( "SHOW TABLES LIKE '{$_diag_rules_tbl}'" ) === $_diag_rules_tbl );
$_active_rules   = $_rules_exists
	? $wpdb->get_results( "SELECT id, name, trigger_name, run_count FROM `{$_diag_rules_tbl}` WHERE status = 'active'" )
	: array();
$_last_tf        = sanitize_text_field( $_GET['tf'] ?? '' );
?>
<div class="re-section" style="border-color:#c7d2fe;background:#eef2ff">
	<div class="re-section-title" style="color:#4338ca"><span>🔍 Diagnostics &amp; Test Fire</span></div>

	<!-- Table health -->
	<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px">
		<div style="background:#fff;border:1px solid #e0e7ff;border-radius:8px;padding:10px 16px;font-size:13px;min-width:200px">
			<div style="font-size:11px;font-weight:700;color:#6366f1;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Trigger Logs Table</div>
			<?php if ( $_logs_exists ) : ?>
			<span style="color:#16a34a;font-weight:600">✅ <?php echo esc_html( $_diag_logs_tbl ); ?></span><br>
			<span style="font-size:11px;color:#6b7280"><?php echo number_format( (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$_diag_logs_tbl}`" ) ); ?> total rows</span>
			<?php else : ?>
			<span style="color:#dc2626;font-weight:600">❌ Table missing</span><br>
			<span style="font-size:11px;color:#dc2626">Deactivate &amp; reactivate the plugin to create it.</span>
			<?php endif; ?>
		</div>
		<div style="background:#fff;border:1px solid #e0e7ff;border-radius:8px;padding:10px 16px;font-size:13px;min-width:200px">
			<div style="font-size:11px;font-weight:700;color:#6366f1;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">Active Rules</div>
			<?php if ( $_active_rules ) : ?>
			<span style="color:#16a34a;font-weight:600">✅ <?php echo count( $_active_rules ); ?> active</span>
			<ul style="margin:4px 0 0;padding-left:16px;font-size:12px;color:#374151">
				<?php foreach ( $_active_rules as $_r ) : ?>
				<li>#<?php echo (int)$_r->id; ?> &ldquo;<?php echo esc_html( $_r->name ); ?>&rdquo; → <code><?php echo esc_html( $_r->trigger_name ); ?></code> (<?php echo (int)$_r->run_count; ?> runs)</li>
				<?php endforeach; ?>
			</ul>
			<?php else : ?>
			<span style="color:#d97706;font-weight:600">⚠️ No active rules</span>
			<?php endif; ?>
		</div>
		<div style="background:#fff;border:1px solid #e0e7ff;border-radius:8px;padding:10px 16px;font-size:13px;min-width:220px">
			<div style="font-size:11px;font-weight:700;color:#6366f1;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px">PHP Error Log</div>
			<span style="font-size:12px;color:#6b7280">Check <code>wp-content/debug.log</code> for<br><code>AH_Rules_Engine::evaluate()</code> errors<br>after submitting a form.</span>
		</div>
	</div>

	<!-- Test fire -->
	<div style="border-top:1px solid #c7d2fe;padding-top:14px;margin-top:4px">
		<div style="font-size:12px;font-weight:700;color:#4338ca;margin-bottom:8px">▶ Test Fire a Trigger</div>
		<p style="font-size:12px;color:#4f46e5;margin:0 0 10px">
			Manually call <code>AH_Rules_Engine::evaluate()</code> with a dummy context. If the trigger matches an active rule, a new <strong>Pending</strong> entry should appear in Trigger Logs immediately.
		</p>
		<form method="post" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
			<?php wp_nonce_field( 'ah_test_fire', 'ah_re_test_fire_nonce' ); ?>
			<div style="display:flex;flex-direction:column;gap:3px">
				<label style="font-size:11px;font-weight:600;color:#6366f1;text-transform:uppercase;letter-spacing:.4px">Trigger Name</label>
				<input type="text" name="test_trigger_name" value="<?php echo esc_attr( $_last_tf ?: ( $_active_rules ? $_active_rules[0]->trigger_name : 'consultation_submitted' ) ); ?>"
				       style="padding:7px 10px;border:1.5px solid #a5b4fc;border-radius:6px;font-size:13px;min-width:240px;background:#fff"
				       placeholder="e.g. consultation_submitted" required>
			</div>
			<button type="submit" class="ah-btn ah-btn-primary" style="margin-top:18px">▶ Fire Test</button>
		</form>
		<?php if ( $_last_tf && ( sanitize_key( $_GET['notice'] ?? '' ) === 'test_fired' ) ) : ?>
		<p style="font-size:12px;color:#16a34a;margin:8px 0 0">
			✅ Fired <code><?php echo esc_html( $_last_tf ); ?></code> - now check the
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules-engine&view=logs' ) ); ?>">Trigger Logs</a> tab for a new Pending entry.
			If nothing appeared, the trigger name doesn't match any active rule, or the table is missing.
		</p>
		<?php endif; ?>
	</div>
</div>

<?php endif; // config view ?>

<?php
// ════════════════════════════════════════════════════════════
// LOGS VIEW
// ════════════════════════════════════════════════════════════
if ( 'logs' === $view ) :
	$log_paged   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	$log_limit   = 10;
	$log_offset  = ( $log_paged - 1 ) * $log_limit;
	$log_filters = array(
		'status'      => sanitize_key( $_GET['log_status'] ?? 'all' ),
		'action_type' => sanitize_key( $_GET['log_action'] ?? 'all' ),
		'search'      => sanitize_text_field( $_GET['log_search'] ?? '' ),
	);
	$logs        = AH_Rules_Engine::get_logs_filtered( $log_filters, $log_limit, $log_offset );
	$total_logs  = AH_Rules_Engine::count_logs_filtered( $log_filters );
	$total_pages = (int) ceil( $total_logs / $log_limit );
	$base_url    = admin_url( 'admin.php?page=ah-rules-engine&view=logs' );
	$filter_qs   = array_filter( array(
		'log_status' => ( 'all' !== $log_filters['status']      ) ? $log_filters['status']      : '',
		'log_action' => ( 'all' !== $log_filters['action_type'] ) ? $log_filters['action_type'] : '',
		'log_search' => $log_filters['search'],
	) );
?>

<!-- Filters bar -->
<form method="get" style="display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:10px 14px">
	<input type="hidden" name="page"  value="ah-rules-engine">
	<input type="hidden" name="view"  value="logs">

	<select name="log_status" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:12px;background:#fff">
		<option value="all"     <?php selected( $log_filters['status'], 'all' );     ?>>All Statuses</option>
		<option value="pending" <?php selected( $log_filters['status'], 'pending' ); ?>>⏳ Pending</option>
		<option value="sent"    <?php selected( $log_filters['status'], 'sent' );    ?>>✅ Sent</option>
		<option value="failed"  <?php selected( $log_filters['status'], 'failed' );  ?>>❌ Failed</option>
		<option value="unsent"  <?php selected( $log_filters['status'], 'unsent' );  ?>>⛔ Unsent</option>
	</select>

	<select name="log_action" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:12px;background:#fff">
		<option value="all"          <?php selected( $log_filters['action_type'], 'all' );          ?>>All Actions</option>
		<option value="send_email"   <?php selected( $log_filters['action_type'], 'send_email' );   ?>>📧 Email</option>
		<option value="whatsapp"     <?php selected( $log_filters['action_type'], 'whatsapp' );     ?>>💬 WhatsApp</option>
		<option value="http_request" <?php selected( $log_filters['action_type'], 'http_request' ); ?>>🌐 HTTP</option>
	</select>

	<input type="text" name="log_search" value="<?php echo esc_attr( $log_filters['search'] ); ?>"
		placeholder="Search rule / trigger…"
		style="padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:12px;width:180px">

	<button type="submit" class="ah-btn ah-btn-primary ah-btn-sm">Filter</button>
	<?php if ( $filter_qs ) : ?>
	<a href="<?php echo esc_url( $base_url ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Clear</a>
	<?php endif; ?>

	<span style="margin-left:auto;font-size:12px;color:#9ca3af"><?php echo number_format( $total_logs ); ?> entries</span>
</form>

<p style="color:#6b7280;font-size:12px;margin:0 0 10px">
	Failed entries with fewer than <strong><?php echo esc_html( AH_Rules_Engine::get_config()['retry_max_attempts'] ); ?> attempts</strong> are automatically retried by the cron.
</p>

<?php if ( $logs ) : ?>
<div class="ah-card">
	<div class="ah-table-wrap" style="overflow-x:auto">
		<table class="re-tbl" style="min-width:900px">
			<thead>
				<tr>
					<th>#</th><th>Rule</th><th>Trigger</th><th>Action</th>
					<th style="text-align:center">Status</th><th style="text-align:center">Attempts</th>
					<th>Time</th><th>Error</th><th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $logs as $lg ) :
				$status_map = array(
					'sent'    => array( 'label' => '✅ Sent',    'color' => '#16a34a' ),
					'failed'  => array( 'label' => '❌ Failed',  'color' => '#dc2626' ),
					'pending' => array( 'label' => '⏳ Pending', 'color' => '#d97706' ),
					'unsent'  => array( 'label' => '⛔ Unsent',  'color' => '#6b7280' ),
				);
				$st  = $status_map[ $lg->status ] ?? array( 'label' => ucfirst( $lg->status ), 'color' => '#374151' );
				$act_labels = array( 'send_email' => '📧 Email', 'whatsapp' => '💬 WhatsApp', 'http_request' => '🌐 HTTP' );
				$time = $lg->sent_at ?: $lg->failed_at ?: $lg->created_at;
				$ctx_data = json_decode( $lg->context_data ?? '{}', true ) ?: array();
				$act_cfg = json_decode( $lg->action_config ?? '{}', true ) ?: array();
				$det_id = 're-det-lg-' . (int) $lg->id;
			?>
			<tr>
				<td style="color:#9ca3af;font-size:12px">#<?php echo (int) $lg->id; ?></td>
				<td style="font-weight:600;font-size:13px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
					<?php echo esc_html( $lg->rule_name ?: '(deleted rule)' ); ?>
				</td>
				<td><code style="font-size:11px"><?php echo esc_html( $lg->trigger_name ); ?></code></td>
				<td style="font-size:12px"><?php echo $act_labels[ $lg->action_type ] ?? esc_html( $lg->action_type ); ?></td>
				<td style="text-align:center;font-size:12px;font-weight:600;color:<?php echo $st['color']; ?>">
					<?php echo $st['label']; ?>
				</td>
				<td style="text-align:center;font-size:13px"><?php echo (int) $lg->attempts; ?></td>
				<td style="font-size:11px;color:#6b7280;white-space:nowrap">
					<?php echo $time ? esc_html( wp_date( 'M j g:i a', strtotime( $time ) ) ) : '-'; ?>
				</td>
				<td style="font-size:11px;color:#dc2626;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo esc_attr( $lg->error_message ?? '' ); ?>">
					<?php echo esc_html( $lg->error_message ? mb_strimwidth( $lg->error_message, 0, 60, '…' ) : '' ); ?>
				</td>
				<td style="white-space:nowrap">
					<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" onclick="document.getElementById('<?php echo esc_js( $det_id ); ?>').style.display = document.getElementById('<?php echo esc_js( $det_id ); ?>').style.display === 'none' ? 'table-row' : 'none'">Details</button>
					<?php if ( 'failed' === $lg->status && ! $lg->is_unsent ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'view' => 'logs', 'retry_log' => $lg->id ), admin_url( 'admin.php' ) ), 'ah_retry_log' ) ); ?>"
					   class="ah-btn ah-btn-secondary ah-btn-sm">Retry</a>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'view' => 'logs', 'unsent_log' => $lg->id ), admin_url( 'admin.php' ) ), 'ah_unsent_log' ) ); ?>"
					   class="ah-btn ah-btn-secondary ah-btn-sm" title="Stop retrying this entry" style="color:#dc2626"
					   onclick="return confirm('Stop retrying this action?')">Cancel</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'view' => 'logs', 'del_log' => $lg->id ), admin_url( 'admin.php' ) ), 'ah_del_log' ) ); ?>"
					   class="ah-btn ah-btn-danger ah-btn-sm"
					   onclick="return confirm('Delete this log entry?')">×</a>
				</td>
			</tr>

			<!-- Details Row -->
			<tr id="<?php echo esc_attr( $det_id ); ?>" style="display:none;background:#f9fafb">
				<td colspan="9" style="padding:16px;border-bottom:2px solid #e5e7eb">
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
						<!-- Input Context Data -->
						<div>
							<h4 style="margin:0 0 12px;font-size:13px;font-weight:600;color:#374151">📥 Input Data (Context)</h4>
							<?php if ( $ctx_data ) : ?>
							<div style="background:#fff;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;font-size:12px">
								<?php foreach ( $ctx_data as $key => $val ) : ?>
								<div style="padding:8px 12px;border-bottom:1px solid #f3f4f6;display:flex;gap:12px">
									<strong style="color:#6b7280;min-width:100px;word-break:break-word"><?php echo esc_html( $key ); ?>:</strong>
									<span style="color:#374151;word-break:break-word"><?php echo esc_html( mb_strimwidth( (string) $val, 0, 100, '…' ) ); ?></span>
								</div>
								<?php endforeach; ?>
							</div>
							<?php else : ?>
							<p style="color:#9ca3af;font-size:12px;margin:0">No context data</p>
							<?php endif; ?>
						</div>

						<!-- Action Details -->
						<div>
							<h4 style="margin:0 0 12px;font-size:13px;font-weight:600;color:#374151">
								<?php
								$action_icons = array( 'send_email' => '📧', 'whatsapp' => '💬', 'http_request' => '🌐' );
								echo $action_icons[ $lg->action_type ] ?? '•';
								?>
								Action Details
							</h4>
							<?php if ( 'send_email' === $lg->action_type && $act_cfg ) : ?>
							<div style="background:#fff;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;font-size:12px">
								<?php
								// Email specific fields
								$email_fields = array(
									'to' => 'To',
									'cc' => 'CC',
									'bcc' => 'BCC',
									'subject' => 'Subject',
									'body' => 'Body',
								);
								foreach ( $email_fields as $field_key => $field_label ) :
									if ( empty( $act_cfg[ $field_key ] ) ) continue;
									$val = $act_cfg[ $field_key ];
									if ( is_array( $val ) ) $val = implode( ', ', $val );
								?>
								<div style="padding:8px 12px;border-bottom:1px solid #f3f4f6">
									<strong style="color:#6b7280;display:block;margin-bottom:4px"><?php echo esc_html( $field_label ); ?>:</strong>
									<span style="color:#374151;display:block;word-break:break-word;font-family:monospace;font-size:11px;background:#f9fafb;padding:6px;border-radius:4px">
										<?php echo esc_html( mb_strimwidth( (string) $val, 0, 150, '…' ) ); ?>
									</span>
								</div>
								<?php endforeach; ?>
							</div>
							<?php elseif ( $act_cfg ) : ?>
							<div style="background:#fff;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;font-size:11px;font-family:monospace">
								<pre style="margin:0;padding:8px 12px;overflow-x:auto;color:#374151"><?php echo esc_html( wp_json_encode( $act_cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre>
							</div>
							<?php else : ?>
							<p style="color:#9ca3af;font-size:12px;margin:0">No action config</p>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( $lg->error_message ) : ?>
					<div style="margin-top:16px;padding:12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:6px">
						<h4 style="margin:0 0 8px;font-size:12px;font-weight:600;color:#991b1b">❌ Error Message</h4>
						<p style="margin:0;font-size:12px;color:#7f1d1d;word-break:break-word;font-family:monospace"><?php echo esc_html( $lg->error_message ); ?></p>
					</div>
					<?php endif; ?>
				</td>
			</tr>

			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<?php if ( $total_pages > 1 ) : ?>
<div style="display:flex;gap:6px;margin-top:14px;flex-wrap:wrap;align-items:center">
	<span style="font-size:12px;color:#6b7280">Page <?php echo $log_paged; ?> of <?php echo $total_pages; ?></span>
	<?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
	<a href="<?php echo esc_url( add_query_arg( array_merge( $filter_qs, array( 'page' => 'ah-rules-engine', 'view' => 'logs', 'paged' => $p ) ), admin_url( 'admin.php' ) ) ); ?>"
	   class="ah-btn <?php echo $p === $log_paged ? 'ah-btn-primary' : 'ah-btn-secondary'; ?> ah-btn-sm"><?php echo $p; ?></a>
	<?php endfor; ?>
</div>
<?php endif; ?>

<?php else : ?>
<div class="ah-card re-empty">
	<div style="font-size:2.5rem;margin-bottom:12px">📋</div>
	<h2 style="font-family:inherit;font-size:1rem;margin:0 0 6px;color:#374151">No log entries yet</h2>
	<p style="color:#9ca3af;font-size:13px;margin:0">Entries appear here each time a rule fires.</p>
</div>
<?php endif; ?>

<?php endif; // logs view ?>

</div><!-- .wrap -->

<script>
jQuery(function ($) {

  /* ── Trigger name → code preview ── */
  $('#re-trigger-name').on('input', function () {
    var v = $(this).val() || 'your_trigger';
    $('#re-code-trigger').text(v);
    $('#re-form-field-helper').toggle(v.trim() === 'form_submit');
  });
  $('.re-preset-chip').on('click', function () {
    $('#re-trigger-name').val($(this).data('val')).trigger('input');
  });

  /* ── Form Field Reference ── */
  var ahReNonce = '<?php echo esc_js( wp_create_nonce( "ah_admin_nonce" ) ); ?>';
  window._lastCondField = null;

  $(document).on('focus', '.re-c-field', function () {
    window._lastCondField = this;
  });

  function buildFFHChip(key, title, bg, border, color) {
    return '<span class="re-ffh-chip" data-key="' + key + '" title="' + title + '" style="background:' + bg + ';border:1px solid ' + border + ';border-radius:4px;padding:4px 10px;font-size:12px;color:' + color + ';cursor:pointer;font-family:monospace;display:inline-block">' + key + '</span>';
  }

  $('#re-form-picker').on('change', function () {
    var fid = $(this).val();
    var $ch = $('#re-field-chips');
    if (!fid) {
      $ch.html('<span style="font-size:12px;color:#7dd3fc;font-style:italic">Select a form above to see its field keys...</span>');
      return;
    }
    $ch.html('<span style="font-size:12px;color:#7dd3fc">Loading...</span>');
    $.post(ajaxurl, { action: 'ah_get_form_fields', form_id: fid, nonce: ahReNonce }, function (r) {
      if (!r.success || !r.data.fields.length) {
        $ch.html('<span style="font-size:12px;color:#dc2626">No fields found for this form.</span>');
        return;
      }
      var s = buildFFHChip('form_id', 'Condition: target this specific form (form_id = ' + fid + ')', '#dbeafe', '#93c5fd', '#1d4ed8') + ' ';
      r.data.fields.forEach(function (f) {
        s += buildFFHChip(f.field_key, f.label + ' - use as {' + f.field_key + '} in action body', '#f0f9ff', '#7dd3fc', '#0369a1') + ' ';
      });
      $ch.html(s);
    }).fail(function () {
      $ch.html('<span style="font-size:12px;color:#dc2626">Error loading fields. Check nonce / login.</span>');
    });
  });

  $(document).on('click', '.re-ffh-chip', function () {
    var key = $(this).data('key');
    if (window._lastCondField && document.contains(window._lastCondField)) {
      $(window._lastCondField).val(key).focus();
    } else if (navigator.clipboard) {
      navigator.clipboard.writeText(key);
    }
    var $c = $(this), orig = $c.attr('style');
    $c.css({ background: '#dcfce7', 'border-color': '#86efac', color: '#166534' });
    setTimeout(function () { $c.attr('style', orig); }, 700);
  });

  /* ── Helpers ── */
  function syncEmpty(wrapId, emptyId) {
    $('#' + emptyId).toggle($('#' + wrapId).children().length === 0);
  }

  /* ── Condition groups ── */
  var opOpts = <?php echo wp_json_encode( $operators ); ?>;
  var NO_VAL = ['is_empty', 'is_not_empty'];

  function buildOpSel(sel) {
    var s = '<select class="re-c-op" style="min-width:160px">';
    Object.keys(opOpts).forEach(function(k) {
      s += '<option value="' + k + '"' + (k === sel ? ' selected' : '') + '>' + opOpts[k] + '</option>';
    });
    return s + '</select>';
  }

  var LIST_OPS = ['in_list', 'not_in_list'];

  function condValPlaceholder(op) {
    if (LIST_OPS.includes(op)) return 'val1, val2, val3';
    return 'value';
  }

  function addCondRowToGroup($group, field, op, val) {
    field = field || ''; op = op || 'equals'; val = val || '';
    var $valInput = $('<input type="text" class="re-c-val" style="flex:1;min-width:120px">')
      .val(val)
      .attr('placeholder', condValPlaceholder(op))
      .toggle(!NO_VAL.includes(op));
    var $opSel = $(buildOpSel(op)).on('change', function() {
      var v = $(this).val();
      NO_VAL.includes(v) ? $valInput.hide().val('') : $valInput.show();
      $valInput.attr('placeholder', condValPlaceholder(v));
    });
    var $row = $('<div class="re-row re-cond-row">').append(
      $('<input type="text" class="re-c-field" placeholder="field_key" style="min-width:140px">').val(field),
      $opSel,
      $valInput,
      $('<button type="button" class="re-rm" title="Remove condition">✕</button>').on('click', function() {
        $row.remove();
        syncEmpty('re-conds', 're-conds-empty');
      })
    );
    $group.find('.re-cond-group-rows').append($row);
    syncEmpty('re-conds', 're-conds-empty');
  }

  function addCondGroup(groupData) {
    groupData = groupData || { match: 'all', conditions: [] };
    var $g = $('<div class="re-cond-group">');
    var $head = $('<div class="re-cond-group-head">').append(
      $('<span>').text('Match '),
      $('<select class="re-c-group-match">').append(
        $('<option value="all"' + ('all' === groupData.match ? ' selected' : '') + '>').text('ALL'),
        $('<option value="any"' + ('any' === groupData.match ? ' selected' : '') + '>').text('ANY')
      ),
      $('<span>').text(' of these conditions:'),
      $('<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-left:8px">+ Condition</button>').on('click', function() {
        addCondRowToGroup($g);
      }),
      $('<button type="button" class="re-rm" title="Remove group">✕</button>').on('click', function() {
        $g.remove();
        syncEmpty('re-conds', 're-conds-empty');
      })
    );
    $g.append($head, $('<div class="re-cond-group-rows">'));
    $('#re-conds').append($g);
    (groupData.conditions || []).forEach(function(c) {
      addCondRowToGroup($g, c.field, c.operator, c.value);
    });
    if (!groupData.conditions || groupData.conditions.length === 0) {
      addCondRowToGroup($g);
    }
    syncEmpty('re-conds', 're-conds-empty');
  }

  $('#re-add-group').on('click', function() { addCondGroup(); });

  /* ── Action card builders ── */
  function rmBtn() {
    return $('<button type="button" class="re-rm" title="Remove action">✕</button>').on('click', function () {
      $(this).closest('.re-act-card').remove();
      syncEmpty('re-actions', 're-actions-empty');
    });
  }

  function buildChannelSel(sel) {
    var s = '<select class="re-a-channel-id">';
    Object.keys(ahReChannels).forEach(function(k) {
      s += '<option value="' + k + '"' + (k === sel ? ' selected' : '') + '>' + ahReChannels[k] + '</option>';
    });
    return s + '</select>';
  }

  function addEmailCard(d) {
    d = d || {};
    var $c = $([
      '<div class="re-act-card" data-type="send_email">',
        '<div class="re-act-card-head">📧 Send Email</div>',
        '<div class="re-field-group"><label>Send via Channel <small>(SMTP profile - leave default for site mail)</small></label>',
          buildChannelSel(d.channel_id || ''), '</div>',
        '<div class="re-field-group" style="border:1px solid #e5e7eb;border-radius:6px;padding:12px;background:#f9fafb;margin:12px 0">',
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">',
            '<label style="margin:0;font-size:12px;font-weight:600;color:#374151">To Recipients <small>(email or {field_key})</small></label>',
            '<button type="button" class="re-add-to-btn ah-btn ah-btn-secondary ah-btn-sm" data-add="to">+ Add</button>',
          '</div>',
          '<div class="re-to-list"></div>',
        '</div>',
        '<div class="re-field-group" style="border:1px solid #e5e7eb;border-radius:6px;padding:12px;background:#f9fafb;margin:12px 0">',
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">',
            '<label style="margin:0;font-size:12px;font-weight:600;color:#374151">CC Recipients <small>(optional)</small></label>',
            '<button type="button" class="re-add-to-btn ah-btn ah-btn-secondary ah-btn-sm" data-add="cc">+ Add</button>',
          '</div>',
          '<div class="re-cc-list"></div>',
        '</div>',
        '<div class="re-field-group" style="border:1px solid #e5e7eb;border-radius:6px;padding:12px;background:#f9fafb;margin:12px 0">',
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">',
            '<label style="margin:0;font-size:12px;font-weight:600;color:#374151">BCC Recipients <small>(optional)</small></label>',
            '<button type="button" class="re-add-to-btn ah-btn ah-btn-secondary ah-btn-sm" data-add="bcc">+ Add</button>',
          '</div>',
          '<div class="re-bcc-list"></div>',
        '</div>',
        '<div class="re-act-grid-2">',
          '<div class="re-field-group"><label>Subject</label>',
            '<input type="text" class="re-a-subj" placeholder="New submission: {name}"></div>',
        '</div>',
        '<div class="re-field-group"><label>Format</label>',
          '<label class="re-html-row"><input type="checkbox" class="re-a-html"> Send as HTML email (supports tags &amp; styles)</label></div>',
        '<div class="re-field-group"><label>Body <small>(use {field_key} tokens)</small></label>',
          '<textarea class="re-a-body" rows="4" placeholder="Hello {name},&#10;&#10;Message: {message}"></textarea></div>',
      '</div>',
    ].join(''));
    $c.find('.re-act-card-head').append(rmBtn());

    // Helper to add recipient rows
    function addRecipientRow(type, value) {
      var $row = $('<div style="display:flex;gap:6px;margin-bottom:6px">'+
        '<input type="text" class="re-a-'+type+'-val" placeholder="email@example.com or {field}" style="flex:1;padding:6px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:12px" value="'+(value||'')+'">'+
        '<button type="button" class="ah-btn ah-btn-danger ah-btn-sm" style="padding:6px 10px">Remove</button>'+
        '</div>');
      $row.find('button').on('click', function(){
        $row.remove();
      });
      return $row;
    }

    // Add buttons
    $c.find('.re-add-to-btn').on('click', function(e){
      e.preventDefault();
      var type = $(this).data('add');
      var target = $c.find('.re-'+type+'-list');
      target.append(addRecipientRow(type));
    });

    // Load existing values
    var toList = d.to ? (Array.isArray(d.to) ? d.to : [d.to]) : [];
    var ccList = d.cc ? (Array.isArray(d.cc) ? d.cc : [d.cc]) : [];
    var bccList = d.bcc ? (Array.isArray(d.bcc) ? d.bcc : [d.bcc]) : [];

    toList.forEach(function(v){ $c.find('.re-to-list').append(addRecipientRow('to', v)); });
    ccList.forEach(function(v){ $c.find('.re-cc-list').append(addRecipientRow('cc', v)); });
    bccList.forEach(function(v){ $c.find('.re-bcc-list').append(addRecipientRow('bcc', v)); });

    // Add at least one TO field
    if (toList.length === 0) {
      $c.find('.re-to-list').append(addRecipientRow('to'));
    }

    $c.find('.re-a-subj').val(d.subject || '');
    $c.find('.re-a-html').prop('checked', !!d.html);
    $c.find('.re-a-body').val(d.body || '');
    $('#re-actions').append($c);
    syncEmpty('re-actions', 're-actions-empty');
  }

  function addWhatsappCard(d) {
    d = d || {};
    var $c = $([
      '<div class="re-act-card" data-type="whatsapp">',
        '<div class="re-act-card-head">💬 WhatsApp</div>',
        '<div class="re-act-grid-2">',
          '<div class="re-field-group"><label>API URL</label>',
            '<input type="url" class="re-a-wa-url" placeholder="https://api.wati.io/api/v1/sendMessage"></div>',
          '<div class="re-field-group"><label>Auth Token</label>',
            '<input type="text" class="re-a-wa-token" placeholder="Bearer token or API key"></div>',
        '</div>',
        '<div class="re-field-group"><label>To Phone <small>(use {field_key})</small></label>',
          '<input type="text" class="re-a-wa-phone" placeholder="+91{phone} or {mobile}"></div>',
        '<div class="re-field-group"><label>Message <small>(use {field_key} tokens)</small></label>',
          '<textarea class="re-a-wa-msg" rows="3" placeholder="Hello {name}, thanks for reaching out!"></textarea></div>',
        '<details class="re-adv">',
          '<summary>▸ Custom Body JSON <span style="font-weight:400">(optional - overrides default payload)</span></summary>',
          '<div class="re-adv-body">',
            '<div class="re-field-group" style="margin:0"><label>JSON Body <small>(use {field_key} tokens inside strings)</small></label>',
            '<textarea class="re-a-wa-json" rows="3" placeholder=\'{"to":"{phone}","type":"text","text":{"body":"{message}"}}\' ></textarea></div>',
          '</div>',
        '</details>',
      '</div>',
    ].join(''));
    $c.find('.re-act-card-head').append(rmBtn());
    $c.find('.re-a-wa-url').val(d.api_url || '');
    $c.find('.re-a-wa-token').val(d.auth_token || '');
    $c.find('.re-a-wa-phone').val(d.to_phone || '');
    $c.find('.re-a-wa-msg').val(d.message || '');
    $c.find('.re-a-wa-json').val(d.body_json || '');
    $('#re-actions').append($c);
    syncEmpty('re-actions', 're-actions-empty');
  }

  function addHttpCard(d) {
    d = d || {};
    var $c = $([
      '<div class="re-act-card" data-type="http_request">',
        '<div class="re-act-card-head">🌐 HTTP Request</div>',
        '<div class="re-act-grid-7030">',
          '<div class="re-field-group"><label>URL <small>(use {field_key})</small></label>',
            '<input type="url" class="re-a-http-url" placeholder="https://api.example.com/webhook"></div>',
          '<div class="re-field-group"><label>Method</label>',
            '<select class="re-a-http-method"><option>POST</option><option>GET</option><option>PUT</option><option>PATCH</option><option>DELETE</option></select></div>',
        '</div>',
        '<div class="re-act-grid-2">',
          '<div class="re-field-group"><label>Auth Type</label>',
            '<select class="re-a-http-authtype">',
              '<option value="none">No Auth</option>',
              '<option value="bearer">Bearer Token</option>',
              '<option value="basic">Basic Auth (user:pass)</option>',
            '</select></div>',
          '<div class="re-field-group re-authval-wrap"><label>Auth Value <small>(token or user:pass)</small></label>',
            '<input type="text" class="re-a-http-authval" placeholder="your-token-here"></div>',
        '</div>',
        '<div class="re-field-group"><label>Headers <small>(JSON object or Key: Value lines - optional)</small></label>',
          '<textarea class="re-a-http-headers" rows="2" placeholder="Authorization: Bearer {token}&#10;X-Custom: value"></textarea></div>',
        '<div class="re-act-grid-7030">',
          '<div class="re-field-group"><label>Body <small>(use {field_key} tokens)</small></label>',
            '<textarea class="re-a-http-body" rows="3" placeholder=\'{"name":"{name}","email":"{email}"}\'></textarea></div>',
          '<div class="re-field-group"><label>Content Type</label>',
            '<select class="re-a-http-ct"><option value="json">JSON</option><option value="form">Form-encoded</option></select></div>',
        '</div>',
      '</div>',
    ].join(''));
    $c.find('.re-act-card-head').append(rmBtn());
    $c.find('.re-a-http-url').val(d.url || '');
    $c.find('.re-a-http-method').val(d.method || 'POST');
    var authType = d.auth_type || 'none';
    $c.find('.re-a-http-authtype').val(authType).on('change', function () {
      $c.find('.re-authval-wrap').toggle($(this).val() !== 'none');
    });
    $c.find('.re-authval-wrap').toggle(authType !== 'none');
    $c.find('.re-a-http-authval').val(d.auth_value || '');
    $c.find('.re-a-http-headers').val(d.headers || '');
    $c.find('.re-a-http-ct').val(d.content_type || 'json');
    $c.find('.re-a-http-body').val(d.body || '');
    $('#re-actions').append($c);
    syncEmpty('re-actions', 're-actions-empty');
  }

  function addWaitCard(d) {
    d = d || {};
    var unitSel = '<select class="re-a-wait-unit" style="width:auto">'
      + '<option value="minutes"' + ('minutes' === (d.unit||'minutes') ? ' selected' : '') + '>Minutes</option>'
      + '<option value="hours"'   + ('hours'   === d.unit ? ' selected' : '') + '>Hours</option>'
      + '<option value="days"'    + ('days'    === d.unit ? ' selected' : '') + '>Days</option>'
      + '</select>';
    var $c = $([
      '<div class="re-act-card" data-type="wait">',
        '<div class="re-act-card-head">⏱ Wait / Delay</div>',
        '<div class="re-row" style="align-items:center;gap:12px;flex-wrap:nowrap">',
          '<div class="re-field-group" style="margin:0;flex:0 0 140px">',
            '<label>Duration</label>',
            '<input type="number" class="re-a-wait-dur" min="1" placeholder="1" style="width:100%">',
          '</div>',
          '<div class="re-field-group" style="margin:0;flex:0 0 120px">',
            '<label>Unit</label>',
            unitSel,
          '</div>',
          '<p style="margin:18px 0 0;font-size:12px;color:#9ca3af;flex:1">',
            'Actions below this card will be queued and run after the delay.',
          '</p>',
        '</div>',
      '</div>',
    ].join(''));
    $c.find('.re-act-card-head').append(rmBtn());
    $c.find('.re-a-wait-dur').val(d.duration || 1);
    $('#re-actions').append($c);
    syncEmpty('re-actions', 're-actions-empty');
  }

  function addUpdateOptionCard(d) {
    d = d || {};
    var $c = $([
      '<div class="re-act-card" data-type="update_option">',
        '<div class="re-act-card-head">🔧 Update WP Option</div>',
        '<div class="re-act-grid-2">',
          '<div class="re-field-group"><label>Option Key <small>(wp_options name)</small></label>',
            '<input type="text" class="re-a-opt-key" placeholder="my_plugin_setting"></div>',
          '<div class="re-field-group"><label>Value <small>(use {field_key} tokens)</small></label>',
            '<input type="text" class="re-a-opt-val" placeholder="{email} or static value"></div>',
        '</div>',
      '</div>',
    ].join(''));
    $c.find('.re-act-card-head').append(rmBtn());
    $c.find('.re-a-opt-key').val(d.option_key   || '');
    $c.find('.re-a-opt-val').val(d.option_value || '');
    $('#re-actions').append($c);
    syncEmpty('re-actions', 're-actions-empty');
  }

  $('[data-add-action="send_email"]').on('click',     function () { addEmailCard(); });
  $('[data-add-action="whatsapp"]').on('click',       function () { addWhatsappCard(); });
  $('[data-add-action="http_request"]').on('click',   function () { addHttpCard(); });
  $('[data-add-action="wait"]').on('click',           function () { addWaitCard(); });
  $('[data-add-action="update_option"]').on('click',  function () { addUpdateOptionCard(); });

  /* ── Channel list for email action dropdown ── */
  var ahReChannels = <?php echo wp_json_encode( AH_Rules_Engine::get_email_channels_list() ); ?>;

  /* ── Populate existing data ── */
  var existingCondGroups = <?php echo wp_json_encode( $editing ? (array) $editing->conditions : array() ); ?>;
  var existingActions    = <?php echo wp_json_encode( $editing ? (array) $editing->actions    : array() ); ?>;
  var existingSettings   = <?php echo wp_json_encode( $editing ? (array) ( $editing->settings ?? array() ) : array() ); ?>;

  // Load condition groups (new format: [{match, conditions:[]}])
  // Also handles legacy flat format by wrapping in a single group
  if (existingCondGroups.length && existingCondGroups[0] && existingCondGroups[0].conditions) {
    existingCondGroups.forEach(function(g) { addCondGroup(g); });
  } else if (existingCondGroups.length && existingCondGroups[0] && existingCondGroups[0].field) {
    // Legacy flat format
    addCondGroup({ match: 'all', conditions: existingCondGroups });
  }

  // Load actions including new types
  existingActions.forEach(function (a) {
    if      (a.type === 'send_email')    addEmailCard(a);
    else if (a.type === 'whatsapp')      addWhatsappCard(a);
    else if (a.type === 'http_request')  addHttpCard(a);
    else if (a.type === 'wait')          addWaitCard(a);
    else if (a.type === 'update_option') addUpdateOptionCard(a);
  });

  // Load settings into dedup/cooldown fields
  if (existingSettings.dedup_key)          $('#re-s-dedup-key').val(existingSettings.dedup_key);
  if (existingSettings.dedup_window_hours) $('#re-s-dedup-win').val(existingSettings.dedup_window_hours);
  if (existingSettings.cooldown_minutes)   $('#re-s-cooldown').val(existingSettings.cooldown_minutes);

  /* ── Serialize on submit ── */
  $('#re-form').on('submit', function () {

    // Serialize condition groups
    var groups = [];
    $('#re-conds .re-cond-group').each(function() {
      var match = $(this).find('.re-c-group-match').val() || 'all';
      var conds = [];
      $(this).find('.re-cond-row').each(function() {
        var f = $(this).find('.re-c-field').val().trim();
        if (!f) return;
        conds.push({
          field:    f,
          operator: $(this).find('.re-c-op').val(),
          value:    $(this).find('.re-c-val:visible').val().trim()
        });
      });
      if (conds.length) groups.push({ match: match, conditions: conds });
    });
    $('#re-cond-json').val(JSON.stringify(groups));

    // Serialize actions
    var acts = [];
    $('.re-act-card').each(function () {
      var type = $(this).data('type');
      if (type === 'send_email') {
        var toList = [], ccList = [], bccList = [];
        $(this).find('.re-to-list .re-a-to-val').each(function(){ var v=$(this).val().trim(); if(v) toList.push(v); });
        $(this).find('.re-cc-list .re-a-cc-val').each(function(){ var v=$(this).val().trim(); if(v) ccList.push(v); });
        $(this).find('.re-bcc-list .re-a-bcc-val').each(function(){ var v=$(this).val().trim(); if(v) bccList.push(v); });
        acts.push({
          type: 'send_email', channel_id: $(this).find('.re-a-channel-id').val(),
          to: toList, cc: ccList, bcc: bccList,
          subject: $(this).find('.re-a-subj').val().trim(),
          body:    $(this).find('.re-a-body').val(),
          html:    $(this).find('.re-a-html').is(':checked') ? 1 : 0,
        });
      } else if (type === 'whatsapp') {
        acts.push({
          type: 'whatsapp',
          api_url:    $(this).find('.re-a-wa-url').val().trim(),
          auth_token: $(this).find('.re-a-wa-token').val().trim(),
          to_phone:   $(this).find('.re-a-wa-phone').val().trim(),
          message:    $(this).find('.re-a-wa-msg').val().trim(),
          body_json:  $(this).find('.re-a-wa-json').val().trim(),
        });
      } else if (type === 'http_request') {
        acts.push({
          type: 'http_request',
          url:          $(this).find('.re-a-http-url').val().trim(),
          method:       $(this).find('.re-a-http-method').val(),
          auth_type:    $(this).find('.re-a-http-authtype').val(),
          auth_value:   $(this).find('.re-a-http-authval').val().trim(),
          headers:      $(this).find('.re-a-http-headers').val().trim(),
          content_type: $(this).find('.re-a-http-ct').val(),
          body:         $(this).find('.re-a-http-body').val().trim(),
        });
      } else if (type === 'wait') {
        acts.push({
          type:     'wait',
          duration: parseInt($(this).find('.re-a-wait-dur').val(), 10) || 1,
          unit:     $(this).find('.re-a-wait-unit').val(),
        });
      } else if (type === 'update_option') {
        var k = $(this).find('.re-a-opt-key').val().trim();
        if (k) acts.push({
          type:         'update_option',
          option_key:   k,
          option_value: $(this).find('.re-a-opt-val').val().trim(),
        });
      }
    });
    $('#re-act-json').val(JSON.stringify(acts));

    // Serialize settings
    $('#re-settings-json').val(JSON.stringify({
      dedup_key:          $('#re-s-dedup-key').val().trim(),
      dedup_window_hours: parseInt($('#re-s-dedup-win').val(), 10) || 0,
      cooldown_minutes:   parseInt($('#re-s-cooldown').val(), 10)  || 0,
    }));
  });


  /* ════════════════════════════════════════════════════════════
     Config page - Email Channels + Custom Variables
     ════════════════════════════════════════════════════════════ */

  var SMTP_PRESETS = {
    gmail:     { host: 'smtp.gmail.com',         port: 587, enc: 'tls' },
    office365: { host: 'smtp.office365.com',      port: 587, enc: 'tls' },
    mailgun:   { host: 'smtp.mailgun.org',         port: 587, enc: 'tls' },
    sendgrid:  { host: 'smtp.sendgrid.net',        port: 587, enc: 'tls' },
    zoho:      { host: 'smtp.zoho.com',            port: 587, enc: 'tls' },
    custom:    { host: '',                         port: 587, enc: 'tls' }
  };

  /* ── Channel cards ── */
  function syncChannelsEmpty() {
    var empty = $('#re-channels').children().length === 0;
    $('#re-channels-empty').toggle(empty);
  }

  function buildChannelCard(d) {
    d = d || {};
    var provOpts = ['custom','gmail','office365','mailgun','sendgrid','zoho'];
    var provLabels = {custom:'Custom SMTP',gmail:'Gmail',office365:'Microsoft 365',mailgun:'Mailgun',sendgrid:'SendGrid',zoho:'Zoho Mail'};
    var pSel = '<select class="re-ch-provider">';
    provOpts.forEach(function(p){ pSel += '<option value="'+p+'"'+(p===(d.provider||'custom')?' selected':'')+'>'+(provLabels[p]||p)+'</option>'; });
    pSel += '</select>';

    var encSel = '<select class="re-ch-enc">'
      + '<option value="tls"'+(d.encryption==='tls'||!d.encryption?' selected':'')+'> TLS (STARTTLS)</option>'
      + '<option value="ssl"'+(d.encryption==='ssl'?' selected':'')+'>SSL</option>'
      + '<option value="none"'+(d.encryption==='none'?' selected':'')+'>None</option>'
      + '</select>';

    var $c = $([
      '<div class="re-ch-card">',
        '<div class="re-ch-card-head">',
          '<strong class="re-ch-display-name">',d.name||'New Channel','</strong>',
          '<span class="re-ch-id-badge">',d.id||'…','</span>',
          '<button type="button" class="re-rm re-ch-rm" title="Remove channel">✕</button>',
        '</div>',
        '<div class="re-act-grid-3">',
          '<div class="re-field-group"><label>Channel ID <small>(unique slug)</small></label><input type="text" class="re-ch-id" placeholder="gmail_support"></div>',
          '<div class="re-field-group"><label>Channel Name</label><input type="text" class="re-ch-cname" placeholder="Support Gmail"></div>',
          '<div class="re-field-group"><label>Provider</label>',pSel,'</div>',
        '</div>',
        '<div class="re-act-grid-2">',
          '<div class="re-field-group"><label>From Name</label><input type="text" class="re-ch-from-name" placeholder="Name"></div>',
          '<div class="re-field-group"><label>From Email</label><input type="email" class="re-ch-from-email" placeholder="test@yopmail.com"></div>',
        '</div>',
        '<div class="re-act-grid-3">',
          '<div class="re-field-group"><label>SMTP Host</label><input type="text" class="re-ch-host" placeholder="smtp.gmail.com"></div>',
          '<div class="re-field-group"><label>Port</label><input type="number" class="re-ch-port" placeholder="587" min="1" max="65535"></div>',
          '<div class="re-field-group"><label>Encryption</label>',encSel,'</div>',
        '</div>',
        '<div class="re-act-grid-2">',
          '<div class="re-field-group"><label>SMTP Username</label><input type="text" class="re-ch-user" placeholder="user@gmail.com"></div>',
          '<div class="re-field-group"><label>Password / App Password</label><input type="password" class="re-ch-pass" placeholder="••••••••" autocomplete="new-password"></div>',
        '</div>',
      '</div>',
    ].join(''));

    // Fill values
    $c.find('.re-ch-id').val(d.id||'');
    $c.find('.re-ch-cname').val(d.name||'');
    $c.find('.re-ch-from-name').val(d.from_name||'');
    $c.find('.re-ch-from-email').val(d.from_email||'');
    $c.find('.re-ch-host').val(d.host||'');
    $c.find('.re-ch-port').val(d.port||587);
    $c.find('.re-ch-user').val(d.username||'');
    $c.find('.re-ch-pass').val(d.password||'');

    // Live update badge when ID/name changes
    $c.find('.re-ch-id').on('input', function(){ $c.find('.re-ch-id-badge').text($(this).val()||'…'); });
    $c.find('.re-ch-cname').on('input', function(){ $c.find('.re-ch-display-name').text($(this).val()||'New Channel'); });

    // Provider preset auto-fill
    $c.find('.re-ch-provider').on('change', function(){
      var p = SMTP_PRESETS[$(this).val()];
      if (!p) return;
      if (p.host) $c.find('.re-ch-host').val(p.host);
      $c.find('.re-ch-port').val(p.port);
      $c.find('.re-ch-enc').val(p.enc);
    });

    // Remove
    $c.find('.re-ch-rm').on('click', function(){ $c.remove(); syncChannelsEmpty(); serializeChannels(); });

    return $c;
  }

  function serializeChannels() {
    var list = [];
    $('#re-channels .re-ch-card').each(function(){
      var id = $(this).find('.re-ch-id').val().trim().replace(/[^a-z0-9_]/gi,'_').toLowerCase();
      if (!id) return;
      list.push({
        id:         id,
        name:       $(this).find('.re-ch-cname').val().trim(),
        from_name:  $(this).find('.re-ch-from-name').val().trim(),
        from_email: $(this).find('.re-ch-from-email').val().trim(),
        provider:   $(this).find('.re-ch-provider').val(),
        host:       $(this).find('.re-ch-host').val().trim(),
        port:       parseInt($(this).find('.re-ch-port').val(),10)||587,
        username:   $(this).find('.re-ch-user').val().trim(),
        password:   $(this).find('.re-ch-pass').val(),
        encryption: $(this).find('.re-ch-enc').val(),
      });
    });
    $('#cfg-channels-json').val(JSON.stringify(list));
  }

  $('#re-add-channel').on('click', function(){
    $('#re-channels').append(buildChannelCard());
    syncChannelsEmpty();
  });

  // Load existing channels
  var existingChannels = <?php echo wp_json_encode( isset( $ch_list ) ? $ch_list : array() ); ?>;
  existingChannels.forEach(function(ch){ $('#re-channels').append(buildChannelCard(ch)); });
  syncChannelsEmpty();

  /* ── Custom Variables ── */
  function syncVarsEmpty() {
    $('#re-vars-empty').toggle($('#re-custom-vars').children().length === 0);
  }

  function addVarRow(d) {
    d = d || {};
    var $r = $('<div class="re-var-row">').append(
      $('<input type="text" class="re-v-key" placeholder="key_name" style="width:160px">').val(d.key||''),
      $('<input type="text" class="re-v-label" placeholder="Label / Description" style="flex:1;min-width:130px">').val(d.label||''),
      $('<input type="text" class="re-v-val" placeholder="Value" style="flex:1;min-width:130px">').val(d.value||''),
      $('<button type="button" class="re-rm" title="Remove">✕</button>').on('click', function(){
        $r.remove(); syncVarsEmpty(); serializeVars();
      })
    );
    $r.find('input').on('input', serializeVars);
    $('#re-custom-vars').append($r);
    syncVarsEmpty();
  }

  function serializeVars() {
    var list = [];
    $('#re-custom-vars .re-var-row').each(function(){
      var k = $(this).find('.re-v-key').val().trim().replace(/[^a-z0-9_]/gi,'_').toLowerCase();
      if (!k) return;
      list.push({ key: k, label: $(this).find('.re-v-label').val().trim(), value: $(this).find('.re-v-val').val().trim() });
    });
    $('#cfg-vars-json').val(JSON.stringify(list));
  }

  $('#re-add-var').on('click', function(){ addVarRow(); });

  // Load existing custom vars
  var existingVars = <?php echo wp_json_encode( isset( $custom_vars ) ? $custom_vars : array() ); ?>;
  existingVars.forEach(function(v){ addVarRow(v); });
  syncVarsEmpty();

  // Serialize on config form submit
  $('#re-cfg-form').on('submit', function(){ serializeChannels(); serializeVars(); });

});
</script>
