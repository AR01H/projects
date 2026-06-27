<?php
defined( 'ABSPATH' ) || exit;

class AH_Admin_Bootstrap {

	public static function init(): void {
		add_action( 'admin_menu', array( 'AH_Admin_Menus', 'register' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		add_action( 'admin_bar_menu', array( self::class, 'clean_admin_bar' ), 999 );
		add_action( 'admin_post_ah_cms_nav', array( self::class, 'handle_navigation' ) );
		add_action( 'admin_post_ah_delete_spotlight_item', array( self::class, 'handle_delete_spotlight_item' ) );
		add_action( 'admin_post_ah_delete_spotlight_term', array( self::class, 'handle_delete_spotlight_term' ) );
		add_action( 'admin_post_ah_save_notice',   array( self::class, 'handle_notice_save' ) );
		add_action( 'admin_post_ah_delete_notice', array( self::class, 'handle_notice_delete' ) );
		add_action( 'admin_post_ah_toggle_notice', array( self::class, 'handle_notice_toggle' ) );
		add_action( 'admin_post_ah_save_banners', array( self::class, 'handle_banners_save' ) );
		add_action( 'add_meta_boxes', array( self::class, 'register_post_metaboxes' ) );
		add_action( 'save_post', array( self::class, 'save_post_metabox' ), 10, 1 );
		AH_Ajax_Handlers::init();
		AH_Analytics_Ajax::init();

		// Ensure newsletter table exists.
		add_action( 'admin_init', array( 'AH_Newsletter', 'maybe_install' ) );

		// Raw HTML output for static pages (?raw=1 from the admin editor).
		add_action( 'admin_init', array( self::class, 'maybe_raw_static_page' ) );

		// Public unsubscribe endpoint: /?ah_nl_unsub=1&email=X&token=Y
		add_action( 'init', array( self::class, 'handle_newsletter_unsub' ) );

		// Show PHP/DB errors to logged-in admins instead of blank 500 pages.
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

		// Show PHP errors inline.
		ini_set( 'display_errors', '1' );
		error_reporting( E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT );

		// Make wpdb print DB errors inline immediately.
		global $wpdb;
		$wpdb->show_errors();

		// Catch PHP fatal errors and render them in a visible banner.
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

		// DB error passed back via redirect URL param (from save handlers).
		if ( ! empty( $_GET['dberr'] ) ) {
			$msg = sanitize_text_field( wp_unslash( $_GET['dberr'] ) );
			echo '<div class="notice notice-error"><p>'
				. '<strong>Database Error (admin-only):</strong> '
				. esc_html( $msg )
				. '</p></div>';
		}

		// Also show any live wpdb error from the current page render.
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

	public static function handle_navigation(): void {
		check_admin_referer( 'ah_cms_navigation' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$nav_items = array();
		foreach ( (array) ( $_POST['nav_items'] ?? array() ) as $item ) {
			$label = sanitize_text_field( $item['label'] ?? '' );
			if ( $label === '' ) {
				continue;
			}

			$type     = ( $item['type'] ?? 'link' ) === 'dropdown' ? 'dropdown' : 'link';
			$submenu  = array();
			foreach ( (array) ( $item['submenu'] ?? array() ) as $sub_item ) {
				$sub_label = sanitize_text_field( $sub_item['label'] ?? '' );
				$sub_url   = self::clean_nav_url( (string) ( $sub_item['url'] ?? '' ) );
				if ( $sub_label === '' || $sub_url === '' ) {
					continue;
				}

				$submenu[] = array(
					'label'       => $sub_label,
					'url'         => $sub_url,
					'description' => sanitize_text_field( $sub_item['description'] ?? '' ),
					'icon'        => sanitize_text_field( $sub_item['icon'] ?? '' ),
					'highlight'   => ! empty( $sub_item['highlight'] ),
				);
			}

			$nav_items[] = array(
				'id'          => sanitize_title( $item['id'] ?? $label ),
				'label'       => $label,
				'type'        => $type,
				'url'         => $type === 'link' ? self::clean_nav_url( (string) ( $item['url'] ?? '' ) ) : '',
				'visible'     => ! empty( $item['visible'] ),
				'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
				'description' => sanitize_text_field( $item['description'] ?? '' ),
				'panel_image' => esc_url_raw( $item['panel_image'] ?? '' ),
				'submenu'     => $submenu,
			);
		}

		update_option(
			'ah_cms_navigation',
			wp_json_encode( $nav_items )
		);
		update_option(
			'ah_cms_nav_cta',
			wp_json_encode(
				array(
					'label' => sanitize_text_field( $_POST['nav_cta']['label'] ?? 'Get Help' ),
					'url'   => self::clean_nav_url( (string) ( $_POST['nav_cta']['url'] ?? '/contact/' ) ),
				)
			)
		);

		$footer_columns = array();
		foreach ( (array) ( $_POST['footer_columns'] ?? array() ) as $column ) {
			$title = sanitize_text_field( $column['title'] ?? '' );
			$items = array();
			foreach ( (array) ( $column['items'] ?? array() ) as $item ) {
				$label = sanitize_text_field( $item['label'] ?? '' );
				$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
				if ( $label === '' || $url === '' ) {
					continue;
				}

				$items[] = array(
					'label'     => $label,
					'url'       => $url,
					'highlight' => ! empty( $item['highlight'] ),
				);
			}

			if ( $title !== '' || ! empty( $items ) ) {
				$footer_columns[] = array(
					'title' => $title ?: 'Links',
					'items' => $items,
				);
			}
		}

		$legal_links = array();
		foreach ( (array) ( $_POST['footer_legal_links'] ?? array() ) as $item ) {
			$label = sanitize_text_field( $item['label'] ?? '' );
			$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
			if ( $label === '' || $url === '' ) {
				continue;
			}

			$legal_links[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}

		update_option(
			'ah_cms_footer',
			wp_json_encode(
				array(
					'brand_description' => wp_kses_post( $_POST['footer_brand_description'] ?? '' ),
					'badge_text'        => sanitize_text_field( $_POST['footer_badge_text'] ?? '' ),
					'columns'           => $footer_columns,
					'cta'               => array(
						'label' => sanitize_text_field( $_POST['footer_cta']['label'] ?? '' ),
						'url'   => self::clean_nav_url( (string) ( $_POST['footer_cta']['url'] ?? '/contact/' ) ),
					),
					'legal_links'       => $legal_links,
				)
			)
		);

		self::redirect( add_query_arg( array( 'page' => 'ah-navigation', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
	}

	public static function handle_delete_spotlight_item(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorised' );
		}
		check_admin_referer( 'ah_del_sp_item' );

		$del_id = (int) ( $_POST['delete_item'] ?? 0 );
		$term_id = (int) ( $_POST['term_id'] ?? 0 );

		$items_model = new AH_Spotlights_Model();
		if ( $del_id ) {
			$items_model->delete( $del_id );
		}

		$redirect = add_query_arg( array( 'page' => 'ah-spotlights', 'tab' => 'items', 'term_id' => $term_id, 'deleted' => 1, 'deleted_id' => $del_id ), admin_url( 'admin.php' ) );
		if ( ! headers_sent() ) {
			wp_safe_redirect( $redirect );
			exit;
		}
		// fallback JS redirect if headers already sent
		echo '<script>window.location.href = ' . wp_json_encode( $redirect ) . ';</script>';
		exit;
	}

	public static function handle_delete_spotlight_term(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorised' );
		}
		check_admin_referer( 'ah_del_sp_term' );

		$del_id = (int) ( $_POST['delete_term'] ?? 0 );

		$terms_model = new AH_Spotlight_Terms_Model();
		if ( $del_id ) {
			$terms_model->delete_with_items( $del_id );
		}

		$redirect = add_query_arg( array( 'page' => 'ah-spotlights', 'tab' => 'terms', 'deleted' => 1, 'deleted_id' => $del_id ), admin_url( 'admin.php' ) );
		if ( ! headers_sent() ) {
			wp_safe_redirect( $redirect );
			exit;
		}
		echo '<script>window.location.href = ' . wp_json_encode( $redirect ) . ';</script>';
		exit;
	}

	public static function get_navigation_data(): array {
		$opt = self::decode_option( get_option( 'ah_cms_navigation', array() ) );
		if ( empty( $opt ) ) {
			$opt = self::decode_option( get_option( 'ah_theme_navigation', array() ) );
		}

		return self::normalize_navigation( is_array( $opt ) ? $opt : array() );
	}

	public static function get_nav_cta_data(): array {
		$defaults = array(
			'label' => 'Get Help',
			'url'   => '/contact/',
		);
		$opt = self::decode_option( get_option( 'ah_cms_nav_cta', array() ) );
		if ( empty( $opt ) ) {
			$opt = self::decode_option( get_option( 'ah_nav_cta', array() ) );
		}

		return array_merge( $defaults, is_array( $opt ) ? $opt : array() );
	}

	public static function get_footer_data(): array {
		$opt = self::decode_option( get_option( 'ah_cms_footer', array() ) );
		if ( empty( $opt ) ) {
			$opt = self::decode_option( get_option( 'ah_theme_footer', array() ) );
		}

		return self::normalize_footer( is_array( $opt ) ? $opt : array() );
	}

	public static function get_nav_link_suggestions(): array {
		$suggestions = array();

		$push = static function ( string $label, string $url, string $type ) use ( &$suggestions ): void {
			$key = strtolower( $label . '|' . $url );
			if ( isset( $suggestions[ $key ] ) ) {
				return;
			}

			$suggestions[ $key ] = array(
				'label' => $label,
				'url'   => $url,
				'type'  => $type,
			);
		};

		$push( 'Home', home_url( '/' ), 'page' );
		$push( 'Blog', home_url( '/blog/' ), 'page' );
		$push( 'Services', home_url( '/services/' ), 'page' );
		$push( 'Contact', home_url( '/contact/' ), 'page' );

		foreach ( get_pages( array( 'post_status' => array( 'publish', 'draft', 'private' ), 'sort_column' => 'post_title' ) ) as $page ) {
			$push(
				$page->post_title ?: ucwords( str_replace( '-', ' ', $page->post_name ) ),
				get_permalink( $page->ID ) ?: home_url( '/' . $page->post_name . '/' ),
				'wp-page'
			);
		}

		foreach ( get_posts( array(
			'post_type'      => 'post',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) ) as $post ) {
			$push(
				get_the_title( $post ) ?: 'Post #' . $post->ID,
				get_permalink( $post ) ?: home_url( '/?p=' . $post->ID ),
				'post'
			);
		}

		$static_dir = trailingslashit( get_template_directory() ) . 'static/';
		foreach ( glob( $static_dir . '*.html' ) ?: array() as $file ) {
			$slug  = basename( $file, '.html' );
			$label = ucwords( str_replace( '-', ' ', $slug ) );
			$page  = get_page_by_path( $slug );
			$push(
				$label,
				$page ? get_permalink( $page->ID ) : home_url( '/' . $slug . '/' ),
				'static-page'
			);
		}

		return array_values( $suggestions );
	}

	private static function decode_option( $value ) {
		if ( is_string( $value ) ) {
			return json_decode( $value, true ) ?: array();
		}

		return $value;
	}

	private static function normalize_navigation( array $items ): array {
		$normalized = array();
		foreach ( $items as $index => $item ) {
			$item  = (array) $item;
			$label = sanitize_text_field( $item['label'] ?? '' );
			if ( $label === '' ) {
				continue;
			}

			$type    = ( $item['type'] ?? 'link' ) === 'dropdown' ? 'dropdown' : 'link';
			$submenu = array();
			foreach ( (array) ( $item['submenu'] ?? array() ) as $sub_item ) {
				$sub_item  = (array) $sub_item;
				$sub_label = sanitize_text_field( $sub_item['label'] ?? '' );
				$sub_url   = self::clean_nav_url( (string) ( $sub_item['url'] ?? '' ) );
				if ( $sub_label === '' || $sub_url === '' ) {
					continue;
				}

				$submenu[] = array(
					'label'       => $sub_label,
					'url'         => $sub_url,
					'description' => sanitize_text_field( $sub_item['description'] ?? '' ),
					'icon'        => sanitize_text_field( $sub_item['icon'] ?? '' ),
					'highlight'   => ! empty( $sub_item['highlight'] ),
				);
			}

			$normalized[] = array(
				'id'          => sanitize_title( $item['id'] ?? $label ?: 'nav-' . $index ),
				'label'       => $label,
				'type'        => $type,
				'url'         => $type === 'link' ? self::clean_nav_url( (string) ( $item['url'] ?? '' ), home_url( '/' ) ) : '',
				'visible'     => isset( $item['visible'] ) ? (bool) $item['visible'] : true,
				'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
				'description' => sanitize_text_field( $item['description'] ?? '' ),
				'submenu'     => $submenu,
			);
		}

		return $normalized;
	}

	private static function normalize_footer( array $footer ): array {
		$columns = array();
		foreach ( (array) ( $footer['columns'] ?? array() ) as $column ) {
			$column = (array) $column;
			$title  = sanitize_text_field( $column['title'] ?? '' );
			$items  = array();
			foreach ( (array) ( $column['items'] ?? array() ) as $item ) {
				$item  = (array) $item;
				$label = sanitize_text_field( $item['label'] ?? '' );
				$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
				if ( $label === '' || $url === '' ) {
					continue;
				}

				$items[] = array(
					'label'     => $label,
					'url'       => $url,
					'highlight' => ! empty( $item['highlight'] ),
				);
			}

			if ( $title !== '' || ! empty( $items ) ) {
				$columns[] = array(
					'title' => $title ?: 'Links',
					'items' => $items,
				);
			}
		}

		$legal_links = array();
		foreach ( (array) ( $footer['legal_links'] ?? array() ) as $item ) {
			$item  = (array) $item;
			$label = sanitize_text_field( $item['label'] ?? '' );
			$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
			if ( $label === '' || $url === '' ) {
				continue;
			}

			$legal_links[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}

		return array(
			'brand_description' => wp_kses_post( $footer['brand_description'] ?? '' ),
			'badge_text'        => sanitize_text_field( $footer['badge_text'] ?? '' ),
			'columns'           => $columns,
			'cta'               => array(
				'label' => sanitize_text_field( $footer['cta']['label'] ?? '' ),
				'url'   => self::clean_nav_url( (string) ( $footer['cta']['url'] ?? '' ), '/contact/' ),
			),
			'legal_links'       => $legal_links,
		);
	}

	// ── WP Editor metabox: CMS Post Settings ─────────────────────────────────────

	public static function register_post_metaboxes(): void {
		add_meta_box(
			'ah-cms-post-settings',
			'CMS Post Settings',
			array( self::class, 'render_post_metabox' ),
			'post',
			'side',
			'default'
		);
	}

	public static function render_post_metabox( WP_Post $post ): void {
		wp_nonce_field( 'ah_cms_post_meta_save', 'ah_cms_post_meta_nonce' );
		$editor_mode  = get_post_meta( $post->ID, '_ah_editor_mode', true ) ?: 'gutenberg';
		$is_featured  = (bool) get_post_meta( $post->ID, '_ah_is_featured',  true );
		$is_popular   = (bool) get_post_meta( $post->ID, '_ah_is_popular',   true );
		$is_suggested = (bool) get_post_meta( $post->ID, '_ah_is_suggested', true );
		$form_edit_url = add_query_arg(
			array( 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $post->ID ),
			admin_url( 'admin.php' )
		);
		?>
		<style>
		.ah-mb .ah-mb-row{margin-bottom:12px}
		.ah-mb label{display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px}
		.ah-mb .ah-mb-hint{font-size:11px;color:#888;margin-top:3px}
		.ah-mb .ah-mb-sep{border:0;border-top:1px solid #e0e0e0;margin:12px 0}
		.ah-mb .ah-tp-group{margin-bottom:8px}
		.ah-mb .ah-tp-head{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#888;margin-bottom:4px}
		.ah-mb .ah-tp-opts{display:flex;flex-wrap:wrap;gap:4px}
		.ah-mb .ah-tp-chip{display:inline-flex;align-items:center;gap:4px;font-size:12px;padding:2px 7px;border:1px solid #cdd;border-radius:10px;cursor:pointer;background:#f9f9f9;user-select:none}
		.ah-mb .ah-tp-chip input{margin:0}
		.ah-mb .ah-tp-chip:has(input:checked){background:#e8f0fe;border-color:#4f7cf5;color:#1a49c4;font-weight:600}
		</style>
		<div class="ah-mb">
			<?php if ( $editor_mode === 'custom' ) : ?>
			<div class="ah-mb-row">
				<a href="<?php echo esc_url( $form_edit_url ); ?>" class="button button-secondary" style="width:100%;text-align:center;display:block;box-sizing:border-box;">
					&larr; Back to Form Editor
				</a>
			</div>
			<hr class="ah-mb-sep">
			<?php endif; ?>

			<div class="ah-mb-row">
				<label>
					<input type="checkbox" name="ah_is_featured" value="1" <?php checked( $is_featured ); ?>>
					<strong>⭐ Featured Post</strong>
				</label>
				<p class="ah-mb-hint">Show in featured sections across the site</p>
			</div>
			<div class="ah-mb-row">
				<label>
					<input type="checkbox" name="ah_is_popular" value="1" <?php checked( $is_popular ); ?>>
					<strong>🔥 Popular Post</strong>
				</label>
			</div>
			<div class="ah-mb-row">
				<label>
					<input type="checkbox" name="ah_is_suggested" value="1" <?php checked( $is_suggested ); ?>>
					<strong>💡 Suggested Post</strong>
				</label>
			</div>

			<hr class="ah-mb-sep">
			<p style="font-size:12px;font-weight:600;margin:0 0 8px;">CMS Taxonomy Terms</p>
			<?php
			if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
				$model  = new AH_Content_Taxonomy_Model();
				$sel    = $model->get_term_ids( 'wp_post', $post->ID );
				$groups = $model->get_active_terms_grouped();
				if ( empty( $groups ) ) {
					echo '<p style="font-size:12px;color:#888;margin:0;">No taxonomy terms yet - add some in <a href="' . esc_url( admin_url( 'admin.php?page=ah-taxonomy' ) ) . '">Taxonomies</a>.</p>';
				} else {
					foreach ( $groups as $group ) {
						if ( empty( $group['items'] ) ) continue;
						?>
						<div class="ah-tp-group">
							<div class="ah-tp-head"><?php echo esc_html( $group['label'] ); ?></div>
							<div class="ah-tp-opts">
								<?php foreach ( $group['items'] as $term ) : ?>
									<label class="ah-tp-chip">
										<input type="checkbox" name="taxonomy_ids[]"
											value="<?php echo esc_attr( $term->id ); ?>"
											<?php checked( in_array( (int) $term->id, $sel, true ) ); ?>>
										<span><?php echo esc_html( $term->name ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<?php
					}
				}
			}
			?>
		</div>
		<?php
	}

	public static function save_post_metabox( int $post_id ): void {
		if ( ! isset( $_POST['ah_cms_post_meta_nonce'] ) ) return;
		if ( ! wp_verify_nonce( $_POST['ah_cms_post_meta_nonce'], 'ah_cms_post_meta_save' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		update_post_meta( $post_id, '_ah_is_featured',  ! empty( $_POST['ah_is_featured'] )  ? '1' : '0' );
		update_post_meta( $post_id, '_ah_is_popular',   ! empty( $_POST['ah_is_popular'] )   ? '1' : '0' );
		update_post_meta( $post_id, '_ah_is_suggested', ! empty( $_POST['ah_is_suggested'] ) ? '1' : '0' );

		if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
			$taxonomy_ids = array_map( 'intval', (array) ( $_POST['taxonomy_ids'] ?? array() ) );
			( new AH_Content_Taxonomy_Model() )->sync_terms( 'wp_post', $post_id, $taxonomy_ids );
		}
	}

	public static function handle_notice_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		check_admin_referer( 'ah_save_site_notice', 'ah_sn_nonce' );

		$edit_id = (int) ( $_POST['edit_id'] ?? 0 );
		$errors  = array();

		$v_title = trim( sanitize_text_field( $_POST['title'] ?? '' ) );
		if ( $v_title === '' ) $errors[] = 'Title is required.';
		if ( ( $_POST['trigger_type'] ?? '' ) === '' ) $errors[] = 'Display Trigger must be selected.';
		if ( ( $_POST['scope'] ?? '' ) === 'slugs' && trim( $_POST['slugs'] ?? '' ) === '' ) $errors[] = 'Enter at least one page slug.';
		if ( ( $_POST['trigger_type'] ?? '' ) === 'delay' && (int) ( $_POST['trigger_delay'] ?? 0 ) < 1 ) $errors[] = 'Delay must be at least 1 second.';

		if ( ! empty( $errors ) ) {
			$back = add_query_arg( array(
				'page'   => 'ah-notices',
				'action' => $edit_id > 0 ? 'edit' : 'add',
				'id'     => $edit_id ?: null,
				'err'    => implode( '|', array_map( 'urlencode', $errors ) ),
			), admin_url( 'admin.php' ) );
			wp_safe_redirect( $back );
			exit;
		}

		global $wpdb;
		$model = new AH_Site_Notices_Model();
		$model->save_notice( $edit_id, wp_unslash( $_POST ) );

		if ( $wpdb->last_error ) {
			wp_safe_redirect( add_query_arg( array(
				'page'   => 'ah-notices',
				'action' => $edit_id > 0 ? 'edit' : 'add',
				'id'     => $edit_id ?: null,
				'dberr'  => rawurlencode( $wpdb->last_error ),
			), admin_url( 'admin.php' ) ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'ah-notices', 'flash' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_notice_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		check_admin_referer( 'ah_del_sn' );
		$model = new AH_Site_Notices_Model();
		$model->delete( (int) ( $_GET['delete_id'] ?? 0 ) );
		wp_safe_redirect( add_query_arg( array( 'page' => 'ah-notices', 'flash' => 'deleted' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_notice_toggle(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		check_admin_referer( 'ah_toggle_sn' );
		$model = new AH_Site_Notices_Model();
		$row   = $model->find( (int) ( $_GET['toggle_id'] ?? 0 ) );
		if ( $row ) {
			$model->set_status( (int) $row->id, $row->status === 'active' ? 'inactive' : 'active' );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'ah-notices', 'flash' => 'updated' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_banners_save(): void {
		check_admin_referer( 'ah_banners_save' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once AH_THEME_DIR . '/helper/class-banners-helper.php';

		$rows     = isset( $_POST['banners'] ) && is_array( $_POST['banners'] ) ? wp_unslash( $_POST['banners'] ) : array();
		$banners  = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) continue;
			$banners[] = array(
				'image'        => $row['image']        ?? '',
				'image_mobile' => $row['image_mobile'] ?? '',
				'subtitle'    => $row['subtitle']    ?? '',
				'title'       => $row['title']       ?? '',
				'description' => $row['description']  ?? '',
				'btn_text'    => $row['btn_text']    ?? '',
				'btn_url'     => $row['btn_url']     ?? '',
				'btn_target'  => $row['btn_target']  ?? '_self',
				'text_align'  => $row['text_align']  ?? 'center',
				'text_pos'    => $row['text_pos']    ?? 'middle',
				'overlay'     => $row['overlay']     ?? '',
				'status'      => $row['status']      ?? 'active',
			);
		}

		AH_Banners_Helper::save_all( $banners );
		AH_Banners_Helper::save_autoplay( (int) ( $_POST['autoplay_ms'] ?? 5000 ) );

		if ( class_exists( 'AH_DB_Helper' ) ) {
			AH_DB_Helper::log_action( 'update', 'home_banners', 0, array( 'count' => count( $banners ) ) );
		}

		wp_redirect( add_query_arg( array( 'page' => 'ah-banners', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private static function clean_nav_url( string $url, string $fallback = '' ): string {
		$url = trim( wp_unslash( $url ) );
		if ( $url === '' ) return $fallback;
		if ( preg_match( '#^(https?:)?//#i', $url ) || strpos( $url, '#' ) === 0 || strpos( $url, 'mailto:' ) === 0 || strpos( $url, 'tel:' ) === 0 ) {
			return esc_url_raw( $url );
		}

		return '/' . trim( sanitize_text_field( $url ), '/' ) . '/';
	}

	/**
	 * Intercepts admin.php?page=ah-static-pages&edit=SLUG&raw=1
	 * and outputs the raw HTML content with no WordPress or admin wrapper.
	 */
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

		// ── Themed preview: inject the site's CSS into the page <head> ──────
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

		// Insert before </head> if tag exists, otherwise prepend after <head> or at the top.
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
