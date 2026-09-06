<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

/**
 * SettingsBridgeService — Bridges CMS Site Settings database table (wp_ch_ah_site_settings)
 * with multi-tier fallback to theme JSON content.
 */
class SettingsBridgeService {

	protected static ?array $db_settings = null;

	/**
	 * Read settings from DB table (via AH_Settings_Model or $wpdb)
	 *
	 * @return array<string, string>
	 */
	public static function get_db_settings(): array {
		if ( null !== static::$db_settings ) {
			return static::$db_settings;
		}

		static::$db_settings = array();

		// 1. Direct class from CMS Plugin
		if ( class_exists( '\AH_Settings_Model' ) ) {
			try {
				$model   = new \AH_Settings_Model();
				$grouped = $model->get_all_grouped();
				foreach ( $grouped as $group => $rows ) {
					foreach ( (array) $rows as $row ) {
						if ( ! empty( $row->setting_key ) && isset( $row->setting_val ) && '' !== trim( (string) $row->setting_val ) ) {
							static::$db_settings[ $row->setting_key ] = (string) $row->setting_val;
						}
					}
				}
			} catch ( \Throwable $e ) {
				// Fall through to direct wpdb
			}
		}

		// 2. Direct $wpdb if model not booted
		if ( empty( static::$db_settings ) && isset( $GLOBALS['wpdb'] ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ah_site_settings';
			$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );
			if ( $table_exists === $table ) {
				$results = $wpdb->get_results( "SELECT setting_key, setting_val FROM `{$table}`" );
				if ( ! empty( $results ) && is_array( $results ) ) {
					foreach ( $results as $row ) {
						if ( ! empty( $row->setting_key ) && isset( $row->setting_val ) && '' !== trim( (string) $row->setting_val ) ) {
							static::$db_settings[ $row->setting_key ] = (string) $row->setting_val;
						}
					}
				}
			}
		}

		return static::$db_settings;
	}

	/**
	 * Read fallback JSON settings
	 */
	protected static function settings(): array {
		return (array) ( JsonFileProvider::read( 'data/content/contact-info.json' ) ?? array() );
	}

