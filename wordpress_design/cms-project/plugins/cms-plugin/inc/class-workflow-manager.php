<?php
defined( 'ABSPATH' ) || exit;

/**
 * Workflow Manager - General-purpose automation platform.
 *
 * Call from anywhere:
 *   AH_Workflow_Manager::evaluate( 'your_trigger_name', [ 'field_key' => 'value', ... ] );
 *
 * Built-in triggers: form_submit (auto-fired on AH Form Builder submissions).
 * Custom triggers:   any string you define, e.g. 'order_placed', 'user_signup'.
 *
 * Actions: send_email · whatsapp · http_request
 */
class AH_Workflow_Manager {

	// ── Schema ───────────────────────────────────────────────────────────────

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_rules';
	}

	public static function logs_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_trigger_logs';
	}

	/** Every evaluate() call gets one row here - even when no rule matches. */
	public static function evaluate_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_evaluate_log';
	}

	public static function install_tables(): void {
		// if ( class_exists( 'AH_DB_Installer' ) ) {
		// 	AH_DB_Installer::ensure_rules_table();
		// 	AH_DB_Installer::ensure_trigger_logs();
		// 	AH_DB_Installer::ensure_evaluate_log();
		// }
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

		$raw_s    = (array) ( $data['settings'] ?? array() );
		$custom_vars = array();
		foreach ( (array) ( $raw_s['custom_vars'] ?? array() ) as $cv ) {
			$k = sanitize_key( $cv['key'] ?? '' );
			if ( $k ) {
				$custom_vars[] = array(
					'key'   => $k,
					'value' => sanitize_text_field( $cv['value'] ?? '' ),
				);
			}
		}

		$settings = array(
			'dedup_key'          => sanitize_text_field( $raw_s['dedup_key']          ?? '' ),
			'dedup_window_hours' => max( 0, (int) ( $raw_s['dedup_window_hours'] ?? 0 ) ),
			'cooldown_minutes'   => max( 0, (int) ( $raw_s['cooldown_minutes']   ?? 0 ) ),
			'custom_vars'        => $custom_vars,
			'var_profile_id'     => sanitize_key( $raw_s['var_profile_id'] ?? '' ),
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
	 * @param string $trigger_name  Event slug, e.g. 'form'.
	 * @param array  $context       Key/value pairs - become {tokens} in action templates.
	 * @param bool   $immediate     true  → run matching actions right now (synchronous).
	 *                              false → queue into ah_trigger_logs for cron (default).
	 *
	 * Call from anywhere:
	 *   AH_Workflow_Manager::evaluate( 'form', [
	 *       'name'  => 'Jane',
	 *       'email' => 'jane@example.com',
	 *   ], true );
	 */
	public static function evaluate( string $trigger_name, array $context, bool $immediate = false ): void {
		global $wpdb;

		// Global freeze: skip everything when the kill-switch is on.
		if ( '1' === ( self::get_config()['global_freeze'] ?? '0' ) ) return;

		// ── Log every evaluate() call before rule check ───────────────────────
		$el = self::evaluate_table();
		$wpdb->insert( $el, array(
			'trigger_name' => $trigger_name,
			'context_data' => wp_json_encode( $context ),
			'rules_found'  => 0,
			'rules_fired'  => 0,
			'created_at'   => current_time( 'mysql' ),
		) );
		$entry_id = (int) $wpdb->insert_id;

		$t  = self::table();
		$lg = self::logs_table();

		if ( isset( $context['_target_rule_id'] ) ) {
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `{$t}` WHERE status = 'active' AND id = %d",
				(int) $context['_target_rule_id']
			) ) ?: array();
		} else {
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `{$t}` WHERE status = 'active' AND trigger_name = %s",
				$trigger_name
			) ) ?: array();
		}

		if ( $wpdb->last_error ) {
			error_log( 'AH_Workflow_Manager::evaluate() DB error: ' . $wpdb->last_error );
		}

		$now         = current_time( 'mysql' );
		$now_ts      = current_time( 'timestamp' );
		$rules_found = count( $rows );
		$rules_fired = 0;

		foreach ( $rows as $rule ) {
			$rule = self::decode( $rule );
			$rule_ctx = $context;

			// Rule-specific freeze: skip saved rules that are currently disabled
			if ( filter_var( $rule->settings['frozen'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
				continue;
			}
			
			// 1. Inject Variable Profile variables first
			$prof_id = $rule->settings['var_profile_id'] ?? '';
			if ( $prof_id ) {
				$profile = self::get_var_profile( $prof_id );
				if ( $profile && ! empty( $profile['vars'] ) ) {
					foreach ( $profile['vars'] as $v ) {
						if ( ! empty( $v['key'] ) ) {
							$rule_ctx[ $v['key'] ] = self::fill( $v['value'] ?? '', $rule_ctx );
						}
					}
				}
			}

			// 2. Inject Rule-specific Custom Variables (these override profile vars)
			foreach ( $rule->settings['custom_vars'] ?? array() as $cv ) {
				if ( ! empty( $cv['key'] ) ) {
					$rule_ctx[ $cv['key'] ] = self::fill( $cv['value'] ?? '', $rule_ctx );
				}
			}

			if ( ! self::conditions_pass( $rule, $rule_ctx ) ) continue;
			if ( ! self::passes_dedup( $rule, $rule_ctx, $lg ) ) continue;
			$rules_fired++;

			$delay_seconds = 0;

			foreach ( $rule->actions as $idx => $action ) {
				$type = $action['type'] ?? '';

				if ( array_key_exists( 'enabled', $action ) && ! filter_var( $action['enabled'], FILTER_VALIDATE_BOOLEAN ) ) {
					continue;
				}

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
					'context_data'  => wp_json_encode( $rule_ctx ),
					'action_index'  => $idx,
					'action_type'   => $type,
					'action_config' => wp_json_encode( $action ),
				);

				// Run synchronously only when immediate mode AND no delay has accumulated
				if ( $immediate && null === $scheduled_at ) {
					$error = null;
					$result = array();
					try {
						$run_results = self::run_actions( array( $action ), $rule_ctx );
						$result = $run_results[0] ?? array();
					} catch ( \Throwable $e ) {
						$error = $e->getMessage();
					}
					$log = array_merge( $log_base, array(
						'status'        => $error ? 'failed' : 'sent',
						'is_done'       => $error ? 0 : 1,
						'attempts'      => 1,
						'error_message' => $error,
						'response_summary' => $error ? null : ( $result['response_summary'] ?? null ),
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
					error_log( 'AH_Workflow_Manager::evaluate() log insert error (rule #' . $rule->id . '): ' . $wpdb->last_error );
				}
			}

			$wpdb->query( $wpdb->prepare(
				"UPDATE `{$t}` SET run_count = run_count + 1, last_run = %s WHERE id = %d",
				$now, (int) $rule->id
			) );
		}

		// ── Update entry with final match counts ──────────────────────────────
		if ( $entry_id ) {
			$wpdb->update( $el, array(
				'rules_found' => $rules_found,
				'rules_fired' => $rules_fired,
			), array( 'id' => $entry_id ) );
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
			$result = array();
			try {
				$run_results = self::run_actions( array( $action ), $context );
				$result = $run_results[0] ?? array();
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
					'status'           => 'sent',
					'is_done'          => 1,
					'sent_at'          => current_time( 'mysql' ),
					'response_summary' => $result['response_summary'] ?? null,
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

	private static function run_actions( array $actions, array $context ): array {
		$cfg     = self::get_config();
		$results = array();
		foreach ( $actions as $a ) {
			$type = $a['type'] ?? '';
			if ( 'send_email' === $type ) {
				// Prefer channel-specific transport: respect channel.email_send_method first,
				// then fall back to inspecting creds (host -> SMTP, api_key/password -> API),
				// finally fall back to global config (default 'api').
				$channel = ! empty( $a['channel_id'] ) ? self::get_email_channel( $a['channel_id'] ) : null;
				if ( $channel ) {
					$method = $channel['email_send_method'] ?? null;
					if ( $method === 'smtp' ) {
						$result = self::action_email_smtp( $a, $context );
					} elseif ( $method === 'api' ) {
						$result = self::action_email( $a, $context );
					} else {
						// Inspect channel fields for a sensible default
						if ( ! empty( $channel['host'] ) ) {
							$result = self::action_email_smtp( $a, $context );
						} elseif ( ! empty( $channel['api_key'] ) || ! empty( $channel['password'] ) ) {
							$result = self::action_email( $a, $context );
						} else {
							// Fallback to global config when channel has no explicit creds
							$result = ( 'smtp' === ( $cfg['email_send_method'] ?? 'api' ) ) ? self::action_email_smtp( $a, $context ) : self::action_email( $a, $context );
						}
					}
				} else {
					$result = ( 'smtp' === ( $cfg['email_send_method'] ?? 'api' ) ) ? self::action_email_smtp( $a, $context ) : self::action_email( $a, $context );
				}
			} else {
				$result = match ( $type ) {
					'whatsapp'      => self::action_whatsapp( $a, $context ),
					'http_request'  => self::action_http( $a, $context ),
					'curl_command'  => self::action_curl_command( $a, $context ),
					'code'          => self::action_code( $a, $context ),
					'update_option' => self::action_update_option( $a, $context ),
					default         => array(),
				};
			}
			$results[] = is_array( $result ) ? $result : array();
		}
		return $results;
	}

	private static function action_result_summary( string $channel_name, int $response_code, ?string $body = null, ?string $message_id = null ): string {
		$parts = array( trim( $channel_name ) );
		if ( $response_code > 0 ) {
			$parts[] = 'HTTP ' . $response_code;
		}
		if ( $message_id ) {
			$parts[] = 'messageId=' . $message_id;
		}
		if ( $body ) {
			$parts[] = self::trim_log_text( $body, 220 );
		}
		return trim( implode( ' | ', array_filter( $parts ) ) );
	}

	private static function trim_log_text( string $text, int $limit = 320 ): string {
		$text = trim( preg_replace( '/\s+/', ' ', $text ) ?? $text );
		return mb_strimwidth( $text, 0, $limit, '…' );
	}

	// ── send_email ────────────────────────────────────────────────────────────

	private static function action_email_smtp( array $a, array $context ): array {
		$ctx = array_merge( self::config_tokens(), $context );
		$cfg = self::get_config();

		// Handle TO as array
		$to_list = is_array( $a['to'] ?? null ) ? $a['to'] : array( $a['to'] ?? '' );
		$to_recipients = array();
		foreach ( $to_list as $to_addr ) {
			$filled = self::fill( (string) $to_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email && ! self::is_email_blocked( $email ) ) $to_recipients[] = $email;
		}
		if ( empty( $to_recipients ) ) {
			throw new \Exception( 'No valid recipient email addresses were provided for this email action.' );
		}
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
		// Direct BCC injection from trigger context
		if ( ! empty( $context['_direct_bcc'] ) ) {
			foreach ( (array) $context['_direct_bcc'] as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $bcc_recipients, true ) ) {
					$bcc_recipients[] = $email;
				}
			}
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
			$smtp_hook = static function ( $mailer ) use ( $ch, $is_html ) {
				$mailer->isSMTP();
				$mailer->Host       = $ch['host'];
				$mailer->Port       = (int) $ch['port'];
				$mailer->SMTPAuth   = ( '' !== (string) $ch['username'] );
				$mailer->Username   = $ch['username'];
				$mailer->Password   = $ch['password'];
				$enc = $ch['encryption'] ?? 'tls';
				$mailer->SMTPSecure = ( 'ssl' === $enc ) ? 'ssl' : ( 'none' === $enc ? false : 'tls' );
				if ( 'none' === $enc ) {
					$mailer->SMTPAutoTLS = false;
				}
				
				// Bypass SSL verification for local dev environments
				$mailer->SMTPOptions = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true
					)
				);
				
				$mailer->isHTML( $is_html );

				$mailer->SMTPDebug   = 2; // 0 = off once fixed
				$mailer->Debugoutput = function ( $str, $level ) {
					error_log( "PHPMailer debug: $str" );
				};

			};
			add_action( 'phpmailer_init', $smtp_hook );
		}


		$_mail_error = '';
		$error_catcher = function ( $wp_error ) use ( &$_mail_error ) {
			if ( is_wp_error( $wp_error ) ) {
				$_mail_error = $wp_error->get_error_message();
			}
		};
		add_action( 'wp_mail_failed', $error_catcher );

		$status = wp_mail( $to, $subject, $body, $headers );

		remove_action( 'wp_mail_failed', $error_catcher );

		if ( $smtp_hook ) {
			remove_action( 'phpmailer_init', $smtp_hook );
		}

		if ( ! $status ) {
			$err_msg = ! empty( $_mail_error ) ? $_mail_error : 'wp_mail returned false (unknown error).';
			if ( $channel ) {
				$err_msg .= "\n\n--- Channel Config Dump ---\n";
				$err_msg .= "Host: " . ( $channel['host'] ?? 'N/A' ) . "\n";
				$err_msg .= "Port: " . ( $channel['port'] ?? 'N/A' ) . "\n";
				$err_msg .= "Encryption: " . ( $channel['encryption'] ?? 'N/A' ) . "\n";
				$err_msg .= "Username: " . ( $channel['username'] ?? 'N/A' ) . "\n";
				$err_msg .= "From: $from_name <$from_email>\n";
			}
			throw new \Exception( $err_msg );
		}

		return array(
			'status'          => 'sent',
			'response_summary' => self::action_result_summary(
				'SMPP/SMTP',
				200,
				$to,
				null
			) . ' | ' . self::trim_log_text( sprintf(
				"Channel: host=%s port=%s enc=%s from=%s",
					$channel['host'] ?? 'N/A',
					$channel['port'] ?? 'N/A',
					$channel['encryption'] ?? 'N/A',
					( isset( $from_name ) && isset( $from_email ) ) ? ( $from_name . ' <' . $from_email . '>' ) : ''
				), 220 ),
		);
	}

	private static function action_email( array $a, array $context ): array {
		$ctx = array_merge( self::config_tokens(), $context );
		$cfg = self::get_config();

		// Handle TO as array
		$to_list = is_array( $a['to'] ?? null ) ? $a['to'] : array( $a['to'] ?? '' );
		$to_recipients = array();
		foreach ( $to_list as $to_addr ) {
			$filled = self::fill( (string) $to_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email && ! self::is_email_blocked( $email ) ) $to_recipients[] = array( 'email' => $email );
		}
		if ( empty( $to_recipients ) ) {
			throw new \Exception( 'No valid recipient email addresses were provided for this email action.' );
		}

		$subject  = self::fill( $a['subject'] ?? 'Notification', $ctx );
		$body_tpl = $a['body'] ?? '';
		$is_html  = ! empty( $a['html'] );
		$body     = $is_html ? self::fill_html( $body_tpl, $ctx ) : self::fill( $body_tpl, $ctx );

		// Resolve channel → global config fallback chain
		$channel = ! empty( $a['channel_id'] ) ? self::get_email_channel( $a['channel_id'] ) : null;

		$api_key = $channel['api_key'] ?? $channel['password'] ?? '';
		if ( ! $api_key ) {
			throw new \Exception( 'No Brevo API key configured for this channel.' );
		}

		$sender_name  = sanitize_text_field( $channel['from_name'] ?? ( $cfg['email_from_name'] ?? '' ) );
		$sender_email = sanitize_email( $channel['from_email'] ?? ( $cfg['email_from_email'] ?? '' ) );
		if ( ! $sender_email ) {
			throw new \Exception( 'No sender email configured for this Brevo channel.' );
		}

		// Handle CC as array
		$cc_list = is_array( $a['cc'] ?? null ) ? $a['cc'] : ( ! empty( $a['cc'] ) ? array( $a['cc'] ) : array() );
		$cc_recipients = array();
		foreach ( $cc_list as $cc_addr ) {
			$email = sanitize_email( self::fill( (string) $cc_addr, $ctx ) );
			if ( $email ) $cc_recipients[] = array( 'email' => $email );
		}

		// Handle BCC - from rule action AND global config
		$bcc_list = is_array( $a['bcc'] ?? null ) ? $a['bcc'] : ( ! empty( $a['bcc'] ) ? array( $a['bcc'] ) : array() );
		$bcc_recipients = array();
		foreach ( $bcc_list as $bcc_addr ) {
			$email = sanitize_email( self::fill( (string) $bcc_addr, $ctx ) );
			if ( $email ) $bcc_recipients[] = array( 'email' => $email );
		}
		// Direct BCC injection from trigger context
		if ( ! empty( $context['_direct_bcc'] ) ) {
			$existing_bcc = array_column( $bcc_recipients, 'email' );
			foreach ( (array) $context['_direct_bcc'] as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $existing_bcc, true ) ) {
					$bcc_recipients[] = array( 'email' => $email );
					$existing_bcc[] = $email;
				}
			}
		}
		if ( ! empty( $cfg['email_bcc'] ) ) {
			$global_bcc = is_array( $cfg['email_bcc'] ) ? $cfg['email_bcc'] : array_filter( array_map( 'trim', explode( ',', $cfg['email_bcc'] ) ) );
			$existing = array_column( $bcc_recipients, 'email' );
			foreach ( $global_bcc as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $existing, true ) ) {
					$bcc_recipients[] = array( 'email' => $email );
					$existing[] = $email;
				}
			}
		}

		$payload = array(
			'sender'  => array( 'name' => $sender_name, 'email' => $sender_email ),
			'to'      => $to_recipients,
			'subject' => $subject,
		);
		$is_html ? $payload['htmlContent'] = $body : $payload['textContent'] = $body;
		if ( ! empty( $cc_recipients ) )  $payload['cc']  = $cc_recipients;
		if ( ! empty( $bcc_recipients ) ) $payload['bcc'] = $bcc_recipients;

		$endpoint = $channel['api_endpoint'] ?? 'https://api.brevo.com/v3/smtp/email';
		$response = wp_remote_post( $endpoint, array(
			'timeout' => 15,
			'headers' => array(
				'api-key'      => $api_key,
				'Content-Type' => 'application/json',
				'accept'       => 'application/json',
			),
			'body' => wp_json_encode( $payload ),
		) );

		$curl_str = "curl -X POST " . $endpoint . " \\\n+";
		$curl_str .= "-H \"api-key: " . ( $api_key ? substr($api_key, 0, 8) . '...' : 'MISSING' ) . "\" \\\n";
		$curl_str .= "-H \"Content-Type: application/json\" \\\n";
		$curl_str .= "-d '" . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES ) . "'";
		
		$dump = "\n\n--- API Config Dump ---\n";
		$dump .= "Endpoint: " . $endpoint . "\n";
		$dump .= "cURL Equivalent:\n" . $curl_str;

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() . $dump );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$resp_body = wp_remote_retrieve_body( $response );
		if ( $code < 200 || $code >= 300 ) {
			$err = json_decode( $resp_body, true );
			$msg = $err['message'] ?? $err['error'] ?? ( $resp_body ?: "Brevo API returned HTTP {$code}." );
			throw new \Exception( $msg . $dump );
		}

		$decoded = json_decode( $resp_body, true );
		$message_id = '';
		if ( is_array( $decoded ) ) {
			$message_id = (string) ( $decoded['messageId'] ?? $decoded['message_id'] ?? $decoded['id'] ?? '' );
		}

		$response_summary = self::action_result_summary(
			'Brevo API',
			$code,
			is_array( $decoded ) ? wp_json_encode( $decoded, JSON_UNESCAPED_SLASHES ) : $resp_body,
			$message_id ?: null
		);

		// Append a trimmed cURL-equivalent / endpoint dump so successful sends also include diagnostics
		$response_summary .= ' | ' . self::trim_log_text( $curl_str, 220 );

		error_log( 'AH_Workflow_Manager email success: ' . $response_summary );

		return array(
			'status'           => 'sent',
			'response_summary' => $response_summary,
		);
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

		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => 15,
		) );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'WhatsApp API connection failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			$body_resp = wp_remote_retrieve_body( $response );
			throw new \Exception( sprintf( 'WhatsApp API returned HTTP %d: %s', $code, mb_strimwidth( $body_resp, 0, 150, '...' ) ) );
		}
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

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'HTTP request connection failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			$body_resp = wp_remote_retrieve_body( $response );
			throw new \Exception( sprintf( 'HTTP request returned HTTP %d: %s', $code, mb_strimwidth( $body_resp, 0, 150, '...' ) ) );
		}
	}

	// ── curl_command ──────────────────────────────────────────────────────────

