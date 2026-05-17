<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

// ── POST handlers ─────────────────────────────────────────────────────────────

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$post_action = sanitize_key( $_POST['_action'] ?? '' );

	if ( $post_action === 'save_rule' ) {
		if ( ! wp_verify_nonce( $_POST['ahrc_nonce'] ?? '', 'ahrc_save_rule' ) ) wp_die( 'Security check failed.' );

		$rule_id  = (int) ( $_POST['rule_id'] ?? 0 );
		$saved_id = AH_Rules_Engine::save_rule( $_POST, $rule_id );

		if ( $saved_id ) {
			$conditions  = array();
			$cond_fields = array_values( (array) ( $_POST['cond_field'] ?? array() ) );
			$cond_ops    = array_values( (array) ( $_POST['cond_operator'] ?? array() ) );
			$cond_vals   = array_values( (array) ( $_POST['cond_value'] ?? array() ) );
			foreach ( $cond_fields as $i => $field ) {
				if ( ! $field ) continue;
				$conditions[] = array(
					'field'    => $field,
					'operator' => $cond_ops[ $i ] ?? 'equals',
					'value'    => $cond_vals[ $i ] ?? '',
				);
			}
			AH_Rules_Engine::save_conditions( $saved_id, $conditions );

			$actions     = array();
			$act_types   = array_values( (array) ( $_POST['act_type'] ?? array() ) );
			$act_to      = array_values( (array) ( $_POST['act_email_to'] ?? array() ) );
			$act_subj    = array_values( (array) ( $_POST['act_email_subject'] ?? array() ) );
			$act_body    = array_values( (array) ( $_POST['act_email_body'] ?? array() ) );
			$act_fn      = array_values( (array) ( $_POST['act_email_from_name'] ?? array() ) );
			$act_fe      = array_values( (array) ( $_POST['act_email_from_email'] ?? array() ) );
			$act_wurl    = array_values( (array) ( $_POST['act_webhook_url'] ?? array() ) );
			$act_wmethod = array_values( (array) ( $_POST['act_webhook_method'] ?? array() ) );
			$act_note    = array_values( (array) ( $_POST['act_note'] ?? array() ) );
			foreach ( $act_types as $i => $type ) {
				if ( ! $type ) continue;
				$action = array( 'action_type' => $type );
				switch ( $type ) {
					case 'send_email':
						$action['to']         = $act_to[ $i ] ?? '';
						$action['subject']    = $act_subj[ $i ] ?? '';
						$action['body']       = wp_kses_post( $act_body[ $i ] ?? '' );
						$action['from_name']  = $act_fn[ $i ] ?? '';
						$action['from_email'] = $act_fe[ $i ] ?? '';
						break;
					case 'webhook':
						$action['url']    = $act_wurl[ $i ] ?? '';
						$action['method'] = $act_wmethod[ $i ] ?? 'POST';
						break;
					case 'internal_note':
						$action['note'] = $act_note[ $i ] ?? '';
						break;
				}
				$actions[] = $action;
			}
			AH_Rules_Engine::save_actions( $saved_id, $actions );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=ah-rules&saved=1' ) );
		exit;
	}

	if ( $post_action === 'delete_rule' ) {
		$del_id = (int) ( $_POST['rule_id'] ?? 0 );
		if ( ! wp_verify_nonce( $_POST['ahrc_nonce'] ?? '', 'ahrc_delete_' . $del_id ) ) wp_die( 'Security check failed.' );
		AH_Rules_Engine::delete_rule( $del_id );
		wp_safe_redirect( admin_url( 'admin.php?page=ah-rules&deleted=1' ) );
		exit;
	}

	if ( $post_action === 'toggle_status' ) {
		$tog_id = (int) ( $_POST['rule_id'] ?? 0 );
		if ( ! wp_verify_nonce( $_POST['ahrc_nonce'] ?? '', 'ahrc_toggle_' . $tog_id ) ) wp_die( 'Security check failed.' );
		AH_Rules_Engine::toggle_status( $tog_id );
		wp_safe_redirect( admin_url( 'admin.php?page=ah-rules' ) );
		exit;
	}
}

