<?php
namespace VintageSoul\Services\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * NoticeBridgeService — Bridges AH CMS Plugin Site Notices with the theme frontend.
 */
class NoticeBridgeService {

	/**
	 * Render site notices popup/announcement bar on frontend.
	 *
	 * Suppresses scope:"all" notices (admin.php?page=ah-notices, no "Slugs"
	 * set) on every page except the front page. That admin's own per-page
	 * targeting can't reach the front page - this site has no static front
	 * page (Settings > Reading = "Your latest posts"), so the plugin's own
	 * slug-matching computes an empty string there, which its filter logic
	 * treats as "no restriction" (shows everywhere) rather than "home only".
	 * A notice explicitly scoped to real page slugs (scope:"slugs") is left
	 * entirely to the plugin's own matching, unaffected by this.
	 */
	public static function render(): void {
		if ( ! is_front_page() && ! is_home() && self::has_only_untargeted_notices() ) {
			return;
		}

		if ( class_exists( 'AH_Notice_Helper' ) ) {
			\AH_Notice_Helper::render_frontend_popup();
			return;
		}

		if ( class_exists( 'AH_Site_Notices_Model' ) ) {
			if ( defined( 'AH_PLUGIN_DIR' ) && is_file( AH_PLUGIN_DIR . '/helper/NoticeHelper.php' ) ) {
				require_once AH_PLUGIN_DIR . '/helper/NoticeHelper.php';
				if ( class_exists( 'AH_Notice_Helper' ) ) {
					\AH_Notice_Helper::render_frontend_popup();
				}
			}
		}
	}

	/**
	 * True when every currently-active notice is scope:"all" (so none of
	 * them are the admin explicitly targeting real page slugs) - meaning
	 * it's safe to skip the whole render call on a non-front page.
	 */
	private static function has_only_untargeted_notices(): bool {
		$active = self::get_active();
		if ( empty( $active ) ) {
			return true;
		}
		foreach ( $active as $notice ) {
			if ( 'slugs' === ( $notice->scope ?? '' ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get active site notices from database
	 *
	 * @return array<object>
	 */
	public static function get_active(): array {
		if ( class_exists( 'AH_Site_Notices_Model' ) ) {
			$model = new \AH_Site_Notices_Model();
			return (array) $model->get_active();
		}
		return array();
	}
}
