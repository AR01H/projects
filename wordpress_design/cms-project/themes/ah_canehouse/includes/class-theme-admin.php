<?php
defined( 'ABSPATH' ) || exit;

class CH_Theme_Admin {

	public static function init(): void {
		add_action( 'admin_menu',                            [ self::class, 'register_menus'          ] );
		add_action( 'admin_enqueue_scripts',                 [ self::class, 'enqueue_assets'          ] );
		add_action( 'admin_post_ch_theme_schema',            [ self::class, 'handle_schema'           ] );
		add_action( 'admin_post_ch_theme_seed',              [ self::class, 'handle_seed'             ] );
		add_action( 'admin_post_ch_theme_cleanup',           [ self::class, 'handle_cleanup'          ] );
		add_action( 'admin_post_ch_theme_content',           [ self::class, 'handle_content'          ] );
		add_action( 'admin_post_ch_content_settings_business', [ self::class, 'handle_cs_business'   ] );
		add_action( 'admin_post_ch_content_settings_contact',  [ self::class, 'handle_cs_contact'    ] );
		add_action( 'admin_post_ch_content_settings_booking',  [ self::class, 'handle_cs_booking'    ] );
		add_action( 'admin_post_ch_content_settings_galleries', [ self::class, 'handle_cs_galleries'  ] );
		add_action( 'admin_post_ch_content_settings_eventswhy', [ self::class, 'handle_cs_eventswhy'  ] );
		add_action( 'admin_post_ch_content_settings_about',     [ self::class, 'handle_cs_about'      ] );
		add_action( 'admin_post_ch_content_settings_certs',     [ self::class, 'handle_cs_certs'      ] );
		// ch_theme_settings handler lives in functions.php (complete version that
		// also saves pricing, certifications and schema). Do NOT register a second
		// handler here - it would overwrite those extended settings.
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
		add_submenu_page( 'ch-theme-admin', 'Overview',             'Overview',             'manage_options', 'ch-theme-admin',       [ self::class, 'page_dashboard'   ] );
		add_submenu_page( 'ch-theme-admin', 'Content & Menu',       'Content & Menu',       'manage_options', 'ch-theme-content',     [ self::class, 'page_content'     ] );
		// Navigation & Footer are managed by the CMS plugin (ah_cms_navigation / ah_cms_footer).
		add_submenu_page( 'ch-theme-admin', 'Site Settings',        'Site Settings',        'manage_options', 'ch-theme-settings',    [ self::class, 'page_settings'    ] );
		add_submenu_page( 'ch-theme-admin', 'Content Settings',     'Content Settings',     'manage_options', 'ch-content-settings',  [ self::class, 'page_content_settings' ] );
		add_submenu_page( 'ch-theme-admin', 'Enquiry Submissions',  'Enquiry Submissions',  'manage_options', 'ch-theme-submissions',     [ self::class, 'page_submissions' ] );
		add_submenu_page( 'ch-theme-admin', 'Order Requests',       'Order Requests 📦',    'manage_options', 'ch-order-requests',        [ self::class, 'page_orders'      ] );
		add_submenu_page( 'ch-theme-admin', 'Booking Requests',     'Booking Requests 🎉',  'manage_options', 'ch-booking-requests',      [ self::class, 'page_bookings'    ] );
		add_submenu_page( 'ch-theme-admin', 'Franchise Enquiries',  'Franchise Enquiries 🌿','manage_options','ch-franchise-enquiries',   [ self::class, 'page_franchise'   ] );
		add_submenu_page( 'ch-theme-admin', 'Install Mock Data',    'Install Mock Data',    'manage_options', 'ch-theme-mock',            [ self::class, 'page_mock'        ] );
		add_submenu_page( 'ch-theme-admin', 'Cleanup Data',         'Cleanup Data',         'manage_options', 'ch-theme-cleanup',     [ self::class, 'page_cleanup'     ] );
	}

	public static function enqueue_assets( string $hook ): void {
		$pages = [ 'ch-theme', 'ch-order-requests', 'ch-booking-requests', 'ch-franchise-enquiries', 'ch-theme-submissions' ];
		$match = false;
		foreach ( $pages as $slug ) { if ( strpos( $hook, $slug ) !== false ) { $match = true; break; } }
		if ( ! $match ) return;
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_add_inline_style( 'wp-admin', self::admin_css() );
	}

