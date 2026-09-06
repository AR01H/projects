<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * BannerBridgeService — Bridges CMS Home Banners (admin.php?page=ah-banners)
 * from table {prefix}ah_home_banners with multi-tier fallback to hero.json.
 */
class BannerBridgeService {

	/**
	 * Get complete hero configuration with slides and autoplay settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_hero(): array {
		$slides = self::get_slides();
		$delay  = self::get_autoplay_delay();

		return array(
			'enabled'  => ! empty( $slides ),
			'settings' => array(
				'autoplay'            => true,
				'autoplay_delay'      => $delay,
				'transition'          => 'fade',
				'transition_duration' => 800,
				'pause_on_hover'      => true,
			),
			'slides'   => $slides,
		);
	}

	/**
	 * Get active banner slides from CMS Database with JSON fallback.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_slides(): array {
		$db_banners = self::get_db_banners();

		if ( ! empty( $db_banners ) ) {
			$hero_json = (array) ( JsonFileProvider::read( 'data/content/hero.json' ) ?? array() );
			$defaults  = (array) ( $hero_json['db_banner_defaults'] ?? array() );
			$secondary_button   = (array) ( $defaults['secondary_button'] ?? array() );
			$no_text_fallback   = (array) ( $defaults['no_button_text_fallback'] ?? array() );

			$slides = array();
			foreach ( $db_banners as $b ) {
				$image        = (string) ( $b['image'] ?? '' );
				$image_mobile = (string) ( $b['image_mobile'] ?? '' );
				$title        = (string) ( $b['title'] ?? '' );
				$subtitle     = (string) ( $b['subtitle'] ?? '' );
				$description  = (string) ( $b['description'] ?? '' );
				$btn_text     = (string) ( $b['btn_text'] ?? '' );
				$btn_url      = (string) ( $b['btn_url'] ?? '/contact' );
				$btn_target   = (string) ( $b['btn_target'] ?? '_self' );

				if ( '' === $image && '' === $title ) {
					continue;
				}

				$is_video = (bool) preg_match( '/\.(mp4|webm|ogv|ogg|mov)$/i', $image );
				$img_src  = UrlHelper::resolve( $image );
				$mob_src  = '' !== $image_mobile ? UrlHelper::resolve( $image_mobile ) : '';

				$buttons = array();
				if ( '' !== $btn_text ) {
					$buttons[] = array(
						'label'  => $btn_text,
						'route'  => $btn_url,
						'url'    => $btn_url,
						'target' => $btn_target,
						'icon'   => '📍',
						'style'  => 'primary',
					);
					// The Home Banners admin only has one button's worth of
					// fields - if it filled those in, top up with the
					// secondary button from hero.json's db_banner_defaults.
					if ( ! empty( $secondary_button ) ) {
						$buttons[] = $secondary_button;
					}
				} else {
					$buttons = $no_text_fallback;
				}

				$slides[] = array(
					'id'      => 'banner-' . ( $b['id'] ?? uniqid() ),
					'media'   => array(
						'type'       => $is_video ? 'video' : 'image',
						'src'        => $img_src,
						'mobile_src' => $mob_src,
						'video'      => $is_video ? $img_src : '',
						'poster'     => UrlHelper::resolve( 'assets/images/sugarcane/hero_juice.jpg' ),
						'alt'        => wp_strip_all_tags( $title ?: 'The Cane House' ),
					),
					'content' => array(
						'eyebrow'     => $subtitle ?: 'Freshly Pressed · Naturally Refreshing',
						'title'       => $title ?: 'WELCOME TO THE TASTE OF TRADITION',
						'subtitle'    => $subtitle ?: '100% Natural · No Additives',
						'description' => $description,
						'checklist'   => array(
							'100% Natural · No Additives · Freshly Pressed',
							'Freshly cold-pressed right before your eyes.',
							'A taste of tradition, crafted with love.',
						),
						'buttons'     => $buttons,
					),
					'overlay' => (string) ( $b['overlay'] ?? 'rgba(16,45,24,0.35)' ),
				);
			}

			if ( ! empty( $slides ) ) {
				return $slides;
			}
		}

		// Fallback: Read from hero.json
		$data   = (array) ( JsonFileProvider::read( 'data/content/hero.json' ) ?? array() );
		$slides = is_array( $data['slides'] ?? null ) ? $data['slides'] : array();

		foreach ( $slides as &$slide ) {
			$slide          = (array) $slide;
			$media          = (array) ( $slide['media'] ?? array() );
			$slide['media'] = array(
				'type'       => (string) ( $media['type'] ?? 'image' ),
				'src'        => UrlHelper::resolve( (string) ( $media['src'] ?? '' ) ),
				'mobile_src' => ! empty( $media['mobile_src'] ) ? UrlHelper::resolve( (string) $media['mobile_src'] ) : '',
				'poster'     => ! empty( $media['poster'] ) ? UrlHelper::resolve( (string) $media['poster'] ) : '',
				'alt'        => (string) ( $media['alt'] ?? '' ),
			);
		}
		unset( $slide );

		return $slides;
	}

	/**
	 * Read active banners from the CMS Database table.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_db_banners(): array {
		// 1. If helper class is available
		if ( class_exists( 'AH_Banners_Helper' ) ) {
			try {
				$rows = \AH_Banners_Helper::get_all( true );
				if ( ! empty( $rows ) && is_array( $rows ) ) {
					return $rows;
				}
			} catch ( \Throwable $e ) {
				// Fall through
			}
		}

		// 2. Direct $wpdb query
		if ( isset( $GLOBALS['wpdb'] ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ah_home_banners';
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $table_exists === $table ) {
				$output_type = defined( 'ARRAY_A' ) ? \ARRAY_A : 'ARRAY_A';
				$rows = $wpdb->get_results( "SELECT * FROM `{$table}` WHERE status = 'active' ORDER BY sort_order ASC, id ASC", $output_type );
				if ( ! empty( $rows ) && is_array( $rows ) ) {
					return $rows;
				}
			}
		}

		return array();
	}

	/**
	 * Get configured autoplay delay in milliseconds (default: 5000ms).
	 */
	public static function get_autoplay_delay(): int {
		if ( class_exists( 'AH_Banners_Helper' ) ) {
			return \AH_Banners_Helper::get_autoplay();
		}
		$v = (int) get_option( 'ah_banner_autoplay', 5000 );
		return $v > 0 ? $v : 5000;
	}
}
