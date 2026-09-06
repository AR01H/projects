<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

/**
 * FormBridgeService — Intermediate theme-level service for CMS Plugin Form Builder integration.
 *
 * 100% JSON-driven: Reads all form mappings, IDs, shortcodes, agreements, aliases,
 * and defaults exclusively from `data/content/forms.json`.
 */
final class FormBridgeService {

	/**
	 * Raw JSON payload cache
	 *
	 * @var array<string, mixed>|null
	 */
	private static ?array $raw_data_cache = null;

	/**
	 * Get the entire forms data structure from JSON
	 *
	 * @return array<string, mixed>
	 */
	public static function get_raw_data(): array {
		if ( null === self::$raw_data_cache ) {
			self::$raw_data_cache = (array) ( JsonFileProvider::read( 'data/content/forms.json' ) ?? array() );
		}
		return self::$raw_data_cache;
	}

	/**
	 * Get all configured forms from JSON
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_forms_config(): array {
		$data = self::get_raw_data();
		return (array) ( $data['forms'] ?? array() );
	}

	/**
	 * Get specific form configuration by key (e.g. 'contact', 'events_booking', 'franchise')
	 *
	 * Everything is resolved dynamically from `data/content/forms.json` with zero hardcoding in PHP.
	 *
	 * @param string $form_key
	 * @return array<string, mixed>
	 */
	public static function get_form_config( string $form_key = 'contact' ): array {
		$data    = self::get_raw_data();
		$forms   = (array) ( $data['forms'] ?? $data );
		$aliases = (array) ( $data['aliases'] ?? array() );

		// 1. Direct match
		if ( isset( $forms[ $form_key ] ) ) {
			$val = $forms[ $form_key ];
			return is_array( $val ) ? $val : array( 'shortcode' => (string) $val );
		}

		// 2. Alias match from JSON
		if ( isset( $aliases[ $form_key ] ) && isset( $forms[ $aliases[ $form_key ] ] ) ) {
			$val = $forms[ $aliases[ $form_key ] ];
			return is_array( $val ) ? $val : array( 'shortcode' => (string) $val );
		}

		// 3. Configured default key from JSON
		$default_key = (string) ( $data['default_key'] ?? 'contact' );
		if ( isset( $forms[ $default_key ] ) ) {
			$val = $forms[ $default_key ];
			return is_array( $val ) ? $val : array( 'shortcode' => (string) $val );
		}

		// 4. First available form in JSON
		if ( ! empty( $forms ) ) {
			$first = reset( $forms );
			if ( is_array( $first ) ) {
				return $first;
			} elseif ( is_string( $first ) ) {
				return array( 'shortcode' => $first );
			}
		}

		return array();
	}

	/**
	 * Get the exact shortcode string for a form key from JSON
	 *
	 * @param string $form_key
	 * @return string
	 */
	public static function get_shortcode( string $form_key = 'contact' ): string {
		$cfg = self::get_form_config( $form_key );
		if ( ! empty( $cfg['shortcode'] ) ) {
			return (string) $cfg['shortcode'];
		}
		if ( ! empty( $cfg['id'] ) ) {
			return '[ah_form id="' . (int) $cfg['id'] . '"]';
		}
		return '';
	}

	/**
	 * Render a CMS Plugin Form Builder form by its configured key
	 *
	 * @param string $form_key
	 * @return string Rendered HTML or empty string if plugin is unavailable
	 */
	public static function render( string $form_key = 'contact' ): string {
		$cfg       = self::get_form_config( $form_key );
		$shortcode = self::get_shortcode( $form_key );

		if ( empty( $shortcode ) && empty( $cfg['id'] ) ) {
			return '';
		}

		// Extract ID dynamically from shortcode string or config
		$form_id = (int) ( $cfg['id'] ?? 0 );
		if ( $form_id <= 0 && preg_match( '/id=[\'"]?(\d+)[\'"]?/', $shortcode, $matches ) ) {
			$form_id = (int) $matches[1];
		}

		// 1. Namespaced FormBuilderController
		if ( $form_id > 0 && class_exists( '\Ah\Cms\Feature\Forms\Controller\FormBuilderController' ) && method_exists( '\Ah\Cms\Feature\Forms\Controller\FormBuilderController', 'render' ) ) {
			$output = (string) \Ah\Cms\Feature\Forms\Controller\FormBuilderController::render( array( 'id' => $form_id ) );
			if ( '' !== trim( $output ) ) {
				return $output;
			}
		}

		// 2. Shortcode execution using exact string from JSON
		if ( '' !== $shortcode && function_exists( 'do_shortcode' ) ) {
			$output = (string) do_shortcode( $shortcode );
			if ( '' !== trim( $output ) ) {
				return $output;
			}
		}

		return '';
	}

	/**
	 * Get agreement configuration for a form key from JSON
	 *
	 * @param string $form_key
	 * @return array<string, mixed>
	 */
	public static function get_agreement( string $form_key = 'contact' ): array {
		$cfg = self::get_form_config( $form_key );
		return (array) ( $cfg['agreement'] ?? array() );
	}
}
