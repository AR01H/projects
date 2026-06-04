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

		// Schema migrations - add new columns if missing
		if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$t}` LIKE 'settings'" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$t}` ADD COLUMN `settings` JSON DEFAULT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$lg = self::logs_table();
		if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$lg}` LIKE 'scheduled_at'" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$lg}` ADD COLUMN `scheduled_at` DATETIME DEFAULT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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

		// Condition groups: each group has its own match + conditions array
		$raw_groups       = (array) ( $data['conditions'] ?? array() );
		$condition_groups = array();
		foreach ( $raw_groups as $g ) {
			if ( isset( $g['field'] ) ) {
				// Legacy flat format - wrap into single group
				$field = sanitize_key( $g['field'] );
				if ( ! $field ) continue;
				$condition_groups[] = array(
					'match'      => 'any' === ( $data['conditions_match'] ?? 'all' ) ? 'any' : 'all',
					'conditions' => array( array(
						'field'    => $field,
						'operator' => self::valid_operator( $g['operator'] ?? 'equals' ),
						'value'    => sanitize_text_field( $g['value'] ?? '' ),
					) ),
				);
			} else {
				// New group format
				$group_conds = array();
				foreach ( (array) ( $g['conditions'] ?? array() ) as $c ) {
					$field = sanitize_key( $c['field'] ?? '' );
					if ( ! $field ) continue;
					$group_conds[] = array(
						'field'    => $field,
						'operator' => self::valid_operator( $c['operator'] ?? 'equals' ),
						'value'    => sanitize_text_field( $c['value'] ?? '' ),
					);
				}
				if ( ! empty( $group_conds ) ) {
					$condition_groups[] = array(
						'match'      => 'any' === ( $g['match'] ?? 'all' ) ? 'any' : 'all',
						'conditions' => $group_conds,
					);
				}
			}
		}

		$actions = array();
		foreach ( (array) ( $data['actions'] ?? array() ) as $a ) {
			$sanitized = self::sanitize_action( $a );
			if ( $sanitized ) $actions[] = $sanitized;
		}

		// Dedup / cooldown settings
		$raw_s    = (array) ( $data['settings'] ?? array() );
		$settings = array(
			'dedup_key'          => sanitize_text_field( $raw_s['dedup_key']          ?? '' ),
			'dedup_window_hours' => max( 0, (int) ( $raw_s['dedup_window_hours'] ?? 0 ) ),
			'cooldown_minutes'   => max( 0, (int) ( $raw_s['cooldown_minutes']   ?? 0 ) ),
		);

		$row = array(
			'name'             => sanitize_text_field( $data['name']             ?? '' ),
			'trigger_name'     => sanitize_text_field( $data['trigger_name']     ?? 'form_submit' ),
			'conditions_match' => 'any' === ( $data['conditions_match'] ?? '' ) ? 'any' : 'all',
			'conditions'       => wp_json_encode( $condition_groups ),
			'actions'          => wp_json_encode( $actions ),
			'status'           => 'inactive' === ( $data['status'] ?? '' ) ? 'inactive' : 'active',
			'settings'         => wp_json_encode( $settings ),
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
		$row->settings   = ! empty( $row->settings ) ? json_decode( $row->settings, true ) : array();
		return $row;
	}

	// ── Public API ────────────────────────────────────────────────────────────

	/**
	 * Evaluate all matching rules for a trigger event.
	 *
	 * @param string $trigger_name  Event slug, e.g. 'sugarcane_contact_form'.
	 * @param array  $context       Key/value pairs - become {tokens} in action templates.
	 * @param bool   $immediate     true  → run matching actions right now (synchronous).
	 *                              false → queue into ah_trigger_logs for cron (default).
	 *
	 * Call from anywhere:
	 *   AH_Rules_Engine::evaluate( 'sugarcane_contact_form', [
	 *       'name'  => 'Jane',
	 *       'email' => 'jane@example.com',
	 *   ], true );
	 */
	public static function evaluate( string $trigger_name, array $context, bool $immediate = false ): void {
		global $wpdb;
		self::install_tables();

		$t  = self::table();
		$lg = self::logs_table();

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$t}` WHERE status = 'active' AND trigger_name = %s",
			$trigger_name
		) ) ?: array();

		if ( $wpdb->last_error ) {
			error_log( 'AH_Rules_Engine::evaluate() DB error: ' . $wpdb->last_error );
		}

		$now    = current_time( 'mysql' );
		$now_ts = current_time( 'timestamp' );

		foreach ( $rows as $rule ) {
			$rule = self::decode( $rule );
			if ( ! self::conditions_pass( $rule, $context ) ) continue;
			if ( ! self::passes_dedup( $rule, $context, $lg ) ) continue;

			$delay_seconds = 0;

			foreach ( $rule->actions as $idx => $action ) {
				$type = $action['type'] ?? '';

				// wait: accumulate delay, no log entry for the wait itself
				if ( 'wait' === $type ) {
					$mults          = array( 'minutes' => 60, 'hours' => 3600, 'days' => 86400 );
					$delay_seconds += max( 0, (int) ( $action['duration'] ?? 0 ) )
					                  * ( $mults[ $action['unit'] ?? 'minutes' ] ?? 60 );
					continue;
				}

				$scheduled_at = $delay_seconds > 0
					? gmdate( 'Y-m-d H:i:s', $now_ts + $delay_seconds )
					: null;

				$log_base = array(
					'rule_id'       => (int) $rule->id,
					'trigger_name'  => $trigger_name,
					'context_data'  => wp_json_encode( $context ),
					'action_index'  => $idx,
					'action_type'   => $type,
					'action_config' => wp_json_encode( $action ),
				);

				// Run synchronously only when immediate mode AND no delay has accumulated
				if ( $immediate && null === $scheduled_at ) {
					$error = null;
					try {
						self::run_actions( array( $action ), $context );
					} catch ( \Throwable $e ) {
						$error = $e->getMessage();
					}
					$log = array_merge( $log_base, array(
						'status'        => $error ? 'failed' : 'sent',
						'is_done'       => $error ? 0 : 1,
						'attempts'      => 1,
						'error_message' => $error,
					) );
					$log[ $error ? 'failed_at' : 'sent_at' ] = $now;
				} else {
					// Queue for cron (deferred or non-immediate)
					$log = array_merge( $log_base, array(
						'status'       => 'pending',
						'is_done'      => 0,
						'attempts'     => 0,
						'scheduled_at' => $scheduled_at,
					) );
				}

				if ( false === $wpdb->insert( $lg, $log ) ) {
					error_log( 'AH_Rules_Engine::evaluate() log insert error (rule #' . $rule->id . '): ' . $wpdb->last_error );
				}
			}

			$wpdb->query( $wpdb->prepare(
				"UPDATE `{$t}` SET run_count = run_count + 1, last_run = %s WHERE id = %d",
				$now, (int) $rule->id
			) );
		}
	}

	// Dedup + cooldown check - returns false if this rule should be skipped
	private static function passes_dedup( object $rule, array $context, string $lg ): bool {
		global $wpdb;
		$s = (array) ( $rule->settings ?? array() );

		// Cooldown: rule cannot fire more than once per N minutes (globally)
		$cooldown = max( 0, (int) ( $s['cooldown_minutes'] ?? 0 ) );
		if ( $cooldown > 0 ) {
			$count = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM `{$lg}` WHERE rule_id = %d AND is_unsent = 0
				   AND created_at >= DATE_SUB(NOW(), INTERVAL %d MINUTE)",
				(int) $rule->id, $cooldown
			) );
			if ( $count > 0 ) return false;
		}

		// Dedup: skip if same context value already triggered this rule within N hours
		$dedup_key    = trim( $s['dedup_key'] ?? '' );
		$dedup_window = max( 0, (int) ( $s['dedup_window_hours'] ?? 0 ) );
		if ( $dedup_key && $dedup_window > 0 ) {
			$field = preg_replace( '/^\{(.+)\}$/', '$1', $dedup_key );
			$value = (string) ( $context[ $field ] ?? self::fill( $dedup_key, $context ) );
			if ( $value !== '' ) {
				$count = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM `{$lg}` WHERE rule_id = %d AND is_unsent = 0
					   AND created_at >= DATE_SUB(NOW(), INTERVAL %d HOUR)
					   AND JSON_UNQUOTE(JSON_EXTRACT(context_data, %s)) = %s",
					(int) $rule->id, $dedup_window,
					'$.' . $field,
					$value
				) );
				if ( $count > 0 ) return false;
			}
		}

		return true;
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
			   AND ( scheduled_at IS NULL OR scheduled_at <= %s )
			 ORDER BY id ASC
			 LIMIT 100",
			$max, current_time( 'mysql' )
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

		$conditions = $rule->conditions;
		$any_top    = ( 'any' === $rule->conditions_match );

		// Legacy flat format: first element has a 'field' key
		if ( isset( $conditions[0]['field'] ) ) {
			return self::evaluate_flat_conditions( $conditions, $context, $any_top );
		}

		// Group format: each element has 'match' + 'conditions'
		foreach ( $conditions as $group ) {
			$any_group  = ( 'any' === ( $group['match'] ?? 'all' ) );
			$group_pass = self::evaluate_flat_conditions( $group['conditions'] ?? array(), $context, $any_group );
			if ( $any_top && $group_pass )       return true;
			if ( ! $any_top && ! $group_pass )   return false;
		}
		return ! $any_top;
	}

	private static function evaluate_flat_conditions( array $conditions, array $context, bool $any ): bool {
		if ( empty( $conditions ) ) return true;
		foreach ( $conditions as $c ) {
			$actual   = strtolower( trim( (string) ( $context[ $c['field'] ] ?? '' ) ) );
			$expected = strtolower( trim( $c['value'] ?? '' ) );
			$op       = $c['operator'] ?? 'equals';
			$list     = array_map( 'trim', explode( ',', $expected ) );
			$pass     = match ( $op ) {
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
				'in_list'      => in_array( $actual, $list, true ),
				'not_in_list'  => ! in_array( $actual, $list, true ),
				default        => false,
			};
			if ( $any && $pass )       return true;
			if ( ! $any && ! $pass )   return false;
		}
		return ! $any;
	}

	// ── Action runner ─────────────────────────────────────────────────────────

	private static function run_actions( array $actions, array $context ): void {
		foreach ( $actions as $a ) {
			match ( $a['type'] ?? '' ) {
				'send_email'    => self::action_email( $a, $context ),
				'whatsapp'      => self::action_whatsapp( $a, $context ),
				'http_request'  => self::action_http( $a, $context ),
				'update_option' => self::action_update_option( $a, $context ),
				default         => null,
			};
		}
	}

	// ── send_email ────────────────────────────────────────────────────────────

	private static function action_email( array $a, array $context ): void {
		$ctx = array_merge( self::config_tokens(), $context );
		$cfg = self::get_config();

		// Handle TO as array
		$to_list = is_array( $a['to'] ?? null ) ? $a['to'] : array( $a['to'] ?? '' );
		$to_recipients = array();
		foreach ( $to_list as $to_addr ) {
			$filled = self::fill( (string) $to_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email ) $to_recipients[] = $email;
		}
		if ( empty( $to_recipients ) ) return;
		$to = implode( ', ', $to_recipients );

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
			$channel ? $channel['from_name'] : $cfg['email_from_name']
		);
		$from_email = sanitize_email(
			$channel ? $channel['from_email'] : $cfg['email_from_email']
		);
		if ( $from_name || $from_email ) {
			$headers[] = "From: {$from_name} <{$from_email}>";
		}

		// Handle CC as array
		$cc_list = is_array( $a['cc'] ?? null ) ? $a['cc'] : ( ! empty( $a['cc'] ) ? array( $a['cc'] ) : array() );
		$cc_recipients = array();
		foreach ( $cc_list as $cc_addr ) {
			$filled = self::fill( (string) $cc_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email ) $cc_recipients[] = $email;
		}
		if ( ! empty( $cc_recipients ) ) {
			$headers[] = 'CC: ' . implode( ', ', $cc_recipients );
		}

		// Handle BCC - from rule action AND global config
		$bcc_list = is_array( $a['bcc'] ?? null ) ? $a['bcc'] : ( ! empty( $a['bcc'] ) ? array( $a['bcc'] ) : array() );
		$bcc_recipients = array();
		foreach ( $bcc_list as $bcc_addr ) {
			$filled = self::fill( (string) $bcc_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email ) $bcc_recipients[] = $email;
		}
		// Add global BCC
		if ( ! empty( $cfg['email_bcc'] ) ) {
			$global_bcc = is_array( $cfg['email_bcc'] ) ? $cfg['email_bcc'] : array_filter( array_map( 'trim', explode( ',', $cfg['email_bcc'] ) ) );
			foreach ( $global_bcc as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $bcc_recipients, true ) ) {
					$bcc_recipients[] = $email;
				}
			}
		}
		if ( ! empty( $bcc_recipients ) ) {
			$headers[] = 'BCC: ' . implode( ', ', $bcc_recipients );
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

	// ── update_option ─────────────────────────────────────────────────────────

	private static function action_update_option( array $a, array $context ): void {
		$ctx = array_merge( self::config_tokens(), $context );
		$key = sanitize_key( self::fill( $a['option_key'] ?? '', $ctx ) );
		if ( ! $key ) return;
		$value = sanitize_text_field( self::fill( $a['option_value'] ?? '', $ctx ) );
		update_option( $key, $value );
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

	private static function sanitize_email_list( $input ): array {
		if ( ! is_array( $input ) ) {
			$input = ! empty( $input ) ? array( $input ) : array();
		}
		$emails = array();
		foreach ( (array) $input as $email ) {
			$email = sanitize_text_field( trim( (string) $email ) );
			if ( $email ) {
				$emails[] = $email;
			}
		}
		return $emails;
	}

	private static function sanitize_action( array $a ): ?array {
		$type = sanitize_key( $a['type'] ?? '' );
		return match ( $type ) {
			'send_email' => array(
				'type'       => 'send_email',
				'to'         => self::sanitize_email_list( $a['to']         ?? array() ),
				'subject'    => sanitize_text_field( $a['subject']    ?? '' ),
				'body'       => wp_kses_post(        $a['body']       ?? '' ),
				'html'       => ! empty( $a['html'] ) ? 1 : 0,
				'channel_id' => sanitize_key(        $a['channel_id'] ?? '' ),
				'cc'         => self::sanitize_email_list( $a['cc']    ?? array() ),
				'bcc'        => self::sanitize_email_list( $a['bcc']   ?? array() ),
			),
			'wait' => array(
				'type'     => 'wait',
				'duration' => max( 1, (int) ( $a['duration'] ?? 1 ) ),
				'unit'     => in_array( $a['unit'] ?? 'minutes', array( 'minutes', 'hours', 'days' ), true )
				              ? $a['unit'] : 'minutes',
			),
			'update_option' => array(
				'type'         => 'update_option',
				'option_key'   => sanitize_key( $a['option_key']   ?? '' ),
				'option_value' => sanitize_text_field( $a['option_value'] ?? '' ),
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
			'in_list'      => 'is in (comma list)',
			'not_in_list'  => 'is not in (comma list)',
		);
	}

	public static function trigger_presets(): array {
		return array(
			'sugarcane_contact_form' => 'Sugarcane - Contact Form Submitted',
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

	public static function get_logs_filtered( array $filters, int $limit = 100, int $offset = 0 ): array {
		global $wpdb;
		$lg     = self::logs_table();
		$rt     = self::table();
		$where  = array();
		$params = array();

		if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
			$where[]  = 'l.status = %s';
			$params[] = sanitize_key( $filters['status'] );
		}
		if ( ! empty( $filters['action_type'] ) && 'all' !== $filters['action_type'] ) {
			$where[]  = 'l.action_type = %s';
			$params[] = sanitize_key( $filters['action_type'] );
		}
		if ( ! empty( $filters['search'] ) ) {
			$where[]  = '( l.trigger_name LIKE %s OR r.name LIKE %s )';
			$like     = '%' . $wpdb->esc_like( sanitize_text_field( $filters['search'] ) ) . '%';
			$params[] = $like;
			$params[] = $like;
		}

		$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$params[]  = $limit;
		$params[]  = $offset;

		return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"SELECT l.*, r.name AS rule_name
			 FROM `{$lg}` l
			 LEFT JOIN `{$rt}` r ON r.id = l.rule_id
			 {$where_sql}
			 ORDER BY l.id DESC LIMIT %d OFFSET %d",
			...$params
		) ) ?: array();
	}

	public static function count_logs_filtered( array $filters ): int {
		global $wpdb;
		$lg     = self::logs_table();
		$where  = array();
		$params = array();

		if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
			$where[]  = 'l.status = %s';
			$params[] = sanitize_key( $filters['status'] );
		}
		if ( ! empty( $filters['action_type'] ) && 'all' !== $filters['action_type'] ) {
			$where[]  = 'l.action_type = %s';
			$params[] = sanitize_key( $filters['action_type'] );
		}
		if ( ! empty( $filters['search'] ) ) {
			$where[]  = '( l.trigger_name LIKE %s OR r.name LIKE %s )';
			$like     = '%' . $wpdb->esc_like( sanitize_text_field( $filters['search'] ) ) . '%';
			$params[] = $like;
			$params[] = $like;
		}

		if ( ! $where ) {
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$lg}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$rt        = self::table();
		$where_sql = 'WHERE ' . implode( ' AND ', $where );
		return (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"SELECT COUNT(*) FROM `{$lg}` l LEFT JOIN `{$rt}` r ON r.id = l.rule_id {$where_sql}",
			...$params
		) );
	}

	public static function cancel_all_pending(): int {
		global $wpdb;
		$lg  = self::logs_table();
		$cfg = self::get_config();
		$max = max( 1, (int) ( $cfg['retry_max_attempts'] ?? 3 ) );
		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"UPDATE `{$lg}` SET status = 'unsent', is_unsent = 1
			 WHERE is_done = 0 AND is_unsent = 0
			   AND ( status = 'pending' OR ( status = 'failed' AND attempts < %d ) )",
			$max
		) );
		return (int) $wpdb->rows_affected;
	}

	public static function retry_all_pending(): array {
		global $wpdb;
		$lg  = self::logs_table();
		$cfg = self::get_config();
		$max = max( 1, (int) ( $cfg['retry_max_attempts'] ?? 3 ) );

		$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"SELECT * FROM `{$lg}`
			 WHERE is_done = 0 AND is_unsent = 0
			   AND ( status = 'pending' OR ( status = 'failed' AND attempts < %d ) )
			 ORDER BY id ASC LIMIT 200",
			$max
		) ) ?: array();

		$ok   = 0;
		$fail = 0;
		foreach ( $rows as $row ) {
			$action  = json_decode( $row->action_config ?? '{}', true ) ?: array();
			$context = json_decode( $row->context_data  ?? '{}', true ) ?: array();
			$error   = null;
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
				$fail++;
			} else {
				$wpdb->update( $lg, array(
					'status'  => 'sent',
					'is_done' => 1,
					'sent_at' => current_time( 'mysql' ),
				), array( 'id' => (int) $row->id ) );
				$ok++;
			}
		}
		return array( 'ok' => $ok, 'fail' => $fail );
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
