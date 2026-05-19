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

	public static function install_tables(): void {
		global $wpdb;
		$t  = self::table();
		$cs = $wpdb->get_charset_collate();
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
		) ENGINE=InnoDB {$cs}" );
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
	 * Fire all active rules that match $trigger_name.
	 *
	 * Call from anywhere in your plugin or theme:
	 *   AH_Rules_Engine::evaluate( 'order_placed', [
	 *       'customer_name' => 'John',
	 *       'email'         => 'john@example.com',
	 *       'amount'        => '250.00',
	 *   ] );
	 *
	 * @param string $trigger_name  Any string - matches rules with the same trigger name.
	 * @param array  $context       Key-value map of variables available in placeholders.
	 */
	public static function evaluate( string $trigger_name, array $context ): void {
		global $wpdb;
		self::install_tables();

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `" . self::table() . "` WHERE status = 'active' AND trigger_name = %s",
			$trigger_name
		) ) ?: array();

		foreach ( $rows as $rule ) {
			$rule = self::decode( $rule );
			if ( self::conditions_pass( $rule, $context ) ) {
				self::run_actions( $rule->actions, $context );
				$wpdb->query( $wpdb->prepare(
					"UPDATE `" . self::table() . "` SET run_count = run_count + 1, last_run = %s WHERE id = %d",
					current_time( 'mysql' ),
					$rule->id
				) );
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
		$to = sanitize_email( self::fill( $a['to'] ?? '', $context ) );
		if ( ! $to ) return;

		$subject  = self::fill( $a['subject'] ?? 'Notification', $context );
		$body_tpl = $a['body'] ?? '';
		$is_html  = ! empty( $a['html'] );

		$body = $is_html
			? self::fill_html( $body_tpl, $context )
			: self::fill( $body_tpl, $context );

		$headers = array( 'Content-Type: ' . ( $is_html ? 'text/html' : 'text/plain' ) . '; charset=UTF-8' );

		if ( ! empty( $a['from_name'] ) || ! empty( $a['from_email'] ) ) {
			$from_name  = sanitize_text_field( $a['from_name']  ?? get_bloginfo( 'name' ) );
			$from_email = sanitize_email(      $a['from_email'] ?? get_option( 'admin_email' ) );
			$headers[]  = "From: {$from_name} <{$from_email}>";
		}

		if ( ! empty( $a['cc'] ) ) {
			$headers[] = 'CC: ' . sanitize_email( self::fill( $a['cc'], $context ) );
		}

		wp_mail( $to, $subject, $body, $headers );
	}

	// ── whatsapp ──────────────────────────────────────────────────────────────

	private static function action_whatsapp( array $a, array $context ): void {
		$url   = esc_url_raw( self::fill( $a['api_url'] ?? '', $context ) );
		$token = self::fill( $a['auth_token'] ?? '', $context );
		$phone = preg_replace( '/\D/', '', self::fill( $a['to_phone'] ?? '', $context ) );
		$msg   = self::fill( $a['message'] ?? '', $context );

		if ( ! $url || ! $phone || ! $msg ) return;

		// Body template: use custom JSON if set, otherwise auto-build for common providers
		if ( ! empty( $a['body_json'] ) ) {
			$body_raw = self::fill( $a['body_json'], $context );
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
		$url    = esc_url_raw( self::fill( $a['url'] ?? '', $context ) );
		$method = strtoupper( $a['method'] ?? 'POST' );
		if ( ! $url ) return;

		// Headers: parse from JSON or key:value lines
		$headers = array();
		$raw_hdrs = self::fill( $a['headers'] ?? '', $context );
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
			$headers['Authorization'] = 'Bearer ' . self::fill( $a['auth_value'], $context );
		} elseif ( 'basic' === $auth_type && ! empty( $a['auth_value'] ) ) {
			$headers['Authorization'] = 'Basic ' . base64_encode( self::fill( $a['auth_value'], $context ) );
		}

		// Body
		$body_tpl     = $a['body'] ?? '';
		$content_type = $a['content_type'] ?? 'json';

		if ( 'json' === $content_type ) {
			$filled = self::fill( $body_tpl, $context );
			$headers['Content-Type'] = 'application/json';
			$body = $filled;
		} else {
			// form-encoded: parse JSON template and fill each value
			$tpl_data = json_decode( $body_tpl, true ) ?: array();
			$body = array();
			foreach ( $tpl_data as $k => $v ) {
				$body[ $k ] = self::fill( (string) $v, $context );
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
			'form_submit'    => 'Form Submission (built-in)',
			'user_signup'    => 'User Signup',
			'order_placed'   => 'Order Placed',
			'order_paid'     => 'Order Paid',
			'appointment'    => 'Appointment Booked',
			'lead_created'   => 'Lead Created',
			'custom'         => 'Custom (define your own)',
		);
	}
}
