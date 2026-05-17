<?php
defined( 'ABSPATH' ) || exit;

class AH_Rules_Engine {

	// ── Public entry point ────────────────────────────────────────────────────

	public static function evaluate( string $trigger, array $data ): void {
		$rules = self::get_all_rules( $trigger, 'active' );
		if ( empty( $rules ) ) return;

		foreach ( $rules as $rule ) {
			if ( ! self::check_conditions( $rule, $data ) ) continue;

			$actions      = self::get_actions( (int) $rule->id );
			$action_notes = array();
			$any_success  = false;
			$any_failed   = false;

			foreach ( $actions as $action ) {
				$ok = self::execute_action( $action, $data );
				$ok ? ( $any_success = true ) : ( $any_failed = true );
				$action_notes[] = $action->action_type . ': ' . ( $ok ? 'ok' : 'failed' );
			}

			$result = $any_failed ? ( $any_success ? 'partial' : 'failed' ) : 'success';
			self::log( (int) $rule->id, $data, $result, implode( '; ', $action_notes ) );
		}
	}

	// ── Condition evaluation ──────────────────────────────────────────────────

	private static function check_conditions( object $rule, array $data ): bool {
		$conditions = self::get_conditions( (int) $rule->id );
		if ( empty( $conditions ) ) return true;

		$use_any = ( $rule->condition_logic === 'any' );

		foreach ( $conditions as $cond ) {
			$result = self::check_condition( $cond, $data );
			if ( $use_any && $result ) return true;
			if ( ! $use_any && ! $result ) return false;
		}

		return ! $use_any; // 'all' passed; 'any' found nothing
	}

	private static function check_condition( object $cond, array $data ): bool {
		$field_val = (string) ( $data[ $cond->field ] ?? '' );
		$rule_val  = (string) ( $cond->value ?? '' );

		switch ( $cond->operator ) {
			case 'equals':       return $field_val === $rule_val;
			case 'not_equals':   return $field_val !== $rule_val;
			case 'contains':     return str_contains( $field_val, $rule_val );
			case 'not_contains': return ! str_contains( $field_val, $rule_val );
			case 'starts_with':  return str_starts_with( $field_val, $rule_val );
			case 'ends_with':    return str_ends_with( $field_val, $rule_val );
			case 'is_empty':     return $field_val === '';
			case 'is_not_empty': return $field_val !== '';
			case 'greater_than': return (float) $field_val > (float) $rule_val;
			case 'less_than':    return (float) $field_val < (float) $rule_val;
			default:             return false;
		}
	}

	// ── Action execution ──────────────────────────────────────────────────────

	private static function execute_action( object $action, array $data ): bool {
		$config = is_string( $action->config )
			? (array) json_decode( $action->config, true )
			: (array) $action->config;

		switch ( $action->action_type ) {
			case 'send_email':    return self::action_send_email( $config, $data );
			case 'webhook':       return self::action_webhook( $config, $data );
			case 'internal_note': return self::action_internal_note( $config, $data );
			default:              return false;
		}
	}

	private static function action_send_email( array $config, array $data ): bool {
		$to      = self::interpolate( $config['to'] ?? '', $data );
		$subject = self::interpolate( $config['subject'] ?? 'New submission', $data );
		$body    = self::interpolate( $config['body'] ?? '', $data );

		if ( ! $to || ! is_email( $to ) ) return false;

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		if ( ! empty( $config['from_name'] ) && ! empty( $config['from_email'] ) ) {
			$headers[] = 'From: ' . sanitize_text_field( $config['from_name'] ) . ' <' . sanitize_email( $config['from_email'] ) . '>';
		}

		return wp_mail( $to, $subject, nl2br( esc_html( $body ) ), $headers );
	}