// ── View state ────────────────────────────────────────────────────────────────

$tab         = sanitize_key( $_GET['tab'] ?? 'rules' );
$view_action = sanitize_key( $_GET['action'] ?? '' );
$edit_id     = (int) ( $_GET['id'] ?? 0 );

$edit_rule       = null;
$edit_conditions = array();
$edit_actions    = array();

if ( in_array( $view_action, array( 'edit', 'new' ), true ) ) {
	if ( $view_action === 'edit' && $edit_id ) {
		$edit_rule       = AH_Rules_Engine::get_rule( $edit_id );
		$edit_conditions = AH_Rules_Engine::get_conditions( $edit_id );
		$edit_actions    = AH_Rules_Engine::get_actions( $edit_id );
		foreach ( $edit_actions as &$ea ) {
			if ( is_string( $ea->config ) ) $ea->config = json_decode( $ea->config );
		}
		unset( $ea );
	}
}

// Suggested field names for the datalist — user can type anything else
$field_suggestions = array(
	'enquiry_type'    => 'Enquiry Type',
	'full_name'       => 'Full Name',
	'email'           => 'Email',
	'phone'           => 'Phone',
	'message'         => 'Message',
	'short_quote'     => 'Short Quote',
	'attachment_name' => 'Attachment Filename',
	'page_url'        => 'Page URL',
	'user_agent'      => 'User Agent',
);

// Suggested trigger events — user can type anything else
$trigger_suggestions = array(
	'contact_submitted'      => 'Contact Form Submitted',
	'consultation_submitted' => 'Consultation Form Submitted',
	'valuation_submitted'    => 'Valuation Form Submitted',
);

$condition_operators = array(
	'equals'       => 'equals',
	'not_equals'   => 'does not equal',
	'contains'     => 'contains',
	'not_contains' => 'does not contain',
	'starts_with'  => 'starts with',
	'ends_with'    => 'ends with',
	'is_empty'     => 'is empty',
	'is_not_empty' => 'is not empty',
);
?>

<div class="wrap ah-wrap">
<h1>
	<span class="dashicons dashicons-randomize"></span>
	<?php esc_html_e( 'Rules Controller', 'ah-theme' ); ?>
	<?php if ( ! in_array( $view_action, array( 'edit', 'new' ), true ) ) : ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules&action=new' ) ); ?>"
		   class="ah-btn ah-btn-primary" style="margin-left:auto;font-size:13px">+ New Rule</a>
	<?php endif; ?>
</h1>

<?php if ( isset( $_GET['saved'] ) )   : ?><div class="ah-notice ah-notice-success">Rule saved.</div><?php endif; ?>
<?php if ( isset( $_GET['deleted'] ) ) : ?><div class="ah-notice ah-notice-success">Rule deleted.</div><?php endif; ?>

<?php if ( ! in_array( $view_action, array( 'edit', 'new' ), true ) ) : ?>
<div class="ah-tabs">
	<a class="ah-tab <?php echo $tab !== 'logs' ? 'active' : ''; ?>"
	   href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules&tab=rules' ) ); ?>">Rules</a>
	<a class="ah-tab <?php echo $tab === 'logs' ? 'active' : ''; ?>"
	   href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules&tab=logs' ) ); ?>">Execution Logs</a>
</div>
<?php endif; ?>

<?php
// ═══════════════════════════════════════════════════════════════════════════
// EDIT / CREATE form
// ═══════════════════════════════════════════════════════════════════════════
if ( in_array( $view_action, array( 'edit', 'new' ), true ) ) :
	$is_edit = ( $view_action === 'edit' && $edit_rule );
?>
<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules' ) ); ?>"
   class="ah-btn ah-btn-secondary ah-btn-sm" style="display:inline-flex;margin-bottom:14px">&larr; Back to Rules</a>

<!-- datalists for free-type fields -->
<datalist id="ahrc-field-list">
	<?php foreach ( $field_suggestions as $fv => $fl ) : ?>
	<option value="<?php echo esc_attr( $fv ); ?>"><?php echo esc_html( $fl ); ?></option>
	<?php endforeach; ?>
