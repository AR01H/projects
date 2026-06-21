<?php
defined( 'ABSPATH' ) || exit;

class AH_Admin_Menus {

	private static string $cap = 'manage_options';

	public static function register(): void {
		// Top-level: CMS Dashboard
		add_menu_page(__( 'CMS ADMIN', 'ah-theme' ),__( 'CMS ADMIN', 'ah-theme' ),self::$cap,'ah-dashboard',[self::class,'page_dashboard'],'dashicons-admin-home',3);

		$submenus = [
			// ── Core ──────────────────────────────────────────────────
			['title' => 'Dashboard',         'menu' => 'Dashboard',         'slug' => 'ah-dashboard',     'callback' => 'page_dashboard'    ],
			['title' => 'Reviews',           'menu' => 'Reviews',           'slug' => 'ah-reviews',       'callback' => 'page_reviews'      ],
			['title' => 'Client Stories',    'menu' => 'Client Stories',    'slug' => 'ah-client-stories','callback' => 'page_client_stories'],
			['title' => 'FAQs',              'menu' => 'FAQs',              'slug' => 'ah-faqs',          'callback' => 'page_faqs'         ],
			// ── Site Structure ────────────────────────────────────────
			['title' => 'Navigation',        'menu' => 'Navigation Editor', 'slug' => 'ah-navigation',    'callback' => 'page_navigation'   ],
			['title' => 'Home Banners',      'menu' => 'Home Banners',      'slug' => 'ah-banners',       'callback' => 'page_banners'      ],
			['title' => 'Spotlights',        'menu' => 'Spotlights',        'slug' => 'ah-spotlights',    'callback' => 'page_spotlights'   ],
			['title' => 'Site Notices',      'menu' => 'Site Notices',      'slug' => 'ah-notices',       'callback' => 'page_notices'      ],
			['title' => 'News Bar',          'menu' => 'News Bar',          'slug' => 'ah-news-bar',      'callback' => 'page_news_bar'     ],
			['title' => 'Media Library',     'menu' => 'Media Library',     'slug' => 'ah-media',         'callback' => 'page_media'        ],
			['title' => 'File Links',        'menu' => 'File Links',        'slug' => 'ah-file-links',    'callback' => 'page_file_links'   ],
			// ── Builder Tools ─────────────────────────────────────────
			['title' => 'Posts / Blog',      'menu' => 'Posts / Blog',      'slug' => 'ah-posts',         'callback' => 'page_posts'        ],
			['title' => 'Page Builder',      'menu' => 'Page Builder',      'slug' => 'ah-page-builder',  'callback' => 'page_builder'      ],
			['title' => 'Static Pages',      'menu' => 'Static Pages',      'slug' => 'ah-static-pages',  'callback' => 'page_static_pages' ],
			['title' => 'Pages Manager',     'menu' => 'Pages Manager',     'slug' => 'ah-pages',         'callback' => 'page_pages'        ],
			['title' => 'Form Builder',      'menu' => 'Form Builder',      'slug' => 'ah-form-builder',  'callback' => 'page_form_builder' ],
			['title' => 'Notifications',      'menu' => '🔔 Notifications',  'slug' => 'ah-newsletter',    'callback' => 'page_newsletter'   ],
			['title' => 'Taxonomies',        'menu' => 'Taxonomies',        'slug' => 'ah-taxonomy',      'callback' => 'page_taxonomy'     ],
			// ── System ────────────────────────────────────────────────
			['title' => 'Analytics Reports', 'menu' => 'Analytics Reports', 'slug' => 'ah-analytics',     'callback' => 'page_analytics'    ],
			['title' => 'Triggers Maker',    'menu' => 'Triggers Maker',    'slug' => 'ah-rules-engine',  'callback' => 'page_rules_engine' ],
			['title' => 'Data Import',       'menu' => 'Data Import',       'slug' => 'ah-import',        'callback' => 'page_import'       ],
			['title' => 'Site Settings',     'menu' => 'Site Settings',     'slug' => 'ah-settings',      'callback' => 'page_settings'     ],
			['title' => 'Audit Log',         'menu' => 'Audit Log',         'slug' => 'ah-audit',         'callback' => 'page_audit'        ],
			['title' => 'Admin Actions',     'menu' => 'Admin Actions',     'slug' => 'ah-admin-actions', 'callback' => 'page_admin_actions'],
			['title' => 'Reference Notes',   'menu' => '📋 Reference Notes', 'slug' => 'ah-ref-notes',     'callback' => 'page_ref_notes'    ],
			['title' => 'Help & Guide',      'menu' => 'Help & Guide',      'slug' => 'ah-help',          'callback' => 'page_help'         ],
		];

		// Let theme define which menus to exclude
		$excluded_slugs = apply_filters( 'ah_admin_menu_exclude_slugs', [] );
		if ( ! empty( $excluded_slugs ) ) {
			$submenus = array_filter( $submenus, function( $item ) use ( $excluded_slugs ) {
				return ! in_array( $item['slug'], $excluded_slugs, true );
			});
		}

		foreach ( $submenus as $submenu ) {
			add_submenu_page('ah-dashboard',__( $submenu['title'], 'ah-theme' ),__( $submenu['menu'], 'ah-theme' ),self::$cap,$submenu['slug'],[ self::class, $submenu['callback'] ]);
		}
	}

