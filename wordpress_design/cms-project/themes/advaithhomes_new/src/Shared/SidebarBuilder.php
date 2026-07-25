<?php
namespace Adn\Theme\Shared;

defined( 'ABSPATH' ) || exit;

/**
 * SidebarBuilder - Shared sidebar widget builders.
 *
 * Extracted from per-feature Context classes to eliminate duplication.
 * Each method returns a single sidebar widget array that contexts
 * compose into their page-specific sidebar structure.
 */
class SidebarBuilder {

	// ── Expert Help CTA ─────────────────────────────────────────
	// Used by: TopicCategory, ToolSingle, GuidesListing, Category
	public static function expertHelp(): array {
		$opt = get_option( 'adn_calculators_page', array() );
		return array(
			'heading'  => ! empty( $opt['sidebar_help_title'] )     ? $opt['sidebar_help_title']     : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' ),
			'subtitle' => ! empty( $opt['sidebar_help_text'] )      ? $opt['sidebar_help_text']      : adn_term( 'sidebar.expert_help_subtitle', 'Get personalised guidance from our experts.' ),
			'cta'      => array(
				'label' => ! empty( $opt['sidebar_help_btn_label'] ) ? $opt['sidebar_help_btn_label'] : adn_term( 'sidebar.expert_help_cta', 'Talk to an Expert' ),
				'url'   => ! empty( $opt['sidebar_help_btn_url'] )   ? $opt['sidebar_help_btn_url']   : home_url( SITE_CONTACT_URL ),
			),
		);
	}

	// ── Contact Help CTA ────────────────────────────────────────
	// Used by: AskExpert
	public static function contactHelp(): array {
		return array(
			'heading'      => adn_term( 'sidebar.contact_for_help_heading', 'Contact for Help' ),
			'desc'         => adn_term( 'sidebar.contact_for_help_desc', "Not sure which expert to choose? Get in touch and we'll guide you." ),
			'button_label' => adn_term( 'sidebar.contact_for_help_btn', 'Get in Touch' ),
			'button_url'   => SITE_CONTACT_URL,
		);
	}

	// ── Newsletter CTA ──────────────────────────────────────────
	// Used by: AskExpert
	public static function newsletterCta(): array {
		return NewsletterBuilder::sidebarCta();
	}

	// ── Browse Topics (guide parent terms) ──────────────────────
	// Used by: News, GuidesListing
	public static function browseTopics( int $limit = 12 ): array {
		$topics = array();
		if ( ! function_exists( 'adn_cms_guide_parents' ) ) {
			return $topics;
		}
		foreach ( adn_cms_guide_parents( $limit ) as $parent ) {
			$pslug = $parent->slug ?? '';
			$pname = $parent->name ?? ucwords( str_replace( '-', ' ', $pslug ) );
			if ( '' === $pslug ) { continue; }
			$topics[] = array(
				'label' => $pname,
				'url'   => home_url( '/' . $pslug . '/' ),
			);
		}
		return $topics;
	}