	private static function action_webhook( array $config, array $data ): bool {
		$url = esc_url_raw( $config['url'] ?? '' );
		if ( ! $url ) return false;

		$method = in_array( strtoupper( $config['method'] ?? 'POST' ), array( 'GET', 'POST' ), true )
			? strtoupper( $config['method'] )
			: 'POST';

		$response = wp_remote_request( $url, array(
			'method'  => $method,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $data ),
			'timeout' => 15,
		) );

		return ! is_wp_error( $response );
	}

	private static function action_internal_note( array $config, array $data ): bool {
		$note          = self::interpolate( $config['note'] ?? '', $data );
		$submission_id = (int) ( $data['submission_id'] ?? 0 );

		if ( ! $submission_id || ! $note ) return false;

		if ( ! class_exists( 'AH_Contact_Model' ) ) return false;
		$model = new AH_Contact_Model();
		return $model->add_note( $submission_id, '[Rule] ' . $note );
	}

	// ── Template interpolation ────────────────────────────────────────────────

	private static function interpolate( string $template, array $data ): string {
		return preg_replace_callback(
			'/\{\{(\w+)\}\}/',
			static function ( array $m ) use ( $data ): string {
				return esc_html( (string) ( $data[ $m[1] ] ?? '' ) );
			},
			$template
		);
	}

	// ── Logging ───────────────────────────────────────────────────────────────

	private static function log( int $rule_id, array $trigger_data, string $result, string $notes = '' ): void {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'ah_rule_logs',
			array(
				'rule_id'      => $rule_id,
				'trigger_data' => wp_json_encode( $trigger_data ),
				'result'       => $result,
				'notes'        => $notes,
			)
		);
	}

	// ── CRUD ──────────────────────────────────────────────────────────────────

	public static function get_all_rules( string $trigger = '', string $status = '' ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_rules';
		$where = array();
		$args  = array();

		if ( $trigger ) { $where[] = 'trigger_event = %s'; $args[] = $trigger; }
		if ( $status )  { $where[] = 'status = %s';        $args[] = $status; }

		$sql = "SELECT * FROM `{$table}`";
		if ( $where ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}
		$sql .= ' ORDER BY sort_order ASC, id ASC';

		if ( $args ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (array) $wpdb->get_results( $wpdb->prepare( $sql, ...$args ) );
		}
		return (array) $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public static function get_rule( int $id ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_rules';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id ) ) ?: null;
	}

	public static function get_conditions( int $rule_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_rule_conditions';
		return (array) $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE rule_id = %d ORDER BY sort_order ASC, id ASC",
			$rule_id
		) );
	}

	public static function get_actions( int $rule_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_rule_actions';
		return (array) $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE rule_id = %d ORDER BY sort_order ASC, id ASC",
			$rule_id
		) );
	}

	public static function get_logs( int $rule_id = 0, int $limit = 100 ): array {
		global $wpdb;
		$lt = $wpdb->prefix . 'ah_rule_logs';
		$rt = $wpdb->prefix . 'ah_rules';

		if ( $rule_id ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return (array) $wpdb->get_results( $wpdb->prepare(
				"SELECT l.*, r.name AS rule_name FROM `{$lt}` l LEFT JOIN `{$rt}` r ON r.id = l.rule_id WHERE l.rule_id = %d ORDER BY l.created_at DESC LIMIT %d",
				$rule_id,
				$limit
			) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (array) $wpdb->get_results( $wpdb->prepare(
			"SELECT l.*, r.name AS rule_name FROM `{$lt}` l LEFT JOIN `{$rt}` r ON r.id = l.rule_id ORDER BY l.created_at DESC LIMIT %d",
			$limit
		) );
	}

	public static function save_rule( array $data, int $id = 0 ): int|false {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_rules';
		$row   = array(
			'name'            => sanitize_text_field( $data['name'] ?? '' ),
			'description'     => sanitize_textarea_field( $data['description'] ?? '' ),
			'trigger_event'   => sanitize_key( $data['trigger_event'] ?? 'contact_submitted' ),
			'condition_logic' => in_array( $data['condition_logic'] ?? '', array( 'all', 'any' ), true ) ? $data['condition_logic'] : 'all',
			'status'          => in_array( $data['status'] ?? '', array( 'active', 'inactive' ), true ) ? $data['status'] : 'active',
			'sort_order'      => (int) ( $data['sort_order'] ?? 0 ),
			'created_by'      => get_current_user_id() ?: null,
		);

		if ( $id ) {
			$wpdb->update( $table, $row, array( 'id' => $id ) );
			return $id;
		}

		return $wpdb->insert( $table, $row ) ? $wpdb->insert_id : false;
	}

	public static function save_conditions( int $rule_id, array $conditions ): void {
		global $wpdb;
		$table       = $wpdb->prefix . 'ah_rule_conditions';
		$allowed_ops = array( 'equals', 'not_equals', 'contains', 'not_contains', 'starts_with', 'ends_with', 'is_empty', 'is_not_empty', 'greater_than', 'less_than' );

		$wpdb->delete( $table, array( 'rule_id' => $rule_id ) );

		foreach ( $conditions as $i => $cond ) {
			$op = in_array( $cond['operator'] ?? '', $allowed_ops, true ) ? $cond['operator'] : 'equals';
			$wpdb->insert( $table, array(
				'rule_id'    => $rule_id,
				'field'      => sanitize_key( $cond['field'] ?? '' ),
				'operator'   => $op,
				'value'      => sanitize_text_field( $cond['value'] ?? '' ),
				'sort_order' => $i,
			) );
		}
	}

	public static function save_actions( int $rule_id, array $actions ): void {
		global $wpdb;
		$table         = $wpdb->prefix . 'ah_rule_actions';
		$allowed_types = array( 'send_email', 'webhook', 'internal_note' );

		$wpdb->delete( $table, array( 'rule_id' => $rule_id ) );

		foreach ( $actions as $i => $action ) {
			$type = in_array( $action['action_type'] ?? '', $allowed_types, true ) ? $action['action_type'] : 'send_email';
			switch ( $type ) {
				case 'send_email':
					$config = array(
						'to'         => sanitize_email( $action['to'] ?? '' ),
						'subject'    => sanitize_text_field( $action['subject'] ?? '' ),
						'body'       => sanitize_textarea_field( $action['body'] ?? '' ),
						'from_name'  => sanitize_text_field( $action['from_name'] ?? '' ),
						'from_email' => sanitize_email( $action['from_email'] ?? '' ),
					);
					break;
				case 'webhook':
					$config = array(
						'url'    => esc_url_raw( $action['url'] ?? '' ),
						'method' => in_array( strtoupper( $action['method'] ?? 'POST' ), array( 'GET', 'POST' ), true ) ? strtoupper( $action['method'] ) : 'POST',
					);
					break;
				default: // internal_note
					$config = array( 'note' => sanitize_textarea_field( $action['note'] ?? '' ) );
					break;
			}
			$wpdb->insert( $table, array(
				'rule_id'     => $rule_id,
				'action_type' => $type,
				'config'      => wp_json_encode( $config ),
				'sort_order'  => $i,
			) );
		}
	}

	public static function delete_rule( int $id ): bool {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ah_rule_conditions', array( 'rule_id' => $id ) );
		$wpdb->delete( $wpdb->prefix . 'ah_rule_actions',    array( 'rule_id' => $id ) );
		$wpdb->delete( $wpdb->prefix . 'ah_rule_logs',       array( 'rule_id' => $id ) );
		return (bool) $wpdb->delete( $wpdb->prefix . 'ah_rules', array( 'id' => $id ) );
	}

	public static function toggle_status( int $id ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_rules';
		$rule  = self::get_rule( $id );
		if ( ! $rule ) return false;
		$new = $rule->status === 'active' ? 'inactive' : 'active';
		return (bool) $wpdb->update( $table, array( 'status' => $new ), array( 'id' => $id ) );
	}
}