</datalist>
<datalist id="ahrc-trigger-list">
	<?php foreach ( $trigger_suggestions as $tv => $tl ) : ?>
	<option value="<?php echo esc_attr( $tv ); ?>"><?php echo esc_html( $tl ); ?></option>
	<?php endforeach; ?>
</datalist>

<div class="ah-card">
	<div class="ah-card-header">
		<h2><?php echo $is_edit ? 'Edit Rule: ' . esc_html( $edit_rule->name ) : 'Create New Rule'; ?></h2>
	</div>

	<form id="ahrc-form" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules' ) ); ?>">
		<?php wp_nonce_field( 'ahrc_save_rule', 'ahrc_nonce' ); ?>
		<input type="hidden" name="_action" value="save_rule">
		<input type="hidden" name="rule_id" value="<?php echo $is_edit ? (int) $edit_rule->id : 0; ?>">

		<!-- Basic settings -->
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:820px">
			<div class="ah-form-row" style="grid-column:1/-1">
				<label>Rule Name *</label>
				<input type="text" name="name" required value="<?php echo esc_attr( $edit_rule->name ?? '' ); ?>">
			</div>
			<div class="ah-form-row" style="grid-column:1/-1">
				<label>Description <small style="font-weight:400;color:var(--ah-muted)">(optional)</small></label>
				<textarea name="description" rows="2"><?php echo esc_textarea( $edit_rule->description ?? '' ); ?></textarea>
			</div>

			<div class="ah-form-row">
				<label>Trigger Event
					<span style="font-weight:400;font-size:11px;color:var(--ah-muted);margin-left:6px">
						— type any trigger name or pick from the list
					</span>
				</label>
				<input type="text" name="trigger_event"
					list="ahrc-trigger-list"
					value="<?php echo esc_attr( $edit_rule->trigger_event ?? 'contact_submitted' ); ?>"
					placeholder="e.g. contact_submitted">
			</div>

			<div class="ah-form-row">
				<label>Condition Logic</label>
				<div style="display:flex;gap:20px;padding-top:8px">
					<label style="font-weight:400;display:flex;align-items:center;gap:6px">
						<input type="radio" name="condition_logic" value="all"
							<?php checked( $edit_rule->condition_logic ?? 'all', 'all' ); ?>> ALL must match
					</label>
					<label style="font-weight:400;display:flex;align-items:center;gap:6px">
						<input type="radio" name="condition_logic" value="any"
							<?php checked( $edit_rule->condition_logic ?? 'all', 'any' ); ?>> ANY must match
					</label>
				</div>
			</div>

			<div class="ah-form-row">
				<label>Status</label>
				<div style="display:flex;gap:20px;padding-top:8px">
					<label style="font-weight:400;display:flex;align-items:center;gap:6px">
						<input type="radio" name="status" value="active"
							<?php checked( $edit_rule->status ?? 'active', 'active' ); ?>> Active
					</label>
					<label style="font-weight:400;display:flex;align-items:center;gap:6px">
						<input type="radio" name="status" value="inactive"
							<?php checked( $edit_rule->status ?? 'active', 'inactive' ); ?>> Inactive
					</label>
				</div>
			</div>
		</div>

		<!-- ── Conditions ── -->
		<hr style="margin:22px 0;border:none;border-top:1px solid var(--ah-border)">
		<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
			<div>
				<strong style="font-size:14px">Conditions</strong>
				<span style="color:var(--ah-muted);font-size:12px;margin-left:8px">
					No conditions = rule always fires · Field name can be anything from the form data
				</span>
			</div>
			<button type="button" id="ahrc-add-cond" class="ah-btn ah-btn-secondary ah-btn-sm">+ Add Condition</button>
		</div>
		<div id="ahrc-conditions">
			<?php foreach ( $edit_conditions as $cond ) :
				$cobj = (object) $cond; ?>
			<div class="ah-repeater-item ahrc-cond-row" style="padding:10px 44px 10px 14px">
				<div style="display:grid;grid-template-columns:1fr 180px 1fr;gap:8px;align-items:center">
					<input type="text" name="cond_field[]"
						list="ahrc-field-list"
						value="<?php echo esc_attr( $cobj->field ?? '' ); ?>"
						placeholder="Field name (type or pick)">
					<select name="cond_operator[]">
						<?php foreach ( $condition_operators as $ov => $ol ) : ?>
						<option value="<?php echo esc_attr( $ov ); ?>" <?php selected( $cobj->operator ?? 'equals', $ov ); ?>><?php echo esc_html( $ol ); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="text" name="cond_value[]"
						value="<?php echo esc_attr( $cobj->value ?? '' ); ?>"
						placeholder="Value to match">
				</div>
				<button type="button" class="ah-repeater-remove ahrc-remove-cond">✕</button>
			</div>
			<?php endforeach; ?>
		</div>

		<!-- ── Actions ── -->
		<hr style="margin:22px 0;border:none;border-top:1px solid var(--ah-border)">
		<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
			<strong style="font-size:14px">Actions</strong>
			<button type="button" id="ahrc-add-action" class="ah-btn ah-btn-secondary ah-btn-sm">+ Add Action</button>
		</div>
		<div id="ahrc-actions">
			<?php foreach ( $edit_actions as $ai => $act ) :
				$acfg    = $act->config ?? (object) array();
				$body_id = 'ahrc_body_' . $ai; ?>
			<div class="ah-repeater-item ahrc-action-row" style="padding-right:44px">
				<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
					<label style="font-size:12px;font-weight:600;color:var(--ah-muted);white-space:nowrap">Action type</label>
					<select name="act_type[]" class="ahrc-action-type" style="font-weight:600">
						<option value="">-- Select --</option>
						<option value="send_email"    <?php selected( $act->action_type, 'send_email' ); ?>>Send Email</option>
						<option value="webhook"       <?php selected( $act->action_type, 'webhook' ); ?>>Call Webhook</option>
						<option value="internal_note" <?php selected( $act->action_type, 'internal_note' ); ?>>Add Internal Note</option>
					</select>
				</div>

				<!-- Send Email config -->
				<div class="ahrc-cfg ahrc-cfg-send_email" style="display:<?php echo $act->action_type === 'send_email' ? 'block' : 'none'; ?>">
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
						<div class="ah-form-row" style="margin-bottom:8px">
							<label>To <small style="font-weight:400">email address or {{email}}</small></label>
							<input type="text" name="act_email_to[]" value="<?php echo esc_attr( $acfg->to ?? '' ); ?>">
						</div>
						<div class="ah-form-row" style="margin-bottom:8px">
							<label>Subject</label>
							<input type="text" name="act_email_subject[]" value="<?php echo esc_attr( $acfg->subject ?? '' ); ?>">
						</div>
						<div class="ah-form-row" style="margin-bottom:8px">
							<label>From Name <small style="font-weight:400">(optional)</small></label>
							<input type="text" name="act_email_from_name[]" value="<?php echo esc_attr( $acfg->from_name ?? '' ); ?>">
						</div>
						<div class="ah-form-row" style="margin-bottom:8px">
							<label>From Email <small style="font-weight:400">(optional)</small></label>
							<input type="text" name="act_email_from_email[]" value="<?php echo esc_attr( $acfg->from_email ?? '' ); ?>">
						</div>
						<div class="ah-form-row" style="margin-bottom:0;grid-column:1/-1">
							<label>Email Body
								<small style="font-weight:400">
									— use <code>{{full_name}}</code> <code>{{email}}</code> <code>{{enquiry_type}}</code> <code>{{message}}</code> <code>{{phone}}</code> etc.
								</small>
							</label>
							<?php
							wp_editor(
								$acfg->body ?? '',
								$body_id,
								array(
									'textarea_name' => 'act_email_body[]',
									'media_buttons' => false,
									'teeny'         => false,
									'editor_height' => 200,
									'tinymce'       => array(
										'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,removeformat,|,undo,redo',
										'toolbar2' => '',
									),
								)
							);
							?>
						</div>
					</div>
				</div>

				<!-- Webhook config -->
				<div class="ahrc-cfg ahrc-cfg-webhook" style="display:<?php echo $act->action_type === 'webhook' ? 'block' : 'none'; ?>">
					<div style="display:grid;grid-template-columns:1fr 110px;gap:10px">
						<div class="ah-form-row" style="margin-bottom:0">
							<label>Webhook URL</label>
							<input type="text" name="act_webhook_url[]" value="<?php echo esc_attr( $acfg->url ?? '' ); ?>">
						</div>
						<div class="ah-form-row" style="margin-bottom:0">
							<label>Method</label>
							<select name="act_webhook_method[]">
								<option value="POST" <?php selected( $acfg->method ?? 'POST', 'POST' ); ?>>POST</option>
								<option value="GET"  <?php selected( $acfg->method ?? 'POST', 'GET'  ); ?>>GET</option>
							</select>
						</div>
					</div>
				</div>

				<!-- Internal Note config -->
				<div class="ahrc-cfg ahrc-cfg-internal_note" style="display:<?php echo $act->action_type === 'internal_note' ? 'block' : 'none'; ?>">
					<div class="ah-form-row" style="margin-bottom:0">
						<label>Note <small style="font-weight:400">{{variables}} supported</small></label>
						<textarea name="act_note[]" rows="3"><?php echo esc_textarea( $acfg->note ?? '' ); ?></textarea>
					</div>
				</div>

				<button type="button" class="ah-repeater-remove ahrc-remove-action">✕</button>
			</div>
			<?php endforeach; ?>
		</div>

		<hr style="margin:22px 0;border:none;border-top:1px solid var(--ah-border)">
		<div style="display:flex;gap:10px">
			<button type="submit" class="ah-btn ah-btn-primary">Save Rule</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules' ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
		</div>
	</form>
