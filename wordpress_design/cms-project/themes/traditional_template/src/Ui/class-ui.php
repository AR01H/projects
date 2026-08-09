<?php
/**
 * src/Ui/class-ui.php
 *
 * FEATURE: Shared UI vocabulary
 * -----------------------------
 * The single source of truth for the things dialogs, alerts, toasts and the
 * notice bar all need to agree on:
 *
 *   - TONES      info | success | warning | error | note | question
 *                (each maps to an icon name + a CSS modifier)
 *   - LABELS     every button/aria string, read from admin/data/ui.json so no
 *                user-facing word is hard-coded in PHP, JS or CSS
 *   - SIZES      sm | md | lg | full  (dialog widths)
 *
 * Nothing here renders markup - it just answers "what is a valid tone?" and
 * "what does this site call the Cancel button?". NT_Dialog and NT_Alert are
 * the renderers; they both ask this class.
 *
 * Add a tone: one entry in self::TONES + one CSS block in ui-kit.css.
 *
 * @package NT\Ui
 */

defined( 'ABSPATH' ) || exit;

class NT_Ui {

	/**
	 * JSON file holding every shared UI string (admin/data/<DATA_KEY>.json).
	 */
	public const DATA_KEY = 'ui';

	/**
	 * tone => icon name in NT_Icons. The array KEY is also the CSS modifier,
	 * i.e. tone "warning" renders `.app-alert--warning` / `.app-dialog--warning`.
	 */
	public const TONES = array(
		'info'     => 'info',
		'success'  => 'success',
		'warning'  => 'warning',
		'error'    => 'error',
		'note'     => 'note',
		'question' => 'question',
	);

	/**
	 * Allowed dialog widths. Anything else falls back to DEFAULT_SIZE.
	 */
	public const SIZES = array( 'sm', 'md', 'lg', 'full' );

	public const DEFAULT_TONE = 'note';
	public const DEFAULT_SIZE = 'md';

	/**
	 * Fallback strings, used ONLY when admin/data/ui.json has no value for the
	 * key. They exist so a fresh copy of the theme never renders a blank
	 * button - the JSON is still the place to edit wording.
	 *
	 * @var array<string,string>
	 */
	protected const LABEL_FALLBACKS = array(
		'ok'            => 'OK',
		'cancel'        => 'Cancel',
		'confirm'       => 'Confirm',
		'close'         => 'Close',
		'dismiss'       => 'Dismiss',
		'submit'        => 'Send',
		'read_more'     => 'Read more',
		'loading'       => 'One moment…',
		'error_generic' => 'Something went wrong. Please try again.',
		'copied'        => 'Copied',
		'required'      => 'Please complete this field.',
	);

	/**
	 * Normalise any tone value coming from JSON.
	 */
	public static function tone( $value, string $default = self::DEFAULT_TONE ): string {
		$value = strtolower( trim( (string) $value ) );
		return isset( self::TONES[ $value ] ) ? $value : $default;
	}

	/**
	 * The icon name a tone uses. Callers may override with their own `icon`.
	 */
	public static function tone_icon( string $tone ): string {
		$tone = self::tone( $tone );
		return self::TONES[ $tone ];
	}

	/**
	 * Normalise a dialog size value coming from JSON.
	 */
	public static function size( $value, string $default = self::DEFAULT_SIZE ): string {
		$value = strtolower( trim( (string) $value ) );
		return in_array( $value, self::SIZES, true ) ? $value : $default;
	}

	/**
	 * A shared UI string from admin/data/ui.json -> "labels".
	 *
	 *   NT_Ui::label( 'cancel' )   // "Cancel", or whatever the JSON says
	 *
	 * @param string $key     Key under "labels".
	 * @param string $default Used when neither the JSON nor LABEL_FALLBACKS has it.
	 */
	public static function label( string $key, string $default = '' ): string {
		$labels = self::labels();
		if ( isset( $labels[ $key ] ) && '' !== trim( (string) $labels[ $key ] ) ) {
			return (string) $labels[ $key ];
		}
		if ( '' !== $default ) {
			return $default;
		}
		return self::LABEL_FALLBACKS[ $key ] ?? '';
	}

