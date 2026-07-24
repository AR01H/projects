<?php
namespace Adn\Theme\Bootstrap;

defined( 'ABSPATH' ) || exit;

class HookRegistrar {

	public static function register(): void {
		self::registerSetup();
		self::registerCache();
		self::registerFrontend();
		self::registerAdmin();
		self::registerDatabase();
		self::registerAjax();
		self::registerFilters();
		self::registerShortcodes();
		self::registerFeatureModules();
	}

	private static function registerSetup(): void {
		\add_action( 'after_setup_theme', 'ahn_include_files' );
		\add_action( 'after_setup_theme', 'adn_theme_register' );
	}

	private static function registerCache(): void {
		\add_action( 'init', function() {
			if ( \class_exists( 'ADN_Cache' ) ) {
				if ( \is_admin() && isset( $_POST['clear_cache'] ) && \current_user_can( 'manage_options' ) ) {
					\ADN_Cache::clear_all();
				}
				if ( isset( $_GET['clear_cache'] ) || isset( $_GET['cache_clear'] ) ) {
					if ( \current_user_can( 'manage_options' ) || ! \is_user_logged_in() ) {
						\ADN_Cache::clear_all();
						if ( isset( $_GET['clear_cache'] ) ) {
							$redirect_url = \remove_query_arg( 'clear_cache' );
							\wp_safe_redirect( $redirect_url );
							\exit;
						}
					}
				}
			}
		} );

		\add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
			if ( ! \is_admin() || ! \current_user_can( 'manage_options' ) ) {
				return;
			}
			$current_url = \admin_url();
			$clear_url = \add_query_arg( 'clear_cache', '1', $current_url );
			$wp_admin_bar->add_node( array(
				'id'    => 'adn-clear-cache',
				'title' => '⚡ Clear Cache',
				'href'  => $clear_url,
				'meta'  => array(
					'title' => 'Clear all theme filesystem and CMS caches',
				),
			) );
		}, 100 );
	}

	private static function registerFrontend(): void {
		\add_action( 'wp_footer', 'adn_render_site_notice_popup' );
		\add_action( 'wp_footer', 'adn_render_floating_contact' );
		\add_action( 'wp_head', 'adn_reveal_gate', 1 );
		\add_action( 'wp_footer', 'adn_reveal_runtime', 30 );
		\add_action( 'template_redirect', 'adn_expert_full_page_render', 0 );
		\add_action( 'template_redirect', 'adn_check_coming_soon' );
		\add_action( 'init', 'adn_set_language_cookie' );

		// Centralized asset loading (replaces scattered wp_enqueue calls in page templates)
		\add_action( 'wp_enqueue_scripts', [ \Adn\Theme\Service\AssetLoader::class, 'load' ] );
	}

	private static function registerAdmin(): void {
		if ( \is_admin() ) {
			// Theme admin loaded from functions.php via require_once
		}
	}

	private static function registerDatabase(): void {
		\add_action( 'after_switch_theme', 'adn_create_default_pages' );
		\add_action( 'after_switch_theme', [ 'AH_Category_Settings', 'install' ] );
		\add_action( 'admin_init', [ 'AH_Category_Settings', 'maybe_install' ] );
		\add_action( 'after_switch_theme', [ 'AH_Calculator_DB', 'install' ] );
		\add_action( 'admin_init', [ 'AH_Calculator_DB', 'maybe_install' ] );
		\add_action( 'after_switch_theme', [ 'AH_Expert_DB', 'install' ] );
		\add_action( 'admin_init', [ 'AH_Expert_DB', 'maybe_install' ] );
		\add_action( 'after_switch_theme', [ 'AH_Enquiry_Model', 'install_table' ] );
		\add_action( 'admin_init', [ 'AH_Enquiry_Model', 'maybe_install' ] );
		\add_action( 'after_switch_theme', function() {
			\flush_rewrite_rules();
		} );
		\add_action( 'admin_init', function() {
			global $wp_rewrite;
			if ( $wp_rewrite->permalink_structure !== \get_option( 'adn_permalink_flushed' ) ) {
				\flush_rewrite_rules();
				\update_option( 'adn_permalink_flushed', $wp_rewrite->permalink_structure );
			}
		} );
	}

	private static function registerAjax(): void {
		\add_action( 'wp_ajax_adn_expert_contact', 'adn_expert_contact_ajax' );
		\add_action( 'wp_ajax_nopriv_adn_expert_contact', 'adn_expert_contact_ajax' );
		\add_action( 'wp_ajax_adn_expert_unlock', 'adn_expert_unlock_ajax' );
		\add_action( 'wp_ajax_nopriv_adn_expert_unlock', 'adn_expert_unlock_ajax' );
		\add_action( 'wp_ajax_adn_post_related_articles', 'adn_post_related_articles_ajax' );
		\add_action( 'wp_ajax_nopriv_adn_post_related_articles', 'adn_post_related_articles_ajax' );
		\add_action( 'wp_ajax_adn_post_helpful', 'adn_post_helpful_ajax' );
		\add_action( 'wp_ajax_nopriv_adn_post_helpful', 'adn_post_helpful_ajax' );
		\add_action( 'wp_ajax_adn_moderate_comment', 'adn_moderate_comment_ajax' );
		\add_action( 'wp_ajax_adn_submit_comment', 'adn_ajax_submit_comment' );
		\add_action( 'wp_ajax_nopriv_adn_submit_comment', 'adn_ajax_submit_comment' );
		\add_action( 'wp_ajax_adn_load_comments', 'adn_ajax_load_comments' );
		\add_action( 'wp_ajax_nopriv_adn_load_comments', 'adn_ajax_load_comments' );
		\add_action( 'init', [ 'ADN_Form_Ajax', 'init' ] );
		\add_action( 'init', [ 'ADN_Form_Ajax', 'init_public' ] );
	}

	private static function registerFilters(): void {
		\add_filter( 'rest_url_prefix', function() { return 'api'; } );
		\add_filter( 'wp_lazy_loading_enabled', '__return_true' );
		\add_filter( 'the_content', 'adn_add_img_lazy_attr', 10 );
		\add_filter( 'adn_calculators', 'adn_merge_db_calculators' );
		\add_filter( 'wp_get_attachment_url', function( $url, $id ) {
			return $url . '?v=' . LOCAL_CACHE_VERSION;
		}, 10, 2 );
		\add_filter( 'wp_get_attachment_image_src', function( $image, $id, $size ) {
			if ( \is_array( $image ) && ! empty( $image[0] ) ) {
				$image[0] = \add_query_arg( 'v', LOCAL_CACHE_VERSION, $image[0] );
			}
			return $image;
		}, 10, 3 );
		\add_filter( 'the_content', function( $content ) {
			return \str_replace( 'src=', 'src=?v=' . LOCAL_CACHE_VERSION . '&amp;', $content );
		} );
		\add_filter( 'pre_get_posts', function( $query ) {
			if ( $query->is_search() && ! \is_admin() ) {
				$query->set( 'posts_per_page', 12 );
			}
		} );
	}

	private static function registerShortcodes(): void {
		\add_shortcode( 'adn_cat_calculators', 'adn_shortcode_cat_calculators' );
		\add_shortcode( 'adn_cookie_preferences', 'adn_shortcode_cookie_preferences' );
	}

	private static function registerFeatureModules(): void {
		// Feature hooks registered via after_setup_theme → setup()
	}
}
