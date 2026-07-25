<?php
namespace Adn\Theme\Shared;

defined( 'ABSPATH' ) || exit;

/**
 * RelatedPostsBuilder - Shared related posts / latest news builders.
 *
 * Handles news items from newsbar, latest news widgets, and related calculators.
 * SidebarBuilder::latestNews() handles sidebar-specific news; this class handles
 * main-content-area news and related items.
 */
class RelatedPostsBuilder {

	/**
	 * News items from newsbar (main content area).
	 *
	 * Used by: GuidesContext, ToolsContext, TopicCategoryContext
	 *
	 * @param int    $limit   Max items.
	 * @param string $shape   'compact' (title/date/tag/gradient/url) or 'full' (adds description/thumbnail).
	 */
	public static function newsItems( int $limit = 3, string $shape = 'compact' ): array {
		$items = array();
		if ( ! function_exists( 'adn_cms_newsbar_items' ) ) {
			return $items;
		}
		foreach ( adn_cms_newsbar_items( $limit ) as $i => $item ) {
			$title = $item->text ?? '';
			if ( '' === $title ) { continue; }
			$stamp = ! empty( $item->start_date ) ? $item->start_date : ( $item->created_at ?? '' );
			$row   = array(
				'title'    => $title,
				'date'     => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'      => $item->label ?? 'NEWS',
				'gradient' => function_exists( 'adn_cms_gradient' ) ? adn_cms_gradient( $i ) : '',
				'url'      => ! empty( $item->link_url ) ? $item->link_url : ( function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $item->id, $item->slug ?? '' ) : '#' ),
			);
			if ( 'full' === $shape ) {
				$row['description'] = ! empty( $item->content ) ? wp_strip_all_tags( (string) $item->content ) : '';
				$row['thumbnail']   = ! empty( $item->image_id ) ? ( wp_get_attachment_image_url( (int) $item->image_id, 'thumbnail' ) ?: '' ) : '';
			}
			$items[] = $row;
		}
		return $items;
	}

	/**
	 * Latest news widget with heading (main content area).
	 *
	 * Used by: AskExpertContext, GuidanceContext
	 *
	 * @param int    $limit  Max items.
	 * @param string $heading Optional heading title override.
	 */
	public static function latestNewsWidget( int $limit = 3, string $heading = '' ): array {
		if ( '' === $heading ) {
			$heading = defined( 'SITE_LABEL_LATEST_NEWS' ) ? SITE_LABEL_LATEST_NEWS : 'Latest News';
		}
		return array(
			'heading' => array(
				'title'      => $heading,
				'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
				'link_url'   => defined( 'SITE_NEWS_URL' ) ? SITE_NEWS_URL : '/',
			),
			'items' => function_exists( 'adn_shared_latest_news_items' )
				? adn_shared_latest_news_items( $limit )
				: array(),
		);
	}

	/**
	 * Latest updates widget with heading (regulations).
	 *
	 * Used by: GuidanceContext
	 */
	public static function latestUpdatesWidget( int $limit = 3 ): array {
		return array(
			'heading' => array(
				'title'      => adn_term( 'labels.latest_updates', 'Latest Updates' ),
				'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
				'link_url'   => defined( 'SITE_REGULATIONS_URL' ) ? SITE_REGULATIONS_URL : '/',
			),
			'items' => function_exists( 'adn_shared_latest_updates_items' )
				? adn_shared_latest_updates_items( $limit )
				: array(),
		);
	}

	/**
	 * Related calculators by category overlap.
	 *
	 * Used by: ToolSingleContext
	 *
	 * @param string $current_key Current calculator key (excluded from results).
	 * @param array  $categories  Categories of current calculator.
	 * @param array  $registry    Full calculator registry.
	 * @param array  $meta_all    All calculator meta.
	 * @param int    $limit       Max related items.
	 */
	public static function relatedCalculators( string $current_key, array $categories, array $registry, array $meta_all, int $limit = 3 ): array {
		$related = array();
		if ( empty( $categories ) ) {
			return $related;
		}
		foreach ( $registry as $rkey => $rcalc ) {
			if ( $rkey === $current_key ) { continue; }
			$rmeta = $meta_all[ $rkey ] ?? array();
			$r_cats = $rmeta['categories'] ?? array();
			if ( ! array_intersect( $categories, $r_cats ) ) { continue; }
			$_thumb = '';
			if ( ! empty( $rmeta['thumbnail_id'] ) ) {
				$_t = wp_get_attachment_image_url( (int) $rmeta['thumbnail_id'], 'thumbnail' );
				$_thumb = $_t ? (string) $_t : '';
			}
			$related[] = array(
				'icon'      => $rcalc['icon'] ?? adn_term( 'icons.tools', '🧮' ),
				'name'      => $rcalc['title'] ?? $rkey,
				'url'       => $rmeta['card_url'] ?? adn_calc_page_url( $rkey ),
				'thumbnail' => $_thumb,
				'highlight' => $rmeta['highlight'] ?? '',
				'desc'      => $rmeta['desc'] ?? '',
			);
			if ( count( $related ) >= $limit ) { break; }
		}
		return $related;
	}
}
