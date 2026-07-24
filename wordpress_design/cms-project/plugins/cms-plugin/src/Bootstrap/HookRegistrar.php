<?php
namespace Ah\Cms\Bootstrap;

defined( 'ABSPATH' ) || exit;

class HookRegistrar {

	public static function register(): void {
		self::registerDatabase();
		self::registerAdmin();
		self::registerRestApi();
		self::registerFrontend();
		self::registerShortcodes();
		self::registerCron();
		self::registerAjax();
		self::registerFilters();
		self::registerFeatureModules();
	}

	private static function registerDatabase(): void {
		\register_activation_hook( AH_PLUGIN_DIR . '/ah-cms.php', [ 'AH_DB_Installer', 'install' ] );
		\add_action( 'wp_loaded', [ 'AH_DB_Installer', 'maybe_upgrade' ] );
	}

	private static function registerAdmin(): void {
		if ( \is_admin() ) {
			\add_action( 'after_setup_theme', [ 'AH_Admin_Bootstrap', 'init' ] );
		}
	}

	private static function registerRestApi(): void {
		\add_action( 'rest_api_init', [ 'AH_Rest_Routes', 'register' ] );
		\add_action( 'rest_api_init', [ 'AH_Analytics_Rest_Controller', 'registerRoutes' ] );
	}

	private static function registerFrontend(): void {
		\add_action( 'template_redirect', [ 'AH_Redirect_Service', 'checkRedirects' ], 1 );
		\add_action( 'template_redirect', [ 'AH_Builder_Page_Service', 'handleFrontend' ], 5 );
		\add_action( 'wp_head', [ 'AH_Custom_Code_Service', 'injectGlobalCss' ], 98 );
		\add_action( 'wp_head', [ 'AH_Custom_Code_Service', 'injectSlugCss' ], 99 );
		\add_action( 'wp_footer', [ 'AH_Custom_Code_Service', 'injectGlobalJs' ], 98 );
		\add_action( 'wp_footer', [ 'AH_Custom_Code_Service', 'injectSlugJs' ], 99 );
	}

	private static function registerShortcodes(): void {
		\add_action( 'init', function (): void {
			\add_shortcode( 'ah_form', [ 'AH_Form_Builder', 'render' ] );
			\add_shortcode( 'ah_related_links', [ 'AH_RelatedLinks_Shortcode', 'render' ] );
			\add_shortcode( 'ah_static_page', [ 'AH_StaticPage_Shortcode', 'render' ] );
			\add_shortcode( 'ah_resource', [ 'AH_Resource_Shortcode', 'render' ] );
			\add_shortcode( 'ah_resources', [ 'AH_Resources_Shortcode', 'render' ] );
		} );
	}

	private static function registerCron(): void {
		\add_filter( 'cron_schedules', function ( array $s ): array {
			if ( ! isset( $s['ah_every_minute'] ) ) {
				$s['ah_every_minute'] = [
					'interval' => 60,
					'display'  => 'Every Minute (AH Workflow Manager)',
				];
			}
			return $s;
		} );

		\add_action( 'init', function (): void {
			$old = \wp_next_scheduled( 'ah_rules_cron_retry' );
			if ( $old ) {
				\wp_unschedule_event( $old, 'ah_rules_cron_retry' );
			}
			if ( ! \wp_next_scheduled( 'ah_rules_cron_process' ) ) {
				\wp_schedule_event( \time(), 'ah_every_minute', 'ah_rules_cron_process' );
			}
		} );

		\register_deactivation_hook( AH_PLUGIN_DIR . '/ah-cms.php', function (): void {
			$ts = \wp_next_scheduled( 'ah_rules_cron_process' );
			if ( $ts ) {
				\wp_unschedule_event( $ts, 'ah_rules_cron_process' );
			}
		} );

		\add_action( 'ah_rules_cron_process', [ 'AH_Workflow_Manager', 'cron_process' ] );
		\add_action( 'ah_cache_warm', [ 'Ah\Cms\Feature\Cache\Service\CacheManager', 'warm' ] );
		\add_action( 'ah_cache_cleanup', [ 'Ah\Cms\Feature\Cache\Service\CacheManager', 'cleanup' ] );
	}

	private static function registerAjax(): void {
		\AH_Ajax_Handlers::init_public();
		\add_action( 'wp_ajax_ah_save_custom_code', [ 'AH_Custom_Code_Service', 'ajaxSave' ] );
		\add_action( 'wp_ajax_ah_delete_custom_code', [ 'AH_Custom_Code_Service', 'ajaxDelete' ] );
		\add_action( 'wp_ajax_ah_toggle_custom_code', [ 'AH_Custom_Code_Service', 'ajaxToggle' ] );
		\add_action( 'wp_ajax_ah_save_global_styles', [ 'AH_Custom_Code_Service', 'ajaxSaveGlobalStyles' ] );
		\add_action( 'wp_ajax_ah_analytics_action', [ 'Ah\Cms\Feature\Analytics\Controller\AnalyticsAjaxController', 'handle' ] );
	}

	private static function registerFilters(): void {
		\add_filter( 'big_image_size_threshold', function ( $threshold ) {
			return \get_option( 'ah_disable_optimized_images', '0' ) === '1' ? false : $threshold;
		}, 10, 1 );
	}

	private static function registerFeatureModules(): void {
		// NOTE: All admin menus are registered by AH_Admin_Menus (admin/menus/AdminMenus.php).
		// Module registerMenu() methods are NOT used — they duplicate menus and reference
		// controller classes without render() methods. Only non-menu hooks are registered here.

		// Asset enqueue hooks
		\add_action( 'admin_enqueue_scripts', [ 'Ah\Cms\Feature\FormBuilder\FormBuilderModule', 'enqueueAssets' ] );
		\add_action( 'admin_enqueue_scripts', [ 'Ah\Cms\Feature\Posts\PostsModule', 'enqueueAssets' ] );
		\add_action( 'admin_enqueue_scripts', [ 'Ah\Cms\Feature\Newsletter\NewsletterModule', 'enqueueAssets' ] );
		\add_action( 'admin_enqueue_scripts', [ 'Ah\Cms\Feature\Workflow\WorkflowModule', 'enqueueAssets' ] );
		\add_action( 'admin_enqueue_scripts', [ 'Ah\Cms\Feature\Pages\PagesModule', 'enqueueAssets' ] );
		\add_action( 'admin_enqueue_scripts', [ 'Ah\Cms\Feature\Settings\SettingsModule', 'enqueueAssets' ] );

		// REST API routes
		\add_action( 'rest_api_init', [ 'Ah\Cms\Feature\Visitors\Controller\VisitorPingRestController', 'registerRoutes' ] );
		\add_action( 'rest_api_init', [ 'Ah\Cms\Feature\Workflow\Controller\WorkflowRestController', 'registerRoutes' ] );

		// Cron
		\add_action( 'ah_workflow_cron', [ 'Ah\Cms\Feature\Workflow\Cron\WorkflowCron', 'process' ] );

		// Frontend routing
		\add_action( 'template_redirect', [ 'Ah\Cms\Feature\Pages\PagesModule', 'handleFrontend' ] );
	}
}
