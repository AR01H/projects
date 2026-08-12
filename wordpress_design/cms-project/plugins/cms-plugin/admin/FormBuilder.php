<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

$notice     = '';
$n_type     = 'success';
$action     = sanitize_key( $_GET['action'] ?? 'list' );
$form_id    = (int) ( $_GET['form_id'] ?? 0 );
$active_tab = sanitize_key( $_GET['tab'] ?? 'build' );
$sub_status = sanitize_key( $_GET['sub_status'] ?? '' );
$sub_view_id = (int) ( $_GET['sub'] ?? 0 );

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

// ── Handle: site-wide CAPTCHA keys ──
if ( isset( $_POST['ah_captcha_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_captcha_nonce'], 'ah_captcha' ) ) wp_die( 'Security.' );
	AH_Form_Builder::save_captcha_settings( array(
		'provider'   => wp_unslash( $_POST['cap_provider'] ?? 'none' ),
		'site_key'   => wp_unslash( $_POST['cap_site_key'] ?? '' ),
		'secret_key' => wp_unslash( $_POST['cap_secret_key'] ?? '' ),
		'threshold'  => wp_unslash( $_POST['cap_threshold'] ?? '0.5' ),
	) );
	$notice = 'Spam protection settings saved.';
}

// ── Handle: bulk submission actions ──
if ( isset( $_POST['ah_bulk_subs_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_bulk_subs_nonce'], 'ah_bulk_subs' ) ) wp_die( 'Security.' );
	$bulk_ids = array_filter( array_map( 'intval', (array) ( $_POST['sub_ids'] ?? array() ) ) );
	$bulk_act = sanitize_key( $_POST['bulk_action'] ?? '' );
	if ( $bulk_ids && $bulk_act ) {
		if ( 'delete' === $bulk_act ) {
			foreach ( $bulk_ids as $bid ) {
				AH_Form_Builder::delete_submission( $bid );
			}
			$notice = count( $bulk_ids ) . ' submission(s) deleted.';
		} elseif ( in_array( $bulk_act, array( 'new', 'read', 'replied', 'closed' ), true ) ) {
			foreach ( $bulk_ids as $bid ) {
				$row = AH_Form_Builder::get_submission( $bid );
				// Keep whatever notes are already on the record.
				AH_Form_Builder::update_submission_meta( $bid, $bulk_act, (string) ( $row['admin_notes'] ?? '' ) );
			}
			$notice = count( $bulk_ids ) . ' submission(s) marked as ' . $bulk_act . '.';
		}
	}
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
		'save_draft'      => isset( $_POST['save_draft'] ) ? 1 : 0,
		'use_captcha'     => isset( $_POST['use_captcha'] ) ? 1 : 0,
		'custom_css'      => wp_unslash( $_POST['custom_css'] ?? '' ),
		'custom_js'       => wp_unslash( $_POST['custom_js'] ?? '' ),
	) );
	$raw    = wp_unslash( $_POST['fields_json'] ?? '[]' );
	$parsed = json_decode( $raw, true );
	if ( is_array( $parsed ) ) {
		AH_Form_Builder::save_fields( $form_id, $parsed );
	}
	AH_Form_Builder::save_header_style( $form_id, sanitize_key( $_POST['header_style'] ?? '' ) );
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
$field_types = array( 'text' => 'Text', 'email' => 'Email', 'tel' => 'Phone / Tel', 'textarea' => 'Textarea', 'select' => 'Dropdown', 'radio' => 'Radio Buttons', 'checkbox' => 'Checkboxes', 'number' => 'Number', 'date' => 'Date', 'daterange' => 'Date Range', 'color' => 'Color Picker', 'url' => 'URL', 'hidden' => 'Hidden Field', 'file' => 'File Upload', 'markup' => 'Markup / Instructions' );
// Structural rows - they render no input, they shape the fields that follow them.
$struct_types = array( 'fieldset' => 'Field Group (fieldset)', 'step' => 'Step / Page Break' );
$group_modes  = array(
	'open'      => 'Always open',
	'expanded'  => 'Collapsible - starts open',
	'collapsed' => 'Collapsible - starts closed',
	'accordion' => 'Accordion - closes other groups',
);

/** Type <select> for a builder row, with fields and structure markers separated. */
$type_select = static function ( string $sel ) use ( $field_types, $struct_types ) {
	$h = '<select class="fb-type"><optgroup label="Fields">';
	foreach ( $field_types as $tv => $tl ) {
		$h .= '<option value="' . esc_attr( $tv ) . '"' . selected( $sel, $tv, false ) . '>' . esc_html( $tl ) . '</option>';
	}
	$h .= '</optgroup><optgroup label="Structure">';
	foreach ( $struct_types as $tv => $tl ) {
		$h .= '<option value="' . esc_attr( $tv ) . '"' . selected( $sel, $tv, false ) . '>' . esc_html( $tl ) . '</option>';
	}
	return $h . '</optgroup></select>';
};

/** Expand/collapse behaviour <select>, shown only on Field Group rows. */
$mode_select = static function ( string $sel, bool $show ) use ( $group_modes ) {
	$h = '<select class="fb-fsmode' . ( $show ? '' : ' fb-hidden' ) . '">';
	foreach ( $group_modes as $mv => $ml ) {
		$h .= '<option value="' . esc_attr( $mv ) . '"' . selected( $sel, $mv, false ) . '>' . esc_html( $ml ) . '</option>';
	}
	return $h . '</select>';
};

$width_opts = array(
	'full'       => 'Full width',
	'two-thirds' => 'Two thirds (2/3)',
	'half'       => 'Half (1/2)',
	'third'      => 'One third (1/3)',
	'quarter'    => 'One quarter (1/4)',
);
$cond_ops = array(
	'is'       => 'is',
	'not'      => 'is not',
	'any'      => 'has any value',
	'empty'    => 'is empty',
	'contains' => 'contains',
);
$affix_types = array( 'text', 'email', 'tel', 'url', 'number', 'date' );

/**
 * The per-row "Advanced" drawer. Everything that would otherwise need its own
 * column lives here, so the main table stays scannable.
 */
