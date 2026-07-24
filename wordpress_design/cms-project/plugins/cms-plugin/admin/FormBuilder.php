<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

AH_Form_Builder::install_tables();
AH_Form_Builder::maybe_upgrade_submissions();

$notice     = '';
$n_type     = 'success';
$action     = sanitize_key( $_GET['action'] ?? 'list' );
$form_id    = (int) ( $_GET['form_id'] ?? 0 );
$active_tab = sanitize_key( $_GET['tab'] ?? 'build' );
$sub_status = sanitize_key( $_GET['sub_status'] ?? '' );

// ── Handle: create new form ──
if ( isset( $_POST['ah_new_form_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_new_form_nonce'], 'ah_new_form' ) ) wp_die( 'Security.' );
	$new_id = AH_Form_Builder::upsert( 0, array(
		'name'            => sanitize_text_field( $_POST['new_form_name'] ?? 'New Form' ),
		'success_message' => 'Thank you! We will get back to you shortly.',
		'disable_rules'   => 0,
	) );
	AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $new_id, 'action' => 'edit', 'tab' => 'build' ), admin_url( 'admin.php' ) ) );
}

// ── Handle: delete form ──
if ( isset( $_GET['delete_form'] ) && isset( $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_form' ) ) wp_die( 'Security.' );
	AH_Form_Builder::delete_form( (int) $_GET['delete_form'] );
	$notice = 'Form deleted.';
	$action = 'list';
}

// ── Handle: delete submission ──
if ( isset( $_GET['del_sub'] ) && isset( $_GET['_wpnonce'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_sub_fb' ) ) wp_die( 'Security.' );
	AH_Form_Builder::delete_submission( (int) $_GET['del_sub'] );
	$notice = 'Submission deleted.';
}

// ── Handle: save form settings + fields ──
if ( isset( $_POST['ah_save_form_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_save_form_nonce'], 'ah_save_form' ) ) wp_die( 'Security.' );
	$form_id = AH_Form_Builder::upsert( $form_id, array(
		'name'            => sanitize_text_field( $_POST['form_name'] ?? '' ),
		'success_message' => sanitize_text_field( $_POST['success_message'] ?? '' ),
		'submit_label'    => sanitize_text_field( $_POST['submit_label'] ?? '' ),
		'status'          => sanitize_key( $_POST['form_status'] ?? 'active' ),
		'disable_rules'   => isset( $_POST['disable_rules'] ) ? 1 : 0,
	) );
	$raw    = wp_unslash( $_POST['fields_json'] ?? '[]' );
	$parsed = json_decode( $raw, true );
	if ( is_array( $parsed ) ) {
		AH_Form_Builder::save_fields( $form_id, $parsed );
	}
	AH_Form_Builder::save_agreement( $form_id, array(
		'enabled'    => isset( $_POST['agr_enabled'] ) ? 1 : 0,
		'before'     => wp_unslash( $_POST['agr_before'] ?? '' ),
		'link_text'  => wp_unslash( $_POST['agr_link_text'] ?? '' ),
		'type'       => wp_unslash( $_POST['agr_type'] ?? 'link' ),
		'url'        => wp_unslash( $_POST['agr_url'] ?? '' ),
		'after'      => wp_unslash( $_POST['agr_after'] ?? '' ),
		'popup_html' => wp_unslash( $_POST['agr_popup_html'] ?? '' ),
	) );
	$notice = 'Form saved successfully.';
}

if ( isset( $_GET['saved'] ) ) $notice = 'Form saved successfully.';

$all_forms   = AH_Form_Builder::get_all();
$current     = $form_id ? AH_Form_Builder::get( $form_id ) : null;
$fields      = $form_id ? AH_Form_Builder::get_fields( $form_id ) : array();
$status_counts = $form_id ? AH_Form_Builder::count_by_status( $form_id ) : array( 'all' => 0, 'new' => 0, 'read' => 0, 'replied' => 0, 'closed' => 0 );
$field_types = array( 'text' => 'Text', 'email' => 'Email', 'tel' => 'Phone / Tel', 'textarea' => 'Textarea', 'select' => 'Dropdown', 'radio' => 'Radio Buttons', 'checkbox' => 'Checkboxes', 'number' => 'Number', 'date' => 'Date', 'daterange' => 'Date Range', 'color' => 'Color Picker', 'url' => 'URL', 'hidden' => 'Hidden Field', 'markup' => 'Markup / Instructions' );
$agr         = $form_id ? AH_Form_Builder::get_agreement( $form_id ) : array( 'enabled' => 0, 'before' => 'I have read and agree to the', 'link_text' => 'Terms & Conditions', 'type' => 'link', 'url' => '', 'after' => '' );
$admin_nonce = wp_create_nonce( 'ah_admin_nonce' );
?>
<style>
/* ── Tabs ── */
.fb-tab-nav{display:flex;gap:2px;border-bottom:2px solid var(--ah-border);margin-bottom:24px}
.fb-tab-nav a{padding:10px 20px;text-decoration:none;font-weight:500;font-size:14px;color:var(--ah-muted);border-radius:6px 6px 0 0;border:1px solid transparent;border-bottom:none;margin-bottom:-2px}
.fb-tab-nav a.on{color:var(--ah-primary);background:#fff;border-color:var(--ah-border);border-bottom-color:#fff}
.fb-tab-nav a:hover:not(.on){color:var(--ah-text);background:var(--ah-bg-light)}
/* ── Fields table ── */
#fb-tbl{border-collapse:collapse;width:100%}
#fb-tbl th{font-size:11.5px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.5px;padding:10px 10px;background:var(--ah-bg-light);border-bottom:1px solid var(--ah-border);white-space:nowrap}
#fb-tbl td{padding:8px 10px;border-bottom:1px solid #f3f4f6;vertical-align:middle}
#fb-tbl tr:hover td{background:#fafafa}
#fb-tbl td input[type="text"],#fb-tbl td textarea,#fb-tbl td select{width:100%;font-size:13px;padding:7px 10px;border:1.5px solid var(--ah-border);border-radius:6px;font-family:inherit;background:#fff;box-sizing:border-box}
#fb-tbl td input:focus,#fb-tbl td textarea:focus,#fb-tbl td select:focus{outline:none;border-color:var(--ah-primary);box-shadow:0 0 0 2px rgba(37,99,235,.1)}
#fb-tbl td textarea{min-height:66px;resize:vertical}
.fb-drag{cursor:grab;color:var(--ah-muted);font-size:20px;padding:0 4px;user-select:none;display:block;text-align:center}
.fb-drag:active{cursor:grabbing}
.fb-chk{width:18px!important;height:18px;cursor:pointer;accent-color:var(--ah-primary);transform:scale(1.2)}
.fb-hidden{display:none!important}
.fb-ghost{opacity:.35;background:#eff6ff!important}
/* ── Shortcode pill ── */
.fb-sc-pill{display:inline-flex;align-items:center;gap:8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:7px 12px;font-family:monospace;font-size:13px;color:#334155}
.fb-sc-copy{background:var(--ah-primary);color:#fff;border:none;border-radius:5px;padding:5px 10px;font-size:12px;cursor:pointer;white-space:nowrap}
.fb-sc-copy:hover{background:var(--ah-primary-dark)}
/* ── Submissions ── */
.sub-data-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:8px}
.sub-data-item{background:#f8fafc;border:1px solid var(--ah-border);border-radius:6px;padding:10px 12px}
.sub-data-lbl{font-size:11px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.sub-data-val{font-size:13.5px;color:var(--ah-text);word-break:break-word}
.sub-row-open td{background:#f0f9ff!important}
.sub-meta-box{background:#fff;border:1px solid var(--ah-border);border-radius:8px;padding:14px 16px;margin-top:14px;display:grid;grid-template-columns:200px 1fr auto;gap:12px;align-items:start}
.sub-status-select{padding:7px 10px;border:1.5px solid var(--ah-border);border-radius:6px;font-size:13px;background:#fff}
.sub-notes-ta{width:100%;font-size:13px;padding:7px 10px;border:1.5px solid var(--ah-border);border-radius:6px;font-family:inherit;min-height:60px;resize:vertical;box-sizing:border-box}
.sub-notes-ta:focus{outline:none;border-color:var(--ah-primary)}
.sub-save-btn{padding:8px 18px;background:var(--ah-success);color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:500;white-space:nowrap}
.sub-save-btn:hover{background:#15803d}
.sub-status-badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:11.5px;font-weight:600;text-transform:capitalize}
.ssb-new{background:#fef3c7;color:#92400e}.ssb-read{background:#dbeafe;color:#1e40af}.ssb-replied{background:#d1fae5;color:#065f46}.ssb-closed{background:#f3f4f6;color:#4b5563}
/* ── Disable-rules flag ── */
.fb-flag-row{display:flex;align-items:center;gap:8px;padding:8px 0;border-top:1px solid #f3f4f6;margin-top:12px}
.fb-flag-row label{margin:0;font-size:13px;color:var(--ah-text);cursor:pointer}
@media (max-width:782px) {
  .sub-meta-box{grid-template-columns:1fr}
}
</style>

<div class="wrap ah-wrap">
	<?php AdminComponents::pageHeader( 'feedback', 'Form Builder', 'Create and manage contact forms with field builder and submissions.' ); ?>
	<?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>

	<?php if ( $action === 'edit' && $current ) : ?>
		<?php AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-form-builder' ), admin_url( 'admin.php' ) ) ); ?>

		<!-- Shortcode -->
		<div style="margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
			<span style="font-size:13px;font-weight:500;color:var(--ah-text)">Shortcode:</span>
			<span class="fb-sc-pill" id="fb-sc-text">[ah_form id="<?php echo esc_html( $form_id ); ?>"]</span>
			<button type="button" class="fb-sc-copy" id="fb-sc-copy">Copy</button>
			<span style="font-size:12px;color:var(--ah-muted)">- paste into any page or template</span>
		</div>

		<!-- Tab nav -->
		<div class="fb-tab-nav">
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'action' => 'edit', 'tab' => 'build' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'build' === $active_tab ? 'on' : ''; ?>">Build Form</a>
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'action' => 'edit', 'tab' => 'submissions' ), admin_url( 'admin.php' ) ) ); ?>" class="<?php echo 'submissions' === $active_tab ? 'on' : ''; ?>">
				Submissions <span style="background:var(--ah-border);border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700"><?php echo esc_html( $status_counts['all'] ); ?></span>
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
					<div class="ah-form-row" style="margin:0"><label>Submit Button Label</label><input type="text" name="submit_label" value="<?php echo esc_attr( $current->submit_label ?? '' ); ?>" placeholder="Send Message"></div>
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

			<!-- Agreement / Terms section -->
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
							<label style="font-size:12px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:8px">Display as</label>
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
							<input type="text" name="agr_url" id="agr_url" value="<?php echo esc_attr( $agr['url'] ); ?>" placeholder="https://... or /privacy-policy/ or #section">
						</div>
						<div id="agr-popup-wrap" class="ah-form-row" style="margin:0;<?php echo ( 'popup' !== $agr['type'] ) ? 'display:none' : ''; ?>">
							<label>Popup HTML content <small style="font-weight:400">(shown in a modal when the link is clicked - HTML allowed)</small></label>
							<textarea name="agr_popup_html" id="agr_popup_html" style="min-height:140px;font-family:monospace;font-size:12px;width:100%;padding:8px 10px;border:1.5px solid var(--ah-border);border-radius:6px;box-sizing:border-box;resize:vertical"><?php echo esc_textarea( $agr['popup_html'] ?? '' ); ?></textarea>
						</div>
					</div>
					<div class="ah-form-row" style="margin:0 0 16px">
						<label>Text after the link <small style="font-weight:400">(optional)</small></label>
						<input type="text" name="agr_after" id="agr_after" value="<?php echo esc_attr( $agr['after'] ); ?>" placeholder="before submitting this form.">
					</div>
					<!-- Live preview -->
					<div style="padding:14px 18px;background:var(--ah-bg-light);border:1px solid var(--ah-border);border-radius:8px">
						<div style="font-size:11px;font-weight:700;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Preview</div>
						<label style="display:flex;align-items:flex-start;gap:10px;font-size:14px;color:var(--ah-text);cursor:default">
							<input type="checkbox" disabled style="margin-top:3px;width:17px;height:17px;flex-shrink:0">
							<span>
								<span id="agr-prev-before"><?php echo esc_html( $agr['before'] ); ?></span>
								<a id="agr-prev-link" href="#" onclick="return false" style="color:var(--ah-primary);text-decoration:underline;font-weight:600;margin:0 3px"><?php echo esc_html( $agr['link_text'] ); ?></a>
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
				<p style="font-size:13px;color:var(--ah-muted);margin:0 0 16px">Drag <strong>&#x2807;</strong> to reorder. Fields appear on the form in this order.</p>

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
								<td><span class="fb-drag">&#x2807;</span></td>
								<td><input type="text" class="fb-label" value="<?php echo esc_attr( $f->label ); ?>" placeholder="Field label"></td>
								<td>
									<select class="fb-type">
										<?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>" <?php selected( $f->field_type, $tv ); ?>><?php echo esc_html( $tl ); ?></option><?php endforeach; ?>
									</select>
								</td>
								<td><input type="text" class="fb-ph<?php echo 'select' === $f->field_type ? ' fb-hidden' : ''; ?>" value="<?php echo esc_attr( $f->placeholder ?? '' ); ?>" placeholder="Placeholder text"></td>
								<td><textarea class="fb-opts<?php echo 'select' !== $f->field_type ? ' fb-hidden' : ''; ?>" rows="3" placeholder="Option A&#10;Option B&#10;Option C"><?php echo esc_textarea( implode( "\n", $f->options ?? array() ) ); ?></textarea></td>
								<td class="<?php echo 'hidden' === $f->field_type ? ' fb-hidden' : ''; ?>"><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"><?php echo esc_textarea( $f->description ?? '' ); ?></textarea></td>
								<td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"<?php checked( $f->is_required && 'hidden' !== $f->field_type ); ?><?php echo 'hidden' === $f->field_type ? ' disabled style="opacity:.3"' : ''; ?>></td>
								<td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">&#10005;</button></td>
							</tr>
							<?php endforeach; ?>
							<?php if ( empty( $fields ) ) : ?>
							<tr class="fb-row" data-key="">
								<td><span class="fb-drag">&#x2807;</span></td>
								<td><input type="text" class="fb-label" value="" placeholder="Field label"></td>
								<td><select class="fb-type"><?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>"><?php echo esc_html( $tl ); ?></option><?php endforeach; ?></select></td>
								<td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
								<td><textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;Option B&#10;Option C"></textarea></td>
								<td><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"></textarea></td>
								<td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"></td>
								<td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">&#10005;</button></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div style="margin-top:20px;display:flex;gap:8px;align-items:center;">
					<button type="submit" class="ah-btn ah-btn-primary">Save Form</button>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-form-builder', 'delete_form' => $form_id ), admin_url( 'admin.php' ) ), 'ah_del_form' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Form" data-confirm="This form and all submissions will be permanently removed.">Delete Form</a>
				</div>
			</div>
		</form>

		<template id="fb-row-tpl">
			<tr class="fb-row" data-key="">
				<td><span class="fb-drag">&#x2807;</span></td>
				<td><input type="text" class="fb-label" value="" placeholder="Field label"></td>
				<td><select class="fb-type"><?php foreach ( $field_types as $tv => $tl ) : ?><option value="<?php echo esc_attr( $tv ); ?>"><?php echo esc_html( $tl ); ?></option><?php endforeach; ?></select></td>
				<td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
				<td><textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;Option B&#10;Option C"></textarea></td>
				<td><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"></textarea></td>
				<td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"></td>
				<td><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">&#10005;</button></td>
			</tr>
		</template>

		<?php else : ?>
		<!-- ════════════════════ Submissions ════════════════════ -->
		<?php
		$sub_search = sanitize_text_field( $_GET['sub_s'] ?? '' );
		$subs       = AH_Form_Builder::get_submissions_filtered( $form_id, $sub_status, 200, 0 );
		if ( $sub_search ) {
			$subs = array_values( array_filter( $subs, static function( $s ) use ( $sub_search ) {
				return false !== stripos( is_string( $s['data'] ?? '' ) ? $s['data'] : wp_json_encode( $s['data'] ?? '' ), $sub_search );
			} ) );
		}
		$f_keys = array_column( $fields, 'label', 'field_key' );
		?>

		<?php
		$sub_status_options = array( '' => 'All' );
		foreach ( array( 'new', 'read', 'replied', 'closed' ) as $sv ) {
			$sub_status_options[ $sv ] = ucfirst( $sv ) . ' (' . ( $status_counts[ $sv ] ?? 0 ) . ')';
		}
		AdminComponents::filterBar( array(
			'page_slug'          => 'ah-form-builder',
			'search_placeholder' => 'Search submissions...',
			'search_name'        => 'sub_s',
			'search_value'       => $sub_search,
			'hidden_inputs'      => array( 'form_id' => $form_id, 'action' => 'edit', 'tab' => 'submissions' ),
			'filters'            => array(
				array(
					'name'     => 'sub_status',
					'options'  => $sub_status_options,
					'selected' => $sub_status,
				),
			),
		) );
		?>

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
						$s_status = $s['sub_status'] ?? 'new';
						$ssb_cls  = 'ssb-' . esc_attr( $s_status );
						$s_id     = (int) $s['id'];
					?>
					<tr class="fb-sub-row" data-id="<?php echo esc_attr( $s_id ); ?>">
						<td style="text-align:center;cursor:pointer" class="fb-toggle">&#9654;</td>
						<td style="color:var(--ah-muted);font-size:12px">#<?php echo esc_html( $s_id ); ?></td>
						<td><span class="sub-status-badge <?php echo esc_attr( $ssb_cls ); ?>"><?php echo esc_html( $s_status ); ?></span></td>
						<?php foreach ( $fields as $fi ) : ?>
							<?php $v = $s['data'][ $fi->field_key ] ?? ''; ?>
							<td><?php echo esc_html( mb_strimwidth( (string) $v, 0, 60, '...' ) ); ?></td>
						<?php endforeach; ?>
						<td><small><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $s['created_at'] ) ) ); ?></small></td>
						<td>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'action' => 'edit', 'tab' => 'submissions', 'del_sub' => $s_id ), admin_url( 'admin.php' ) ), 'ah_del_sub_fb' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Submission" data-confirm="This submission will be permanently removed.">Delete</a>
						</td>
					</tr>
					<tr class="fb-sub-detail fb-hidden" id="fb-det-<?php echo esc_attr( $s_id ); ?>">
						<td colspan="<?php echo 5 + count( $fields ); ?>" style="padding:0">
							<div style="padding:16px 20px;background:var(--ah-bg-light);border-top:1px solid var(--ah-border)">
								<div class="sub-data-grid">
									<?php foreach ( $s['data'] as $k => $v ) : if ( ! $v || 'agreed' === $v ) continue; ?>
									<div class="sub-data-item">
										<div class="sub-data-lbl"><?php echo esc_html( $f_keys[ $k ] ?? $k ); ?></div>
										<div class="sub-data-val"><?php echo nl2br( esc_html( $v ) ); ?></div>
									</div>
									<?php endforeach; ?>
								</div>
								<div class="sub-meta-box">
									<div>
										<label style="font-size:11.5px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px">Status</label>
										<select class="sub-status-select" data-id="<?php echo esc_attr( $s_id ); ?>">
											<?php foreach ( array( 'new', 'read', 'replied', 'closed' ) as $sv ) : ?>
												<option value="<?php echo esc_attr( $sv ); ?>" <?php selected( $s_status, $sv ); ?>><?php echo esc_html( ucfirst( $sv ) ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div>
										<label style="font-size:11.5px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px">Admin Notes</label>
										<textarea class="sub-notes-ta" data-id="<?php echo esc_attr( $s_id ); ?>" placeholder="Internal notes about this submission..."><?php echo esc_textarea( $s['admin_notes'] ?? '' ); ?></textarea>
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
		<?php endif; // tab ?>

	<?php else : ?>
		<!-- ════════════════════ List Page ════════════════════ -->
		<?php
		$search = sanitize_text_field( $_GET['s'] ?? '' );
		$filtered = $all_forms;
		if ( $search ) {
			$filtered = array_values( array_filter( $filtered, function ( $f ) use ( $search ) {
				return stripos( $f->name, $search ) !== false;
			} ) );
		}
		?>

		<?php AdminComponents::filterBar( array(
			'page_slug'          => 'ah-form-builder',
			'search_placeholder' => 'Search forms...',
			'search_value'       => $search,
			'add_url'            => '#',
			'add_label'          => '',
			'extra_fields'       => '<button type="button" class="ah-btn ah-btn-primary" id="fb-new-btn">+ New Form</button>',
		) ); ?>

		<!-- New form inline dialog -->
		<div id="fb-new-dialog" style="display:none;background:#fff;border:1px solid var(--ah-border);border-radius:10px;padding:20px 24px;margin-bottom:20px;max-width:480px;box-shadow:0 4px 20px rgba(0,0,0,.1)">
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

		<?php
		$rows = array();
		foreach ( $filtered as $f ) {
			$counts = AH_Form_Builder::count_by_status( (int) $f->id );
			$row = new \stdClass();
			$row->id       = (int) $f->id;
			$row->name     = $f->name;
			$row->status   = $f->status ?? 'active';
			$row->count    = $counts['all'] ?? 0;
			$row->new_count = $counts['new'] ?? 0;
			$row->edit_url = add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $f->id, 'action' => 'edit', 'tab' => 'build' ), admin_url( 'admin.php' ) );
			$row->subs_url = add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $f->id, 'action' => 'edit', 'tab' => 'submissions' ), admin_url( 'admin.php' ) );
			$row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-form-builder', 'delete_form' => $f->id ), admin_url( 'admin.php' ) ), 'ah_del_form' );
			$rows[] = $row;
		}
		AdminComponents::dataTable( array(
			'columns' => array(
				array( 'label' => 'Form Name', 'render' => function ( $r ) {
					return '<strong>' . esc_html( $r->name ) . '</strong>';
				} ),
				array( 'label' => 'Status', 'render' => function ( $r ) {
					return '<span class="ah-badge ah-badge-' . esc_attr( $r->status ) . '">' . esc_html( ucfirst( $r->status ) ) . '</span>';
				} ),
				array( 'label' => 'Submissions', 'render' => function ( $r ) {
					$html = '<a href="' . esc_url( $r->subs_url ) . '" style="text-decoration:none;color:var(--ah-text);font-weight:600;">' . esc_html( $r->count ) . '</a>';
					if ( $r->new_count > 0 ) {
						$html .= ' <span class="ah-badge ah-badge-new">' . esc_html( $r->new_count ) . ' new</span>';
					}
					return $html;
				} ),
				array( 'label' => 'Shortcode', 'render' => function ( $r ) {
					return '<code style="font-size:11px;background:#f0f4ff;color:#3b5bdb;padding:2px 6px;border-radius:3px;border:1px solid #c5d0e6;">[ah_form id="' . esc_html( $r->id ) . '"]</code>';
				} ),
			),
			'items'         => $rows,
			'empty_message' => 'No forms yet. Click "+ New Form" to create one.',
			'actions'       => function ( $r ) {
				$html = '<a href="' . esc_url( $r->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
				$html .= ' <a href="' . esc_url( $r->subs_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Submissions</a>';
				$html .= ' <a href="' . esc_url( $r->delete_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Form" data-confirm="This form and all submissions will be permanently removed.">Delete</a>';
				return $html;
			},
		) ); ?>
	<?php endif; ?>
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
		if (type === 'select' || type === 'radio' || type === 'checkbox') {
			$ph.addClass('fb-hidden');
			$opts.removeClass('fb-hidden').attr('placeholder', 'Option A\nOption B\nOption C');
			$desc.removeClass('fb-hidden');
			$req.prop('disabled', false).css('opacity', '');
		} else if (type === 'hidden') {
			$ph.removeClass('fb-hidden').attr('placeholder', 'Value sent with form');
			$opts.addClass('fb-hidden');
			$desc.addClass('fb-hidden');
			$req.prop('checked', false).prop('disabled', true).css('opacity', '0.3');
		} else if (type === 'markup') {
			$ph.addClass('fb-hidden');
			$opts.addClass('fb-hidden');
			$desc.removeClass('fb-hidden');
			$req.prop('checked', false).prop('disabled', true).css('opacity', '0.3');
		} else {
			$ph.removeClass('fb-hidden').attr('placeholder', 'Placeholder text');
			$opts.addClass('fb-hidden');
			$desc.removeClass('fb-hidden');
			$req.prop('disabled', false).css('opacity', '');
		}
	}
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
			if (type === 'select' || type === 'radio' || type === 'checkbox') {
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
	$(document).on('click', '#fb-sc-copy', function () {
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
		$(this).text($det.hasClass('fb-hidden') ? '\u25B6' : '\u25BC');
		$(this).closest('tr').toggleClass('sub-row-open');
	});

	// ── Save submission meta via AJAX ──
	$('#fb-subs').on('click', '.sub-save-btn', function () {
		var id      = $(this).data('id');
		var $btn    = $(this);
		var $msg    = $('.sub-save-msg[data-id="' + id + '"]');
		var status  = $('.sub-status-select[data-id="' + id + '"]').val();
		var notes   = $('.sub-notes-ta[data-id="' + id + '"]').val();
		$btn.prop('disabled', true).text('Saving...');
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