	// ── Latest News (from newsbar items) ────────────────────────
	// Used by: AskExpert, TopicCategory
	public static function latestNews( int $limit = 3 ): array {
		$news_items = array();
		if ( ! function_exists( 'adn_cms_newsbar_items' ) ) {
			return $news_items;
		}
		foreach ( adn_cms_newsbar_items( $limit ) as $np ) {
			if ( empty( $np->text ) ) { continue; }
			$_stamp       = ! empty( $np->start_date ) ? $np->start_date : ( $np->created_at ?? '' );
			$news_items[] = array(
				'title'       => (string) $np->text,
				'description' => ! empty( $np->content ) ? wp_strip_all_tags( (string) $np->content ) : '',
				'date'        => $_stamp ? date_i18n( 'M j, Y', strtotime( $_stamp ) ) : '',
				'tag'         => 'NEWS',
				'gradient'    => function_exists( 'adn_cms_gradient' ) ? adn_cms_gradient( count( $news_items ) ) : '',
				'url'         => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $np->id, $np->slug ?? '' ) : '',
			);
		}
		return $news_items;
	}

	// ── Latest News (with heading + view_all) ───────────────────
	// Used by: AskExpert sidebar
	public static function latestNewsWidget( int $limit = 3 ): array {
		return array(
			'heading'  => SITE_LABEL_LATEST_NEWS,
			'items'    => self::latestNews( $limit ),
			'view_all' => array( 'label' => CONTENT_VIEW_ALL_NEWS, 'url' => SITE_NEWS_URL ),
		);
	}

	// ── Sidebar Recent News (compact, for sidebar_link_list) ────
	// Used by: News
	public static function sidebarRecentNews( int $limit = 5 ): array {
		$news = array();
		if ( ! function_exists( 'adn_cms_newsbar_items' ) ) {
			return $news;
		}
		foreach ( adn_cms_newsbar_items( $limit ) as $sni ) {
			$sn_label = $sni->text ?? '';
			if ( '' === $sn_label ) { continue; }
			$sn_thumb = '';
			if ( ! empty( $sni->image_id ) ) {
				$t = wp_get_attachment_image_url( (int) $sni->image_id, 'thumbnail' );
				$sn_thumb = $t ? (string) $t : '';
			}
			$sn_stamp = ! empty( $sni->start_date ) ? $sni->start_date : ( $sni->created_at ?? '' );
			$news[]   = array(
				'label'     => $sn_label,
				'url'       => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $sni->id, $sni->slug ?? '' ) : '',
				'thumbnail' => $sn_thumb,
				'icon'      => $sn_thumb ? '' : 'fa-newspaper',
				'meta'      => $sn_stamp ? date_i18n( 'M j, Y', strtotime( $sn_stamp ) ) : '',
			);
		}
		return $news;
	}

	// ── Calculator Tools (sidebar) ──────────────────────────────
	// Used by: AskExpert
	public static function calculatorTools( int $limit = 4 ): array {
		$calc_items = array();
		if ( ! function_exists( 'adn_calculators' ) ) {
			return $calc_items;
		}
		$_tools_page = get_permalink( get_page_by_path( trim( SITE_TOOLS_URL, '/' ) ) ) ?: home_url( SITE_TOOLS_URL );
		$_ci         = 0;
		foreach ( adn_calculators() as $_ck => $_calc ) {
			if ( $_ci >= $limit ) { break; }
			$calc_items[] = array(
				'icon'  => isset( $_calc['icon'] )  ? (string) $_calc['icon']  : adn_term( 'icons.tools', '🧮' ),
				'label' => isset( $_calc['title'] ) ? (string) $_calc['title'] : $_ck,
				'url'   => $_tools_page,
			);
			$_ci++;
		}
		return array(
			'heading' => SITE_TOOLS_PLURAL,
			'items'   => $calc_items,
			'cta'     => array(
				'label' => CONTENT_VIEW_ALL_TOOLS,
				'url'   => SITE_CALCULATORS_URL,
			),
		);
	}

	// ── Guide Topics (sidebar with icon + count) ────────────────
	// Used by: AskExpert
	public static function guideTopics( int $limit = 6 ): array {
		$topic_items = array();
		if ( function_exists( 'adn_cms_guide_parents' ) ) {
			foreach ( (array) adn_cms_guide_parents( $limit ) as $_parent ) {
				$_slug = isset( $_parent->slug ) ? (string) $_parent->slug : '';
				$_name = isset( $_parent->name ) ? (string) $_parent->name : '';
				if ( '' === $_slug || '' === $_name ) { continue; }
				$topic_items[] = array(
					'icon'  => ( isset( $_parent->icon ) && '' !== (string) $_parent->icon ) ? (string) $_parent->icon : adn_term( 'icons.guide_parent', '📚' ),
					'label' => $_name,
					'url'   => '/' . $_slug . '/',
					'count' => 0,
				);
			}
		}
		return array(
			'heading' => adn_term( 'sidebar.browse_guides', 'Browse Guides' ),
			'items'   => $topic_items,
		);
	}

	// ── Full sidebar (AskExpert page) ───────────────────────────
	public static function askExpertSidebar(): array {
		return array(
			'contact_help'   => self::contactHelp(),
			'latest_news'    => self::latestNewsWidget( 3 ),
			'tools'          => self::calculatorTools( 4 ),
			'guide_topics'   => self::guideTopics( 6 ),
			'newsletter_cta' => self::newsletterCta(),
		);
	}

	// ── Full sidebar (News page) ────────────────────────────────
	public static function newsSidebar(): array {
		return array(
			'topics'       => self::browseTopics( 12 ),
			'recent_news'  => self::sidebarRecentNews( 5 ),
		);
	}
}