</div>

<?php
// ═══════════════════════════════════════════════════════════════════════════
// RULES LIST
// ═══════════════════════════════════════════════════════════════════════════
elseif ( $tab !== 'logs' ) :
	$rules = AH_Rules_Engine::get_all_rules();
?>
<?php if ( empty( $rules ) ) : ?>
<div class="ah-card" style="text-align:center;padding:60px 20px;color:var(--ah-muted)">
	<span class="dashicons dashicons-randomize" style="font-size:40px;width:40px;height:40px;color:var(--ah-border);margin-bottom:12px"></span>
	<p style="font-size:15px;font-weight:600;margin:0 0 8px">No rules yet</p>
	<p style="margin:0 0 20px;font-size:13px">Rules fire automatically when a trigger event happens — send an email, call a webhook, or add an internal note.</p>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules&action=new' ) ); ?>" class="ah-btn ah-btn-primary">Create First Rule</a>
</div>
<?php else : ?>
<div class="ah-table-wrap">
<table class="ah-table">
	<thead>
		<tr>
			<th>Rule</th>
			<th>Trigger</th>
			<th style="text-align:center">Conditions</th>
			<th style="text-align:center">Actions</th>
			<th style="text-align:center">Logic</th>
			<th style="text-align:center">Status</th>
			<th>Options</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $rules as $rule ) :
		$cond_count   = count( AH_Rules_Engine::get_conditions( (int) $rule->id ) );
		$action_count = count( AH_Rules_Engine::get_actions( (int) $rule->id ) );
		$badge_cls    = $rule->status === 'active' ? 'ah-badge-active' : 'ah-badge-inactive';
	?>
	<tr>
		<td>
			<strong><?php echo esc_html( $rule->name ); ?></strong>
			<?php if ( $rule->description ) : ?>
			<div style="font-size:12px;color:var(--ah-muted);margin-top:2px"><?php echo esc_html( $rule->description ); ?></div>
			<?php endif; ?>
		</td>
		<td><code style="background:var(--ah-bg-light);padding:2px 6px;border-radius:4px;font-size:12px"><?php echo esc_html( $rule->trigger_event ); ?></code></td>
		<td style="text-align:center"><?php echo (int) $cond_count ? (int) $cond_count : '<span style="color:var(--ah-muted)">—</span>'; ?></td>
		<td style="text-align:center"><?php echo (int) $action_count; ?></td>
		<td style="text-align:center">
			<span class="ah-badge ah-badge-new"><?php echo esc_html( strtoupper( $rule->condition_logic ) ); ?></span>
		</td>
		<td style="text-align:center">
			<span class="ah-badge <?php echo esc_attr( $badge_cls ); ?>"><?php echo esc_html( ucfirst( $rule->status ) ); ?></span>
		</td>
		<td class="row-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-rules&action=edit&id=' . (int) $rule->id ) ); ?>"
			   class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>

			<form method="post" style="display:inline" onsubmit="return confirm('Toggle status?')">
				<?php wp_nonce_field( 'ahrc_toggle_' . (int) $rule->id, 'ahrc_nonce' ); ?>
				<input type="hidden" name="_action" value="toggle_status">
				<input type="hidden" name="rule_id" value="<?php echo (int) $rule->id; ?>">
				<button type="submit" class="ah-btn ah-btn-secondary ah-btn-sm">
					<?php echo $rule->status === 'active' ? 'Deactivate' : 'Activate'; ?>
				</button>
			</form>

			<form method="post" style="display:inline" onsubmit="return confirm('Delete this rule and all its logs?')">
				<?php wp_nonce_field( 'ahrc_delete_' . (int) $rule->id, 'ahrc_nonce' ); ?>
				<input type="hidden" name="_action" value="delete_rule">
				<input type="hidden" name="rule_id" value="<?php echo (int) $rule->id; ?>">
				<button type="submit" class="ah-btn ah-btn-danger ah-btn-sm">Delete</button>
			</form>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
