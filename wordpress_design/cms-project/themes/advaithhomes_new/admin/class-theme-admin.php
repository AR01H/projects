<?php
defined( 'ABSPATH' ) || exit;

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

		// Admin Actions handlers (admin-post.php endpoints).
		add_action( 'admin_post_adn_clear_cache',          array( __CLASS__, 'handle_clear_cache' ) );
		add_action( 'admin_post_adn_sync_pages',           array( __CLASS__, 'handle_sync_pages' ) );
		add_action( 'admin_post_adn_flush_rewrites',       array( __CLASS__, 'handle_flush_rewrites' ) );
		add_action( 'admin_post_adn_install_contact_rule', array( __CLASS__, 'handle_install_contact_rule' ) );
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
	}

	// ── Routing helpers ─────────────────────────────────────────────────────────

	private static function active_tab() {
		$tabs = self::tabs();
		$req  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
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
		$args = array( 'page' => self::MENU_SLUG, 'tab' => $tab );
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
			<h1><?php echo esc_html( COMPANY_NAME ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $key => $def ) : ?>
					<a href="<?php echo esc_url( self::tab_url( $key ) ); ?>"
						class="nav-tab <?php echo $key === $active ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $def['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<?php if ( ! empty( $tab['subtabs'] ) ) : ?>
				<ul class="subsubsub adn-subtabs" style="margin-top:.8rem;">
					<?php
					$total = count( $tab['subtabs'] );
					$i     = 0;
					foreach ( $tab['subtabs'] as $skey => $sdef ) :
						$i++;
						?>
						<li>
							<a href="<?php echo esc_url( self::tab_url( $active, $skey ) ); ?>"
								class="<?php echo $skey === $active_sub ? 'current' : ''; ?>">
								<?php echo esc_html( $sdef['label'] ); ?>
							</a><?php echo $i < $total ? ' |' : ''; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<div style="clear:both;"></div>
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
}
