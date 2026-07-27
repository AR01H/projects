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

		self::registerDynamicShortcuts();
	}

	/**
	 * One WP shortcode per active row in ah_shortcuts (admin: Shortcuts submenu),
	 * tag `ah_sc_{row->tag}` so a custom shortcut can never collide with a tag
	 * registered anywhere else - no hardcoded collision list to maintain.
	 * `{{variable}}` tokens in the stored HTML are replaced with the matching
	 * shortcode attribute, always HTML-escaped (attribute values may come from
	 * a lower-privileged post author, not just the manage_options admin who
	 * wrote the template). Per-shortcut CSS, if any, is collected as each
	 * shortcode actually fires and printed once per tag on `wp_footer` - pages
	 * that use no custom shortcuts print nothing extra.
	 */
	private static function registerDynamicShortcuts(): void {
		$used_css = []; // tag => css, populated only for tags that actually render on this request.

		\add_action( 'init', function () use ( &$used_css ): void {
			if ( ! \class_exists( 'AH_Shortcuts_Model' ) ) {
				return;
			}
			foreach ( ( new \AH_Shortcuts_Model() )->get_active() as $row ) {
				if ( '' === (string) $row->tag ) {
					continue;
				}
				\add_shortcode( 'ah_sc_' . $row->tag, function ( $atts, $content = null ) use ( $row, &$used_css ) {
					if ( '' === (string) $row->html ) {
						return '';
					}
					$atts = \is_array( $atts ) ? $atts : [];
					if ( null !== $content ) {
						$atts['content'] = $content;
					}
					$html = \preg_replace_callback(
						'/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
						function ( $m ) use ( $atts ) {
							return isset( $atts[ $m[1] ] ) ? \esc_html( (string) $atts[ $m[1] ] ) : '';
						},
						(string) $row->html
					);
					if ( '' !== (string) $row->css && ! isset( $used_css[ $row->tag ] ) ) {
						$used_css[ $row->tag ] = (string) $row->css;
					}
					return \do_shortcode( $html );
				} );
			}
		}, 20 );

		\add_action( 'wp_footer', function () use ( &$used_css ): void {
			foreach ( $used_css as $tag => $css ) {
				echo '<style id="ah-sc-' . \esc_attr( $tag ) . '-css">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admin-authored CSS, same model as AH_Custom_Code_Service.
			}
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
