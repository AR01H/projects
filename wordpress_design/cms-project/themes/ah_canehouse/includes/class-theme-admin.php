<?php
defined( 'ABSPATH' ) || exit;

class CH_Theme_Admin {

	public static function init(): void {
		add_action( 'admin_menu',                      [ self::class, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts',           [ self::class, 'enqueue_assets' ] );
		add_action( 'admin_post_ch_theme_schema',      [ self::class, 'handle_schema'  ] );
		add_action( 'admin_post_ch_theme_seed',        [ self::class, 'handle_seed'    ] );
		add_action( 'admin_post_ch_theme_cleanup',     [ self::class, 'handle_cleanup' ] );
		add_action( 'admin_post_ch_theme_sections',    [ self::class, 'handle_sections' ] );
		add_action( 'admin_post_ch_theme_content',     [ self::class, 'handle_content' ] );
		add_action( 'admin_post_ch_theme_settings',    [ self::class, 'handle_settings' ] );
	}

	public static function register_menus(): void {
		add_menu_page(
			__( 'Cane House CMS', 'ch-theme' ),
			__( 'Cane House CMS', 'ch-theme' ),
			'manage_options',
			'ch-theme-admin',
			[ self::class, 'page_dashboard' ],
			'dashicons-coffee',
			3
		);
		add_submenu_page( 'ch-theme-admin', __( 'Overview',           'ch-theme' ), __( 'Overview',           'ch-theme' ), 'manage_options', 'ch-theme-admin',       [ self::class, 'page_dashboard'   ] );
		add_submenu_page( 'ch-theme-admin', __( 'Section Controls',   'ch-theme' ), __( 'Section Controls',   'ch-theme' ), 'manage_options', 'ch-theme-sections',    [ self::class, 'page_sections'    ] );
		add_submenu_page( 'ch-theme-admin', __( 'Content & Menu',     'ch-theme' ), __( 'Content & Menu',     'ch-theme' ), 'manage_options', 'ch-theme-content',     [ self::class, 'page_content'     ] );
		add_submenu_page( 'ch-theme-admin', __( 'Site Settings',      'ch-theme' ), __( 'Site Settings',      'ch-theme' ), 'manage_options', 'ch-theme-settings',    [ self::class, 'page_settings'    ] );
		add_submenu_page( 'ch-theme-admin', __( 'Enquiry Submissions','ch-theme' ), __( 'Enquiry Submissions','ch-theme' ), 'manage_options', 'ch-theme-submissions', [ self::class, 'page_submissions' ] );
		add_submenu_page( 'ch-theme-admin', __( 'Install Mock Data',  'ch-theme' ), __( 'Install Mock Data',  'ch-theme' ), 'manage_options', 'ch-theme-mock',        [ self::class, 'page_mock'        ] );
		add_submenu_page( 'ch-theme-admin', __( 'Cleanup Data',       'ch-theme' ), __( 'Cleanup Data',       'ch-theme' ), 'manage_options', 'ch-theme-cleanup',     [ self::class, 'page_cleanup'     ] );
	}

	public static function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'ch-theme' ) === false ) return;
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_add_inline_style( 'wp-admin', self::admin_css() );
	}

	// ── Page renderers ────────────────────────────────────────────────────────

	public static function page_dashboard(): void   { require get_template_directory() . '/admin/theme-dashboard.php';   }
	public static function page_sections(): void    { require get_template_directory() . '/admin/theme-sections.php';    }
	public static function page_content(): void     { require get_template_directory() . '/admin/theme-content.php';    }
	public static function page_settings(): void    { require get_template_directory() . '/admin/theme-settings.php';   }
	public static function page_submissions(): void { require get_template_directory() . '/admin/theme-submissions.php';}
	public static function page_mock(): void        { require get_template_directory() . '/admin/theme-mock-data.php';   }
	public static function page_cleanup(): void     { require get_template_directory() . '/admin/theme-cleanup.php';    }

	// ── POST handlers ─────────────────────────────────────────────────────────

	public static function handle_schema(): void {
		check_admin_referer( 'ch_theme_schema' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		require_once get_template_directory() . '/mock_data/seeder.php';
		$result = CH_Theme_Seeder::seed_schema_only();
		$msg = 'Schema installed: tables created, ' . $result['updated'] . ' settings saved.';
		if ( ! empty( $result['errors'] ) ) $msg .= ' Warnings: ' . implode( '; ', $result['errors'] );
		wp_redirect( add_query_arg( [ 'page' => 'ch-theme-mock', 'seeded' => '1', 'type' => 'schema', 'msg' => urlencode( $msg ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_seed(): void {
		check_admin_referer( 'ch_theme_seed' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		require_once get_template_directory() . '/mock_data/seeder.php';
		$selected = isset( $_POST['seed_types'] ) ? array_map( 'sanitize_key', (array) $_POST['seed_types'] ) : [];
		$result   = $selected ? CH_Theme_Seeder::seed_selected( $selected ) : CH_Theme_Seeder::seed_all();
		$msg = 'Mock data installed: ' . $result['inserted'] . ' inserted, ' . $result['updated'] . ' updated.';
		if ( ! empty( $result['errors'] ) ) {
			$msg .= ' Warnings: ' . implode( '; ', $result['errors'] );
		}
		wp_redirect( add_query_arg( [ 'page' => 'ch-theme-mock', 'seeded' => '1', 'type' => 'mock', 'msg' => urlencode( $msg ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cleanup(): void {
		check_admin_referer( 'ch_theme_cleanup' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		require_once get_template_directory() . '/mock_data/seeder.php';
		$result = CH_Theme_Seeder::cleanup_all();
		$msg = 'Cleanup complete - ' . $result['deleted'] . ' items removed.';
		wp_redirect( add_query_arg( [ 'page' => 'ch-theme-cleanup', 'cleaned' => '1', 'msg' => urlencode( $msg ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_sections(): void {
		check_admin_referer( 'ch_theme_sections' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		$all_keys = [
			'news_ticker', 'hero', 'marquee', 'how_to_order', 'reviews',
			'menu_builder', 'benefits', 'story', 'hire', 'franchise', 'faqs', 'contact',
		];
		$visibility = [];
		foreach ( $all_keys as $k ) {
			$visibility[ $k ] = isset( $_POST[ 'section_' . $k ] ) ? 1 : 0;
		}
		update_option( 'ch_section_visibility', wp_json_encode( $visibility ) );
		wp_redirect( add_query_arg( [ 'page' => 'ch-theme-sections', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_content(): void {
		check_admin_referer( 'ch_theme_content' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		// Hero settings
		$hero = [];
		foreach ( [ 'hero_tag', 'hero_headline', 'hero_brand', 'hero_desc', 'hero_cta_label', 'hero_cta_url', 'hero_cta2_label', 'hero_cta2_url', 'hero_badge_1', 'hero_badge_2', 'hero_badge_3', 'hero_badge_4' ] as $k ) {
			$hero[ $k ] = sanitize_text_field( $_POST[ $k ] ?? '' );
		}
		update_option( 'ch_home_settings', wp_json_encode( $hero ) );

		// Marquee items (one per line)
		$lines = array_filter( array_map( 'sanitize_text_field', explode( "\n", $_POST['marquee_items'] ?? '' ) ) );
		update_option( 'ch_marquee_items', wp_json_encode( array_values( $lines ) ) );

		// Order steps
		$steps = [];
		foreach ( (array) ( $_POST['order_steps'] ?? [] ) as $step ) {
			$title = sanitize_text_field( $step['title'] ?? '' );
			if ( ! $title ) continue;
			$steps[] = [
				'num'       => sanitize_text_field( $step['num']   ?? '' ),
				'emoji'     => sanitize_text_field( $step['emoji'] ?? '' ),
				'title'     => $title,
				'desc'      => sanitize_textarea_field( $step['desc'] ?? '' ),
				'highlight' => ! empty( $step['highlight'] ),
			];
		}
		if ( ! empty( $steps ) ) update_option( 'ch_order_steps', wp_json_encode( $steps ) );

		// Benefits
		$benefits = [];
		foreach ( (array) ( $_POST['benefits'] ?? [] ) as $b ) {
			$title = sanitize_text_field( $b['title'] ?? '' );
			if ( ! $title ) continue;
			$benefits[] = [
				'icon'  => sanitize_text_field( $b['icon'] ?? '' ),
				'title' => $title,
				'desc'  => sanitize_textarea_field( $b['desc'] ?? '' ),
			];
		}
		if ( ! empty( $benefits ) ) update_option( 'ch_benefits', wp_json_encode( $benefits ) );

		// FAQs (simple text pairs, topic-based)
		$faqs = [];
		foreach ( (array) ( $_POST['faqs'] ?? [] ) as $faq ) {
			$q = sanitize_text_field( $faq['question'] ?? '' );
			if ( ! $q ) continue;
			$faqs[] = [
				'topic'    => sanitize_text_field( $faq['topic'] ?? 'General' ),
				'question' => $q,
				'answer'   => sanitize_textarea_field( $faq['answer'] ?? '' ),
			];
		}
		if ( ! empty( $faqs ) ) update_option( 'ch_faqs_manual', wp_json_encode( $faqs ) );

		// Menu sizes
		$sizes = [];
		foreach ( (array) ( $_POST['menu_sizes'] ?? [] ) as $sz ) {
			$name = sanitize_text_field( $sz['name'] ?? '' );
			if ( ! $name ) continue;
			$sizes[] = [
				'icon'     => sanitize_text_field( $sz['icon']     ?? '' ),
				'name'     => $name,
				'desc'     => sanitize_text_field( $sz['desc']     ?? '' ),
				'price'    => sanitize_text_field( $sz['price']    ?? '' ),
				'badge'    => sanitize_text_field( $sz['badge']    ?? '' ),
				'featured' => ! empty( $sz['featured'] ),
			];
		}
		if ( ! empty( $sizes ) ) update_option( 'ch_menu_sizes', wp_json_encode( $sizes ) );

		// Hire packages
		$packages = [];
		foreach ( (array) ( $_POST['hire_packages'] ?? [] ) as $pkg ) {
			$title = sanitize_text_field( $pkg['title'] ?? '' );
			if ( ! $title ) continue;
			$items = array_filter( array_map( 'sanitize_text_field', (array) ( $pkg['items'] ?? [] ) ) );
			$packages[] = [
				'icon'  => sanitize_text_field( $pkg['icon']  ?? '' ),
				'title' => $title,
				'desc'  => sanitize_textarea_field( $pkg['desc'] ?? '' ),
				'items' => array_values( $items ),
			];
		}
		if ( ! empty( $packages ) ) update_option( 'ch_hire_packages', wp_json_encode( $packages ) );

		// Franchise locations
		$locations = [];
		foreach ( (array) ( $_POST['franchise_locations'] ?? [] ) as $loc ) {
			$name = sanitize_text_field( $loc['name'] ?? '' );
			if ( ! $name ) continue;
			$locations[] = [
				'icon' => sanitize_text_field( $loc['icon'] ?? '📍' ),
				'name' => $name,
			];
		}
		if ( ! empty( $locations ) ) update_option( 'ch_franchise_locations', wp_json_encode( $locations ) );

		wp_redirect( add_query_arg( [ 'page' => 'ch-theme-content', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_settings(): void {
		check_admin_referer( 'ch_theme_settings' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		$fields = [ 'phone', 'email', 'address', 'website', 'whatsapp', 'facebook_url', 'instagram_url', 'tiktok_url', 'youtube_url', 'tagline' ];
		$saved  = [];
		foreach ( $fields as $f ) {
			$saved[ $f ] = sanitize_text_field( $_POST[ $f ] ?? '' );
		}
		update_option( 'ch_site_settings', wp_json_encode( $saved ) );
		wp_redirect( add_query_arg( [ 'page' => 'ch-theme-settings', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	// ── Shared admin CSS ──────────────────────────────────────────────────────

	public static function admin_css(): string {
		return '
		.ch-admin-wrap { max-width:900px; }
		.ch-admin-wrap h1 { font-size:1.6rem; margin-bottom:1.5rem; color:#1a3a0f; }
		.ch-card { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:1.5rem; margin-bottom:1.5rem; }
		.ch-card h2 { font-size:1.1rem; margin-bottom:1rem; padding-bottom:.5rem; border-bottom:1px solid #eee; }
		.ch-row { display:flex; gap:1rem; align-items:center; margin-bottom:.8rem; flex-wrap:wrap; }
		.ch-row label { min-width:160px; font-weight:600; font-size:.85rem; }
		.ch-row input, .ch-row textarea, .ch-row select { flex:1; padding:.5rem; border:1px solid #ddd; border-radius:4px; min-width:200px; }
		.ch-badge { display:inline-block; padding:.2rem .7rem; border-radius:20px; font-size:.75rem; font-weight:700; }
		.ch-badge--green { background:#d4edda; color:#155724; }
		.ch-badge--yellow { background:#fff3cd; color:#856404; }
		.ch-notice { padding:.8rem 1rem; border-radius:6px; margin-bottom:1rem; }
		.ch-notice--success { background:#d4edda; border-left:4px solid #28a745; color:#155724; }
		.ch-notice--warning { background:#fff3cd; border-left:4px solid #ffc107; color:#856404; }
		.ch-stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:1rem; }
		.ch-stat { background:linear-gradient(135deg,#2d5a1b,#4a8c2a); color:#fff; border-radius:8px; padding:1.2rem; text-align:center; }
		.ch-stat__num { font-size:1.8rem; font-weight:800; }
		.ch-stat__label { font-size:.75rem; opacity:.8; margin-top:.3rem; }
		.ch-section-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:.8rem; }
		.ch-section-item { background:#f9f9f9; border:1px solid #e0e0e0; border-radius:6px; padding:.8rem 1rem; display:flex; align-items:center; gap:.8rem; }
		.ch-section-item label { font-size:.85rem; cursor:pointer; flex:1; }
		';
	}
}
