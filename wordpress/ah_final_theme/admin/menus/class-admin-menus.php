<?php
defined( 'ABSPATH' ) || exit;

class AH_Admin_Menus {

	private static string $cap = 'manage_options';

	public static function register(): void {
		// Top-level: CMS Dashboard
		add_menu_page(
			__( 'CMS Portal', 'ah-theme' ),
			__( 'CMS Portal', 'ah-theme' ),
			self::$cap,
			'ah-dashboard',
			array( self::class, 'page_dashboard' ),
			'dashicons-admin-home',
			3
		);

		// Sub: Dashboard
		add_submenu_page( 'ah-dashboard', __( 'Dashboard', 'ah-theme' ), __( 'Dashboard', 'ah-theme' ), self::$cap, 'ah-dashboard', array( self::class, 'page_dashboard' ) );

		// Sub: Site Settings
		add_submenu_page( 'ah-dashboard', __( 'Site Settings', 'ah-theme' ), __( 'Site Settings', 'ah-theme' ), self::$cap, 'ah-settings', array( self::class, 'page_settings' ) );

		// Sub: Navigation Menus
		add_submenu_page( 'ah-dashboard', __( 'Navigation Menus', 'ah-theme' ), __( 'Navigation Menus', 'ah-theme' ), self::$cap, 'ah-nav-menus', array( self::class, 'page_nav_menus' ) );

		// Sub: Pages Manager
		add_submenu_page( 'ah-dashboard', __( 'Pages Manager', 'ah-theme' ), __( 'Pages Manager', 'ah-theme' ), self::$cap, 'ah-pages', array( self::class, 'page_pages' ) );

		// Sub: Media Library
		add_submenu_page( 'ah-dashboard', __( 'Media Library', 'ah-theme' ), __( 'Media Library', 'ah-theme' ), self::$cap, 'ah-media', array( self::class, 'page_media' ) );

		// ---- Content ----

		// Sub: News Bar
		add_submenu_page( 'ah-dashboard', __( 'News Bar', 'ah-theme' ), __( 'News Bar', 'ah-theme' ), self::$cap, 'ah-news-bar', array( self::class, 'page_news_bar' ) );

		// Sub: Home Sections
		add_submenu_page( 'ah-dashboard', __( 'Home Sections', 'ah-theme' ), __( 'Home Sections', 'ah-theme' ), self::$cap, 'ah-home', array( self::class, 'page_home' ) );

		// Sub: Services
		add_submenu_page( 'ah-dashboard', __( 'Services', 'ah-theme' ), __( 'Services', 'ah-theme' ), self::$cap, 'ah-services', array( self::class, 'page_services' ) );

		// Sub: About Page
		add_submenu_page( 'ah-dashboard', __( 'About Page', 'ah-theme' ), __( 'About Page', 'ah-theme' ), self::$cap, 'ah-about', array( self::class, 'page_about' ) );

		// Sub: Reviews
		add_submenu_page( 'ah-dashboard', __( 'Reviews', 'ah-theme' ), __( 'Reviews', 'ah-theme' ), self::$cap, 'ah-reviews', array( self::class, 'page_reviews' ) );

		// Sub: FAQs
		add_submenu_page( 'ah-dashboard', __( 'FAQs', 'ah-theme' ), __( 'FAQs', 'ah-theme' ), self::$cap, 'ah-faqs', array( self::class, 'page_faqs' ) );

		// Sub: Posts (Blog / News / Articles)
		add_submenu_page( 'ah-dashboard', __( 'Posts / Blog', 'ah-theme' ), __( 'Posts / Blog', 'ah-theme' ), self::$cap, 'ah-posts', array( self::class, 'page_posts' ) );

		// Sub: Team Members
		add_submenu_page( 'ah-dashboard', __( 'Team Members', 'ah-theme' ), __( 'Team Members', 'ah-theme' ), self::$cap, 'ah-team', array( self::class, 'page_team' ) );

		// Sub: Client Stories
		add_submenu_page( 'ah-dashboard', __( 'Client Stories', 'ah-theme' ), __( 'Client Stories', 'ah-theme' ), self::$cap, 'ah-client-stories', array( self::class, 'page_client_stories' ) );

		// Sub: Contact Page
		add_submenu_page( 'ah-dashboard', __( 'Contact Page', 'ah-theme' ), __( 'Contact Page', 'ah-theme' ), self::$cap, 'ah-contact', array( self::class, 'page_contact' ) );

		// Sub: Taxonomy
		add_submenu_page( 'ah-dashboard', __( 'Categories & Tags', 'ah-theme' ), __( 'Categories & Tags', 'ah-theme' ), self::$cap, 'ah-taxonomy', array( self::class, 'page_taxonomy' ) );

		// ---- Reports ----

		// Sub: Contact Submissions
		add_submenu_page( 'ah-dashboard', __( 'Contact Submissions', 'ah-theme' ), __( 'Contact Submissions', 'ah-theme' ), self::$cap, 'ah-submissions', array( self::class, 'page_submissions' ) );

		// Sub: Audit Log
		add_submenu_page( 'ah-dashboard', __( 'Audit Log', 'ah-theme' ), __( 'Audit Log', 'ah-theme' ), self::$cap, 'ah-audit', array( self::class, 'page_audit' ) );

		// ---- Tools ----

		// Sub: Data Import
		add_submenu_page( 'ah-dashboard', __( 'Data Import', 'ah-theme' ), __( 'Data Import', 'ah-theme' ), self::$cap, 'ah-import', array( self::class, 'page_import' ) );

		// Sub: File Links
		add_submenu_page( 'ah-dashboard', __( 'File Links', 'ah-theme' ), __( 'File Links', 'ah-theme' ), self::$cap, 'ah-file-links', array( self::class, 'page_file_links' ) );

		// Sub: Form Builder
		add_submenu_page( 'ah-dashboard', __( 'Form Builder', 'ah-theme' ), __( 'Form Builder', 'ah-theme' ), self::$cap, 'ah-form-builder', array( self::class, 'page_form_builder' ) );

		// Sub: Static Pages
		add_submenu_page( 'ah-dashboard', __( 'Static Pages', 'ah-theme' ), __( 'Static Pages', 'ah-theme' ), self::$cap, 'ah-static-pages', array( self::class, 'page_static_pages' ) );

		// Sub: Admin Actions
		add_submenu_page( 'ah-dashboard', __( 'Admin Actions', 'ah-theme' ), __( 'Admin Actions', 'ah-theme' ), self::$cap, 'ah-admin-actions', array( self::class, 'page_admin_actions' ) );
	}

