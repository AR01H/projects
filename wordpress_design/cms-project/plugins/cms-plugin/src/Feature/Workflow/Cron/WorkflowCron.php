<?php

namespace Ah\Cms\Feature\Workflow\Cron;

use Ah\Cms\Feature\Workflow\Service\RuleEngine;
use Ah\Cms\Feature\Workflow\Service\ActionExecutor;

defined( 'ABSPATH' ) || exit;

/**
 * Workflow Cron — processes pending/failed actions and dedup/cooldown logic.
 */
class WorkflowCron {

	/**
	 * Called by WP-Cron every minute.
	 * Picks up all 'pending' rows and 'failed' rows under the retry limit.
	 */
	public static function process(): void {
		global $wpdb;
		$lg  = RuleEngine::logsTable();
		$cfg = RuleEngine::getConfig();
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

	/**
	 * Dedup + cooldown check - returns false if this rule should be skipped.
	 */
	public static function passesDedup( object $rule, array $context ): bool {
		global $wpdb;
		$lg = RuleEngine::logsTable();
		$s  = (array) ( $rule->settings ?? array() );

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
			$value = (string) ( $context[ $field ] ?? ActionExecutor::fill( $dedup_key, $context ) );
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
}
