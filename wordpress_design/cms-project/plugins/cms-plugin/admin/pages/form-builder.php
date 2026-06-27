<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

AH_Form_Builder::install_tables();
AH_Form_Builder::maybe_upgrade_submissions();

$content_tax_m = new AH_Content_Taxonomy_Model();
$notice        = '';
$active_tab    = sanitize_key( isset( $_GET['tab'] ) ? $_GET['tab'] : 'build' );
$form_id       = (int) ( isset( $_GET['form_id'] ) ? $_GET['form_id'] : 0 );
$sub_status    = sanitize_key( isset( $_GET['sub_status'] ) ? $_GET['sub_status'] : '' );

// ── Handle: create new form ───────────────────────────────────────────────────
if ( isset( $_POST['ah_new_form_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_new_form_nonce'], 'ah_new_form' ) ) wp_die( 'Security.' );
	$new_id = AH_Form_Builder::upsert( 0, array(
		'name'            => sanitize_text_field( isset( $_POST['new_form_name'] ) ? $_POST['new_form_name'] : 'New Form' ),
		'success_message' => 'Thank you! We will get back to you shortly.',
		'disable_rules'   => 0,
	) );
	AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $new_id, 'tab' => 'build' ), admin_url( 'admin.php' ) ) );
}

// ── Handle: delete form ───────────────────────────────────────────────────────
if ( isset( $_GET['delete_form'] ) && isset( $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_form' ) ) wp_die( 'Security.' );
	$deleted_form_id = (int) $_GET['delete_form'];
	AH_Form_Builder::delete_form( $deleted_form_id );
	$content_tax_m->sync_terms( 'form', $deleted_form_id, array() );
	AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-form-builder' ) );
}

// ── Handle: delete submission ─────────────────────────────────────────────────
if ( isset( $_GET['del_sub'] ) && isset( $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_sub_fb' ) ) wp_die( 'Security.' );
	AH_Form_Builder::delete_submission( (int) $_GET['del_sub'] );
	$notice = 'success:Submission deleted.';
}

// ── Handle: save form settings + fields ──────────────────────────────────────
if ( isset( $_POST['ah_save_form_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_save_form_nonce'], 'ah_save_form' ) ) wp_die( 'Security.' );

	$form_id = AH_Form_Builder::upsert( $form_id, array(
		'name'            => sanitize_text_field( isset( $_POST['form_name'] ) ? $_POST['form_name'] : '' ),
		'success_message' => sanitize_text_field( isset( $_POST['success_message'] ) ? $_POST['success_message'] : '' ),
		'submit_label'    => sanitize_text_field( isset( $_POST['submit_label'] ) ? $_POST['submit_label'] : '' ),
		'status'          => sanitize_key( isset( $_POST['form_status'] ) ? $_POST['form_status'] : 'active' ),
		'disable_rules'   => isset( $_POST['disable_rules'] ) ? 1 : 0,
	) );

	$raw    = wp_unslash( isset( $_POST['fields_json'] ) ? $_POST['fields_json'] : '[]' );
	$parsed = json_decode( $raw, true );
	if ( is_array( $parsed ) ) {
		AH_Form_Builder::save_fields( $form_id, $parsed );
	}
	$content_tax_m->sync_terms( 'form', $form_id, isset( $_POST['taxonomy_ids'] ) ? $_POST['taxonomy_ids'] : array() );

	// Save agreement config.
	AH_Form_Builder::save_agreement( $form_id, array(
		'enabled'    => isset( $_POST['agr_enabled'] ) ? 1 : 0,
		'before'     => isset( $_POST['agr_before'] )     ? wp_unslash( $_POST['agr_before'] )     : '',
		'link_text'  => isset( $_POST['agr_link_text'] )  ? wp_unslash( $_POST['agr_link_text'] )  : '',
		'type'       => isset( $_POST['agr_type'] )       ? wp_unslash( $_POST['agr_type'] )       : 'link',
		'url'        => isset( $_POST['agr_url'] )        ? wp_unslash( $_POST['agr_url'] )        : '',
		'after'      => isset( $_POST['agr_after'] )      ? wp_unslash( $_POST['agr_after'] )      : '',
		'popup_html' => isset( $_POST['agr_popup_html'] ) ? wp_unslash( $_POST['agr_popup_html'] ) : '',
	) );

	AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'build', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
}

if ( isset( $_GET['saved'] ) ) $notice = 'success:Form saved successfully.';

$all_forms   = AH_Form_Builder::get_all();
$current     = $form_id ? AH_Form_Builder::get( $form_id ) : null;
$fields      = $form_id ? AH_Form_Builder::get_fields( $form_id ) : array();
$status_counts = $form_id ? AH_Form_Builder::count_by_status( $form_id ) : array( 'all' => 0, 'new' => 0, 'read' => 0, 'replied' => 0, 'closed' => 0 );
$field_types = array( 'text' => 'Text', 'email' => 'Email', 'tel' => 'Phone / Tel', 'textarea' => 'Textarea', 'select' => 'Dropdown', 'number' => 'Number', 'date' => 'Date', 'url' => 'URL', 'hidden' => 'Hidden Field' );
$agr         = $form_id ? AH_Form_Builder::get_agreement( $form_id ) : array( 'enabled' => 0, 'before' => 'I have read and agree to the', 'link_text' => 'Terms & Conditions', 'type' => 'link', 'url' => '', 'after' => '' );
$admin_nonce = wp_create_nonce( 'ah_admin_nonce' );
?>
<style>
/* ── Layout ── */
.fb-header{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.fb-header h1{margin:0;flex:1}
.fb-forms-bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;margin-bottom:20px}
.fb-forms-bar select{padding:7px 12px;border:1.5px solid #d1d5db;border-radius:7px;font-size:14px;min-width:200px}
/* ── Tabs ── */
.fb-tab-nav{display:flex;gap:2px;border-bottom:2px solid #e5e7eb;margin-bottom:24px}
.fb-tab-nav a{padding:10px 20px;text-decoration:none;font-weight:500;font-size:14px;color:#6b7280;border-radius:6px 6px 0 0;border:1px solid transparent;border-bottom:none;margin-bottom:-2px}
.fb-tab-nav a.on{color:#2563eb;background:#fff;border-color:#e5e7eb;border-bottom-color:#fff}
.fb-tab-nav a:hover:not(.on){color:#1f2937;background:#f9fafb}
/* ── Fields table ── */
#fb-tbl{border-collapse:collapse;width:100%}
#fb-tbl th{font-size:11.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;padding:10px 10px;background:#f9fafb;border-bottom:1px solid #e5e7eb;white-space:nowrap}
#fb-tbl td{padding:8px 10px;border-bottom:1px solid #f3f4f6;vertical-align:middle}
#fb-tbl tr:hover td{background:#fafafa}
#fb-tbl td input[type="text"],#fb-tbl td textarea,#fb-tbl td select{width:100%;font-size:13px;padding:7px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-family:inherit;background:#fff;box-sizing:border-box}
#fb-tbl td input:focus,#fb-tbl td textarea:focus,#fb-tbl td select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.1)}
#fb-tbl td textarea{min-height:66px;resize:vertical}
.fb-drag{cursor:grab;color:#9ca3af;font-size:20px;padding:0 4px;user-select:none;display:block;text-align:center}
.fb-drag:active{cursor:grabbing}
.fb-chk{width:18px!important;height:18px;cursor:pointer;accent-color:#2563eb;transform:scale(1.2)}
.fb-hidden{display:none!important}
.fb-ghost{opacity:.35;background:#eff6ff!important}
/* ── Shortcode pill ── */
.fb-sc-pill{display:inline-flex;align-items:center;gap:8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:7px 12px;font-family:monospace;font-size:13px;color:#334155}
.fb-sc-copy{background:#2563eb;color:#fff;border:none;border-radius:5px;padding:5px 10px;font-size:12px;cursor:pointer;white-space:nowrap}
.fb-sc-copy:hover{background:#1d4ed8}
/* ── Submissions ── */
.sub-status-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;align-items:center}
.sub-status-pill{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:500;cursor:pointer;border:2px solid #e5e7eb;background:#fff;color:#6b7280;text-decoration:none;transition:all .15s}
.sub-status-pill:hover{border-color:#2563eb;color:#2563eb}
.sub-status-pill.active{background:#2563eb;color:#fff;border-color:#2563eb}
.sub-status-pill .badge{display:inline-block;background:rgba(255,255,255,.25);border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700;min-width:18px;text-align:center}
.sub-status-pill:not(.active) .badge{background:#f3f4f6;color:#374151}
.sub-data-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:8px}
.sub-data-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:10px 12px}
.sub-data-lbl{font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.sub-data-val{font-size:13.5px;color:#1f2937;word-break:break-word}
.sub-row-open td{background:#f0f9ff!important}
.sub-meta-box{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:14px 16px;margin-top:14px;display:grid;grid-template-columns:200px 1fr auto;gap:12px;align-items:start}
.sub-status-select{padding:7px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-size:13px;background:#fff}
.sub-notes-ta{width:100%;font-size:13px;padding:7px 10px;border:1.5px solid #d1d5db;border-radius:6px;font-family:inherit;min-height:60px;resize:vertical;box-sizing:border-box}
.sub-notes-ta:focus{outline:none;border-color:#2563eb}
.sub-save-btn{padding:8px 18px;background:#16a34a;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:500;white-space:nowrap}
.sub-save-btn:hover{background:#15803d}
.sub-status-badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:11.5px;font-weight:600;text-transform:capitalize}
.ssb-new{background:#fef3c7;color:#92400e}.ssb-read{background:#dbeafe;color:#1e40af}.ssb-replied{background:#d1fae5;color:#065f46}.ssb-closed{background:#f3f4f6;color:#4b5563}
/* ── Disable-rules flag ── */
.fb-flag-row{display:flex;align-items:center;gap:8px;padding:8px 0;border-top:1px solid #f3f4f6;margin-top:12px}
.fb-flag-row label{margin:0;font-size:13px;color:#374151;cursor:pointer}
</style>

<div class="wrap ah-wrap">

  <?php if ( $notice ) : list( $nt, $nm ) = explode( ':', $notice, 2 ); ?>
    <div class="ah-notice ah-notice-<?php echo 'success' === $nt ? 'success' : 'warning'; ?>"><?php echo esc_html( $nm ); ?></div>
  <?php endif; ?>

  <div class="fb-header">
    <h1><span class="dashicons dashicons-feedback"></span> Form Builder</h1>
    <button class="ah-btn ah-btn-primary" id="fb-new-btn">+ New Form</button>
  </div>

  <!-- New form inline dialog -->
  <div id="fb-new-dialog" style="display:none;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:20px 24px;margin-bottom:20px;max-width:480px;box-shadow:0 4px 20px rgba(0,0,0,.1)">
    <h3 style="margin:0 0 14px">Create New Form</h3>
    <form method="post">
      <?php wp_nonce_field( 'ah_new_form', 'ah_new_form_nonce' ); ?>
      <div class="ah-form-row" style="margin-bottom:14px"><label>Form Name</label><input type="text" name="new_form_name" placeholder="e.g. Contact Form, Quote Request" autofocus></div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="ah-btn ah-btn-primary">Create Form</button>
        <button type="button" class="ah-btn ah-btn-secondary" id="fb-new-cancel">Cancel</button>
      </div>
    </form>
  </div>

  <!-- Form selector bar -->
  <?php if ( $all_forms ) : ?>
  <div class="fb-forms-bar">
    <span style="font-weight:500;font-size:13px;color:#6b7280">SELECT FORM:</span>
    <select id="fb-form-select" onchange="location.href=this.value">
      <option value="">- Choose a form -</option>
      <?php foreach ( $all_forms as $f ) : ?>
        <option value="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $f->id, 'tab' => 'build' ), admin_url( 'admin.php' ) ) ); ?>" <?php selected( $form_id, (int) $f->id ); ?>>
          <?php echo esc_html( $f->name ); ?> (#<?php echo esc_html( $f->id ); ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <?php if ( $current ) : ?>
      <span class="ah-badge <?php echo 'active' === $current->status ? 'ah-badge-active' : 'ah-badge-inactive'; ?>"><?php echo esc_html( ucfirst( $current->status ) ); ?></span>
      <?php $content_tax_m->render_badges( 'form', $form_id ); ?>
      <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-form-builder', 'delete_form' => $form_id ), admin_url( 'admin.php' ) ), 'ah_del_form' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete this form and all its submissions?');">Delete Form</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if ( ! $current && ! empty( $all_forms ) ) : ?>
    <div class="ah-notice ah-notice-warning">Select a form above to edit it, or create a new one.</div>
  <?php elseif ( ! $all_forms ) : ?>
    <div class="ah-notice ah-notice-warning">No forms yet. Click <strong>+ New Form</strong> to create your first form.</div>
  <?php endif; ?>

  <?php if ( $current ) : ?>

  <!-- Shortcode display -->
  <div style="margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <span style="font-size:13px;font-weight:500;color:#374151">Shortcode:</span>
    <span class="fb-sc-pill" id="fb-sc-text">[ah_form id="<?php echo esc_html( $form_id ); ?>"]</span>
    <button class="fb-sc-copy" id="fb-sc-copy">Copy</button>
    <span style="font-size:12px;color:#6b7280">- paste into any page or template to embed this form</span>
  </div>

  <!-- Tab nav -->
  <div class="fb-tab-nav">
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'build' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'build' === $active_tab ? 'on' : ''; ?>">Build Form</a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'submissions' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'submissions' === $active_tab ? 'on' : ''; ?>">
      Submissions <span style="background:#e5e7eb;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700"><?php echo esc_html( $status_counts['all'] ); ?></span>
    </a>
  </div>

  <?php if ( 'build' === $active_tab ) : ?>
  <!-- ════════════════════ Build Form ════════════════════ -->
  <form method="post" id="fb-form">
    <?php wp_nonce_field( 'ah_save_form', 'ah_save_form_nonce' ); ?>
    <input type="hidden" name="fields_json" id="fb-fields-json">

    <!-- Form settings card -->
    <div class="ah-card" style="margin-bottom:20px">
      <div class="ah-card-header"><h2>Form Settings</h2></div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:16px;align-items:end">
        <div class="ah-form-row" style="margin:0"><label>Form Name</label><input type="text" name="form_name" value="<?php echo esc_attr( $current->name ); ?>" required></div>
        <div class="ah-form-row" style="margin:0"><label>Success Message</label><input type="text" name="success_message" value="<?php echo esc_attr( $current->success_message ); ?>"></div>
        <div class="ah-form-row" style="margin:0"><label>Submit Button Label</label><input type="text" name="submit_label" value="<?php echo esc_attr( isset( $current->submit_label ) ? $current->submit_label : '' ); ?>" placeholder="Send Message"></div>
        <div class="ah-form-row" style="margin:0"><label>Status</label>
          <select name="form_status">
            <option value="active" <?php selected( $current->status, 'active' ); ?>>Active</option>
            <option value="inactive" <?php selected( $current->status, 'inactive' ); ?>>Inactive</option>
          </select>
        </div>
      </div>
      <div class="fb-flag-row">
        <input type="checkbox" name="disable_rules" id="fb-disable-rules" class="fb-chk" value="1" <?php checked( ! empty( $current->disable_rules ) ); ?>>
        <label for="fb-disable-rules"><strong>Disable Workflow Manager</strong> - submissions from this form will NOT trigger any automation rules</label>
      </div>
    </div>

    <div class="ah-card" style="margin-bottom:20px">
      <div class="ah-card-header"><h2>Taxonomy Terms</h2></div>
      <?php $content_tax_m->render_picker( 'form', $form_id ); ?>
    </div>

    <!-- ── Agreement / Terms section ── -->
    <div class="ah-card" style="margin-bottom:20px">
      <div class="ah-card-header" style="gap:16px">
        <h2 style="margin:0">Agreement / Terms</h2>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:500;margin-left:auto">
          <input type="checkbox" name="agr_enabled" id="agr_enabled" class="fb-chk" value="1" <?php checked( ! empty( $agr['enabled'] ) ); ?>>
          Show agreement checkbox on this form
        </label>
      </div>
      <div id="agr-body" style="<?php echo empty( $agr['enabled'] ) ? 'display:none;' : ''; ?>padding-top:4px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
          <div class="ah-form-row" style="margin:0">
            <label>Text before the link <small style="font-weight:400">(before checkbox text)</small></label>
            <input type="text" name="agr_before" id="agr_before" value="<?php echo esc_attr( $agr['before'] ); ?>" placeholder="I have read and agree to the">
          </div>
          <div class="ah-form-row" style="margin:0">
            <label>Link / label text</label>
            <input type="text" name="agr_link_text" id="agr_link_text" value="<?php echo esc_attr( $agr['link_text'] ); ?>" placeholder="Terms & Conditions">
          </div>
        </div>
        <div style="display:grid;grid-template-columns:220px 1fr;gap:16px;align-items:start;margin-bottom:16px">
          <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:8px">Display as</label>
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px;margin-bottom:8px">
              <input type="radio" name="agr_type" id="agr_type_link" value="link" <?php checked( $agr['type'], 'link' ); ?>> Link (opens in new tab)
            </label>
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px;margin-bottom:8px">
              <input type="radio" name="agr_type" id="agr_type_iframe" value="iframe" <?php checked( $agr['type'], 'iframe' ); ?>> Inline iframe (embed page)
            </label>
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px">
              <input type="radio" name="agr_type" id="agr_type_popup" value="popup" <?php checked( $agr['type'], 'popup' ); ?>> Popup (custom HTML)
            </label>
          </div>
          <div id="agr-url-wrap" class="ah-form-row" style="margin:0;<?php echo ( 'popup' === $agr['type'] ) ? 'display:none' : ''; ?>">
            <label>URL <small style="font-weight:400">(page to link to or embed)</small></label>
            <input type="text" name="agr_url" id="agr_url" value="<?php echo esc_attr( $agr['url'] ); ?>" placeholder="https://… or /privacy-policy/ or #section">
          </div>
          <div id="agr-popup-wrap" class="ah-form-row" style="margin:0;<?php echo ( 'popup' !== $agr['type'] ) ? 'display:none' : ''; ?>">
            <label>Popup HTML content <small style="font-weight:400">(shown in a modal when the link is clicked - HTML allowed)</small></label>
            <textarea name="agr_popup_html" id="agr_popup_html" style="min-height:140px;font-family:monospace;font-size:12px;width:100%;padding:8px 10px;border:1.5px solid #d1d5db;border-radius:6px;box-sizing:border-box;resize:vertical"><?php echo esc_textarea( isset( $agr['popup_html'] ) ? $agr['popup_html'] : '' ); ?></textarea>
          </div>
        </div>
        <div class="ah-form-row" style="margin:0 0 16px">
          <label>Text after the link <small style="font-weight:400">(optional)</small></label>
          <input type="text" name="agr_after" id="agr_after" value="<?php echo esc_attr( $agr['after'] ); ?>" placeholder="before submitting this form.">
        </div>
        <!-- Live preview -->
        <div style="padding:14px 18px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px">
          <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Preview</div>
          <label style="display:flex;align-items:flex-start;gap:10px;font-size:14px;color:#374151;cursor:default">
            <input type="checkbox" disabled style="margin-top:3px;width:17px;height:17px;flex-shrink:0">
            <span>
              <span id="agr-prev-before"><?php echo esc_html( $agr['before'] ); ?></span>
              <a id="agr-prev-link" href="#" onclick="return false" style="color:#1a3c5e;text-decoration:underline;font-weight:600;margin:0 3px"><?php echo esc_html( $agr['link_text'] ); ?></a>
              <span id="agr-prev-after"><?php echo esc_html( $agr['after'] ); ?></span>
            </span>
          </label>
        </div>
      </div>
    </div>

    <!-- Fields builder card -->
    <div class="ah-card">
      <div class="ah-card-header">
        <h2>Form Fields</h2>
        <button type="button" class="ah-btn ah-btn-primary ah-btn-sm" id="fb-add">+ Add Field</button>
      </div>
      <p style="font-size:13px;color:var(--ah-muted);margin:0 0 16px">Drag <strong>⠿</strong> to reorder. Fields appear on the form in this order.</p>

      <div class="ah-table-wrap">
        <table id="fb-tbl">
          <thead>
            <tr>
              <th style="width:34px"></th>
              <th style="min-width:160px">Field Label</th>
              <th style="width:148px">Type</th>
              <th>Placeholder / Value</th>
              <th style="width:180px">Dropdown Options <small style="font-weight:400;text-transform:none">(one per line)</small></th>
              <th style="min-width:160px">Description <small style="font-weight:400;text-transform:none">(help text)</small></th>
              <th style="width:70px;text-align:center">Required</th>
              <th style="width:46px"></th>
            </tr>
          </thead>
          <tbody id="fb-body">
            <?php foreach ( $fields as $f ) : ?>
            <tr class="fb-row" data-key="<?php echo esc_attr( $f->field_key ); ?>">
              <td><span class="fb-drag">⠿</span></td>
              <td><input type="text" class="fb-label" value="<?php echo esc_attr( $f->label ); ?>" placeholder="Field label"></td>
              <td>
                <select class="fb-type">
                  <?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>" <?php selected( $f->field_type, $tv ); ?>><?php echo esc_html( $tl ); ?></option><?php endforeach; ?>
                </select>
              </td>
              <td><input type="text" class="fb-ph<?php echo 'select' === $f->field_type ? ' fb-hidden' : ''; ?>" value="<?php echo esc_attr( isset( $f->placeholder ) ? $f->placeholder : '' ); ?>" placeholder="Placeholder text"></td>
              <td><textarea class="fb-opts<?php echo 'select' !== $f->field_type ? ' fb-hidden' : ''; ?>" rows="3" placeholder="Option A&#10;Option B&#10;Option C"><?php echo esc_textarea( implode( "\n", isset( $f->options ) ? $f->options : array() ) ); ?></textarea></td>
              <td class="<?php echo 'hidden' === $f->field_type ? 'fb-hidden' : ''; ?>"><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"><?php echo esc_textarea( isset( $f->description ) ? $f->description : '' ); ?></textarea></td>
              <td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"<?php checked( $f->is_required && 'hidden' !== $f->field_type ); ?><?php echo 'hidden' === $f->field_type ? ' disabled style="opacity:.3"' : ''; ?>></td>
              <td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">✕</button></td>
            </tr>
            <?php endforeach; ?>
            <?php if ( empty( $fields ) ) : ?>
            <tr class="fb-row" data-key="">
              <td><span class="fb-drag">⠿</span></td>
              <td><input type="text" class="fb-label" value="" placeholder="Field label"></td>
              <td><select class="fb-type"><?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>"><?php echo esc_html( $tl ); ?></option><?php endforeach; ?></select></td>
              <td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
              <td><textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;Option B&#10;Option C"></textarea></td>
              <td><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"></textarea></td>
              <td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"></td>
              <td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">✕</button></td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div style="margin-top:20px">
        <button type="submit" class="ah-btn ah-btn-primary">Save Form</button>
      </div>
    </div>
  </form>

  <template id="fb-row-tpl">
    <tr class="fb-row" data-key="">
      <td><span class="fb-drag">⠿</span></td>
      <td><input type="text" class="fb-label" value="" placeholder="Field label"></td>
      <td><select class="fb-type"><?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>"><?php echo esc_html( $tl ); ?></option><?php endforeach; ?></select></td>
      <td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
      <td><textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;Option B&#10;Option C"></textarea></td>
      <td><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"></textarea></td>
      <td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"></td>
      <td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">✕</button></td>
    </tr>
  </template>

  <?php else : ?>
  <!-- ════════════════════ Submissions ════════════════════ -->
  <?php
  $sub_search = sanitize_text_field( isset( $_GET['sub_s'] ) ? $_GET['sub_s'] : '' );
  $subs       = AH_Form_Builder::get_submissions_filtered( $form_id, $sub_status, 200, 0 );
  if ( $sub_search ) {
    $subs = array_values( array_filter( $subs, static function( $s ) use ( $sub_search ) {
      return false !== stripos( isset( $s->data ) ? ( is_string( $s->data ) ? $s->data : wp_json_encode( $s->data ) ) : '', $sub_search );
    } ) );
  }
  $f_keys = array_column( $fields, 'label', 'field_key' );
  ?>

  <!-- Status filter pills + search -->
  <form method="get" style="margin-bottom:10px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="page" value="ah-form-builder">
    <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>">
    <input type="hidden" name="tab" value="submissions">
    <input type="hidden" name="sub_status" value="<?php echo esc_attr( $sub_status ); ?>">
    <input type="search" name="sub_s" value="<?php echo esc_attr( $sub_search ); ?>" placeholder="Search submissions…" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:12px;min-width:220px">
    <button class="ah-btn ah-btn-secondary ah-btn-sm">Search</button>
    <?php if ( $sub_search ) : ?><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'submissions', 'sub_status' => $sub_status ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="opacity:.7;">✕ Clear</a><?php endif; ?>
  </form>
  <div class="sub-status-bar">
    <span style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">Filter:</span>
    <?php
    $pill_defs = array(
      ''        => array( 'label' => 'All',     'count' => $status_counts['all'] ),
      'new'     => array( 'label' => 'New',     'count' => $status_counts['new'] ),
      'read'    => array( 'label' => 'Read',    'count' => $status_counts['read'] ),
      'replied' => array( 'label' => 'Replied', 'count' => $status_counts['replied'] ),
      'closed'  => array( 'label' => 'Closed',  'count' => $status_counts['closed'] ),
    );
    foreach ( $pill_defs as $pv => $pd ) :
      $url = add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'submissions', 'sub_status' => $pv ), admin_url( 'admin.php' ) );
    ?>
    <a href="<?php echo esc_url( $url ); ?>" class="sub-status-pill <?php echo $sub_status === $pv ? 'active' : ''; ?>">
      <?php echo esc_html( $pd['label'] ); ?> <span class="badge"><?php echo esc_html( $pd['count'] ); ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if ( $subs ) : ?>
  <div class="ah-table-wrap">
    <table class="ah-table" id="fb-subs">
      <thead>
        <tr>
          <th style="width:30px"></th>
          <th>#</th>
          <th>Status</th>
          <?php foreach ( $fields as $fi ) : ?><th><?php echo esc_html( $fi->label ); ?></th><?php endforeach; ?>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $subs as $s ) :
          $s_status = isset( $s['sub_status'] ) ? $s['sub_status'] : 'new';
          $ssb_cls  = 'ssb-' . esc_attr( $s_status );
          $s_id     = (int) $s['id'];
        ?>
        <tr class="fb-sub-row" data-id="<?php echo esc_attr( $s_id ); ?>">
          <td style="text-align:center;cursor:pointer" class="fb-toggle">▶</td>
          <td style="color:var(--ah-muted);font-size:12px">#<?php echo esc_html( $s_id ); ?></td>
          <td><span class="sub-status-badge <?php echo esc_attr( $ssb_cls ); ?>"><?php echo esc_html( $s_status ); ?></span></td>
          <?php foreach ( $fields as $fi ) : ?>
            <?php $v = isset( $s['data'][ $fi->field_key ] ) ? (string) $s['data'][ $fi->field_key ] : ''; ?>
            <td><?php echo esc_html( mb_strimwidth( $v, 0, 60, '…' ) ); ?></td>
          <?php endforeach; ?>
          <td><small><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $s['created_at'] ) ) ); ?></small></td>
          <td>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'submissions', 'del_sub' => $s_id ), admin_url( 'admin.php' ) ), 'ah_del_sub_fb' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete this submission?')">Delete</a>
          </td>
        </tr>
        <!-- Expanded detail row -->
        <tr class="fb-sub-detail fb-hidden" id="fb-det-<?php echo esc_attr( $s_id ); ?>">
          <td colspan="<?php echo 5 + count( $fields ); ?>" style="padding:0">
            <div style="padding:16px 20px;background:#f8fafc;border-top:1px solid #e5e7eb">
              <div class="sub-data-grid">
                <?php foreach ( $s['data'] as $k => $v ) : if ( ! $v || 'agreed' === $v ) continue; ?>
                <div class="sub-data-item">
                  <div class="sub-data-lbl"><?php echo esc_html( isset( $f_keys[ $k ] ) ? $f_keys[ $k ] : $k ); ?></div>
                  <div class="sub-data-val"><?php echo nl2br( esc_html( $v ) ); ?></div>
                </div>
                <?php endforeach; ?>
              </div>
              <!-- Admin notes + status -->
              <div class="sub-meta-box">
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px">Status</label>
                  <select class="sub-status-select" data-id="<?php echo esc_attr( $s_id ); ?>">
                    <?php foreach ( array( 'new', 'read', 'replied', 'closed' ) as $sv ) : ?>
                      <option value="<?php echo esc_attr( $sv ); ?>" <?php selected( $s_status, $sv ); ?>><?php echo esc_html( ucfirst( $sv ) ); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label style="font-size:11.5px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px">Admin Notes</label>
                  <textarea class="sub-notes-ta" data-id="<?php echo esc_attr( $s_id ); ?>" placeholder="Internal notes about this submission..."><?php echo esc_textarea( isset( $s['admin_notes'] ) ? $s['admin_notes'] : '' ); ?></textarea>
                </div>
                <div style="padding-top:22px">
                  <button class="sub-save-btn" data-id="<?php echo esc_attr( $s_id ); ?>">Save</button>
                  <div class="sub-save-msg" data-id="<?php echo esc_attr( $s_id ); ?>" style="font-size:12px;margin-top:6px;display:none"></div>
                </div>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else : ?>
    <div class="ah-card" style="text-align:center;padding:48px;color:var(--ah-muted)">
      <p style="font-size:1.1rem;margin:0">No submissions<?php echo $sub_status ? ' with status <strong>' . esc_html( $sub_status ) . '</strong>' : ''; ?> for this form.</p>
      <p style="margin:8px 0 0;font-size:13px">Use the shortcode <strong>[ah_form id="<?php echo esc_html( $form_id ); ?>"]</strong> to embed the form on your site.</p>
    </div>
  <?php endif; ?>
  <?php endif; ?>

  <?php endif; // $current ?>
</div>

<script>
jQuery(function ($) {
  // ── New form dialog ──
  $('#fb-new-btn').on('click', function () { $('#fb-new-dialog').slideToggle(180); });
  $('#fb-new-cancel').on('click', function () { $('#fb-new-dialog').slideUp(180); });

  // ── Sortable ──
  $('#fb-body').sortable({ handle: '.fb-drag', placeholder: 'fb-row fb-ghost', axis: 'y', tolerance: 'pointer' });

  // ── Add row ──
  var uid = Date.now();
  $('#fb-add').on('click', function () {
    var tpl  = document.getElementById('fb-row-tpl');
    var $row = $(tpl.content.firstElementChild.cloneNode(true));
    $row.attr('data-key', 'new_' + (++uid));
    $('#fb-body').append($row);
    $row.find('.fb-label').focus();
  });

  // ── Delete row ──
  $('#fb-body').on('click', '.fb-del', function () {
    if ($('#fb-body .fb-row').length <= 1) { alert('The form needs at least one field.'); return; }
    $(this).closest('tr').fadeOut(160, function () { $(this).remove(); });
  });

  // ── Toggle columns based on field type ──
  function applyTypeUI($r, type) {
    var $ph   = $r.find('.fb-ph');
    var $opts = $r.find('.fb-opts');
    var $desc = $r.find('.fb-desc').closest('td');
    var $req  = $r.find('.fb-req');
    if (type === 'select') {
      $ph.addClass('fb-hidden');
      $opts.removeClass('fb-hidden').attr('placeholder', 'Option A\nOption B\nOption C');
      $desc.removeClass('fb-hidden');
      $req.prop('disabled', false).css('opacity', '');
    } else if (type === 'hidden') {
      $ph.removeClass('fb-hidden').attr('placeholder', 'Value sent with form');
      $opts.addClass('fb-hidden');
      $desc.addClass('fb-hidden');
      $req.prop('checked', false).prop('disabled', true).css('opacity', '0.3');
    } else {
      $ph.removeClass('fb-hidden').attr('placeholder', 'Placeholder text');
      $opts.addClass('fb-hidden');
      $desc.removeClass('fb-hidden');
      $req.prop('disabled', false).css('opacity', '');
    }
  }
  // Apply on page load for existing rows
  $('#fb-body .fb-row').each(function() {
    applyTypeUI($(this), $(this).find('.fb-type').val());
  });
  $('#fb-body').on('change', '.fb-type', function () {
    applyTypeUI($(this).closest('tr'), $(this).val());
  });

  // ── Agreement card toggle ──
  $('#agr_enabled').on('change', function () {
    $('#agr-body').slideToggle(200);
  });

  // ── Agreement live preview ──
  function updateAgrPreview() {
    $('#agr-prev-before').text($('#agr_before').val() || '');
    $('#agr-prev-link').text($('#agr_link_text').val() || 'Terms & Conditions');
    var after = $('#agr_after').val();
    $('#agr-prev-after').text(after ? ' ' + after : '');
    var url = $('#agr_url').val();
    if (url && $('input[name="agr_type"]:checked').val() === 'link') {
      $('#agr-prev-link').attr('href', url);
    } else {
      $('#agr-prev-link').attr('href', '#').on('click', function(){ return false; });
    }
  }
  function updateAgrTypeUI() {
    var t = $('input[name="agr_type"]:checked').val();
    if (t === 'popup') {
      $('#agr-url-wrap').hide();
      $('#agr-popup-wrap').show();
    } else {
      $('#agr-url-wrap').show();
      $('#agr-popup-wrap').hide();
    }
  }
  $('input[name="agr_type"]').on('change', function() { updateAgrTypeUI(); updateAgrPreview(); });
  $('#agr_before, #agr_link_text, #agr_after, #agr_url').on('input', updateAgrPreview);

  // ── Serialize fields to JSON before submit ──
  $('#fb-form').on('submit', function () {
    var fields = [];
    $('#fb-body .fb-row').each(function (i) {
      var $r   = $(this);
      var type = $r.find('.fb-type').val();
      var opts = [];
      if (type === 'select') {
        var raw = $r.find('.fb-opts').val().trim();
        if (raw) opts = raw.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
      }
      fields.push({
        field_key:   $r.data('key') || ('field_' + i),
        label:       $r.find('.fb-label').val().trim(),
        field_type:  type,
        placeholder: $r.find('.fb-ph').val().trim(),
        is_required: $r.find('.fb-req').is(':checked'),
        options:     opts,
        description: $r.find('.fb-desc').val().trim(),
      });
    });
    $('#fb-fields-json').val(JSON.stringify(fields));
  });

  // ── Copy shortcode ──
  $('#fb-sc-copy').on('click', function () {
    var sc = $('#fb-sc-text').text();
    if (navigator.clipboard) {
      navigator.clipboard.writeText(sc);
    } else {
      var ta = $('<textarea>').val(sc).appendTo('body').select();
      document.execCommand('copy');
      ta.remove();
    }
    $(this).text('Copied!');
    setTimeout(function () { $('#fb-sc-copy').text('Copy'); }, 2000);
  });

  // ── Expand/collapse submission rows ──
  $('#fb-subs').on('click', '.fb-toggle', function () {
    var id   = $(this).closest('tr').data('id');
    var $det = $('#fb-det-' + id);
    $det.toggleClass('fb-hidden');
    $(this).text($det.hasClass('fb-hidden') ? '▶' : '▼');
    $(this).closest('tr').toggleClass('sub-row-open');
  });

  // ── Save submission meta (status + notes) via AJAX ──
  $('#fb-subs').on('click', '.sub-save-btn', function () {
    var id      = $(this).data('id');
    var $btn    = $(this);
    var $msg    = $('.sub-save-msg[data-id="' + id + '"]');
    var status  = $('.sub-status-select[data-id="' + id + '"]').val();
    var notes   = $('.sub-notes-ta[data-id="' + id + '"]').val();
    $btn.prop('disabled', true).text('Saving…');
    $.post(ajaxurl, {
      action:       'ah_save_submission_meta',
      nonce:        '<?php echo esc_js( $admin_nonce ); ?>',
      sub_id:       id,
      sub_status:   status,
      admin_notes:  notes,
    }, function (res) {
      $btn.prop('disabled', false).text('Save');
      $msg.show().text(res.success ? 'Saved!' : (res.data && res.data.message ? res.data.message : 'Error'));
      $msg.css('color', res.success ? '#16a34a' : '#dc2626');
      if (res.success) {
        // update badge in header row
        var $badge = $btn.closest('tr').prev('.fb-sub-row').find('.sub-status-badge');
        $badge.attr('class', 'sub-status-badge ssb-' + status).text(status);
      }
      setTimeout(function () { $msg.fadeOut(400); }, 2500);
    }).fail(function () {
      $btn.prop('disabled', false).text('Save');
      $msg.show().css('color','#dc2626').text('Request failed.');
      setTimeout(function () { $msg.fadeOut(400); }, 2500);
    });
  });
});
</script>
