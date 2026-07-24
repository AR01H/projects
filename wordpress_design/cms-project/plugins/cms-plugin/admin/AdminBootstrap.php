<?php
defined( 'ABSPATH' ) || exit;

use Ah\Cms\Feature\Navigation\Controller\NavigationAdminController;
use Ah\Cms\Feature\Spotlights\Controller\SpotlightsAdminController;
use Ah\Cms\Feature\SiteNotices\Controller\SiteNoticesAdminController;
use Ah\Cms\Feature\Banners\Controller\BannersAdminController;
use Ah\Cms\Feature\Posts\Controller\PostAdminController;

class AH_Admin_Bootstrap {

	public static function init(): void {
		add_action( 'admin_menu', array( 'AH_Admin_Menus', 'register' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'admin_bar_menu', array( self::class, 'clean_admin_bar' ), 999 );
		add_action( 'admin_post_adn_purge_cache', array( self::class, 'handle_purge_cache' ) );

		// Feature controllers.
		add_action( 'admin_post_ah_cms_nav',                array( NavigationAdminController::class, 'handle_save' ) );
		add_action( 'admin_post_ah_delete_spotlight_item',  array( SpotlightsAdminController::class, 'handle_delete_item' ) );
		add_action( 'admin_post_ah_delete_spotlight_term',  array( SpotlightsAdminController::class, 'handle_delete_term' ) );
		add_action( 'admin_post_ah_save_notice',            array( SiteNoticesAdminController::class, 'handle_save' ) );
		add_action( 'admin_post_ah_delete_notice',          array( SiteNoticesAdminController::class, 'handle_delete' ) );
		add_action( 'admin_post_ah_toggle_notice',          array( SiteNoticesAdminController::class, 'handle_toggle' ) );
		add_action( 'admin_post_ah_save_banners',           array( BannersAdminController::class, 'handle_save' ) );
		add_action( 'add_meta_boxes',                       array( PostAdminController::class, 'register_metaboxes' ) );
		add_action( 'save_post',                            array( PostAdminController::class, 'save_metabox' ), 10, 1 );

		AH_Ajax_Handlers::init();
		AH_Analytics_Ajax::init();
		add_action( 'admin_init', array( 'AH_Newsletter', 'maybe_install' ) );
		add_action( 'admin_init', array( self::class, 'maybe_raw_static_page' ) );
		add_action( 'init', array( self::class, 'handle_newsletter_unsub' ) );
		add_action( 'init', array( self::class, 'enable_admin_error_display' ), 1 );
		add_action( 'admin_notices', array( self::class, 'show_admin_db_error_notice' ) );
	}

	public static function enable_admin_error_display(): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( empty( $_GET['showerror'] ) || $_GET['showerror'] !== 'true' ) {
			return;
		}

		ini_set( 'display_errors', '1' );
		error_reporting( E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT );

		global $wpdb;
		$wpdb->show_errors();

		register_shutdown_function( static function () {
			$e = error_get_last();
			if ( ! $e || ! in_array( $e['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ), true ) ) {
				return;
			}
			echo '<div style="position:fixed;bottom:0;left:0;right:0;z-index:999999;'
				. 'background:#fff0f0;border-top:4px solid #dc2626;padding:16px 20px;'
				. 'font-family:monospace;font-size:13px;line-height:1.6;">';
			echo '<strong style="color:#dc2626;">&#9888; Fatal Error (admin-only)</strong><br>';
			echo esc_html( $e['message'] ) . '<br>';
			echo '<small style="color:#6b7280;">'
				. esc_html( str_replace( ABSPATH, '', $e['file'] ) )
				. ' &nbsp;line&nbsp;' . (int) $e['line'] . '</small>';
			echo '</div>';
		} );
	}

	public static function show_admin_db_error_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		if ( ! empty( $_GET['dberr'] ) ) {
			$msg = sanitize_text_field( wp_unslash( $_GET['dberr'] ) );
			echo '<div class="notice notice-error"><p>'
				. '<strong>Database Error (admin-only):</strong> '
				. esc_html( $msg )
				. '</p></div>';
		}

		global $wpdb;
		if ( $wpdb->last_error ) {
			echo '<div class="notice notice-error"><p>'
				. '<strong>Database Error (admin-only):</strong> '
				. esc_html( $wpdb->last_error )
				. '</p></div>';
		}
	}

	public static function handle_newsletter_unsub(): void {
		if ( empty( $_GET['ah_nl_unsub'] ) || empty( $_GET['email'] ) || empty( $_GET['token'] ) ) {
			return;
		}
		AH_Newsletter::maybe_install();
		$email = sanitize_email( wp_unslash( $_GET['email'] ) );
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
		if ( ! is_email( $email ) || ! hash_equals( AH_Newsletter::unsub_token( $email ), $token ) ) {
			wp_die( 'Invalid unsubscribe link.', 'Unsubscribe', array( 'response' => 400 ) );
		}
		AH_Newsletter::unsubscribe( $email );
		wp_die(
			'<p style="font-family:sans-serif;font-size:18px;text-align:center;padding:60px 20px">You have been unsubscribed successfully. You will no longer receive newsletters from ' . esc_html( get_bloginfo( 'name' ) ) . '.</p>',
			'Unsubscribed',
			array( 'response' => 200 )
		);
	}

	public static function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'ah-' ) === false ) return;

		wp_enqueue_style(
			'ah-admin-style',
			AH_THEME_URL . '/admin/assets/css/admin-style.css',
			array( 'wp-color-picker' ),
			AH_THEME_VERSION
		);
		wp_add_inline_style( 'ah-admin-style', self::sidebar_icons_css() );

		wp_enqueue_script(
			'ah-admin-script',
			AH_THEME_URL . '/admin/assets/js/admin-script.js',
			array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker', 'media-upload', 'thickbox' ),
			AH_THEME_VERSION,
			true
		);
		wp_localize_script( 'ah-admin-script', 'ahAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ah_admin_nonce' ),
			'confirm' => __( 'Are you sure? This cannot be undone.', 'ah-theme' ),
		) );

		wp_enqueue_media();
		add_thickbox();

		// Output confirm modal HTML + JS in admin_footer so both are in DOM together
		add_action( 'admin_footer', function () {
			if ( did_action( 'ah_confirm_modal_html' ) ) return;
			do_action( 'ah_confirm_modal_html' );
			echo '<div id="ah-confirm-modal" class="ah-modal-overlay"><div class="ah-modal"><div class="ah-modal-header"><div class="ah-modal-icon"><span class="dashicons dashicons-warning"></span></div><h3 class="ah-modal-title">Confirm Action</h3></div><div class="ah-modal-body"><p class="ah-modal-message"></p></div><div class="ah-modal-footer"><button type="button" class="ah-btn ah-modal-cancel">Cancel</button><button type="button" class="ah-btn ah-modal-confirm">Confirm</button></div></div></div>';
			echo '<script>
			(function(){
				var modal = document.getElementById("ah-confirm-modal");
				if (!modal) return;
				var titleEl = modal.querySelector(".ah-modal-title");
				var msgEl = modal.querySelector(".ah-modal-message");
				var confirmBtn = modal.querySelector(".ah-modal-confirm");
				var cancelBtn = modal.querySelector(".ah-modal-cancel");
				var pendingHref = "";
				var pendingTarget = "";
				function openModal(href, title, message, target) {
					pendingHref = href;
					pendingTarget = target || "";
					titleEl.textContent = title || "Confirm Action";
					msgEl.textContent = message || "Are you sure? This cannot be undone.";
					confirmBtn.textContent = "Yes, Delete";
					modal.classList.add("is-visible");
				}
				function closeModal() {
					modal.classList.remove("is-visible");
					pendingHref = "";
					pendingTarget = "";
				}
				document.addEventListener("click", function(e){
					var btn = e.target.closest(".ah-confirm-delete");
					if (!btn) return;
					e.preventDefault();
					e.stopPropagation();
					openModal(btn.getAttribute("href"), btn.dataset.title, btn.dataset.confirm, btn.dataset.target);
				});
				confirmBtn.addEventListener("click", function(e){
					e.preventDefault();
					if (pendingTarget === "_blank" && pendingHref) {
						window.open(pendingHref, "_blank");
						closeModal();
					} else if (pendingHref) {
						window.location.href = pendingHref;
					}
				});
				cancelBtn.addEventListener("click", function(e){
					e.preventDefault();
					closeModal();
				});
				modal.addEventListener("click", function(e){
					if (e.target === modal) closeModal();
				});
				document.addEventListener("keydown", function(e){
					if (e.key === "Escape" && modal.classList.contains("is-visible")) closeModal();
				});
			})();
			</script>';
		}, 1 );

		if (
			strpos( $hook, 'ah-page-builder' ) !== false ||
			strpos( $hook, 'ah-pages' ) !== false ||
			strpos( $hook, 'ah-posts' ) !== false
		) {
			wp_enqueue_editor();
		}
	}

	public static function clean_admin_bar( \WP_Admin_Bar $bar ): void {
		$bar->remove_node( 'new-post' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=adn_purge_cache&_wp_http_referer=' . rawurlencode( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ),
			'adn_purge_cache'
		);
		$bar->add_node( array(
			'id'    => 'adn-purge-cache',
			'title' => 'Purge Cache',
			'href'  => esc_url( $url ),
			'meta'  => array( 'title' => 'Force all browsers to reload CSS/JS' ),
		) );
	}

	public static function handle_purge_cache(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.', 403 );
		}
		check_admin_referer( 'adn_purge_cache' );
		update_option( 'adn_asset_ver', (string) time(), false );
		$back = isset( $_GET['_wp_http_referer'] ) ? wp_unslash( $_GET['_wp_http_referer'] ) : admin_url();
		wp_safe_redirect( sanitize_url( $back ) );
		exit;
	}

	public static function redirect( string $url ): void {
		if ( ! headers_sent() ) {
			wp_safe_redirect( $url );
			exit;
		}

		printf(
			'<script>window.location.href = %s;</script><noscript><meta http-equiv="refresh" content="0;url=%s"></noscript>',
			wp_json_encode( $url ),
			esc_url( $url )
		);
		exit;
	}

	private static function sidebar_icons_css(): string {
		return <<<'CSS'
#adminmenu .wp-submenu a[href*="page=ah-"]::before {
	font-family: dashicons !important; font-size:15px; display:inline-block;
	vertical-align:middle; margin-right:6px; opacity:.75; line-height:1;
	font-weight:400; -webkit-font-smoothing:antialiased;
}
#adminmenu .wp-submenu a[href$="page=ah-dashboard"]::before    { content:"\f226"; }
#adminmenu .wp-submenu a[href*="page=ah-settings"]::before     { content:"\f108"; }
#adminmenu .wp-submenu a[href*="page=ah-pages"]::before        { content:"\f105"; }
#adminmenu .wp-submenu a[href*="page=ah-media"]::before        { content:"\f128"; }
#adminmenu .wp-submenu a[href*="page=ah-posts"]::before        { content:"\f122"; }
#adminmenu .wp-submenu a[href*="page=ah-news-bar"]::before     { content:"\f463"; }
#adminmenu .wp-submenu a[href*="page=ah-navigation"]::before   { content:"\f333"; }
#adminmenu .wp-submenu a[href*="page=ah-home"]::before         { content:"\f102"; }
#adminmenu .wp-submenu a[href*="page=ah-services"]::before     { content:"\f313"; }
#adminmenu .wp-submenu a[href*="page=ah-client-stories"]::before { content:"\f109"; }
#adminmenu .wp-submenu a[href*="page=ah-reviews"]::before      { content:"\f205"; }
#adminmenu .wp-submenu a[href*="page=ah-faqs"]::before         { content:"\f223"; }
#adminmenu .wp-submenu a[href*="page=ah-taxonomy"]::before     { content:"\f323"; }
#adminmenu .wp-submenu a[href*="page=ah-contact"]::before      { content:"\f466"; }
#adminmenu .wp-submenu a[href*="page=ah-submissions"]::before  { content:"\f465"; }
#adminmenu .wp-submenu a[href*="page=ah-page-builder"]::before { content:"\f116"; }
#adminmenu .wp-submenu a[href*="page=ah-form-builder"]::before { content:"\f468"; }
#adminmenu .wp-submenu a[href*="page=ah-static-pages"]::before { content:"\f105"; }
#adminmenu .wp-submenu a[href*="page=ah-file-links"]::before   { content:"\f501"; }
#adminmenu .wp-submenu a[href*="page=ah-import"]::before       { content:"\f181"; }
#adminmenu .wp-submenu a[href*="page=ah-audit"]::before        { content:"\f174"; }
#adminmenu .wp-submenu a[href*="page=ah-admin-actions"]::before{ content:"\f534"; }
#adminmenu .wp-submenu a[href*="page=ah-help"]::before         { content:"\f223"; }
#adminmenu .wp-submenu a[href*="page=ah-workflow-manager"]::before { content:"\f211"; }
#adminmenu .wp-submenu a[href*="page=ah-analytics"]::before   { content:"\f185"; }
#adminmenu .wp-submenu a[href*="page=ah-banners"]::before   { content:"\f232"; }
#adminmenu .wp-submenu a[href*="page=ah-spotlights"]::before   { content:"\f339"; }
#adminmenu .wp-submenu a[href*="page=ah-notices"]::before   { content:"\f148"; }
#adminmenu .wp-submenu a[href*="page=ah-newsletter"]::before   { content:"\f534"; }
#adminmenu .wp-submenu a[href*="page=ah-ref-notes"]::before   { content:"\f123"; }
#adminmenu .wp-submenu a[href*="page=ah-redirects"]::before   { content:"\f237"; }
#adminmenu .wp-submenu a[href*="page=ah-custom-code"]::before  { content:"\f475"; }
#adminmenu .wp-submenu a[href*="page=ah-visitors"]::before     { content:"\f307"; }
#adminmenu .wp-submenu a[href*="page=ah-featured-in"]::before  { content:"\f529"; }
#adminmenu .wp-submenu a[href*="page=ah-resources"]::before    { content:"\f233"; }