	public static function site_name(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['site_name'] ) ) {
			return (string) $db['site_name'];
		}
		return function_exists( 'get_bloginfo' ) ? (string) get_bloginfo( 'name' ) : 'The Cane House';
	}

	public static function tagline_fallback(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['footer_tagline'] ) ) {
			return (string) $db['footer_tagline'];
		}
		if ( ! empty( $db['tagline'] ) ) {
			return (string) $db['tagline'];
		}

		$d = static::settings();
		return (string) ( $d['tagline'] ?? ( $d['site']['tagline_fallback'] ?? '' ) );
	}

	public static function phone(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['contact_phone'] ) ) {
			return (string) $db['contact_phone'];
		}
		if ( ! empty( $db['phone'] ) ) {
			return (string) $db['phone'];
		}

		$d = static::settings();
		return (string) ( $d['phone'] ?? ( $d['site']['phone'] ?? '' ) );
	}

	public static function whatsapp(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['whatsapp_number'] ) ) {
			return (string) $db['whatsapp_number'];
		}
		if ( ! empty( $db['whatsapp'] ) ) {
			return (string) $db['whatsapp'];
		}

		$d = static::settings();
		return (string) ( $d['whatsapp'] ?? ( $d['site']['whatsapp'] ?? static::phone() ) );
	}

	public static function email(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['contact_email'] ) ) {
			return (string) $db['contact_email'];
		}
		if ( ! empty( $db['email'] ) ) {
			return (string) $db['email'];
		}

		$d = static::settings();
		return (string) ( $d['email'] ?? ( $d['site']['email'] ?? '' ) );
	}

	public static function address(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['contact_address'] ) ) {
			return (string) $db['contact_address'];
		}
		if ( ! empty( $db['address'] ) ) {
			return (string) $db['address'];
		}

		$d = static::settings();
		return (string) ( $d['address'] ?? ( $d['site']['address'] ?? '' ) );
	}

	public static function get_setting( string $key, string $default = '' ): string {
		$db = static::get_db_settings();
		if ( isset( $db[ $key ] ) && '' !== trim( (string) $db[ $key ] ) ) {
			return (string) $db[ $key ];
		}
		$d = static::settings();
		return (string) ( $d[ $key ] ?? $default );
	}

	public static function google_maps_url(): string {
		return static::get_setting( 'google_maps_url', 'https://maps.google.com/?q=Sutton,London' );
	}

	public static function website(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['website'] ) ) {
			return (string) $db['website'];
		}

		$d = static::settings();
		return (string) ( $d['website'] ?? ( $d['site']['website'] ?? '' ) );
	}

	public static function opening_hours(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['opening_hours'] ) ) {
			return (string) $db['opening_hours'];
		}

		$d = static::settings();
		return (string) ( $d['hours'] ?? ( $d['opening_hours'] ?? ( $d['site']['opening_hours'] ?? '' ) ) );
	}

	public static function logo_fallback( string $context = 'header' ): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['site_logo'] ) ) {
			return (string) $db['site_logo'];
		}

		$d = static::settings();
		if ( 'footer' === $context && ! empty( $d['logo_footer'] ) ) {
			return (string) $d['logo_footer'];
		}
		return (string) ( $d['logo'] ?? ( $d['site']['logo_fallback'] ?? '' ) );
	}

	public static function socials(): array {
		$db = static::get_db_settings();
		$links = array();

		if ( ! empty( $db['instagram_url'] ) ) $links['instagram'] = (string) $db['instagram_url'];
		if ( ! empty( $db['facebook_url'] ) )  $links['facebook']  = (string) $db['facebook_url'];
		if ( ! empty( $db['youtube_url'] ) )   $links['youtube']   = (string) $db['youtube_url'];
		if ( ! empty( $db['tiktok_url'] ) )    $links['tiktok']    = (string) $db['tiktok_url'];
		if ( ! empty( $db['whatsapp_url'] ) )  $links['whatsapp']  = (string) $db['whatsapp_url'];

		if ( ! empty( $links ) ) {
			return $links;
		}

		$d = static::settings();
		if ( ! empty( $d['social_links'] ) && is_array( $d['social_links'] ) ) {
			return array_filter( (array) $d['social_links'] );
		}
		if ( ! empty( $d['socials'] ) && is_array( $d['socials'] ) ) {
			foreach ( $d['socials'] as $soc ) {
				$icon = (string) ( $soc['icon'] ?? '' );
				$url  = (string) ( $soc['url'] ?? '' );
				if ( '' !== $icon && '' !== $url ) {
					$links[ $icon ] = $url;
				}
			}
			return $links;
		}
		return array_filter( (array) ( $d['social'] ?? array() ) );
	}

	public static function social_links(): array {
		return static::socials();
	}

	public static function social_url( string $key, string $default = '' ): string {
		$socials = static::socials();
		return (string) ( $socials[ $key ] ?? $default );
	}

	public static function whatsapp_url(): string {
		$db = static::get_db_settings();
		if ( ! empty( $db['whatsapp_url'] ) ) {
			return (string) $db['whatsapp_url'];
		}
		$wa = static::social_url( 'whatsapp', '' );
		if ( '' !== $wa ) {
			return $wa;
		}
		$wa_num = static::whatsapp();
		$digits = preg_replace( '/\D+/', '', $wa_num );
		return '' !== $digits ? 'https://wa.me/' . $digits : '';
	}

	public static function preheader(): array {
		$db = static::get_db_settings();
		if ( ! empty( $db['preheader_text'] ) ) {
			return array_values( array_filter( array_map( 'trim', explode( '•', (string) $db['preheader_text'] ) ) ) );
		}

		$d = static::settings();
		return array_values( array_filter( array_map( 'strval', (array) ( $d['preheader'] ?? array() ) ) ) );
	}
}
