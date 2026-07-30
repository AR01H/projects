<?php
/**
 * ADN_Theme_Admin - Main admin page with TABS + SUBTABS layout.
 *
 * Handlers are split into separate classes in admin/Handlers/.
 * This file handles: menu registration, routing, tab/subtab navigation, view loading.
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/SettingsSchemas.php';
require_once __DIR__ . '/ThemeSettings.php';
require_once __DIR__ . '/Handlers/BaseHandler.php';
require_once __DIR__ . '/Handlers/CalculatorHandler.php';
require_once __DIR__ . '/Handlers/HomeHandler.php';
require_once __DIR__ . '/Handlers/CategoryHandler.php';
require_once __DIR__ . '/Handlers/ExpertHandler.php';
require_once __DIR__ . '/Handlers/AdminActionsHandler.php';

class ADN_Theme_Admin {

	const MENU_SLUG  = 'adn-theme';
	const CAPABILITY = 'manage_options';

	// ── Initialization ─────────────────────────────────────────────────────

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_menu', array( __CLASS__, 'hide_plugin_menus' ), 999 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect_to_subtab' ) );

		ADN_Theme_Settings::init();

		// Import / Export.
		add_action( 'admin_post_adn_export_settings', array( 'ADN_Admin_Actions_Handler', 'handle_export_settings' ) );
		add_action( 'admin_post_adn_import_settings', array( 'ADN_Admin_Actions_Handler', 'handle_import_settings' ) );

		// Calculators.
		add_action( 'admin_post_adn_save_calc_list', array( 'ADN_Calculator_Handler', 'handle_save_list' ) );
		add_action( 'admin_post_adn_save_calc_new',   array( 'ADN_Calculator_Handler', 'handle_save_new' ) );
		add_action( 'admin_post_adn_delete_calc',     array( 'ADN_Calculator_Handler', 'handle_delete' ) );
		add_action( 'admin_post_adn_save_tools_page', array( 'ADN_Calculator_Handler', 'handle_save_page' ) );

		// Experts.
		add_action( 'admin_post_adn_save_expert',        array( 'ADN_Expert_Handler', 'handle_save' ) );
		add_action( 'admin_post_adn_delete_expert',      array( 'ADN_Expert_Handler', 'handle_delete' ) );
		add_action( 'admin_post_adn_save_expert_banner', array( 'ADN_Expert_Handler', 'handle_save_banner' ) );

		// Home Page.
		add_action( 'admin_post_adn_save_home_newsblocks', array( 'ADN_Home_Handler', 'handle_save_newsblocks' ) );
		add_action( 'admin_post_adn_save_home_resources',  array( 'ADN_Home_Handler', 'handle_save_resources' ) );
		add_action( 'admin_post_adn_save_home_journey',    array( 'ADN_Home_Handler', 'handle_save_journey' ) );

		// Category Pages.
		add_action( 'admin_post_adn_save_category_term', array( 'ADN_Category_Handler', 'handle_save_term' ) );
		add_action( 'wp_ajax_adn_cat_post_search',      array( 'ADN_Category_Handler', 'handle_post_search' ) );
		add_action( 'wp_ajax_adn_cat_tax_search',       array( 'ADN_Category_Handler', 'handle_taxonomy_search' ) );
		add_action( 'wp_ajax_adn_cat_faq_search',       array( 'ADN_Category_Handler', 'handle_faq_search' ) );

		// Admin Actions.
		add_action( 'admin_post_adn_clear_cache',          array( 'ADN_Admin_Actions_Handler', 'handle_clear_cache' ) );
		add_action( 'admin_post_adn_reask_cookie_all',      array( 'ADN_Admin_Actions_Handler', 'handle_reask_cookie_all' ) );
		add_action( 'admin_post_adn_reask_cookie_rejected', array( 'ADN_Admin_Actions_Handler', 'handle_reask_cookie_rejected' ) );
		add_action( 'admin_post_adn_sync_pages',           array( 'ADN_Admin_Actions_Handler', 'handle_sync_pages' ) );
		add_action( 'admin_post_adn_flush_rewrites',       array( 'ADN_Admin_Actions_Handler', 'handle_flush_rewrites' ) );
		add_action( 'admin_post_adn_install_contact_rule', array( 'ADN_Admin_Actions_Handler', 'handle_install_contact_rule' ) );
	}

	// ── Tab Registry ───────────────────────────────────────────────────────

	private static function fa( string $icon, string $label ): string {
		return '<i class="fa-solid ' . esc_attr( $icon ) . '" style="margin-right:5px;opacity:.75;"></i>' . esc_html( $label );
	}

	public static function tabs(): array {
		$tools_label = defined( 'SITE_TOOLS_PLURAL' ) ? SITE_TOOLS_PLURAL : __( 'Calculator', ADN_TEXT_DOMAIN );
		return array(
			'dashboard' => array(
				'label' => self::fa( 'fa-gauge-high', __( 'Dashboard', ADN_TEXT_DOMAIN ) ),
				'view'  => 'TabDashboard.php',
			),
			'home' => array(
				'label'   => self::fa( 'fa-house', __( 'Home Page', ADN_TEXT_DOMAIN ) ),
				'subtabs' => array(
					'sections'   => array( 'label' => self::fa( 'fa-table-cells-large', __( 'Sections', ADN_TEXT_DOMAIN ) ),         'view' => 'home/SubSections.php' ),
					'hero'       => array( 'label' => self::fa( 'fa-star', __( 'Hero & Intro', ADN_TEXT_DOMAIN ) ),                  'view' => 'home/SubHero.php' ),
					'featured'   => array( 'label' => self::fa( 'fa-bookmark', __( 'Featured Guides', ADN_TEXT_DOMAIN ) ),            'view' => 'home/SubFeatured.php' ),
					'newsblocks' => array( 'label' => self::fa( 'fa-newspaper', __( 'Regulations & Hot Topics', ADN_TEXT_DOMAIN ) ), 'view' => 'home/SubNewsblocks.php' ),
					'resources'  => array( 'label' => self::fa( 'fa-folder-open', __( 'Resources', ADN_TEXT_DOMAIN ) ),              'view' => 'home/SubResources.php' ),
					'journey'    => array( 'label' => self::fa( 'fa-images', __( 'Journey Cards', ADN_TEXT_DOMAIN ) ),               'view' => 'home/SubJourney.php' ),
				),
			),
			'calculators' => array(
				'label'   => self::fa( 'fa-calculator', sprintf( __( 'Manage %s', ADN_TEXT_DOMAIN ), $tools_label ) ),
				'subtabs' => array(
					'general' => array( 'label' => self::fa( 'fa-sliders', __( 'Heading & Banner', ADN_TEXT_DOMAIN ) ), 'view' => 'calculators/SubGeneral.php' ),
					'list'    => array( 'label' => self::fa( 'fa-list', __( 'Tool List', ADN_TEXT_DOMAIN ) ),           'view' => 'calculators/SubList.php' ),
					'page'    => array( 'label' => self::fa( 'fa-file-lines', __( 'Page Content', ADN_TEXT_DOMAIN ) ),   'view' => 'calculators/SubPage.php' ),
					'new'     => array( 'label' => self::fa( 'fa-plus', __( 'Add / Edit', ADN_TEXT_DOMAIN ) ),          'view' => 'calculators/SubNew.php' ),
				),
			),
			'experts' => array(
				'label'   => self::fa( 'fa-user-tie', __( 'Experts / Team', ADN_TEXT_DOMAIN ) ),
				'subtabs' => array(
					'list'   => array( 'label' => self::fa( 'fa-users', __( 'Expert List', ADN_TEXT_DOMAIN ) ),       'view' => 'experts/SubList.php' ),
					'new'    => array( 'label' => self::fa( 'fa-user-plus', __( 'Add / Edit', ADN_TEXT_DOMAIN ) ),    'view' => 'experts/SubNew.php' ),
					'banner' => array( 'label' => self::fa( 'fa-image', __( 'Expert Banner', ADN_TEXT_DOMAIN ) ),     'view' => 'experts/SubBanner.php' ),
				),
			),
			'contact-inbox' => array(
				'label' => self::fa( 'fa-envelope-open-text', __( 'Contact Inbox', ADN_TEXT_DOMAIN ) ),
				'view'  => 'TabContactInbox.php',
			),
			'guidance-inbox' => array(
				'label' => self::fa( 'fa-clipboard-list', __( 'Guidance Inbox', ADN_TEXT_DOMAIN ) ),
				'view'  => 'TabGuidanceInbox.php',
			),
			'import-export' => array(
				'label' => self::fa( 'fa-arrow-right-arrow-left', __( 'Import / Export', ADN_TEXT_DOMAIN ) ),
				'view'  => 'TabImportExport.php',
			),
			'category-pages' => array(
				'label'   => self::fa( 'fa-folder-open', __( 'Category Pages', ADN_TEXT_DOMAIN ) ),
				'subtabs' => self::category_subtabs(),
			),
			'admin-actions' => array(
				'label'   => self::fa( 'fa-screwdriver-wrench', __( 'Admin Actions', ADN_TEXT_DOMAIN ) ),
				'subtabs' => array(
					'cache'       => array( 'label' => self::fa( 'fa-rotate', __( 'Cache', ADN_TEXT_DOMAIN ) ),                   'view' => 'admin-actions/SubCache.php' ),
					'pages'       => array( 'label' => self::fa( 'fa-sitemap', __( 'Pages & Permalinks', ADN_TEXT_DOMAIN ) ),     'view' => 'admin-actions/SubPages.php' ),
					'rules'       => array( 'label' => self::fa( 'fa-bolt', __( 'Workflow Manager', ADN_TEXT_DOMAIN ) ),          'view' => 'admin-actions/SubRules.php' ),
					'sample-data' => array( 'label' => self::fa( 'fa-database', __( 'Sample Data', ADN_TEXT_DOMAIN ) ),           'view' => 'admin-actions/SubSampleData.php' ),
					'tracking'    => array( 'label' => self::fa( 'fa-chart-line', __( 'Tracking & Analytics', ADN_TEXT_DOMAIN ) ), 'view' => 'admin-actions/SubTracking.php' ),
				),
			),
		);
	}

	// ── Menu Registration ──────────────────────────────────────────────────

	public static function register_menu(): void {
		add_menu_page(
			__( 'CMS THEME', ADN_TEXT_DOMAIN ),
			__( 'CMS THEME', ADN_TEXT_DOMAIN ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-home',
			3
		);

		$first = array_key_first( self::tabs() );
		foreach ( self::tabs() as $key => $def ) {
			$slug = ( $key === $first ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $key;
			add_submenu_page( self::MENU_SLUG, $def['label'], $def['label'], self::CAPABILITY, $slug, array( __CLASS__, 'render_page' ) );
		}
	}

	public static function hide_plugin_menus(): void {
		// remove_submenu_page( 'ah-dashboard', 'ah-reviews' );
		// remove_submenu_page( 'ah-dashboard', 'ah-client-stories' );
	}

	public static function enqueue_admin_assets( string $hook ): void {
		if ( false === strpos( $hook, self::MENU_SLUG ) ) { return; }
		wp_enqueue_media();
		wp_enqueue_style( 'adn-fontawesome-admin', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
	}

	public static function tab_page_slug( string $tab ): string {
		$first = array_key_first( self::tabs() );
		return ( $tab === $first ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $tab;
	}

	// ── Routing ────────────────────────────────────────────────────────────

	private static function active_tab(): string {
		$tabs = self::tabs();
		$page = sanitize_key( wp_unslash( $_GET['page'] ?? '' ) );
		if ( $page && $page !== self::MENU_SLUG && 0 === strpos( $page, self::MENU_SLUG . '-' ) ) {
			$key = substr( $page, strlen( self::MENU_SLUG ) + 1 );
			if ( isset( $tabs[ $key ] ) ) { return $key; }
		}
		$req = sanitize_key( wp_unslash( $_GET['tab'] ?? '' ) );
		return isset( $tabs[ $req ] ) ? $req : array_key_first( $tabs );
	}

	private static function active_subtab( string $tab_key ): string {
		$subs = self::tabs()[ $tab_key ]['subtabs'] ?? array();
		if ( empty( $subs ) ) { return ''; }
		$req = sanitize_key( wp_unslash( $_GET['subtab'] ?? '' ) );
		return isset( $subs[ $req ] ) ? $req : array_key_first( $subs );
	}

	private static function tab_url( string $tab, string $subtab = '' ): string {
		$args = array( 'page' => self::tab_page_slug( $tab ) );
		if ( '' !== $subtab ) { $args['subtab'] = $subtab; }
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	public static function maybe_redirect_to_subtab(): void {
		if ( ! is_admin() || ! current_user_can( self::CAPABILITY ) ) { return; }

		$page = sanitize_key( wp_unslash( $_GET['page'] ?? '' ) );
		if ( '' === $page ) { return; }

		$tabs = self::tabs();
		$matched = '';
		foreach ( $tabs as $k => $d ) {
			if ( self::tab_page_slug( $k ) === $page ) { $matched = $k; break; }
		}
		if ( '' === $matched || empty( $tabs[ $matched ]['subtabs'] ) ) { return; }

		$req_sub = sanitize_key( wp_unslash( $_GET['subtab'] ?? '' ) );
		if ( '' !== $req_sub && isset( $tabs[ $matched ]['subtabs'][ $req_sub ] ) ) { return; }

		$first_sub = (string) array_key_first( $tabs[ $matched ]['subtabs'] );
		if ( '' !== $first_sub ) {
			wp_safe_redirect( self::tab_url( $matched, $first_sub ) );
			exit;
		}
	}

	// ── View Loading ───────────────────────────────────────────────────────

	private static function load_view( string $relative ): void {
		$base = realpath( ADN_THEME_DIR . '/admin/tabs' );
		$path = realpath( ADN_THEME_DIR . '/admin/tabs/' . $relative );
		if ( $base && $path && 0 === strpos( $path, $base ) && is_file( $path ) ) {
			require $path;
			return;
		}
		echo '<div class="notice notice-error"><p>' . esc_html__( 'View not found.', ADN_TEXT_DOMAIN ) . '</p></div>';
	}

	// ── Page Renderer ──────────────────────────────────────────────────────

	public static function render_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', ADN_TEXT_DOMAIN ) );
		}

		$tabs      = self::tabs();
		$active    = self::active_tab();
		$active_sub = self::active_subtab( $active );
		$tab       = $tabs[ $active ];

		$view = ! empty( $tab['subtabs'] )
			? ( $tab['subtabs'][ $active_sub ]['view'] ?? '' )
			: ( $tab['view'] ?? '' );

		self::render_notice();
		?>
		<div class="wrap adn-admin">
			<h1><?php echo wp_kses( $tab['label'], array( 'i' => array( 'class' => array(), 'style' => array(), 'aria-hidden' => array() ) ) ); ?></h1>
			<?php if ( ! empty( $tab['subtabs'] ) ) : ?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $tab['subtabs'] as $skey => $sdef ) : ?>
						<a href="<?php echo esc_url( self::tab_url( $active, $skey ) ); ?>"
						   class="nav-tab <?php echo $skey === $active_sub ? 'nav-tab-active' : ''; ?>">
							<?php echo wp_kses( $sdef['label'], array( 'i' => array( 'class' => array(), 'style' => array(), 'aria-hidden' => array() ) ) ); ?>
						</a>
					<?php endforeach; ?>
				</h2>
			<?php endif; ?>
			<div class="adn-tab-body" style="margin-top:1rem;">
				<?php $view ? self::load_view( $view ) : '<p>' . esc_html__( 'Nothing here yet.', ADN_TEXT_DOMAIN ) . '</p>'; ?>
			</div>
		</div>
		<?php
	}

	// ── Notices ────────────────────────────────────────────────────────────

	private static function render_notice(): void {
		if ( ! empty( $_GET['adn_err'] ) ) {
			$msg = sanitize_text_field( wp_unslash( $_GET['adn_msg'] ?? __( 'An error occurred.', ADN_TEXT_DOMAIN ) ) );
			printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $msg ) );
			return;
		}
		if ( empty( $_GET['adn_done'] ) ) { return; }
		$msg = sanitize_text_field( wp_unslash( $_GET['adn_msg'] ?? __( 'Done.', ADN_TEXT_DOMAIN ) ) );
		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $msg ) );
	}

	// ── Dynamic Category Subtabs ───────────────────────────────────────────

	private static function category_subtabs(): array {
		$subtabs = array();
		if ( function_exists( 'adn_cms_available' ) && adn_cms_available() && function_exists( 'adn_cms_guide_parents' ) ) {
			foreach ( adn_cms_guide_parents( 20 ) as $term ) {
				$slug = sanitize_key( $term->slug ?? '' );
				$name = $term->name ?? ucwords( str_replace( '-', ' ', $slug ) );
				if ( '' === $slug ) { continue; }
				$subtabs[ $slug ] = array( 'label' => $name, 'view' => 'category/SubTerm.php' );
			}
		}
		if ( empty( $subtabs ) ) {
			$subtabs['_none'] = array( 'label' => __( 'No Terms', ADN_TEXT_DOMAIN ), 'view' => 'category/SubNoTerms.php' );
		}
		return $subtabs;
	}
}