	/**
	 * Every label, JSON values merged over the fallbacks.
	 *
	 * @return array<string,string>
	 */
	public static function labels(): array {
		static $merged = null;
		if ( null !== $merged ) {
			return $merged;
		}
		$data   = is_callable( array( 'App_Helpers', 'data' ) ) ? App_Data_Provider::get( self::DATA_KEY ) : array();
		$json   = ( is_array( $data ) && ! empty( $data['labels'] ) && is_array( $data['labels'] ) ) ? $data['labels'] : array();
		$merged = array_merge( self::LABEL_FALLBACKS, array_map( 'strval', $json ) );
		return $merged;
	}

	/**
	 * An accessibility string from admin/data/ui.json -> "aria".
	 */
	public static function aria( string $key, string $default = '' ): string {
		$data = is_callable( array( 'App_Helpers', 'data' ) ) ? App_Data_Provider::get( self::DATA_KEY ) : array();
		$map  = ( is_array( $data ) && ! empty( $data['aria'] ) && is_array( $data['aria'] ) ) ? $data['aria'] : array();
		return ( isset( $map[ $key ] ) && '' !== trim( (string) $map[ $key ] ) ) ? (string) $map[ $key ] : $default;
	}

	/**
	 * A behaviour setting from admin/data/ui.json -> "behaviour" (numbers/bools
	 * such as toast duration). Kept out of CSS/JS so timings are editable too.
	 */
	public static function setting( string $key, $default = null ) {
		$data = is_callable( array( 'App_Helpers', 'data' ) ) ? App_Data_Provider::get( self::DATA_KEY ) : array();
		$map  = ( is_array( $data ) && ! empty( $data['behaviour'] ) && is_array( $data['behaviour'] ) ) ? $data['behaviour'] : array();
		return array_key_exists( $key, $map ) ? $map[ $key ] : $default;
	}

	/**
	 * Icon names the browser needs SVG for, because JS builds markup that
	 * uses them (dialog chrome, tone icons, list ticks, arrows).
	 */
	protected const JS_ICONS = array(
		'close', 'check', 'arrow-right', 'chevron-right', 'chevron-down',
		'shield', 'settings', 'lock',
	);

	/**
	 * THE payload handed to assets/js/ui-kit.js as `window.ntUi`.
	 *
	 * This is the whole contract between PHP and the browser. The server
	 * decides WHAT exists - which dialogs this page can open, which notices
	 * apply today, what every button is called - and the browser decides WHEN
	 * and builds the markup. No user-facing copy and no markup template lives
	 * in the JavaScript.
	 *
	 * Built at footer time (see app_localize_ui_kit) so the dialog queue is
	 * complete: only dialogs something on the page actually referenced are
	 * shipped.
	 *
	 * @return array
	 */
	public static function js_config(): array {
		$icons = array();
		foreach ( array_keys( self::TONES ) as $tone ) {
			$icons[ $tone ] = NT_Icons::get( self::tone_icon( $tone ) );
		}
		foreach ( self::JS_ICONS as $name ) {
			$icons[ $name ] = NT_Icons::get( $name );
		}

		return array(
			'labels'   => self::labels(),
			'aria'     => array(
				'close'  => self::aria( 'close_dialog', self::label( 'close' ) ),
				'notice' => self::aria( 'notice', 'Site notice' ),
				'toasts' => self::aria( 'toasts', 'Notifications' ),
			),
			'icons'    => $icons,
			'tones'    => array_keys( self::TONES ),
			'defaults' => array(
				'tone'          => self::DEFAULT_TONE,
				'size'          => self::DEFAULT_SIZE,
				'toastDuration' => (int) self::setting( 'toast_duration', 5000 ),
				'noticeStore'   => (string) self::setting( 'notice_store', 'app_dismissed_notices' ),
				'dialogStore'   => (string) self::setting( 'dialog_store', 'app_seen_dialogs' ),
			),
			// Data for the two things the browser renders on its own.
			'dialogs'  => class_exists( 'NT_Dialog' ) ? NT_Dialog::js_dialogs() : array(),
			'notices'  => class_exists( 'NT_Alert' ) ? NT_Alert::notices() : array(),
			// Where the notice strip is inserted, in preference order.
			'noticeMount' => '#app-nav, .app-nav, header',
		);
	}
}
