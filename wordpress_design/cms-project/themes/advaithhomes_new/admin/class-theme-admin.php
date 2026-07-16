<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/settings-schemas.php';
require_once __DIR__ . '/class-theme-settings.php';

/**
 * ADN_Theme_Admin
 *
 * One top-level admin page rendered as a data-driven
 * TABS + SUBTABS layout. To add a tab or subtab, edit tabs() only - the
 * navigation, routing and view loading all update automatically.
 *
 * Tab views live in /admin/tabs/ and are loaded through a realpath-guarded
 * whitelist (load_view) so a tampered ?tab / ?subtab can never include an
 * arbitrary file.
 */
class ADN_Theme_Admin {

	const MENU_SLUG  = 'adn-theme';
	const CAPABILITY = 'manage_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_menu', array( __CLASS__, 'hide_plugin_menus' ), 999 );

		// Reusable settings engine (shared admin-post save handler for all tabs).
		ADN_Theme_Settings::init();

		// Import / Export (theme settings as JSON).
		add_action( 'admin_post_adn_export_settings', array( __CLASS__, 'handle_export_settings' ) );
		add_action( 'admin_post_adn_import_settings', array( __CLASS__, 'handle_import_settings' ) );

		// Manage Calculator → Calculator List (per-calculator controls).
		add_action( 'admin_post_adn_save_calc_list', array( __CLASS__, 'handle_save_calc_list' ) );

		// Manage Calculator → Add / Edit DB calculator.
		add_action( 'admin_post_adn_save_calc_new',   array( __CLASS__, 'handle_save_calc_new' ) );
		add_action( 'admin_post_adn_delete_calc',     array( __CLASS__, 'handle_delete_calc' ) );

		// Experts / Team → Add / Edit / Delete DB expert.
		add_action( 'admin_post_adn_save_expert',        array( __CLASS__, 'handle_save_expert' ) );
		add_action( 'admin_post_adn_delete_expert',      array( __CLASS__, 'handle_delete_expert' ) );
		add_action( 'admin_post_adn_save_expert_banner', array( __CLASS__, 'handle_save_expert_banner' ) );

		// Manage Calculator → Page Content settings.
		add_action( 'admin_post_adn_save_tools_page', array( __CLASS__, 'handle_save_tools_page' ) );

		// Admin Actions → Sample Data (seed / remove demo content).
		add_action( 'admin_post_adn_seed_content',     array( __CLASS__, 'handle_seed_content' ) );
		add_action( 'admin_post_adn_remove_mock_data', array( __CLASS__, 'handle_remove_mock_data' ) );

		// Admin Actions handlers (admin-post.php endpoints).
		add_action( 'admin_post_adn_clear_cache',          array( __CLASS__, 'handle_clear_cache' ) );
		add_action( 'admin_post_adn_sync_pages',           array( __CLASS__, 'handle_sync_pages' ) );
		add_action( 'admin_post_adn_flush_rewrites',       array( __CLASS__, 'handle_flush_rewrites' ) );
		add_action( 'admin_post_adn_install_contact_rule', array( __CLASS__, 'handle_install_contact_rule' ) );

		// Category Pages: per-term journey / calculators / sidebar / CTA settings.
		add_action( 'admin_post_adn_save_category_term',  array( __CLASS__, 'handle_save_category_term' ) );
		add_action( 'admin_post_adn_save_home_newsblocks', array( __CLASS__, 'handle_save_home_newsblocks' ) );
		add_action( 'admin_post_adn_save_home_resources',  array( __CLASS__, 'handle_save_home_resources'  ) );
		add_action( 'admin_post_adn_save_home_journey',    array( __CLASS__, 'handle_save_home_journey'    ) );

		// Category Pages: AJAX post search (Hot Topics + Popular Posts).
		add_action( 'wp_ajax_adn_cat_post_search', array( __CLASS__, 'handle_cat_post_search' ) );

		// Category Pages: AJAX taxonomy term search (Featured Topics).
		add_action( 'wp_ajax_adn_cat_tax_search', array( __CLASS__, 'handle_cat_taxonomy_search' ) );

		// Category Pages: AJAX FAQ search (from plugin ah_faqs table).
		add_action( 'wp_ajax_adn_cat_faq_search', array( __CLASS__, 'handle_cat_faq_search' ) );

		// Enqueue wp.media on our admin page (required for the thumbnail uploader).
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );

		// Redirect to include &subtab= in the URL when missing, so form hidden fields work.
		// Must run on admin_init (before any output) - NOT inside render_page().
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect_to_subtab' ) );
	}

	public static function enqueue_admin_assets( $hook ) {
		if ( false === strpos( $hook, self::MENU_SLUG ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'adn-fontawesome-admin', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
	}

	/**
	 * Tab + subtab registry - the single source of truth for the whole page.
	 *
	 * Each tab has a 'label' and EITHER a 'view' (no subtabs) OR a 'subtabs'
	 * array (each subtab has its own 'label' + 'view'). View paths are
	 * relative to /admin/tabs/.
	 */
	/** Return a nav label with an inline Font Awesome icon prefix. */
	private static function fa( $icon_class, $label ) {
		return '<i class="fa-solid ' . esc_attr( $icon_class ) . '" style="margin-right:5px;opacity:.75;"></i>' . esc_html( $label );
	}

	public static function tabs() {
		$tools_label = defined( 'SITE_TOOLS_PLURAL' ) ? SITE_TOOLS_PLURAL : __( 'Calculator', ADN_TEXT_DOMAIN );
		return array(
			'dashboard' => array(
				'label' => self::fa( 'fa-gauge-high', __( 'Dashboard', ADN_TEXT_DOMAIN ) ),
				'view'  => 'tab-dashboard.php',
			),
			'home' => array(
				'label'   => self::fa( 'fa-house', __( 'Home Page', ADN_TEXT_DOMAIN ) ),
				'subtabs' => array(
					'sections' => array(
						'label' => self::fa( 'fa-table-cells-large', __( 'Sections', ADN_TEXT_DOMAIN ) ),
						'view'  => 'home/sub-sections.php',
					),
					'hero' => array(
						'label' => self::fa( 'fa-star', __( 'Hero & Intro', ADN_TEXT_DOMAIN ) ),
						'view'  => 'home/sub-hero.php',
					),
					'featured' => array(
						'label' => self::fa( 'fa-bookmark', __( 'Featured Guides', ADN_TEXT_DOMAIN ) ),
						'view'  => 'home/sub-featured.php',
					),
					'newsblocks' => array(
						'label' => self::fa( 'fa-newspaper', __( 'Regulations & Hot Topics', ADN_TEXT_DOMAIN ) ),
						'view'  => 'home/sub-newsblocks.php',
					),
					'resources' => array(
						'label' => self::fa( 'fa-folder-open', __( 'Resources', ADN_TEXT_DOMAIN ) ),
						'view'  => 'home/sub-resources.php',
					),
					'journey' => array(
						'label' => self::fa( 'fa-images', __( 'Journey Cards', ADN_TEXT_DOMAIN ) ),
						'view'  => 'home/sub-journey.php',
					),
				),
			),
			'calculators' => array(
				/* translators: %s: site-specific tool noun e.g. "Calculators" */
				'label'   => self::fa( 'fa-calculator', sprintf( __( 'Manage %s', ADN_TEXT_DOMAIN ), $tools_label ) ),
				'subtabs' => array(
					'general' => array(
						'label' => self::fa( 'fa-sliders', __( 'Heading & Banner', ADN_TEXT_DOMAIN ) ),
						'view'  => 'calculators/sub-general.php',
					),
					'list' => array(
						'label' => self::fa( 'fa-list', __( 'Tool List', ADN_TEXT_DOMAIN ) ),
						'view'  => 'calculators/sub-list.php',
					),
					'page' => array(
						'label' => self::fa( 'fa-file-lines', __( 'Page Content', ADN_TEXT_DOMAIN ) ),
						'view'  => 'calculators/sub-page.php',
					),
					'new' => array(
						'label' => self::fa( 'fa-plus', __( 'Add / Edit', ADN_TEXT_DOMAIN ) ),
						'view'  => 'calculators/sub-new.php',
					),
				),
			),
			'experts' => array(
				'label'   => self::fa( 'fa-user-tie', __( 'Experts / Team', ADN_TEXT_DOMAIN ) ),
				'subtabs' => array(
					'list' => array(
						'label' => self::fa( 'fa-users', __( 'Expert List', ADN_TEXT_DOMAIN ) ),
						'view'  => 'experts/sub-list.php',
					),
					'new' => array(
						'label' => self::fa( 'fa-user-plus', __( 'Add / Edit', ADN_TEXT_DOMAIN ) ),
						'view'  => 'experts/sub-new.php',
					),
					'banner' => array(
						'label' => self::fa( 'fa-image', __( 'Expert Banner', ADN_TEXT_DOMAIN ) ),
						'view'  => 'experts/sub-banner.php',
					),
				),
			),
			'contact-inbox' => array(
				'label' => self::fa( 'fa-envelope-open-text', __( 'Contact Inbox', ADN_TEXT_DOMAIN ) ),
				'view'  => 'tab-contact-inbox.php',
			),
			'guidance-inbox' => array(
				'label' => self::fa( 'fa-clipboard-list', __( 'Guidance Inbox', ADN_TEXT_DOMAIN ) ),
				'view'  => 'tab-guidance-inbox.php',
			),
			'import-export' => array(
				'label' => self::fa( 'fa-arrow-right-arrow-left', __( 'Import / Export', ADN_TEXT_DOMAIN ) ),
				'view'  => 'tab-import-export.php',
			),
			'category-pages' => array(
				'label'   => self::fa( 'fa-folder-open', __( 'Category Pages', ADN_TEXT_DOMAIN ) ),
				'subtabs' => self::category_subtabs(),
			),
			'admin-actions' => array(
				'label'   => self::fa( 'fa-screwdriver-wrench', __( 'Admin Actions', ADN_TEXT_DOMAIN ) ),
				'subtabs' => array(
					'cache' => array(
						'label' => self::fa( 'fa-rotate', __( 'Cache', ADN_TEXT_DOMAIN ) ),
						'view'  => 'admin-actions/sub-cache.php',
					),
					'pages' => array(
						'label' => self::fa( 'fa-sitemap', __( 'Pages & Permalinks', ADN_TEXT_DOMAIN ) ),
						'view'  => 'admin-actions/sub-pages.php',
					),
					'rules' => array(
						'label' => self::fa( 'fa-bolt', __( 'Workflow Manager', ADN_TEXT_DOMAIN ) ),
						'view'  => 'admin-actions/sub-rules.php',
					),
					'sample-data' => array(
						'label' => self::fa( 'fa-database', __( 'Sample Data', ADN_TEXT_DOMAIN ) ),
						'view'  => 'admin-actions/sub-sample-data.php',
					),
				),
			),
		);
	}

	public static function hide_plugin_menus() {
		remove_submenu_page( 'ah-dashboard', 'ah-reviews' );
		remove_submenu_page( 'ah-dashboard', 'ah-client-stories' );
	}

	public static function register_menu() {
		add_menu_page(
			__( 'CMS THEME', ADN_TEXT_DOMAIN ),
			__( 'CMS THEME', ADN_TEXT_DOMAIN ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-home',
			3
		);

		// Register each top-level tab as a real WordPress submenu page, so the
		// areas appear in the admin sidebar (a division mechanism beyond the
		// in-page tabs). The first tab reuses the parent slug so WordPress does
		// not show a duplicate entry. Subtabs stay as in-page tabs.
		$first = array_key_first( self::tabs() );
		foreach ( self::tabs() as $key => $def ) {
			$slug = ( $key === $first ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $key;
			add_submenu_page(
				self::MENU_SLUG,
				$def['label'],
				$def['label'],
				self::CAPABILITY,
				$slug,
				array( __CLASS__, 'render_page' )
			);
		}
	}

	/** The WP page slug for a tab's submenu (first tab reuses the parent slug). */
	public static function tab_page_slug( $tab ) {
		$first = array_key_first( self::tabs() );
		return ( $tab === $first ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $tab;
	}

	// ── Routing helpers ─────────────────────────────────────────────────────────

	private static function active_tab() {
		$tabs = self::tabs();

		// Prefer the submenu page slug (adn-theme / adn-theme-{key}).
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( $page && $page !== self::MENU_SLUG && 0 === strpos( $page, self::MENU_SLUG . '-' ) ) {
			$key = substr( $page, strlen( self::MENU_SLUG ) + 1 );
			if ( isset( $tabs[ $key ] ) ) {
				return $key;
			}
		}

		// Back-compat: ?tab=, else the first tab.
		$req = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		return isset( $tabs[ $req ] ) ? $req : array_key_first( $tabs );
	}

	private static function active_subtab( $tab_key ) {
		$tabs = self::tabs();
		$subs = isset( $tabs[ $tab_key ]['subtabs'] ) ? $tabs[ $tab_key ]['subtabs'] : array();
		if ( empty( $subs ) ) {
			return '';
		}
		$req = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : '';
		return isset( $subs[ $req ] ) ? $req : array_key_first( $subs );
	}

	private static function tab_url( $tab, $subtab = '' ) {
		$args = array( 'page' => self::tab_page_slug( $tab ) );
		if ( $subtab ) {
			$args['subtab'] = $subtab;
		}
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Fired on admin_init (before any output).
	 * When the active tab has subtabs but &subtab= is absent from the URL,
	 * redirect to add it - so sub-views can safely read $_GET['subtab'] for form fields.
	 */
	public static function maybe_redirect_to_subtab() {
		if ( ! is_admin() || ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( '' === $page ) {
			return;
		}

		// Only act when viewing one of our admin pages.
		$tabs = self::tabs();
		$matched_tab = '';
		foreach ( $tabs as $tab_key => $tab_def ) {
			if ( self::tab_page_slug( $tab_key ) === $page ) {
				$matched_tab = $tab_key;
				break;
			}
		}
		if ( '' === $matched_tab ) {
			return;
		}

		// Only tabs that have subtabs need this redirect.
		if ( empty( $tabs[ $matched_tab ]['subtabs'] ) ) {
			return;
		}

		// If &subtab= is already present and valid, nothing to do.
		$req_sub = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : '';
		if ( '' !== $req_sub && isset( $tabs[ $matched_tab ]['subtabs'][ $req_sub ] ) ) {
			return;
		}

		// Redirect to inject the first (default) subtab key.
		$first_sub = (string) array_key_first( $tabs[ $matched_tab ]['subtabs'] );
		if ( '' === $first_sub ) {
			return;
		}

		wp_safe_redirect( self::tab_url( $matched_tab, $first_sub ) );
		exit;
	}

	/**
	 * Load a tab view safely: only files that resolve to inside /admin/tabs/
	 * are ever included (whitelist via the registry + realpath containment).
	 */
	private static function load_view( $relative ) {
		$base = realpath( ADN_THEME_DIR . '/admin/tabs' );
		$path = realpath( ADN_THEME_DIR . '/admin/tabs/' . $relative );
		if ( $base && $path && 0 === strpos( $path, $base ) && is_file( $path ) ) {
			require $path;
			return;
		}
		echo '<div class="notice notice-error"><p>' . esc_html__( 'View not found.', ADN_TEXT_DOMAIN ) . '</p></div>';
	}

	// ── Page renderer ───────────────────────────────────────────────────────────

	public static function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', ADN_TEXT_DOMAIN ) );
		}

		$tabs       = self::tabs();
		$active      = self::active_tab();
		$active_sub  = self::active_subtab( $active );
		$tab         = $tabs[ $active ];

		$view = ! empty( $tab['subtabs'] )
			? ( isset( $tab['subtabs'][ $active_sub ]['view'] ) ? $tab['subtabs'][ $active_sub ]['view'] : '' )
			: ( isset( $tab['view'] ) ? $tab['view'] : '' );

		self::render_notice();
		?>
		<div class="wrap adn-admin">
			<h1><?php echo wp_kses( $tab['label'], array( 'i' => array( 'class' => array(), 'style' => array(), 'aria-hidden' => array() ) ) ); ?></h1>

			<?php /* Sidebar submenus are the top level; a section's own subtabs
			         render here as its in-page tabs (no duplicate of the sidebar). */ ?>
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
				<?php
				if ( $view ) {
					self::load_view( $view );
				} else {
					echo '<p>' . esc_html__( 'Nothing here yet.', ADN_TEXT_DOMAIN ) . '</p>';
				}
				?>
			</div>
		</div>
		<?php
	}

	// ── Post-action notice (after redirect) ─────────────────────────────────────

	private static function render_notice() {
		if ( ! empty( $_GET['adn_err'] ) ) {
			$msg = isset( $_GET['adn_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['adn_msg'] ) ) : __( 'An error occurred.', ADN_TEXT_DOMAIN );
			printf(
				'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
				esc_html( $msg )
			);
			return;
		}
		if ( empty( $_GET['adn_done'] ) ) {
			return;
		}
		$msg = isset( $_GET['adn_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['adn_msg'] ) ) : __( 'Done.', ADN_TEXT_DOMAIN );
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( $msg )
		);
	}

	private static function redirect_back_error( $tab, $subtab, $msg ) {
		wp_safe_redirect( add_query_arg(
			array(
				'adn_err' => 1,
				'adn_msg' => rawurlencode( $msg ),
			),
			self::tab_url( $tab, $subtab )
		) );
		exit;
	}

	private static function redirect_back( $tab, $subtab, $msg ) {
		wp_safe_redirect( add_query_arg(
			array(
				'adn_done' => 1,
				'adn_msg'  => rawurlencode( $msg ),
			),
			self::tab_url( $tab, $subtab )
		) );
		exit;
	}

	// ── Action: Clear cache ─────────────────────────────────────────────────────

	public static function handle_clear_cache() {
		check_admin_referer( 'adn_clear_cache' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$cleared = array();

		// 1) Object cache.
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
			$cleared[] = __( 'object cache', ADN_TEXT_DOMAIN );
		}

		// 2) This theme's transients (anything prefixed adn_).
		global $wpdb;
		$like  = $wpdb->esc_like( '_transient_adn_' ) . '%';
		$names = $wpdb->get_col( $wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			$like
		) );
		foreach ( (array) $names as $option_name ) {
			delete_transient( str_replace( '_transient_', '', $option_name ) );
		}
		if ( ! empty( $names ) ) {
			/* translators: %d: number of transients deleted */
			$cleared[] = sprintf( _n( '%d transient', '%d transients', count( $names ), ADN_TEXT_DOMAIN ), count( $names ) );
		}

		if ( function_exists( 'cache_clear_all' ) ) {
			cache_clear_all( null, 'home_frag', true );
			$cleared[] = __( 'home fragment cache', ADN_TEXT_DOMAIN );
		}

		// 3) OPcache (if available).
		if ( function_exists( 'opcache_reset' ) ) {
			@opcache_reset(); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$cleared[] = 'OPcache';
		}

		$msg = ! empty( $cleared )
			/* translators: %s: comma-separated list of caches cleared */
			? sprintf( __( 'Cache cleared: %s.', ADN_TEXT_DOMAIN ), implode( ', ', $cleared ) )
			: __( 'Nothing to clear.', ADN_TEXT_DOMAIN );

		self::redirect_back( 'admin-actions', 'cache', $msg );
	}

	// ── Action: Sync default pages + flush permalinks ───────────────────────────

	public static function handle_sync_pages() {
		check_admin_referer( 'adn_sync_pages' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$created = 0;
		if ( function_exists( 'adn_create_default_pages' ) ) {
			$created = (int) adn_create_default_pages();
		}
		flush_rewrite_rules();

		/* translators: %d: number of pages created */
		$msg = sprintf( __( 'Pages synced (%d created) and permalinks flushed.', ADN_TEXT_DOMAIN ), $created );
		self::redirect_back( 'admin-actions', 'pages', $msg );
	}

	// ── Action: Flush permalinks only ───────────────────────────────────────────

	public static function handle_flush_rewrites() {
		check_admin_referer( 'adn_flush_rewrites' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}
		flush_rewrite_rules();
		self::redirect_back( 'admin-actions', 'pages', __( 'Permalinks flushed.', ADN_TEXT_DOMAIN ) );
	}

	// ── Action: Install sample contact rule into the Rules Engine ────────────────

	/** Name used to detect whether the sample rule is already installed (brand from COMPANY_NAME). */
	public static function sample_rule_name() {
		return COMPANY_NAME . ' - Contact Form Email (sample)';
	}

	/** Count active rules in the engine for one trigger. -1 = plugin inactive. */
	public static function count_rules_for_trigger( $trigger ) {
		if ( ! class_exists( 'AH_Workflow_Manager' ) ) {
			return -1;
		}
		$count = 0;
		foreach ( AH_Workflow_Manager::get_all() as $rule ) {
			if ( $rule->trigger_name === $trigger && 'active' === $rule->status ) {
				$count++;
			}
		}
		return $count;
	}

	public static function handle_install_contact_rule() {
		check_admin_referer( 'adn_install_contact_rule' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		if ( ! class_exists( 'AH_Workflow_Manager' ) || ! class_exists( 'ADN_Rules' ) ) {
			self::redirect_back( 'admin-actions', 'rules', __( 'The CMS plugin (AH_Workflow_Manager) is not active - activate it first.', ADN_TEXT_DOMAIN ) );
		}

		// Already installed? Don't create a duplicate.
		foreach ( AH_Workflow_Manager::get_all() as $rule ) {
			if ( self::sample_rule_name() === $rule->name ) {
				self::redirect_back( 'admin-actions', 'rules', __( 'Sample contact rule already exists - edit it in the CMS plugin.', ADN_TEXT_DOMAIN ) );
			}
		}

		AH_Workflow_Manager::install_tables();

		$rule_id = AH_Workflow_Manager::save( 0, array(
			'name'             => self::sample_rule_name(),
			'trigger_name'     => ADN_Rules::CONTACT_FORM,
			'conditions_match' => 'all',
			'conditions'       => array(), // no conditions = fires on every contact submission
			'status'           => 'active',
			'actions'          => array(
				array(
					'type'    => 'send_email',
					'to'      => array( get_option( 'admin_email' ) ),
					'subject' => '[' . COMPANY_NAME . '] New contact enquiry from {name}',
					'html'    => 1,
					'body'    => '<h2>New contact form submission</h2>'
						. '<p><strong>Name:</strong> {name}<br>'
						. '<strong>Email:</strong> {email}<br>'
						. '<strong>Phone:</strong> {phone}<br>'
						. '<strong>Topic:</strong> {topic}</p>'
						. '<p><strong>Message:</strong><br>{message}</p>'
						. '<p>Submitted at {submitted_at} - {site_url} (submission #{submission_id})</p>',
				),
			),
		) );

		$msg = $rule_id
			? __( 'Sample contact rule installed - submissions now email the site admin. Edit it in the CMS plugin.', ADN_TEXT_DOMAIN )
			: __( 'Could not create the rule - check the CMS plugin.', ADN_TEXT_DOMAIN );
		self::redirect_back( 'admin-actions', 'rules', $msg );
	}

	// ── Import / Export: theme settings as JSON ─────────────────────────────────

	/** Stream every theme-settings option as a downloadable JSON file. */
	public static function handle_export_settings() {
		check_admin_referer( 'adn_export_settings' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$settings = array();
		foreach ( adn_settings_schemas() as $schema ) {
			$settings[ $schema['option'] ] = get_option( $schema['option'], null );
		}

		$payload = array(
			'theme'       => 'advaithhomes_new',
			'exported_at' => gmdate( 'c' ),
			'settings'    => $settings,
		);

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="advaith-settings-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/** Import theme settings from an uploaded JSON file (recognised options only). */
	public static function handle_import_settings() {
		check_admin_referer( 'adn_import_settings' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		if ( empty( $_FILES['settings_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['settings_file']['tmp_name'] ) ) {
			self::redirect_back( 'import-export', '', __( 'No file uploaded.', ADN_TEXT_DOMAIN ) );
		}

		$raw    = file_get_contents( $_FILES['settings_file']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$parsed = json_decode( (string) $raw, true );
		if ( ! is_array( $parsed ) ) {
			self::redirect_back( 'import-export', '', __( 'That file is not valid JSON.', ADN_TEXT_DOMAIN ) );
		}

		$settings = ( isset( $parsed['settings'] ) && is_array( $parsed['settings'] ) ) ? $parsed['settings'] : $parsed;

		// Whitelist: only recognised theme-settings options, array values only.
		$allowed = array();
		foreach ( adn_settings_schemas() as $schema ) {
			$allowed[ $schema['option'] ] = true;
		}

		$count = 0;
		foreach ( $settings as $option => $value ) {
			if ( isset( $allowed[ $option ] ) && is_array( $value ) ) {
				update_option( $option, $value );
				$count++;
			}
		}

		/* translators: %d: number of settings groups imported */
		self::redirect_back( 'import-export', '', sprintf( __( 'Imported %d settings group(s).', ADN_TEXT_DOMAIN ), $count ) );
	}

	// ── Manage Calculator: per-calculator list (enabled / label / help / guide) ──

	public static function handle_save_calc_list() {
		check_admin_referer( 'adn_save_calc_list' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$input = ( isset( $_POST['calc'] ) && is_array( $_POST['calc'] ) ) ? wp_unslash( $_POST['calc'] ) : array();
		$meta  = array();

		// Allowed category slugs - dynamic when the categories function is available.
		$allowed_cats = function_exists( 'adn_calculator_categories' )
			? array_keys( adn_calculator_categories() )
			: array();

		// Iterate the registered calculators (the source of truth), not POST keys.
		foreach ( adn_calculators() as $key => $calc ) {
			$row       = isset( $input[ $key ] ) && is_array( $input[ $key ] ) ? $input[ $key ] : array();
			$raw_cats  = ( isset( $row['categories'] ) && is_array( $row['categories'] ) ) ? $row['categories'] : array();
			$clean_cats = array();
			foreach ( $raw_cats as $c ) {
				$c = sanitize_key( $c );
				if ( in_array( $c, $allowed_cats, true ) ) { $clean_cats[] = $c; }
			}
			$meta[ $key ] = array(
				'enabled'             => empty( $row['enabled'] ) ? 0 : 1,
				'label'               => sanitize_text_field( isset( $row['label'] ) ? $row['label'] : '' ),
				'desc'                => sanitize_textarea_field( isset( $row['desc'] ) ? $row['desc'] : '' ),
				'categories'          => $clean_cats,
				'thumbnail_id'        => absint( isset( $row['thumbnail_id'] ) ? $row['thumbnail_id'] : 0 ),
				'highlight'           => sanitize_text_field( isset( $row['highlight'] ) ? $row['highlight'] : '' ),
				'is_popular'          => empty( $row['is_popular'] ) ? 0 : 1,
				'hidden_from_listing' => empty( $row['hidden_from_listing'] ) ? 0 : 1,
				'help'                => sanitize_textarea_field( isset( $row['help'] ) ? $row['help'] : '' ),
				'card_url'            => esc_url_raw( isset( $row['card_url'] ) ? $row['card_url'] : '' ),
				'guide_label'         => sanitize_text_field( isset( $row['guide_label'] ) ? $row['guide_label'] : '' ),
				'guide_url'           => esc_url_raw( isset( $row['guide_url'] ) ? $row['guide_url'] : '' ),
				'hl_heading'          => sanitize_text_field( isset( $row['hl_heading'] ) ? $row['hl_heading'] : '' ),
				'hl_links'            => (function() use ( $row ) {
					$out      = array();
					$raw_list = ( isset( $row['hl_links'] ) && is_array( $row['hl_links'] ) ) ? $row['hl_links'] : array();
					foreach ( $raw_list as $item ) {
						if ( ! is_array( $item ) ) { continue; }
						$i_label = sanitize_text_field( isset( $item['label'] ) ? $item['label'] : '' );
						if ( '' === $i_label ) { continue; }
						$out[] = array(
							'icon'  => sanitize_text_field( isset( $item['icon'] )  ? $item['icon']  : '' ),
							'label' => $i_label,
							'url'   => esc_url_raw( isset( $item['url'] ) ? $item['url'] : '' ),
						);
					}
					return $out;
				})(),
			);
		}

		update_option( 'adn_calculators_meta', $meta );
		self::redirect_back( 'calculators', 'list', __( 'Calculator list saved.', ADN_TEXT_DOMAIN ) );
	}

	// ── Add / Edit DB calculator ────────────────────────────────────────────────────

	public static function handle_save_calc_new() {
		check_admin_referer( 'adn_save_calc_new' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}
		if ( ! class_exists( 'AH_Calculator_DB' ) ) {
			wp_die( esc_html__( 'Calculator DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$edit_key = isset( $_POST['edit_key'] ) ? sanitize_key( wp_unslash( $_POST['edit_key'] ) ) : '';
		$is_edit  = '' !== $edit_key;

		// Determine the key: for new calcs use submitted value; for edits reuse the existing key.
		$key = $is_edit ? $edit_key : sanitize_key( isset( $_POST['calc_key'] ) ? wp_unslash( $_POST['calc_key'] ) : '' );

		if ( '' === $key ) {
			wp_die( esc_html__( 'Calculator key is required.', ADN_TEXT_DOMAIN ) );
		}
		if ( sanitize_text_field( wp_unslash( isset( $_POST['title'] ) ? $_POST['title'] : '' ) ) === '' ) {
			wp_die( esc_html__( 'Title is required.', ADN_TEXT_DOMAIN ) );
		}

		// When adding a new calc, prevent overwriting an existing one (file or DB).
		if ( ! $is_edit ) {
			$file_tools = function_exists( 'adn_calculators' ) ? array_keys( adn_calculators() ) : array();
			if ( in_array( $key, $file_tools, true ) || null !== AH_Calculator_DB::get( $key ) ) {
				wp_die( esc_html( sprintf(
					__( 'A calculator with the key "%s" already exists. Choose a different key.', ADN_TEXT_DOMAIN ),
					$key
				) ) );
			}
		}

		// Ensure the DB table exists before inserting (lazy install fallback).
		AH_Calculator_DB::maybe_install();

		$saved = AH_Calculator_DB::save( array(
			'calc_key'     => $key,
			'title'        => wp_unslash( isset( $_POST['title'] )        ? $_POST['title']        : '' ),
			'icon'         => wp_unslash( isset( $_POST['icon'] )         ? $_POST['icon']         : '' ),
			'label'        => wp_unslash( isset( $_POST['label'] )        ? $_POST['label']        : '' ),
			'html_content' => wp_unslash( isset( $_POST['html_content'] ) ? $_POST['html_content'] : '' ),
			'js_content'   => wp_unslash( isset( $_POST['js_content'] )   ? $_POST['js_content']   : '' ),
			'status'       => wp_unslash( isset( $_POST['status'] )       ? $_POST['status']       : 'active' ),
		) );

		if ( ! $saved ) {
			global $wpdb;
			wp_die( esc_html__( 'Could not save calculator to the database.', ADN_TEXT_DOMAIN )
				. ( $wpdb->last_error ? ' Error: ' . esc_html( $wpdb->last_error ) : '' ) );
		}

		// Persist admin meta settings for this calculator.
		$all_meta = get_option( 'adn_calculators_meta', array() );
		if ( ! is_array( $all_meta ) ) { $all_meta = array(); }

		$raw_cats = ( isset( $_POST['meta_categories'] ) && is_array( $_POST['meta_categories'] ) )
			? array_map( 'sanitize_key', wp_unslash( $_POST['meta_categories'] ) )
			: array();

		$all_meta[ $key ] = array_merge(
			isset( $all_meta[ $key ] ) && is_array( $all_meta[ $key ] ) ? $all_meta[ $key ] : array(),
			array(
				'desc'         => sanitize_textarea_field( wp_unslash( isset( $_POST['meta_desc'] )        ? $_POST['meta_desc']        : '' ) ),
				'categories'   => $raw_cats,
				'parent_terms' => ( isset( $_POST['meta_parent_terms'] ) && is_array( $_POST['meta_parent_terms'] ) )
					? array_values( array_filter( array_map( 'sanitize_key', wp_unslash( $_POST['meta_parent_terms'] ) ) ) )
					: array(),
				'thumbnail_id' => absint( isset( $_POST['meta_thumbnail_id'] ) ? $_POST['meta_thumbnail_id'] : 0 ),
				'highlight'    => sanitize_text_field( wp_unslash( isset( $_POST['meta_highlight'] )  ? $_POST['meta_highlight']  : '' ) ),
				'is_popular'          => empty( $_POST['meta_is_popular'] ) ? 0 : 1,
				'is_featured'         => empty( $_POST['meta_is_featured'] ) ? 0 : 1,
				'is_suggestion'       => empty( $_POST['meta_is_suggestion'] ) ? 0 : 1,
				'featured_title'      => sanitize_text_field( wp_unslash( isset( $_POST['meta_featured_title'] ) ? $_POST['meta_featured_title'] : '' ) ),
				'featured_desc'       => sanitize_textarea_field( wp_unslash( isset( $_POST['meta_featured_desc'] ) ? $_POST['meta_featured_desc'] : '' ) ),
				'benefit_1'           => sanitize_text_field( wp_unslash( isset( $_POST['meta_benefit_1'] ) ? $_POST['meta_benefit_1'] : '' ) ),
				'benefit_2'           => sanitize_text_field( wp_unslash( isset( $_POST['meta_benefit_2'] ) ? $_POST['meta_benefit_2'] : '' ) ),
				'benefit_3'           => sanitize_text_field( wp_unslash( isset( $_POST['meta_benefit_3'] ) ? $_POST['meta_benefit_3'] : '' ) ),
				'benefit_4'           => sanitize_text_field( wp_unslash( isset( $_POST['meta_benefit_4'] ) ? $_POST['meta_benefit_4'] : '' ) ),
				'hidden_from_listing' => empty( $_POST['meta_hidden_from_listing'] ) ? 0 : 1,
				'card_url'     => esc_url_raw( wp_unslash( isset( $_POST['meta_card_url'] )    ? $_POST['meta_card_url']    : '' ) ),
				'help'         => sanitize_textarea_field( wp_unslash( isset( $_POST['meta_help'] )        ? $_POST['meta_help']        : '' ) ),
				'guide_label'     => sanitize_text_field( wp_unslash( isset( $_POST['meta_guide_label'] ) ? $_POST['meta_guide_label'] : '' ) ),
				'guide_url'       => esc_url_raw( wp_unslash( isset( $_POST['meta_guide_url'] )   ? $_POST['meta_guide_url']   : '' ) ),
				'before_content'  => wp_kses_post( wp_unslash( isset( $_POST['meta_before_content'] ) ? $_POST['meta_before_content'] : '' ) ),
				'after_content'   => wp_kses_post( wp_unslash( isset( $_POST['meta_after_content'] )  ? $_POST['meta_after_content']  : '' ) ),
				'hl_heading'      => sanitize_text_field( wp_unslash( isset( $_POST['meta_hl_heading'] ) ? $_POST['meta_hl_heading'] : '' ) ),
				'hl_links'     => (function() {
					$out      = array();
					$raw_list = ( isset( $_POST['meta_hl_links'] ) && is_array( $_POST['meta_hl_links'] ) )
						? wp_unslash( $_POST['meta_hl_links'] )
						: array();
					foreach ( $raw_list as $item ) {
						if ( ! is_array( $item ) ) { continue; }
						$i_label = sanitize_text_field( isset( $item['label'] ) ? $item['label'] : '' );
						if ( '' === $i_label ) { continue; }
						$out[] = array(
							'icon'  => sanitize_text_field( isset( $item['icon'] )  ? $item['icon']  : '' ),
							'label' => $i_label,
							'url'   => esc_url_raw( isset( $item['url'] ) ? $item['url'] : '' ),
						);
					}
					return $out;
				})(),
			)
		);

		update_option( 'adn_calculators_meta', $all_meta );

		if ( $is_edit ) {
			// Editing: redirect back to the same edit form so user sees their saved data.
			$msg = __( 'Calculator updated.', ADN_TEXT_DOMAIN );
			wp_safe_redirect( add_query_arg(
				array( 'edit_key' => $key, 'adn_done' => 1, 'adn_msg' => rawurlencode( $msg ) ),
				self::tab_url( 'calculators', 'new' )
			) );
		} else {
			// New: redirect to the edit form so user sees the saved row (not a blank add form).
			$msg = sprintf(
				__( 'Calculator saved. Embed with [ah_calculator key="%s"]', ADN_TEXT_DOMAIN ),
				$key
			);
			wp_safe_redirect( add_query_arg(
				array( 'edit_key' => $key, 'adn_done' => 1, 'adn_msg' => rawurlencode( $msg ) ),
				self::tab_url( 'calculators', 'new' )
			) );
		}
		exit;
	}

	public static function handle_delete_calc() {
		check_admin_referer( 'adn_delete_calc' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}
		if ( ! class_exists( 'AH_Calculator_DB' ) ) {
			wp_die( esc_html__( 'Calculator DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$key = sanitize_key( wp_unslash( isset( $_POST['calc_key'] ) ? $_POST['calc_key'] : '' ) );
		if ( '' === $key ) { wp_die( esc_html__( 'No key provided.', ADN_TEXT_DOMAIN ) ); }

		// Safety: only delete DB-stored calcs, not file-based ones.
		if ( null === AH_Calculator_DB::get( $key ) ) {
			wp_die( esc_html__( 'This calculator is file-based and cannot be deleted from the admin.', ADN_TEXT_DOMAIN ) );
		}

		AH_Calculator_DB::delete( $key );
		self::redirect_back( 'calculators', 'list', __( 'Calculator deleted.', ADN_TEXT_DOMAIN ) );
	}

	// ── Calculators: page content settings ──────────────────────────────────────────

	public static function handle_save_tools_page() {
		check_admin_referer( 'adn_save_tools_page' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$pg = array();

		// Hero.
		$pg['hero_title'] = sanitize_text_field( wp_unslash( isset( $_POST['hero_title'] ) ? $_POST['hero_title'] : '' ) );
		$pg['hero_desc']  = sanitize_textarea_field( wp_unslash( isset( $_POST['hero_desc'] ) ? $_POST['hero_desc'] : '' ) );
		$pg['hero_icon']  = sanitize_text_field( wp_unslash( isset( $_POST['hero_icon'] ) ? $_POST['hero_icon'] : '' ) );

		// Trust bar (4 items).
		for ( $i = 1; $i <= 4; $i++ ) {
			$pg[ 'trust_' . $i . '_icon' ]     = sanitize_text_field( wp_unslash( isset( $_POST[ 'trust_' . $i . '_icon' ] ) ? $_POST[ 'trust_' . $i . '_icon' ] : '' ) );
			$pg[ 'trust_' . $i . '_title' ]    = sanitize_text_field( wp_unslash( isset( $_POST[ 'trust_' . $i . '_title' ] ) ? $_POST[ 'trust_' . $i . '_title' ] : '' ) );
			$pg[ 'trust_' . $i . '_subtitle' ] = sanitize_text_field( wp_unslash( isset( $_POST[ 'trust_' . $i . '_subtitle' ] ) ? $_POST[ 'trust_' . $i . '_subtitle' ] : '' ) );
		}

		// Search.
		$pg['search_placeholder'] = sanitize_text_field( wp_unslash( isset( $_POST['search_placeholder'] ) ? $_POST['search_placeholder'] : '' ) );

		// Sidebar highlight sections (1 and 2).
		foreach ( array( 1, 2 ) as $_sn ) {
			$_hkey = 'sidebar_hl' . $_sn . '_heading';
			$_ikey = 'sidebar_hl' . $_sn . '_items';
			$pg[ $_hkey ] = sanitize_text_field( wp_unslash( isset( $_POST[ $_hkey ] ) ? $_POST[ $_hkey ] : '' ) );
			$_raw_items   = isset( $_POST[ $_ikey ] ) && is_array( $_POST[ $_ikey ] ) ? $_POST[ $_ikey ] : array();
			$pg[ $_ikey ] = array();
			foreach ( array_slice( $_raw_items, 0, 6 ) as $_row ) {
				$_lbl = sanitize_text_field( wp_unslash( isset( $_row['label'] ) ? $_row['label'] : '' ) );
				if ( '' === $_lbl ) { continue; }
				$pg[ $_ikey ][] = array(
					'icon'  => sanitize_text_field( wp_unslash( isset( $_row['icon'] ) ? $_row['icon'] : '' ) ),
					'label' => $_lbl,
					'url'   => esc_url_raw( wp_unslash( isset( $_row['url'] ) ? $_row['url'] : '' ) ),
				);
			}
		}

		// Sidebar help CTA.
		$pg['sidebar_help_title']     = sanitize_text_field( wp_unslash( isset( $_POST['sidebar_help_title'] ) ? $_POST['sidebar_help_title'] : '' ) );
		$pg['sidebar_help_text']      = sanitize_textarea_field( wp_unslash( isset( $_POST['sidebar_help_text'] ) ? $_POST['sidebar_help_text'] : '' ) );
		$pg['sidebar_help_btn_label'] = sanitize_text_field( wp_unslash( isset( $_POST['sidebar_help_btn_label'] ) ? $_POST['sidebar_help_btn_label'] : '' ) );
		$pg['sidebar_help_btn_url']   = esc_url_raw( wp_unslash( isset( $_POST['sidebar_help_btn_url'] ) ? $_POST['sidebar_help_btn_url'] : '' ) );

		// Find CTA.
		$pg['find_cta_title']     = sanitize_text_field( wp_unslash( isset( $_POST['find_cta_title'] ) ? $_POST['find_cta_title'] : '' ) );
		$pg['find_cta_desc']      = sanitize_textarea_field( wp_unslash( isset( $_POST['find_cta_desc'] ) ? $_POST['find_cta_desc'] : '' ) );
		$pg['find_cta_btn_label'] = sanitize_text_field( wp_unslash( isset( $_POST['find_cta_btn_label'] ) ? $_POST['find_cta_btn_label'] : '' ) );
		$pg['find_cta_btn_url']   = esc_url_raw( wp_unslash( isset( $_POST['find_cta_btn_url'] ) ? $_POST['find_cta_btn_url'] : '' ) );

		update_option( 'adn_calculators_page', $pg );
		self::redirect_back( 'calculators', 'page', __( 'Page settings saved.', ADN_TEXT_DOMAIN ) );
	}

	// ── Category Pages: AJAX post search ───────────────────────────────────────────

	public static function handle_cat_post_search() {
		check_ajax_referer( 'adn_cat_search', 'nonce' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Unauthorised', 403 );
		}

		$q    = sanitize_text_field( wp_unslash( isset( $_GET['q'] )    ? $_GET['q']    : '' ) );
		$slug = sanitize_key(        wp_unslash( isset( $_GET['slug'] ) ? $_GET['slug'] : '' ) );

		if ( mb_strlen( $q ) < 2 ) {
			wp_send_json_success( array() );
		}

		$results  = array();
		$ids_seen = array();

		// 1. Articles within this parent term (CMS plugin).
		if ( $slug && function_exists( 'adn_cms_articles_for_parent' ) ) {
			$cms_posts = adn_cms_articles_for_parent( $slug, 60 );
			foreach ( (array) $cms_posts as $p ) {
				$title = isset( $p->title ) ? (string) $p->title : '';
				if ( '' === $title || false === stripos( $title, $q ) ) {
					continue;
				}
				$pid       = (int) $p->ID;
				$results[] = array( 'id' => $pid, 'title' => $title, 'url' => get_permalink( $pid ) );
				$ids_seen[] = $pid;
				if ( count( $results ) >= 8 ) {
					break;
				}
			}
		}

		// 2. General WP post search to fill up to 10.
		if ( count( $results ) < 10 ) {
			$query_args = array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				's'              => $q,
				'posts_per_page' => 10 - count( $results ),
				'no_found_rows'  => true,
			);
			if ( ! empty( $ids_seen ) ) {
				$query_args['post__not_in'] = array_values( $ids_seen );
			}
			$wp_q = new WP_Query( $query_args );
			foreach ( (array) $wp_q->posts as $p ) {
				$results[] = array( 'id' => $p->ID, 'title' => $p->post_title, 'url' => get_permalink( $p ) );
			}
			wp_reset_postdata();
		}

		wp_send_json_success( array_slice( $results, 0, 10 ) );
	}

	// ── Category Pages: AJAX taxonomy term search (Featured Topics) ──────────────

	public static function handle_cat_taxonomy_search() {
		check_ajax_referer( 'adn_cat_search', 'nonce' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Unauthorised', 403 );
		}

		$q    = sanitize_text_field( wp_unslash( isset( $_GET['q'] )    ? $_GET['q']    : '' ) );
		$slug = sanitize_key(        wp_unslash( isset( $_GET['slug'] ) ? $_GET['slug'] : '' ) );

		if ( '' === $q || '' === $slug ) {
			wp_send_json_success( array() );
		}

		// Get the parent term to find child topics.
		$parent = function_exists( 'adn_cms_parent_by_slug' ) ? adn_cms_parent_by_slug( $slug ) : null;
		if ( ! $parent ) {
			wp_send_json_success( array() );
		}

		$topics  = function_exists( 'adn_cms_topics' ) ? adn_cms_topics( (int) $parent->id, 100 ) : array();
		$results = array();
		foreach ( (array) $topics as $topic ) {
			$name = isset( $topic->name ) ? (string) $topic->name : '';
			if ( '' === $name || false === stripos( $name, $q ) ) {
				continue;
			}
			$term_slug = isset( $topic->slug ) ? (string) $topic->slug : '';
			$results[] = array(
				'id'    => (int) $topic->id,
				'title' => $name,
				'url'   => '/' . $slug . '/?topic=' . rawurlencode( $term_slug ),
				'icon'  => isset( $topic->icon_emoji ) ? (string) $topic->icon_emoji : '',
			);
			if ( count( $results ) >= 10 ) {
				break;
			}
		}

		wp_send_json_success( $results );
	}

	// ── Category Pages: AJAX FAQ search ─────────────────────────────────────────────

	public static function handle_cat_faq_search() {
		check_ajax_referer( 'adn_cat_search', 'nonce' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Unauthorised', 403 );
		}

		$q = sanitize_text_field( wp_unslash( isset( $_GET['q'] ) ? $_GET['q'] : '' ) );
		if ( mb_strlen( $q ) < 2 ) {
			wp_send_json_success( array() );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ah_faqs';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			wp_send_json_success( array() );
		}

		$like    = '%' . $wpdb->esc_like( $q ) . '%';
		$rows    = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, question FROM `{$table}` WHERE status = 'active' AND question LIKE %s ORDER BY sort_order ASC, id ASC LIMIT 10",
			$like
		) );

		$results = array();
		foreach ( (array) $rows as $row ) {
			$results[] = array(
				'id'    => (int)    $row->id,
				'title' => (string) $row->question,
			);
		}
		wp_send_json_success( $results );
	}

	// ── Category Pages: dynamic subtabs, one per active parent term ────────────────

	/**
	 * Build the subtabs array for the "Category Pages" tab.
	 * Returns one subtab per active parent term from the CMS plugin; falls back
	 * to a single "no terms" placeholder when the plugin is absent or empty.
	 */
	private static function category_subtabs() {
		$subtabs = array();
		if ( function_exists( 'adn_cms_available' ) && adn_cms_available()
			&& function_exists( 'adn_cms_guide_parents' ) ) {
			foreach ( adn_cms_guide_parents( 20 ) as $term ) {
				$slug = isset( $term->slug ) ? sanitize_key( $term->slug ) : '';
				$name = isset( $term->name ) ? (string) $term->name        : ucwords( str_replace( '-', ' ', $slug ) );
				if ( '' === $slug ) {
					continue;
				}
				$subtabs[ $slug ] = array(
					'label' => $name,
					'view'  => 'category/sub-term.php',
				);
			}
		}
		if ( empty( $subtabs ) ) {
			$subtabs['_none'] = array(
				'label' => __( 'No Terms', ADN_TEXT_DOMAIN ),
				'view'  => 'category/sub-no-terms.php',
			);
		}
		return $subtabs;
	}

	// ── Home: Regulations & Hot Topics save handler ─────────────────────────────

	public static function handle_save_home_newsblocks() {
		check_admin_referer( 'adn_save_home_newsblocks' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Permission denied.', ADN_TEXT_DOMAIN ) );
		}

		$data = array();

		// Regulations items: post_id (int) + badge (text).
		$reg_raw = ( isset( $_POST['regulations']['items'] ) && is_array( $_POST['regulations']['items'] ) )
		           ? $_POST['regulations']['items'] : array();
		$reg_items = array();
		foreach ( array_values( $reg_raw ) as $row ) {
			$pid = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
			if ( ! $pid ) {
				continue;
			}
			$reg_items[] = array(
				'post_id' => $pid,
				'badge'   => sanitize_textarea_field( wp_unslash( isset( $row['badge'] ) ? $row['badge'] : 'GOV UK' ) ),
			);
			if ( count( $reg_items ) >= 5 ) {
				break;
			}
		}
		$data['regulations']['items'] = $reg_items;

		// Hot Topics items: post_id (int) + icon (text).
		$ht_raw = ( isset( $_POST['hot_topics']['items'] ) && is_array( $_POST['hot_topics']['items'] ) )
		          ? $_POST['hot_topics']['items'] : array();
		$ht_items = array();
		foreach ( array_values( $ht_raw ) as $row ) {
			$pid = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
			if ( ! $pid ) {
				continue;
			}
			$ht_items[] = array(
				'post_id' => $pid,
				'icon'    => sanitize_text_field( wp_unslash( isset( $row['icon'] ) ? $row['icon'] : '🔥' ) ),
			);
			if ( count( $ht_items ) >= 5 ) {
				break;
			}
		}
		$data['hot_topics']['items'] = $ht_items;

		update_option( 'adn_home_newsblocks', $data );

		wp_safe_redirect( add_query_arg(
			array( 'page' => self::tab_page_slug( 'home' ), 'subtab' => 'newsblocks', 'adn_saved' => 'regulations' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	// ── Home: Journey Card Images save handler ────────────────────────────────

	public static function handle_save_home_journey() {
		check_admin_referer( 'adn_save_home_journey' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Permission denied.', ADN_TEXT_DOMAIN ) );
		}

		/* taxonomy-term overrides (keyed by term ID) */
		$raw_term  = ( isset( $_POST['journey_images'] ) && is_array( $_POST['journey_images'] ) ) ? $_POST['journey_images'] : array();
		$clean_term = array();
		foreach ( $raw_term as $tid => $aid ) {
			$tid = absint( $tid );
			$aid = absint( $aid );
			if ( $tid > 0 && $aid > 0 ) {
				$clean_term[ $tid ] = $aid;
			}
		}
		update_option( 'adn_journey_card_images', $clean_term );

		/* JSON-card overrides (keyed by URL slug) */
		$raw_json  = ( isset( $_POST['journey_json_images'] ) && is_array( $_POST['journey_json_images'] ) ) ? $_POST['journey_json_images'] : array();
		$clean_json = array();
		foreach ( $raw_json as $slug => $aid ) {
			$slug = sanitize_key( (string) $slug );
			$aid  = absint( $aid );
			if ( '' !== $slug && $aid > 0 ) {
				$clean_json[ $slug ] = $aid;
			}
		}
		update_option( 'adn_journey_json_images', $clean_json );

		wp_safe_redirect( add_query_arg(
			array( 'page' => self::tab_page_slug( 'home' ), 'subtab' => 'journey', 'adn_saved' => 'journey' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	// ── Home: Resources save handler ─────────────────────────────────────────

	public static function handle_save_home_resources() {
		check_admin_referer( 'adn_save_home_resources' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Permission denied.', ADN_TEXT_DOMAIN ) );
		}

		$raw_ids     = ( isset( $_POST['resource_ids'] ) && is_array( $_POST['resource_ids'] ) ) ? $_POST['resource_ids'] : array();
		$library_ids = array();
		foreach ( $raw_ids as $rid ) {
			$rid = absint( $rid );
			if ( $rid > 0 ) { $library_ids[] = $rid; }
		}

		update_option( 'adn_home_resources', array(
			'library_ids' => $library_ids,
			'heading'     => sanitize_text_field( wp_unslash( isset( $_POST['heading'] ) ? $_POST['heading'] : '' ) ),
		) );

		wp_safe_redirect( add_query_arg(
			array( 'page' => self::tab_page_slug( 'home' ), 'subtab' => 'resources', 'adn_saved' => 'resources' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	// ── Category Pages: save handler ────────────────────────────────────────────

	public static function handle_save_category_term() {
		$slug = isset( $_POST['term_slug'] ) ? sanitize_key( wp_unslash( $_POST['term_slug'] ) ) : '';
		if ( '' === $slug ) {
			wp_die( esc_html__( 'Invalid term slug.', ADN_TEXT_DOMAIN ) );
		}
		check_admin_referer( 'adn_save_category_term_' . $slug );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		// ── Appearance ───────────────────────────────────────────────────────────
		$raw_app      = ( isset( $_POST['appearance'] ) && is_array( $_POST['appearance'] ) ) ? wp_unslash( $_POST['appearance'] ) : array();
		$appearance   = array(
			'thumbnail_id' => (int) ( isset( $raw_app['thumbnail_id'] ) ? $raw_app['thumbnail_id'] : 0 ),
		);
		AH_Category_Settings::save( $slug, 'appearance', $appearance );

		// ── Journey ──────────────────────────────────────────────────────────────
		$raw_j = ( isset( $_POST['journey'] ) && is_array( $_POST['journey'] ) ) ? wp_unslash( $_POST['journey'] ) : array();
		$steps = array();
		if ( isset( $raw_j['steps'] ) && is_array( $raw_j['steps'] ) ) {
			foreach ( $raw_j['steps'] as $s ) {
				if ( ! is_array( $s ) || empty( $s['label'] ) ) { continue; }
				$steps[] = array(
					'icon'  => sanitize_text_field( isset( $s['icon'] )  ? $s['icon']  : '' ),
					'label' => sanitize_text_field( $s['label'] ),
					'desc'  => sanitize_text_field( isset( $s['desc'] )  ? $s['desc']  : '' ),
				);
			}
		}
		$journey = array(
			'heading'        => sanitize_text_field( isset( $raw_j['heading'] )        ? $raw_j['heading']        : '' ),
			'steps'          => $steps,
			'tip_icon'       => sanitize_text_field( isset( $raw_j['tip_icon'] )       ? $raw_j['tip_icon']       : '' ),
			'tip_text'       => sanitize_text_field( isset( $raw_j['tip_text'] )       ? $raw_j['tip_text']       : '' ),
			'tip_link_label' => sanitize_text_field( isset( $raw_j['tip_link_label'] ) ? $raw_j['tip_link_label'] : '' ),
			'tip_link_url'   => esc_url_raw(         isset( $raw_j['tip_link_url'] )   ? $raw_j['tip_link_url']   : '' ),
		);
		AH_Category_Settings::save( $slug, 'journey', $journey );

		// ── Hot Topics ───────────────────────────────────────────────────────────
		$raw_ht   = ( isset( $_POST['hot_topics'] ) && is_array( $_POST['hot_topics'] ) ) ? wp_unslash( $_POST['hot_topics'] ) : array();
		$ht_items = array();
		if ( isset( $raw_ht['items'] ) && is_array( $raw_ht['items'] ) ) {
			foreach ( $raw_ht['items'] as $t ) {
				if ( ! is_array( $t ) || empty( $t['label'] ) ) { continue; }
				$ht_items[] = array(
					'icon'  => sanitize_text_field( isset( $t['icon'] )  ? $t['icon']  : '' ),
					'label' => sanitize_text_field( $t['label'] ),
					'url'   => esc_url_raw(          isset( $t['url'] )   ? $t['url']   : '' ),
				);
			}
		}
		$hot_topics = array(
			'heading'        => sanitize_text_field( isset( $raw_ht['heading'] )        ? $raw_ht['heading']        : '' ),
			'items'          => $ht_items,
			'view_all_label' => sanitize_text_field( isset( $raw_ht['view_all_label'] ) ? $raw_ht['view_all_label'] : '' ),
			'view_all_url'   => esc_url_raw(          isset( $raw_ht['view_all_url'] )   ? $raw_ht['view_all_url']   : '' ),
		);
		AH_Category_Settings::save( $slug, 'hot_topics', $hot_topics );

		// ── Popular Posts ────────────────────────────────────────────────────────
		$raw_pp  = ( isset( $_POST['popular_posts'] ) && is_array( $_POST['popular_posts'] ) ) ? wp_unslash( $_POST['popular_posts'] ) : array();
		$pp_items = array();
		if ( isset( $raw_pp['items'] ) && is_array( $raw_pp['items'] ) ) {
			foreach ( $raw_pp['items'] as $p ) {
				if ( ! is_array( $p ) || empty( $p['post_id'] ) ) { continue; }
				$pid = (int) $p['post_id'];
				if ( $pid <= 0 ) { continue; }
				$pp_items[] = array( 'post_id' => $pid );
				if ( count( $pp_items ) >= 12 ) { break; }
			}
		}
		$popular_posts = array(
			'heading' => sanitize_text_field( isset( $raw_pp['heading'] ) ? $raw_pp['heading'] : '' ),
			'items'   => $pp_items,
		);
		AH_Category_Settings::save( $slug, 'popular_posts', $popular_posts );

		// ── Calculators (selected from registered list) ───────────────────────────
		$raw_calc = ( isset( $_POST['calc'] ) && is_array( $_POST['calc'] ) ) ? wp_unslash( $_POST['calc'] ) : array();
		$sel_keys = array();
		if ( isset( $raw_calc['selected_keys'] ) && is_array( $raw_calc['selected_keys'] )
			&& function_exists( 'adn_calculators' ) ) {
			$registered = array_keys( adn_calculators() );
			foreach ( $raw_calc['selected_keys'] as $k ) {
				$k = sanitize_key( $k );
				if ( in_array( $k, $registered, true ) ) {
					$sel_keys[] = $k;
				}
			}
		}
		AH_Category_Settings::save( $slug, 'calculators', array(
			'heading'       => sanitize_text_field( isset( $raw_calc['heading'] ) ? $raw_calc['heading'] : '' ),
			'selected_keys' => $sel_keys,
		) );

		// ── Featured Topics ───────────────────────────────────────────────────────
		$raw_ft   = ( isset( $_POST['featured_topics'] ) && is_array( $_POST['featured_topics'] ) ) ? wp_unslash( $_POST['featured_topics'] ) : array();
		$ft_items = array();
		if ( isset( $raw_ft['items'] ) && is_array( $raw_ft['items'] ) ) {
			foreach ( $raw_ft['items'] as $t ) {
				if ( ! is_array( $t ) || empty( $t['name'] ) ) { continue; }
				$ft_items[] = array(
					'term_id' => (int) ( isset( $t['term_id'] ) ? $t['term_id'] : 0 ),
					'icon'    => sanitize_text_field( isset( $t['icon'] ) ? $t['icon'] : '' ),
					'name'    => sanitize_text_field( $t['name'] ),
					'url'     => esc_url_raw( isset( $t['url'] ) ? $t['url'] : '' ),
				);
			}
		}
		AH_Category_Settings::save( $slug, 'featured_topics', array(
			'heading' => sanitize_text_field( isset( $raw_ft['heading'] ) ? $raw_ft['heading'] : '' ),
			'items'   => $ft_items,
		) );

		// ── Sidebar ──────────────────────────────────────────────────────────────
		$raw_sb = ( isset( $_POST['sidebar'] ) && is_array( $_POST['sidebar'] ) ) ? wp_unslash( $_POST['sidebar'] ) : array();
		$tools  = array();
		if ( isset( $raw_sb['tools'] ) && is_array( $raw_sb['tools'] ) ) {
			foreach ( $raw_sb['tools'] as $t ) {
				if ( ! is_array( $t ) || empty( $t['label'] ) ) { continue; }
				$tools[] = array(
					'icon'  => sanitize_text_field( isset( $t['icon'] )  ? $t['icon']  : '' ),
					'label' => sanitize_text_field( $t['label'] ),
					'url'   => esc_url_raw(          isset( $t['url'] )   ? $t['url']   : '' ),
				);
			}
		}
		$expert_list = array();
		if ( isset( $raw_sb['experts'] ) && is_array( $raw_sb['experts'] ) ) {
			foreach ( $raw_sb['experts'] as $e ) {
				if ( ! is_array( $e ) || empty( $e['name'] ) ) { continue; }
				$expert_list[] = array(
					'icon' => sanitize_text_field( isset( $e['icon'] ) ? $e['icon'] : '' ),
					'name' => sanitize_text_field( $e['name'] ),
					'desc' => sanitize_text_field( isset( $e['desc'] ) ? $e['desc'] : '' ),
					'url'  => esc_url_raw(          isset( $e['url'] )  ? $e['url']  : '' ),
				);
			}
		}
		$sidebar = array(
			'tools'            => $tools,
			'cta_label'        => sanitize_text_field( isset( $raw_sb['cta_label'] )        ? $raw_sb['cta_label']        : '' ),
			'cta_url'          => esc_url_raw(          isset( $raw_sb['cta_url'] )          ? $raw_sb['cta_url']          : '' ),
			'expert_heading'   => sanitize_text_field( isset( $raw_sb['expert_heading'] )   ? $raw_sb['expert_heading']   : '' ),
			'expert_subtitle'  => sanitize_text_field( isset( $raw_sb['expert_subtitle'] )  ? $raw_sb['expert_subtitle']  : '' ),
			'experts'          => $expert_list,
			'expert_cta_label' => sanitize_text_field( isset( $raw_sb['expert_cta_label'] ) ? $raw_sb['expert_cta_label'] : '' ),
			'expert_cta_url'   => esc_url_raw(          isset( $raw_sb['expert_cta_url'] )   ? $raw_sb['expert_cta_url']   : '' ),
		);
		AH_Category_Settings::save( $slug, 'sidebar', $sidebar );

		// ── CTA Banner ───────────────────────────────────────────────────────────
		$raw_cta = ( isset( $_POST['cta'] ) && is_array( $_POST['cta'] ) ) ? wp_unslash( $_POST['cta'] ) : array();
		$cta     = array(
			'icon'        => sanitize_text_field(    isset( $raw_cta['icon'] )        ? $raw_cta['icon']        : '' ),
			'title'       => sanitize_text_field(    isset( $raw_cta['title'] )       ? $raw_cta['title']       : '' ),
			'description' => sanitize_textarea_field( isset( $raw_cta['description'] ) ? $raw_cta['description'] : '' ),
			'btn_label'   => sanitize_text_field(    isset( $raw_cta['btn_label'] )   ? $raw_cta['btn_label']   : '' ),
			'btn_url'     => esc_url_raw(            isset( $raw_cta['btn_url'] )     ? $raw_cta['btn_url']     : '' ),
		);
		AH_Category_Settings::save( $slug, 'cta_banner', $cta );

		// ── Marquee ───────────────────────────────────────────────────────────────
		$raw_mq = ( isset( $_POST['marquee'] ) && is_array( $_POST['marquee'] ) ) ? wp_unslash( $_POST['marquee'] ) : array();
		AH_Category_Settings::save( $slug, 'marquee', array(
			'marquee_enabled' => ! empty( $raw_mq['enabled'] ) ? 1 : 0,
			'marquee_mode'    => ( isset( $raw_mq['mode'] ) && 'icon' === $raw_mq['mode'] ) ? 'icon' : 'string',
			'marquee_items'   => sanitize_textarea_field( isset( $raw_mq['items'] ) ? (string) $raw_mq['items'] : '' ),
		) );

		// ── Resources - library IDs + section heading ────────────────────────────
		$raw_res         = ( isset( $_POST['resources'] ) && is_array( $_POST['resources'] ) ) ? wp_unslash( $_POST['resources'] ) : array();
		$res_library_ids = array();
		if ( isset( $raw_res['library_ids'] ) && is_array( $raw_res['library_ids'] ) ) {
			foreach ( $raw_res['library_ids'] as $rid ) {
				$rid = absint( $rid );
				if ( $rid > 0 ) { $res_library_ids[] = $rid; }
			}
		}

		AH_Category_Settings::save( $slug, 'resources', array(
			'library_ids' => $res_library_ids,
			'heading'     => sanitize_text_field( isset( $raw_res['heading'] ) ? $raw_res['heading'] : '' ),
		) );

		// ── FAQs (plugin FAQ items selected by ID) ───────────────────────────────────
		$raw_faq   = ( isset( $_POST['faqs'] ) && is_array( $_POST['faqs'] ) ) ? wp_unslash( $_POST['faqs'] ) : array();
		$faq_items = array();
		if ( isset( $raw_faq['items'] ) && is_array( $raw_faq['items'] ) ) {
			foreach ( $raw_faq['items'] as $f ) {
				if ( ! is_array( $f ) || empty( $f['faq_id'] ) ) { continue; }
				$fid = (int) $f['faq_id'];
				if ( $fid <= 0 ) { continue; }
				$faq_items[] = array( 'faq_id' => $fid );
				if ( count( $faq_items ) >= 10 ) { break; }
			}
		}
		AH_Category_Settings::save( $slug, 'faqs', array(
			'heading' => sanitize_text_field( isset( $raw_faq['heading'] ) ? $raw_faq['heading'] : '' ),
			'items'   => $faq_items,
		) );

		// ── Spotlights ───────────────────────────────────────────────────────────
		$raw_sp    = ( isset( $_POST['spotlights'] ) && is_array( $_POST['spotlights'] ) ) ? wp_unslash( $_POST['spotlights'] ) : array();
		$sp_terms  = array();
		if ( isset( $raw_sp['terms'] ) && is_array( $raw_sp['terms'] ) ) {
			foreach ( $raw_sp['terms'] as $_st ) {
				$_st = sanitize_key( $_st );
				if ( '' !== $_st ) { $sp_terms[] = $_st; }
			}
		}
		AH_Category_Settings::save( $slug, 'spotlights', array( 'terms' => array_values( array_unique( $sp_terms ) ) ) );

		// Quick Links.
		$raw_ql     = ( isset( $_POST['quick_links'] ) && is_array( $_POST['quick_links'] ) ) ? wp_unslash( $_POST['quick_links'] ) : array();
		$ql_heading = sanitize_text_field( $raw_ql['heading'] ?? '' );
		$ql_items   = array();
		if ( isset( $raw_ql['items'] ) && is_array( $raw_ql['items'] ) ) {
			foreach ( $raw_ql['items'] as $_qi ) {
				$_label = sanitize_text_field( $_qi['label'] ?? '' );
				if ( '' === $_label ) { continue; }
				$ql_items[] = array(
					'icon'  => sanitize_text_field( $_qi['icon']  ?? '' ),
					'label' => $_label,
					'url'   => esc_url_raw( $_qi['url'] ?? '' ),
				);
			}
		}
		AH_Category_Settings::save( $slug, 'quick_links', array( 'heading' => $ql_heading, 'items' => $ql_items ) );

		// ── Featured In strip ────────────────────────────────────────────────────
		$fi_section = sanitize_key( isset( $_POST['featured_in']['section'] ) ? wp_unslash( $_POST['featured_in']['section'] ) : '' );
		AH_Category_Settings::save( $slug, 'featured_in', array( 'section' => $fi_section ) );

		self::redirect_back( 'category-pages', $slug, __( 'Category settings saved.', ADN_TEXT_DOMAIN ) );
	}

	// ── Admin Actions: seed sample content into the CMS plugin ─────────────────

	public static function handle_seed_content() {
		check_admin_referer( 'adn_seed_content' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		require_once ADN_THEME_DIR . '/admin/mock-installer.php';
		$result = ADN_Mock_Installer::seed();

		self::redirect_back( 'admin-actions', 'sample-data', $result['message'] );
	}

	// ── Admin Actions: remove all mock/demo data ──────────────────────────────

	public static function handle_remove_mock_data() {
		check_admin_referer( 'adn_remove_mock_data' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		require_once ADN_THEME_DIR . '/admin/mock-installer.php';
		$result = ADN_Mock_Installer::remove_all();

		self::redirect_back( 'admin-actions', 'sample-data', $result['message'] );
	}

	// ── Experts / Team: save ────────────────────────────────────────────────────

	public static function handle_save_expert() {
		check_admin_referer( 'adn_save_expert' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}
		if ( ! class_exists( 'AH_Expert_DB' ) ) {
			wp_die( esc_html__( 'Expert DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$edit_slug = isset( $_POST['edit_slug'] ) ? sanitize_key( wp_unslash( $_POST['edit_slug'] ) ) : '';
		$is_edit   = '' !== $edit_slug;

		$slug = $is_edit ? $edit_slug : sanitize_key( wp_unslash( isset( $_POST['expert_slug'] ) ? $_POST['expert_slug'] : '' ) );

		if ( '' === $slug ) {
			self::redirect_back_error( 'experts', 'new', __( 'Expert slug is required.', ADN_TEXT_DOMAIN ) );
		}
		if ( ! $is_edit && null !== AH_Expert_DB::get( $slug ) ) {
			self::redirect_back_error( 'experts', 'new', sprintf(
				/* translators: %s: expert slug */
				__( 'The slug "%s" is already taken - choose a different one.', ADN_TEXT_DOMAIN ),
				$slug
			) );
		}

		// Bullets: one per line in a textarea → sanitised array.
		$bullets_raw = isset( $_POST['bullets_text'] ) ? wp_unslash( $_POST['bullets_text'] ) : '';
		$bullets     = array_values( array_filter( array_map(
			'sanitize_text_field',
			explode( "\n", $bullets_raw )
		) ) );

		// Client images: JSON from hidden field - decode, sanitise IDs and captions, re-encode.
		$ci_json = isset( $_POST['client_images_json'] ) ? wp_unslash( $_POST['client_images_json'] ) : '';
		$ci_raw  = json_decode( $ci_json, true );
		$client_images = array();
		if ( is_array( $ci_raw ) ) {
			foreach ( $ci_raw as $ci ) {
				if ( ! is_array( $ci ) ) { continue; }
				$client_images[] = array(
					'image_id' => absint( isset( $ci['image_id'] ) ? $ci['image_id'] : 0 ),
					'caption'  => sanitize_text_field( isset( $ci['caption'] ) ? $ci['caption'] : '' ),
				);
			}
		}

		// Banner stats: array of { icon, value, label } rows from repeater.
		$banner_raw = isset( $_POST['expert_banner_items'] ) && is_array( $_POST['expert_banner_items'] )
			? array_values( wp_unslash( $_POST['expert_banner_items'] ) )
			: array();
		$banner = array();
		foreach ( $banner_raw as $bi ) {
			if ( ! is_array( $bi ) ) { continue; }
			$b_icon  = sanitize_text_field( isset( $bi['icon'] )  ? $bi['icon']  : '' );
			$b_value = sanitize_text_field( isset( $bi['value'] ) ? $bi['value'] : '' );
			$b_label = sanitize_text_field( isset( $bi['label'] ) ? $bi['label'] : '' );
			if ( '' === $b_value && '' === $b_label ) { continue; }
			$banner[] = array( 'icon' => $b_icon, 'value' => $b_value, 'label' => $b_label );
		}

		AH_Expert_DB::maybe_install();

		AH_Expert_DB::save( array(
			'expert_slug'     => $slug,
			'name'            => wp_unslash( isset( $_POST['name'] )          ? $_POST['name']          : '' ),
			'title'           => wp_unslash( isset( $_POST['title'] )         ? $_POST['title']         : '' ),
			'category'        => wp_unslash( isset( $_POST['category'] )      ? $_POST['category']      : '' ),
			'status'          => wp_unslash( isset( $_POST['status'] )        ? $_POST['status']        : 'active' ),
			'sort_order'      => isset( $_POST['sort_order'] )         ? absint( $_POST['sort_order'] )         : 100,
			'is_locked'       => ! empty( $_POST['is_locked'] ) ? 1 : 0,
			'photo_id'        => isset( $_POST['photo_id'] )           ? absint( $_POST['photo_id'] )           : 0,
			'bio'             => wp_unslash( isset( $_POST['bio'] )           ? $_POST['bio']           : '' ),
			'rating'          => isset( $_POST['rating'] )             ? floatval( $_POST['rating'] )           : 0,
			'reviews_count'   => isset( $_POST['reviews_count'] )      ? absint( $_POST['reviews_count'] )      : 0,
			'location'        => wp_unslash( isset( $_POST['location'] )      ? $_POST['location']      : '' ),
			'phone'           => wp_unslash( isset( $_POST['phone'] )         ? $_POST['phone']         : '' ),
			'email'           => wp_unslash( isset( $_POST['email'] )         ? $_POST['email']         : '' ),
			'bullets'         => $bullets,
			'client_images'   => $client_images,
			'banner_image_id' => isset( $_POST['banner_image_id'] )    ? absint( $_POST['banner_image_id'] )    : 0,
			'banner'          => $banner,
			'mega_html'       => wp_unslash( isset( $_POST['mega_html'] ) ? $_POST['mega_html'] : '' ),
		) );

		$msg = $is_edit
			? __( 'Expert updated.', ADN_TEXT_DOMAIN )
			: __( 'Expert saved.', ADN_TEXT_DOMAIN );

		if ( $is_edit ) {
			// Redirect back to the same edit form so admin can see their saved data.
			wp_safe_redirect( add_query_arg(
				array( 'edit_slug' => $slug, 'adn_done' => 1, 'adn_msg' => rawurlencode( $msg ) ),
				self::tab_url( 'experts', 'new' )
			) );
			exit;
		}

		self::redirect_back( 'experts', 'list', $msg );
	}

	// ── Experts / Team: banner settings ───────────────────────────────────────

	public static function handle_save_expert_banner() {
		check_admin_referer( 'adn_save_expert_banner' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$raw_items     = isset( $_POST['marquee_items'] ) ? (array) wp_unslash( $_POST['marquee_items'] ) : array();
		$marquee_items = array();
		foreach ( $raw_items as $_mi ) {
			if ( ! is_array( $_mi ) ) { continue; }
			$_label = sanitize_text_field( isset( $_mi['label'] ) ? (string) $_mi['label'] : '' );
			if ( '' === $_label ) { continue; }
			$marquee_items[] = array(
				'icon'  => sanitize_text_field( isset( $_mi['icon'] ) ? (string) $_mi['icon'] : '' ),
				'label' => $_label,
				'note'  => sanitize_text_field( isset( $_mi['note'] ) ? (string) $_mi['note'] : '' ),
			);
		}

		$raw_vcats    = isset( $_POST['virtual_cats'] ) ? (array) wp_unslash( $_POST['virtual_cats'] ) : array();
		$virtual_cats = array();
		foreach ( $raw_vcats as $_vc ) {
			if ( ! is_array( $_vc ) ) { continue; }
			$_label = sanitize_text_field( isset( $_vc['label'] ) ? (string) $_vc['label'] : '' );
			if ( '' === $_label ) { continue; }
			$virtual_cats[] = array(
				'label'   => $_label,
				'message' => sanitize_textarea_field( isset( $_vc['message'] ) ? (string) $_vc['message'] : '' ),
			);
		}

		$banner = array(
			'heading'             => sanitize_text_field( wp_unslash( isset( $_POST['banner_heading'] ) ? $_POST['banner_heading'] : '' ) ),
			'info'                => sanitize_textarea_field( wp_unslash( isset( $_POST['banner_info'] ) ? $_POST['banner_info'] : '' ) ),
			'enabled'             => ! empty( $_POST['banner_enabled'] ) ? 1 : 0,
			'marquee_items'       => $marquee_items,
			'featured_in_section' => sanitize_key( isset( $_POST['featured_in_section'] ) ? wp_unslash( $_POST['featured_in_section'] ) : '' ),
			'unlock_password'     => sanitize_text_field( wp_unslash( isset( $_POST['unlock_password'] ) ? $_POST['unlock_password'] : '' ) ),
			'virtual_cats'        => $virtual_cats,
		);

		update_option( 'adn_expert_banner', $banner );
		self::redirect_back( 'experts', 'banner', __( 'Expert banner saved.', ADN_TEXT_DOMAIN ) );
	}

	// ── Experts / Team: delete ──────────────────────────────────────────────────

	public static function handle_delete_expert() {
		check_admin_referer( 'adn_delete_expert' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}
		if ( ! class_exists( 'AH_Expert_DB' ) ) {
			wp_die( esc_html__( 'Expert DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$slug = sanitize_key( wp_unslash( isset( $_POST['expert_slug'] ) ? $_POST['expert_slug'] : '' ) );
		if ( '' === $slug ) {
			wp_die( esc_html__( 'No slug provided.', ADN_TEXT_DOMAIN ) );
		}

		AH_Expert_DB::delete( $slug );
		self::redirect_back( 'experts', 'list', __( 'Expert deleted.', ADN_TEXT_DOMAIN ) );
	}
}

