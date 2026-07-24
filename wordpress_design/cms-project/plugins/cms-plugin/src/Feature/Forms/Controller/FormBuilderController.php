<?php

namespace Ah\Cms\Feature\Forms\Controller;

defined( 'ABSPATH' ) || exit;

class FormBuilderController {

	// ── Tables ───────────────────────────────────────────────────────────────

	const SUB_DB_VERSION = '3';
	const SUB_DB_OPTION  = 'ah_form_sub_db_v';

	public static function install_tables(): void {
		global $wpdb;
		$p  = $wpdb->prefix;
		$cs = $wpdb->get_charset_collate();

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$p}ah_forms` (
			`id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name`            VARCHAR(200) NOT NULL DEFAULT '',
			`notify_email`    VARCHAR(200) DEFAULT NULL,
			`success_message` VARCHAR(500) NOT NULL DEFAULT 'Thank you! We will get back to you shortly.',
			`submit_label`    VARCHAR(200) NOT NULL DEFAULT '',
			`status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
			`disable_rules`   TINYINT(1) NOT NULL DEFAULT 0,
			`created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB {$cs}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$p}ah_form_fields` (
			`id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`form_id`     INT UNSIGNED NOT NULL,
			`label`       VARCHAR(200) NOT NULL DEFAULT '',
			`field_key`   VARCHAR(100) NOT NULL DEFAULT '',
			`field_type`  ENUM('text','email','tel','textarea','select','number','date','url','hidden') NOT NULL DEFAULT 'text',
			`placeholder` VARCHAR(300) DEFAULT '',
			`options`     JSON DEFAULT NULL,
			`description` TEXT DEFAULT NULL,
			`is_required` TINYINT(1) NOT NULL DEFAULT 0,
			`sort_order`  INT NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`),
			KEY `idx_form` (`form_id`)
		) ENGINE=InnoDB {$cs}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$p}ah_form_submissions` (
			`id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`form_id`     INT UNSIGNED NOT NULL,
			`data`        JSON NOT NULL,
			`ip_address`  VARCHAR(45) DEFAULT NULL,
			`sub_status`  VARCHAR(20) NOT NULL DEFAULT 'new',
			`admin_notes` TEXT NOT NULL DEFAULT '',
			`created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `idx_form`    (`form_id`),
			KEY `idx_status`  (`sub_status`),
			KEY `idx_created` (`created_at`)
		) ENGINE=InnoDB {$cs}" );

		self::maybe_upgrade_submissions();
	}

	/** Add new columns to existing submissions table (safe to call on upgrade). */
	public static function maybe_upgrade_submissions(): void {
		global $wpdb;
		if ( get_option( self::SUB_DB_OPTION ) === self::SUB_DB_VERSION ) { return; }
		$t = $wpdb->prefix . 'ah_form_submissions';
		$cols = $wpdb->get_results( "SHOW COLUMNS FROM `{$t}`", ARRAY_A );
		if ( ! $cols ) { update_option( self::SUB_DB_OPTION, self::SUB_DB_VERSION ); return; }
		$existing = array_column( $cols, 'Field' );
		if ( ! in_array( 'sub_status', $existing, true ) ) {
			$wpdb->query( "ALTER TABLE `{$t}` ADD COLUMN `sub_status` VARCHAR(20) NOT NULL DEFAULT 'new' AFTER `ip_address`" );
			$wpdb->query( "ALTER TABLE `{$t}` ADD INDEX `idx_status` (`sub_status`)" );
		}
		if ( ! in_array( 'admin_notes', $existing, true ) ) {
			$wpdb->query( "ALTER TABLE `{$t}` ADD COLUMN `admin_notes` TEXT NOT NULL DEFAULT '' AFTER `sub_status`" );
		}
		$ft = $wpdb->prefix . 'ah_forms';
		$fcols = $wpdb->get_results( "SHOW COLUMNS FROM `{$ft}`", ARRAY_A );
		if ( $fcols ) {
			$fexisting = array_column( $fcols, 'Field' );
			if ( ! in_array( 'disable_rules', $fexisting, true ) ) {
				$wpdb->query( "ALTER TABLE `{$ft}` ADD COLUMN `disable_rules` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`" );
			}
			if ( ! in_array( 'submit_label', $fexisting, true ) ) {
				$wpdb->query( "ALTER TABLE `{$ft}` ADD COLUMN `submit_label` VARCHAR(200) NOT NULL DEFAULT '' AFTER `success_message`" );
			}
		}

		// ── ah_form_fields: add description column + hidden ENUM value ──
		$ff = $wpdb->prefix . 'ah_form_fields';
		$ff_cols = $wpdb->get_results( "SHOW COLUMNS FROM `{$ff}`", ARRAY_A );
		if ( $ff_cols ) {
			$ff_existing = array_column( $ff_cols, 'Field' );
			if ( ! in_array( 'description', $ff_existing, true ) ) {
				$wpdb->query( "ALTER TABLE `{$ff}` ADD COLUMN `description` TEXT DEFAULT NULL AFTER `options`" );
			}
			$ff_type_map = array_column( $ff_cols, 'Type', 'Field' );
			if ( isset( $ff_type_map['field_type'] ) && false === strpos( $ff_type_map['field_type'], 'hidden' ) ) {
				$wpdb->query( "ALTER TABLE `{$ff}` MODIFY COLUMN `field_type` ENUM('text','email','tel','textarea','select','number','date','url','hidden') NOT NULL DEFAULT 'text'" );
			}
		}

		update_option( self::SUB_DB_OPTION, self::SUB_DB_VERSION );
	}

	// ── Form CRUD ────────────────────────────────────────────────────────────

	public static function get_all(): array {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}ah_forms` ORDER BY id ASC" ) ?: array();
	}

	public static function get( int $id ): ?object {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_forms';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$t}` WHERE id = %d", $id ) ) ?: null;
	}

	public static function upsert( int $id, array $data ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_forms';
		if ( $id > 0 ) {
			$wpdb->update( $t, $data, array( 'id' => $id ) );
			return $id;
		}
		$wpdb->insert( $t, $data );
		return (int) $wpdb->insert_id;
	}

	public static function delete_form( int $id ): void {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ah_form_submissions', array( 'form_id' => $id ), array( '%d' ) );
		$wpdb->delete( $wpdb->prefix . 'ah_form_fields',      array( 'form_id' => $id ), array( '%d' ) );
		$wpdb->delete( $wpdb->prefix . 'ah_forms',            array( 'id'      => $id ), array( '%d' ) );
	}

	// ── Fields CRUD ──────────────────────────────────────────────────────────

	public static function get_fields( int $form_id ): array {
		global $wpdb;
		$t    = $wpdb->prefix . 'ah_form_fields';
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$t}` WHERE form_id = %d ORDER BY sort_order ASC, id ASC", $form_id )
		) ?: array();
		foreach ( $rows as $row ) {
			$row->options = ( $row->options ) ? json_decode( $row->options, true ) : array();
		}
		return $rows;
	}

	public static function save_fields( int $form_id, array $fields ): void {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_fields';
		$wpdb->delete( $t, array( 'form_id' => $form_id ), array( '%d' ) );
		foreach ( $fields as $i => $f ) {
			$label = sanitize_text_field( $f['label'] ?? '' );
			if ( ! $label ) continue;
			$type = self::allowed_type( $f['field_type'] ?? 'text' );
			$opts = ( in_array( $type, array( 'select', 'radio', 'checkbox' ), true ) && ! empty( $f['options'] ) ) ? array_values( array_filter( array_map( 'sanitize_text_field', (array) $f['options'] ) ) ) : array();
			$wpdb->insert( $t, array(
				'form_id'     => $form_id,
				'label'       => $label,
				'field_key'   => self::to_key( $label ),
				'field_type'  => $type,
				'placeholder' => sanitize_text_field( $f['placeholder'] ?? '' ),
				'options'     => ! empty( $opts ) ? wp_json_encode( $opts ) : null,
				'description' => sanitize_textarea_field( $f['description'] ?? '' ),
				'is_required' => ( 'hidden' === $type ) ? 0 : ( empty( $f['is_required'] ) ? 0 : 1 ),
				'sort_order'  => $i,
			) );
		}
	}

	// ── Submissions ──────────────────────────────────────────────────────────

	public static function submit( int $form_id, array $data ): int {
		global $wpdb;
		$result = $wpdb->insert( $wpdb->prefix . 'ah_form_submissions', array(
			'form_id'    => $form_id,
			'data'       => wp_json_encode( $data ),
			'ip_address' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
		) );
		return $result ? (int) $wpdb->insert_id : false;
	}

	public static function get_submissions( int $form_id, int $limit = 50, int $offset = 0 ): array {
		global $wpdb;
		$t    = $wpdb->prefix . 'ah_form_submissions';
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$t}` WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", $form_id, $limit, $offset )
		) ?: array();
		foreach ( $rows as $row ) {
			$row->data = $row->data ? json_decode( $row->data, true ) : array();
		}
		return $rows;
	}

	public static function count_submissions( int $form_id ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_submissions';
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$t}` WHERE form_id = %d", $form_id ) );
	}

	public static function delete_submission( int $id ): void {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ah_form_submissions', array( 'id' => $id ), array( '%d' ) );
	}

	/** Save admin status and notes for a single submission. */
	public static function update_submission_meta( int $id, string $status, string $notes ): bool {
		global $wpdb;
		$allowed = array( 'new', 'read', 'replied', 'closed' );
		$status  = in_array( $status, $allowed, true ) ? $status : 'new';
		return (bool) $wpdb->update(
			$wpdb->prefix . 'ah_form_submissions',
			array(
				'sub_status'  => $status,
				'admin_notes' => sanitize_textarea_field( $notes ),
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/** Get submissions optionally filtered by sub_status. */
	public static function get_submissions_filtered( int $form_id, string $status = '', int $limit = 100, int $offset = 0 ): array {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_submissions';
		if ( '' !== $status ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM `{$t}` WHERE form_id = %d AND sub_status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d", $form_id, $status, $limit, $offset ),
				ARRAY_A
			) ?: array();
		} else {
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM `{$t}` WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", $form_id, $limit, $offset ),
				ARRAY_A
			) ?: array();
		}
		foreach ( $rows as &$row ) {
			$row['data'] = $row['data'] ? json_decode( $row['data'], true ) : array();
		}
		return $rows;
	}

	/** Count submissions by status for a form. */
	public static function count_by_status( int $form_id ): array {
		global $wpdb;
		$t    = $wpdb->prefix . 'ah_form_submissions';
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT sub_status, COUNT(*) AS cnt FROM `{$t}` WHERE form_id = %d GROUP BY sub_status", $form_id ),
			ARRAY_A
		) ?: array();
		$counts = array( 'new' => 0, 'read' => 0, 'replied' => 0, 'closed' => 0 );
		foreach ( $rows as $r ) {
			if ( isset( $counts[ $r['sub_status'] ] ) ) { $counts[ $r['sub_status'] ] = (int) $r['cnt']; }
		}
		$counts['all'] = array_sum( $counts );
		return $counts;
	}

	// ── Shortcode renderer ───────────────────────────────────────────────────

	public static function render( array $atts ): string {
		self::install_tables();
		$form_id = (int) ( $atts['id'] ?? 0 );
		$form    = $form_id ? self::get( $form_id ) : null;
		$fields  = $form_id ? self::get_fields( $form_id ) : array();

		if ( ! $form || empty( $fields ) ) {
			return '<p style="color:#6b7280;font-style:italic;">Form not configured yet.</p>';
		}

		$uid   = 'ahf_' . $form_id . '_' . wp_rand( 100, 999 );
		$nonce = wp_create_nonce( 'ah_frontend_nonce' );
		$ajax  = admin_url( 'admin-ajax.php' );

		ob_start();
		?>
<style>
@keyframes ah-spin{to{transform:rotate(360deg)}}
.ah-fw{max-width:640px}
.ah-fw .ah-sp{animation:ah-spin .8s linear infinite;display:none}
.ah-fw .ah-req{color:#e53935;margin-left:2px}
/* Form fields */
.ch-form-group{margin-bottom:20px}
.ch-form-label{display:block;font-size:14px;font-weight:600;color:#1f2937;margin-bottom:7px}
.ch-form-input,
.ch-form-textarea,
.ch-form-select{width:100%;padding:12px 16px;border:1.5px solid #d1d5db;border-radius:8px;font-size:15px;font-family:inherit;color:#111827;background:#fff;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;outline:none;appearance:none}
.ch-form-input:focus,
.ch-form-textarea:focus,
.ch-form-select:focus{border-color:#1a3c5e;box-shadow:0 0 0 3px rgba(26,60,94,.1)}
.ch-form-input::placeholder,
.ch-form-textarea::placeholder{color:#9ca3af}
.ch-form-textarea{min-height:130px;resize:vertical;line-height:1.6}
.ch-form-select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:40px;cursor:pointer}
.ch-form-desc{font-size:11.5px;color:#9ca3af;margin:4px 0 0;line-height:1.4}
.ch-form-submit{display:inline-flex;align-items:center;gap:8px;background:#1a3c5e;color:#fff;border:none;border-radius:8px;padding:13px 32px;font-size:15px;font-weight:600;cursor:pointer;font-family:inherit;letter-spacing:.01em;transition:background .15s,transform .1s}
.ch-form-submit:hover{background:#15304d}
.ch-form-submit:active{transform:scale(.98)}
.ch-form-submit:disabled{opacity:.6;cursor:not-allowed;transform:none}
/* Feedback messages */
.ch-form-feedback{display:none;border-radius:8px;padding:12px 16px;font-size:14px;margin-bottom:16px}
.ch-form-feedback.success{display:block;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.ch-form-feedback.error{display:block;background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
/* Agreement section */
.ch-agr-intro{font-size:14px;line-height:1.7;color:#4b5563;margin-bottom:14px;padding:14px 16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px}
.ch-agr-iframe-wrap{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:14px}
.ch-agr-iframe{width:100%;height:240px;border:none;display:block}
.ch-form-agreement .ch-agreement-label{display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:14px;line-height:1.6;font-weight:400;color:#374151}
.ch-form-agreement .ch-agreement-chk{margin-top:3px;flex-shrink:0;width:17px;height:17px;cursor:pointer;accent-color:#1a3c5e}
.ch-terms-link{color:#1a3c5e;text-decoration:underline;font-weight:600;margin-left:3px;margin-right:3px}
.ch-terms-link:hover{color:#15304d}
</style>

<div class="ah-fw" id="<?php echo esc_attr( $uid ); ?>">
  <div class="ch-form-feedback" role="alert"><span></span></div>
  <div class="ch-form-feedback" role="alert"><span></span></div>

  <form novalidate>
    <input type="hidden" name="nonce"   value="<?php echo esc_attr( $nonce ); ?>">
    <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>">
    <div style="display:none;visibility:hidden" aria-hidden="true"><input type="text" name="ah_hp" tabindex="-1" autocomplete="off"></div>

    <?php foreach ( $fields as $f ) :
      if ( 'hidden' === $f->field_type ) : ?>
        <input type="hidden" name="<?php echo esc_attr( $f->field_key ); ?>" value="<?php echo esc_attr( $f->placeholder ); ?>">
        <?php continue; ?>
      <?php endif; ?>
      <?php
      $fid   = esc_attr( $uid . '_' . $f->field_key );
      $fname = esc_attr( $f->field_key );
      $fph   = esc_attr( $f->placeholder );
      $freq  = $f->is_required;
      $fdesc = isset( $f->description ) ? trim( (string) $f->description ) : '';
      ?>
    <div class="ch-form-group">
      <?php if ( 'markup' === $f->field_type ) : ?>
        <?php if ( $fdesc ) : ?><div class="ch-form-markup" style="font-size:14px;color:#4b5563;line-height:1.6;margin-bottom:5px;"><?php echo wp_kses_post( $fdesc ); ?></div><?php endif; ?>
      <?php else : ?>
        <label class="ch-form-label" for="<?php echo $fid; ?>"><?php echo esc_html( $f->label ); ?><?php if ( $freq ) : ?><span class="ah-req">*</span><?php endif; ?></label>
        <?php if ( 'textarea' === $f->field_type ) : ?>
          <textarea class="ch-form-textarea" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>" placeholder="<?php echo $fph; ?>"<?php echo $freq ? ' required' : ''; ?>></textarea>
        <?php elseif ( 'select' === $f->field_type && ! empty( $f->options ) ) : ?>
          <select class="ch-form-select" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>"<?php echo $freq ? ' required' : ''; ?>>
            <option value=""><?php echo esc_html( $f->placeholder ?: '- Select an option -' ); ?></option>
            <?php foreach ( $f->options as $opt ) : ?><option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option><?php endforeach; ?>
          </select>
        <?php elseif ( 'radio' === $f->field_type && ! empty( $f->options ) ) : ?>
          <div class="ch-form-radio-group" style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ( $f->options as $opt ) : ?>
              <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;">
                <input type="radio" name="<?php echo $fname; ?>" value="<?php echo esc_attr( $opt ); ?>" <?php echo $freq ? ' required' : ''; ?> style="accent-color:#1a3c5e;">
                <?php echo esc_html( $opt ); ?>
              </label>
            <?php endforeach; ?>
          </div>
        <?php elseif ( 'checkbox' === $f->field_type && ! empty( $f->options ) ) : ?>
          <div class="ch-form-checkbox-group" style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ( $f->options as $idx => $opt ) : ?>
              <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;">
                <input type="checkbox" name="<?php echo $fname; ?>[]" value="<?php echo esc_attr( $opt ); ?>" style="accent-color:#1a3c5e;" <?php echo ( $freq && $idx === 0 ) ? ' data-required-group="true"' : ''; ?>>
                <?php echo esc_html( $opt ); ?>
              </label>
            <?php endforeach; ?>
          </div>
        <?php elseif ( 'daterange' === $f->field_type ) : ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <div style="flex:1;min-width:140px;">
              <span style="display:block;font-size:12px;color:#6b7280;margin-bottom:4px;">Start Date</span>
              <input class="ch-form-input" type="date" id="<?php echo $fid; ?>_start" name="<?php echo $fname; ?>_start" <?php echo $freq ? ' required' : ''; ?>>
            </div>
            <div style="flex:1;min-width:140px;">
              <span style="display:block;font-size:12px;color:#6b7280;margin-bottom:4px;">End Date</span>
              <input class="ch-form-input" type="date" id="<?php echo $fid; ?>_end" name="<?php echo $fname; ?>_end" <?php echo $freq ? ' required' : ''; ?>>
            </div>
          </div>
        <?php elseif ( 'color' === $f->field_type ) : ?>
          <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>" <?php echo $freq ? ' required' : ''; ?> style="width:44px;height:44px;padding:2px;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">
            <span style="font-size:13px;color:#6b7280;"><?php echo esc_html( $f->placeholder ?: 'Select a color' ); ?></span>
          </div>
        <?php else : ?>
          <input class="ch-form-input" type="<?php echo esc_attr( $f->field_type ); ?>" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>" placeholder="<?php echo $fph; ?>"<?php echo $freq ? ' required' : ''; ?>>
        <?php endif; ?>
        <?php if ( $fdesc ) : ?><p class="ch-form-desc"><?php echo esc_html( $fdesc ); ?></p><?php endif; ?>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php
    // ── Form-level Agreement / Terms section ──────────────────────────────
    $agr = self::get_agreement( $form_id );
    if ( ! empty( $agr['enabled'] ) ) :
      $agr_uid  = esc_attr( $uid . '_agr' );
      $agr_url  = $agr['url'];
      $agr_type = $agr['type'];
    ?>
    <div style="margin-bottom:20px">
      <?php if ( $agr_type === 'iframe' && $agr_url ) : ?>
        <div class="ch-agr-iframe-wrap">
          <iframe class="ch-agr-iframe" src="<?php echo esc_url( $agr_url ); ?>" loading="lazy" title="<?php echo esc_attr( $agr['link_text'] ); ?>"></iframe>
        </div>
      <?php endif; ?>
      <div class="ch-form-group ch-form-agreement" style="margin-bottom:0">
        <label class="ch-agreement-label" for="<?php echo $agr_uid; ?>">
          <input type="checkbox" class="ch-agreement-chk" id="<?php echo $agr_uid; ?>" name="ah_agreement" value="1" required>
          <span>
            <?php if ( $agr['before'] ) echo esc_html( $agr['before'] ); ?>
            <?php if ( $agr_url && $agr_type === 'link' ) : ?>
              <a class="ch-terms-link" href="<?php echo esc_url( $agr_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $agr['link_text'] ); ?></a>
            <?php elseif ( $agr_type === 'popup' ) : ?>
              <button type="button" class="ch-terms-popup-btn" data-popup="ch-tpop-<?php echo esc_attr( $uid ); ?>"><?php echo esc_html( $agr['link_text'] ); ?></button>
            <?php elseif ( $agr['link_text'] ) : ?>
              <strong class="ch-terms-link" style="text-decoration:none"><?php echo esc_html( $agr['link_text'] ); ?></strong>
            <?php endif; ?>
            <?php if ( $agr['after'] ) echo esc_html( $agr['after'] ); ?>
          </span>
        </label>
      </div>
      <?php if ( $agr_type === 'popup' && ! empty( $agr['popup_html'] ) ) : ?>
        <div id="ch-tpop-<?php echo esc_attr( $uid ); ?>" role="dialog" aria-modal="true" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;align-items:center;justify-content:center;padding:24px;box-sizing:border-box;backdrop-filter:blur(2px)">
          <div style="background:#fff;border-radius:16px;max-width:600px;width:100%;max-height:82vh;display:flex;flex-direction:column;position:relative;box-shadow:0 24px 64px rgba(0,0,0,.28);outline:1px solid rgba(255,255,255,0.12);ring:1px solid rgba(0,0,0,0.06)">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f0f0f0;flex-shrink:0">
              <span style="font-size:1rem;font-weight:700;color:#111827;letter-spacing:-0.01em">Terms &amp; Conditions</span>
              <button type="button" aria-label="Close" style="background:none;border:1px solid #e5e7eb;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-size:16px;line-height:1;cursor:pointer;color:#6b7280;padding:0;flex-shrink:0;transition:border-color .15s,color .15s">&times;</button>
            </div>
            <div class="ch-terms-popup-content" style="overflow-y:auto;padding:20px 24px 24px;flex:1;min-height:0"><?php echo wp_kses_post( $agr['popup_html'] ); ?></div>
          </div>
        </div>
        <style>
.ch-terms-popup-btn{background:none;border:none;padding:0;color:#1a3c5e;text-decoration:underline;font-weight:600;cursor:pointer;font-family:inherit;font-size:inherit;margin:0 3px}
.ch-terms-popup-content{color:#4b5563;font-size:0.875rem;line-height:1.75}
.ch-terms-popup-content h1,.ch-terms-popup-content h2,.ch-terms-popup-content h3{color:#111827;font-weight:700;margin:0 0 10px;line-height:1.3}
.ch-terms-popup-content h1{font-size:1.1rem}
.ch-terms-popup-content h2{font-size:1rem;margin-top:18px}
.ch-terms-popup-content h3{font-size:0.95rem;margin-top:14px}
.ch-terms-popup-content p{margin:0 0 10px}
.ch-terms-popup-content p:last-child{margin-bottom:0}
.ch-terms-popup-content ul,.ch-terms-popup-content ol{margin:0 0 12px;padding-left:18px}
.ch-terms-popup-content li{margin-bottom:5px}
.ch-terms-popup-content strong{color:#111827;font-weight:600}
.ch-terms-popup-content a{color:#1a3c5e;text-decoration:underline}
</style>
        <script>
        (function(){
          var ov=document.getElementById('ch-tpop-<?php echo esc_js( $uid ); ?>');
          if(!ov)return;
          document.querySelectorAll('[data-popup="ch-tpop-<?php echo esc_js( $uid ); ?>"]').forEach(function(b){
            b.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();ov.style.display='flex';});
          });
          var close=ov.querySelector('[aria-label="Close"]');
          if(close)close.addEventListener('click',function(){ov.style.display='none';});
          ov.addEventListener('click',function(e){if(e.target===ov)ov.style.display='none';});
          document.addEventListener('keydown',function(e){if(e.key==='Escape'&&ov.style.display==='flex')ov.style.display='none';});
        })();
        </script>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php $submit_label = ! empty( $form->submit_label ) ? $form->submit_label : 'Send Message'; ?>
    <div>
      <button type="submit" class="ch-form-submit ah-sb">
        <svg class="ah-sp" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:none;vertical-align:middle;margin-right:6px"><circle cx="12" cy="12" r="10" stroke-dasharray="31 62"/></svg>
        <span class="ah-bt"><?php echo esc_html( $submit_label ); ?></span>
      </button>
    </div>
  </form>
</div>

<script>
(function(){
  var w = document.getElementById('<?php echo esc_js( $uid ); ?>');
  if (!w) return;
  var f   = w.querySelector('form');
  var btn = w.querySelector('.ah-sb');
  var btt = w.querySelector('.ah-bt');
  var sp  = w.querySelector('.ah-sp');
  var fb  = w.querySelectorAll('.ch-form-feedback');
  var sc  = fb[0];
  var ec  = fb[1];
  function msg(el, type, txt) {
    sc.className = 'ch-form-feedback';
    ec.className = 'ch-form-feedback';
    el.querySelector('span').textContent = txt;
    el.className = 'ch-form-feedback ' + type;
    el.scrollIntoView({behavior:'smooth',block:'nearest'});
  }
  f.addEventListener('submit', function(e) {
    e.preventDefault();
    btn.disabled = true; btt.textContent = 'Sending…'; sp.style.display = 'inline-block';
    fetch('<?php echo esc_js( $ajax ); ?>', {
      method: 'POST',
      body: new URLSearchParams(Object.assign(
        Object.fromEntries(new FormData(f)),
        {action: 'ah_form_submit'}
      ))
    })
    .then(function(r){ return r.json(); })
    .then(function(r){
      if (r.success) {
        msg(sc, 'success', r.data.message);
        f.reset();
        f.querySelectorAll('input:not([type=hidden]),textarea,select').forEach(function(el){ el.disabled = true; el.style.opacity = '0.5'; el.style.cursor = 'not-allowed'; });
        btn.disabled = true; btt.textContent = 'Sent'; sp.style.display = 'none'; btn.style.opacity = '0.55'; btn.style.cursor = 'not-allowed';
      } else {
        msg(ec, 'error', r.data && r.data.message ? r.data.message : 'Something went wrong.');
        btn.disabled = false; btt.textContent = '<?php echo esc_js( $submit_label ); ?>'; sp.style.display = 'none';
      }
    })
    .catch(function(){
      msg(ec, 'error', 'Network error. Please try again.');
      btn.disabled = false; btt.textContent = 'Send Message'; sp.style.display = 'none';
    });
  });
})();
</script>
		<?php
		return ob_get_clean();
	}

	// ── Agreement config (form-level, stored in wp_option) ──────────────────

	public static function get_agreement( int $form_id ): array {
		$defaults = array(
			'enabled'    => 0,
			'before'     => 'I have read and agree to the',
			'link_text'  => 'Terms & Conditions',
			'type'       => 'link',
			'url'        => '',
			'after'      => '',
			'popup_html' => '',
		);
		$saved = get_option( 'ah_form_agr_' . $form_id, array() );
		return array_merge( $defaults, is_array( $saved ) ? $saved : array() );
	}

	public static function save_agreement( int $form_id, array $data ): void {
		$clean = array(
			'enabled'    => ! empty( $data['enabled'] ) ? 1 : 0,
			'before'     => sanitize_text_field( isset( $data['before'] )    ? $data['before']    : 'I have read and agree to the' ),
			'link_text'  => sanitize_text_field( isset( $data['link_text'] ) ? $data['link_text'] : 'Terms & Conditions' ),
			'type'       => in_array( isset( $data['type'] ) ? $data['type'] : '', array( 'link', 'iframe', 'popup' ), true ) ? $data['type'] : 'link',
			'url'        => esc_url_raw( isset( $data['url'] )        ? $data['url']        : '' ),
			'after'      => sanitize_text_field( isset( $data['after'] )     ? $data['after']     : '' ),
			'popup_html' => wp_kses_post( isset( $data['popup_html'] ) ? $data['popup_html'] : '' ),
		);
		update_option( 'ah_form_agr_' . $form_id, $clean );
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	public static function to_key( string $label ): string {
		return str_replace( '-', '_', sanitize_title( $label ) );
	}

	public static function allowed_type( string $t ): string {
		return in_array( $t, array( 'text', 'email', 'tel', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'daterange', 'color', 'url', 'hidden', 'markup' ), true ) ? $t : 'text';
	}

	public static function field_type_label( string $type ): string {
		$map = array(
			'text'      => 'Text',
			'email'     => 'Email',
			'tel'       => 'Phone / Tel',
			'textarea'  => 'Textarea',
			'select'    => 'Dropdown',
			'radio'     => 'Radio Buttons',
			'checkbox'  => 'Checkboxes',
			'number'    => 'Number',
			'date'      => 'Date',
			'daterange' => 'Date Range',
			'color'     => 'Color Picker',
			'url'       => 'URL',
			'hidden'    => 'Hidden Field',
			'markup'    => 'Markup / Instructions',
		);
		return $map[ $type ] ?? $type;
	}
}