	// ── Page renderers ────────────────────────────────────────────────────────

	public static function page_dashboard(): void        { require get_template_directory() . '/admin/theme-dashboard.php';         }
	public static function page_content(): void          { require get_template_directory() . '/admin/theme-content.php';           }
	public static function page_settings(): void         { require get_template_directory() . '/admin/theme-settings.php';          }
	public static function page_content_settings(): void { require get_template_directory() . '/admin/theme-content-settings.php';  }
	public static function page_submissions(): void      { require get_template_directory() . '/admin/theme-submissions.php';       }
	public static function page_orders(): void           { require get_template_directory() . '/admin/theme-order-requests.php';       }
	public static function page_bookings(): void         { require get_template_directory() . '/admin/theme-booking-requests.php';    }
	public static function page_franchise(): void        { require get_template_directory() . '/admin/theme-franchise-enquiries.php'; }
	public static function page_mock(): void             { require get_template_directory() . '/admin/theme-mock-data.php';           }
	public static function page_cleanup(): void          { require get_template_directory() . '/admin/theme-cleanup.php';           }

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

		$result = ! empty( $selected )
			? CH_Theme_Seeder::seed_selected( $selected )
			: [ 'inserted' => 0, 'updated' => 0, 'errors' => [] ];

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

		// Hero badges (one per line)
		$badge_lines = array_filter( array_map( 'sanitize_text_field', explode( "\n", $_POST['hero_badges'] ?? '' ) ) );
		update_option( 'ch_hero_badges', wp_json_encode( array_values( $badge_lines ) ) );




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

		// Story cards → merge into site settings
		$existing_settings = get_option( 'ch_site_settings', [] );
		if ( is_string( $existing_settings ) ) $existing_settings = json_decode( $existing_settings, true ) ?: [];

		// Homepage display limits (only when the limits card was on the submitted form)
		if ( isset( $_POST['home_limits_present'] ) ) {
			$raw_hl = isset( $_POST['home_limits'] ) ? (array) $_POST['home_limits'] : [];
			$hl     = [];
			foreach ( [ 'story_cards', 'faqs' ] as $hk ) {
				$hl[ $hk . '_limit' ] = isset( $raw_hl[ $hk . '_limit' ] ) ? '1' : '0';
				$hl[ $hk . '_count' ] = max( 1, (int) ( $raw_hl[ $hk . '_count' ] ?? 0 ) );
			}
			$existing_settings['home_limits'] = $hl;
		}
		update_option( 'ch_site_settings', $existing_settings );


