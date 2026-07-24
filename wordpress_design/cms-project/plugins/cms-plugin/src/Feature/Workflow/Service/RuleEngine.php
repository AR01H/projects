<?php

namespace Ah\Cms\Feature\Workflow\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Rule Engine — handles CRUD, schema, config, meta helpers, email channels,
 * blocked emails, variable profiles, custom vars, and log management for workflow rules.
 */
class RuleEngine {

	// ── Schema ───────────────────────────────────────────────────────────────

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_rules';
	}

	public static function logsTable(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_trigger_logs';
	}

	public static function evaluateTable(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_evaluate_log';
	}

	public static function installTables(): void {
		// Delegated to AH_DB_Installer when available.
	}

	// ── CRUD ─────────────────────────────────────────────────────────────────

	public static function getAll(): array {
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
						'operator' => self::validOperator( $g['operator'] ?? 'equals' ),
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
						'operator' => self::validOperator( $c['operator'] ?? 'equals' ),
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
			$sanitized = self::sanitizeAction( $a );
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

	public static function decode( object $row ): object {
		$row->conditions = $row->conditions ? json_decode( $row->conditions, true ) : array();
		$row->actions    = $row->actions    ? json_decode( $row->actions, true )    : array();
		$row->settings   = ! empty( $row->settings ) ? json_decode( $row->settings, true ) : array();
		return $row;
	}

	// ── Sanitize actions on save ──────────────────────────────────────────────

	public static function sanitizeEmailList( $input ): array {
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

	public static function sanitizeAction( array $a ): ?array {
		$type = sanitize_key( $a['type'] ?? '' );
		return match ( $type ) {
			'send_email' => array(
				'type'       => 'send_email',
				'enabled'    => filter_var( $a['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0,
				'to'         => self::sanitizeEmailList( $a['to']         ?? array() ),
				'subject'    => sanitize_text_field( $a['subject']    ?? '' ),
				'body'       => wp_kses_post(        $a['body']       ?? '' ),
				'html'       => ! empty( $a['html'] ) ? 1 : 0,
				'channel_id' => sanitize_key(        $a['channel_id'] ?? '' ),
				'cc'         => self::sanitizeEmailList( $a['cc']    ?? array() ),
				'bcc'        => self::sanitizeEmailList( $a['bcc']   ?? array() ),
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
				'curl_string' => trim( $a['curl_string'] ?? '' ),
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

	public static function validOperator( string $op ): string {
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

	public static function triggerPresets(): array {
		return array(
			'sample1' => 'Sample 1',
			'sample2' => 'Sample 2',
			'custom'  => 'Custom (define your own)',
		);
	}

	// ── Global config ─────────────────────────────────────────────────────────

	public static function getConfig(): array {
		$saved = get_option( 'ah_re_config', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return array_merge( array(
			'global_freeze'      => '0',
			'retry_max_attempts' => '3',
			'cron_enabled'       => '1',
		), (array) $saved );
	}

	public static function saveConfig( array $data ): void {
		$clean = array(
			'global_freeze'      => ! empty( $data['global_freeze'] ) ? '1' : '0',
			'retry_max_attempts' => (string) max( 1, min( 10, (int) ( $data['retry_max_attempts'] ?? 3 ) ) ),
			'cron_enabled'       => ! empty( $data['cron_enabled'] ) ? '1' : '0',
		);
		update_option( 'ah_re_config', $clean );
	}

	// ── Custom config variables ───────────────────────────────────────────────

	public static function getCustomVars(): array {
		$saved = get_option( 'ah_re_custom_vars', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return is_array( $saved ) ? $saved : array();
	}

	public static function saveCustomVars( array $vars ): void {
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

	public static function getVarProfiles(): array {
		$saved = get_option( 'ah_re_var_profiles', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return is_array( $saved ) ? $saved : array();
	}

	public static function getVarProfile( string $id ): ?array {
		foreach ( self::getVarProfiles() as $prof ) {
			if ( ( $prof['id'] ?? '' ) === $id ) return $prof;
		}
		return null;
	}

	public static function saveVarProfiles( array $profiles ): void {
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

	public static function getEmailChannels(): array {
		$saved = get_option( 'ah_re_email_channels', array() );
		if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: array();
		return is_array( $saved ) ? $saved : array();
	}

	public static function getEmailChannelsList(): array {
		$list = array( '' => '- Default (site mail) -' );
		foreach ( self::getEmailChannels() as $ch ) {
			if ( ! empty( $ch['id'] ) ) {
				$list[ $ch['id'] ] = $ch['name'] ?: $ch['id'];
			}
		}
		return $list;
	}

	public static function getEmailChannel( string $id ): ?array {
		foreach ( self::getEmailChannels() as $ch ) {
			if ( ( $ch['id'] ?? '' ) === $id ) return $ch;
		}
		return null;
	}

	public static function saveEmailChannels( array $channels ): void {
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
				'api_endpoint' => sanitize_text_field( $ch['api_endpoint'] ?? '' ),
				'api_key'      => (string) ( $ch['api_key'] ?? $ch['password'] ?? '' ),
				'encryption' => $enc,
			);
		}
		update_option( 'ah_re_email_channels', $clean );
	}

	// ── Blocked emails ────────────────────────────────────────────────────────

	public static function getBlockedEmails(): array {
		$saved = get_option( 'ah_re_blocked_emails', array() );
		return is_array( $saved ) ? $saved : array();
	}

	public static function isEmailBlocked( string $email ): bool {
		$email = strtolower( trim( $email ) );
		if ( '' === $email ) return false;
		foreach ( self::getBlockedEmails() as $blocked ) {
			if ( strtolower( trim( $blocked ) ) === $email ) return true;
		}
		return false;
	}

	public static function addBlockedEmail( string $email ): void {
		$email = strtolower( sanitize_email( $email ) );
		if ( ! is_email( $email ) ) return;
		$list = self::getBlockedEmails();
		foreach ( $list as $existing ) {
			if ( strtolower( trim( $existing ) ) === $email ) return;
		}
		$list[] = $email;
		update_option( 'ah_re_blocked_emails', array_values( $list ) );
	}

	public static function removeBlockedEmail( string $email ): void {
		$email = strtolower( trim( $email ) );
		$list  = array_filter( self::getBlockedEmails(), static function ( $e ) use ( $email ) {
			return strtolower( trim( $e ) ) !== $email;
		} );
		update_option( 'ah_re_blocked_emails', array_values( $list ) );
	}

	// ── Trigger log helpers ───────────────────────────────────────────────────

	public static function getLogs( int $limit = 100, int $offset = 0 ): array {
		global $wpdb;
		$lg = self::logsTable();
		$rt = self::table();
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT l.*, r.name AS rule_name
			 FROM `{$lg}` l
			 LEFT JOIN `{$rt}` r ON r.id = l.rule_id
			 ORDER BY l.id DESC LIMIT %d OFFSET %d",
			$limit, $offset
		) ) ?: array();
	}

	public static function countLogs(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `" . self::logsTable() . "`" );
	}

	public static function getLogsFiltered( array $filters, int $limit = 100, int $offset = 0 ): array {
		global $wpdb;
		$lg     = self::logsTable();
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

	public static function countLogsFiltered( array $filters ): int {
		global $wpdb;
		$lg     = self::logsTable();
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

	public static function cancelAllPending(): int {
		global $wpdb;
		$lg  = self::logsTable();
		$cfg = self::getConfig();
		$max = max( 1, (int) ( $cfg['retry_max_attempts'] ?? 3 ) );
		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"UPDATE `{$lg}` SET status = 'unsent', is_unsent = 1
			 WHERE is_done = 0 AND is_unsent = 0
			   AND ( status = 'pending' OR ( status = 'failed' AND attempts < %d ) )",
			$max
		) );
		return (int) $wpdb->rows_affected;
	}

	public static function retryAllPending(): array {
		global $wpdb;
		$lg  = self::logsTable();
		$cfg = self::getConfig();
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
				$run_results = ActionExecutor::execute( array( $action ), $context );
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

	public static function deleteLog( int $id ): void {
		global $wpdb;
		$wpdb->delete( self::logsTable(), array( 'id' => $id ), array( '%d' ) );
	}

	public static function clearLogs(): void {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE `" . self::logsTable() . "`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function markLogUnsent( int $id ): void {
		global $wpdb;
		$wpdb->update( self::logsTable(), array( 'is_unsent' => 1, 'status' => 'unsent' ), array( 'id' => $id ) );
	}

	public static function retryLog( int $id ): bool {
		global $wpdb;
		$lg  = self::logsTable();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$lg}` WHERE id = %d", $id ) );
		if ( ! $row ) return false;

		$action  = json_decode( $row->action_config ?? '{}', true ) ?: array();
		$context = json_decode( $row->context_data  ?? '{}', true ) ?: array();

		$error = null;
		$result = array();
		try {
			$run_results = ActionExecutor::execute( array( $action ), $context );
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
}