#adminmenu .wp-submenu li:has(> a[href*="page=ah-posts"]),
#adminmenu .wp-submenu li:has(> a[href*="page=ah-contact"]),
#adminmenu .wp-submenu li:has(> a[href*="page=ah-audit"])
{
	border-top:1px solid rgba(255,255,255,.12); margin-top:6px; padding-top:4px;
}
CSS;
	}

	public static function maybe_raw_static_page(): void {
		if ( empty( $_GET['page'] ) || 'ah-static-pages' !== $_GET['page'] ) {
			return;
		}
		$is_raw    = isset( $_GET['raw'] )    && '1'    === (string) $_GET['raw'];
		$is_themed = isset( $_GET['themed'] ) && '1'    === (string) $_GET['themed'];
		if ( ! $is_raw && ! $is_themed ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied.' );
		}

		$slug = sanitize_file_name( wp_unslash( $_GET['edit'] ?? '' ) );
		$html = '';
		if ( $slug !== '' && class_exists( 'AH_Static_Pages_Model' ) ) {
			$html = ( new AH_Static_Pages_Model() )->get_html( $slug );
		}

		header( 'Content-Type: text/html; charset=UTF-8' );

		if ( '' === $html ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:sans-serif;padding:40px;color:#374151;"><p>No content found' . ( $slug ? ' for <code>' . esc_html( $slug ) . '</code>' : '' ) . '.</p></body></html>';
			exit;
		}

		if ( $is_raw ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $html;
			exit;
		}

		$theme_uri = get_template_directory_uri();
		$ver       = defined( 'ADN_THEME_VERSION' ) ? ADN_THEME_VERSION : '1';
		$css_files = array(
			$theme_uri . '/assets/css/variables.css?ver=' . $ver,
			$theme_uri . '/assets/css/chrome.css?ver='    . $ver,
			$theme_uri . '/assets/css/main.css?ver='      . $ver,
			$theme_uri . '/assets/css/common.css?ver='    . $ver,
			$theme_uri . '/assets/css/components.css?ver='. $ver,
			$theme_uri . '/assets/css/fastyles.css?ver='  . $ver,
		);

		$inject = "\n<!-- Advaith Homes Theme CSS (Match Theme preview) -->\n";
		$inject .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		$inject .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		foreach ( $css_files as $url ) {
			$inject .= '<link rel="stylesheet" href="' . esc_url( $url ) . '">' . "\n";
		}
		$inject .= "<!-- /Theme CSS -->\n";

		if ( stripos( $html, '</head>' ) !== false ) {
			$html = str_ireplace( '</head>', $inject . '</head>', $html );
		} elseif ( stripos( $html, '<head>' ) !== false ) {
			$html = str_ireplace( '<head>', '<head>' . $inject, $html );
		} else {
			$html = $inject . $html;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
		exit;
	}
}