private static function action_curl_command( array $a, array $context ): array {
    $ctx = array_merge( self::config_tokens(), $context );
    $curl_str = self::fill( $a['curl_string'] ?? '', $ctx );
    if ( ! $curl_str ) {
        return array(
            'status' => 'skipped',
            'response_summary' => 'No cURL command provided.',
        );
    }

    $status_file = self::fill( $a['status_file'] ?? '', $ctx );
    if ( ! $status_file ) {
        $status_file = self::get_action_status_file_path( 'curl' );
    }

    if ( ! str_starts_with( trim( $curl_str ), 'curl ' ) ) {
        $message = 'Invalid cURL command: must start with "curl ".';
        self::write_action_status_file( $status_file, 'failure', array( 'message' => $message ) );
        throw new \Exception( $message );
    }

    $curl_request = self::parse_curl_command_to_request_options( $curl_str );
    if ( $curl_request === null ) {
        $message = 'Unable to parse cURL command into HTTP request options.';
        self::write_action_status_file( $status_file, 'failure', array( 'message' => $message ) );
        throw new \Exception( $message );
    }

    try {
        $shell_available = self::is_shell_command_available() && strtoupper( substr( PHP_OS, 0, 3 ) ) !== 'WIN';
        if ( $shell_available ) {
            $curl_str = self::prepare_windows_curl_command( $curl_str );

            error_log( 'AH_Workflow_Manager::action_curl_command execute shell: ' . $curl_str );
            $output = array();
            $result = -1;
            self::run_command_with_output( $curl_str, $output, $result );

            if ( $result === 0 ) {
                $output_text = implode( ' ', array_filter( $output ) );
                self::write_action_status_file( $status_file, 'success', array( 'path' => $status_file, 'method' => 'shell' ) );
                return array(
                    'status' => 'sent',
                    'response_summary' => self::trim_log_text(
                        'cURL command executed successfully via shell.' . ( $output_text ? ' | ' . $output_text : '' ),
                        220
                    ),
                );
            }

            error_log( 'AH_Workflow_Manager::action_curl_command shell failed: ' . $curl_str . ' | output: ' . implode( " \n", $output ) );
        } else {
            error_log( 'AH_Workflow_Manager::action_curl_command skipping shell execution on Windows or disabled shell functions' );
        }

        error_log( 'AH_Workflow_Manager::action_curl_command fallback to WP_HTTP' );
        $http_result = self::run_wp_remote_for_curl_request( $curl_request );
        self::write_action_status_file( $status_file, 'success', array( 'path' => $status_file, 'method' => 'wp_remote_request' ) );

        return array(
            'status' => 'sent',
            'response_summary' => self::trim_log_text(
                self::action_result_summary(
                    'cURL',
                    $http_result['code'] ?? 0,
                    $http_result['body'] ?? null,
                    null
                ) . ' | ' . sprintf(
                    '%s %s',
                    $curl_request['args']['method'] ?? 'GET',
                    $curl_request['url'] ?? ''
                ),
                240
            ),
        );
    } catch ( \Throwable $e ) {
        self::write_action_status_file( $status_file, 'failure', array( 'message' => $e->getMessage() ) );
        throw $e;
    }
}