</div>
<?php endif; ?>

<?php
// ═══════════════════════════════════════════════════════════════════════════
// LOGS
// ═══════════════════════════════════════════════════════════════════════════
else :
	$logs = AH_Rules_Engine::get_logs( 0, 200 );
?>
<?php if ( empty( $logs ) ) : ?>
<div class="ah-card" style="text-align:center;padding:60px 20px;color:var(--ah-muted)">
	<span class="dashicons dashicons-list-view" style="font-size:40px;width:40px;height:40px;color:var(--ah-border);margin-bottom:12px"></span>
	<p style="font-size:15px;font-weight:600;margin:0 0 6px">No logs yet</p>
	<p style="margin:0;font-size:13px">Logs appear here each time a rule fires against a form submission.</p>
</div>
<?php else : ?>
<div class="ah-table-wrap">
<table class="ah-table">
	<thead>
		<tr>
			<th>Date</th>
			<th>Rule</th>
			<th style="text-align:center">Result</th>
			<th>Action Notes</th>
			<th>Trigger Data</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $logs as $log ) :
		$td  = is_string( $log->trigger_data ) ? json_decode( $log->trigger_data, true ) : (array) $log->trigger_data;
		$td  = is_array( $td ) ? $td : array();
		$rc  = array( 'success' => 'ah-badge-active', 'partial' => 'ah-badge-draft', 'failed' => 'ah-badge-spam' );
		$lid = 'ahrc-td-' . (int) $log->id;
	?>
	<tr>
		<td style="white-space:nowrap;font-size:12px;color:var(--ah-muted)"><?php echo esc_html( $log->created_at ); ?></td>
		<td><strong><?php echo esc_html( $log->rule_name ?? '—' ); ?></strong></td>
		<td style="text-align:center">
			<span class="ah-badge <?php echo esc_attr( $rc[ $log->result ] ?? 'ah-badge-inactive' ); ?>">
				<?php echo esc_html( ucfirst( $log->result ) ); ?>
			</span>
		</td>
		<td style="font-size:12px;color:var(--ah-muted)"><?php echo esc_html( $log->notes ?? '' ); ?></td>
		<td>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm"
				onclick="var el=document.getElementById('<?php echo esc_js( $lid ); ?>');el.style.display=el.style.display==='none'?'block':'none'">
				View
			</button>
			<pre id="<?php echo esc_attr( $lid ); ?>"
				style="display:none;margin-top:6px;background:var(--ah-bg-light);border:1px solid var(--ah-border);border-radius:6px;padding:10px;font-size:11px;max-width:360px;overflow:auto;white-space:pre-wrap"><?php
				$safe = array();
				foreach ( $td as $k => $v ) {
					if ( 'user_agent' === $k ) continue;
					$safe[ esc_html( $k ) ] = esc_html( (string) $v );
				}
				echo esc_html( wp_json_encode( $safe, JSON_PRETTY_PRINT ) );
			?></pre>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
