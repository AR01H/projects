<?php
/**
 * src/Consent/class-consent.php
 *
 * FEATURE: Cookie consent
 * -----------------------
 * A site-wide consent banner with granular, per-category preferences - the
 * same model as the sibling theme's cookie-consent.js, rebuilt to this
 * theme's rules: every word and every category comes from JSON, the logic
 * lives in a class, and the preferences panel reuses the shared vintage
 * dialog instead of a second bespoke modal.
 *
 * admin/data/cookies.json controls all of it:
 *
 *   enabled          bool    Master switch.
 *   cookie_name      string  Where the decision is stored.
 *   accept_version   string  Bump to re-ask EVERYONE.
 *   reject_version   string  Bump to re-ask only people who declined.
 *   accept_days      int     How long a yes (or partial yes) lasts.
 *   reject_hours     int     How soon a full no is asked again.
 *   banner           object  kicker/title/text + button labels + policy link.
 *   categories       array   [{ key, label, text, required, default }]
 *
 * "necessary" categories are never shown as a toggle and are never stored -
 * they are always on, which is what makes the stored payload small and the
 * legal position simple.
 *
 * WHY A CLASS: the banner, the preferences dialog and the JS all need the
 * same list of categories and the same version numbers. They ask this class,
 * so those three can never drift apart.
 *
 * @package NT\Consent
 */

defined( 'ABSPATH' ) || exit;

class NT_Consent {

	/**
	 * JSON config file (admin/data/<DATA_KEY>.json).
	 */
	public const DATA_KEY = 'cookies';

	/**
	 * Categories that exist in every build, whatever the JSON says. The
	 * visitor can never switch these off, so they carry no toggle.
	 */
	public const ALWAYS_ON = 'necessary';

	/**
	 * Ship the config. There is no server-side render: the banner and the
	 * preferences panel are built by assets/js/cookie-consent.js, because a
	 * consent UI that HTML alone cannot operate has no business being in the
	 * HTML - and this way a visitor who already decided never gets a flash of
	 * a banner that then disappears.
	 */
	public static function boot(): void {
		add_action( 'wp_footer', array( __CLASS__, 'localize' ), 1 );
	}

	/**
	 * The raw config, with defaults filled in.
	 *
	 * @return array
	 */
	public static function config(): array {
		static $config = null;
		if ( null !== $config ) {
			return $config;
		}
		$data = is_callable( array( 'App_Helpers', 'data' ) ) ? App_Helpers::data( self::DATA_KEY ) : array();
		$data = is_array( $data ) ? $data : array();

		$config = array(
			'enabled'        => ! empty( $data['enabled'] ),
			'cookie_name'    => (string) ( $data['cookie_name'] ?? 'app_cookie_consent' ),
			'accept_version' => (string) ( $data['accept_version'] ?? '1' ),
			'reject_version' => (string) ( $data['reject_version'] ?? '1' ),
			'accept_days'    => max( 1, (int) ( $data['accept_days'] ?? 365 ) ),
			'reject_hours'   => max( 1, (int) ( $data['reject_hours'] ?? 20 ) ),
			'position'       => in_array( (string) ( $data['position'] ?? 'bottom' ), array( 'bottom', 'corner' ), true )
				? (string) $data['position'] : 'bottom',
			'banner'         => is_array( $data['banner'] ?? null ) ? $data['banner'] : array(),
			'categories'     => self::categories( $data ),
		);
		return $config;
	}

	/**
	 * Is the banner switched on for this request?
	 */
	public static function enabled(): bool {
		$config = self::config();
		return $config['enabled'] && ! empty( $config['categories'] );
	}

