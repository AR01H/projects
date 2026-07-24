<?php
defined( 'ABSPATH' ) || exit;

use Ah\Cms\Feature\Workflow\Service\RuleEngine;
use Ah\Cms\Feature\Workflow\Service\ConditionEvaluator;
use Ah\Cms\Feature\Workflow\Service\ActionExecutor;
use Ah\Cms\Feature\Workflow\Cron\WorkflowCron;

/**
 * Workflow Manager — thin facade that delegates to decomposed service classes.
 *
 * Call from anywhere:
 *   AH_Workflow_Manager::evaluate( 'your_trigger_name', [ 'field_key' => 'value', ... ] );
 *
 * All public methods preserve their original signatures for backward compatibility.
 */
class AH_Workflow_Manager {

	// ── Schema ───────────────────────────────────────────────────────────────

	public static function table(): string {
		return RuleEngine::table();
	}

	public static function logs_table(): string {
		return RuleEngine::logsTable();
	}

	public static function evaluate_table(): string {
		return RuleEngine::evaluateTable();
	}

	public static function install_tables(): void {
		RuleEngine::installTables();
	}

	// ── CRUD ─────────────────────────────────────────────────────────────────

	public static function get_all(): array {
		return RuleEngine::getAll();
	}

	public static function get( int $id ): ?object {
		return RuleEngine::get( $id );
	}

	public static function save( int $id, array $data ): int {
		return RuleEngine::save( $id, $data );
	}

	public static function delete( int $id ): void {
		RuleEngine::delete( $id );
	}

	// ── Public API ────────────────────────────────────────────────────────────

