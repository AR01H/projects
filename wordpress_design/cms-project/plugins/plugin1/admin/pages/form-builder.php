<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

// Ensure tables exist
AH_Form_Builder::install_tables();

$content_tax_m = new AH_Content_Taxonomy_Model();
$notice     = '';
$active_tab = sanitize_key( $_GET['tab'] ?? 'build' );
$form_id    = (int) ( $_GET['form_id'] ?? 0 );

// ── Handle: create new form ───────────────────────────────────────────────────
if ( isset( $_POST['ah_new_form_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_new_form_nonce'], 'ah_new_form' ) ) wp_die( 'Security.' );
	$new_id = AH_Form_Builder::upsert( 0, array(
		'name'            => sanitize_text_field( $_POST['new_form_name'] ?? 'New Form' ),
		'notify_email'    => sanitize_email( get_option( 'admin_email' ) ),
		'success_message' => 'Thank you! We\'ll get back to you shortly.',
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

	// Save form meta
	$form_id = AH_Form_Builder::upsert( $form_id, array(
		'name'            => sanitize_text_field( $_POST['form_name'] ?? '' ),
		'notify_email'    => sanitize_email( $_POST['notify_email'] ?? '' ),
		'success_message' => sanitize_text_field( $_POST['success_message'] ?? '' ),
		'status'          => sanitize_key( $_POST['form_status'] ?? 'active' ),
	) );

	// Save fields from JSON payload
	$raw    = wp_unslash( $_POST['fields_json'] ?? '[]' );
	$parsed = json_decode( $raw, true );
	if ( is_array( $parsed ) ) {
		AH_Form_Builder::save_fields( $form_id, $parsed );
	}
	$content_tax_m->sync_terms( 'form', $form_id, $_POST['taxonomy_ids'] ?? array() );

	$notice = 'success:Form saved successfully.';
	AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'build', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
}

if ( isset( $_GET['saved'] ) ) $notice = 'success:Form saved successfully.';

$all_forms   = AH_Form_Builder::get_all();
$current     = $form_id ? AH_Form_Builder::get( $form_id ) : null;
$fields      = $form_id ? AH_Form_Builder::get_fields( $form_id ) : array();
$sub_count   = $form_id ? AH_Form_Builder::count_submissions( $form_id ) : 0;
$field_types = array( 'text' => 'Text', 'email' => 'Email', 'tel' => 'Phone / Tel', 'textarea' => 'Textarea', 'select' => 'Dropdown', 'number' => 'Number', 'date' => 'Date', 'url' => 'URL' );
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
.sub-data-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:8px}
.sub-data-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:10px 12px}
.sub-data-lbl{font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.sub-data-val{font-size:13.5px;color:#1f2937;word-break:break-word}
.sub-row-open td{background:#f0f9ff!important}
</style>

<div class="wrap ah-wrap">

  <?php if ( $notice ) : list( $nt, $nm ) = explode( ':', $notice, 2 ); ?>
    <div class="ah-notice ah-notice-<?php echo 'success' === $nt ? 'success' : 'warning'; ?>"><?php echo esc_html( $nm ); ?></div>
  <?php endif; ?>

  <div class="fb-header">
    <h1><span class="dashicons dashicons-feedback"></span> Form Builder</h1>
    <!-- New form modal trigger -->
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
      <option value="">— Choose a form —</option>
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
    <span style="font-size:12px;color:#6b7280">— paste into any page or template to embed this form</span>
  </div>

  <!-- Tab nav -->
  <div class="fb-tab-nav">
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'build' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'build' === $active_tab ? 'on' : ''; ?>">Build Form</a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'submissions' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'submissions' === $active_tab ? 'on' : ''; ?>">
      Submissions <span style="background:#e5e7eb;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700"><?php echo esc_html( $sub_count ); ?></span>
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
        <div class="ah-form-row" style="margin:0"><label>Notify Email <small>(receives submissions)</small></label><input type="email" name="notify_email" value="<?php echo esc_attr( $current->notify_email ?? get_option( 'admin_email' ) ); ?>"></div>
        <div class="ah-form-row" style="margin:0"><label>Success Message</label><input type="text" name="success_message" value="<?php echo esc_attr( $current->success_message ); ?>"></div>
        <div class="ah-form-row" style="margin:0"><label>Status</label>
          <select name="form_status">
            <option value="active" <?php selected( $current->status, 'active' ); ?>>Active</option>
            <option value="inactive" <?php selected( $current->status, 'inactive' ); ?>>Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <div class="ah-card" style="margin-bottom:20px">
      <div class="ah-card-header"><h2>Taxonomy Terms</h2></div>
      <?php $content_tax_m->render_picker( 'form', $form_id ); ?>
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
              <th>Placeholder</th>
              <th style="width:180px">Dropdown Options <small style="font-weight:400;text-transform:none">(one per line)</small></th>
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
              <td><input type="text" class="fb-ph<?php echo 'select' === $f->field_type ? ' fb-hidden' : ''; ?>" value="<?php echo esc_attr( $f->placeholder ?? '' ); ?>" placeholder="Placeholder text"></td>
              <td><textarea class="fb-opts<?php echo 'select' !== $f->field_type ? ' fb-hidden' : ''; ?>" rows="3" placeholder="Option A&#10;Option B&#10;Option C"><?php echo esc_textarea( implode( "\n", $f->options ?? array() ) ); ?></textarea></td>
              <td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"<?php checked( $f->is_required ); ?>></td>
              <td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">✕</button></td>
            </tr>
            <?php endforeach; ?>
            <?php if ( empty( $fields ) ) : ?>
            <!-- Default starter row -->
            <tr class="fb-row" data-key="">
              <td><span class="fb-drag">⠿</span></td>
              <td><input type="text" class="fb-label" value="" placeholder="Field label"></td>
              <td><select class="fb-type"><?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>"><?php echo esc_html( $tl ); ?></option><?php endforeach; ?></select></td>
              <td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
              <td><textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;Option B&#10;Option C"></textarea></td>
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

  <!-- Row template -->
  <template id="fb-row-tpl">
    <tr class="fb-row" data-key="">
      <td><span class="fb-drag">⠿</span></td>
      <td><input type="text" class="fb-label" value="" placeholder="Field label"></td>
      <td><select class="fb-type"><?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>"><?php echo esc_html( $tl ); ?></option><?php endforeach; ?></select></td>
      <td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
      <td><textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;Option B&#10;Option C"></textarea></td>
      <td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"></td>
      <td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">✕</button></td>
    </tr>
  </template>

  <?php else : ?>
  <!-- ════════════════════ Submissions ════════════════════ -->
  <?php
  $subs   = AH_Form_Builder::get_submissions( $form_id, 100 );
  $f_keys = array_column( $fields, 'label', 'field_key' );
  ?>
  <?php if ( $subs ) : ?>
  <div class="ah-table-wrap">
    <table class="ah-table" id="fb-subs">
      <thead>
        <tr>
          <th style="width:30px"></th>
          <th>#</th>
          <?php foreach ( $fields as $fi ) : ?><th><?php echo esc_html( $fi->label ); ?></th><?php endforeach; ?>
          <th>Date</th>
          <th>IP</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $subs as $s ) : ?>
        <tr class="fb-sub-row" data-id="<?php echo esc_attr( $s->id ); ?>">
          <td style="text-align:center;cursor:pointer" class="fb-toggle">▶</td>
          <td style="color:var(--ah-muted);font-size:12px">#<?php echo esc_html( $s->id ); ?></td>
          <?php foreach ( $fields as $fi ) : ?>
            <td><?php $v = $s->data[ $fi->field_key ] ?? ''; echo esc_html( mb_strimwidth( $v, 0, 60, '…' ) ); ?></td>
          <?php endforeach; ?>
          <td><small><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $s->created_at ) ) ); ?></small></td>
          <td><small style="color:var(--ah-muted)"><?php echo esc_html( $s->ip_address ); ?></small></td>
          <td>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'tab' => 'submissions', 'del_sub' => $s->id ), admin_url( 'admin.php' ) ), 'ah_del_sub_fb' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete this submission?')">Delete</a>
          </td>
        </tr>
        <!-- Expanded row (hidden by default) -->
        <tr class="fb-sub-detail fb-hidden" id="fb-det-<?php echo esc_attr( $s->id ); ?>">
          <td colspan="<?php echo 4 + count( $fields ) + 2; ?>" style="padding:0">
            <div style="padding:16px 20px;background:#f8fafc;border-top:1px solid #e5e7eb">
              <div class="sub-data-grid">
                <?php foreach ( $s->data as $k => $v ) : if ( ! $v ) continue; ?>
                <div class="sub-data-item">
                  <div class="sub-data-lbl"><?php echo esc_html( $f_keys[ $k ] ?? $k ); ?></div>
                  <div class="sub-data-val"><?php echo nl2br( esc_html( $v ) ); ?></div>
                </div>
                <?php endforeach; ?>
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
      <p style="font-size:1.1rem;margin:0">No submissions yet for this form.</p>
      <p style="margin:8px 0 0;font-size:13px">Use the shortcode <strong>[ah_form id="<?php echo esc_html( $form_id ); ?>"]</strong> to embed the form on your site.</p>
    </div>
  <?php endif; ?>
  <?php endif; ?>

  <?php endif; // $current ?>
