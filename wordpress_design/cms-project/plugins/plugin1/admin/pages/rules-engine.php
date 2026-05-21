<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

AH_Rules_Engine::install_tables();

$notice  = '';
$rule_id = (int) ( $_GET['rule_id'] ?? 0 );
$view    = sanitize_key( $_GET['view'] ?? 'list' );

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

	$saved_id = AH_Rules_Engine::save( $rule_id, array(
		'name'             => sanitize_text_field( $_POST['re_name']             ?? '' ),
		'trigger_name'     => sanitize_text_field( $_POST['re_trigger_name']     ?? 'form_submit' ),
		'conditions_match' => sanitize_key(        $_POST['re_conditions_match'] ?? 'all' ),
		'conditions'       => $raw_conditions,
		'actions'          => $raw_actions,
		'status'           => sanitize_key(        $_POST['re_status']           ?? 'active' ),
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
	$n      = sanitize_key( $_GET['notice'] );
	$notice = 'saved' === $n ? 'success:Rule saved.' : ( 'deleted' === $n ? 'success:Rule deleted.' : '' );
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
</style>

<div class="wrap ah-wrap">

<?php if ( $notice ) :
	list( $nt, $nm ) = explode( ':', $notice, 2 ); ?>
	<div class="ah-notice ah-notice-<?php echo 'success' === $nt ? 'success' : 'warning'; ?>"><?php echo esc_html( $nm ); ?></div>
<?php endif; ?>

<div class="re-header">
	<h1><span class="dashicons dashicons-randomize" style="font-size:1.4rem;vertical-align:middle;margin-right:4px"></span>Triggers Maker</h1>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-rules-engine', 'view' => 'edit', 'rule_id' => '0' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ New Rule</a>
	<?php if ( $editing ) : ?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules-engine' ) ); ?>" class="ah-btn ah-btn-secondary">← All Rules</a>
	<?php endif; ?>
</div>

<p style="color:#6b7280;font-size:13px;margin:-8px 0 20px">
	Automate anything. Define a <strong>trigger name</strong>, set optional <strong>conditions</strong>, and run <strong>actions</strong> - send emails, WhatsApp messages, or call any API. <strong>Trigger → Conditions → Actions.</strong>
</p>

<?php if ( $editing ) : /* ════════ RULE EDITOR ════════ */ ?>

<form method="post" id="re-form">
<?php wp_nonce_field( 'ah_save_rule', 'ah_re_nonce' ); ?>
<input type="hidden" name="re_conditions_json" id="re-cond-json">
<input type="hidden" name="re_actions_json"    id="re-act-json">

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

<!-- Conditions -->
<div class="re-section">
	<div class="re-section-title">
		<span>🔍 Conditions - IF</span>
		<select name="re_conditions_match" id="re-match" style="font-size:12px;padding:4px 8px;border:1.5px solid #d1d5db;border-radius:6px;font-weight:600;background:#fff">
			<option value="all" <?php selected( $editing->conditions_match, 'all' ); ?>>ALL match</option>
			<option value="any" <?php selected( $editing->conditions_match, 'any' ); ?>>ANY match</option>
		</select>
		<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" id="re-add-cond">+ Condition</button>
	</div>
	<div id="re-conds"></div>
	<p id="re-conds-empty" style="color:#9ca3af;font-size:13px;margin:0">
		No conditions - rule fires on <em>every</em> matching trigger. Click <strong>+ Condition</strong> to filter by context values.
	</p>
	<p style="font-size:12px;color:#6b7280;margin:8px 0 0">
		Field key must match a key in the <code>$context</code> array passed to <code>evaluate()</code>.
	</p>
</div>

<!-- Actions -->
<div class="re-section">
	<div class="re-section-title">
		<span>⚙️ Actions - THEN do this…</span>
		<div style="display:flex;gap:6px;flex-wrap:wrap">
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="send_email">📧 Email</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="whatsapp">💬 WhatsApp</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" data-add-action="http_request">🌐 HTTP Request</button>
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

</div><!-- .wrap -->

<script>
jQuery(function ($) {

  /* ── Trigger name → code preview ── */
  $('#re-trigger-name').on('input', function () {
    $('#re-code-trigger').text($(this).val() || 'your_trigger');
  });
  $('.re-preset-chip').on('click', function () {
    $('#re-trigger-name').val($(this).data('val')).trigger('input');
  });

  /* ── Helpers ── */
  function syncEmpty(wrapId, emptyId) {
    $('#' + emptyId).toggle($('#' + wrapId).children().length === 0);
  }

  /* ── Condition rows ── */
  var opOpts   = <?php echo wp_json_encode( $operators ); ?>;
  var NO_VAL   = ['is_empty', 'is_not_empty'];

  function buildOpSel(sel) {
    var s = '<select class="re-c-op" style="min-width:160px">';
    Object.keys(opOpts).forEach(function (k) {
      s += '<option value="' + k + '"' + (k === sel ? ' selected' : '') + '>' + opOpts[k] + '</option>';
    });
    return s + '</select>';
  }

  function addCondRow(field, op, val) {
    field = field || ''; op = op || 'equals'; val = val || '';
    var $row = $('<div class="re-row re-cond-row">').append(
      $('<input type="text" class="re-c-field" placeholder="field_key" style="min-width:140px">').val(field),
      $(buildOpSel(op)).on('change', function () {
        var $v = $(this).closest('.re-row').find('.re-c-val');
        NO_VAL.includes($(this).val()) ? $v.hide().val('') : $v.show();
      }),
      $('<input type="text" class="re-c-val" placeholder="value" style="flex:1;min-width:120px">').val(val)
        .toggle(!NO_VAL.includes(op)),
      $('<button type="button" class="re-rm" title="Remove">✕</button>').on('click', function () {
        $(this).closest('.re-cond-row').remove();
        syncEmpty('re-conds', 're-conds-empty');
      })
    );
    $('#re-conds').append($row);
    syncEmpty('re-conds', 're-conds-empty');
  }

  $('#re-add-cond').on('click', function () { addCondRow(); });

  /* ── Action card builders ── */
  function rmBtn() {
    return $('<button type="button" class="re-rm" title="Remove action">✕</button>').on('click', function () {
      $(this).closest('.re-act-card').remove();
      syncEmpty('re-actions', 're-actions-empty');
    });
  }

  function addEmailCard(d) {
    d = d || {};
    var $c = $([
      '<div class="re-act-card" data-type="send_email">',
        '<div class="re-act-card-head">📧 Send Email</div>',
        '<div class="re-act-grid-2">',
          '<div class="re-field-group"><label>To <small>(email or {field_key})</small></label>',
            '<input type="text" class="re-a-to" placeholder="manager@example.com or {email}"></div>',
          '<div class="re-field-group"><label>Subject</label>',
            '<input type="text" class="re-a-subj" placeholder="New submission: {name}"></div>',
        '</div>',
        '<div class="re-field-group"><label>Format</label>',
          '<label class="re-html-row"><input type="checkbox" class="re-a-html"> Send as HTML email (supports tags &amp; styles)</label></div>',
        '<div class="re-field-group"><label>Body <small>(use {field_key} tokens)</small></label>',
          '<textarea class="re-a-body" rows="4" placeholder="Hello {name},&#10;&#10;Message: {message}"></textarea></div>',
        '<div class="re-act-grid-3">',
          '<div class="re-field-group"><label>From Name <small>(optional)</small></label>',
            '<input type="text" class="re-a-from-name" placeholder="My Website"></div>',
          '<div class="re-field-group"><label>From Email <small>(optional)</small></label>',
            '<input type="email" class="re-a-from-email" placeholder="noreply@example.com"></div>',
          '<div class="re-field-group"><label>CC <small>(optional)</small></label>',
            '<input type="email" class="re-a-cc" placeholder="cc@example.com"></div>',
        '</div>',
      '</div>',
    ].join(''));
    $c.find('.re-act-card-head').append(rmBtn());
    $c.find('.re-a-to').val(d.to || '');
    $c.find('.re-a-subj').val(d.subject || '');
    $c.find('.re-a-html').prop('checked', !!d.html);
    $c.find('.re-a-body').val(d.body || '');
    $c.find('.re-a-from-name').val(d.from_name || '');
    $c.find('.re-a-from-email').val(d.from_email || '');
    $c.find('.re-a-cc').val(d.cc || '');
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

  $('[data-add-action="send_email"]').on('click',   function () { addEmailCard(); });
  $('[data-add-action="whatsapp"]').on('click',     function () { addWhatsappCard(); });
  $('[data-add-action="http_request"]').on('click', function () { addHttpCard(); });

  /* ── Populate existing data ── */
  var existingConds   = <?php echo wp_json_encode( $editing ? (array) $editing->conditions : array() ); ?>;
  var existingActions = <?php echo wp_json_encode( $editing ? (array) $editing->actions    : array() ); ?>;

  existingConds.forEach(function (c) { addCondRow(c.field, c.operator, c.value); });
  existingActions.forEach(function (a) {
    if      (a.type === 'send_email')   addEmailCard(a);
    else if (a.type === 'whatsapp')     addWhatsappCard(a);
    else if (a.type === 'http_request') addHttpCard(a);
  });

  /* ── Serialize on submit ── */
  $('#re-form').on('submit', function () {
    var conds = [];
    $('.re-cond-row').each(function () {
      var f = $(this).find('.re-c-field').val().trim();
      if (!f) return;
      conds.push({ field: f, operator: $(this).find('.re-c-op').val(), value: $(this).find('.re-c-val:visible').val().trim() });
    });
    $('#re-cond-json').val(JSON.stringify(conds));

    var acts = [];
    $('.re-act-card').each(function () {
      var type = $(this).data('type');
      if (type === 'send_email') {
        acts.push({
          type: 'send_email',
          to:         $(this).find('.re-a-to').val().trim(),
          subject:    $(this).find('.re-a-subj').val().trim(),
          body:       $(this).find('.re-a-body').val(),
          html:       $(this).find('.re-a-html').is(':checked') ? 1 : 0,
          from_name:  $(this).find('.re-a-from-name').val().trim(),
          from_email: $(this).find('.re-a-from-email').val().trim(),
          cc:         $(this).find('.re-a-cc').val().trim(),
        });
      } else if (type === 'whatsapp') {
        acts.push({
          type:       'whatsapp',
          api_url:    $(this).find('.re-a-wa-url').val().trim(),
          auth_token: $(this).find('.re-a-wa-token').val().trim(),
          to_phone:   $(this).find('.re-a-wa-phone').val().trim(),
          message:    $(this).find('.re-a-wa-msg').val().trim(),
          body_json:  $(this).find('.re-a-wa-json').val().trim(),
        });
      } else if (type === 'http_request') {
        acts.push({
          type:         'http_request',
          url:          $(this).find('.re-a-http-url').val().trim(),
          method:       $(this).find('.re-a-http-method').val(),
          auth_type:    $(this).find('.re-a-http-authtype').val(),
          auth_value:   $(this).find('.re-a-http-authval').val().trim(),
          headers:      $(this).find('.re-a-http-headers').val().trim(),
          content_type: $(this).find('.re-a-http-ct').val(),
          body:         $(this).find('.re-a-http-body').val().trim(),
        });
      }
    });
    $('#re-act-json').val(JSON.stringify(acts));
  });

});
</script>