		wp_redirect( add_query_arg( [ 'page' => 'ch-theme-content', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	// handle_settings() intentionally removed - the authoritative handler is in
	// functions.php (saves contact, social, pricing, certifications and schema).

	// ── Content Settings handlers ─────────────────────────────────────────────

	public static function handle_cs_business(): void {
		check_admin_referer( 'ch_content_settings_business' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$existing = get_option( 'ch_site_settings', [] );
		if ( is_string( $existing ) ) $existing = json_decode( $existing, true ) ?: [];

		foreach ( [ 'business_hours', 'response_time', 'address', 'events_info_text', 'franchise_info_text' ] as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				$existing[ $k ] = sanitize_text_field( wp_unslash( $_POST[ $k ] ) );
			}
		}
		update_option( 'ch_site_settings', $existing );
		wp_redirect( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'business', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cs_contact(): void {
		check_admin_referer( 'ch_content_settings_contact' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$types = [];
		foreach ( (array) ( $_POST['enquiry_types'] ?? [] ) as $et ) {
			$value = sanitize_key( $et['value'] ?? '' );
			$label = sanitize_text_field( wp_unslash( $et['label'] ?? '' ) );
			if ( $value && $label ) {
				$types[] = [ 'value' => $value, 'label' => $label ];
			}
		}
		update_option( 'ch_enquiry_types', wp_json_encode( $types ) );
		wp_redirect( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'contact', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cs_booking(): void {
		check_admin_referer( 'ch_content_settings_booking' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$occasions = [];
		foreach ( (array) ( $_POST['occasions'] ?? [] ) as $occ ) {
			$occ = sanitize_text_field( wp_unslash( $occ ) );
			if ( $occ !== '' ) $occasions[] = $occ;
		}
		update_option( 'ch_occasions', wp_json_encode( $occasions ) );
		wp_redirect( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'booking', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}


	public static function handle_cs_galleries(): void {
		check_admin_referer( 'ch_content_settings_galleries' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		$keys = [ 'events' => 'ch_events_gallery', 'franchise' => 'ch_franchise_gallery', 'about' => 'ch_about_gallery', 'equipment' => 'ch_equipment_gallery' ];
		foreach ( $keys as $slug => $option ) {
			$raw  = (array) ( $_POST[ 'gallery_' . $slug ] ?? [] );
			$imgs = [];
			foreach ( $raw as $img ) {
				$src = esc_url_raw( $img['src'] ?? '' );
				if ( $src ) {
					$imgs[] = [
						'src'   => $src,
						'label' => sanitize_text_field( wp_unslash( $img['label'] ?? '' ) ),
						'desc'  => sanitize_text_field( wp_unslash( $img['desc']  ?? '' ) ),
					];
				}
			}
			update_option( $option, wp_json_encode( $imgs ) );
		}
		wp_redirect( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'galleries', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cs_eventswhy(): void {
		check_admin_referer( 'ch_content_settings_eventswhy' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$items = [];
		foreach ( (array) ( $_POST['events_why_items'] ?? [] ) as $item ) {
			$title = sanitize_text_field( wp_unslash( $item['title'] ?? '' ) );
			if ( $title ) $items[] = [
				'icon'  => sanitize_text_field( wp_unslash( $item['icon'] ?? '' ) ),
				'title' => $title,
				'text'  => sanitize_text_field( wp_unslash( $item['text'] ?? '' ) ),
			];
		}
		$data = [
			'image' => esc_url_raw( $_POST['events_why_image'] ?? '' ),
			'items' => $items,
		];
		update_option( 'ch_events_why', wp_json_encode( $data ) );
		wp_redirect( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'eventswhy', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cs_about(): void {
		check_admin_referer( 'ch_content_settings_about' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		$mvv = [];
		foreach ( (array) ( $_POST['about_mvv'] ?? [] ) as $card ) {
			$title = sanitize_text_field( wp_unslash( $card['title'] ?? '' ) );
			if ( $title ) $mvv[] = [
				'icon'  => sanitize_text_field( wp_unslash( $card['icon']  ?? '' ) ),
				'title' => $title,
				'text'  => sanitize_text_field( wp_unslash( $card['text']  ?? '' ) ),
			];
		}
		update_option( 'ch_about_mvv', wp_json_encode( $mvv ) );

		$quality = [];
		foreach ( (array) ( $_POST['about_quality'] ?? [] ) as $item ) {
			$item = sanitize_text_field( wp_unslash( $item ) );
			if ( $item ) $quality[] = $item;
		}
		update_option( 'ch_about_quality', wp_json_encode( $quality ) );

		wp_redirect( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'about', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_cs_certs(): void {
		check_admin_referer( 'ch_content_settings_certs' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		global $wpdb;
		$table = ch_theme_table( 'certifications' );

		// Ensure table exists before writing.
		CH_Schema::create_all();

		$wpdb->query( "DELETE FROM `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$i = 0;
		foreach ( (array) ( $_POST['certs'] ?? [] ) as $cert ) {
			$title = sanitize_text_field( wp_unslash( $cert['title'] ?? '' ) );
			if ( ! $title ) continue;
			$wpdb->insert(
				$table,
				[
					'icon'       => sanitize_text_field( wp_unslash( $cert['icon']  ?? '✅' ) ),
					'title'      => $title,
					'descr'      => sanitize_textarea_field( wp_unslash( $cert['desc']  ?? '' ) ),
					'badge'      => sanitize_text_field( wp_unslash( $cert['badge'] ?? '' ) ),
					'sort_order' => $i++,
					'status'     => 'active',
				],
				[ '%s', '%s', '%s', '%s', '%d', '%s' ]
			);
		}

		wp_redirect( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'certs', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	// ── Shared admin CSS ──────────────────────────────────────────────────────

	public static function admin_css(): string {
		return '
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
