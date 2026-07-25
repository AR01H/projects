<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * ExpertSingleContext - Builds context for single expert profile pages.
 * Split into small focused methods.
 */
class ExpertSingleContext {

	// ── Cache helpers ───────────────────────────────────────────
	private static function cacheGet( string $key ) {
		if ( class_exists( 'ADN_Cache' ) ) {
			return \ADN_Cache::get( $key, 'pages' );
		}
		return false;
	}

	private static function cacheSet( string $key, array $ctx ): void {
		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
		}
	}

	// ── Hero section ────────────────────────────────────────────
	public static function buildHero( string $name, string $title ): array {
		return array(
			'title'       => $name,
			'description' => $title,
			'bg_icon'     => '👤',
		);
	}

	// ── Breadcrumb section ──────────────────────────────────────
	public static function buildBreadcrumb( string $name ): array {
		return \Adn\Theme\Shared\BreadcrumbBuilder::expertSingle( $name );
	}

	// ── Photo section ───────────────────────────────────────────
	public static function buildPhoto( array $expert ): string {
		$photo_id = isset( $expert['photo_id'] ) ? (int) $expert['photo_id'] : 0;
		if ( $photo_id > 0 ) {
			$big = wp_get_attachment_image_url( $photo_id, 'large' );
			if ( $big ) { return $big; }
		}
		return '';
	}

	// ── Bullets section ─────────────────────────────────────────
	public static function buildBullets( array $expert ): array {
		$bullets = array();
		if ( ! empty( $expert['bullets'] ) ) {
			$dec = json_decode( $expert['bullets'], true );
			if ( is_array( $dec ) ) { $bullets = $dec; }
		}
		return $bullets;
	}

	// ── Banner section ──────────────────────────────────────────
	public static function buildBanner( array $expert ): array {
		$image_id = isset( $expert['banner_image_id'] ) ? (int) $expert['banner_image_id'] : 0;
		$image_url = '';
		if ( $image_id > 0 ) {
			$_bu = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $_bu ) { $image_url = (string) $_bu; }
		}

		$items = array();
		if ( ! empty( $expert['banner_json'] ) ) {
			$_bd = json_decode( $expert['banner_json'], true );
			if ( is_array( $_bd ) ) {
				foreach ( $_bd as $_bi ) {
					if ( ! is_array( $_bi ) ) { continue; }
					$_bv = $_bi['value'] ?? '';
					$_bl = $_bi['label'] ?? '';
					if ( '' === $_bv && '' === $_bl ) { continue; }
					$items[] = array(
						'icon'  => $_bi['icon'] ?? '',
						'label' => $_bv,
						'note'  => $_bl,
					);
				}
			}
		}

		return array(
			'image_url' => $image_url,
			'items'     => $items,
		);
	}

	// ── Client images section ───────────────────────────────────
	public static function buildClientImages( array $expert ): array {
		$images = array();
		if ( empty( $expert['client_images'] ) ) { return $images; }
		$dec = json_decode( $expert['client_images'], true );
		if ( ! is_array( $dec ) ) { return $images; }
		foreach ( $dec as $ci ) {
			if ( ! is_array( $ci ) ) { continue; }
			$ci_id = isset( $ci['image_id'] ) ? (int) $ci['image_id'] : 0;
			$ci_url = '';
			if ( $ci_id > 0 ) {
				$u = wp_get_attachment_image_url( $ci_id, 'medium_large' );
				if ( $u ) { $ci_url = $u; }
			}
			if ( '' === $ci_url ) { continue; }
			$images[] = array(
				'url'     => $ci_url,
				'caption' => $ci['caption'] ?? '',
			);
		}
		return $images;
	}

	// ── Profile lock check ──────────────────────────────────────
	public static function isProfileLocked( array $expert ): bool {
		if ( empty( $expert['is_locked'] ) ) { return false; }
		$_banner_data    = get_option( 'adn_expert_banner', array() );
		$_stored_pw      = $_banner_data['unlock_password'] ?? '';
		$_expected_token = ( '' !== $_stored_pw ) ? hash_hmac( 'sha256', $_stored_pw, wp_salt( 'secure_auth' ) ) : '';
		$_cookie_val     = $_COOKIE['adn_experts_unlocked'] ?? '';
		$_is_unlocked    = ( '' !== $_expected_token && '' !== $_cookie_val && hash_equals( $_expected_token, $_cookie_val ) );
		return ! $_is_unlocked;
	}

	// ── Main getContext ─────────────────────────────────────────
	public static function getContext( $slug ) {
		if ( ! class_exists( 'AH_Expert_DB' ) ) { return null; }
		$slug = sanitize_key( $slug );

		$cache_key = 'expert_single_context_' . $slug;
		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) { return $cached; }

		$expert = \AH_Expert_DB::get( $slug );
		if ( ! $expert || 'active' !== $expert['status'] ) { return null; }

		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();
		$name   = $expert['name'] ?? '';
		$title  = $expert['title'] ?? '';

		$result = array(
			'slug'          => $slug,
			'name'          => $name,
			'title'         => $title,
			'category'      => trim( $expert['category'] ?? '' ),
			'photo_url'     => self::buildPhoto( $expert ),
			'bio'           => trim( $expert['bio'] ?? '' ),
			'rating'        => (float) ( $expert['rating'] ?? 0 ),
			'reviews_count' => (int) ( $expert['reviews_count'] ?? 0 ),
			'location'      => trim( $expert['location'] ?? '' ),
			'phone'         => trim( $expert['phone'] ?? '' ),
			'email'         => trim( $expert['email'] ?? '' ),
			'bullets'          => self::buildBullets( $expert ),
			'banner_image_url' => self::buildBanner( $expert )['image_url'],
			'banner_items'     => self::buildBanner( $expert )['items'],
			'client_images'    => self::buildClientImages( $expert ),
			'mega_html'     => $expert['mega_html'] ?? '',
			'hero'          => self::buildHero( $name, $title ),
			'breadcrumb'    => self::buildBreadcrumb( $name ),
			'chrome'        => $chrome,
			'contact_nonce' => wp_create_nonce( 'adn_expert_contact' ),
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'is_locked'     => self::isProfileLocked( $expert ),
			'unlock_nonce'  => wp_create_nonce( 'adn_expert_unlock' ),
		);

		self::cacheSet( $cache_key, $result );
		return $result;
	}
}
