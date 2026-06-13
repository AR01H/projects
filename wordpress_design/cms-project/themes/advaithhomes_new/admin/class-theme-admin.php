<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/settings-schemas.php';
require_once __DIR__ . '/class-theme-settings.php';

/**
 * ADN_Theme_Admin
 *
 * One top-level admin page ("Advaith Homes") rendered as a data-driven
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

		// Reusable settings engine (shared admin-post save handler for all tabs).
		ADN_Theme_Settings::init();

		// Import / Export (theme settings as JSON).
		add_action( 'admin_post_adn_export_settings', array( __CLASS__, 'handle_export_settings' ) );
		add_action( 'admin_post_adn_import_settings', array( __CLASS__, 'handle_import_settings' ) );

		// Manage Calculator → Calculator List (per-calculator controls).
		add_action( 'admin_post_adn_save_calc_list', array( __CLASS__, 'handle_save_calc_list' ) );

		// Admin Actions → Sample Data (seed Guide terms / articles / news).
		add_action( 'admin_post_adn_seed_content', array( __CLASS__, 'handle_seed_content' ) );

		// Admin Actions handlers (admin-post.php endpoints).
		add_action( 'admin_post_adn_clear_cache',          array( __CLASS__, 'handle_clear_cache' ) );
		add_action( 'admin_post_adn_sync_pages',           array( __CLASS__, 'handle_sync_pages' ) );
		add_action( 'admin_post_adn_flush_rewrites',       array( __CLASS__, 'handle_flush_rewrites' ) );
		add_action( 'admin_post_adn_install_contact_rule', array( __CLASS__, 'handle_install_contact_rule' ) );

		// Category Pages: per-term journey / calculators / sidebar / CTA settings.
		add_action( 'admin_post_adn_save_category_term', array( __CLASS__, 'handle_save_category_term' ) );

		// Category Pages: AJAX post search (Hot Topics + Popular Posts).
		add_action( 'wp_ajax_adn_cat_post_search', array( __CLASS__, 'handle_cat_post_search' ) );

		// Category Pages: AJAX taxonomy term search (Featured Topics).
		add_action( 'wp_ajax_adn_cat_tax_search', array( __CLASS__, 'handle_cat_taxonomy_search' ) );

		// Enqueue wp.media on our admin page (required for the thumbnail uploader).
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	public static function enqueue_admin_assets( $hook ) {
		if ( false === strpos( $hook, self::MENU_SLUG ) ) {
			return;
		}
		wp_enqueue_media();
	}

	/**
	 * Tab + subtab registry - the single source of truth for the whole page.
	 *
	 * Each tab has a 'label' and EITHER a 'view' (no subtabs) OR a 'subtabs'
	 * array (each subtab has its own 'label' + 'view'). View paths are
	 * relative to /admin/tabs/.
	 */
	public static function tabs() {
		return array(
			'dashboard' => array(
				'label' => __( 'Dashboard', ADN_TEXT_DOMAIN ),
				'view'  => 'tab-dashboard.php',
			),
			'home' => array(
				'label'   => __( 'Home Page', ADN_TEXT_DOMAIN ),
				'subtabs' => array(
					'sections' => array(
						'label' => __( 'Sections', ADN_TEXT_DOMAIN ),
						'view'  => 'home/sub-sections.php',
					),
					'hero' => array(
						'label' => __( 'Hero & Intro', ADN_TEXT_DOMAIN ),
						'view'  => 'home/sub-hero.php',
					),
					'featured' => array(
						'label' => __( 'Featured Guides', ADN_TEXT_DOMAIN ),
						'view'  => 'home/sub-featured.php',
					),
				),
			),
			'calculators' => array(
				'label'   => __( 'Manage Calculator', ADN_TEXT_DOMAIN ),
				'subtabs' => array(
					'general' => array(
						'label' => __( 'Heading & Banner', ADN_TEXT_DOMAIN ),
						'view'  => 'calculators/sub-general.php',
					),
					'list' => array(
						'label' => __( 'Calculator List', ADN_TEXT_DOMAIN ),
						'view'  => 'calculators/sub-list.php',
					),
				),
			),
			'import-export' => array(
				'label' => __( 'Import / Export', ADN_TEXT_DOMAIN ),
				'view'  => 'tab-import-export.php',
			),
			'category-pages' => array(
				'label'   => __( 'Category Pages', ADN_TEXT_DOMAIN ),
				'subtabs' => self::category_subtabs(),
			),
			'admin-actions' => array(
				'label'   => __( 'Admin Actions', ADN_TEXT_DOMAIN ),
				'subtabs' => array(
					'cache' => array(
						'label' => __( 'Cache', ADN_TEXT_DOMAIN ),
						'view'  => 'admin-actions/sub-cache.php',
					),
					'pages' => array(
						'label' => __( 'Pages & Permalinks', ADN_TEXT_DOMAIN ),
						'view'  => 'admin-actions/sub-pages.php',
					),
					'rules' => array(
						'label' => __( 'Rules Engine', ADN_TEXT_DOMAIN ),
						'view'  => 'admin-actions/sub-rules.php',
					),
					'sample-data' => array(
						'label' => __( 'Sample Data', ADN_TEXT_DOMAIN ),
						'view'  => 'admin-actions/sub-sample-data.php',
					),
				),
			),
		);
	}

	public static function register_menu() {
		add_menu_page(
			COMPANY_NAME,
			COMPANY_NAME,
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
			<h1><?php echo esc_html( $tab['label'] ); ?></h1>

			<?php /* Sidebar submenus are the top level; a section's own subtabs
			         render here as its in-page tabs (no duplicate of the sidebar). */ ?>
			<?php if ( ! empty( $tab['subtabs'] ) ) : ?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $tab['subtabs'] as $skey => $sdef ) : ?>
						<a href="<?php echo esc_url( self::tab_url( $active, $skey ) ); ?>"
							class="nav-tab <?php echo $skey === $active_sub ? 'nav-tab-active' : ''; ?>">
							<?php echo esc_html( $sdef['label'] ); ?>
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
		if ( empty( $_GET['adn_done'] ) ) {
			return;
		}
		$msg = isset( $_GET['adn_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['adn_msg'] ) ) : __( 'Done.', ADN_TEXT_DOMAIN );
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( $msg )
		);
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
		if ( ! class_exists( 'AH_Rules_Engine' ) ) {
			return -1;
		}
		$count = 0;
		foreach ( AH_Rules_Engine::get_all() as $rule ) {
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

		if ( ! class_exists( 'AH_Rules_Engine' ) || ! class_exists( 'ADN_Rules' ) ) {
			self::redirect_back( 'admin-actions', 'rules', __( 'The CMS plugin (AH_Rules_Engine) is not active - activate it first.', ADN_TEXT_DOMAIN ) );
		}

		// Already installed? Don't create a duplicate.
		foreach ( AH_Rules_Engine::get_all() as $rule ) {
			if ( self::sample_rule_name() === $rule->name ) {
				self::redirect_back( 'admin-actions', 'rules', __( 'Sample contact rule already exists - edit it in the CMS plugin.', ADN_TEXT_DOMAIN ) );
			}
		}

		AH_Rules_Engine::install_tables();

		$rule_id = AH_Rules_Engine::save( 0, array(
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

		// Iterate the registered calculators (the source of truth), not POST keys.
		foreach ( adn_calculators() as $key => $calc ) {
			$row          = isset( $input[ $key ] ) && is_array( $input[ $key ] ) ? $input[ $key ] : array();
			$meta[ $key ] = array(
				'enabled'     => empty( $row['enabled'] ) ? 0 : 1,
				'label'       => sanitize_text_field( $row['label'] ?? '' ),
				'help'        => sanitize_textarea_field( $row['help'] ?? '' ),
				'guide_label' => sanitize_text_field( $row['guide_label'] ?? '' ),
				'guide_url'   => esc_url_raw( $row['guide_url'] ?? '' ),
			);
		}

		update_option( 'adn_calculators_meta', $meta );
		self::redirect_back( 'calculators', 'list', __( 'Calculator list saved.', ADN_TEXT_DOMAIN ) );
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

		// ── External Links ────────────────────────────────────────────────────────
		$raw_el   = ( isset( $_POST['external_links'] ) && is_array( $_POST['external_links'] ) ) ? wp_unslash( $_POST['external_links'] ) : array();
		$el_items = array();
		if ( isset( $raw_el['items'] ) && is_array( $raw_el['items'] ) ) {
			foreach ( $raw_el['items'] as $l ) {
				if ( ! is_array( $l ) || empty( $l['title'] ) ) { continue; }
				$el_items[] = array(
					'icon'  => sanitize_text_field( isset( $l['icon'] )  ? $l['icon']  : '' ),
					'title' => sanitize_text_field( $l['title'] ),
					'url'   => esc_url_raw(          isset( $l['url'] )   ? $l['url']   : '' ),
					'desc'  => sanitize_text_field( isset( $l['desc'] )  ? $l['desc']  : '' ),
				);
			}
		}
		AH_Category_Settings::save( $slug, 'external_links', array(
			'heading' => sanitize_text_field( isset( $raw_el['heading'] ) ? $raw_el['heading'] : '' ),
			'items'   => $el_items,
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

		self::redirect_back( 'category-pages', $slug, __( 'Category settings saved.', ADN_TEXT_DOMAIN ) );
	}

	// ── Admin Actions: seed sample Guide content into the CMS plugin ─────────────

	public static function handle_seed_content() {
		check_admin_referer( 'adn_seed_content' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		require_once ADN_THEME_DIR . '/admin/mock-installer.php';
		$result = ADN_Mock_Installer::seed();

		self::redirect_back( 'admin-actions', 'sample-data', $result['message'] );
	}
}