</div>

<script>
(function ($) {
  // ── New form dialog ──
  $('#fb-new-btn').on('click', function () { $('#fb-new-dialog').slideToggle(180); });
  $('#fb-new-cancel').on('click', function () { $('#fb-new-dialog').slideUp(180); });

  // ── Sortable ──
  $('#fb-body').sortable({ handle: '.fb-drag', placeholder: 'fb-row fb-ghost', axis: 'y', tolerance: 'pointer' });

  // ── Add row ──
  var uid = Date.now();
  $('#fb-add').on('click', function () {
    var tpl = document.getElementById('fb-row-tpl');
    var $row = $(document.importNode(tpl.content, true)).find('tr');
    $row.attr('data-key', 'new_' + (++uid));
    $('#fb-body').append($row);
    $row.find('.fb-label').focus();
  });

  // ── Delete row ──
  $('#fb-body').on('click', '.fb-del', function () {
    if ($('#fb-body .fb-row').length <= 1) { alert('The form needs at least one field.'); return; }
    $(this).closest('tr').fadeOut(160, function () { $(this).remove(); });
  });

  // ── Toggle placeholder ↔ options on type change ──
  $('#fb-body').on('change', '.fb-type', function () {
    var $r = $(this).closest('tr');
    if ($(this).val() === 'select') {
      $r.find('.fb-ph').addClass('fb-hidden');
      $r.find('.fb-opts').removeClass('fb-hidden');
    } else {
      $r.find('.fb-ph').removeClass('fb-hidden');
      $r.find('.fb-opts').addClass('fb-hidden');
    }
  });

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
      });
    });
    $('#fb-fields-json').val(JSON.stringify(fields));
  });

  // ── Copy shortcode ──
  $('#fb-sc-copy').on('click', function () {
    var sc = $('#fb-sc-text').text();
    navigator.clipboard ? navigator.clipboard.writeText(sc) : (function () { var ta = $('<textarea>').val(sc).appendTo('body').select(); document.execCommand('copy'); ta.remove(); })();
    $(this).text('Copied!');
    setTimeout(function () { $('#fb-sc-copy').text('Copy'); }, 2000);
  });

  // ── Expand/collapse submission rows ──
  $('#fb-subs').on('click', '.fb-toggle', function () {
    var id  = $(this).closest('tr').data('id');
    var $det = $('#fb-det-' + id);
    $det.toggleClass('fb-hidden');
    $(this).text($det.hasClass('fb-hidden') ? '▶' : '▼');
    $(this).closest('tr').toggleClass('sub-row-open');
  });
}(jQuery));
</script>
