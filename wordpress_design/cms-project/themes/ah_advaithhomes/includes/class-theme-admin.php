<?php
defined( 'ABSPATH' ) || exit;

class AH_Theme_Admin {

	public static function init(): void {
		add_action( 'admin_menu',             [ self::class, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts',  [ self::class, 'enqueue_assets' ] );
		add_action( 'admin_post_ah_theme_seed',     [ self::class, 'handle_seed' ] );
		add_action( 'admin_post_ah_theme_cleanup',  [ self::class, 'handle_cleanup' ] );
		add_action( 'admin_post_ah_theme_sections', [ self::class, 'handle_sections' ] );
		if ( ! class_exists( 'AH_Admin_Bootstrap' ) ) {
			add_action( 'admin_post_ah_theme_nav', [ self::class, 'handle_nav' ] );
		}
		add_action( 'admin_post_ah_theme_content',  [ self::class, 'handle_content' ] );
	}

	public static function register_menus(): void {
		add_menu_page(
			__( 'CMS Controller', 'ah-theme' ),
			__( 'CMS Controller', 'ah-theme' ),
			'manage_options',
			'ah-theme-admin',
			[ self::class, 'page_dashboard' ],
			'dashicons-admin-tools',
			3
		);
		add_submenu_page( 'ah-theme-admin', __( 'Overview',          'ah-theme' ), __( 'Overview',          'ah-theme' ), 'manage_options', 'ah-theme-admin',    [ self::class, 'page_dashboard' ] );
		add_submenu_page( 'ah-theme-admin', __( 'Section Controls',  'ah-theme' ), __( 'Section Controls',  'ah-theme' ), 'manage_options', 'ah-theme-sections', [ self::class, 'page_sections'  ] );
		if ( ! class_exists( 'AH_Admin_Bootstrap' ) ) {
			add_submenu_page( 'ah-theme-admin', __( 'Navigation', 'ah-theme' ), __( 'Navigation', 'ah-theme' ), 'manage_options', 'ah-theme-nav', [ self::class, 'page_nav' ] );
		}
		add_submenu_page( 'ah-theme-admin', __( 'Content Controls',  'ah-theme' ), __( 'Content Controls',  'ah-theme' ), 'manage_options', 'ah-theme-content',     [ self::class, 'page_content'     ] );
		add_submenu_page( 'ah-theme-admin', __( 'Contact Submissions', 'ah-theme' ), __( 'Contact Submissions', 'ah-theme' ), 'manage_options', 'ah-theme-submissions', [ self::class, 'page_submissions' ] );
		add_submenu_page( 'ah-theme-admin', __( 'Install Mock Data', 'ah-theme' ), __( 'Install Mock Data', 'ah-theme' ), 'manage_options', 'ah-theme-mock',        [ self::class, 'page_mock'        ] );
		add_submenu_page( 'ah-theme-admin', __( 'Cleanup Data',      'ah-theme' ), __( 'Cleanup Data',      'ah-theme' ), 'manage_options', 'ah-theme-cleanup',  [ self::class, 'page_cleanup'   ] );
	}

	public static function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'ah-theme' ) === false ) return;
		// Inline styles for the theme admin pages (reuses WP's native admin style + our overrides)
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_add_inline_style( 'wp-admin', self::admin_css() );
	}

	// ── Page renderers ────────────────────────────────────────────────────────

	public static function page_dashboard(): void {
		require get_template_directory() . '/admin/theme-dashboard.php';
	}

	public static function page_mock(): void {
		require get_template_directory() . '/admin/theme-mock-data.php';
	}

	public static function page_cleanup(): void {
		require get_template_directory() . '/admin/theme-cleanup.php';
	}

	public static function page_sections(): void {
		require get_template_directory() . '/admin/theme-sections.php';
	}

	public static function page_nav(): void {
		require get_template_directory() . '/admin/theme-nav.php';
	}

	public static function page_content(): void {
		require get_template_directory() . '/admin/theme-content.php';
	}

	public static function page_submissions(): void {
		require get_template_directory() . '/admin/theme-submissions.php';
	}

	// ── POST handlers ─────────────────────────────────────────────────────────

	public static function handle_seed(): void {
		check_admin_referer( 'ah_theme_seed' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/mock_data/seeder.php';
		$result = AH_Theme_Seeder::seed_all();

		$msg = 'Seeded ' . $result['inserted'] . ' rows and updated ' . $result['updated'] . ' options successfully.';
		if ( ! empty( $result['errors'] ) ) {
			$msg .= ' Warnings: ' . implode( '; ', $result['errors'] );
		}
		wp_redirect( add_query_arg( [ 'page' => 'ah-theme-mock', 'seeded' => '1', 'msg' => urlencode( $msg ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cleanup(): void {
		check_admin_referer( 'ah_theme_cleanup' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once get_template_directory() . '/mock_data/seeder.php';
		$result = AH_Theme_Seeder::cleanup_all();

		$msg = 'Cleanup complete — ' . $result['deleted'] . ' items removed.';
		wp_redirect( add_query_arg( [ 'page' => 'ah-theme-cleanup', 'cleaned' => '1', 'msg' => urlencode( $msg ) ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_sections(): void {
		check_admin_referer( 'ah_theme_sections' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$all_keys = [
			'global_news_ticker', 'global_trust_bar',
			'home_hero', 'home_guide_cards', 'home_process', 'home_stats',
			'home_services', 'home_testimonials', 'home_properties', 'home_team',
			'home_faq', 'home_blog', 'home_cta',
		];

		$visibility = [];
		foreach ( $all_keys as $k ) {
			$visibility[ $k ] = isset( $_POST[ 'section_' . $k ] ) ? 1 : 0;
		}
		update_option( 'ah_section_visibility', wp_json_encode( $visibility ) );

		// Save featured selections (tag-picker values)
		$pickers = [ 'featured_services', 'featured_faqs' ];
		foreach ( $pickers as $p ) {
			$val = sanitize_text_field( $_POST[ $p ] ?? '' );
			update_option( 'ah_' . $p, $val );
		}

		wp_redirect( add_query_arg( [ 'page' => 'ah-theme-sections', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_nav(): void {
		check_admin_referer( 'ah_theme_nav' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$nav_items = [];
		foreach ( (array) ( $_POST['nav_items'] ?? [] ) as $item ) {
			$label = sanitize_text_field( $item['label'] ?? '' );
			if ( $label === '' ) {
				continue;
			}

			$type = ( $item['type'] ?? 'link' ) === 'dropdown' ? 'dropdown' : 'link';
			$sub_items = [];
			foreach ( (array) ( $item['submenu'] ?? [] ) as $sub_item ) {
				$sub_label = sanitize_text_field( $sub_item['label'] ?? '' );
				$sub_url   = ah_normalize_theme_url( (string) ( $sub_item['url'] ?? '' ) );
				if ( $sub_label === '' || $sub_url === '' ) {
					continue;
				}

				$sub_items[] = [
					'label'       => $sub_label,
					'url'         => $sub_url,
					'description' => sanitize_text_field( $sub_item['description'] ?? '' ),
					'icon'        => sanitize_text_field( $sub_item['icon'] ?? '' ),
					'highlight'   => ! empty( $sub_item['highlight'] ),
				];
			}

			$nav_items[] = [
				'id'          => sanitize_title( $item['id'] ?? $label ),
				'label'       => $label,
				'type'        => $type,
				'url'         => $type === 'link' ? ah_normalize_theme_url( (string) ( $item['url'] ?? '' ) ) : '',
				'visible'     => ! empty( $item['visible'] ),
				'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
				'description' => sanitize_text_field( $item['description'] ?? '' ),
				'submenu'     => $sub_items,
			];
		}
		update_option( 'ah_theme_navigation', wp_json_encode( $nav_items ) );
		update_option(
			'ah_nav_cta',
			wp_json_encode(
				[
					'label' => sanitize_text_field( $_POST['nav_cta']['label'] ?? 'Get Help' ),
					'url'   => ah_normalize_theme_url( (string) ( $_POST['nav_cta']['url'] ?? '' ), '/contact/' ),
				]
			)
		);

		$footer_columns = [];
		foreach ( (array) ( $_POST['footer_columns'] ?? [] ) as $column ) {
			$title = sanitize_text_field( $column['title'] ?? '' );
			$items = [];
			foreach ( (array) ( $column['items'] ?? [] ) as $item ) {
				$label = sanitize_text_field( $item['label'] ?? '' );
				$url   = ah_normalize_theme_url( (string) ( $item['url'] ?? '' ) );
				if ( $label === '' || $url === '' ) {
					continue;
				}
				$items[] = [
					'label'     => $label,
					'url'       => $url,
					'highlight' => ! empty( $item['highlight'] ),
				];
			}

			if ( $title !== '' || ! empty( $items ) ) {
				$footer_columns[] = [
					'title' => $title ?: 'Links',
					'items' => $items,
				];
			}
		}

		$legal_links = [];
		foreach ( (array) ( $_POST['footer_legal_links'] ?? [] ) as $item ) {
			$label = sanitize_text_field( $item['label'] ?? '' );
			$url   = ah_normalize_theme_url( (string) ( $item['url'] ?? '' ) );
			if ( $label === '' || $url === '' ) {
				continue;
			}
			$legal_links[] = [
				'label' => $label,
				'url'   => $url,
			];
		}

		$footer = [
			'brand_description' => wp_kses_post( $_POST['footer_brand_description'] ?? '' ),
			'badge_text'        => sanitize_text_field( $_POST['footer_badge_text'] ?? '' ),
			'columns'           => $footer_columns,
			'contact'           => [
				'phone_note'   => sanitize_text_field( $_POST['footer_contact']['phone_note'] ?? '' ),
				'email_note'   => sanitize_text_field( $_POST['footer_contact']['email_note'] ?? '' ),
				'address_note' => sanitize_text_field( $_POST['footer_contact']['address_note'] ?? '' ),
			],
			'cta'               => [
				'label' => sanitize_text_field( $_POST['footer_cta']['label'] ?? '' ),
				'url'   => ah_normalize_theme_url( (string) ( $_POST['footer_cta']['url'] ?? '' ), '/contact/' ),
			],
			'legal_links'       => $legal_links,
		];
		update_option( 'ah_theme_footer', wp_json_encode( $footer ) );

		wp_redirect( add_query_arg( [ 'page' => 'ah-theme-nav', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_content(): void {
		check_admin_referer( 'ah_theme_content' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		// Featured post IDs
		update_option( 'ah_featured_post_ids', sanitize_text_field( $_POST['featured_post_ids'] ?? '' ) );

		// News bar items (one per line)
		$lines = array_filter( array_map( 'sanitize_text_field', explode( "\n", $_POST['news_bar_items'] ?? '' ) ) );
		update_option( 'ah_news_bar_items', wp_json_encode( array_values( $lines ) ) );

		// Trust signals
		$signals = [];
		foreach ( (array) ( $_POST['trust_signals'] ?? [] ) as $sig ) {
			$text = sanitize_text_field( $sig['text'] ?? '' );
			if ( ! $text ) continue;
			$signals[] = [
				'icon' => sanitize_text_field( $sig['icon'] ?? '' ),
				'text' => $text,
			];
		}
		if ( ! empty( $signals ) ) {
			update_option( 'ah_trust_signals', wp_json_encode( $signals ) );
		}

		// Properties
		$props = [];
		foreach ( (array) ( $_POST['properties'] ?? [] ) as $p ) {
			$loc = sanitize_text_field( $p['location'] ?? '' );
			if ( ! $loc ) continue;
			$props[] = [
				'emoji'    => sanitize_text_field( $p['emoji']    ?? '' ),
				'price'    => sanitize_text_field( $p['price']    ?? '' ),
				'location' => $loc,
				'area'     => sanitize_text_field( $p['area']     ?? '' ),
				'saved'    => sanitize_text_field( $p['saved']    ?? '' ),
				'type'     => sanitize_text_field( $p['type']     ?? '' ),
				'beds'     => (int) ( $p['beds'] ?? 0 ),
				'result'   => sanitize_text_field( $p['result']   ?? '' ),
			];
		}
		update_option( 'ah_featured_properties', wp_json_encode( $props ) );

		// HTML blocks
		$allowed = [ 'above_footer', 'below_hero', 'global_banner' ];
		$blocks  = [];
		foreach ( $allowed as $bkey ) {
			$blocks[ $bkey ] = wp_kses_post( $_POST['html_block'][ $bkey ] ?? '' );
		}
		update_option( 'ah_html_blocks', wp_json_encode( $blocks ) );

		// Contact settings
		$contact = [
			'recipient_email' => sanitize_email( $_POST['contact']['recipient_email'] ?? '' ),
			'subject_prefix'  => sanitize_text_field( $_POST['contact']['subject_prefix'] ?? '' ),
			'thank_you_msg'   => sanitize_textarea_field( $_POST['contact']['thank_you_msg'] ?? '' ),
			'show_phone'      => ! empty( $_POST['contact']['show_phone'] ),
			'show_budget'     => ! empty( $_POST['contact']['show_budget'] ),
			'show_timeline'   => ! empty( $_POST['contact']['show_timeline'] ),
		];
		update_option( 'ah_contact_settings', wp_json_encode( $contact ) );

		// Static quick links (comma-separated slugs from picker)
		update_option( 'ah_static_quick_links', sanitize_text_field( $_POST['static_quick_links'] ?? '' ) );

		wp_redirect( add_query_arg( [ 'page' => 'ah-theme-content', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	// ── Inline CSS ────────────────────────────────────────────────────────────

	private static function admin_css(): string {
		return '
.ah-admin-wrap { max-width:860px; }
.ah-admin-header { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
.ah-admin-logo { width:44px; height:44px; background:#b7791f; border-radius:10px; display:grid; place-items:center; color:white; font-size:1.2rem; font-weight:700; flex-shrink:0; }
.ah-admin-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.ah-admin-header p { margin:2px 0 0; color:#64748b; font-size:.875rem; }
.ah-admin-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:16px; margin-bottom:28px; }
.ah-admin-card { background:white; border:1px solid #e2e8f0; border-radius:10px; padding:20px; }
.ah-admin-card__label { font-size:.75rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px; }
.ah-admin-card__value { font-size:1.6rem; font-weight:700; color:#0f172a; }
.ah-admin-card__sub { font-size:.78rem; color:#94a3b8; margin-top:4px; }
.ah-admin-card--ok .ah-admin-card__value { color:#16a34a; }
.ah-admin-card--warn .ah-admin-card__value { color:#d97706; }
.ah-admin-card--missing .ah-admin-card__value { color:#dc2626; }
.ah-admin-box { background:white; border:1px solid #e2e8f0; border-radius:10px; padding:24px 28px; margin-bottom:20px; }
.ah-admin-box h2 { font-size:1rem; font-weight:700; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #f1f5f9; }
.ah-admin-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.ah-admin-table th { text-align:left; font-weight:600; color:#64748b; font-size:.78rem; text-transform:uppercase; letter-spacing:.06em; padding:8px 12px; border-bottom:1px solid #f1f5f9; }
.ah-admin-table td { padding:10px 12px; border-bottom:1px solid #f8fafc; }
.ah-admin-table tr:last-child td { border-bottom:none; }
.ah-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:600; }
.ah-badge--ok   { background:#dcfce7; color:#16a34a; }
.ah-badge--warn { background:#fef3c7; color:#d97706; }
.ah-badge--missing { background:#fee2e2; color:#dc2626; }
.ah-admin-notice { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:.9rem; }
.ah-admin-notice--success { background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; }
.ah-admin-notice--error   { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }
.ah-admin-notice--warn    { background:#fef3c7; border:1px solid #fde68a; color:#92400e; }
.ah-btn-danger { background:#dc2626; color:white; border-color:#dc2626; }
.ah-btn-danger:hover { background:#b91c1c; color:white; }
/* Section controls */
.ah-section-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px; }
.ah-section-row { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:white; border:1px solid #e2e8f0; border-radius:8px; transition:border-color .15s; }
.ah-section-row:hover { border-color:#b7791f; }
.ah-section-row__left { display:flex; align-items:center; gap:10px; }
.ah-section-row__icon { font-size:1.3rem; }
.ah-section-row__name { font-weight:600; font-size:.9rem; color:#0f172a; }
.ah-section-row__desc { font-size:.75rem; color:#94a3b8; margin-top:1px; }
/* Toggle switch */
.ah-toggle { position:relative; width:44px; height:24px; flex-shrink:0; }
.ah-toggle input { opacity:0; width:0; height:0; position:absolute; }
.ah-toggle__track { position:absolute; inset:0; background:#cbd5e1; border-radius:12px; cursor:pointer; transition:background .2s; }
.ah-toggle__track::after { content:""; position:absolute; left:3px; top:3px; width:18px; height:18px; background:white; border-radius:50%; transition:transform .2s; }
.ah-toggle input:checked + .ah-toggle__track { background:#b7791f; }
.ah-toggle input:checked + .ah-toggle__track::after { transform:translateX(20px); }
/* Tag picker */
.ah-tag-picker { border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 8px; min-height:44px; display:flex; flex-wrap:wrap; gap:6px; align-items:center; cursor:text; background:white; transition:border-color .2s; }
.ah-tag-picker:focus-within { border-color:#b7791f; box-shadow:0 0 0 3px rgba(183,121,31,.12); }
.ah-tag-picker__tag { display:inline-flex; align-items:center; gap:4px; background:#fef3c7; border:1px solid #fde68a; color:#92400e; border-radius:20px; padding:3px 10px; font-size:.78rem; font-weight:600; }
.ah-tag-picker__tag button { background:none; border:none; cursor:pointer; color:#92400e; font-size:1rem; line-height:1; padding:0 0 0 2px; opacity:.7; }
.ah-tag-picker__tag button:hover { opacity:1; }
.ah-tag-picker__input { border:none; outline:none; font-size:.875rem; min-width:140px; flex:1; padding:2px 4px; background:transparent; }
.ah-suggestions { position:absolute; top:100%; left:0; right:0; background:white; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.1); z-index:100; max-height:220px; overflow-y:auto; margin-top:4px; }
.ah-suggestion-item { padding:10px 14px; font-size:.875rem; cursor:pointer; border-bottom:1px solid #f8fafc; transition:background .15s; }
.ah-suggestion-item:last-child { border-bottom:none; }
.ah-suggestion-item:hover, .ah-suggestion-item.is-focused { background:#fef3c7; }
.ah-suggestion-item mark { background:transparent; font-weight:700; color:#b7791f; }
.ah-picker-wrap { position:relative; }
';
	}
}