	// ----------------------------------------------------------------
	// Page callbacks - each loads its dedicated template file
	// ----------------------------------------------------------------

	private static function load( string $file ): void {
		// Theme-level admin pages take priority over plugin-level ones.
		$theme_path  = get_template_directory() . '/admin/pages/' . $file . '.php';
		$plugin_path = AH_PLUGIN_DIR . '/admin/pages/' . $file . '.php';
		$path        = file_exists( $theme_path ) ? $theme_path : $plugin_path;
		if ( file_exists( $path ) ) {
			include $path;
		} else {
			echo '<div class="wrap"><h1>Page not found: ' . esc_html( $file ) . '</h1></div>';
		}
	}

	public static function page_dashboard()      { self::load( 'dashboard'      ); }
	public static function page_notices()        { self::load( 'notices'        ); }
	public static function page_banners()        { self::load( 'banners'        ); }
	public static function page_spotlights()     { self::load( 'spotlights'     ); }
	public static function page_settings()       { self::load( 'settings'       ); }
	public static function page_pages()          { self::load( 'pages'          ); }
	public static function page_media()          { self::load( 'media'          ); }
	public static function page_news_bar()       { self::load( 'news-bar'       ); }
	public static function page_navigation()     { self::load( 'navigation'     ); }
	public static function page_reviews()        { self::load( 'reviews'        ); }
    public static function page_faqs()           { self::load( 'faqs'           ); }
	public static function page_posts()          { self::load( 'posts'          ); }
	public static function page_client_stories() { self::load( 'client-stories' ); }
	public static function page_contact()        { self::load( 'contact'        ); }
	public static function page_taxonomy()       { self::load( 'taxonomy'       ); }
	public static function page_audit()          { self::load( 'audit-log'      ); }
	public static function page_import()         { self::load( 'import'         ); }
	public static function page_file_links()     { self::load( 'file-links'     ); }
	public static function page_builder()         { self::load( 'page-builder'    ); }
	public static function page_form_builder()   { self::load( 'form-builder'   ); }
	public static function page_newsletter()     { self::load( 'notifications'  ); }
	public static function page_guidance()      { self::load( 'guidance'       ); }
	public static function page_rules_engine()   { self::load( 'rules-engine'   ); }
	public static function page_admin_actions()  { self::load( 'admin-actions'  ); }
	public static function page_static_pages()   { self::load( 'static-pages'   ); }
	public static function page_analytics()      { self::load( 'analytics'      ); }
	public static function page_ref_notes()      { self::load( 'reference-notes' ); }
	public static function page_help()           { self::load( 'help'           ); }
}
