<?php
defined( 'ABSPATH' ) || exit;

class AH_Admin_Menus {

	private static string $cap = 'manage_options';

	public static function register(): void {
		// Top-level: CMS Dashboard
		add_menu_page(__( 'CMS ADMIN', 'ah-theme' ),__( 'CMS ADMIN', 'ah-theme' ),self::$cap,'ah-dashboard',[self::class,'page_dashboard'],'dashicons-admin-home',3);

		$submenus = [
			// ── Dashboard ─────────────────────────────────────────────
			['title' => 'Dashboard',            'menu' => 'Dashboard',            'slug' => 'ah-dashboard',     'callback' => 'page_dashboard'    ],
			// ── Content ───────────────────────────────────────────────
			['title' => 'Navigation Editor',    'menu' => 'Navigation Editor',    'slug' => 'ah-navigation',    'callback' => 'page_navigation'   ],
			['title' => 'Site Settings',        'menu' => 'Site Settings',        'slug' => 'ah-settings',      'callback' => 'page_settings'     ],
			['title' => 'Site Notices',         'menu' => 'Site Notices',         'slug' => 'ah-notices',       'callback' => 'page_notices'      ],
			['title' => 'Blog Posts',           'menu' => 'Blog Posts',           'slug' => 'ah-posts',         'callback' => 'page_posts'        ],
			['title' => 'Taxonomy Manager',     'menu' => 'Taxonomy Manager',     'slug' => 'ah-taxonomy',      'callback' => 'page_taxonomy'     ],
			['title' => 'Page Builder',         'menu' => 'Page Builder',         'slug' => 'ah-page-builder',  'callback' => 'page_builder'      ],
			// ── Site Layout ───────────────────────────────────────────
			['title' => 'Home Banners',         'menu' => 'Home Banners',         'slug' => 'ah-banners',       'callback' => 'page_banners'      ],
			['title' => 'Featured In',          'menu' => 'Featured In',          'slug' => 'ah-featured-in',   'callback' => 'page_featured_in'  ],
			['title' => 'Spotlights',           'menu' => 'Spotlights',           'slug' => 'ah-spotlights',    'callback' => 'page_spotlights'   ],
			['title' => 'News Bar',             'menu' => 'News Bar',             'slug' => 'ah-news-bar',      'callback' => 'page_news_bar'     ],
			['title' => 'Client Stories',       'menu' => 'Client Stories',       'slug' => 'ah-client-stories','callback' => 'page_client_stories'],
			['title' => 'Reviews',              'menu' => 'Reviews',              'slug' => 'ah-reviews',       'callback' => 'page_reviews'      ],
			['title' => 'FAQs',                 'menu' => 'FAQs',                 'slug' => 'ah-faqs',          'callback' => 'page_faqs'         ],
			['title' => 'Resources',            'menu' => 'Resources',            'slug' => 'ah-resources',     'callback' => 'page_resources'    ],
			// ── Pages & Builders ──────────────────────────────────────
			['title' => 'Static Pages',         'menu' => 'Static Pages',         'slug' => 'ah-static-pages',  'callback' => 'page_static_pages' ],
			['title' => 'Pages Manager',        'menu' => 'Pages Manager',        'slug' => 'ah-pages',         'callback' => 'page_pages'        ],
			// ── Forms & Communication ─────────────────────────────────
			['title' => 'Form Builder',         'menu' => 'Form Builder',         'slug' => 'ah-form-builder',  'callback' => 'page_form_builder' ],
			['title' => 'Notifications',        'menu' => 'Notifications',        'slug' => 'ah-newsletter',    'callback' => 'page_newsletter'   ],
			// ── Assets ────────────────────────────────────────────────
			['title' => 'Media Library',        'menu' => 'Media Library',        'slug' => 'ah-media',         'callback' => 'page_media'        ],
			['title' => 'File Links',           'menu' => 'File Links',           'slug' => 'ah-file-links',    'callback' => 'page_file_links'   ],
			// ── System ────────────────────────────────────────────────
			['title' => 'Visitor Stats',         'menu' => 'Visitor Stats',         'slug' => 'ah-visitors',      'callback' => 'page_visitors'     ],
			['title' => 'Custom Code',          'menu' => 'Custom Code',          'slug' => 'ah-custom-code',   'callback' => 'page_custom_code'  ],
			['title' => 'Analytics Reports',    'menu' => 'Analytics Reports',    'slug' => 'ah-analytics',     'callback' => 'page_analytics'    ],
			['title' => 'Workflow Manager',      'menu' => 'Workflow Manager',      'slug' => 'ah-workflow-manager','callback' => 'page_workflow_manager'],
			['title' => 'Data Import',          'menu' => 'Data Import',          'slug' => 'ah-import',        'callback' => 'page_import'       ],
			['title' => 'Redirect Rules',       'menu' => 'Redirect Rules',       'slug' => 'ah-redirects',     'callback' => 'page_redirects'    ],
			['title' => 'Audit Log',            'menu' => 'Audit Log',            'slug' => 'ah-audit',         'callback' => 'page_audit'        ],
			['title' => 'Admin Tools',          'menu' => 'Admin Tools',          'slug' => 'ah-admin-actions', 'callback' => 'page_admin_actions'],
			['title' => 'Global Settings',      'menu' => 'Global Settings',      'slug' => 'ah-global-settings', 'callback' => 'page_global_settings'],
			['title' => 'Reference Notes',      'menu' => 'Reference Notes',      'slug' => 'ah-ref-notes',     'callback' => 'page_ref_notes'    ],
			['title' => 'Help & Guide',         'menu' => 'Help & Guide',         'slug' => 'ah-help',          'callback' => 'page_help'         ],
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
	public static function page_featured_in()    { self::load( 'featured-in'    ); }
	public static function page_spotlights()     { self::load( 'spotlights'     ); }
	public static function page_settings()       { self::load( 'settings'       ); }
	public static function page_global_settings(){ self::load( 'global-settings'); }
	public static function page_pages()          { self::load( 'pages'          ); }
	public static function page_media()          { self::load( 'media'          ); }
	public static function page_resources()      { self::load( 'resources'      ); }
	public static function page_news_bar()       { self::load( 'news-bar'       ); }
	public static function page_navigation()     { self::load( 'navigation'     ); }
	public static function page_reviews()        { self::load( 'reviews'        ); }
    public static function page_faqs()           { self::load( 'faqs'           ); }
	public static function page_posts()          { self::load( 'posts'          ); }
	public static function page_client_stories() { self::load( 'client-stories' ); }
	public static function page_taxonomy()       { self::load( 'taxonomy'       ); }
	public static function page_audit()          { self::load( 'audit-log'      ); }
	public static function page_import()         { self::load( 'import'         ); }
	public static function page_file_links()     { self::load( 'file-links'     ); }
	public static function page_builder()         { self::load( 'page-builder'    ); }
	public static function page_form_builder()   { self::load( 'form-builder'   ); }
	public static function page_newsletter()     { self::load( 'notifications'  ); }
	public static function page_workflow_manager() { self::load( 'workflow-manager' ); }
	public static function page_admin_actions()  { self::load( 'admin-actions'  ); }
	public static function page_static_pages()   { self::load( 'static-pages'   ); }
	public static function page_redirects()      { self::load( 'redirect-rules'  ); }
	public static function page_custom_code()    { self::load( 'custom-code'     ); }
	public static function page_visitors()        { self::load( 'visitors'       ); }
	public static function page_analytics()      { self::load( 'analytics'      ); }
	public static function page_ref_notes()      { self::load( 'reference-notes' ); }
	public static function page_help()           { self::load( 'help'           ); }
}