	/**
	 * Evaluate all matching rules for a trigger event.
	 *
	 * @param string $trigger_name  Event slug, e.g. 'form'.
	 * @param array  $context       Key/value pairs - become {tokens} in action templates.
	 * @param bool   $immediate     true  -> run matching actions right now (synchronous).
	 *                              false -> queue into ah_trigger_logs for cron (default).
	 */
	public static function evaluate( string $trigger_name, array $context, bool $immediate = false ): void {
		global $wpdb;

		// Global freeze: skip everything when the kill-switch is on.
		if ( '1' === ( RuleEngine::getConfig()['global_freeze'] ?? '0' ) ) return;

		// Log every evaluate() call before rule check
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
			$rule = RuleEngine::decode( $rule );
			$rule_ctx = $context;

			// Rule-specific freeze: skip saved rules that are currently disabled
			if ( filter_var( $rule->settings['frozen'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
				continue;
			}

			// 1. Inject Variable Profile variables first
			$prof_id = $rule->settings['var_profile_id'] ?? '';
			if ( $prof_id ) {
				$profile = RuleEngine::getVarProfile( $prof_id );
				if ( $profile && ! empty( $profile['vars'] ) ) {
					foreach ( $profile['vars'] as $v ) {
						if ( ! empty( $v['key'] ) ) {
							$rule_ctx[ $v['key'] ] = ActionExecutor::fill( $v['value'] ?? '', $rule_ctx );
						}
					}
				}
			}

			// 2. Inject Rule-specific Custom Variables (these override profile vars)
			foreach ( $rule->settings['custom_vars'] ?? array() as $cv ) {
				if ( ! empty( $cv['key'] ) ) {
					$rule_ctx[ $cv['key'] ] = ActionExecutor::fill( $cv['value'] ?? '', $rule_ctx );
				}
			}

			if ( ! ConditionEvaluator::evaluate( $rule, $rule_ctx ) ) continue;
			if ( ! WorkflowCron::passesDedup( $rule, $rule_ctx ) ) continue;
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
						$run_results = ActionExecutor::execute( array( $action ), $rule_ctx );
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

		// Update entry with final match counts
		if ( $entry_id ) {
			$wpdb->update( $el, array(
				'rules_found' => $rules_found,
				'rules_fired' => $rules_fired,
			), array( 'id' => $entry_id ) );
		}
	}

	// ── Cron ──────────────────────────────────────────────────────────────────

	public static function cron_process(): void {
		WorkflowCron::process();
	}

	// ── Placeholder interpolation ─────────────────────────────────────────────

	public static function fill( string $tpl, array $ctx ): string {
		return ActionExecutor::fill( $tpl, $ctx );
	}

	public static function fill_html( string $tpl, array $ctx ): string {
		return ActionExecutor::fillHtml( $tpl, $ctx );
	}

	// ── Sanitize actions on save ──────────────────────────────────────────────

	private static function sanitize_email_list( $input ): array {
		return RuleEngine::sanitizeEmailList( $input );
	}

	private static function sanitize_action( array $a ): ?array {
		return RuleEngine::sanitizeAction( $a );
	}

	// ── Meta helpers ─────────────────────────────────────────────────────────

	public static function valid_operator( string $op ): string {
		return RuleEngine::validOperator( $op );
	}

	public static function operators(): array {
		return RuleEngine::operators();
	}

	public static function trigger_presets(): array {
		return RuleEngine::triggerPresets();
	}

	// ── Global config ─────────────────────────────────────────────────────────

	public static function get_config(): array {
		return RuleEngine::getConfig();
	}

	public static function save_config( array $data ): void {
		RuleEngine::saveConfig( $data );
	}

	// ── Custom config variables ───────────────────────────────────────────────

	public static function get_custom_vars(): array {
		return RuleEngine::getCustomVars();
	}

	public static function save_custom_vars( array $vars ): void {
		RuleEngine::saveCustomVars( $vars );
	}

	// ── Variable Profiles ─────────────────────────────────────────────────────

	public static function get_var_profiles(): array {
		return RuleEngine::getVarProfiles();
	}

	public static function get_var_profile( string $id ): ?array {
		return RuleEngine::getVarProfile( $id );
	}

	public static function save_var_profiles( array $profiles ): void {
		RuleEngine::saveVarProfiles( $profiles );
	}

	// ── Email channels / SMTP profiles ────────────────────────────────────────

	public static function get_email_channels(): array {
		return RuleEngine::getEmailChannels();
	}

	public static function get_email_channels_list(): array {
		return RuleEngine::getEmailChannelsList();
	}

	public static function get_email_channel( string $id ): ?array {
		return RuleEngine::getEmailChannel( $id );
	}

	public static function save_email_channels( array $channels ): void {
		RuleEngine::saveEmailChannels( $channels );
	}

	// ── Blocked emails ────────────────────────────────────────────────────────

	public static function get_blocked_emails(): array {
		return RuleEngine::getBlockedEmails();
	}

	public static function is_email_blocked( string $email ): bool {
		return RuleEngine::isEmailBlocked( $email );
	}

	public static function add_blocked_email( string $email ): void {
		RuleEngine::addBlockedEmail( $email );
	}

	public static function remove_blocked_email( string $email ): void {
		RuleEngine::removeBlockedEmail( $email );
	}

	// ── Trigger log helpers ───────────────────────────────────────────────────

	public static function get_logs( int $limit = 100, int $offset = 0 ): array {
		return RuleEngine::getLogs( $limit, $offset );
	}

	public static function count_logs(): int {
		return RuleEngine::countLogs();
	}

	public static function get_logs_filtered( array $filters, int $limit = 100, int $offset = 0 ): array {
		return RuleEngine::getLogsFiltered( $filters, $limit, $offset );
	}

	public static function count_logs_filtered( array $filters ): int {
		return RuleEngine::countLogsFiltered( $filters );
	}

	public static function cancel_all_pending(): int {
		return RuleEngine::cancelAllPending();
	}

	public static function retry_all_pending(): array {
		return RuleEngine::retryAllPending();
	}

	public static function delete_log( int $id ): void {
		RuleEngine::deleteLog( $id );
	}

	public static function clear_logs(): void {
		RuleEngine::clearLogs();
	}

	public static function mark_log_unsent( int $id ): void {
		RuleEngine::markLogUnsent( $id );
	}

	public static function retry_log( int $id ): bool {
		return RuleEngine::retryLog( $id );
	}

	// ── REST API ──────────────────────────────────────────────────────────────

	public static function register_rest_routes() {
		// Routes are now registered by WorkflowRestController via WorkflowModule.
		// This method is kept for backward compatibility with any direct
		// add_action( 'rest_api_init', [...] ) registrations.
	}

	public static function verify_external_trigger( WP_REST_Request $request ) {
		return \Ah\Cms\Feature\Workflow\Controller\WorkflowRestController::verifyExternalTrigger( $request );
	}

	public static function handle_external_trigger( WP_REST_Request $request ) {
		return \Ah\Cms\Feature\Workflow\Controller\WorkflowRestController::handleExternalTrigger( $request );
	}

	public static function handle_test_channel( WP_REST_Request $request ) {
		return \Ah\Cms\Feature\Workflow\Controller\WorkflowRestController::handleTestChannel( $request );
	}

	public static function handle_test_rule( WP_REST_Request $request ) {
		return \Ah\Cms\Feature\Workflow\Controller\WorkflowRestController::handleTestRule( $request );
	}
}
