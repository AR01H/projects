<?php

namespace Ah\Cms\Feature\Workflow;

defined( 'ABSPATH' ) || exit;

/**
 * Workflow module entry point.
 * Registers hooks for the automation rules engine.
 */
class WorkflowModule {

	public static function register(): void {
		\add_action( 'admin_menu', [ self::class, 'registerMenu' ] );
		\add_action( 'admin_enqueue_scripts', [ self::class, 'enqueueAssets' ] );
		\add_action( 'ah_workflow_cron', [ Cron\WorkflowCron::class, 'process' ] );
		\add_action( 'rest_api_init', [ Controller\WorkflowRestController::class, 'registerRoutes' ] );
	}

	public static function registerMenu(): void {
		\add_submenu_page( 'ah-cms', 'Workflow Manager', 'Workflow Manager', 'manage_options', 'ah-workflow', [ Controller\WorkflowAdminController::class, 'render' ] );
	}

	public static function enqueueAssets( string $hook ): void {
		if ( \strpos( $hook, 'ah-workflow' ) === false ) {
			return;
		}
		\wp_enqueue_style( 'ah-workflow', AH_PLUGIN_URL . '/src/Feature/Workflow/Assets/css/workflow.css', [], '1.0' );
		\wp_enqueue_script( 'ah-workflow', AH_PLUGIN_URL . '/src/Feature/Workflow/Assets/js/workflow.js', [ 'jquery' ], '1.0', true );
		\wp_localize_script( 'ah-workflow', 'ahWorkflow', [
			'ajaxUrl' => \admin_url( 'admin-ajax.php' ),
			'nonce'   => \wp_create_nonce( 'ah_workflow_nonce' ),
		] );
	}
}
