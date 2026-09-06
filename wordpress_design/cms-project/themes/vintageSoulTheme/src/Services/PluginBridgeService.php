<?php
namespace VintageSoul\Services;

use VintageSoul\Services\Plugins\BannerBridgeService;
use VintageSoul\Services\Plugins\ClientStoriesBridgeService;
use VintageSoul\Services\Plugins\CookieConsentBridgeService;
use VintageSoul\Services\Plugins\CustomCodeBridgeService;
use VintageSoul\Services\Plugins\FaqBridgeService;
use VintageSoul\Services\Plugins\FeaturedInBridgeService;
use VintageSoul\Services\Plugins\FormBridgeService;
use VintageSoul\Services\Plugins\GlobalSettingsBridgeService;
use VintageSoul\Services\Plugins\GoogleServicesBridgeService;
use VintageSoul\Services\Plugins\NavigationBridgeService;
use VintageSoul\Services\Plugins\NoticeBridgeService;
use VintageSoul\Services\Plugins\PageBridgeService;
use VintageSoul\Services\Plugins\RedirectBridgeService;
use VintageSoul\Services\Plugins\StaticPageBridgeService;
use VintageSoul\Services\Plugins\VisitorBridgeService;

defined( 'ABSPATH' ) || exit;

/**
 * PluginBridgeService — Coordinates all theme-level CMS Plugin bridges and overrides.
 *
 * Bridges located under namespace VintageSoul\Services\Plugins:
 * - AdminBridgeService (admin menu exclusions, redirects, styles)
 * - BannerBridgeService (home hero banners DB manager & autoplay)
 * - ClientStoriesBridgeService (CMS Showcase Gallery header & images)
 * - CustomCodeBridgeService (CMS Custom Code global/per-page CSS & JS)
 * - FaqBridgeService (CMS FAQ Builder, global & per-page-slug)
 * - FeaturedInBridgeService (CMS Featured In logo strips)
 * - FormBridgeService (form builder and dynamic AJAX handlers)
 * - GlobalSettingsBridgeService (CMS Global Settings timezone, cache, & image optimization)
 * - GoogleServicesBridgeService (Google Analytics/Tag Manager IDs -> plugin's google-services.js)
 * - CookieConsentBridgeService (cookie-consent version bump / re-ask)
 * - NavigationBridgeService (navigation editor and CTA buttons)
 * - NoticeBridgeService (site notices and popups)
 * - PageBridgeService (CMS Pages Manager, hero titles/descriptions/media)
 * - RedirectBridgeService (CMS Redirect Rules 301, 302, 410, & exit interstitial)
 * - SettingsBridgeService (site settings DB table and fallback chain)
 * - StaticPageBridgeService (CMS Static HTML Pages Manager & Template)
 * - VisitorBridgeService (CMS Visitor Logs & Real-Time Stats)
 */
final class PluginBridgeService {

	public static function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_plugin_override_styles' ), 99 );
		add_action( 'wp_footer', array( self::class, 'render_site_notices' ), 25 );
		StaticPageBridgeService::register_hooks();
		VisitorBridgeService::register_hooks();
		CustomCodeBridgeService::register_hooks();
		RedirectBridgeService::register_hooks();
		GlobalSettingsBridgeService::register_hooks();
		GoogleServicesBridgeService::register_hooks();
		CookieConsentBridgeService::register_hooks();
	}

	/**
	 * Enqueue CMS Plugin theme-level override styles
	 */
	public static function enqueue_plugin_override_styles(): void {
		$css_url = VINTAGESOUL_URI . '/assets/css/cmsplugstylesoverride.css';
		wp_enqueue_style( 'vst-cms-plugin-overrides', $css_url, array(), VINTAGESOUL_VERSION );
	}

	/**
	 * Render a form from CMS Form Builder dynamically by its key
	 */
	public static function render_form( string $form_key = 'contact' ): string {
		return FormBridgeService::render( $form_key );
	}

	/**
	 * Get configuration for a specific form from JSON
	 */
	public static function get_form_config( string $form_key = 'contact' ): array {
		return FormBridgeService::get_form_config( $form_key );
	}

	/**
	 * Get a "Featured In" logo strip section's logos by section id
	 */
	public static function get_featured_in_items( string $section_id = '' ): array {
		return FeaturedInBridgeService::get_items( $section_id );
	}

	/**
	 * Get "Showcase Gallery" images from the CMS Client Stories admin
	 */
	public static function get_showcase_gallery_items(): array {
		return ClientStoriesBridgeService::get_gallery_items();
	}

	/**
	 * Get FAQ items from the CMS FAQ Builder, by page slug (or global)
	 */
	public static function get_faq_items( string $slug = '' ): array {
		return FaqBridgeService::get_items( $slug );
	}

	/**
	 * Render site notices popup/announcement bar from AH CMS Plugin on frontend
	 */
	public static function render_site_notices(): void {
		NoticeBridgeService::render();
	}

	/**
	 * Get primary header navigation items from CMS Plugin Navigation Editor
	 */
	public static function get_primary_navigation(): array {
		return NavigationBridgeService::get_primary_navigation();
	}

	/**
	 * Get header CTA button configuration from CMS Plugin Navigation Editor
	 */
	public static function get_header_cta(): array {
		return NavigationBridgeService::get_header_cta();
	}

	/**
	 * Get active site notices list from CMS Plugin database
	 */
	public static function get_active_site_notices(): array {
		return NoticeBridgeService::get_active();
	}

	/**
	 * Format date/time according to Global Settings timezone offset
	 */
	public static function format_datetime( $datetime = 'now', string $format = 'j F Y, g:i A' ): string {
		return GlobalSettingsBridgeService::format_datetime( $datetime, $format );
	}

	/**
	 * Get site timezone offset
	 */
	public static function get_timezone_offset(): float {
		return GlobalSettingsBridgeService::get_timezone_offset();
	}

	/**
	 * Get site timezone label string
	 */
	public static function get_timezone_string(): string {
		return GlobalSettingsBridgeService::get_timezone_string();
	}

	/**
	 * Check if cache is active
	 */
	public static function is_cache_enabled(): bool {
		return GlobalSettingsBridgeService::is_cache_enabled();
	}

	/**
	 * Flush all theme and plugin caches
	 */
	public static function clear_all_caches(): bool {
		return GlobalSettingsBridgeService::clear_all();
	}
}

