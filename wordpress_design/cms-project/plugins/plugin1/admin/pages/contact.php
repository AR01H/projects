<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$model   = new AH_Contact_Model();
$pages_m = new AH_Pages_Model();
$notice  = '';

$contact_page = $pages_m->get_by_type( 'contact' );
$page_id      = $contact_page ? (int) $contact_page->id : 0;

// Ensure extra columns exist (safe to run on every page load)
$cfg_table = AH_DB_Helper::table( 'contact_page_config' );
$sub_table = AH_DB_Helper::table( 'contact_form_submissions' );

if ( ! $wpdb->get_results( "SHOW COLUMNS FROM `{$cfg_table}` LIKE 'form_fields'" ) ) {
	$wpdb->query( "ALTER TABLE `{$cfg_table}` ADD COLUMN `form_fields` JSON NULL" );
}
if ( ! $wpdb->get_results( "SHOW COLUMNS FROM `{$cfg_table}` LIKE 'notify_email'" ) ) {
	$wpdb->query( "ALTER TABLE `{$cfg_table}` ADD COLUMN `notify_email` VARCHAR(200) NULL" );
}
if ( ! $wpdb->get_results( "SHOW COLUMNS FROM `{$sub_table}` LIKE 'extra_data'" ) ) {
	$wpdb->query( "ALTER TABLE `{$sub_table}` ADD COLUMN `extra_data` JSON NULL" );
}

// ── POST handlers ────────────────────────────────────────────────────────────

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$post_action = sanitize_key( $_POST['ah_post_action'] ?? '' );

	// Tab 1: Page Settings
	if ( 'save_config' === $post_action ) {
		if ( ! wp_verify_nonce( $_POST['ah_contact_nonce'] ?? '', 'ah_save_contact_config' ) ) wp_die( 'Security.' );
		$model->save_page_config( $page_id, array(
			'heading'         => sanitize_text_field( $_POST['heading'] ?? '' ),
			'basic_info'      => sanitize_textarea_field( $_POST['basic_info'] ?? '' ),
			'email'           => sanitize_email( $_POST['email'] ?? '' ),
			'notify_email'    => sanitize_email( $_POST['notify_email'] ?? '' ),
			'whatsapp_number' => sanitize_text_field( $_POST['whatsapp_number'] ?? '' ),
			'phone_number'    => sanitize_text_field( $_POST['phone_number'] ?? '' ),
			'maps_embed_url'  => wp_kses( $_POST['maps_embed_url'] ?? '', array(
				'iframe' => array(
					'src' => true, 'width' => true, 'height' => true,
					'frameborder' => true, 'allowfullscreen' => true,
					'loading' => true, 'style' => true, 'title' => true,
				),
			) ),
			'is_visible' => (int) ( $_POST['is_visible'] ?? 1 ),
		) );
		AH_DB_Helper::log_action( 'update', 'contact_page_config', $page_id );
		$notice = 'success:Page settings saved.';
	}

	// Tab 2: Form Builder
	if ( 'save_fields' === $post_action ) {
		if ( ! wp_verify_nonce( $_POST['ah_fields_nonce'] ?? '', 'ah_save_form_fields' ) ) wp_die( 'Security.' );

		$raw     = wp_unslash( $_POST['form_fields_json'] ?? '[]' );
		$decoded = json_decode( $raw, true );
		$allowed_types = array( 'text', 'email', 'tel', 'textarea', 'select', 'number', 'date', 'url' );
		$sanitized = array();

		if ( is_array( $decoded ) ) {
			foreach ( $decoded as $i => $f ) {
				$type = sanitize_key( $f['type'] ?? 'text' );
				if ( ! in_array( $type, $allowed_types, true ) ) $type = 'text';

				$opts = array();
				if ( 'select' === $type && ! empty( $f['options'] ) && is_array( $f['options'] ) ) {
					$opts = array_values( array_filter( array_map( 'sanitize_text_field', $f['options'] ) ) );
				}

				$lbl = sanitize_text_field( $f['label'] ?? '' );
				if ( ! $lbl ) continue; // skip blank rows

				$sanitized[] = array(
					'id'          => sanitize_key( $f['id'] ?? ( 'field_' . ( $i + 1 ) ) ),
					'label'       => $lbl,
					'type'        => $type,
					'placeholder' => sanitize_text_field( $f['placeholder'] ?? '' ),
					'required'    => ! empty( $f['required'] ),
					'options'     => $opts,
					'sort_order'  => $i,
				);
			}
		}

		$row = $page_id ? $model->get_page_config( $page_id ) : null;
		$json_val = wp_json_encode( $sanitized );
		if ( $row ) {
			$wpdb->update( $cfg_table, array( 'form_fields' => $json_val ), array( 'id' => (int) $row->id ) );
		} else {
			$wpdb->insert( $cfg_table, array( 'page_id' => $page_id, 'form_fields' => $json_val ) );
		}
		AH_DB_Helper::log_action( 'update', 'contact_page_config', $page_id, array( 'form_fields' => count( $sanitized ) . ' fields' ) );
		$notice = 'success:Form fields saved. ' . count( $sanitized ) . ' field(s) active.';
	}
}

