<?php

namespace Ah\Cms\Feature\Workflow\Controller;

use Ah\Cms\Feature\Workflow\Service\RuleEngine;
use Ah\Cms\Feature\Workflow\Service\ConditionEvaluator;
use Ah\Cms\Feature\Workflow\Service\ActionExecutor;

defined( 'ABSPATH' ) || exit;

class WorkflowRestController {

	public static function registerRoutes(): void {
		// CRUD routes
		\register_rest_route( 'ah-workflow/v1', '/rules', [
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'getRules' ],
				'permission_callback' => function() {
					return \current_user_can( 'manage_options' );
				},
			],
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'saveRule' ],
				'permission_callback' => function() {
					return \current_user_can( 'manage_options' );
				},
			],
		] );

		\register_rest_route( 'ah-workflow/v1', '/rules/(?P<id>\d+)', [
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'getRule' ],
				'permission_callback' => function() {
					return \current_user_can( 'manage_options' );
				},
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ self::class, 'deleteRule' ],
				'permission_callback' => function() {
					return \current_user_can( 'manage_options' );
				},
			],
		] );

		\register_rest_route( 'ah-workflow/v1', '/evaluate', [
			'methods'             => 'POST',
			'callback'            => [ self::class, 'evaluate' ],
			'permission_callback' => function() {
				return \current_user_can( 'manage_options' );
			},
		] );

		// Legacy trigger/test routes (backward compatibility with AH_Workflow_Manager)
		\register_rest_route( 'ah-workflow/v1', '/trigger', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ self::class, 'handleExternalTrigger' ],
			'permission_callback' => [ self::class, 'verifyExternalTrigger' ],
		] );

		\register_rest_route( 'ah-workflow/v1', '/test-channel', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ self::class, 'handleTestChannel' ],
			'permission_callback' => function() { return \current_user_can( 'manage_options' ); },
		] );

		\register_rest_route( 'ah-workflow/v1', '/test-rule', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ self::class, 'handleTestRule' ],
			'permission_callback' => function() { return \current_user_can( 'manage_options' ); },
		] );
	}

	// ── CRUD handlers ────────────────────────────────────────────────────────

	public static function getRules( \WP_REST_Request $request ): \WP_REST_Response {
		$rules = RuleEngine::getAll();
		return new \WP_REST_Response( $rules, 200 );
	}

	public static function getRule( \WP_REST_Request $request ): \WP_REST_Response {
		$id = (int) $request->get_param( 'id' );
		$rule = RuleEngine::get( $id );
		if ( ! $rule ) {
			return new \WP_REST_Response( [ 'error' => 'Rule not found' ], 404 );
		}
		return new \WP_REST_Response( $rule, 200 );
	}

	public static function saveRule( \WP_REST_Request $request ): \WP_REST_Response {
		$data = $request->get_json_params();
		$id = $data['id'] ?? 0;
		$result = RuleEngine::save( $id, $data );

		if ( ! $result ) {
			return new \WP_REST_Response( [ 'error' => 'Failed to save rule' ], 500 );
		}

		return new \WP_REST_Response( [ 'success' => true, 'id' => $result ], 200 );
	}

	public static function deleteRule( \WP_REST_Request $request ): \WP_REST_Response {
		$id = (int) $request->get_param( 'id' );
		RuleEngine::delete( $id );
		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}

	public static function evaluate( \WP_REST_Request $request ): \WP_REST_Response {
		$data = $request->get_json_params();
		$triggerName = $data['trigger_name'] ?? '';
		$context = $data['context'] ?? $data;

		if ( $triggerName ) {
			\AH_Workflow_Manager::evaluate( $triggerName, $context, true );
			return new \WP_REST_Response( [ 'success' => true ], 200 );
		}

		$activeRules = RuleEngine::getAll();
		$results = [];
		foreach ( $activeRules as $rule ) {
			if ( ConditionEvaluator::evaluate( $rule, $context ) ) {
				$ruleResults = ActionExecutor::execute( $rule->actions, $context );
				$results[] = [
					'rule_id'  => $rule->id,
					'executed' => $ruleResults,
				];
			}
		}
		return new \WP_REST_Response( [ 'results' => $results ], 200 );
	}

	// ── Legacy trigger/test handlers ─────────────────────────────────────────

	public static function verifyExternalTrigger( \WP_REST_Request $request ) {
		if ( defined( 'AH_WORKFLOW_API_KEY' ) && AH_WORKFLOW_API_KEY ) {
			$header = $request->get_header( 'x_ah_workflow_key' );
			if ( $header !== AH_WORKFLOW_API_KEY ) {
				return new \WP_Error( 'unauthorized', 'Invalid API Key', array( 'status' => 401 ) );
			}
		}
		return true;
	}

	public static function handleExternalTrigger( \WP_REST_Request $request ) {
		$trigger_name = sanitize_text_field( $request->get_param( 'trigger_name' ) ?? '' );
		$context      = (array) ( $request->get_param( 'context' ) ?? array() );

		if ( ! $trigger_name ) {
			return new \WP_Error( 'missing_trigger', 'Missing trigger_name parameter', array( 'status' => 400 ) );
		}

		\AH_Workflow_Manager::evaluate( $trigger_name, $context, true );
		return new \WP_REST_Response( array( 'success' => true, 'message' => "Evaluated trigger: {$trigger_name}" ), 200 );
	}

	public static function handleTestChannel( \WP_REST_Request $request ) {
		$channel_id = sanitize_key( $request->get_param( 'channel_id' ) ?? '' );
		$test_email = sanitize_email( $request->get_param( 'test_email' ) ?? '' );

		if ( ! $channel_id || ! $test_email ) {
			return new \WP_Error( 'missing_params', 'Missing channel_id or test_email', array( 'status' => 400 ) );
		}

		$channel = RuleEngine::getEmailChannel( $channel_id );
		if ( ! $channel ) {
			return new \WP_Error( 'not_found', 'Channel not found', array( 'status' => 404 ) );
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
			ActionExecutor::execute( array( $dummy_action ), array() );
			return new \WP_REST_Response( array( 'success' => true, 'message' => 'Test email sent successfully!' ), 200 );
		} catch ( \Throwable $e ) {
			return new \WP_Error( 'send_failed', 'Test failed: ' . $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	public static function handleTestRule( \WP_REST_Request $request ) {
		global $wpdb;
		$rule_id = (int) $request->get_param( 'rule_id' );
		$context = $request->get_param( 'context' );

		if ( is_string( $context ) ) {
			$context = json_decode( $context, true ) ?: array();
		}
		$context = (array) $context;

		if ( ! $rule_id ) {
			return new \WP_Error( 'missing_params', 'Missing rule_id parameter', array( 'status' => 400 ) );
		}

		$rule = RuleEngine::get( $rule_id );
		if ( ! $rule ) {
			return new \WP_Error( 'not_found', 'Rule not found', array( 'status' => 404 ) );
		}

		// 1. Setup Variables
		$rule_ctx = $context;
		$prof_id = $rule->settings['var_profile_id'] ?? '';
		if ( $prof_id ) {
			$profile = RuleEngine::getVarProfile( $prof_id );
			if ( $profile && ! empty( $profile['vars'] ) ) {
				foreach ( $profile['vars'] as $v ) {
					if ( ! empty( $v['key'] ) ) $rule_ctx[ $v['key'] ] = ActionExecutor::fill( $v['value'] ?? '', $rule_ctx );
				}
			}
		}
		foreach ( $rule->settings['custom_vars'] ?? array() as $cv ) {
			if ( ! empty( $cv['key'] ) ) $rule_ctx[ $cv['key'] ] = ActionExecutor::fill( $cv['value'] ?? '', $rule_ctx );
		}

		$lg = RuleEngine::logsTable();
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
					$run_results = ActionExecutor::execute( array( $action ), $rule_ctx );
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
			"UPDATE `" . RuleEngine::table() . "` SET run_count = run_count + 1, last_run = %s WHERE id = %d",
			$now, (int) $rule->id
		) );

		if ( $overall_error ) {
			return new \WP_Error( 'send_failed', 'Rule test completed with errors: ' . $overall_error, array( 'status' => 500 ) );
		}

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Rule actions executed and logged successfully!' ), 200 );
	}
}
