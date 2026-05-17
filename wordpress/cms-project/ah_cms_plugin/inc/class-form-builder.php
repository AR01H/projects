<?php
defined( 'ABSPATH' ) || exit;

class AH_Form_Builder {

	// ── Tables ───────────────────────────────────────────────────────────────

	public static function install_tables(): void {
		global $wpdb;
		$p  = $wpdb->prefix;
		$cs = $wpdb->get_charset_collate();

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$p}ah_forms` (
			`id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`name`            VARCHAR(200) NOT NULL DEFAULT '',
			`notify_email`    VARCHAR(200) DEFAULT NULL,
			`success_message` VARCHAR(500) NOT NULL DEFAULT 'Thank you! We will get back to you shortly.',
			`status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
			`created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB {$cs}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$p}ah_form_fields` (
			`id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`form_id`     INT UNSIGNED NOT NULL,
			`label`       VARCHAR(200) NOT NULL DEFAULT '',
			`field_key`   VARCHAR(100) NOT NULL DEFAULT '',
			`field_type`  ENUM('text','email','tel','textarea','select','number','date','url') NOT NULL DEFAULT 'text',
			`placeholder` VARCHAR(300) DEFAULT '',
			`options`     JSON DEFAULT NULL,
			`is_required` TINYINT(1) NOT NULL DEFAULT 0,
			`sort_order`  INT NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`),
			KEY `idx_form` (`form_id`)
		) ENGINE=InnoDB {$cs}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$p}ah_form_submissions` (
			`id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`form_id`    INT UNSIGNED NOT NULL,
			`data`       JSON NOT NULL,
			`ip_address` VARCHAR(45) DEFAULT NULL,
			`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `idx_form`    (`form_id`),
			KEY `idx_created` (`created_at`)
		) ENGINE=InnoDB {$cs}" );
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
			$opts = array();
			if ( 'select' === $type && ! empty( $f['options'] ) ) {
				foreach ( (array) $f['options'] as $o ) {
					if ( is_array( $o ) ) {
						$v = sanitize_text_field( $o['value'] ?? '' );
						$l = sanitize_text_field( $o['label'] ?? $v );
						if ( $v !== '' ) $opts[] = array( 'value' => $v, 'label' => $l );
					} elseif ( is_string( $o ) && trim( $o ) !== '' ) {
						$v = sanitize_text_field( $o );
						$opts[] = array( 'value' => $v, 'label' => $v );
					}
				}
			}
			$wpdb->insert( $t, array(
				'form_id'     => $form_id,
				'label'       => $label,
				'field_key'   => self::to_key( $label ),
				'field_type'  => $type,
				'placeholder' => sanitize_text_field( $f['placeholder'] ?? '' ),
				'options'     => ! empty( $opts ) ? wp_json_encode( $opts ) : null,
				'is_required' => empty( $f['is_required'] ) ? 0 : 1,
				'sort_order'  => $i,
			) );
		}
	}

	// ── Submissions ──────────────────────────────────────────────────────────

	public static function submit( int $form_id, array $data ): int|false {
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
.ah-fw{font-family:inherit}
.ah-fw .ah-df{display:flex;flex-direction:column;gap:18px}
.ah-fw .ah-fr{display:flex;flex-direction:column;gap:6px}
.ah-fw .ah-fr label{font-size:14px;font-weight:500;color:#374151}
.ah-fw .ah-fr input,.ah-fw .ah-fr textarea,.ah-fw .ah-fr select{padding:11px 14px;border:1.5px solid #d1d5db;border-radius:8px;font-size:15px;color:#1f2937;background:#fff;font-family:inherit;width:100%;box-sizing:border-box;transition:border-color .18s,box-shadow .18s}
.ah-fw .ah-fr input:focus,.ah-fw .ah-fr textarea:focus,.ah-fw .ah-fr select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.ah-fw .ah-fr textarea{resize:vertical;min-height:110px}
.ah-fw .ah-sb{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:background .18s,transform .1s}
.ah-fw .ah-sb:hover{background:#1d4ed8}
.ah-fw .ah-sb:active{transform:scale(.98)}
.ah-fw .ah-sb:disabled{background:#93c5fd;cursor:not-allowed}
.ah-fw .ah-msg{display:none;align-items:flex-start;gap:10px;padding:14px 16px;border-radius:8px;margin-bottom:20px;font-size:14.5px;font-weight:500}
.ah-fw .ah-msg.show{display:flex}
.ah-fw .ah-suc{background:#f0fdf4;border:1px solid #86efac;color:#166534}
.ah-fw .ah-err{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b}
.ah-fw .ah-req{color:#ef4444;margin-left:2px}
@keyframes ah-spin{to{transform:rotate(360deg)}}
.ah-fw .ah-sp{animation:ah-spin .8s linear infinite}
</style>

<div class="ah-fw" id="<?php echo esc_attr( $uid ); ?>">
  <div class="ah-msg ah-suc"><svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span></span></div>
  <div class="ah-msg ah-err"><svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span></span></div>

  <form class="ah-df" novalidate>
    <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
    <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>">
    <div style="display:none;visibility:hidden" aria-hidden="true"><input type="text" name="ah_hp" tabindex="-1" autocomplete="off"></div>

    <?php foreach ( $fields as $f ) : $fid = esc_attr( $uid . '_' . $f->field_key ); $fname = esc_attr( $f->field_key ); $fph = esc_attr( $f->placeholder ); $freq = $f->is_required; ?>
    <div class="ah-fr">
      <label for="<?php echo $fid; ?>"><?php echo esc_html( $f->label ); ?><?php if ( $freq ) : ?><span class="ah-req">*</span><?php endif; ?></label>
      <?php if ( 'textarea' === $f->field_type ) : ?>
        <textarea id="<?php echo $fid; ?>" name="<?php echo $fname; ?>" placeholder="<?php echo $fph; ?>"<?php echo $freq ? ' required' : ''; ?>></textarea>
      <?php elseif ( 'select' === $f->field_type && ! empty( $f->options ) ) : ?>
        <select id="<?php echo $fid; ?>" name="<?php echo $fname; ?>"<?php echo $freq ? ' required' : ''; ?>>
          <option value=""><?php echo esc_html( $f->placeholder ?: '— Select an option —' ); ?></option>
          <?php foreach ( $f->options as $opt ) :
            $ov = is_array( $opt ) ? ( $opt['value'] ?? '' ) : $opt;
            $ol = is_array( $opt ) ? ( $opt['label'] ?? $ov ) : $opt;
          ?><option value="<?php echo esc_attr( $ov ); ?>"><?php echo esc_html( $ol ); ?></option><?php endforeach; ?>
        </select>
      <?php else : ?>
        <input type="<?php echo esc_attr( $f->field_type ); ?>" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>" placeholder="<?php echo $fph; ?>"<?php echo $freq ? ' required' : ''; ?>>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div>
      <button type="submit" class="ah-sb">
        <svg class="ah-sp" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:none"><circle cx="12" cy="12" r="10" stroke-dasharray="31 62"/></svg>
        <span class="ah-bt">Send Message</span>
      </button>
    </div>
  </form>
</div>

<script>
(function(){
  var w = document.getElementById('<?php echo esc_js( $uid ); ?>');
  if (!w) return;
  var f   = w.querySelector('.ah-df');
  var btn = w.querySelector('.ah-sb');
  var btt = w.querySelector('.ah-bt');
  var sp  = w.querySelector('.ah-sp');
  var sc  = w.querySelector('.ah-suc');
  var ec  = w.querySelector('.ah-err');
  function msg(el, txt) {
    sc.classList.remove('show'); ec.classList.remove('show');
    el.querySelector('span').textContent = txt;
    el.classList.add('show');
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
      if (r.success) { msg(sc, r.data.message); f.reset(); }
      else { msg(ec, r.data && r.data.message ? r.data.message : 'Something went wrong.'); }
    })
    .catch(function(){ msg(ec, 'Network error. Please try again.'); })
    .finally(function(){ btn.disabled = false; btt.textContent = 'Send Message'; sp.style.display = 'none'; });
  });
})();
</script>
		<?php
		return ob_get_clean();
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	public static function to_key( string $label ): string {
		return str_replace( '-', '_', sanitize_title( $label ) );
	}

	public static function allowed_type( string $type ): string {
		$allowed = array( 'text', 'email', 'tel', 'textarea', 'select', 'number', 'date', 'url' );
		return in_array( $type, $allowed, true ) ? $type : 'text';
	}

	public static function field_type_label( string $type ): string {
		$map = array(
			'text'     => 'Text',
			'email'    => 'Email',
			'tel'      => 'Phone / Tel',
			'textarea' => 'Textarea',
			'select'   => 'Dropdown',
			'number'   => 'Number',
			'date'     => 'Date',
			'url'      => 'URL',
		);
		return $map[ $type ] ?? $type;
	}
}
