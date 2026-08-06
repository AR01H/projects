<?php
/**
 * Admin Actions Handler - cache, pages, rules, sample data, import/export.
 */
defined( 'ABSPATH' ) || exit;

class ADN_Admin_Actions_Handler extends ADN_Base_Handler {

	/**
	 * Flush object cache, theme transients, home-fragment cache, filesystem
	 * cache, OPcache, and any known host/plugin page cache. Shared by the
	 * manual "Clear Cache" action and the cookie re-ask actions below - a
	 * version-number bump means nothing to visitors if a stale copy of
	 * get_option( 'adn_cookie_consent_*_version' ) keeps being served from
	 * object cache / OPcache / a page cache on production.
	 *
	 * @return string[] Labels of what was cleared, for the admin notice.
	 */
	private static function flush_all_caches(): array {
		$cleared = array();

		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
			$cleared[] = __( 'object cache', ADN_TEXT_DOMAIN );
		}

		global $wpdb;
		$like  = $wpdb->esc_like( '_transient_adn_' ) . '%';
		$names = $wpdb->get_col( $wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like
		) );
		foreach ( (array) $names as $option_name ) {
			delete_transient( str_replace( '_transient_', '', $option_name ) );
		}
		if ( ! empty( $names ) ) {
			$cleared[] = sprintf( _n( '%d transient', '%d transients', count( $names ), ADN_TEXT_DOMAIN ), count( $names ) );
		}

		if ( function_exists( 'cache_clear_all' ) ) {
			cache_clear_all( null, 'home_frag', true );
			$cleared[] = __( 'home fragment cache', ADN_TEXT_DOMAIN );
		}

		if ( class_exists( 'ADN_Cache' ) ) {
			ADN_Cache::clear_all();
			$cleared[] = __( 'theme filesystem cache', ADN_TEXT_DOMAIN );
		}

		if ( function_exists( 'opcache_reset' ) ) {
			@opcache_reset();
			$cleared[] = 'OPcache';
		}

		// Known host/plugin page caches - a stale full-page cache would keep
		// serving old version numbers baked into the localized script output.
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
			$cleared[] = 'WP Rocket';
		}
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
			$cleared[] = 'LiteSpeed Cache';
		}
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
			$cleared[] = 'W3 Total Cache';
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
			$cleared[] = 'WP Super Cache';
		}
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
			$cleared[] = 'SiteGround Cache';
		}

		return $cleared;
	}

	/** Clear all caches (object, transients, fragments, filesystem, OPcache). */
	public static function handle_clear_cache(): void {
		self::verify_request( 'adn_clear_cache' );

		// Save cache enabled state.
		if ( isset( $_POST['_wp_http_referer'] ) ) {
			update_option( 'ah_cache_enabled', isset( $_POST['ah_cache_enabled'] ) ? '1' : '0' );
		}

		$cleared = self::flush_all_caches();

		$msg = ! empty( $cleared )
			? sprintf( __( 'Cache cleared: %s.', ADN_TEXT_DOMAIN ), implode( ', ', $cleared ) )
			: __( 'Nothing to clear.', ADN_TEXT_DOMAIN );

		self::redirect_success( 'admin-actions', 'cache', $msg );
	}

	/** Re-ask cookie consent for all visitors. */
	public static function handle_reask_cookie_all(): void {
		self::verify_request( 'adn_reask_cookie_all' );

		update_option( 'adn_cookie_consent_accept_version', (int) get_option( 'adn_cookie_consent_accept_version', 1 ) + 1 );
		update_option( 'adn_cookie_consent_reject_version', (int) get_option( 'adn_cookie_consent_reject_version', 1 ) + 1 );

		self::flush_all_caches();

		self::redirect_success( 'admin-actions', 'cache', __( 'Cookie consent will be re-asked for every visitor on their next page load.', ADN_TEXT_DOMAIN ) );
	}

	/** Re-ask cookie consent for rejected visitors only. */
	public static function handle_reask_cookie_rejected(): void {
		self::verify_request( 'adn_reask_cookie_rejected' );

		update_option( 'adn_cookie_consent_reject_version', (int) get_option( 'adn_cookie_consent_reject_version', 1 ) + 1 );

		self::flush_all_caches();

		self::redirect_success( 'admin-actions', 'cache', __( 'Cookie consent will be re-asked only for visitors who previously rejected.', ADN_TEXT_DOMAIN ) );
	}

	/** Sync default pages + flush permalinks. */
	public static function handle_sync_pages(): void {
		self::verify_request( 'adn_sync_pages' );

		$created = function_exists( 'adn_create_default_pages' ) ? (int) adn_create_default_pages() : 0;
		flush_rewrite_rules();

		self::redirect_success( 'admin-actions', 'pages',
			sprintf( __( 'Pages synced (%d created) and permalinks flushed.', ADN_TEXT_DOMAIN ), $created )
		);
	}

	/** Flush permalinks only. */
	public static function handle_flush_rewrites(): void {
		self::verify_request( 'adn_flush_rewrites' );
		flush_rewrite_rules();
		self::redirect_success( 'admin-actions', 'pages', __( 'Permalinks flushed.', ADN_TEXT_DOMAIN ) );
	}

	/** Install sample contact rule into the Rules Engine. */
	public static function handle_install_contact_rule(): void {
		self::verify_request( 'adn_install_contact_rule' );

		if ( ! class_exists( 'AH_Workflow_Manager' ) || ! class_exists( 'ADN_Rules' ) ) {
			self::redirect_error( 'admin-actions', 'rules', __( 'The CMS plugin is not active.', ADN_TEXT_DOMAIN ) );
		}

		$rule_name = COMPANY_NAME . ' - Contact Form Email (sample)';
		foreach ( AH_Workflow_Manager::get_all() as $rule ) {
			if ( $rule_name === $rule->name ) {
				self::redirect_success( 'admin-actions', 'rules', __( 'Sample contact rule already exists.', ADN_TEXT_DOMAIN ) );
			}
		}

		AH_Workflow_Manager::install_tables();
		$rule_id = AH_Workflow_Manager::save( 0, array(
			'name'             => $rule_name,
			'trigger_name'     => ADN_Rules::CONTACT_FORM,
			'conditions_match' => 'all',
			'conditions'       => array(),
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
						. '<p><strong>Message:</strong><br>{message}</p>',
				),
			),
		) );

		$msg = $rule_id
			? __( 'Sample contact rule installed.', ADN_TEXT_DOMAIN )
			: __( 'Could not create the rule.', ADN_TEXT_DOMAIN );
		self::redirect_success( 'admin-actions', 'rules', $msg );
	}

	/** Export theme settings as JSON. */
	public static function handle_export_settings(): void {
		self::verify_request( 'adn_export_settings' );

		$settings = array();
		foreach ( adn_settings_schemas() as $schema ) {
			$settings[ $schema['option'] ] = get_option( $schema['option'], null );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="advaith-settings-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo wp_json_encode( array(
			'theme'       => 'advaithhomes_new',
			'exported_at' => gmdate( 'c' ),
			'settings'    => $settings,
		), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/** Import theme settings from uploaded JSON. */
	public static function handle_import_settings(): void {
		self::verify_request( 'adn_import_settings' );

		if ( empty( $_FILES['settings_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['settings_file']['tmp_name'] ) ) {
			self::redirect_error( 'import-export', '', __( 'No file uploaded.', ADN_TEXT_DOMAIN ) );
		}

		$raw    = file_get_contents( $_FILES['settings_file']['tmp_name'] );
		$parsed = json_decode( (string) $raw, true );
		if ( ! is_array( $parsed ) ) {
			self::redirect_error( 'import-export', '', __( 'That file is not valid JSON.', ADN_TEXT_DOMAIN ) );
		}

		$settings = $parsed['settings'] ?? $parsed;
		$allowed  = array();
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

		self::redirect_success( 'import-export', '',
			sprintf( __( 'Imported %d settings group(s).', ADN_TEXT_DOMAIN ), $count )
		);
	}

	/** Clear Contact Inbox */
	public static function handle_clear_contact_inbox(): void {
		self::verify_request( 'adn_clear_contact_inbox' );
		if ( class_exists( 'AH_Enquiry_Model' ) ) {
			global $wpdb;
			$wpdb->delete( AH_Enquiry_Model::table(), array( 'form_type' => 'contact' ) );
		}
		self::redirect_success( 'contact-inbox', '', 'Contact inbox cleared.' );
	}

	/** Clear Guidance Inbox */
	public static function handle_clear_guidance_inbox(): void {
		self::verify_request( 'adn_clear_guidance_inbox' );
		if ( class_exists( 'AH_Enquiry_Model' ) ) {
			global $wpdb;
			$wpdb->delete( AH_Enquiry_Model::table(), array( 'form_type' => 'guidance' ) );
		}
		self::redirect_success( 'guidance-inbox', '', 'Guidance inbox cleared.' );
	}
}
