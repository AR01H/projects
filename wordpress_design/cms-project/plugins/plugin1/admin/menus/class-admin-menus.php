<?php
defined( 'ABSPATH' ) || exit;

class AH_Admin_Menus {

	private static string $cap = 'manage_options';

	public static function register(): void {
		// Top-level: CMS Dashboard
		add_menu_page(__( 'CMS ADMIN', 'ah-theme' ),__( 'CMS ADMIN', 'ah-theme' ),self::$cap,'ah-dashboard',[self::class,'page_dashboard'],'dashicons-admin-home',3);

		$submenus = [
			['title' => 'Dashboard','menu' => 'Dashboard','slug' => 'ah-dashboard','callback' => 'page_dashboard'],
			['title' => 'Site Notices','menu' => 'Site Notices','slug' => 'ah-notices','callback' => 'page_notices'],
			['title' => 'Media Library','menu' => 'Media Library','slug' => 'ah-media','callback' => 'page_media'],
			['title' => 'File Links','menu' => 'File Links','slug' => 'ah-file-links','callback' => 'page_file_links'],
			['title' => 'News Bar','menu' => 'News Bar','slug' => 'ah-news-bar','callback' => 'page_news_bar'],
			['title' => 'Navigation','menu' => 'Navigation Editor','slug' => 'ah-navigation','callback' => 'page_navigation'],
			['title' => 'Home Sections','menu' => 'Home Sections','slug' => 'ah-home','callback' => 'page_home'],
			['title' => 'Services','menu' => 'Services','slug' => 'ah-services','callback' => 'page_services'],
			['title' => 'About Page','menu' => 'About Page','slug' => 'ah-about','callback' => 'page_about'],
			['title' => 'Reviews','menu' => 'Reviews','slug' => 'ah-reviews','callback' => 'page_reviews'],
			['title' => 'Client Stories','menu' => 'Client Stories','slug' => 'ah-client-stories','callback' => 'page_client_stories'],
			['title' => 'FAQs','menu' => 'FAQs','slug' => 'ah-faqs','callback' => 'page_faqs'],
			['title' => 'Posts / Blog','menu' => 'Posts / Blog','slug' => 'ah-posts','callback' => 'page_posts'],
			['title' => 'Page Builder','menu' => 'Page Builder','slug' => 'ah-page-builder','callback' => 'page_builder'],
			['title' => 'Static Pages','menu' => 'Static Pages','slug' => 'ah-static-pages','callback' => 'page_static_pages'],
			['title' => 'Pages Manager','menu' => 'Pages Manager','slug' => 'ah-pages','callback' => 'page_pages'],
			['title' => 'Form Builder','menu' => 'Form Builder','slug' => 'ah-form-builder','callback' => 'page_form_builder'],
			['title' => 'Triggers Maker','menu' => 'Triggers Maker','slug' => 'ah-rules-engine','callback' => 'page_rules_engine'],
			['title' => 'Team Members','menu' => 'Team Members','slug' => 'ah-team','callback' => 'page_team'],
			['title' => 'Taxonomies','menu' => 'Taxonomies','slug' => 'ah-taxonomy','callback' => 'page_taxonomy'],
			['title' => 'Data Import','menu' => 'Data Import','slug' => 'ah-import','callback' => 'page_import'],
			['title' => 'Site Settings','menu' => 'Site Settings','slug' => 'ah-settings','callback' => 'page_settings'],
			['title' => 'Audit Log','menu' => 'Audit Log','slug' => 'ah-audit','callback' => 'page_audit'],
			['title' => 'Admin Actions','menu' => 'Admin Actions','slug' => 'ah-admin-actions','callback' => 'page_admin_actions'],
			['title' => 'Help & Guide','menu' => 'Help & Guide','slug' => 'ah-help','callback' => 'page_help'],
		];

		foreach ( $submenus as $submenu ) {
			add_submenu_page('ah-dashboard',__( $submenu['title'], 'ah-theme' ),__( $submenu['menu'], 'ah-theme' ),self::$cap,$submenu['slug'],[ self::class, $submenu['callback'] ]);
		}
	}

	// ----------------------------------------------------------------
	// Page callbacks - each loads its dedicated template file
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
	public static function page_notices()        { self::load( 'notices'        ); }
	public static function page_settings()       { self::load( 'settings'       ); }
	public static function page_pages()          { self::load( 'pages'          ); }
	public static function page_media()          { self::load( 'media'          ); }
	public static function page_news_bar()       { self::load( 'news-bar'       ); }
	public static function page_navigation()     { self::load( 'navigation'     ); }
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
	public static function page_audit()          { self::load( 'audit-log'      ); }
	public static function page_import()         { self::load( 'import'         ); }
	public static function page_file_links()     { self::load( 'file-links'     ); }
	public static function page_builder()         { self::load( 'page-builder'    ); }
	public static function page_form_builder()   { self::load( 'form-builder'   ); }
	public static function page_rules_engine()   { self::load( 'rules-engine'   ); }
	public static function page_admin_actions()  { self::load( 'admin-actions'  ); }
	public static function page_static_pages()   { self::load( 'static-pages'   ); }
	public static function page_help()           { self::load( 'help'           ); }
}