// ── Load data ────────────────────────────────────────────────────────────────

$config = $page_id ? $model->get_page_config( $page_id ) : null;

$existing_fields = array();
if ( $config && ! empty( $config->form_fields ) ) {
	$existing_fields = json_decode( $config->form_fields, true ) ?: array();
}
if ( empty( $existing_fields ) ) {
	$existing_fields = array(
		array( 'id' => 'field_name',    'label' => 'Full Name',     'type' => 'text',     'placeholder' => 'Your full name',           'required' => true,  'options' => array() ),
		array( 'id' => 'field_email',   'label' => 'Email Address', 'type' => 'email',    'placeholder' => 'your@email.com',           'required' => true,  'options' => array() ),
		array( 'id' => 'field_phone',   'label' => 'Phone Number',  'type' => 'tel',      'placeholder' => '+91 98765 43210',          'required' => false, 'options' => array() ),
		array( 'id' => 'field_subject', 'label' => 'Subject',       'type' => 'text',     'placeholder' => 'How can we help?',         'required' => false, 'options' => array() ),
		array( 'id' => 'field_message', 'label' => 'Message',       'type' => 'textarea', 'placeholder' => 'Tell us about your requirements…', 'required' => true,  'options' => array() ),
	);
}

$active_tab = sanitize_key( $_GET['tab'] ?? 'config' );
$field_types = array(
	'text'     => 'Text',
	'email'    => 'Email',
	'tel'      => 'Phone / Tel',
	'textarea' => 'Textarea',
	'select'   => 'Dropdown',
	'number'   => 'Number',
	'date'     => 'Date',
	'url'      => 'URL',
);
?>