</div>
<?php endif; ?>

<?php endif; ?>
</div><!-- .ah-wrap -->

<?php if ( in_array( $view_action, array( 'edit', 'new' ), true ) ) : ?>
<!-- ── Datalists (outside form so they work globally) ── -->
<datalist id="ahrc-field-list-tpl">
	<?php foreach ( $field_suggestions as $fv => $fl ) : ?>
	<option value="<?php echo esc_attr( $fv ); ?>"><?php echo esc_html( $fl ); ?></option>
	<?php endforeach; ?>
</datalist>

<!-- ── Condition row template ── -->
<template id="ahrc-cond-tpl">
<div class="ah-repeater-item ahrc-cond-row" style="padding:10px 44px 10px 14px">
	<div style="display:grid;grid-template-columns:1fr 180px 1fr;gap:8px;align-items:center">
		<input type="text" name="cond_field[]" list="ahrc-field-list" placeholder="Field name (type or pick)">
		<select name="cond_operator[]">
			<?php foreach ( $condition_operators as $ov => $ol ) : ?>
			<option value="<?php echo esc_attr( $ov ); ?>"><?php echo esc_html( $ol ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="text" name="cond_value[]" placeholder="Value to match">
	</div>
	<button type="button" class="ah-repeater-remove ahrc-remove-cond">✕</button>
</div>
</template>

<!-- ── Action row template (body = plain textarea; JS will init TinyMCE) ── -->
<template id="ahrc-action-tpl">
<div class="ah-repeater-item ahrc-action-row" style="padding-right:44px">
	<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
		<label style="font-size:12px;font-weight:600;color:var(--ah-muted);white-space:nowrap">Action type</label>
		<select name="act_type[]" class="ahrc-action-type" style="font-weight:600">
			<option value="">-- Select --</option>
			<option value="send_email">Send Email</option>
			<option value="webhook">Call Webhook</option>
			<option value="internal_note">Add Internal Note</option>
		</select>
	</div>

	<div class="ahrc-cfg ahrc-cfg-send_email" style="display:none">
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
			<div class="ah-form-row" style="margin-bottom:8px">
				<label>To <small style="font-weight:400">email or {{email}}</small></label>
				<input type="text" name="act_email_to[]">
			</div>
			<div class="ah-form-row" style="margin-bottom:8px">
				<label>Subject</label>
				<input type="text" name="act_email_subject[]">
			</div>
			<div class="ah-form-row" style="margin-bottom:8px">
				<label>From Name <small style="font-weight:400">(optional)</small></label>
				<input type="text" name="act_email_from_name[]">
			</div>
			<div class="ah-form-row" style="margin-bottom:8px">
				<label>From Email <small style="font-weight:400">(optional)</small></label>
				<input type="text" name="act_email_from_email[]">
			</div>
			<div class="ah-form-row" style="margin-bottom:0;grid-column:1/-1">
				<label>Email Body
					<small style="font-weight:400">— <code>{{full_name}}</code> <code>{{email}}</code> <code>{{message}}</code> etc.</small>
				</label>
				<textarea name="act_email_body[]" class="ahrc-rich-body" rows="5" style="width:100%;max-width:100%"></textarea>
			</div>
		</div>
	</div>

	<div class="ahrc-cfg ahrc-cfg-webhook" style="display:none">
		<div style="display:grid;grid-template-columns:1fr 110px;gap:10px">
			<div class="ah-form-row" style="margin-bottom:0">
				<label>Webhook URL</label>
				<input type="text" name="act_webhook_url[]">
			</div>
			<div class="ah-form-row" style="margin-bottom:0">
				<label>Method</label>
				<select name="act_webhook_method[]">
					<option value="POST">POST</option>
					<option value="GET">GET</option>
				</select>
			</div>
		</div>
	</div>

	<div class="ahrc-cfg ahrc-cfg-internal_note" style="display:none">
		<div class="ah-form-row" style="margin-bottom:0">
			<label>Note <small style="font-weight:400">{{variables}} supported</small></label>
			<textarea name="act_note[]" rows="3"></textarea>
		</div>
	</div>

	<button type="button" class="ah-repeater-remove ahrc-remove-action">✕</button>
</div>
</template>

<script>
(function () {
	'use strict';

	var editorSeq = <?php echo count( $edit_actions ); ?>;

	// ── TinyMCE helpers ───────────────────────────────────────────────────────
	function initTinyMCE(textarea) {
		if (!textarea || !window.wp || !wp.editor) return;
		var uid = 'ahrc_body_dyn_' + (++editorSeq);
		textarea.id = uid;
		wp.editor.initialize(uid, {
			tinymce: {
				wpautop: true,
				plugins: 'lists paste link charmap',
				toolbar1: 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,removeformat,|,undo,redo',
				toolbar2: '',
				height: 200,
			},
			quicktags: false,
			mediaButtons: false,
		});
	}

	function removeTinyMCE(row) {
		if (!window.wp || !wp.editor) return;
		row.querySelectorAll('.ahrc-rich-body').forEach(function (ta) {
			if (ta.id) {
				try { wp.editor.remove(ta.id); } catch (e) {}
			}
		});
	}

	// ── Condition rows ────────────────────────────────────────────────────────
	var addCond  = document.getElementById('ahrc-add-cond');
	var condWrap = document.getElementById('ahrc-conditions');
	var condTpl  = document.getElementById('ahrc-cond-tpl');

	if (addCond && condWrap && condTpl) {
		addCond.addEventListener('click', function () {
			condWrap.appendChild(condTpl.content.cloneNode(true));
		});
		condWrap.addEventListener('click', function (e) {
			if (e.target.classList.contains('ahrc-remove-cond')) {
				e.target.closest('.ahrc-cond-row').remove();
			}
		});
	}

	// ── Action rows ───────────────────────────────────────────────────────────
	var addAct  = document.getElementById('ahrc-add-action');
	var actWrap = document.getElementById('ahrc-actions');
	var actTpl  = document.getElementById('ahrc-action-tpl');

	function showCfg(row, type) {
		row.querySelectorAll('.ahrc-cfg').forEach(function (el) { el.style.display = 'none'; });
		if (type) {
			var cfg = row.querySelector('.ahrc-cfg-' + type);
			if (cfg) cfg.style.display = 'block';
		}
	}

	function bindActionType(row) {
		var sel = row.querySelector('.ahrc-action-type');
		if (!sel) return;
		showCfg(row, sel.value);
		sel.addEventListener('change', function () {
			showCfg(row, this.value);
			// If switching to send_email, init TinyMCE on the textarea
			if (this.value === 'send_email') {
				var ta = row.querySelector('.ahrc-rich-body');
				if (ta && !ta.id) initTinyMCE(ta);
			}
		});
	}

	if (addAct && actWrap && actTpl) {
		addAct.addEventListener('click', function () {
			var frag = actTpl.content.cloneNode(true);
			actWrap.appendChild(frag);
			bindActionType(actWrap.lastElementChild);
		});
		actWrap.addEventListener('click', function (e) {
			if (e.target.classList.contains('ahrc-remove-action')) {
				var row = e.target.closest('.ahrc-action-row');
				removeTinyMCE(row);
				row.remove();
			}
		});
		actWrap.querySelectorAll('.ahrc-action-row').forEach(bindActionType);
	}

	// ── Sync TinyMCE to textareas before submit ───────────────────────────────
	var form = document.getElementById('ahrc-form');
	if (form) {
		form.addEventListener('submit', function () {
			if (window.tinyMCE) tinyMCE.triggerSave();
		});
	}
}());
</script>
<?php endif; ?>
