<?php
defined( 'ABSPATH' ) || exit;

class AH_Admin_Bootstrap {

	public static function init(): void {
		add_action( 'admin_menu', array( 'AH_Admin_Menus', 'register' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'admin_bar_menu', array( self::class, 'clean_admin_bar' ), 999 );
		add_action( 'admin_post_ah_cms_nav', array( self::class, 'handle_navigation' ) );
		AH_Ajax_Handlers::init();
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
	}

	public static function redirect( string $url ): void {
		if ( ! headers_sent() ) {
			wp_safe_redirect( $url );
			exit;
		}

		$url = esc_url( $url );
		printf(
			'<script>window.location.href = %s;</script><noscript><meta http-equiv="refresh" content="0;url=%s"></noscript>',
			wp_json_encode( $url ),
			esc_attr( $url )
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
#adminmenu .wp-submenu a[href*="page=ah-about"]::before        { content:"\f488"; }
#adminmenu .wp-submenu a[href*="page=ah-team"]::before         { content:"\f307"; }
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
#adminmenu .wp-submenu li:has(> a[href*="page=ah-posts"]),
#adminmenu .wp-submenu li:has(> a[href*="page=ah-contact"]),
#adminmenu .wp-submenu li:has(> a[href*="page=ah-team"]),
#adminmenu .wp-submenu li:has(> a[href*="page=ah-audit"]) {
	border-top:1px solid rgba(255,255,255,.12); margin-top:6px; padding-top:4px;
}
CSS;
	}

	public static function handle_navigation(): void {
		check_admin_referer( 'ah_theme_nav' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$keys = array( 'buying', 'finance', 'legal', 'news', 'services' );
		$vis  = array();
		foreach ( $keys as $key ) {
			$vis[ $key ] = isset( $_POST['nav_vis'][ $key ] ) ? 1 : 0;
		}
		update_option( 'ah_nav_visibility', wp_json_encode( $vis ) );

		$labels = array();
		$label_defaults = array(
			'buying'   => 'Buying',
			'finance'  => 'Finance',
			'legal'    => 'Legal & Surveys',
			'news'     => 'News & Guides',
			'services' => 'Services',
		);
		foreach ( $label_defaults as $key => $default ) {
			$labels[ $key ] = sanitize_text_field( $_POST['nav_label'][ $key ] ?? $default );
		}
		update_option( 'ah_nav_top_labels', wp_json_encode( $labels ) );

		$links = array();
		foreach ( array( 'news', 'services' ) as $key ) {
			$links[ $key ] = array(
				'label' => sanitize_text_field( $_POST['nav_link'][ $key ]['label'] ?? '' ),
				'url'   => self::clean_nav_url( $_POST['nav_link'][ $key ]['url'] ?? '' ),
			);
		}
		update_option( 'ah_nav_static_links', wp_json_encode( $links ) );

		update_option(
			'ah_nav_cta',
			wp_json_encode(
				array(
					'label' => sanitize_text_field( $_POST['nav_cta_label'] ?? 'Get Help' ),
					'url'   => self::clean_nav_url( $_POST['nav_cta_url'] ?? '/contact/' ),
				)
			)
		);

		$valid_sections = array( 'buying', 'finance', 'legal', 'footer' );
		$static_links   = array();
		foreach ( (array) ( $_POST['static_nav'] ?? array() ) as $item ) {
			$slug = sanitize_title( $item['slug'] ?? '' );
			if ( ! $slug ) continue;

			$static_links[] = array(
				'slug'    => $slug,
				'label'   => sanitize_text_field( $item['label'] ?? '' ),
				'icon'    => sanitize_text_field( $item['icon'] ?? '' ),
				'section' => in_array( $item['section'] ?? '', $valid_sections, true ) ? $item['section'] : 'buying',
			);
		}
		update_option( 'ah_nav_static_page_links', wp_json_encode( $static_links ) );

		foreach ( array( 'buying', 'finance', 'legal' ) as $section ) {
			$items = array();
			foreach ( (array) ( $_POST[ $section . '_items' ] ?? array() ) as $item ) {
				$title = sanitize_text_field( $item['title'] ?? '' );
				if ( ! $title ) continue;

				$items[] = array(
					'icon'      => sanitize_text_field( $item['icon'] ?? '' ),
					'title'     => $title,
					'desc'      => sanitize_text_field( $item['desc'] ?? '' ),
					'slug'      => sanitize_title( ( $item['slug'] ?? '' ) ?: $title ),
					'highlight' => ! empty( $item['highlight'] ),
				);
			}
			update_option( 'ah_nav_' . $section . '_topics', wp_json_encode( $items ) );
		}

		self::redirect( add_query_arg( array( 'page' => 'ah-navigation', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
	}

	private static function clean_nav_url( string $url ): string {
		$url = trim( $url );
		if ( $url === '' ) return '';
		if ( preg_match( '#^https?://#i', $url ) ) return esc_url_raw( $url );

		return '/' . trim( sanitize_text_field( $url ), '/' ) . '/';
	}
}