<style>
.ah-tab-nav { display:flex; gap:2px; border-bottom:2px solid #e5e7eb; margin-bottom:24px; }
.ah-tab-nav a { padding:10px 20px; text-decoration:none; font-weight:500; font-size:14px; color:#6b7280; border-radius:6px 6px 0 0; border:1px solid transparent; border-bottom:none; margin-bottom:-2px; }
.ah-tab-nav a.active { color:#2563eb; background:#fff; border-color:#e5e7eb; border-bottom-color:#fff; }
.ah-tab-nav a:hover:not(.active) { color:#1f2937; background:#f9fafb; }

#ah-fields-table { border-collapse:collapse; width:100%; }
#ah-fields-table th { font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; padding:10px 10px; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
#ah-fields-table td { padding:8px 10px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
#ah-fields-table td input[type="text"],
#ah-fields-table td input[type="email"],
#ah-fields-table td textarea,
#ah-fields-table td select { width:100%; font-size:13px; padding:7px 10px; border:1.5px solid #d1d5db; border-radius:6px; background:#fff; font-family:inherit; box-sizing:border-box; }
#ah-fields-table td input:focus,
#ah-fields-table td textarea:focus,
#ah-fields-table td select:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.1); }
#ah-fields-table td textarea { min-height:70px; resize:vertical; }
#ah-fields-table tr.ah-field-row:hover td { background:#fafafa; }
.drag-handle { cursor:grab; color:#9ca3af; font-size:20px; padding:0 6px; line-height:1; user-select:none; }
.drag-handle:active { cursor:grabbing; }
.ah-req-chk { width:18px !important; height:18px; transform:scale(1.2); cursor:pointer; accent-color:#2563eb; }
.ah-hidden { display:none !important; }
.ah-sortable-ghost { opacity:.4; background:#eff6ff !important; }
</style>

<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-phone"></span> <?php esc_html_e( 'Contact Page', 'ah-theme' ); ?></h1>

  <?php if ( $notice ) :
    list( $ntype, $nmsg ) = explode( ':', $notice, 2 );
  ?>
    <div class="ah-notice ah-notice-<?php echo 'success' === $ntype ? 'success' : 'warning'; ?>"><?php echo esc_html( $nmsg ); ?></div>
  <?php endif; ?>
  <?php if ( ! $page_id ) : ?>
    <div class="ah-notice ah-notice-warning">Contact page not found in Pages Manager. Create it there first.</div>
  <?php endif; ?>

  <!-- Tab Nav -->
  <div class="ah-tab-nav">
    <a href="<?php echo esc_url( add_query_arg( 'tab', 'config', admin_url( 'admin.php?page=ah-contact' ) ) ); ?>" class="<?php echo 'config' === $active_tab ? 'active' : ''; ?>">Page Settings</a>
    <a href="<?php echo esc_url( add_query_arg( 'tab', 'builder', admin_url( 'admin.php?page=ah-contact' ) ) ); ?>" class="<?php echo 'builder' === $active_tab ? 'active' : ''; ?>">Form Builder</a>
    <a href="<?php echo esc_url( add_query_arg( 'tab', 'submissions', admin_url( 'admin.php?page=ah-contact' ) ) ); ?>" class="<?php echo 'submissions' === $active_tab ? 'active' : ''; ?>">
      Recent Submissions
      <?php $unread = $model->unread_count(); if ( $unread ) : ?><span class="update-plugins count-<?php echo $unread; ?>" style="top:-2px;"><span class="plugin-count"><?php echo $unread; ?></span></span><?php endif; ?>
    </a>
  </div>

  <?php if ( 'config' === $active_tab ) : ?>
  <!-- ═══════════════════════════════════ TAB: Page Settings ═══════════════════════════════════ -->
  <div class="ah-card">
    <div class="ah-card-header"><h2>Contact Page Configuration</h2></div>
    <form method="post">
      <?php wp_nonce_field( 'ah_save_contact_config', 'ah_contact_nonce' ); ?>
      <input type="hidden" name="ah_post_action" value="save_config">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <div>
          <div class="ah-form-row"><label>Page Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $config->heading ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>Basic Info / Description</label><textarea name="basic_info" rows="4"><?php echo esc_textarea( $config->basic_info ?? '' ); ?></textarea></div>
          <div class="ah-form-row"><label>Display Email <small>(shown on page)</small></label><input type="email" name="email" value="<?php echo esc_attr( $config->email ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>Notification Email <small>(submissions sent here)</small></label><input type="email" name="notify_email" value="<?php echo esc_attr( $config->notify_email ?? $config->email ?? get_option( 'admin_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"></div>
          <div class="ah-form-row"><label>WhatsApp Number <small>(with country code)</small></label><input type="tel" name="whatsapp_number" value="<?php echo esc_attr( $config->whatsapp_number ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>Phone Number</label><input type="tel" name="phone_number" value="<?php echo esc_attr( $config->phone_number ?? '' ); ?>"></div>
        </div>
        <div>
          <div class="ah-form-row">
            <label>Google Maps Embed Code</label>
            <textarea name="maps_embed_url" rows="7" placeholder='<iframe src="https://maps.google.com/..." ...></iframe>'><?php echo esc_textarea( $config->maps_embed_url ?? '' ); ?></textarea>
            <p class="description">Paste the full &lt;iframe&gt; code from Google Maps &rarr; Share &rarr; Embed.</p>
          </div>
          <div class="ah-form-row">
            <label>Page Visible</label>
            <select name="is_visible">
              <option value="1" <?php selected( $config->is_visible ?? 1, 1 ); ?>>Yes</option>
              <option value="0" <?php selected( $config->is_visible ?? 1, 0 ); ?>>No</option>
            </select>
          </div>
        </div>
      </div>
      <button type="submit" class="ah-btn ah-btn-primary">Save Page Settings</button>
    </form>
  </div>

  <?php elseif ( 'builder' === $active_tab ) : ?>
  <!-- ═══════════════════════════════════ TAB: Form Builder ═══════════════════════════════════ -->
  <div class="ah-card">
    <div class="ah-card-header">
      <h2>Form Builder</h2>
      <button type="button" class="ah-btn ah-btn-primary ah-btn-sm" id="ah-add-field">+ Add Field</button>
    </div>
    <p style="color:var(--ah-muted);font-size:13px;margin:0 0 20px;">Drag <span class="drag-handle" style="cursor:default;">⠿</span> to reorder. Fields render on the frontend contact form in this exact order.</p>

    <form method="post" id="ah-builder-form">
      <?php wp_nonce_field( 'ah_save_form_fields', 'ah_fields_nonce' ); ?>
      <input type="hidden" name="ah_post_action" value="save_fields">
      <input type="hidden" name="form_fields_json" id="ah-fields-json">

      <div class="ah-table-wrap">
        <table id="ah-fields-table">
          <thead>
            <tr>
              <th style="width:36px"></th>
              <th style="min-width:180px;">Field Label</th>
              <th style="width:145px;">Type</th>
              <th>Placeholder / Options</th>
              <th style="width:80px;text-align:center;">Required</th>
              <th style="width:50px;"></th>
            </tr>
          </thead>
          <tbody id="ah-fields-body">
            <?php foreach ( $existing_fields as $f ) : ?>
            <tr class="ah-field-row" data-id="<?php echo esc_attr( $f['id'] ); ?>">
              <td><span class="drag-handle">⠿</span></td>
              <td><input type="text" class="field-label" value="<?php echo esc_attr( $f['label'] ); ?>" placeholder="e.g. Property Budget"></td>
              <td>
                <select class="field-type">
                  <?php foreach ( $field_types as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $f['type'] ?? 'text', $val ); ?>><?php echo esc_html( $lbl ); ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <input type="text" class="field-placeholder<?php echo ( ( $f['type'] ?? '' ) === 'select' ) ? ' ah-hidden' : ''; ?>" value="<?php echo esc_attr( $f['placeholder'] ?? '' ); ?>" placeholder="Placeholder text…">
                <textarea class="field-options<?php echo ( ( $f['type'] ?? '' ) !== 'select' ) ? ' ah-hidden' : ''; ?>" rows="3" placeholder="One option per line&#10;e.g. Home Loan&#10;Property Search"><?php echo esc_textarea( implode( "\n", $f['options'] ?? array() ) ); ?></textarea>
              </td>
              <td style="text-align:center;"><input type="checkbox" class="field-required ah-req-chk" <?php checked( ! empty( $f['required'] ) ); ?>></td>
              <td style="text-align:center;"><button type="button" class="ah-btn ah-btn-danger ah-btn-sm ah-del-field" title="Remove field">✕</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="margin-top:20px;display:flex;align-items:center;gap:12px;">
        <button type="submit" class="ah-btn ah-btn-primary">Save Form Fields</button>
        <span style="font-size:13px;color:var(--ah-muted);"><?php echo count( $existing_fields ); ?> field(s) configured</span>
      </div>
    </form>
  </div>

  <!-- Hidden row template (used by JS to clone new rows) -->
  <template id="ah-row-tpl">
    <tr class="ah-field-row" data-id="">
      <td><span class="drag-handle">⠿</span></td>
      <td><input type="text" class="field-label" value="" placeholder="e.g. Property Budget"></td>
      <td>
        <select class="field-type">
          <?php foreach ( $field_types as $val => $lbl ) : ?>
            <option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $lbl ); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
      <td>
        <input type="text" class="field-placeholder" value="" placeholder="Placeholder text…">
        <textarea class="field-options ah-hidden" rows="3" placeholder="One option per line&#10;e.g. Home Loan&#10;Property Search"></textarea>
      </td>
      <td style="text-align:center;"><input type="checkbox" class="field-required ah-req-chk"></td>
      <td style="text-align:center;"><button type="button" class="ah-btn ah-btn-danger ah-btn-sm ah-del-field" title="Remove field">✕</button></td>
    </tr>
  </template>

  <script>
  (function ($) {
    var $body  = $('#ah-fields-body');
    var $form  = $('#ah-builder-form');
    var uid    = Date.now();

    // ── Sortable drag-and-drop ──
    $body.sortable({
      handle:      '.drag-handle',
      placeholder: 'ah-field-row ah-sortable-ghost',
      axis:        'y',
      tolerance:   'pointer',
    });

    // ── Add field ──
    $('#ah-add-field').on('click', function () {
      var tpl = document.getElementById('ah-row-tpl');
      var $row = $(document.importNode(tpl.content, true)).find('tr');
      $row.attr('data-id', 'field_' + (++uid));
      $body.append($row);
      $row.find('.field-label').focus();
    });

    // ── Delete field ──
    $body.on('click', '.ah-del-field', function () {
      if ($body.find('.ah-field-row').length <= 1) {
        alert('You need at least one field on the form.');
        return;
      }
      $(this).closest('tr').fadeOut(200, function () { $(this).remove(); });
    });

    // ── Toggle placeholder ↔ options textarea when type changes ──
    $body.on('change', '.field-type', function () {
      var $row = $(this).closest('tr');
      if ($(this).val() === 'select') {
        $row.find('.field-placeholder').addClass('ah-hidden');
        $row.find('.field-options').removeClass('ah-hidden');
      } else {
        $row.find('.field-placeholder').removeClass('ah-hidden');
        $row.find('.field-options').addClass('ah-hidden');
      }
    });

    // ── Serialize to JSON before submit ──
    $form.on('submit', function (e) {
      var fields = [];
      $body.find('.ah-field-row').each(function (i) {
        var $r   = $(this);
        var type = $r.find('.field-type').val();
        var opts = [];
        if (type === 'select') {
          var raw = $r.find('.field-options').val().trim();
          if (raw) {
            opts = raw.split('\n').map(function (s) { return s.trim(); }).filter(function (s) { return s.length > 0; });
          }
        }
        fields.push({
          id:          $r.data('id') || ('field_' + i),
          label:       $r.find('.field-label').val().trim(),
          type:        type,
          placeholder: $r.find('.field-placeholder').val().trim(),
          required:    $r.find('.field-required').is(':checked'),
          options:     opts,
          sort_order:  i,
        });
      });
      $('#ah-fields-json').val(JSON.stringify(fields));
    });
  }(jQuery));
  </script>

  <?php else : ?>
  <!-- ═══════════════════════════════════ TAB: Submissions ═══════════════════════════════════ -->
  <?php
  $status_f = sanitize_key( $_GET['status'] ?? '' );
  $search   = sanitize_text_field( $_GET['s'] ?? '' );
  $paged    = AH_Pagination::current_page();
  $result   = $model->get_paginated( $paged, $status_f, $search );
  $items    = $result['items'];
  $meta     = $result['meta'];
  ?>

  <div class="ah-table-top">
    <form class="ah-search-form" method="get">
      <input type="hidden" name="page" value="ah-contact">
      <input type="hidden" name="tab" value="submissions">
      <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search name or email…">
      <select name="status">
        <option value="">All Status</option>
        <?php foreach ( array( 'new', 'in_progress', 'resolved', 'spam' ) as $st ) : ?>
          <option value="<?php echo $st; ?>" <?php selected( $status_f, $st ); ?>><?php echo esc_html( ucfirst( str_replace( '_', ' ', $st ) ) ); ?></option>
        <?php endforeach; ?>
      </select>
      <button class="ah-btn ah-btn-secondary">Filter</button>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-submissions' ) ); ?>" class="ah-btn ah-btn-secondary">View All Submissions &rarr;</a>
    </form>
  </div>

  <div class="ah-table-wrap">
    <table class="ah-table">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Subject</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if ( $items ) : ?>
          <?php foreach ( $items as $sub ) : ?>
          <tr style="<?php echo ! $sub->is_read ? 'font-weight:600;' : ''; ?>">
            <td><?php echo esc_html( $sub->full_name ); ?><?php echo ! $sub->is_read ? ' <span class="ah-badge ah-badge-new" style="font-size:10px;">New</span>' : ''; ?></td>
            <td><?php echo esc_html( $sub->email ); ?></td>
            <td><?php echo esc_html( $sub->phone ?: '—' ); ?></td>
            <td><?php echo esc_html( $sub->subject ? wp_trim_words( $sub->subject, 5 ) : '—' ); ?></td>
            <td><span class="ah-badge ah-badge-<?php echo esc_attr( str_replace( '_', '-', $sub->status ) ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $sub->status ) ) ); ?></span></td>
            <td><small><?php echo esc_html( wp_date( 'M j, Y', strtotime( $sub->submitted_at ) ) ); ?></small></td>
            <td class="row-actions">
              <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-submissions', 'id' => $sub->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">View</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr><td colspan="7" style="text-align:center;color:var(--ah-muted);padding:30px;">No submissions yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php echo AH_Pagination::render( $meta ); ?>
  <?php endif; ?>
</div>