$adv_panel = static function ( array $set, string $type ) use ( $width_opts, $cond_ops, $affix_types ) {
	$is_struct = in_array( $type, array( 'step', 'fieldset' ), true );
	$sel       = static function ( $val, $opts, $class ) {
		$h = '<select class="' . esc_attr( $class ) . '">';
		foreach ( $opts as $k => $lbl ) {
			$h .= '<option value="' . esc_attr( $k ) . '"' . selected( $val, $k, false ) . '>' . esc_html( $lbl ) . '</option>';
		}
		return $h . '</select>';
	};
	$hide = static function ( bool $show ) {
		return $show ? '' : ' fb-hidden';
	};

	ob_start(); ?>
	<div class="fb-adv-grid">
		<div class="fb-adv-item fb-adv-width<?php echo esc_attr( $hide( 'step' !== $type ) ); ?>">
			<label>Width</label>
			<?php echo $sel( $set['width'] ?? 'full', $width_opts, 'fb-width' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in closure. ?>
		</div>
		<div class="fb-adv-item fb-adv-icon<?php echo esc_attr( $hide( true ) ); ?>">
			<label>Icon</label>
			<input type="text" class="fb-icon" list="fb-icon-list" value="<?php echo esc_attr( $set['icon'] ?? '' ); ?>" placeholder="home, user, star… or an emoji">
		</div>
		<div class="fb-adv-item fb-adv-default<?php echo esc_attr( $hide( ! in_array( $type, array( 'step', 'fieldset', 'markup' ), true ) ) ); ?>">
			<label>Default value</label>
			<input type="text" class="fb-default" value="<?php echo esc_attr( $set['default'] ?? '' ); ?>" placeholder="Pre-filled value" maxlength="300">
			<small>Pre-fills the field. For radio/checkbox/dropdown use the option text; for a multi-select checkbox separate several with commas.</small>
		</div>
		<div class="fb-adv-item fb-adv-next<?php echo esc_attr( $hide( 'step' === $type ) ); ?>">
			<label>Next button text</label>
			<input type="text" class="fb-nextlbl" value="<?php echo esc_attr( $set['next_label'] ?? '' ); ?>" placeholder="Next" maxlength="60">
			<small>Wording for the button that leaves this step, e.g. "Continue to Your Home Search". Blank = "Next". The last step shows the form's submit button instead.</small>
		</div>
		<div class="fb-adv-item fb-adv-layout<?php echo esc_attr( $hide( in_array( $type, array( 'radio', 'checkbox' ), true ) ) ); ?>">
			<label>Options layout</label>
			<?php echo $sel( $set['layout'] ?? 'list', array( 'list' => 'List (radio dots)', 'pills' => 'Pills (inline buttons)', 'cards' => 'Cards (equal columns)', 'checks' => 'Boxes with a tick', 'tiles' => 'Tiles (with icons)' ), 'fb-layout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in closure. ?>
		</div>
		<div class="fb-adv-item fb-adv-affix<?php echo esc_attr( $hide( in_array( $type, $affix_types, true ) ) ); ?>">
			<label>Prefix / Suffix</label>
			<div style="display:flex;gap:6px">
				<input type="text" class="fb-prefix" value="<?php echo esc_attr( $set['prefix'] ?? '' ); ?>" placeholder="£" maxlength="8">
				<input type="text" class="fb-suffix" value="<?php echo esc_attr( $set['suffix'] ?? '' ); ?>" placeholder="%" maxlength="8">
			</div>
		</div>
		<div class="fb-adv-item fb-adv-file<?php echo esc_attr( $hide( 'file' === $type ) ); ?>">
			<label>Upload limits</label>
			<div style="display:flex;gap:6px">
				<input type="number" class="fb-maxsize" min="1" max="20" value="<?php echo esc_attr( $set['max_size'] ?? 5 ); ?>" title="Maximum size in MB" style="max-width:78px">
				<input type="text" class="fb-accept" value="<?php echo esc_attr( $set['accept'] ?? '' ); ?>" placeholder="pdf, jpg, png" title="Allowed extensions">
			</div>
			<small>MB cap (max 20) and allowed extensions. Blank = pdf, doc, images.</small>
		</div>
		<div class="fb-adv-item fb-adv-intl<?php echo esc_attr( $hide( 'tel' === $type ) ); ?>">
			<label>Country code</label>
			<div style="display:flex;gap:8px;align-items:center">
				<label style="display:flex;align-items:center;gap:6px;font-weight:400;margin:0;white-space:nowrap">
					<input type="checkbox" class="fb-intl fb-chk" value="1" <?php checked( ! empty( $set['intl'] ) ); ?>> Show selector
				</label>
				<?php echo $sel( $set['intl_cc'] ?? '+44', array_combine( array_values( AH_Form_Builder::dial_codes() ), array_map( static function ( $iso, $dial ) { return $iso . ' ' . $dial; }, array_keys( AH_Form_Builder::dial_codes() ), array_values( AH_Form_Builder::dial_codes() ) ) ), 'fb-intlcc' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in closure. ?>
			</div>
		</div>
		<div class="fb-adv-item fb-adv-class">
			<label>CSS class</label>
			<input type="text" class="fb-class" value="<?php echo esc_attr( AH_Form_Builder::css_class( $set ) ); ?>" placeholder="e.g. my-field">
		</div>
		<div class="fb-adv-item fb-adv-cond" style="grid-column:1/-1">
			<label>Show this <?php echo $is_struct ? 'group' : 'field'; ?> only when&hellip;</label>
			<div class="fb-cond-row">
				<select class="fb-cond-field" data-saved="<?php echo esc_attr( $set['cond']['field'] ?? '' ); ?>"></select>
				<?php echo $sel( $set['cond']['op'] ?? 'is', $cond_ops, 'fb-cond-op' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in closure. ?>
				<input type="text" class="fb-cond-value" value="<?php echo esc_attr( $set['cond']['value'] ?? '' ); ?>" placeholder="value to match">
			</div>
			<small>Leave the first box on <em>no condition</em> to always show it. Match the option's stored value, not its label.</small>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
};
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
#fb-tbl td{padding:8px 10px;border-bottom:1px solid var(--ah-bg-light);vertical-align:middle}
#fb-tbl tr:hover td{background:var(--ah-bg-light)}
#fb-tbl td input[type="text"],#fb-tbl td textarea,#fb-tbl td select{width:100%;font-size:13px;padding:7px 10px;border:1.5px solid var(--ah-border);border-radius:6px;font-family:inherit;background:#fff;box-sizing:border-box}
#fb-tbl td input:focus,#fb-tbl td textarea:focus,#fb-tbl td select:focus{outline:none;border-color:var(--ah-primary);box-shadow:0 0 0 2px rgba(37,99,235,.1)}
#fb-tbl td textarea{min-height:66px;resize:vertical}
.fb-drag{cursor:grab;color:var(--ah-muted);font-size:20px;padding:0 4px;user-select:none;display:block;text-align:center}
.fb-drag:active{cursor:grabbing}
.fb-chk{width:18px!important;height:18px;cursor:pointer;accent-color:var(--ah-primary);transform:scale(1.2)}
.fb-hidden{display:none!important}
.fb-ghost{opacity:.35;background:var(--ah-bg-light)!important}
/* ── Structural rows (step / group) read as bands, not fields ── */
#fb-tbl tr.fb-row-step td{background:#eef2ff;border-bottom:1px solid #c7d2fe}
#fb-tbl tr.fb-row-step:hover td{background:#e0e7ff}
#fb-tbl tr.fb-row-fieldset td{background:#f0fdf4;border-bottom:1px solid #bbf7d0}
#fb-tbl tr.fb-row-fieldset:hover td{background:#dcfce7}
#fb-tbl tr.fb-row-step .fb-label,#fb-tbl tr.fb-row-fieldset .fb-label{font-weight:600}
.fb-struct-tag{display:inline-block;font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;padding:2px 7px;border-radius:10px;margin-bottom:5px}
.fb-row-step .fb-struct-tag{background:#4338ca;color:#fff}
.fb-row-fieldset .fb-struct-tag{background:#15803d;color:#fff}
.fb-row:not(.fb-row-step):not(.fb-row-fieldset) .fb-struct-tag{display:none}
.fb-legend{display:flex;gap:18px;flex-wrap:wrap;font-size:12px;color:var(--ah-muted);margin:0 0 16px}
.fb-legend span{display:inline-flex;align-items:center;gap:6px}
.fb-legend i{width:11px;height:11px;border-radius:3px;display:inline-block}
/* ── Advanced settings drawer ── */
.fb-gear{background:none;border:1px solid var(--ah-border);border-radius:6px;width:30px;height:30px;cursor:pointer;font-size:15px;line-height:1;color:var(--ah-muted);padding:0}
.fb-gear:hover,.fb-gear.on{background:var(--ah-primary);border-color:var(--ah-primary);color:#fff}
/* ── Row actions stay reachable ──
   The columns do not fit the admin content area, so the wrapper scrolls
   sideways - which used to carry the last column off the right edge, and the
   remove button with it. All three per-row buttons now live in ONE cell that is
   pinned to the scrollport: a single pinned column needs no offset arithmetic,
   so there is no seam to keep in sync with a neighbour's width. A pinned cell
   needs its own opaque background or the scrolled row shows through - these
   mirror the row backgrounds set above. */
#fb-tbl th:last-child,#fb-tbl tr.fb-row>td:last-child{position:sticky;right:0;z-index:2;background:#fff;border-left:1px solid var(--ah-border)}
/* Insert-below: a new field lands where you are instead of at the very bottom,
   so a field added late no longer has to be dragged the whole way up. */
/* Not display:flex - a flex <td> drops out of the table's column sizing. */
.fb-actions{white-space:nowrap;text-align:center}
.fb-actions>*{vertical-align:middle}
.fb-actions>*+*{margin-left:5px}
.fb-ins{background:none;border:1px solid var(--ah-border);border-radius:6px;width:30px;height:30px;cursor:pointer;font-size:17px;font-weight:700;line-height:1;color:var(--ah-muted);padding:0}
.fb-ins:hover{background:#16a34a;border-color:#16a34a;color:#fff}
/* skip the pinned cell: its background is what hides the scrolled row behind it */
#fb-tbl tr.fb-row-new>td:not(:last-child){animation:fb-flash 1.1s ease-out}
@keyframes fb-flash{from{background:#fef9c3}to{background:transparent}}
#fb-tbl th:last-child{background:var(--ah-bg-light);z-index:3}
#fb-tbl tr.fb-row:hover>td:last-child{background:var(--ah-bg-light)}
#fb-tbl tr.fb-row-step>td:last-child{background:#eef2ff}
#fb-tbl tr.fb-row-step:hover>td:last-child{background:#e0e7ff}
#fb-tbl tr.fb-row-fieldset>td:last-child{background:#f0fdf4}
#fb-tbl tr.fb-row-fieldset:hover>td:last-child{background:#dcfce7}
.fb-del{font-weight:700}
#fb-tbl tr.fb-adv>td{background:#f8fafc;border-bottom:2px solid var(--ah-border);padding:14px 16px}
.fb-adv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}
.fb-adv-item label{display:block;font-size:11px;font-weight:700;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
.fb-adv-item input[type="text"],.fb-adv-item select{width:100%;font-size:13px;padding:7px 9px;border:1.5px solid var(--ah-border);border-radius:6px;font-family:inherit;background:#fff;box-sizing:border-box}
.fb-adv-item small{display:block;font-size:11.5px;color:var(--ah-muted);margin-top:5px}
.fb-cond-row{display:grid;grid-template-columns:2fr 1fr 2fr;gap:8px}
@media (max-width:782px){.fb-cond-row{grid-template-columns:1fr}}
/* ── Shortcode pill ── */
.fb-sc-pill{display:inline-flex;align-items:center;gap:8px;background:var(--ah-bg-light);border:1px solid var(--ah-border);border-radius:6px;padding:7px 12px;font-family:monospace;font-size:13px;color:var(--ah-text)}
.fb-sc-copy{background:var(--ah-primary);color:#fff;border:none;border-radius:5px;padding:5px 10px;font-size:12px;cursor:pointer;white-space:nowrap}
.fb-sc-copy:hover{background:var(--ah-primary-dark)}
/* ── Submissions ── */
.sub-data-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:8px}
.sub-data-item{background:var(--ah-bg-light);border:1px solid var(--ah-border);border-radius:6px;padding:10px 12px}
.sub-data-lbl{font-size:11px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.sub-data-val{font-size:13.5px;color:var(--ah-text);word-break:break-word}
.sub-row-open td{background:var(--ah-bg-light)!important}
.sub-meta-box{background:#fff;border:1px solid var(--ah-border);border-radius:8px;padding:14px 16px;margin-top:14px;display:grid;grid-template-columns:200px 1fr auto;gap:12px;align-items:start}
.sub-status-select{padding:7px 10px;border:1.5px solid var(--ah-border);border-radius:6px;font-size:13px;background:#fff}
.sub-notes-ta{width:100%;font-size:13px;padding:7px 10px;border:1.5px solid var(--ah-border);border-radius:6px;font-family:inherit;min-height:60px;resize:vertical;box-sizing:border-box}
.sub-notes-ta:focus{outline:none;border-color:var(--ah-primary)}
.sub-save-btn{padding:8px 18px;background:var(--ah-success);color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:500;white-space:nowrap}
.sub-save-btn:hover{background:var(--ah-success)}
.sub-status-badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:11.5px;font-weight:600;text-transform:capitalize}
.ssb-new{background:#fef3c7;color:var(--ah-warning)}.ssb-read{background:var(--ah-bg-light);color:var(--ah-primary)}.ssb-replied{background:var(--ah-bg-light);color:var(--ah-success)}.ssb-closed{background:var(--ah-bg-light);color:#4b5563}
/* ── JSON import / export panel ── */
#fb-json-toggle.on{background:var(--ah-primary);border-color:var(--ah-primary);color:#fff}
#fb-json-panel{border:1px solid var(--ah-border);border-radius:10px;background:var(--ah-bg-light);padding:16px;margin-bottom:18px}
.fb-json-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:16px;align-items:start}
@media (max-width:900px){.fb-json-grid{grid-template-columns:1fr}}
.fb-json-col{background:#fff;border:1px solid var(--ah-border);border-radius:9px;padding:15px 16px}
.fb-json-head{display:flex;align-items:center;gap:9px;margin:0 0 4px;font-size:14.5px;font-weight:700;color:var(--ah-text)}
.fb-json-ico{display:inline-flex;align-items:center;justify-content:center;width:25px;height:25px;border-radius:50%;background:var(--ah-primary);color:#fff;font-size:14px;line-height:1;flex-shrink:0}
.fb-json-sub{font-size:12.5px;color:var(--ah-muted);margin:0 0 12px;line-height:1.55}
.fb-json-lbl{display:block;font-size:11px;font-weight:700;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px}
#fb-json-input{width:100%;box-sizing:border-box;font-family:Consolas,Monaco,monospace;font-size:12.5px;line-height:1.55;padding:10px 12px;border:1.5px solid var(--ah-border);border-radius:8px;background:#fcfcfd;resize:vertical;margin-top:10px}
#fb-json-input:focus{outline:none;border-color:var(--ah-primary);background:#fff}
#fb-json-file{font-size:12px;width:100%}
.fb-json-count{font-size:12.5px;color:var(--ah-muted);background:var(--ah-bg-light);border:1px solid var(--ah-border);border-radius:7px;padding:9px 12px;margin-bottom:12px}
.fb-json-count strong{color:var(--ah-primary);font-size:15px}
.fb-json-mode{display:flex;gap:9px;align-items:flex-start;padding:9px 11px;border:1.5px solid var(--ah-border);border-radius:8px;background:#fff;margin-bottom:8px;cursor:pointer;font-size:13px;line-height:1.4}
.fb-json-mode:hover{border-color:var(--ah-primary)}
.fb-json-mode input{margin-top:3px;accent-color:var(--ah-primary)}
.fb-json-mode small{color:var(--ah-muted);font-size:11.5px}
.fb-json-hint{font-size:11.5px;color:var(--ah-muted);margin:9px 0 0;line-height:1.5}
.fb-json-msg{margin-top:14px;padding:11px 14px;border-radius:8px;font-size:13px;line-height:1.55;border:1px solid}
.fb-json-msg.ok{background:#f0fdf4;border-color:#bbf7d0;color:#166534}
.fb-json-msg.warn{background:#fffbeb;border-color:#fde68a;color:#92400e}
.fb-json-msg.err{background:#fef2f2;border-color:#fecaca;color:#991b1b}
.fb-json-warn{margin:8px 0 0;padding-left:18px;font-size:12.5px}
.fb-json-help{margin-top:12px;font-size:12.5px;color:var(--ah-muted)}
.fb-json-help summary{cursor:pointer;font-weight:600;color:var(--ah-text)}
.fb-json-help p{margin:8px 0 0;line-height:1.6}
.fb-json-help code{background:#fff;border:1px solid var(--ah-border);border-radius:4px;padding:1px 5px;font-size:11.5px}
/* ── Submissions list ── */
.fb-subs-tbl tr.is-unread td{background:#fffdf5}
.fb-subs-tbl tr.is-unread td:nth-child(3){font-weight:700;color:var(--ah-text)}
.fb-bulk-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:10px 12px;background:var(--ah-bg-light);border:1px solid var(--ah-border);border-bottom:none;border-radius:8px 8px 0 0}
.fb-bulk-bar select{padding:6px 10px;border:1.5px solid var(--ah-border);border-radius:6px;font-size:13px;background:#fff}
.fb-bulk-count{font-size:12.5px;color:var(--ah-primary);font-weight:600}
.fb-quick{padding:16px 20px;background:var(--ah-bg-light);border-top:1px solid var(--ah-border)}
.fb-view-dash{color:#c3c7cc}
.fb-pager{display:flex;gap:6px;flex-wrap:wrap;margin-top:14px}
.fb-page{display:inline-block;min-width:32px;text-align:center;padding:6px 9px;border:1px solid var(--ah-border);border-radius:6px;text-decoration:none;font-size:13px;color:var(--ah-text);background:#fff}
.fb-page.on{background:var(--ah-primary);border-color:var(--ah-primary);color:#fff;font-weight:700}
.fb-page:hover:not(.on){background:var(--ah-bg-light)}
/* ── Single submission view ── */
.fb-view-bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.fb-print-area{background:#fff;border:1px solid var(--ah-border);border-radius:10px;overflow:hidden}
.fb-view-head{display:flex;gap:20px;flex-wrap:wrap;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--ah-border);background:var(--ah-bg-light)}
.fb-view-head h2{margin:0 0 3px;font-size:19px}
.fb-view-head p{margin:0;font-size:13px;color:var(--ah-muted)}
.fb-view-meta{display:grid;grid-template-columns:auto auto;gap:3px 12px;margin:0;font-size:12.5px;align-content:start}
.fb-view-meta dt{color:var(--ah-muted);text-transform:uppercase;font-size:10.5px;letter-spacing:.5px;font-weight:700;align-self:center}
.fb-view-meta dd{margin:0;color:var(--ah-text)}
.fb-empty-toggle{display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--ah-muted);padding:10px 24px 0;cursor:pointer}
.fb-view-tbl{width:100%;border-collapse:collapse}
.fb-view-tbl tr.fb-view-step th{background:var(--ah-primary);color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:.7px;padding:9px 24px;text-align:left}
.fb-view-tbl tr.fb-view-sec th{background:var(--ah-bg-light);color:var(--ah-text);font-size:13px;padding:10px 24px;text-align:left;border-top:1px solid var(--ah-border);border-bottom:1px solid var(--ah-border)}
.fb-view-tbl tr.fb-view-row th{width:38%;text-align:left;font-weight:600;font-size:13px;color:var(--ah-muted);padding:9px 24px;vertical-align:top;border-bottom:1px solid #f1f2f4}
.fb-view-tbl tr.fb-view-row td{font-size:13.5px;color:var(--ah-text);padding:9px 24px;vertical-align:top;border-bottom:1px solid #f1f2f4;word-break:break-word}
.fb-view-tbl tr.fb-view-row:hover td,.fb-view-tbl tr.fb-view-row:hover th{background:#fcfcfd}
.fb-view-tbl tr.fb-view-blank{display:none}
.fb-view-tbl.show-blank tr.fb-view-blank{display:table-row}
@media (max-width:782px){
  .fb-view-tbl tr.fb-view-row th{width:42%}
  .fb-view-head{flex-direction:column}
}
/* ── Print: just the submission, none of the admin chrome ── */
@media print{
  #adminmenumain,#adminmenuback,#adminmenuwrap,#wpadminbar,#wpfooter,#screen-meta,#screen-meta-links,
  .fb-noprint,.ah-page-header,.fb-tab-nav,.notice,.update-nag,.ah-notice{display:none!important}
  /* On a single submission only the record itself prints - the page heading,
     back link, shortcode bar and tabs are all screen furniture. */
  .fb-sub-print > *:not(.fb-print-area){display:none!important}
  html,body,#wpwrap,#wpcontent,#wpbody,#wpbody-content,.wrap,.ah-wrap{margin:0!important;padding:0!important;background:#fff!important;float:none!important;width:auto!important}
  .fb-print-area{border:none!important;border-radius:0!important}
  .fb-view-head{background:#fff!important;border-bottom:2px solid #000!important;padding:0 0 10px!important}
  .fb-view-tbl tr.fb-view-step th{background:#eee!important;color:#000!important;padding:7px 0!important;border-top:1px solid #000;border-bottom:1px solid #000}
  .fb-view-tbl tr.fb-view-sec th{background:#f7f7f7!important;padding:7px 0!important}
  .fb-view-tbl tr.fb-view-row th,.fb-view-tbl tr.fb-view-row td{padding:5px 0!important;font-size:11pt}
  .fb-view-tbl tr{page-break-inside:avoid}
  .sub-status-badge{border:1px solid #000;background:#fff!important;color:#000!important}
  a[href]:after{content:""}
}
/* ── Disable-rules flag ── */
.fb-flag-row{display:flex;align-items:center;gap:8px;padding:8px 0;border-top:1px solid var(--ah-bg-light);margin-top:12px}
.fb-flag-row label{margin:0;font-size:13px;color:var(--ah-text);cursor:pointer}
@media (max-width:782px) {
  .sub-meta-box{grid-template-columns:1fr}
}
</style>

<div class="wrap ah-wrap<?php echo ( $sub_view_id && 'submissions' === $active_tab ) ? ' fb-sub-print' : ''; ?>">
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
					<?php $head_style = AH_Form_Builder::get_header_style( $form_id ); ?>
					<div class="ah-form-row" style="margin:0">
						<label>Step Header Design</label>
						<select name="header_style">
							<option value="bar"   <?php selected( $head_style, 'bar' ); ?>>Progress bar on top, title below</option>
							<option value="split" <?php selected( $head_style, 'split' ); ?>>Title left, progress bar right</option>
							<option value="plain" <?php selected( $head_style, 'plain' ); ?>>Title only, no progress bar</option>
						</select>
						<small style="display:block;margin-top:5px;font-size:11.5px;color:var(--ah-muted)">Only applies to multi-step forms (forms with Step markers).</small>
					</div>
				</div>
				<div class="fb-flag-row">
					<input type="checkbox" name="disable_rules" id="fb-disable-rules" class="fb-chk" value="1" <?php checked( ! empty( $current->disable_rules ) ); ?>>
					<label for="fb-disable-rules"><strong>Disable Workflow Manager</strong> - submissions from this form will NOT trigger any automation rules</label>
				</div>
				<div class="fb-flag-row">
					<input type="checkbox" name="use_captcha" id="fb-use-captcha" class="fb-chk" value="1" <?php checked( ! empty( $current->use_captcha ) ); ?>>
					<label for="fb-use-captcha"><strong>Spam protection (CAPTCHA)</strong> - challenge visitors before this form is accepted.
					<?php
					$cap_cfg = AH_Form_Builder::captcha_settings();
					if ( 'none' === $cap_cfg['provider'] || '' === $cap_cfg['site_key'] || '' === $cap_cfg['secret_key'] ) {
						echo '<em style="color:var(--ah-warning)">No keys configured yet - set them up on the Form Builder home screen.</em>';
					} else {
						echo '<em>Using ' . esc_html( $cap_cfg['provider'] ) . '.</em>';
					}
					?></label>
				</div>
				<div class="fb-flag-row">
					<input type="checkbox" name="save_draft" id="fb-save-draft" class="fb-chk" value="1" <?php checked( ! empty( $current->save_draft ) ); ?>>
					<label for="fb-save-draft"><strong>Save draft in the visitor's browser</strong> - unsubmitted answers are kept in localStorage and refilled if the visitor returns. A <em>Clear form</em> button is added, and the draft is wiped on a successful submit. Avoid on forms that collect sensitive details on shared computers.</label>
				</div>
			</div>

			<!-- Custom CSS / JS (this form only) -->
			<div class="ah-card" style="margin-bottom:20px">
				<div class="ah-card-header"><h2>Custom CSS / JS</h2></div>
				<p style="font-size:13px;color:var(--ah-muted);margin:0 0 16px">Applies only to this form. CSS is wrapped in a <code>&lt;style&gt;</code> tag; JS is wrapped in <code>&lt;script&gt;(function(){ ... })();&lt;/script&gt;</code> so variables don't leak globally.</p>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
					<div class="ah-form-row" style="margin:0">
						<label>Custom CSS</label>
						<textarea name="custom_css" rows="8" style="width:100%;font-family:monospace;font-size:12.5px;padding:8px 10px;border:1.5px solid var(--ah-border);border-radius:6px;box-sizing:border-box;resize:vertical" placeholder=".ch-form-submit { background: #1a3c5e; }"><?php echo esc_textarea( $current->custom_css ?? '' ); ?></textarea>
					</div>
					<div class="ah-form-row" style="margin:0">
						<label>Custom JS</label>
						<textarea name="custom_js" rows="8" style="width:100%;font-family:monospace;font-size:12.5px;padding:8px 10px;border:1.5px solid var(--ah-border);border-radius:6px;box-sizing:border-box;resize:vertical" placeholder="// runs after the form renders"><?php echo esc_textarea( $current->custom_js ?? '' ); ?></textarea>
					</div>
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
					<div style="display:flex;gap:8px;flex-wrap:wrap">
						<button type="button" class="ah-btn ah-btn-primary ah-btn-sm" id="fb-add">+ Add Field</button>
						<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" id="fb-add-group">+ Add Group</button>
						<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" id="fb-add-step">+ Add Step</button>
						<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" id="fb-json-toggle">&#8644; JSON</button>
					</div>
				</div>

				<!-- ── JSON import / export ── -->
				<div id="fb-json-panel" style="display:none">
					<div class="fb-json-grid">

						<!-- ── Import ── -->
						<section class="fb-json-col">
							<h4 class="fb-json-head"><span class="fb-json-ico">&#8681;</span> Import fields</h4>
							<p class="fb-json-sub">Upload a <code>.json</code> file, or paste the definitions below.</p>

							<input type="file" id="fb-json-file" accept=".json,application/json">

							<textarea id="fb-json-input" rows="9" spellcheck="false" placeholder='[
  { "field_type": "step", "label": "About You", "settings": { "icon": "user" } },
  { "field_type": "fieldset", "label": "1. About You", "settings": { "icon": "user", "mode": "open" } },
  { "field_type": "text", "label": "Full Name", "placeholder": "Enter your full name", "is_required": true },
  { "field_type": "select", "label": "Buying with", "options": ["On my own", "With a partner"], "settings": { "width": "half" } }
]'></textarea>

							<label class="fb-json-lbl" style="margin-top:12px">How to apply it</label>
							<label class="fb-json-mode"><input type="radio" name="fb_json_mode" value="add" checked> <span><strong>Add</strong><br><small>Append these fields after the existing ones.</small></span></label>
							<label class="fb-json-mode"><input type="radio" name="fb_json_mode" value="update"> <span><strong>Update</strong><br><small>Match by label/key and overwrite those; anything new is added.</small></span></label>
							<label class="fb-json-mode"><input type="radio" name="fb_json_mode" value="replace"> <span><strong>Create (replace all)</strong><br><small>Clear the builder and build it from this JSON.</small></span></label>

							<button type="button" class="ah-btn ah-btn-primary" id="fb-json-apply" style="width:100%;margin-top:4px">Import fields &rarr;</button>
							<p class="fb-json-hint">Fills the table below only. <strong>Nothing is written to the database until you press “Save Form”.</strong></p>
						</section>

						<!-- ── Export ── -->
						<section class="fb-json-col">
							<h4 class="fb-json-head"><span class="fb-json-ico">&#8679;</span> Export fields</h4>
							<p class="fb-json-sub">Save the current builder layout as JSON - steps, groups, widths, icons, tiles and conditions are all included.</p>

							<div class="fb-json-count"><strong id="fb-json-count">0</strong> row<span id="fb-json-count-s">s</span> in the builder</div>

							<button type="button" class="ah-btn ah-btn-primary" id="fb-json-download" style="width:100%">&#8659; Download .json</button>
							<button type="button" class="ah-btn ah-btn-secondary" id="fb-json-copy" style="width:100%;margin-top:8px">Copy to clipboard</button>
							<button type="button" class="ah-btn ah-btn-secondary" id="fb-json-load-current" style="width:100%;margin-top:8px">Show in the box &uarr;</button>

							<p class="fb-json-hint">Exports what is in the table right now, including unsaved edits. Use <em>Show in the box</em> to tweak it and import it straight back.</p>
						</section>
					</div>
					<div id="fb-json-msg" class="fb-json-msg" style="display:none"></div>
					<details class="fb-json-help">
						<summary>Accepted keys</summary>
						<p><code>label</code>, <code>field_type</code>, <code>placeholder</code>, <code>description</code>, <code>is_required</code>, <code>options</code> (array), <code>settings</code>.</p>
						<p><strong>field_type:</strong> text, email, tel, textarea, select, radio, checkbox, number, date, daterange, color, url, hidden, markup, <strong>step</strong>, <strong>fieldset</strong>.</p>
						<p><strong>settings:</strong> <code>width</code> (full/two-thirds/half/third/quarter), <code>icon</code> (icon name, emoji, or an image URL), <code>mode</code> (fieldset: open/expanded/collapsed/accordion), <code>next_label</code> (step: forward-button wording), <code>layout</code> (list/pills/cards/checks/tiles), <code>default</code>, <code>prefix</code>, <code>suffix</code>, <code>intl</code>, <code>intl_cc</code>, <code>class</code>, <code>cond</code> (<code>{"field":"other_key","op":"is","value":"Yes"}</code>).</p>
						<p>Tile options take an icon as a third part: <code>"detached|Detached|home"</code>. The top-level value may be an array, or <code>{"fields":[…]}</code>.</p>
					</details>
				</div>
				<p style="font-size:13px;color:var(--ah-muted);margin:0 0 10px">
					Drag <strong>&#x2807;</strong> to reorder. Fields appear on the form in this order.
					A <strong>Step</strong> starts a new page - the visitor must complete the current page before moving on.
					A <strong>Group</strong> wraps every field below it (until the next Group or Step) in a fieldset that can be expanded or collapsed.
				</p>
				<div class="fb-legend">
					<span><i style="background:#4338ca"></i> Step / Page Break</span>
					<span><i style="background:#15803d"></i> Field Group</span>
					<span><i style="background:var(--ah-border)"></i> Field</span>
				</div>

				<div class="ah-table-wrap">
					<table id="fb-tbl">
						<thead>
							<tr>
								<th style="width:34px"></th>
								<th style="min-width:160px">Field / Step / Group Label</th>
								<th style="width:148px">Type</th>
								<th>Placeholder / Value</th>
								<th style="width:180px">Options <small style="font-weight:400;text-transform:none">(one per line, or <code>value|Label</code>) / Group behaviour</small></th>
								<th style="min-width:160px">Description <small style="font-weight:400;text-transform:none">(help text)</small></th>
								<th style="width:70px;text-align:center">Required</th>
								<th style="text-align:center">Actions</th>
							</tr>
						</thead>
						<tbody id="fb-body">
							<?php foreach ( $fields as $f ) :
								// Mirrors applyTypeUI() in the script below exactly, so a saved radio/
								// checkbox/select field shows its Options editor (and hides Placeholder)
								// from the very first paint - not just after JS re-applies it on load.
								$_fb_is_choice = in_array( $f->field_type, array( 'select', 'radio', 'checkbox' ), true );
								$_fb_is_hidden = 'hidden' === $f->field_type;
								$_fb_is_markup = 'markup' === $f->field_type;
								$_fb_is_step   = 'step' === $f->field_type;
								$_fb_is_fs     = 'fieldset' === $f->field_type;
								$_fb_is_struct = $_fb_is_step || $_fb_is_fs;
								$_fb_no_req    = $_fb_is_hidden || $_fb_is_markup || $_fb_is_struct;
								$_fb_row_cls   = $_fb_is_step ? ' fb-row-step' : ( $_fb_is_fs ? ' fb-row-fieldset' : '' );
								$_fb_lbl_ph    = $_fb_is_step ? 'Step title (e.g. Your details)' : ( $_fb_is_fs ? 'Group title (e.g. Contact info)' : 'Field label' );
								$_fb_desc_ph   = $_fb_is_step ? 'Optional intro text for this step' : ( $_fb_is_fs ? 'Optional text shown inside the group' : 'Optional help text shown below the field' );
								$_fb_mode      = isset( $f->settings['mode'] ) ? (string) $f->settings['mode'] : 'open';
								$_fb_set       = is_array( $f->settings ?? null ) ? $f->settings : array();
								$_fb_uid       = 'r' . (int) $f->id;
							?>
							<tr class="fb-row<?php echo esc_attr( $_fb_row_cls ); ?>" data-key="<?php echo esc_attr( $f->field_key ); ?>" data-uid="<?php echo esc_attr( $_fb_uid ); ?>">
								<td><span class="fb-drag">&#x2807;</span></td>
								<td>
									<span class="fb-struct-tag"><?php echo $_fb_is_step ? 'Step' : 'Group'; ?></span>
									<input type="text" class="fb-label" value="<?php echo esc_attr( $f->label ); ?>" placeholder="<?php echo esc_attr( $_fb_lbl_ph ); ?>">
								</td>
								<td><?php echo $type_select( (string) $f->field_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?></td>
								<td><input type="text" class="fb-ph<?php echo ( $_fb_is_choice || $_fb_is_markup || $_fb_is_struct ) ? ' fb-hidden' : ''; ?>" value="<?php echo esc_attr( $f->placeholder ?? '' ); ?>" placeholder="<?php echo $_fb_is_hidden ? 'Value sent with form' : 'Placeholder text'; ?>"></td>
								<td>
									<textarea class="fb-opts<?php echo ! $_fb_is_choice ? ' fb-hidden' : ''; ?>" rows="3" placeholder="Option A&#10;red|Red Apple &#x1F34E;&#10;Option C"><?php echo esc_textarea( implode( "\n", $f->options ?? array() ) ); ?></textarea>
									<?php echo $mode_select( $_fb_mode, $_fb_is_fs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>
								</td>
								<td><textarea class="fb-desc<?php echo $_fb_is_hidden ? ' fb-hidden' : ''; ?>" rows="2" placeholder="<?php echo esc_attr( $_fb_desc_ph ); ?>"><?php echo esc_textarea( $f->description ?? '' ); ?></textarea></td>
								<td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"<?php checked( $f->is_required && ! $_fb_no_req ); ?><?php echo $_fb_no_req ? ' disabled style="opacity:.3"' : ''; ?>></td>
																<td class="fb-actions"><button type="button" class="fb-gear" title="Advanced settings">&#9881;</button><button type="button" class="fb-ins" title="Insert a new field directly below this one">&#43;</button><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">&#10005;</button></td>
							</tr>
							<tr class="fb-adv" data-for="<?php echo esc_attr( $_fb_uid ); ?>" hidden>
								<td colspan="8"><?php echo $adv_panel( $_fb_set, (string) $f->field_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?></td>
							</tr>
							<?php endforeach; ?>
							<?php if ( empty( $fields ) ) : ?>
							<tr class="fb-row" data-key="" data-uid="r0">
								<td><span class="fb-drag">&#x2807;</span></td>
								<td><span class="fb-struct-tag">Group</span><input type="text" class="fb-label" value="" placeholder="Field label"></td>
								<td><?php echo $type_select( 'text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?></td>
								<td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
								<td>
									<textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;red|Red Apple &#x1F34E;&#10;Option C"></textarea>
									<?php echo $mode_select( 'open', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>
								</td>
								<td><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"></textarea></td>
								<td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"></td>
																<td class="fb-actions"><button type="button" class="fb-gear" title="Advanced settings">&#9881;</button><button type="button" class="fb-ins" title="Insert a new field directly below this one">&#43;</button><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">&#10005;</button></td>
							</tr>
							<tr class="fb-adv" data-for="r0" hidden>
								<td colspan="8"><?php echo $adv_panel( array(), 'text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?></td>
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
			<tr class="fb-row" data-key="" data-uid="">
				<td><span class="fb-drag">&#x2807;</span></td>
				<td><span class="fb-struct-tag">Group</span><input type="text" class="fb-label" value="" placeholder="Field label"></td>
				<td><?php echo $type_select( 'text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?></td>
				<td><input type="text" class="fb-ph" value="" placeholder="Placeholder text"></td>
				<td>
					<textarea class="fb-opts fb-hidden" rows="3" placeholder="Option A&#10;red|Red Apple &#x1F34E;&#10;Option C"></textarea>
					<?php echo $mode_select( 'open', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>
				</td>
				<td><textarea class="fb-desc" rows="2" placeholder="Optional help text shown below the field"></textarea></td>
				<td style="text-align:center"><input type="checkbox" class="fb-req fb-chk"></td>
								<td class="fb-actions"><button type="button" class="fb-gear" title="Advanced settings">&#9881;</button><button type="button" class="fb-ins" title="Insert a new field directly below this one">&#43;</button><button type="button" class="ah-btn ah-btn-danger ah-btn-sm fb-del" title="Remove">&#10005;</button></td>
			</tr>
		</template>
		<template id="fb-adv-tpl">
			<tr class="fb-adv" data-for="" hidden>
				<td colspan="8"><?php echo $adv_panel( array(), 'text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?></td>
			</tr>
		</template>
		<datalist id="fb-icon-list">
			<?php foreach ( array_keys( AH_Form_Builder::icons() ) as $ik ) : ?>
				<option value="<?php echo esc_attr( $ik ); ?>"></option>
			<?php endforeach; ?>
		</datalist>

		<?php else : ?>
		<!-- ════════════════════ Submissions ════════════════════ -->
		<?php
		$sub_search  = sanitize_text_field( wp_unslash( $_GET['sub_s'] ?? '' ) );
		$f_keys      = array_column( $fields, 'label', 'field_key' );
		/** Uploaded files are served through a nonced admin endpoint, never a direct URL. */
		$file_link   = static function ( $sub_id, $key, $name ) {
			$url = wp_nonce_url( add_query_arg( array(
				'action' => 'ah_form_file',
				'sub'    => (int) $sub_id,
				'key'    => $key,
			), admin_url( 'admin-post.php' ) ), 'ah_form_file' );
			return '<a href="' . esc_url( $url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">&#8659; ' . esc_html( $name ) . '</a>';
		};
		$subs_url    = add_query_arg( array( 'page' => 'ah-form-builder', 'form_id' => $form_id, 'action' => 'edit', 'tab' => 'submissions' ), admin_url( 'admin.php' ) );
		$export_url  = wp_nonce_url( add_query_arg( array(
			'action'     => 'ah_export_subs',
			'form_id'    => $form_id,
			'sub_status' => $sub_status,
			'sub_s'      => $sub_search,
		), admin_url( 'admin-post.php' ) ), 'ah_export_subs' );
		?>

		<?php if ( $sub_view_id ) : ?>
		<?php
		// ── Single submission ────────────────────────────────────────────
		$sub = AH_Form_Builder::get_submission( $sub_view_id );
		if ( ! $sub || (int) $sub['form_id'] !== $form_id ) {
			echo '<div class="ah-card" style="padding:40px;text-align:center">Submission not found. <a href="' . esc_url( $subs_url ) . '">Back to submissions</a></div>';
		} else {
			$s_status = $sub['sub_status'] ?? 'new';
			// Opening an unread enquiry marks it read, the way an inbox would.
			if ( 'new' === $s_status ) {
				AH_Form_Builder::update_submission_meta( $sub_view_id, 'read', (string) ( $sub['admin_notes'] ?? '' ) );
				$s_status = 'read';
			}
			$prev_id   = AH_Form_Builder::neighbour_submission( $form_id, $sub_view_id, 'prev' );
			$next_id   = AH_Form_Builder::neighbour_submission( $form_id, $sub_view_id, 'next' );
			$structure = AH_Form_Builder::build_structure( $fields );
			$shown     = array();
			$one_csv   = wp_nonce_url( add_query_arg( array(
				'action'  => 'ah_export_subs',
				'form_id' => $form_id,
				'ids'     => $sub_view_id,
			), admin_url( 'admin-post.php' ) ), 'ah_export_subs' );
		?>
		<div class="fb-view-bar fb-noprint">
			<a href="<?php echo esc_url( $subs_url ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; All submissions</a>
			<div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto">
				<?php if ( $prev_id ) : ?><a href="<?php echo esc_url( add_query_arg( 'sub', $prev_id, $subs_url ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; Newer</a><?php endif; ?>
				<?php if ( $next_id ) : ?><a href="<?php echo esc_url( add_query_arg( 'sub', $next_id, $subs_url ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Older &rarr;</a><?php endif; ?>
				<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" id="fb-print">&#128424; Print</button>
				<a href="<?php echo esc_url( $one_csv ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&#8659; CSV</a>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'del_sub', $sub_view_id, $subs_url ), 'ah_del_sub_fb' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Submission" data-confirm="This submission will be permanently removed.">Delete</a>
			</div>
		</div>

		<div class="fb-print-area">
			<div class="fb-view-head">
				<div>
					<h2>Submission #<?php echo esc_html( $sub_view_id ); ?></h2>
					<p><?php echo esc_html( $current->name ); ?></p>
				</div>
				<dl class="fb-view-meta">
					<dt>Status</dt><dd><span class="sub-status-badge ssb-<?php echo esc_attr( $s_status ); ?>"><?php echo esc_html( $s_status ); ?></span></dd>
					<dt>Received</dt><dd><?php echo esc_html( wp_date( 'j M Y, g:i a', strtotime( $sub['created_at'] ) ) ); ?></dd>
					<dt>IP address</dt><dd><?php echo esc_html( $sub['ip_address'] ?: '—' ); ?></dd>
				</dl>
			</div>

			<label class="fb-empty-toggle fb-noprint">
				<input type="checkbox" id="fb-show-empty" class="fb-chk"> Show unanswered fields
			</label>

			<table class="fb-view-tbl">
				<tbody>
				<?php foreach ( $structure as $st ) : ?>
					<?php if ( '' !== $st['title'] ) : ?>
					<tr class="fb-view-step"><th colspan="2"><?php echo esc_html( $st['title'] ); ?></th></tr>
					<?php endif; ?>
					<?php foreach ( $st['blocks'] as $b ) : ?>
						<?php if ( 'group' === $b['type'] ) : ?>
							<?php if ( '' !== $b['title'] ) : ?>
							<tr class="fb-view-sec"><th colspan="2"><?php echo esc_html( $b['title'] ); ?></th></tr>
							<?php endif; ?>
							<?php foreach ( $b['fields'] as $gf ) : ?>
								<?php
								if ( 'markup' === $gf->field_type ) { continue; }
								$val            = (string) ( $sub['data'][ $gf->field_key ] ?? '' );
								$shown[ $gf->field_key ] = true;
								?>
								<tr class="fb-view-row<?php echo '' === $val ? ' fb-view-blank' : ''; ?>">
									<th><?php echo esc_html( $gf->label ); ?></th>
									<td><?php
									if ( '' === $val ) {
										echo '<span class="fb-view-dash">—</span>';
									} elseif ( 'file' === $gf->field_type && ! empty( $sub['data'][ '_file_' . $gf->field_key ] ) ) {
										echo $file_link( $sub_view_id, $gf->field_key, $val ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure.
									} else {
										echo nl2br( esc_html( $val ) );
									}
									?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<?php
							$gf = $b['field'];
							if ( 'markup' === $gf->field_type ) { continue; }
							$val            = (string) ( $sub['data'][ $gf->field_key ] ?? '' );
							$shown[ $gf->field_key ] = true;
							?>
							<tr class="fb-view-row<?php echo '' === $val ? ' fb-view-blank' : ''; ?>">
								<th><?php echo esc_html( $gf->label ); ?></th>
								<td><?php
								if ( '' === $val ) {
									echo '<span class="fb-view-dash">—</span>';
								} elseif ( 'file' === $gf->field_type && ! empty( $sub['data'][ '_file_' . $gf->field_key ] ) ) {
									echo $file_link( $sub_view_id, $gf->field_key, $val ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure.
								} else {
									echo nl2br( esc_html( $val ) );
								}
								?></td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>

				<?php
				// Anything stored that the form no longer defines - renamed or removed
				// fields - would otherwise vanish from view entirely.
				$leftover = array();
				foreach ( (array) $sub['data'] as $k => $v ) {
					// _file_* holds the storage path behind a File Upload field, not an answer.
					if ( 0 === strpos( (string) $k, '_file_' ) || ! is_scalar( $v ) ) {
						continue;
					}
					if ( ! isset( $shown[ $k ] ) && '' !== (string) $v ) {
						$leftover[ $k ] = $v;
					}
				}
				?>
				<?php if ( $leftover ) : ?>
					<tr class="fb-view-sec"><th colspan="2">Other answers</th></tr>
					<?php foreach ( $leftover as $k => $v ) : ?>
					<tr class="fb-view-row">
						<th><?php echo esc_html( $f_keys[ $k ] ?? ltrim( $k, '_' ) ); ?></th>
						<td><?php echo nl2br( esc_html( (string) $v ) ); ?></td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="ah-card fb-noprint" style="margin-top:18px">
			<div class="ah-card-header"><h2>Internal</h2></div>
			<div class="sub-meta-box" style="margin-top:0">
				<div>
					<label style="font-size:11.5px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px">Status</label>
					<select class="sub-status-select" data-id="<?php echo esc_attr( $sub_view_id ); ?>">
						<?php foreach ( array( 'new', 'read', 'replied', 'closed' ) as $sv ) : ?>
							<option value="<?php echo esc_attr( $sv ); ?>" <?php selected( $s_status, $sv ); ?>><?php echo esc_html( ucfirst( $sv ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label style="font-size:11.5px;font-weight:600;color:var(--ah-muted);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px">Admin Notes</label>
					<textarea class="sub-notes-ta" data-id="<?php echo esc_attr( $sub_view_id ); ?>" placeholder="Internal notes about this submission..."><?php echo esc_textarea( $sub['admin_notes'] ?? '' ); ?></textarea>
				</div>
				<div style="padding-top:22px">
					<button class="sub-save-btn" data-id="<?php echo esc_attr( $sub_view_id ); ?>">Save</button>
					<div class="sub-save-msg" data-id="<?php echo esc_attr( $sub_view_id ); ?>" style="font-size:12px;margin-top:6px;display:none"></div>
				</div>
			</div>
		</div>
		<?php } // end found ?>

		<?php else : ?>
		<?php
		// ── Submissions list ─────────────────────────────────────────────
		$per_page   = 25;
		$sub_paged  = max( 1, (int) ( $_GET['spaged'] ?? 1 ) );
		$sub_total  = AH_Form_Builder::count_filtered( $form_id, $sub_status, $sub_search );
		$sub_pages  = max( 1, (int) ceil( $sub_total / $per_page ) );
		$sub_paged  = min( $sub_paged, $sub_pages );
		$subs       = AH_Form_Builder::get_submissions_filtered( $form_id, $sub_status, $per_page, ( $sub_paged - 1 ) * $per_page, $sub_search );
		// One column per field is unusable on a long form, so show a few key ones
		// and put everything else on the single-submission page.
		$cols       = AH_Form_Builder::summary_fields( $fields, 4 );
		$span       = 5 + count( $cols );

		$sub_status_options = array( '' => 'All (' . ( $status_counts['all'] ?? 0 ) . ')' );
		foreach ( array( 'new', 'read', 'replied', 'closed' ) as $sv ) {
			$sub_status_options[ $sv ] = ucfirst( $sv ) . ' (' . ( $status_counts[ $sv ] ?? 0 ) . ')';
		}
		AdminComponents::filterBar( array(
			'page_slug'          => 'ah-form-builder',
			'search_placeholder' => 'Search answers and notes...',
			'search_name'        => 'sub_s',
			'search_value'       => $sub_search,
			'hidden_inputs'      => array( 'form_id' => $form_id, 'action' => 'edit', 'tab' => 'submissions' ),
			'filters'            => array(
				array( 'name' => 'sub_status', 'options' => $sub_status_options, 'selected' => $sub_status ),
			),
			'extra_fields'       => '<a href="' . esc_url( $export_url ) . '" class="ah-btn ah-btn-secondary">&#8659; Download CSV</a>',
		) );
		?>

		<?php if ( $subs ) : ?>
		<form method="post" id="fb-subs-form">
			<?php wp_nonce_field( 'ah_bulk_subs', 'ah_bulk_subs_nonce' ); ?>
			<div class="fb-bulk-bar">
				<select name="bulk_action" id="fb-bulk-action">
					<option value="">Bulk actions</option>
					<option value="read">Mark as read</option>
					<option value="replied">Mark as replied</option>
					<option value="closed">Mark as closed</option>
					<option value="delete">Delete permanently</option>
				</select>
				<button type="submit" class="ah-btn ah-btn-secondary ah-btn-sm" id="fb-bulk-apply">Apply</button>
				<span class="fb-bulk-count" id="fb-bulk-count"></span>
				<span style="margin-left:auto;font-size:12.5px;color:var(--ah-muted)">
					<?php echo esc_html( number_format_i18n( $sub_total ) ); ?> submission<?php echo 1 === $sub_total ? '' : 's'; ?>
					<?php if ( $sub_pages > 1 ) : ?> &middot; page <?php echo esc_html( $sub_paged ); ?> of <?php echo esc_html( $sub_pages ); ?><?php endif; ?>
				</span>
			</div>

			<div class="ah-table-wrap">
				<table class="ah-table fb-subs-tbl" id="fb-subs">
					<thead>
						<tr>
							<th style="width:30px;text-align:center"><input type="checkbox" id="fb-check-all" class="fb-chk" title="Select all"></th>
							<th style="width:30px"></th>
							<th style="width:60px">#</th>
							<th style="width:92px">Status</th>
							<?php foreach ( $cols as $fi ) : ?><th><?php echo esc_html( $fi->label ); ?></th><?php endforeach; ?>
							<th style="width:150px">Received</th>
							<th style="width:130px"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $subs as $s ) :
							$s_status = $s['sub_status'] ?? 'new';
							$s_id     = (int) $s['id'];
							$view_url = add_query_arg( 'sub', $s_id, $subs_url );
						?>
						<tr class="fb-sub-row<?php echo 'new' === $s_status ? ' is-unread' : ''; ?>" data-id="<?php echo esc_attr( $s_id ); ?>">
							<td style="text-align:center"><input type="checkbox" name="sub_ids[]" value="<?php echo esc_attr( $s_id ); ?>" class="fb-chk fb-sub-check"></td>
							<td style="text-align:center;cursor:pointer" class="fb-toggle" title="Quick preview">&#9654;</td>
							<td style="color:var(--ah-muted);font-size:12px">#<?php echo esc_html( $s_id ); ?></td>
							<td><span class="sub-status-badge ssb-<?php echo esc_attr( $s_status ); ?>"><?php echo esc_html( $s_status ); ?></span></td>
							<?php foreach ( $cols as $fi ) : ?>
								<?php $v = (string) ( $s['data'][ $fi->field_key ] ?? '' ); ?>
								<td><?php echo '' === $v ? '<span class="fb-view-dash">—</span>' : esc_html( mb_strimwidth( $v, 0, 48, '…' ) ); ?></td>
							<?php endforeach; ?>
							<td><small><?php echo esc_html( wp_date( 'j M Y, g:i a', strtotime( $s['created_at'] ) ) ); ?></small></td>
							<td style="white-space:nowrap">
								<a href="<?php echo esc_url( $view_url ); ?>" class="ah-btn ah-btn-primary ah-btn-sm">View</a>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'del_sub', $s_id, $subs_url ), 'ah_del_sub_fb' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Submission" data-confirm="This submission will be permanently removed.">Delete</a>
							</td>
						</tr>
						<tr class="fb-sub-detail fb-hidden" id="fb-det-<?php echo esc_attr( $s_id ); ?>">
							<td colspan="<?php echo (int) $span; ?>" style="padding:0">
								<div class="fb-quick">
									<div class="sub-data-grid">
										<?php
										$peek = 0;
										foreach ( (array) $s['data'] as $k => $v ) :
											if ( ! is_scalar( $v ) || 0 === strpos( (string) $k, '_file_' ) ) { continue; }
											if ( '' === (string) $v || 'agreed' === $v || $peek >= 12 ) { continue; }
											$peek++;
										?>
										<div class="sub-data-item">
											<div class="sub-data-lbl"><?php echo esc_html( $f_keys[ $k ] ?? ltrim( $k, '_' ) ); ?></div>
											<div class="sub-data-val"><?php echo esc_html( mb_strimwidth( (string) $v, 0, 120, '…' ) ); ?></div>
										</div>
										<?php endforeach; ?>
									</div>
									<p style="margin:12px 0 0"><a href="<?php echo esc_url( $view_url ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Open full submission &rarr;</a></p>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</form>

		<?php if ( $sub_pages > 1 ) : ?>
		<div class="fb-pager">
			<?php for ( $pi = 1; $pi <= $sub_pages; $pi++ ) : ?>
				<?php if ( $pi === $sub_paged ) : ?>
					<span class="fb-page on"><?php echo esc_html( $pi ); ?></span>
				<?php else : ?>
					<a class="fb-page" href="<?php echo esc_url( add_query_arg( array( 'spaged' => $pi, 'sub_status' => $sub_status, 'sub_s' => $sub_search ), $subs_url ) ); ?>"><?php echo esc_html( $pi ); ?></a>
				<?php endif; ?>
			<?php endfor; ?>
		</div>
		<?php endif; ?>

		<?php else : ?>
			<div class="ah-card" style="text-align:center;padding:48px;color:var(--ah-muted)">
				<p style="font-size:1.1rem;margin:0">No submissions<?php echo $sub_status ? ' with status <strong>' . esc_html( $sub_status ) . '</strong>' : ''; ?><?php echo $sub_search ? ' matching <strong>' . esc_html( $sub_search ) . '</strong>' : ''; ?>.</p>
				<p style="margin:8px 0 0;font-size:13px">Use the shortcode <strong>[ah_form id="<?php echo esc_html( $form_id ); ?>"]</strong> to embed the form on your site.</p>
			</div>
		<?php endif; ?>
		<?php endif; // single vs list ?>
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

		// ── Site-wide spam protection keys ────────────────────────────────
		$cap = AH_Form_Builder::captcha_settings();
		$cap_ready = ( 'none' !== $cap['provider'] && '' !== $cap['site_key'] && '' !== $cap['secret_key'] );
		?>
		<div class="ah-card" style="margin-bottom:20px">
			<div class="ah-card-header" style="gap:12px">
				<h2 style="margin:0">Spam Protection</h2>
				<span class="ah-badge <?php echo $cap_ready ? 'ah-badge-active' : 'ah-badge-inactive'; ?>" style="margin-left:auto">
					<?php echo $cap_ready ? 'Configured' : 'Not set up'; ?>
				</span>
				<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" id="cap-toggle">
					<?php echo $cap_ready ? 'Edit keys' : 'Set up'; ?>
				</button>
			</div>
			<p style="font-size:13px;color:var(--ah-muted);margin:0">
				Keys are shared by every form; switch it on per form in that form's settings.
				Get reCAPTCHA keys at <code>google.com/recaptcha/admin</code>, or Turnstile at <code>dash.cloudflare.com</code> &rarr; Turnstile.
			</p>
			<div id="cap-body" style="display:none;padding-top:16px">
				<form method="post">
					<?php wp_nonce_field( 'ah_captcha', 'ah_captcha_nonce' ); ?>
					<div style="display:grid;grid-template-columns:220px 1fr 1fr;gap:16px;align-items:end">
						<div class="ah-form-row" style="margin:0">
							<label>Provider</label>
							<select name="cap_provider" id="cap-provider">
								<?php foreach ( array(
									'none'         => 'Off',
									'recaptcha_v3' => 'reCAPTCHA v3 (invisible)',
									'recaptcha_v2' => 'reCAPTCHA v2 ("I am not a robot")',
									'turnstile'    => 'Cloudflare Turnstile',
								) as $pv => $pl ) : ?>
									<option value="<?php echo esc_attr( $pv ); ?>" <?php selected( $cap['provider'], $pv ); ?>><?php echo esc_html( $pl ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="ah-form-row" style="margin:0">
							<label>Site key <small style="font-weight:400">(public)</small></label>
							<input type="text" name="cap_site_key" value="<?php echo esc_attr( $cap['site_key'] ); ?>" placeholder="6Lc… / 0x4AAA…" autocomplete="off">
						</div>
						<div class="ah-form-row" style="margin:0">
							<label>Secret key <small style="font-weight:400">(private)</small></label>
							<input type="password" name="cap_secret_key" value="<?php echo esc_attr( $cap['secret_key'] ); ?>" placeholder="6Lc… / 0x4AAA…" autocomplete="off">
						</div>
					</div>
					<div id="cap-threshold-row" class="ah-form-row" style="margin:16px 0 0;max-width:320px;<?php echo 'recaptcha_v3' === $cap['provider'] ? '' : 'display:none'; ?>">
						<label>v3 score threshold <small style="font-weight:400">(0 = lenient, 1 = strict; 0.5 is typical)</small></label>
						<input type="number" name="cap_threshold" min="0" max="1" step="0.1" value="<?php echo esc_attr( $cap['threshold'] ); ?>">
					</div>
					<div style="margin-top:16px"><button type="submit" class="ah-btn ah-btn-primary">Save Spam Protection</button></div>
				</form>
			</div>
		</div>

		<?php AdminComponents::dataTable( array(
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
					return '<code style="font-size:11px;background:var(--ah-bg-light);color:var(--ah-primary);padding:2px 6px;border-radius:3px;border:1px solid var(--ah-border);">[ah_form id="' . esc_html( $r->id ) . '"]</code>';
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

	// ── Spam protection card ──
	$('#cap-toggle').on('click', function () { $('#cap-body').slideToggle(180); });
	$('#cap-provider').on('change', function () {
		$('#cap-threshold-row').toggle($(this).val() === 'recaptcha_v3');
	});

	// ── Advanced drawer: each .fb-row owns the .fb-adv row that follows it ──
	function advOf($row) { return $('#fb-body .fb-adv[data-for="' + $row.attr('data-uid') + '"]'); }
	function closeAllAdv() {
		$('#fb-body .fb-adv').prop('hidden', true);
		$('#fb-body .fb-gear').removeClass('on');
	}
	$('#fb-body').on('click', '.fb-gear', function () {
		var $row = $(this).closest('tr.fb-row');
		var $adv = advOf($row);
		var open = $adv.prop('hidden');
		closeAllAdv();
		if (open) { $adv.prop('hidden', false); $(this).addClass('on'); refreshCondSelects(); }
	});

	// ── Sortable ── drawers ride along with their row
	$('#fb-body').sortable({
		handle: '.fb-drag', placeholder: 'fb-row fb-ghost', axis: 'y', tolerance: 'pointer',
		items: '> tr.fb-row',
		start: closeAllAdv,
		stop: function () {
			$('#fb-body .fb-row').each(function () { advOf($(this)).insertAfter($(this)); });
		}
	});

	// ── Add row ──
	var uid = Date.now();
	// Builds a row + its drawer and returns the row. With $after given the pair
	// goes straight after that row (and its own drawer, so the two never split);
	// without it the pair is appended to the end as before.
	function makeRow(type, $after) {
		var $row = $(document.getElementById('fb-row-tpl').content.firstElementChild.cloneNode(true));
		var $adv = $(document.getElementById('fb-adv-tpl').content.firstElementChild.cloneNode(true));
		var id   = 'n' + (++uid);
		$row.attr({ 'data-key': 'new_' + id, 'data-uid': id });
		$adv.attr('data-for', id);
		if (type) $row.find('.fb-type').val(type);
		if ($after && $after.length) {
			var $anchor = advOf($after);
			if (!$anchor.length) { $anchor = $after; }
			$row.insertAfter($anchor);
			$adv.insertAfter($row);
		} else {
			$('#fb-body').append($row).append($adv);
		}
		applyTypeUI($row, $row.find('.fb-type').val());
		return $row;
	}
	function addRow(type, $after) {
		var $row = makeRow(type, $after);
		refreshCondSelects();
		$row.addClass('fb-row-new');
		setTimeout(function () { $row.removeClass('fb-row-new'); }, 1200);
		$row.find('.fb-label').focus();
		if ($row[0] && $row[0].scrollIntoView) {
			$row[0].scrollIntoView({ block: 'center', inline: 'nearest' });
		}
	}

	// ── Insert a field directly below this one ──
	$('#fb-body').on('click', '.fb-ins', function () {
		addRow('text', $(this).closest('tr.fb-row'));
	});

	// ── Condition target dropdowns, rebuilt from the current rows ──
	function slugKey(s) {
		return String(s).toLowerCase().trim()
			.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').replace(/-/g, '_');
	}
	function keyOf($row) {
		var k = String($row.attr('data-key') || '');
		if (k && k.indexOf('new_') !== 0) return k;
		return slugKey($row.find('.fb-label').val() || '');
	}
	function refreshCondSelects() {
		var opts = [];
		$('#fb-body .fb-row').each(function () {
			var $r = $(this), t = $r.find('.fb-type').val();
			if (t === 'step' || t === 'fieldset' || t === 'markup' || t === 'hidden') return;
			var k = keyOf($r);
			if (k) opts.push({ v: k, t: ($r.find('.fb-label').val() || k) + '  (' + k + ')' });
		});
		$('#fb-body .fb-cond-field').each(function () {
			var $s = $(this);
			var cur = $s.val() || $s.attr('data-saved') || '';
			$s.empty().append($('<option>').val('').text('— no condition —'));
			var found = false;
			opts.forEach(function (o) {
				$s.append($('<option>').val(o.v).text(o.t));
				if (o.v === cur) found = true;
			});
			// Keep a saved target that no longer matches any row, so editing an
			// unrelated field never silently drops the condition.
			if (cur && !found) $s.append($('<option>').val(cur).text(cur + '  (missing)'));
			$s.val(cur);
		});
	}
	refreshCondSelects();
	$('#fb-body').on('input', '.fb-label', function () { refreshCondSelects(); });
	$('#fb-add').on('click',       function () { addRow('text'); });
	$('#fb-add-group').on('click', function () { addRow('fieldset'); });
	$('#fb-add-step').on('click',  function () { addRow('step'); });

	// ── Delete row ──
	$('#fb-body').on('click', '.fb-del', function () {
		if ($('#fb-body .fb-row').length <= 1) { alert('The form needs at least one field.'); return; }
		var $row = $(this).closest('tr.fb-row');
		advOf($row).remove();
		$row.fadeOut(160, function () { $(this).remove(); refreshCondSelects(); });
	});

	// ── Toggle columns based on field type ──
	function applyTypeUI($r, type) {
		var $ph   = $r.find('.fb-ph');
		var $opts = $r.find('.fb-opts');
		// Hide the control, never its <td> - an empty cell would shift the whole row.
		var $desc = $r.find('.fb-desc');
		var $req  = $r.find('.fb-req');
		var $mode = $r.find('.fb-fsmode');

		// Structural rows (step / group) carry a title + description only.
		var isStep = type === 'step';
		var isFs   = type === 'fieldset';

		// Show only the advanced settings this type actually supports.
		var $adv = advOf($r);
		if ($adv.length) {
			var affix = ['text','email','tel','url','number','date'].indexOf(type) > -1;
			$adv.find('.fb-adv-width').toggleClass('fb-hidden', isStep); // groups may be half-width too
			$adv.find('.fb-adv-icon').removeClass('fb-hidden'); // any field may carry an icon
			$adv.find('.fb-adv-layout').toggleClass('fb-hidden', type !== 'radio' && type !== 'checkbox');
			$adv.find('.fb-adv-next').toggleClass('fb-hidden', !isStep);
			$adv.find('.fb-adv-default').toggleClass('fb-hidden', isStep || isFs || type === 'markup');
			$adv.find('.fb-adv-affix').toggleClass('fb-hidden', !affix);
			$adv.find('.fb-adv-intl').toggleClass('fb-hidden', type !== 'tel');
			$adv.find('.fb-adv-file').toggleClass('fb-hidden', type !== 'file');
			$adv.find('.fb-adv-cond label').first()
				.text('Show this ' + ((isStep || isFs) ? 'group' : 'field') + ' only when…');
			// A step is a page boundary, not a block that can be hidden.
			$adv.find('.fb-adv-cond').toggleClass('fb-hidden', isStep);
		}

		$r.toggleClass('fb-row-step', isStep).toggleClass('fb-row-fieldset', isFs);
		$mode.toggleClass('fb-hidden', !isFs);
		if (isStep || isFs) {
			$r.find('.fb-struct-tag').text(isStep ? 'Step' : 'Group');
			$r.find('.fb-label').attr('placeholder', isStep ? 'Step title (e.g. Your details)' : 'Group title (e.g. Contact info)');
			$r.find('.fb-desc').attr('placeholder', isStep ? 'Optional intro text for this step' : 'Optional text shown inside the group');
			$ph.addClass('fb-hidden');
			$opts.addClass('fb-hidden');
			$desc.removeClass('fb-hidden');
			$req.prop('checked', false).prop('disabled', true).css('opacity', '0.3');
			return;
		}
		$r.find('.fb-label').attr('placeholder', 'Field label');
		$r.find('.fb-desc').attr('placeholder', 'Optional help text shown below the field');

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

	// ── Read every builder row back into plain field objects ──
	function collectFields() {
		var fields = [];
		$('#fb-body .fb-row').each(function (i) {
			var $r     = $(this);
			var type   = $r.find('.fb-type').val();
			var struct = (type === 'step' || type === 'fieldset');
			var opts   = [];
			if (type === 'select' || type === 'radio' || type === 'checkbox') {
				var raw = $r.find('.fb-opts').val().trim();
				if (raw) opts = raw.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
			}
			var $adv = advOf($r);
			var advVal = function (sel) {
				var $el = $adv.find(sel);
				return $el.length ? String($el.val() || '').trim() : '';
			};
			var settings = {};
			var cls = advVal('.fb-class');
			if (cls) settings.class = cls;
			if (type === 'fieldset') settings.mode = $r.find('.fb-fsmode').val() || 'open';

			// Fields and groups can take a width (two half groups sit side by
			// side); a step is a page of its own and always spans the row.
			if (type !== 'step') {
				var w = advVal('.fb-width');
				if (w && w !== 'full') settings.width = w;
			}
			var ic = advVal('.fb-icon');
			if (ic) settings.icon = ic;
			var dflt = advVal('.fb-default');
			if (dflt && type !== 'step' && type !== 'fieldset' && type !== 'markup') settings.default = dflt;
			if (type === 'step') {
				var nl = advVal('.fb-nextlbl');
				if (nl) settings.next_label = nl;
			}
			if (type === 'radio' || type === 'checkbox') {
				// 'list' is the default, so only a non-default presentation is stored.
				var lay = advVal('.fb-layout');
				if (['tiles', 'pills', 'cards', 'checks'].indexOf(lay) > -1) settings.layout = lay;
			}
			if (['text','email','tel','url','number','date'].indexOf(type) > -1) {
				var pre = advVal('.fb-prefix'), suf = advVal('.fb-suffix');
				if (pre) settings.prefix = pre;
				if (suf) settings.suffix = suf;
			}
			if (type === 'file') {
				var ms = parseInt(advVal('.fb-maxsize'), 10);
				settings.max_size = (ms > 0 ? Math.min(ms, 20) : 5);
				var acc = advVal('.fb-accept');
				if (acc) settings.accept = acc;
			}
			if (type === 'tel' && $adv.find('.fb-intl').is(':checked')) {
				settings.intl = 1;
				settings.intl_cc = advVal('.fb-intlcc') || '+44';
			}
			if (type !== 'step') {
				var cField = advVal('.fb-cond-field');
				if (cField) {
					settings.cond = {
						field: cField,
						op:    advVal('.fb-cond-op') || 'is',
						value: advVal('.fb-cond-value')
					};
				}
			}

			fields.push({
				field_key:   $r.data('key') || ('field_' + i),
				label:       $r.find('.fb-label').val().trim(),
				field_type:  type,
				placeholder: $r.find('.fb-ph').val().trim(),
				is_required: !struct && $r.find('.fb-req').is(':checked'),
				options:     opts,
				description: $r.find('.fb-desc').val().trim(),
				settings:    settings,
			});
		});
		return fields;
	}

	$('#fb-form').on('submit', function () {
		$('#fb-fields-json').val(JSON.stringify(collectFields()));
	});

	// ══════════════════ JSON import / export ══════════════════
	var FB_TYPES = ['text','email','tel','textarea','select','radio','checkbox','number','date',
	                'daterange','color','url','hidden','markup','step','fieldset'];

	// Drop empty values so an exported file stays readable and easy to hand-edit.
	function tidyField(f) {
		var o = { label: f.label || '', field_type: f.field_type };
		if (f.placeholder)                 o.placeholder = f.placeholder;
		if (f.description)                 o.description = f.description;
		if (f.is_required)                 o.is_required = true;
		if (f.options && f.options.length) o.options     = f.options;
		if (f.settings && Object.keys(f.settings).length) o.settings = f.settings;
		return o;
	}
	function exportJson() {
		return JSON.stringify(collectFields().map(tidyField), null, 2);
	}

	// Push one field object into an existing builder row.
	function applyFieldToRow($row, f) {
		var type = FB_TYPES.indexOf(f.field_type) > -1 ? f.field_type : 'text';
		var $adv = advOf($row);
		var set  = (f.settings && typeof f.settings === 'object') ? f.settings : {};

		$row.find('.fb-type').val(type);
		$row.find('.fb-label').val(f.label || '');
		$row.find('.fb-ph').val(f.placeholder || '');
		$row.find('.fb-desc').val(f.description || '');
		$row.find('.fb-req').prop('checked', !!f.is_required);
		$row.find('.fb-opts').val((f.options || []).join('\n'));
		$row.find('.fb-fsmode').val(set.mode || 'open');

		$adv.find('.fb-class').val(set['class'] || '');
		$adv.find('.fb-width').val(set.width || 'full');
		$adv.find('.fb-icon').val(set.icon || '');
		$adv.find('.fb-layout').val(set.layout || 'list');
		$adv.find('.fb-prefix').val(set.prefix || '');
		$adv.find('.fb-suffix').val(set.suffix || '');
		$adv.find('.fb-maxsize').val(set.max_size || 5);
		$adv.find('.fb-accept').val(set.accept || '');
		$adv.find('.fb-intl').prop('checked', !!set.intl);
		$adv.find('.fb-intlcc').val(set.intl_cc || '+44');
		// The condition dropdown is rebuilt later, so stash the target for it.
		$adv.find('.fb-cond-field').attr('data-saved', (set.cond && set.cond.field) || '').val('');
		$adv.find('.fb-cond-op').val((set.cond && set.cond.op) || 'is');
		$adv.find('.fb-cond-value').val((set.cond && set.cond.value) || '');

		applyTypeUI($row, type);
		return type;
	}

	function fbReport(html, cls) {
		$('#fb-json-msg').attr('class', 'fb-json-msg ' + (cls || 'ok')).html(html).show();
	}

	function applyJson(raw, mode) {
		var parsed;
		try {
			parsed = JSON.parse(raw);
		} catch (err) {
			fbReport('<strong>That is not valid JSON.</strong><br>' + String(err.message), 'err');
			return;
		}
		// Accept a bare array or {"fields": [...]} - both are natural to hand-write.
		var list = Array.isArray(parsed) ? parsed
		         : (parsed && Array.isArray(parsed.fields) ? parsed.fields : null);
		if (!list) {
			fbReport('<strong>Expected an array of fields</strong>, or an object with a <code>fields</code> array.', 'err');
			return;
		}
		if (!list.length) { fbReport('That file contains no fields.', 'err'); return; }

		var added = 0, updated = 0, warn = [];

		if (mode === 'replace') {
			$('#fb-body').empty();
		}

		list.forEach(function (f, i) {
			if (!f || typeof f !== 'object') { warn.push('Entry ' + (i + 1) + ' is not an object - skipped.'); return; }
			var type = f.field_type || 'text';
			var structural = (type === 'step' || type === 'fieldset');
			if (FB_TYPES.indexOf(type) === -1) {
				warn.push('Entry ' + (i + 1) + ' (' + (f.label || 'untitled') + ') has unknown type "' + type + '" - imported as Text.');
			}
			if (!f.label && !structural) {
				warn.push('Entry ' + (i + 1) + ' has no label - it will be dropped when you save.');
			}

			var $target = null;
			if (mode === 'update') {
				// Match on the stored key, otherwise on what the label would become.
				var want = f.field_key || slugKey(f.label || '');
				if (want) {
					$('#fb-body .fb-row').each(function () {
						if (!$target && keyOf($(this)) === want) { $target = $(this); }
					});
				}
			}
			if ($target) { updated++; } else { $target = makeRow(type); added++; }
			applyFieldToRow($target, f);
		});

		// Conditions can point at fields that only exist now, so wire them up last.
		refreshCondSelects();
		$('#fb-body .fb-cond-field').each(function () {
			var want = $(this).attr('data-saved');
			if (want) { $(this).val(want); }
		});

		var msg = '<strong>Applied to the builder.</strong> ' +
			added + ' field' + (added === 1 ? '' : 's') + ' added' +
			(updated ? ', ' + updated + ' updated' : '') + '.' +
			' <em>Nothing is saved yet - press “Save Form” to keep it.</em>';
		if (warn.length) {
			msg += '<ul class="fb-json-warn"><li>' + warn.slice(0, 8).join('</li><li>') + '</li></ul>';
			if (warn.length > 8) { msg += '<p>…and ' + (warn.length - 8) + ' more.</p>'; }
		}
		fbReport(msg, warn.length ? 'warn' : 'ok');
		refreshJsonCount();
		$('html, body').animate({ scrollTop: $('#fb-json-panel').offset().top - 40 }, 200);
	}

	// ── JSON panel wiring ──
	function refreshJsonCount() {
		var n = $('#fb-body .fb-row').length;
		$('#fb-json-count').text(n);
		$('#fb-json-count-s').text(n === 1 ? '' : 's');
	}
	$('#fb-json-toggle').on('click', function () {
		$('#fb-json-panel').slideToggle(160);
		$(this).toggleClass('on');
		refreshJsonCount();
	});
	refreshJsonCount();
	$('#fb-json-apply').on('click', function () {
		var raw = $('#fb-json-input').val().trim();
		if (!raw) { fbReport('Paste some JSON or choose a file first.', 'err'); return; }
		applyJson(raw, $('input[name="fb_json_mode"]:checked').val() || 'add');
	});
	$('#fb-json-file').on('change', function () {
		var file = this.files && this.files[0];
		if (!file) { return; }
		var reader = new FileReader();
		reader.onload = function (e) {
			$('#fb-json-input').val(String(e.target.result || ''));
			fbReport('Loaded <strong>' + file.name + '</strong>. Choose a mode, then press “Apply to builder”.', 'ok');
		};
		reader.onerror = function () { fbReport('Could not read that file.', 'err'); };
		reader.readAsText(file);
	});
	$('#fb-json-load-current').on('click', function () {
		$('#fb-json-input').val(exportJson());
		fbReport('Loaded the fields currently in the builder - edit them and apply, or copy them out.', 'ok');
	});
	$('#fb-json-copy').on('click', function () {
		ahCopy(exportJson(), $(this), 'Copied!');
	});
	$('#fb-json-download').on('click', function () {
		var name = ($('input[name="form_name"]').val() || 'form').toLowerCase()
			.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'form';
		var blob = new Blob([exportJson()], { type: 'application/json' });
		var url  = URL.createObjectURL(blob);
		var a    = document.createElement('a');
		a.href = url; a.download = name + '-fields.json';
		document.body.appendChild(a); a.click(); document.body.removeChild(a);
		setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
	});

	// ── Copy shortcode - use global ahCopy ──
	$(document).on('click', '#fb-sc-copy', function () {
		ahCopy($('#fb-sc-text').text(), $(this), 'Copied!');
	});

	// ── Expand/collapse submission rows ──
	$('#fb-subs').on('click', '.fb-toggle', function () {
		var id   = $(this).closest('tr').data('id');
		var $det = $('#fb-det-' + id);
		$det.toggleClass('fb-hidden');
		$(this).text($det.hasClass('fb-hidden') ? '\u25B6' : '\u25BC');
		$(this).closest('tr').toggleClass('sub-row-open');
	});

	// \u2500\u2500 Bulk selection \u2500\u2500
	function refreshBulk() {
		var n   = $('.fb-sub-check:checked').length;
		var all = $('.fb-sub-check').length;
		$('#fb-bulk-count').text(n ? n + ' selected' : '');
		$('#fb-check-all').prop('checked', n > 0 && n === all)
		                  .prop('indeterminate', n > 0 && n < all);
	}
	$('#fb-check-all').on('change', function () {
		// Read the DOM property, not :checked - that selector does not match an
		// indeterminate box even when its checkedness is true.
		var on = this.checked;
		$('.fb-sub-check').prop('checked', on);
		this.indeterminate = false;
		refreshBulk();
	});
	$('#fb-subs').on('change', '.fb-sub-check', refreshBulk);
	$('#fb-subs-form').on('submit', function (e) {
		var act = $('#fb-bulk-action').val();
		var n   = $('.fb-sub-check:checked').length;
		if (!act) { e.preventDefault(); alert('Choose a bulk action first.'); return; }
		if (!n)   { e.preventDefault(); alert('Select at least one submission.'); return; }
		if (act === 'delete' && !confirm('Permanently delete ' + n + ' submission(s)? This cannot be undone.')) {
			e.preventDefault();
		}
	});

	// \u2500\u2500 Single submission: print + show/hide unanswered fields \u2500\u2500
	$('#fb-print').on('click', function () { window.print(); });
	$('#fb-show-empty').on('change', function () {
		$('.fb-view-tbl').toggleClass('show-blank', $(this).is(':checked'));
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
			$msg.css('color', res.success ? 'var(--ah-success)' : 'var(--ah-danger)');
			if (res.success) {
				var $badge = $btn.closest('tr').prev('.fb-sub-row').find('.sub-status-badge');
				$badge.attr('class', 'sub-status-badge ssb-' + status).text(status);
			}
			setTimeout(function () { $msg.fadeOut(400); }, 2500);
		}).fail(function () {
			$btn.prop('disabled', false).text('Save');
			$msg.show().css('color','var(--ah-danger)').text('Request failed.');
			setTimeout(function () { $msg.fadeOut(400); }, 2500);
		});
	});
});
</script>