private static function run_command_with_output( string $cmd, array &$output, int &$exitCode ): void {
    $output = array();
    $exitCode = -1;
    $disabled = strtolower( ini_get( 'disable_functions' ) );

    if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
        if ( function_exists( 'proc_open' ) && strpos( $disabled, 'proc_open' ) === false ) {
            $args = self::parse_command_string( $cmd );
            $descriptors = array(
                1 => array( 'pipe', 'w' ),
                2 => array( 'pipe', 'w' ),
            );
            $process = proc_open( $args, $descriptors, $pipes );
            if ( is_resource( $process ) ) {
                $stdout = stream_get_contents( $pipes[1] );
                $stderr = stream_get_contents( $pipes[2] );
                fclose( $pipes[1] );
                fclose( $pipes[2] );
                $exitCode = proc_close( $process );
                $output = array_filter( array_merge( explode( "\n", $stdout ), explode( "\n", $stderr ) ) );
                return;
            }
        }

        if ( function_exists( 'exec' ) && strpos( $disabled, 'exec' ) === false ) {
            exec( $cmd . ' 2>&1', $output, $exitCode );
            return;
        }

        return;
    }

    exec( $cmd . ' 2>&1', $output, $exitCode );
}

private static function is_shell_command_available(): bool {
    $disabled = strtolower( ini_get( 'disable_functions' ) );
    return ( function_exists( 'proc_open' ) && strpos( $disabled, 'proc_open' ) === false ) || ( function_exists( 'exec' ) && strpos( $disabled, 'exec' ) === false );
}

