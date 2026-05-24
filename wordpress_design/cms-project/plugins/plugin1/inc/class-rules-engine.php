<?php
defined( 'ABSPATH' ) || exit;

/**
 * Triggers Maker - General-purpose automation platform.
 *
 * Call from anywhere:
 *   AH_Rules_Engine::evaluate( 'your_trigger_name', [ 'field_key' => 'value', ... ] );
 *
 * Built-in triggers: form_submit (auto-fired on AH Form Builder submissions).
 * Custom triggers:   any string you define, e.g. 'order_placed', 'user_signup'.
 *
 * Actions: send_email · whatsapp · http_request
 */
class AH_Rules_Engine {

	// ── Schema ───────────────────────────────────────────────────────────────

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_rules';
	}

	public static function logs_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_trigger_logs';
	}

	public static function install_tables(): void {
		global $wpdb;
		$cs = $wpdb->get_charset_collate();

		// Rules table
		$t = self::table();
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$t}` (
			`id`               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
			`name`             VARCHAR(200)      NOT NULL DEFAULT '',
			`trigger_name`     VARCHAR(100)      NOT NULL DEFAULT 'form_submit',
			`conditions_match` ENUM('all','any') NOT NULL DEFAULT 'all',
			`conditions`       JSON              DEFAULT NULL,
			`actions`          JSON              DEFAULT NULL,
			`status`           ENUM('active','inactive') NOT NULL DEFAULT 'active',
			`run_count`        INT UNSIGNED      NOT NULL DEFAULT 0,
			`last_run`         DATETIME          DEFAULT NULL,
			`created_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Trigger logs table - also created by AH_DB_Installer but install_tables()
		// is called on every admin page load so we guard with IF NOT EXISTS.
		if ( class_exists( 'AH_DB_Installer' ) ) {
			AH_DB_Installer::ensure_trigger_logs();
		} else {
			$lg = self::logs_table();
			$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$lg}` (
				`id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
				`rule_id`       INT UNSIGNED     NOT NULL,
				`trigger_name`  VARCHAR(100)     NOT NULL,
				`context_data`  JSON             DEFAULT NULL,
				`action_index`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
				`action_type`   VARCHAR(50)      NOT NULL DEFAULT '',
				`action_config` JSON             DEFAULT NULL,
				`status`        ENUM('pending','sent','failed','unsent') NOT NULL DEFAULT 'pending',
				`is_done`       TINYINT(1)       NOT NULL DEFAULT 0,
				`is_unsent`     TINYINT(1)       NOT NULL DEFAULT 0,
				`attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
				`error_message` TEXT             DEFAULT NULL,
				`sent_at`       DATETIME         DEFAULT NULL,
				`failed_at`     DATETIME         DEFAULT NULL,
				`created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `idx_rule`    (`rule_id`),
				KEY `idx_status`  (`status`),
				KEY `idx_trigger` (`trigger_name`),
				KEY `idx_done`    (`is_done`),
				KEY `idx_unsent`  (`is_unsent`)
			) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	// ── CRUD ─────────────────────────────────────────────────────────────────

	public static function get_all(): array {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT * FROM `" . self::table() . "` ORDER BY id ASC" ) ?: array();
		return array_map( array( self::class, 'decode' ), $rows );
	}

	public static function get( int $id ): ?object {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . self::table() . "` WHERE id = %d", $id ) );
		return $row ? self::decode( $row ) : null;
	}

	public static function save( int $id, array $data ): int {
		global $wpdb;
		$t = self::table();

		$conditions = array();
		foreach ( (array) ( $data['conditions'] ?? array() ) as $c ) {
			$field = sanitize_key( $c['field'] ?? '' );
			if ( ! $field ) continue;
			$conditions[] = array(
				'field'    => $field,
				'operator' => self::valid_operator( $c['operator'] ?? 'equals' ),
				'value'    => sanitize_text_field( $c['value'] ?? '' ),
			);
		}

		$actions = array();
		foreach ( (array) ( $data['actions'] ?? array() ) as $a ) {
			$sanitized = self::sanitize_action( $a );
			if ( $sanitized ) $actions[] = $sanitized;
		}

		$row = array(
			'name'             => sanitize_text_field( $data['name'] ?? '' ),
			'trigger_name'     => sanitize_text_field( $data['trigger_name'] ?? 'form_submit' ),
			'conditions_match' => 'any' === ( $data['conditions_match'] ?? '' ) ? 'any' : 'all',
			'conditions'       => wp_json_encode( $conditions ),
			'actions'          => wp_json_encode( $actions ),
			'status'           => 'inactive' === ( $data['status'] ?? '' ) ? 'inactive' : 'active',
		);

		if ( $id > 0 ) {
			$wpdb->update( $t, $row, array( 'id' => $id ) );
			return $id;
		}
		$wpdb->insert( $t, $row );
		return (int) $wpdb->insert_id;
	}

	public static function delete( int $id ): void {
		global $wpdb;
		$wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
	}

	private static function decode( object $row ): object {
		$row->conditions = $row->conditions ? json_decode( $row->conditions, true ) : array();
		$row->actions    = $row->actions    ? json_decode( $row->actions, true )    : array();
		return $row;
	}

	// ── Public API ────────────────────────────────────────────────────────────

	/**
	 * Queue all matching rule actions into ah_trigger_logs as 'pending'.
	 * Nothing executes here - the cron picks them up and fires them in the background.
	 *
	 * Call from anywhere:
	 *   AH_Rules_Engine::evaluate( 'order_placed', [
	 *       'customer_name' => 'John',
	 *       'email'         => 'john@example.com',
	 *   ] );
	 */
	public static function evaluate( string $trigger_name, array $context ): void {
		global $wpdb;
		self::install_tables();

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `" . self::table() . "` WHERE status = 'active' AND trigger_name = %s",
			$trigger_name
		) ) ?: array();

		if ( $wpdb->last_error ) {
			error_log( 'AH_Rules_Engine::evaluate() rules query error: ' . $wpdb->last_error );
		}

		$now = current_time( 'mysql' );

		foreach ( $rows as $rule ) {
			$rule = self::decode( $rule );
			if ( ! self::conditions_pass( $rule, $context ) ) continue;

			foreach ( $rule->actions as $idx => $action ) {
				$inserted = $wpdb->insert( self::logs_table(), array(
					'rule_id'       => (int) $rule->id,
					'trigger_name'  => $trigger_name,
					'context_data'  => wp_json_encode( $context ),
					'action_index'  => $idx,
					'action_type'   => $action['type'] ?? '',
					'action_config' => wp_json_encode( $action ),
					'status'        => 'pending',
					'attempts'      => 0,
				) );
				if ( false === $inserted ) {
					error_log( 'AH_Rules_Engine::evaluate() log insert error (rule #' . $rule->id . '): ' . $wpdb->last_error );
				}
			}

			$wpdb->query( $wpdb->prepare(
				"UPDATE `" . self::table() . "` SET run_count = run_count + 1, last_run = %s WHERE id = %d",
				$now,
				(int) $rule->id
			) );
		}
	}

	// ── Cron: process pending + retry failed ──────────────────────────────────

	/**
	 * Called by WP-Cron every minute.
	 * Picks up all 'pending' rows and 'failed' rows under the retry limit,
	 * executes them, and updates the log status.
	 */
	public static function cron_process(): void {
		global $wpdb;
		$lg  = self::logs_table();
		$cfg = self::get_config();
		if ( '0' === ( $cfg['cron_enabled'] ?? '1' ) ) return;
		$max = max( 1, (int) ( $cfg['retry_max_attempts'] ?? 3 ) );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$lg}`
			 WHERE is_done = 0 AND is_unsent = 0
			   AND ( status = 'pending' OR ( status = 'failed' AND attempts < %d ) )
			 ORDER BY id ASC
			 LIMIT 100",
			$max
		) ) ?: array();

		foreach ( $rows as $row ) {
			$action  = json_decode( $row->action_config ?? '{}', true ) ?: array();
			$context = json_decode( $row->context_data  ?? '{}', true ) ?: array();

			$error = null;
			try {
				self::run_actions( array( $action ), $context );
			} catch ( \Throwable $e ) {
				$error = $e->getMessage();
			}

			if ( $error ) {
				$wpdb->update( $lg, array(
					'status'        => 'failed',
					'attempts'      => (int) $row->attempts + 1,
					'error_message' => $error,
					'failed_at'     => current_time( 'mysql' ),
				), array( 'id' => (int) $row->id ) );
			} else {
				$wpdb->update( $lg, array(
					'status'  => 'sent',
					'is_done' => 1,
					'sent_at' => current_time( 'mysql' ),
				), array( 'id' => (int) $row->id ) );
			}
		}
	}

	// ── Condition evaluation ─────────────────────────────────────────────────

	private static function conditions_pass( object $rule, array $context ): bool {
		if ( empty( $rule->conditions ) ) return true;

		$any = ( 'any' === $rule->conditions_match );
		foreach ( $rule->conditions as $c ) {
			$actual   = strtolower( trim( (string) ( $context[ $c['field'] ] ?? '' ) ) );
			$expected = strtolower( trim( $c['value'] ?? '' ) );
			$op       = $c['operator'] ?? 'equals';

			$pass = match ( $op ) {
				'equals'       => $actual === $expected,
				'not_equals'   => $actual !== $expected,
				'contains'     => str_contains( $actual, $expected ),
				'not_contains' => ! str_contains( $actual, $expected ),
				'starts_with'  => str_starts_with( $actual, $expected ),
				'ends_with'    => str_ends_with( $actual, $expected ),
				'is_empty'     => $actual === '',
				'is_not_empty' => $actual !== '',
				'greater_than' => is_numeric( $actual ) && is_numeric( $expected ) && ( (float) $actual > (float) $expected ),
				'less_than'    => is_numeric( $actual ) && is_numeric( $expected ) && ( (float) $actual < (float) $expected ),
				default        => false,
			};

			if ( $any && $pass )  return true;
			if ( ! $any && ! $pass ) return false;
		}
		return ! $any;
	}

	// ── Action runner ─────────────────────────────────────────────────────────

	private static function run_actions( array $actions, array $context ): void {
		foreach ( $actions as $a ) {
			match ( $a['type'] ?? '' ) {
				'send_email'   => self::action_email( $a, $context ),
				'whatsapp'     => self::action_whatsapp( $a, $context ),
				'http_request' => self::action_http( $a, $context ),
				default        => null,
			};
		}
	}

	// ── send_email ────────────────────────────────────────────────────────────

	private static function action_email( array $a, array $context ): void {
		$ctx = array_merge( self::config_tokens(), $context );
		$cfg = self::get_config();

		$to = sanitize_email( self::fill( $a['to'] ?? '', $ctx ) );
		if ( ! $to ) return;

		$subject  = self::fill( $a['subject'] ?? 'Notification', $ctx );
		$body_tpl = $a['body'] ?? '';
		$is_html  = ! empty( $a['html'] );

		$body = $is_html
			? self::fill_html( $body_tpl, $ctx )
			: self::fill( $body_tpl, $ctx );

		$headers = array( 'Content-Type: ' . ( $is_html ? 'text/html' : 'text/plain' ) . '; charset=UTF-8' );

		// Resolve channel → global config fallback chain
		$channel = ! empty( $a['channel_id'] ) ? self::get_email_channel( $a['channel_id'] ) : null;

		$from_name = sanitize_text_field(
			! empty( $a['from_name'] )
				? self::fill( $a['from_name'], $ctx )
				: ( $channel ? $channel['from_name'] : $cfg['email_from_name'] )
		);
		$from_email = sanitize_email(
			! empty( $a['from_email'] )
				? self::fill( $a['from_email'], $ctx )
				: ( $channel ? $channel['from_email'] : $cfg['email_from_email'] )
		);
		if ( $from_name || $from_email ) {
			$headers[] = "From: {$from_name} <{$from_email}>";
		}

		if ( ! empty( $a['cc'] ) ) {
			$headers[] = 'CC: ' . sanitize_email( self::fill( $a['cc'], $ctx ) );
		}
		if ( ! empty( $cfg['email_bcc'] ) ) {
			$headers[] = 'BCC: ' . sanitize_email( $cfg['email_bcc'] );
		}

		// Per-channel SMTP override via phpmailer_init - added just before wp_mail, removed immediately after
		$smtp_hook = null;
		if ( $channel && ! empty( $channel['host'] ) ) {
			$ch        = $channel;
			$smtp_hook = static function ( $mailer ) use ( $ch ) {
				$mailer->isSMTP();
				$mailer->Host       = $ch['host'];
				$mailer->Port       = (int) $ch['port'];
				$mailer->SMTPAuth   = ( '' !== (string) $ch['username'] );
				$mailer->Username   = $ch['username'];
				$mailer->Password   = $ch['password'];
				$enc = $ch['encryption'] ?? 'tls';
				$mailer->SMTPSecure = ( 'ssl' === $enc ) ? 'ssl' : ( 'none' === $enc ? '' : 'tls' );
			};
			add_action( 'phpmailer_init', $smtp_hook );
		}

		wp_mail( $to, $subject, $body, $headers );

		if ( $smtp_hook ) {
			remove_action( 'phpmailer_init', $smtp_hook );
		}
	}

	// ── whatsapp ──────────────────────────────────────────────────────────────

	private static function action_whatsapp( array $a, array $context ): void {
		$ctx   = array_merge( self::config_tokens(), $context );
		$cfg   = self::get_config();
		// Fall back to global config when action field is blank
		$url   = esc_url_raw( self::fill( ! empty( $a['api_url'] ) ? $a['api_url'] : $cfg['wa_api_url'], $ctx ) );
		$token = self::fill( ! empty( $a['auth_token'] ) ? $a['auth_token'] : $cfg['wa_auth_token'], $ctx );
		$phone = preg_replace( '/\D/', '', self::fill( $a['to_phone'] ?? '', $ctx ) );
		$msg   = self::fill( $a['message'] ?? '', $ctx );

		if ( ! $url || ! $phone || ! $msg ) return;

		// Body template: use custom JSON if set, otherwise auto-build for common providers
		if ( ! empty( $a['body_json'] ) ) {
			$body_raw = self::fill( $a['body_json'], $ctx );
			$body     = json_decode( $body_raw, true );
		} else {
			// Default: WATI / generic WhatsApp Business API pattern
			$body = array(
				'to'          => $phone,
				'type'        => 'text',
				'text'        => array( 'body' => $msg ),
			);
		}

		$headers = array( 'Content-Type' => 'application/json' );
		if ( $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => 15,
		) );
	}

	// ── http_request ──────────────────────────────────────────────────────────

	private static function action_http( array $a, array $context ): void {
		$ctx    = array_merge( self::config_tokens(), $context );
		$url    = esc_url_raw( self::fill( $a['url'] ?? '', $ctx ) );
		$method = strtoupper( $a['method'] ?? 'POST' );
		if ( ! $url ) return;

		// Headers: parse from JSON or key:value lines
		$headers  = array();
		$raw_hdrs = self::fill( $a['headers'] ?? '', $ctx );
		if ( $raw_hdrs ) {
			$decoded = json_decode( $raw_hdrs, true );
			if ( is_array( $decoded ) ) {
				$headers = $decoded;
			} else {
				foreach ( explode( "\n", $raw_hdrs ) as $line ) {
					if ( str_contains( $line, ':' ) ) {
						[ $k, $v ] = explode( ':', $line, 2 );
						$headers[ trim( $k ) ] = trim( $v );
					}
				}
			}
		}

		// Auth
		$auth_type = $a['auth_type'] ?? 'none';
		if ( 'bearer' === $auth_type && ! empty( $a['auth_value'] ) ) {
			$headers['Authorization'] = 'Bearer ' . self::fill( $a['auth_value'], $ctx );
		} elseif ( 'basic' === $auth_type && ! empty( $a['auth_value'] ) ) {
			$headers['Authorization'] = 'Basic ' . base64_encode( self::fill( $a['auth_value'], $ctx ) );
		}

		// Body
		$body_tpl     = $a['body'] ?? '';
		$content_type = $a['content_type'] ?? 'json';

		if ( 'json' === $content_type ) {
			$filled = self::fill( $body_tpl, $ctx );
			$headers['Content-Type'] = 'application/json';
			$body = $filled;
		} else {
			// form-encoded: parse JSON template and fill each value
			$tpl_data = json_decode( $body_tpl, true ) ?: array();
			$body = array();
			foreach ( $tpl_data as $k => $v ) {
				$body[ $k ] = self::fill( (string) $v, $ctx );
			}
		}

		$args = array(
			'method'  => $method,
			'headers' => $headers,
			'timeout' => 15,
		);
		if ( 'GET' !== $method ) $args['body'] = $body;

		wp_remote_request( $url, $args );
	}

	// ── Placeholder interpolation ─────────────────────────────────────────────

	/**
	 * Replace {key} tokens with context values (plain text - no escaping).
	 * Use for: URLs, JSON bodies, plain-text emails.
	 */
	public static function fill( string $tpl, array $ctx ): string {
		foreach ( $ctx as $k => $v ) {
			$tpl = str_replace( '{' . $k . '}', (string) $v, $tpl );
		}
		return $tpl;
	}

	/**
	 * Replace {key} tokens - values are HTML-escaped (safe for HTML email).
	 */
	public static function fill_html( string $tpl, array $ctx ): string {
		foreach ( $ctx as $k => $v ) {
			$tpl = str_replace( '{' . $k . '}', esc_html( (string) $v ), $tpl );
		}
		return $tpl;
	}

	// ── Sanitize actions on save ──────────────────────────────────────────────

	private static function sanitize_action( array $a ): ?array {
		$type = sanitize_key( $a['type'] ?? '' );
		return match ( $type ) {
			'send_email' => array(
				'type'       => 'send_email',
				'to'         => sanitize_text_field( $a['to']         ?? '' ),
				'subject'    => sanitize_text_field( $a['subject']    ?? '' ),
				'body'       => wp_kses_post(        $a['body']       ?? '' ),
				'html'       => ! empty( $a['html'] ) ? 1 : 0,
				'from_name'  => sanitize_text_field( $a['from_name']  ?? '' ),
				'from_email' => sanitize_email(      $a['from_email'] ?? '' ),
				'cc'         => sanitize_text_field( $a['cc']         ?? '' ),
				'channel_id' => sanitize_key(        $a['channel_id'] ?? '' ),
			),
			'whatsapp' => array(
				'type'       => 'whatsapp',
				'api_url'    => esc_url_raw(         $a['api_url']    ?? '' ),
				'auth_token' => sanitize_text_field( $a['auth_token'] ?? '' ),
				'to_phone'   => sanitize_text_field( $a['to_phone']   ?? '' ),
				'message'    => sanitize_textarea_field( $a['message'] ?? '' ),
				'body_json'  => wp_kses_post(        $a['body_json']  ?? '' ),
			),
			'http_request' => array(
				'type'         => 'http_request',
				'url'          => esc_url_raw(            $a['url']          ?? '' ),
				'method'       => strtoupper( sanitize_key( $a['method']     ?? 'POST' ) ),
				'auth_type'    => sanitize_key(           $a['auth_type']    ?? 'none' ),
				'auth_value'   => sanitize_text_field(    $a['auth_value']   ?? '' ),
				'headers'      => sanitize_textarea_field($a['headers']      ?? '' ),
				'content_type' => sanitize_key(           $a['content_type'] ?? 'json' ),
				'body'         => wp_kses_post(           $a['body']         ?? '' ),
			),
			default => null,
		};
	}

	// ── Meta helpers ─────────────────────────────────────────────────────────

	public static function valid_operator( string $op ): string {
		return array_key_exists( $op, self::operators() ) ? $op : 'equals';
	}

	public static function operators(): array {
		return array(
			'equals'       => 'equals',
			'not_equals'   => 'does not equal',
			'contains'     => 'contains',
			'not_contains' => 'does not contain',
			'starts_with'  => 'starts with',
			'ends_with'    => 'ends with',
			'is_empty'     => 'is empty',
			'is_not_empty' => 'is not empty',
			'greater_than' => 'is greater than',
			'less_than'    => 'is less than',
		);
	}

	public static function trigger_presets(): array {
		return array(
			'contact_submitted'      => 'Contact Form Submitted',
			'consultation_submitted' => 'Consultation Form Submitted',
			'form_submit'            => 'Form Submission (built-in)',
			'user_signup'            => 'User Signup',
			'order_placed'           => 'Order Placed',
			'order_paid'             => 'Order Paid',
			'appointment'            => 'Appointment Booked',
			'lead_created'           => 'Lead Created',
			'custom'                 => 'Custom (define your own)',
		);
	}

	// ── Global config ─────────────────────────────────────────────────────────

	public static function get_config(): array {
		$saved = get_option( 'ah_re_config', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return array_merge( array(
			'email_from_name'    => get_bloginfo( 'name' ),
			'email_from_email'   => get_option( 'admin_email' ),
			'email_bcc'          => '',
			'wa_api_url'         => '',
			'wa_auth_token'      => '',
			'retry_max_attempts' => '3',
			'cron_enabled'       => '1',
		), (array) $saved );
	}

	public static function save_config( array $data ): void {
		$clean = array(
			'email_from_name'    => sanitize_text_field( $data['email_from_name']    ?? '' ),
			'email_from_email'   => sanitize_email(      $data['email_from_email']   ?? '' ),
			'email_bcc'          => sanitize_email(      $data['email_bcc']          ?? '' ),
			'wa_api_url'         => esc_url_raw(         $data['wa_api_url']         ?? '' ),
			'wa_auth_token'      => sanitize_text_field( $data['wa_auth_token']      ?? '' ),
			'retry_max_attempts' => (string) max( 1, min( 10, (int) ( $data['retry_max_attempts'] ?? 3 ) ) ),
			'cron_enabled'       => ! empty( $data['cron_enabled'] ) ? '1' : '0',
		);
		update_option( 'ah_re_config', $clean );
	}

	// ── Custom config variables ───────────────────────────────────────────────

	public static function get_custom_vars(): array {
		$saved = get_option( 'ah_re_custom_vars', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return is_array( $saved ) ? $saved : array();
	}

	public static function save_custom_vars( array $vars ): void {
		$clean = array();
		foreach ( $vars as $v ) {
			$key = sanitize_key( $v['key'] ?? '' );
			if ( ! $key ) continue;
			$clean[] = array(
				'key'   => $key,
				'label' => sanitize_text_field( $v['label'] ?? $key ),
				'value' => sanitize_text_field( $v['value'] ?? '' ),
			);
		}
		update_option( 'ah_re_custom_vars', $clean );
	}

	// ── Email channels / SMTP profiles ────────────────────────────────────────

	public static function get_email_channels(): array {
		$saved = get_option( 'ah_re_email_channels', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return is_array( $saved ) ? $saved : array();
	}

	/** Returns id => name map for select dropdowns. */
	public static function get_email_channels_list(): array {
		$list = array( '' => '- Default (site mail) -' );
		foreach ( self::get_email_channels() as $ch ) {
			if ( ! empty( $ch['id'] ) ) {
				$list[ $ch['id'] ] = $ch['name'] ?: $ch['id'];
			}
		}
		return $list;
	}

	public static function get_email_channel( string $id ): ?array {
		foreach ( self::get_email_channels() as $ch ) {
			if ( ( $ch['id'] ?? '' ) === $id ) return $ch;
		}
		return null;
	}

	public static function save_email_channels( array $channels ): void {
		$clean = array();
		foreach ( $channels as $ch ) {
			$id = sanitize_key( $ch['id'] ?? '' );
			if ( ! $id ) continue;
			$enc = in_array( $ch['encryption'] ?? '', array( 'tls', 'ssl', 'none' ), true )
				? $ch['encryption'] : 'tls';
			$clean[] = array(
				'id'         => $id,
				'name'       => sanitize_text_field( $ch['name']       ?? '' ),
				'from_name'  => sanitize_text_field( $ch['from_name']  ?? '' ),
				'from_email' => sanitize_email(      $ch['from_email'] ?? '' ),
				'provider'   => sanitize_key(        $ch['provider']   ?? 'custom' ),
				'host'       => sanitize_text_field( $ch['host']       ?? '' ),
				'port'       => (int) ( $ch['port'] ?? 587 ),
				'username'   => sanitize_text_field( $ch['username']   ?? '' ),
				'password'   => (string) ( $ch['password'] ?? '' ),
				'encryption' => $enc,
			);
		}
		update_option( 'ah_re_email_channels', $clean );
	}

	/**
	 * Return config values + custom variables as {config_xxx} tokens.
	 * Built-in keys: {config_email_from_name}, {config_wa_api_url}, etc.
	 * Custom keys:   {config_support_email}, {config_whatsapp_number}, etc.
	 */
	private static function config_tokens(): array {
		$tokens = array();
		foreach ( self::get_config() as $k => $v ) {
			$tokens[ 'config_' . $k ] = $v;
		}
		foreach ( self::get_custom_vars() as $var ) {
			if ( ! empty( $var['key'] ) ) {
				$tokens[ 'config_' . $var['key'] ] = $var['value'] ?? '';
			}
		}
		return $tokens;
	}

	// ── Trigger log helpers ───────────────────────────────────────────────────

	public static function get_logs( int $limit = 100, int $offset = 0 ): array {
		global $wpdb;
		$lg = self::logs_table();
		$rt = self::table();
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT l.*, r.name AS rule_name
			 FROM `{$lg}` l
			 LEFT JOIN `{$rt}` r ON r.id = l.rule_id
			 ORDER BY l.id DESC LIMIT %d OFFSET %d",
			$limit, $offset
		) ) ?: array();
	}

	public static function count_logs(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `" . self::logs_table() . "`" );
	}

	public static function delete_log( int $id ): void {
		global $wpdb;
		$wpdb->delete( self::logs_table(), array( 'id' => $id ), array( '%d' ) );
	}

	public static function mark_log_unsent( int $id ): void {
		global $wpdb;
		$wpdb->update( self::logs_table(), array( 'is_unsent' => 1, 'status' => 'unsent' ), array( 'id' => $id ) );
	}

	public static function retry_log( int $id ): bool {
		global $wpdb;
		$lg  = self::logs_table();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$lg}` WHERE id = %d", $id ) );
		if ( ! $row ) return false;

		$action  = json_decode( $row->action_config ?? '{}', true ) ?: array();
		$context = json_decode( $row->context_data  ?? '{}', true ) ?: array();

		$error = null;
		try {
			self::run_actions( array( $action ), $context );
		} catch ( \Throwable $e ) {
			$error = $e->getMessage();
		}

		if ( $error ) {
			$wpdb->update( $lg, array(
				'attempts'      => (int) $row->attempts + 1,
				'error_message' => $error,
				'failed_at'     => current_time( 'mysql' ),
			), array( 'id' => $id ) );
			return false;
		}

		$wpdb->update( $lg, array(
			'status'  => 'sent',
			'is_done' => 1,
			'sent_at' => current_time( 'mysql' ),
		), array( 'id' => $id ) );
		return true;
	}
}