	// ----------------------------------------------------------------
	// Page callbacks — each loads its dedicated template file
	// ----------------------------------------------------------------

	private static function load( string $file ): void {
		$path = AH_THEME_DIR . '/admin/pages/' . $file . '.php';
		if ( file_exists( $path ) ) {
			include $path;
		} else {
			echo '<div class="wrap"><h1>Page not found: ' . esc_html( $file ) . '</h1></div>';
		}
	}

	public static function page_dashboard()      { self::load( 'dashboard'      ); }
	public static function page_settings()       { self::load( 'settings'       ); }
	public static function page_nav_menus()      { self::load( 'nav-menus'      ); }
	public static function page_pages()          { self::load( 'pages'          ); }
	public static function page_media()          { self::load( 'media'          ); }
	public static function page_news_bar()       { self::load( 'news-bar'       ); }
	public static function page_home()           { self::load( 'home-sections'  ); }
	public static function page_services()       { self::load( 'services'       ); }
	public static function page_about()          { self::load( 'about'          ); }
	public static function page_reviews()        { self::load( 'reviews'        ); }
	public static function page_faqs()           { self::load( 'faqs'           ); }
	public static function page_posts()          { self::load( 'posts'          ); }
	public static function page_team()           { self::load( 'team'           ); }
	public static function page_client_stories() { self::load( 'client-stories' ); }
	public static function page_contact()        { self::load( 'contact'        ); }
	public static function page_taxonomy()       { self::load( 'taxonomy'       ); }
	public static function page_submissions()    { self::load( 'submissions'    ); }
	public static function page_audit()          { self::load( 'audit-log'      ); }
	public static function page_import()         { self::load( 'import'         ); }
	public static function page_file_links()     { self::load( 'file-links'     ); }
	public static function page_form_builder()   { self::load( 'form-builder'   ); }
	public static function page_admin_actions()  { self::load( 'admin-actions'  ); }
	public static function page_static_pages()   { self::load( 'static-pages'   ); }
}