private static function run_wp_remote_for_curl_request( array $request ): array {
    $url = $request['url'] ?? '';
    $args = $request['args'] ?? array();

    $response = wp_remote_request( $url, $args );
    if ( is_wp_error( $response ) ) {
        throw new \Exception( 'WP HTTP request failed: ' . $response->get_error_message() );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    if ( $code < 200 || $code >= 300 ) {
        throw new \Exception( sprintf( 'WP HTTP request returned HTTP %d: %s', $code, mb_strimwidth( $body, 0, 150, '...' ) ) );
    }

    return array(
        'code' => $code,
        'body' => $body,
    );
}

private static function write_action_status_file( string $path, string $status, array $data = array() ): void {
    if ( ! $path ) {
        return;
    }

    $payload = array_merge(
        array(
            'status'    => $status,
            'timestamp' => current_time( 'mysql' ),
        ),
        $data
    );

    $dir = dirname( $path );
    if ( ! is_dir( $dir ) ) {
        @mkdir( $dir, 0755, true );
    }

    @file_put_contents( $path, wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
}

private static function get_action_status_file_path( string $prefix ): string {
    $path = wp_tempnam( "ah-{$prefix}-status-" );
    if ( ! $path ) {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "ah-{$prefix}-status-" . uniqid() . '.json';
    }
    return $path;
}

private static function parse_curl_command_to_request_options( string $curl_str ): ?array {
    $opts = array(
        'url'     => '',
        'headers' => array(),
        'method'  => 'GET',
        'body'    => null,
    );

    $tokens = self::parse_command_string( $curl_str );
    $current = null;
    $use_body = false;

    foreach ( $tokens as $token ) {
        if ( $current ) {
            switch ( $current ) {
                case '-X':
                case '--request':
                    $opts['method'] = strtoupper( $token );
                    break;
                case '--url':
                    $opts['url'] = $token;
                    break;
                case '-H':
                case '--header':
                    if ( str_contains( $token, ':' ) ) {
                        [ $name, $value ] = explode( ':', $token, 2 );
                        $opts['headers'][ trim( $name ) ] = trim( $value );
                    }
                    break;
                case '-d':
                case '--data':
                case '--data-raw':
                case '--data-binary':
                    $opts['body'] = $token;
                    $use_body = true;
                    break;
            }
            $current = null;
            continue;
        }

        if ( str_starts_with( $token, 'curl' ) ) {
            continue;
        }

        if ( str_starts_with( $token, 'http://' ) || str_starts_with( $token, 'https://' ) ) {
            $opts['url'] = $token;
            continue;
        }

        if ( in_array( $token, array( '-X', '--request', '--url', '-H', '--header', '-d', '--data', '--data-raw', '--data-binary' ), true ) ) {
            $current = $token;
            if ( in_array( $token, array( '-d', '--data', '--data-raw', '--data-binary' ), true ) ) {
                $opts['method'] = 'POST';
            }
            continue;
        }

        if ( str_starts_with( $token, '--url=' ) ) {
            $opts['url'] = substr( $token, 6 );
            continue;
        }

        if ( str_starts_with( $token, '-H' ) && strlen( $token ) > 2 ) {
            $hdr = substr( $token, 2 );
            if ( str_contains( $hdr, ':' ) ) {
                [ $name, $value ] = explode( ':', $hdr, 2 );
                $opts['headers'][ trim( $name ) ] = trim( $value );
            }
            continue;
        }

        if ( str_starts_with( $token, '-d' ) && strlen( $token ) > 2 ) {
            $opts['body'] = substr( $token, 2 );
            $use_body = true;
            $opts['method'] = 'POST';
            continue;
        }
    }

    if ( ! $opts['url'] ) {
        return null;
    }

    $args = array(
        'method'  => $opts['method'],
        'headers' => $opts['headers'],
        'timeout' => 15,
    );

    if ( $use_body ) {
        $args['body'] = $opts['body'];
    }

    return array(
        'url'  => $opts['url'],
        'args' => $args,
    );
}

private static function parse_command_string( string $cmd ): array {
    $args = array();
    $length = strlen( $cmd );
    $index = 0;

    while ( $index < $length ) {
        while ( $index < $length && ctype_space( $cmd[ $index ] ) ) {
            $index++;
        }

        if ( $index >= $length ) {
            break;
        }

        $char = $cmd[ $index ];
        $token = '';

        if ( $char === '"' || $char === "'" ) {
            $quote = $char;
            $index++;
            while ( $index < $length ) {
                $c = $cmd[ $index++ ];
                if ( $c === $quote ) {
                    break;
                }
                if ( $c === '\\' && $index < $length ) {
                    $next = $cmd[ $index++ ];
                    if ( $next === "\r" || $next === "\n" ) {
                        while ( $index < $length && ctype_space( $cmd[ $index ] ) ) {
                            $index++;
                        }
                        continue;
                    }
                    $token .= $next;
                    continue;
                }
                $token .= $c;
            }
        } else {
            while ( $index < $length && ! ctype_space( $cmd[ $index ] ) ) {
                $c = $cmd[ $index++ ];
                if ( $c === '\\' && $index < $length ) {
                    $next = $cmd[ $index++ ];
                    if ( $next === "\r" || $next === "\n" ) {
                        while ( $index < $length && ctype_space( $cmd[ $index ] ) ) {
                            $index++;
                        }
                        continue;
                    }
                    $token .= $next;
                    continue;
                }
                $token .= $c;
            }
        }

        if ( $token !== '' ) {
            $args[] = $token;
        }
    }

    return $args;
}

private static function prepare_windows_curl_command( string $curl_str ): string {
    $curl_str = str_replace( array( "\r", "\n" ), ' ', $curl_str );

    // Remove escaped quote sequences produced by pasted PHP-style strings.
    $curl_str = str_replace( array( '\\"', "\\'" ), array( '"', "'" ), $curl_str );

    // Normalize header quoting for cmd.exe.
    $curl_str = preg_replace_callback(
        '/\b(-H|--header)\s+(?:\'([^\']*)\'|"([^"]*)")/i',
        function ( $matches ) {
            $value = isset( $matches[2] ) && $matches[2] !== '' ? $matches[2] : $matches[3];
            $value = str_replace( '"', '\\"', $value );
            return $matches[1] . ' "' . $value . '"';
        },
        $curl_str
    );

    // Convert raw POST data to a temporary file to avoid Windows shell JSON quoting issues.
    $curl_str = preg_replace_callback(
        '/\b(-d|--data|--data-raw|--data-binary)\s+(?:\'([^\']*)\'|"([^"]*)")/i',
        function ( $matches ) {
            $data = isset( $matches[2] ) && $matches[2] !== '' ? $matches[2] : $matches[3];
            $data = str_replace( '\\"', '"', $data );
            $tmp_file = wp_tempnam( 'ah-curl-body-' );
            if ( ! $tmp_file ) {
                $tmp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ah-curl-body-' . uniqid() . '.txt';
            }
            file_put_contents( $tmp_file, $data );
            if ( str_contains( $tmp_file, ' ' ) ) {
                return $matches[1] . ' @"' . str_replace( '"', '\\"', $tmp_file ) . '"';
            }
            return $matches[1] . ' @' . $tmp_file;
        },
        $curl_str
    );

    return $curl_str;
}

private static function action_code( array $a, array $context ): void {
    $ctx = array_merge( self::config_tokens(), $context );
    $code = trim( self::fill( $a['code'] ?? '', $ctx ) );
    if ( ! $code ) {
        return;
    }

    // $disallowed = '/\b(?:assert|shell_exec|system|passthru|popen|proc_open|pcntl_exec|new(?!\s+\\?Exception\b)|fopen|fwrite|fread|file_put_contents|file_get_contents|unlink|mkdir|rmdir|rename|copy|chmod|chown|opendir|readdir|glob|scandir|putenv|getenv|parse_ini_file|parse_ini_string|extract|include|require|include_once|require_once|->|::|\$GLOBALS|\$_(?:GET|POST|REQUEST|SERVER|SESSION|COOKIE|ENV)|wpdb|mysql|mysqli|pdo|sqlite|update_option|add_option|delete_option)(?![a-zA-Z0-9_])/i';
    // if ( preg_match( $disallowed, $code ) ) {
    //     throw new \Exception( 'CODE contains disallowed operations or functions' );
    // }

    try {
        eval( $code );
    } catch ( \Throwable $e ) {
        throw new \Exception( 'CODE execution failed: ' . $e->getMessage() );
    }
}

/**
 * Makes a raw token value safe to drop into a JSON string that is
 * itself wrapped in single quotes as a shell argument (-d '...').
 */
private static function escape_for_curl_template( $value ): string {
    if ( is_array( $value ) ) {
        // Don't attempt to stringify structured values here — leave as-is
        // and let the caller build them explicitly if ever needed.
        return $value;
    }

    $value = (string) $value;

    // 1) JSON-escape: turns " \ and control chars into \" \\ \n etc.
    $jsonEscaped = substr( json_encode( $value ), 1, -1 );

    // 2) Shell-escape for the enclosing single-quoted '...' argument.
    //    Inside single quotes the only special char is the quote itself.
    //    Standard trick: close quote, insert escaped literal quote, reopen.
    return str_replace( "'", "'\\''", $jsonEscaped );
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
			$val = (string) $v;
			$tpl = str_replace( '{{' . $k . '}}', $val, $tpl );
			$tpl = str_replace( '((' . $k . '))', $val, $tpl );
		}
		// Basic support for evaluating math/logic expressions inside {{eval: ...}}
		$tpl = preg_replace_callback('/\{\{eval:(.*?)\}\}/is', function( $m ) {
			$expr = trim( $m[1] );
			// Only allow safe characters: digits, math operators, parentheses, space, and comparison ops
			if ( preg_match( '/^[0-9\+\-\*\/\.\(\)\s\>\<\=\!]+$/', $expr ) ) {
				try {
					$result = eval( 'return ' . $expr . ';' );
					if ( is_bool( $result ) ) {
						return $result ? '1' : '0';
					}
					return (string) $result;
				} catch ( \Throwable $e ) { }
			}
			return $m[0]; // If eval fails or is unsafe, return as-is
		}, $tpl);
		return $tpl;
	}

	/**
	 * Replace token - values are HTML-escaped (safe for HTML email).
	 */
	public static function fill_html( string $tpl, array $ctx ): string {
		foreach ( $ctx as $k => $v ) {
			$val = esc_html( (string) $v );
			$tpl = str_replace( '{{' . $k . '}}', $val, $tpl );
			$tpl = str_replace( '((' . $k . '))', $val, $tpl );
		}
		// Evaluate safe expressions inside {{eval: ...}} for HTML context too
		$tpl = preg_replace_callback('/\{\{eval:(.*?)\}\}/is', function( $m ) {
			$expr = trim( $m[1] );
			if ( preg_match( '/^[0-9\+\-\*\/\.\(\)\s\>\<\=\!]+$/', $expr ) ) {
				try {
					$result = eval( 'return ' . $expr . ';' );
					if ( is_bool( $result ) ) {
						return $result ? '1' : '0';
					}
					return esc_html( (string) $result );
				} catch ( \Throwable $e ) { }
			}
			return $m[0];
		}, $tpl);
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
				'enabled'    => filter_var( $a['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
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
				'enabled'      => filter_var( $a['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
				'option_key'   => sanitize_key( $a['option_key']   ?? '' ),
				'option_value' => sanitize_text_field( $a['option_value'] ?? '' ),
			),
			'curl_command' => array(
				'type'        => 'curl_command',
				'enabled'     => filter_var( $a['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
				'curl_string' => trim( $a['curl_string'] ?? '' ), // Do not sanitize heavily since cURL needs special chars
			),
			'code' => array(
				'type'    => 'code',
				'enabled' => filter_var( $a['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
				'code'    => trim( $a['code'] ?? '' ),
			),
			'whatsapp' => array(
				'type'       => 'whatsapp',
				'enabled'    => filter_var( $a['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
				'api_url'    => esc_url_raw(         $a['api_url']    ?? '' ),
				'auth_token' => sanitize_text_field( $a['auth_token'] ?? '' ),
				'to_phone'   => sanitize_text_field( $a['to_phone']   ?? '' ),
				'message'    => sanitize_textarea_field( $a['message'] ?? '' ),
				'body_json'  => wp_kses_post(        $a['body_json']  ?? '' ),
			),
			'http_request' => array(
				'type'         => 'http_request',
				'enabled'      => filter_var( $a['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
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
			'sample1' => 'Sample 1',
			'sample2' => 'Sample 2',
			'custom'  => 'Custom (define your own)',
		);
	}

	// ── Global config ─────────────────────────────────────────────────────────

	public static function get_config(): array {
  		$saved = get_option( 'ah_re_config', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return array_merge( array(
			'global_freeze'      => '0',
			'retry_max_attempts' => '3',
			'cron_enabled'       => '1',
		), (array) $saved );
	}

	public static function save_config( array $data ): void {
		$clean = array(
			'global_freeze'      => ! empty( $data['global_freeze'] ) ? '1' : '0',
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

	// ── Variable Profiles ─────────────────────────────────────────────────────

	public static function get_var_profiles(): array {
		$saved = get_option( 'ah_re_var_profiles', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return is_array( $saved ) ? $saved : array();
	}

	public static function get_var_profile( string $id ): ?array {
		foreach ( self::get_var_profiles() as $prof ) {
			if ( ( $prof['id'] ?? '' ) === $id ) return $prof;
		}
		return null;
	}

	public static function save_var_profiles( array $profiles ): void {
		$clean = array();
		foreach ( $profiles as $prof ) {
			$id = sanitize_key( $prof['id'] ?? '' );
			if ( ! $id ) continue;
			$vars_clean = array();
			foreach ( $prof['vars'] ?? array() as $v ) {
				$key = sanitize_key( $v['key'] ?? '' );
				if ( ! $key ) continue;
				$vars_clean[] = array(
					'key'   => $key,
					'value' => sanitize_text_field( $v['value'] ?? '' ),
				);
			}
			$clean[] = array(
				'id'   => $id,
				'name' => sanitize_text_field( $prof['name'] ?? '' ),
				'vars' => $vars_clean,
			);
		}
		update_option( 'ah_re_var_profiles', $clean );
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
			$method = in_array( $ch['email_send_method'] ?? 'api', array( 'api', 'smtp' ), true )
				? $ch['email_send_method'] : 'api';
			$clean[] = array(
				'id'         => $id,
				'name'       => sanitize_text_field( $ch['name']       ?? '' ),
				'from_name'  => sanitize_text_field( $ch['from_name']  ?? '' ),
				'from_email' => sanitize_email(      $ch['from_email'] ?? '' ),
				'provider'   => sanitize_key(        $ch['provider']   ?? 'custom' ),
				'email_send_method' => $method,
				'host'       => sanitize_text_field( $ch['host']       ?? '' ),
				'port'       => (int) ( $ch['port'] ?? 587 ),
				'username'   => sanitize_text_field( $ch['username']   ?? '' ),
				'password'   => (string) ( $ch['password'] ?? '' ),
				// API-specific fields (optional)
				'api_endpoint' => sanitize_text_field( $ch['api_endpoint'] ?? '' ),
				'api_key'      => (string) ( $ch['api_key'] ?? $ch['password'] ?? '' ),
				'encryption' => $enc,
			);
		}
		update_option( 'ah_re_email_channels', $clean );
	}

	// ── Blocked emails ────────────────────────────────────────────────────────

	public static function get_blocked_emails(): array {
		$saved = get_option( 'ah_re_blocked_emails', array() );
		return is_array( $saved ) ? $saved : array();
	}

	public static function is_email_blocked( string $email ): bool {
		$email = strtolower( trim( $email ) );
		if ( '' === $email ) return false;
		foreach ( self::get_blocked_emails() as $blocked ) {
			if ( strtolower( trim( $blocked ) ) === $email ) return true;
		}
		return false;
	}

	public static function add_blocked_email( string $email ): void {
		$email = strtolower( sanitize_email( $email ) );
		if ( ! is_email( $email ) ) return;
		$list = self::get_blocked_emails();
		foreach ( $list as $existing ) {
			if ( strtolower( trim( $existing ) ) === $email ) return;
		}
		$list[] = $email;
		update_option( 'ah_re_blocked_emails', array_values( $list ) );
	}

	public static function remove_blocked_email( string $email ): void {
		$email = strtolower( trim( $email ) );
		$list  = array_filter( self::get_blocked_emails(), static function ( $e ) use ( $email ) {
			return strtolower( trim( $e ) ) !== $email;
		} );
		update_option( 'ah_re_blocked_emails', array_values( $list ) );
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
			$result  = array();
			try {
				$run_results = self::run_actions( array( $action ), $context );
				$result = $run_results[0] ?? array();
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
					'status'           => 'sent',
					'is_done'          => 1,
					'sent_at'          => current_time( 'mysql' ),
					'response_summary' => $result['response_summary'] ?? null,
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

	public static function clear_logs(): void {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE `" . self::logs_table() . "`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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
		$result = array();
		try {
			$run_results = self::run_actions( array( $action ), $context );
			$result = $run_results[0] ?? array();
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
			'status'           => 'sent',
			'is_done'          => 1,
			'sent_at'          => current_time( 'mysql' ),
			'response_summary' => $result['response_summary'] ?? null,
		), array( 'id' => $id ) );
		return true;
	}
	public static function register_rest_routes() {
		register_rest_route( 'ah-workflow/v1', '/trigger', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( self::class, 'handle_external_trigger' ),
			'permission_callback' => array( self::class, 'verify_external_trigger' ),
		) );

		register_rest_route( 'ah-workflow/v1', '/test-channel', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( self::class, 'handle_test_channel' ),
			'permission_callback' => function() { return current_user_can( 'manage_options' ); },
		) );

		register_rest_route( 'ah-workflow/v1', '/test-rule', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( self::class, 'handle_test_rule' ),
			'permission_callback' => function() { return current_user_can( 'manage_options' ); },
		) );
	}

	public static function verify_external_trigger( WP_REST_Request $request ) {
		if ( defined( 'AH_WORKFLOW_API_KEY' ) && AH_WORKFLOW_API_KEY ) {
			$header = $request->get_header( 'x_ah_workflow_key' );
			if ( $header !== AH_WORKFLOW_API_KEY ) {
				return new WP_Error( 'unauthorized', 'Invalid API Key', array( 'status' => 401 ) );
			}
		}
		return true;
	}

	public static function handle_external_trigger( WP_REST_Request $request ) {
		$trigger_name = sanitize_text_field( $request->get_param( 'trigger_name' ) ?? '' );
		$context      = (array) ( $request->get_param( 'context' ) ?? array() );

		if ( ! $trigger_name ) {
			return new WP_Error( 'missing_trigger', 'Missing trigger_name parameter', array( 'status' => 400 ) );
		}

		self::evaluate( $trigger_name, $context, true ); // Run immediately for webhooks
		return new WP_REST_Response( array( 'success' => true, 'message' => "Evaluated trigger: {$trigger_name}" ), 200 );
	}

	public static function handle_test_channel( WP_REST_Request $request ) {
		$channel_id = sanitize_key( $request->get_param( 'channel_id' ) ?? '' );
		$test_email = sanitize_email( $request->get_param( 'test_email' ) ?? '' );

		if ( ! $channel_id || ! $test_email ) {
			return new WP_Error( 'missing_params', 'Missing channel_id or test_email', array( 'status' => 400 ) );
		}

		$channel = self::get_email_channel( $channel_id );
		if ( ! $channel ) {
			return new WP_Error( 'not_found', 'Channel not found', array( 'status' => 404 ) );
		}

		$dummy_action = array(
			'type'       => 'send_email',
			'channel_id' => $channel_id,
			'to'         => $test_email,
			'subject'    => 'AH Workflow: Test Connection',
			'body'       => 'If you are receiving this, your AH Workflow Email Channel configuration is working perfectly!',
			'html'       => 0,
		);

		try {
			$method = $channel['email_send_method'] ?? 'api';
			if ( 'smtp' === $method ) {
				self::action_email_smtp( $dummy_action, array() );
			} else {
				self::action_email( $dummy_action, array() );
			}
			return new WP_REST_Response( array( 'success' => true, 'message' => 'Test email sent successfully!' ), 200 );
		} catch ( \Throwable $e ) {
			return new WP_Error( 'send_failed', 'Test failed: ' . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	public static function handle_test_rule( WP_REST_Request $request ) {
		global $wpdb;
		$rule_id = (int) $request->get_param( 'rule_id' );
		$context = $request->get_param( 'context' );
		
		if ( is_string( $context ) ) {
			$context = json_decode( $context, true ) ?: array();
		}
		$context = (array) $context;

		if ( ! $rule_id ) {
			return new WP_Error( 'missing_params', 'Missing rule_id parameter', array( 'status' => 400 ) );
		}

		$rule = self::get( $rule_id );
		if ( ! $rule ) {
			return new WP_Error( 'not_found', 'Rule not found', array( 'status' => 404 ) );
		}

		// 1. Setup Variables
		$rule_ctx = $context;
		$prof_id = $rule->settings['var_profile_id'] ?? '';
		if ( $prof_id ) {
			$profile = self::get_var_profile( $prof_id );
			if ( $profile && ! empty( $profile['vars'] ) ) {
				foreach ( $profile['vars'] as $v ) {
					if ( ! empty( $v['key'] ) ) $rule_ctx[ $v['key'] ] = self::fill( $v['value'] ?? '', $rule_ctx );
				}
			}
		}
		foreach ( $rule->settings['custom_vars'] ?? array() as $cv ) {
			if ( ! empty( $cv['key'] ) ) $rule_ctx[ $cv['key'] ] = self::fill( $cv['value'] ?? '', $rule_ctx );
		}

		$lg = self::logs_table();
		$now = current_time( 'mysql' );
		$now_ts = current_time( 'timestamp' );
		$delay_seconds = 0;
		$overall_error = null;

		foreach ( $rule->actions as $idx => $action ) {
			$type = $action['type'] ?? '';

			if ( 'wait' === $type ) {
				$mults = array( 'minutes' => 60, 'hours' => 3600, 'days' => 86400 );
				$delay_seconds += max( 0, (int) ( $action['duration'] ?? 0 ) ) * ( $mults[ $action['unit'] ?? 'minutes' ] ?? 60 );
				continue;
			}

			$scheduled_at = $delay_seconds > 0 ? gmdate( 'Y-m-d H:i:s', $now_ts + $delay_seconds ) : null;

			$log_base = array(
				'rule_id'       => (int) $rule->id,
				'trigger_name'  => $rule->trigger_name . ' (Manual Test)',
				'context_data'  => wp_json_encode( $rule_ctx ),
				'action_index'  => $idx,
				'action_type'   => $type,
				'action_config' => wp_json_encode( $action ),
			);

			if ( null === $scheduled_at ) {
				$error = null;
				$result = array();
				try {
					$run_results = self::run_actions( array( $action ), $rule_ctx );
					$result = $run_results[0] ?? array();
				} catch ( \Throwable $e ) {
					$error = $e->getMessage();
					$overall_error = $overall_error ?: $error;
				}
				$log = array_merge( $log_base, array(
					'status'        => $error ? 'failed' : 'sent',
					'is_done'       => $error ? 0 : 1,
					'attempts'      => 1,
					'error_message' => $error,
					'response_summary' => $error ? null : ( $result['response_summary'] ?? null ),
				) );
				$log[ $error ? 'failed_at' : 'sent_at' ] = current_time( 'mysql' );
			} else {
				$log = array_merge( $log_base, array(
					'status'       => 'pending',
					'is_done'      => 0,
					'attempts'     => 0,
					'scheduled_at' => $scheduled_at,
				) );
			}

			$wpdb->insert( $lg, $log );
		}

		$wpdb->query( $wpdb->prepare(
			"UPDATE `" . self::table() . "` SET run_count = run_count + 1, last_run = %s WHERE id = %d",
			$now, (int) $rule->id
		) );

		if ( $overall_error ) {
			return new WP_Error( 'send_failed', 'Rule test completed with errors: ' . $overall_error, array( 'status' => 500 ) );
		}

		return new WP_REST_Response( array( 'success' => true, 'message' => 'Rule actions executed and logged successfully!' ), 200 );
	}
}

add_action( 'rest_api_init', array( 'AH_Workflow_Manager', 'register_rest_routes' ) );