	/**
	 * Normalised category list. Required categories are forced on and marked
	 * so the template renders them as a locked row rather than a switch.
	 *
	 * @param array $data Raw JSON (passed in to avoid recursing into config()).
	 * @return array<int,array>
	 */
	protected static function categories( array $data ): array {
		$out = array();
		foreach ( (array) ( $data['categories'] ?? array() ) as $row ) {
			$row = (array) $row;
			$key = sanitize_key( (string) ( $row['key'] ?? '' ) );
			if ( '' === $key ) {
				continue;
			}
			$required = ! empty( $row['required'] ) || self::ALWAYS_ON === $key;
			$out[]    = array(
				'key'      => $key,
				'label'    => (string) ( $row['label'] ?? ucfirst( $key ) ),
				'text'     => (string) ( $row['text'] ?? '' ),
				'required' => $required,
				'default'  => $required ? true : ! empty( $row['default'] ),
			);
		}
		return $out;
	}

	/**
	 * Only the categories the visitor can actually change - the ones that get
	 * a toggle and get written into the stored payload.
	 *
	 * @return array<int,array>
	 */
	public static function optional_categories(): array {
		return array_values( array_filter(
			self::config()['categories'],
			static function ( $category ) {
				return empty( $category['required'] );
			}
		) );
	}

	/**
	 * One banner string, e.g. NT_Consent::text( 'accept_all' ).
	 */
	public static function text( string $key, string $default = '' ): string {
		$banner = self::config()['banner'];
		$value  = isset( $banner[ $key ] ) ? trim( (string) $banner[ $key ] ) : '';
		return '' !== $value ? $value : $default;
	}

	/**
	 * THE payload handed to assets/js/cookie-consent.js as `window.ntConsent`.
	 * Every category, every word and every timing the banner needs - the
	 * script itself holds none of them.
	 *
	 * @return array
	 */
	public static function js_config(): array {
		$config = self::config();

		// The full list, required rows included, because the preferences
		// panel shows them as locked "always on" rows.
		$categories = array();
		foreach ( $config['categories'] as $category ) {
			$categories[] = array(
				'key'      => $category['key'],
				'label'    => $category['label'],
				'text'     => $category['text'],
				'required' => (bool) $category['required'],
				'default'  => (bool) $category['default'],
			);
		}

		return array(
			'enabled'       => $config['enabled'],
			'cookieName'    => $config['cookie_name'],
			'acceptVersion' => $config['accept_version'],
			'rejectVersion' => $config['reject_version'],
			'acceptDays'    => $config['accept_days'],
			'rejectHours'   => $config['reject_hours'],
			'position'      => $config['position'],
			'categories'    => $categories,
			'policyUrl'     => '' !== self::text( 'policy_url' ) ? App_Helpers::link( self::text( 'policy_url' ) ) : '',
			'text'          => array(
				'aria'         => self::text( 'aria', 'Cookie notice' ),
				'kicker'       => self::text( 'kicker' ),
				'title'        => self::text( 'title' ),
				'body'         => self::text( 'text' ),
				'policyLabel'  => self::text( 'policy_label' ),
				'acceptAll'    => self::text( 'accept_all' ),
				'rejectAll'    => self::text( 'reject_all' ),
				'manage'       => self::text( 'manage' ),
				'savePrefs'    => self::text( 'save_prefs' ),
				'alwaysOn'     => self::text( 'always_on' ),
				'saved'        => self::text( 'saved_toast' ),
				'prefsKicker'  => self::text( 'prefs_kicker' ),
				'prefsTitle'   => self::text( 'prefs_title' ),
				'prefsText'    => self::text( 'prefs_text' ),
				'close'        => NT_Ui::label( 'close' ),
			),
			'icons'         => array(
				'shield' => NT_Icons::get( 'shield' ),
				'lock'   => NT_Icons::get( 'lock' ),
			),
		);
	}

	/**
	 * Hand the config to the script.
	 */
	public static function localize(): void {
		if ( ! self::enabled() || ! wp_script_is( 'app-cookie-consent', 'registered' ) ) {
			return;
		}
		wp_add_inline_script(
			'app-cookie-consent',
			'window.ntConsent=' . wp_json_encode( self::js_config() ) . ';',
			'before'
		);
	}
}

NT_Consent::boot();
