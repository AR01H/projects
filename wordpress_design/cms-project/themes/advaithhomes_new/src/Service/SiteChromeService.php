<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class SiteChromeService {

	private static ?array $cache = null;

	public static function getData(): array {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$chrome = \ADN_Real_Loader::json( 'site_chrome' );
		$chrome = \is_array( $chrome ) ? $chrome : [];
		$chrome = self::overlayDbSettings( $chrome );

		$plugin_nav = self::getPluginNav();
		if ( ! empty( $plugin_nav ) ) {
			$chrome['nav'] = $plugin_nav;
		}

		$plugin_cta = self::getPluginCta();
		if ( ! empty( $plugin_cta ) ) {
			$chrome['header_cta'] = $plugin_cta;
		}

		$json_footer = isset( $chrome['footer'] ) && \is_array( $chrome['footer'] ) ? $chrome['footer'] : [];
		$plugin_footer = self::getPluginFooter( $json_footer );
		if ( ! empty( $plugin_footer ) ) {
			$chrome['footer'] = $plugin_footer;
		}

		$social_map = [
			'facebook_url'  => [ 'label' => 'Facebook',  'icon' => 'fab fa-facebook-f' ],
			'instagram_url' => [ 'label' => 'Instagram', 'icon' => 'fab fa-instagram' ],
			'twitter_url'   => [ 'label' => 'X / Twitter', 'icon' => 'fab fa-x-twitter' ],
			'linkedin_url'  => [ 'label' => 'LinkedIn',  'icon' => 'fab fa-linkedin-in' ],
			'youtube_url'   => [ 'label' => 'YouTube',   'icon' => 'fab fa-youtube' ],
			'tiktok_url'    => [ 'label' => 'TikTok',    'icon' => 'fab fa-tiktok' ],
		];
		$db_socials = [];
		foreach ( $social_map as $sk => $sm ) {
			$sv = self::getSocialSetting( $sk );
			if ( '' !== $sv ) {
				$db_socials[] = [ 'url' => $sv, 'label' => $sm['label'], 'icon' => $sm['icon'] ];
			}
		}
		if ( ! isset( $chrome['footer'] ) || ! \is_array( $chrome['footer'] ) ) {
			$chrome['footer'] = [];
		}
		$chrome['footer']['social'] = $db_socials;

		self::$cache = $chrome;
		return self::$cache;
	}

	public static function getContactSetting( string $key ): string {
		$settings = \get_option( 'adn_site_settings', [] );
		$group = $settings['contact'] ?? [];
		return (string) ( $group[ $key ] ?? '' );
	}

	public static function getSocialSetting( string $key ): string {
		$settings = \get_option( 'adn_site_settings', [] );
		$group = $settings['social'] ?? [];
		return (string) ( $group[ $key ] ?? '' );
	}

	private static function overlayDbSettings( array $chrome ): array {
		$db_settings = \adn_chrome_db_settings();
		if ( \is_array( $db_settings ) ) {
			$chrome = \array_replace_recursive( $chrome, $db_settings );
		}
		return $chrome;
	}

	private static function getPluginNav(): array {
		return \function_exists( 'adn_chrome_plugin_nav' ) ? \adn_chrome_plugin_nav() : [];
	}

	private static function getPluginCta(): array {
		return \function_exists( 'adn_chrome_plugin_cta' ) ? \adn_chrome_plugin_cta() : [];
	}

	private static function getPluginFooter( array $json_footer ): array {
		return \function_exists( 'adn_chrome_plugin_footer' ) ? \adn_chrome_plugin_footer( $json_footer ) : [];
	}
}
