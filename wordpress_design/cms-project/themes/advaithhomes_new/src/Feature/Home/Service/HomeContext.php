<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * HomeContext - Builds the full context array for the home page.
 *
 * Big getContext() is split into small focused methods for reusability.
 */
class HomeContext {

	// ── Repository ──────────────────────────────────────────────
	public static function repository() {
		static $repo = null;
		if ( null === $repo ) {
			$repo = new \Adn\Theme\Repository\HomeRepository();
		}
		return $repo;
	}

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

	// ── Data helper ─────────────────────────────────────────────
	private static function sectionData( array $data, string $key, array $defaults = array() ): array {
		$value = $data[ $key ] ?? array();
		return array_merge( $defaults, is_array( $value ) ? $value : array() );
	}

	// ── Hero section ────────────────────────────────────────────
	public static function buildHero( array $data ): array {
		$hero = self::sectionData( $data, 'hero', array(
			'title_lines' => array(),
			'actions'     => array(),
			'trust_items' => array(),
			'diagram'     => array(),
		) );

		// Apply overrides from options
		$hero_opt = get_option( 'adn_home_hero' );
		if ( is_array( $hero_opt ) ) {
			$hero = self::applyHeroOverrides( $hero, $hero_opt );
		}

		// Apply trust items from marquee settings
		$_hs = get_option( 'adn_home_sections', array() );
		$_mq = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $_hs ) : null;
		if ( $_mq ) {
			$hero['trust_items'] = $_mq['trust'] ?? array();
		}

		// Apply media overrides
		if ( ! empty( $_hs['home_banner'] ) && function_exists( 'adn_settings_media_url_type' ) ) {
			$_desktop_media = adn_settings_media_url_type( $_hs['home_banner'] );
			if ( '' !== $_desktop_media['url'] ) {
				$hero['image'] = $_desktop_media['url'];
				$hero['media'] = $_desktop_media;
				$hero['media_mobile'] = null;
				if ( ! empty( $_hs['home_banner_mobile'] ) ) {
					$_mobile_media = adn_settings_media_url_type( $_hs['home_banner_mobile'] );
					if ( '' !== $_mobile_media['url'] ) {
						$hero['media_mobile'] = $_mobile_media;
					}
				}

				// Collect additional banners from repeater
				$_extra = isset( $_hs['hero_banners'] ) && is_array( $_hs['hero_banners'] ) ? $_hs['hero_banners'] : array();
				if ( ! empty( $_extra ) ) {
					$slides = array(
						array( 'image' => $hero['image'], 'media' => $hero['media'], 'media_mobile' => $hero['media_mobile'] ),
					);
					foreach ( $_extra as $_banner ) {
						if ( empty( $_banner['image'] ) ) { continue; }
						$_d = adn_settings_media_url_type( (string) $_banner['image'] );
						if ( '' === $_d['url'] ) { continue; }
						$_m = null;
						if ( ! empty( $_banner['image_mobile'] ) ) {
							$_m = adn_settings_media_url_type( (string) $_banner['image_mobile'] );
							if ( '' === $_m['url'] ) { $_m = null; }
						}
						$slides[] = array( 'image' => $_d['url'], 'media' => $_d, 'media_mobile' => $_m );
					}
					if ( count( $slides ) > 1 ) {
						$hero['slides'] = $slides;
					}
				}
			}
		}

		return $hero;
	}

	// ── Journey section ─────────────────────────────────────────
	public static function buildJourney( array $data ): array {
		$journey = self::sectionData( $data, 'journey', array(
			'heading' => array(),
			'cards'   => array(),
		) );

		// Add CMS journey cards
		if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
			$cards = self::cmsJourneyCards();
			if ( ! empty( $cards ) ) {
				$journey['cards'] = array_merge( $cards, $journey['cards'] );
			}
		}

		// Apply image overrides
		$_jni = get_option( 'adn_journey_json_images', array() );
		if ( ! empty( $_jni ) && is_array( $_jni ) ) {
			foreach ( $journey['cards'] as &$_jcard ) {
				$_jcard_title = $_jcard['title'] ?? '';
				if ( '' === $_jcard_title ) { continue; }
				$_jkey = sanitize_key( sanitize_title( $_jcard_title ) );
				$_old_jkey = sanitize_key( sanitize_title( trim( $_jcard['url'] ?? '', '/' ) ) );
				$_img_id = $_jni[ $_jkey ] ?? ( $_jni[ $_old_jkey ] ?? 0 );
				if ( $_img_id > 0 ) {
					$_jimg = wp_get_attachment_image_url( (int) $_img_id, 'large' );
					if ( $_jimg ) { $_jcard['image'] = $_jimg; }
				}
			}
			unset( $_jcard );
		}

		return $journey;
	}

	// ── Banners section ─────────────────────────────────────────
	public static function buildBanners( array $data, array $skip ): array {
		return array(
			'heading' => self::sectionData( $data, 'banners', array( 'heading' => array() ) )['heading'],
			'items'   => ( ! in_array( 'banners', $skip, true ) && class_exists( 'AH_Banners_Helper' ) )
				? \AH_Banners_Helper::get_all( true )
				: array(),
		);
	}

	// ── News section ────────────────────────────────────────────
	public static function buildNews( array $data, array $skip ): array {
		$news = self::sectionData( $data, 'news', array( 'heading' => array(), 'items' => array() ) );
		if ( ! in_array( 'news', $skip, true ) && function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
			$news['items'] = self::cmsNewsItems();
		}
		return $news;
	}

	// ── Regulations section ─────────────────────────────────────
	public static function buildRegulations( array $data, array $skip ): array {
		$regs = self::sectionData( $data, 'regulations', array( 'heading' => array(), 'items' => array() ) );
		if ( ! in_array( 'news', $skip, true ) ) {
			$items = self::cmsRegulationsItems();
			if ( ! empty( $items ) ) { $regs['items'] = $items; }
		}
		return $regs;
	}

	// ── Hot topics section ──────────────────────────────────────
	public static function buildHotTopics( array $data, array $skip ): array {
		$ht = self::sectionData( $data, 'hot_topics', array( 'title' => '', 'items' => array(), 'cta' => array() ) );
		if ( ! in_array( 'news', $skip, true ) ) {
			$items = self::cmsHotTopicsItems();
			if ( ! empty( $items ) ) { $ht['items'] = $items; }
		}
		return $ht;
	}

	// ── Tools section ───────────────────────────────────────────
	public static function buildTools( array $data, array $skip ): array {
		$tools = self::sectionData( $data, 'tools', array( 'heading' => array(), 'items' => array() ) );
		if ( ! in_array( 'tools', $skip, true ) && function_exists( 'adn_calculators' ) ) {
			$tools['items'] = self::cmsToolItems();
		}
		return $tools;
	}

	// ── Guides section ──────────────────────────────────────────
	public static function buildGuides( array $data, array $skip ): array {
		$guides = self::sectionData( $data, 'guides', array( 'heading' => array(), 'items' => array() ) );
		if ( ! in_array( 'guides', $skip, true ) && function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
			$guides['items'] = self::cmsGuideItems();
		}
		return $guides;
	}

	// ── Newsletter section ──────────────────────────────────────
	public static function buildNewsletter( array $data ): array {
		return self::sectionData( $data, 'newsletter' );
	}

	// ── Main getContext ─────────────────────────────────────────
	public static function getContext( $skip = array() ) {
		$skip = is_array( $skip ) ? $skip : array();
		$cache_key = 'home_context_' . md5( wp_json_encode( $skip ) );

		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$data   = function_exists( 'adn_service_home_data' ) ? adn_service_home_data() : array();
		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$ctx = array(
			'chrome'      => is_array( $chrome ) ? $chrome : array(),
			'hero'        => self::buildHero( $data ),
			'journey'     => self::buildJourney( $data ),
			'banners'     => self::buildBanners( $data, $skip ),
			'news'        => self::buildNews( $data, $skip ),
			'regulations' => self::buildRegulations( $data, $skip ),
			'hot_topics'  => self::buildHotTopics( $data, $skip ),
			'tools'       => self::buildTools( $data, $skip ),
			'guides'      => self::buildGuides( $data, $skip ),
			'newsletter'  => self::buildNewsletter( $data ),
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}

	// ── Fragment context (lazy-loaded sections) ──────────────────
	public static function getFragmentContext( $section ) {
		static $cache = array();
		$section = sanitize_key( (string) $section );
		if ( isset( $cache[ $section ] ) ) {
			return $cache[ $section ];
		}

		$data = function_exists( 'adn_service_home_data' ) ? adn_service_home_data() : array();
		$ctx  = array();

		switch ( $section ) {
			case 'banners':
				$ctx['banners'] = self::buildBanners( $data, array() );
				break;
			case 'news_row':
				$ctx['news']        = self::buildNews( $data, array() );
				$ctx['regulations'] = self::buildRegulations( $data, array() );
				$ctx['hot_topics']  = self::buildHotTopics( $data, array() );
				break;
			case 'tools':
				$ctx['tools'] = self::buildTools( $data, array() );
				break;
			case 'guides':
				$ctx['guides'] = self::buildGuides( $data, array() );
				break;
			case 'resources':
				$ctx = array();
				break;
		}

		$cache[ $section ] = $ctx;
		return $ctx;
	}

	// ── Section visibility check ────────────────────────────────
	public static function sectionVisible( $key ) {
		$sections = get_option( 'adn_home_sections' );
		if ( ! is_array( $sections ) ) {
			return true;
		}
		return ! array_key_exists( $key, $sections ) || ! empty( $sections[ $key ] );
	}

	// ── Hero overrides from options ─────────────────────────────
	public static function applyHeroOverrides( $hero, $opt ) {
		$lines = array();
		if ( ! empty( $opt['heading_1'] ) ) {
			$lines[] = array( 'text' => $opt['heading_1'], 'accent' => false );
		}
		if ( ! empty( $opt['heading_accent'] ) ) {
			$lines[] = array( 'text' => $opt['heading_accent'], 'accent' => true );
		}
		if ( ! empty( $opt['heading_3'] ) ) {
			$lines[] = array( 'text' => $opt['heading_3'], 'accent' => false );
		}
		if ( ! empty( $lines ) ) { $hero['title_lines'] = $lines; }
		if ( ! empty( $opt['description'] ) ) { $hero['description'] = $opt['description']; }

		$actions = array();
		if ( ! empty( $opt['cta1_label'] ) ) {
			$actions[] = array( 'label' => $opt['cta1_label'], 'url' => $opt['cta1_url'] ?? '#', 'style' => 'primary' );
		}
		if ( ! empty( $opt['cta2_label'] ) ) {
			$actions[] = array( 'label' => $opt['cta2_label'], 'url' => $opt['cta2_url'] ?? '#', 'style' => 'outline' );
		}
		if ( ! empty( $actions ) ) { $hero['actions'] = $actions; }

		// Diagram overrides
		$diagram = $hero['diagram'] ?? array();
		if ( ! empty( $opt['diagram_center_icon'] ) ) {
			$diagram['center_icon'] = sanitize_text_field( wp_unslash( $opt['diagram_center_icon'] ) );
		}
		$center_lines = array();
		if ( ! empty( $opt['diagram_center_line1'] ) ) {
			$center_lines[] = sanitize_text_field( wp_unslash( $opt['diagram_center_line1'] ) );
		}
		if ( ! empty( $opt['diagram_center_line2'] ) ) {
			$center_lines[] = sanitize_text_field( wp_unslash( $opt['diagram_center_line2'] ) );
		}
		if ( ! empty( $center_lines ) ) { $diagram['center_lines'] = $center_lines; }

		if ( ! empty( $opt['diagram_nodes'] ) ) {
			$nodes = array();
			foreach ( explode( "\n", wp_unslash( $opt['diagram_nodes'] ) ) as $line ) {
				if ( count( $nodes ) >= 8 ) { break; }
				$line = trim( $line );
				if ( '' === $line ) { continue; }
				$parts   = explode( '|', $line, 2 );
				$nodes[] = array(
					'icon'  => sanitize_text_field( $parts[0] ?? '' ),
					'label' => sanitize_text_field( $parts[1] ?? '' ),
				);
			}
			if ( ! empty( $nodes ) ) { $diagram['nodes'] = $nodes; }
		}
		$hero['diagram'] = $diagram;

		return $hero;
	}

	// ── CMS journey cards ───────────────────────────────────────
	public static function cmsJourneyCards() {
		$cards     = array();
		$overrides = get_option( 'adn_journey_card_images', array() );
		if ( ! is_array( $overrides ) ) { $overrides = array(); }
		if ( ! function_exists( 'adn_cms_guide_parents' ) ) { return $cards; }
		foreach ( adn_cms_guide_parents() as $i => $term ) {
			$name = $term->name ?? '';
			if ( '' === $name ) { continue; }
			$tid         = (int) $term->id;
			$override_id = $overrides[ $tid ] ?? 0;
			$image_id    = $override_id ?: ( ! empty( $term->image_id ) ? (int) $term->image_id : 0 );
			$image_url   = $image_id ? ( wp_get_attachment_image_url( $image_id, 'large' ) ?: '' ) : '';
			$cards[] = array(
				'image'       => $image_url,
				'icon'        => ! empty( $term->icon_emoji ) ? $term->icon_emoji : adn_term( 'icons.guide_fallback', '🏡' ),
				'gradient'    => adn_cms_gradient( $i ),
				'title'       => $name,
				'description' => (string) ( $term->description ?? '' ),
				'link_label'  => adn_term( 'buttons.explore', 'Explore' ),
				'url'         => adn_cms_term_url( $term ),
			);
		}
		return $cards;
	}

	// ── CMS guide items ─────────────────────────────────────────
	public static function cmsGuideItems() {
		$featured  = get_option( 'adn_home_featured', array() );
		$count     = ( $featured['count'] ?? 10 ) > 0 ? (int) $featured['count'] : 10;
		$topic_ids = is_array( $featured['topics'] ?? null ) ? array_map( 'intval', $featured['topics'] ) : array();

		$items = array();
		foreach ( adn_cms_guides_by_category( $count, $topic_ids ) as $i => $post ) {
			$cat_name = $post->category_name ?? '';
			if ( '' === $cat_name ) { continue; }
			$_term_img_url = '';
			if ( ! empty( $post->term_image_id ) ) {
				$_tiu = wp_get_attachment_image_url( (int) $post->term_image_id, 'medium' );
				$_term_img_url = $_tiu ? (string) $_tiu : '';
			}
			$items[] = array(
				'icon'        => $post->term_icon ?? ( $post->parent_icon ?? adn_term( 'icons.guide_parent', '📚' ) ),
				'gradient'    => adn_cms_gradient( $i ),
				'image'       => $_term_img_url,
				'parent_name' => (string) ( $post->parent_name ?? '' ),
				'category'    => (string) ( $post->parent_name ?? '' ),
				'title'       => $cat_name,
				'description' => (string) ( $post->_term_desc ?? '' ),
				'read_more'   => adn_term( 'content.read_more', 'Explore' ),
				'url'         => home_url( '/' . trim( (string) $post->_term_slug, '/' ) . '/' ),
			);
		}
		return $items;
	}

	// ── CMS news items ──────────────────────────────────────────
	public static function cmsNewsItems() {
		$items = array();
		if ( ! function_exists( 'adn_cms_newsbar_items' ) ) { return $items; }
		foreach ( adn_cms_newsbar_items( 5 ) as $i => $item ) {
			$title = $item->text ?? '';
			if ( '' === $title ) { continue; }
			$stamp = $item->created_at ?? '';
			$items[] = array(
				'title'       => $title,
				'description' => wp_strip_all_tags( (string) ( $item->content ?? '' ) ),
				'date'        => $stamp ? date_i18n( 'M jS', strtotime( $stamp ) ) : '',
				'date_full'   => $stamp ? date_i18n( 'M jS, Y', strtotime( $stamp ) ) : '',
				'tag'         => (string) ( $item->label ?? '' ),
				'gradient'    => adn_cms_gradient( $i ),
				'thumbnail'   => ! empty( $item->image_id ) ? ( wp_get_attachment_image_url( (int) $item->image_id, 'thumbnail' ) ?: '' ) : '',
				'url'         => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $item->id, $item->slug ?? '' ) : '#',
			);
		}
		return $items;
	}

	// ── CMS regulations items ───────────────────────────────────
	public static function cmsRegulationsItems() {
		$opt = get_option( 'adn_home_newsblocks', array() );
		$raw = $opt['regulations']['items'] ?? array();
		if ( empty( $raw ) ) { return array(); }

		$pids = array();
		$meta = array();
		foreach ( $raw as $row ) {
			$pid = (int) ( $row['post_id'] ?? 0 );
			if ( $pid ) { $pids[ $pid ] = $row; }
		}
		if ( empty( $pids ) ) { return array(); }

		$query = new \WP_Query( array(
			'post_type'      => 'post',
			'post__in'       => array_keys( $pids ),
			'posts_per_page' => count( $pids ),
			'orderby'        => 'post__in',
		) );

		$items = array();
		foreach ( $query->posts as $post ) {
			$pid    = (int) $post->ID;
			$row    = $pids[ $pid ] ?? array();
			$_thumb = get_the_post_thumbnail_url( $pid, 'medium' ) ?: '';
			$_stamp = ! empty( $post->post_date ) ? $post->post_date : '';
			$_desc  = ! empty( $post->post_excerpt ) ? wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 15 );
			$items[] = array(
				'title'       => get_the_title( $pid ),
				'description' => $_desc,
				'thumbnail'   => $_thumb,
				'url'         => get_permalink( $pid ),
				'date'        => $_stamp ? date_i18n( 'M j, Y', strtotime( $_stamp ) ) : '',
				'overlay'     => ! empty( $row['badge'] ) ? (string) $row['badge'] : '',
				'badge_lines' => ! empty( $row['badge_lines'] ) ? (array) $row['badge_lines'] : array(),
				'icon'        => $row['icon'] ?? '📰',
				'badge'       => $row['badge'] ?? '',
				'gradient'    => $row['gradient'] ?? '',
			);
		}
		return $items;
	}

	// ── CMS hot topics items ────────────────────────────────────
	public static function cmsHotTopicsItems() {
		$opt = get_option( 'adn_home_newsblocks', array() );
		$raw = $opt['hot_topics']['items'] ?? array();
		if ( empty( $raw ) ) { return array(); }

		$pids = array();
		$meta = array();
		foreach ( $raw as $row ) {
			$pid = (int) ( $row['post_id'] ?? 0 );
			if ( $pid ) { $pids[ $pid ] = $row; }
		}
		if ( empty( $pids ) ) { return array(); }

		$query = new \WP_Query( array(
			'post_type'      => 'post',
			'post__in'       => array_keys( $pids ),
			'posts_per_page' => count( $pids ),
			'orderby'        => 'post__in',
		) );

		$items = array();
		foreach ( $query->posts as $post ) {
			$pid = (int) $post->ID;
			$row = $pids[ $pid ] ?? array();
			$_thumb = get_the_post_thumbnail_url( $pid, 'medium' ) ?: '';
			$_desc  = ! empty( $post->post_excerpt ) ? wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 15 );
			$_icon  = ! empty( $row['icon'] ) ? \sanitize_text_field( $row['icon'] ) : '🔥';
			$items[] = array(
				'title'       => get_the_title( $pid ),
				'description' => $_desc,
				'thumbnail'   => $_thumb,
				'url'         => get_permalink( $pid ),
				'icon'        => $_icon,
				'gradient'    => $row['gradient'] ?? '',
				'badge'       => $row['badge'] ?? '',
			);
		}
		return $items;
	}

	// ── CMS tool items ──────────────────────────────────────────
	public static function cmsToolItems() {
		$registry  = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
		$meta_all  = get_option( 'adn_calculators_meta', array() );
		$items     = array();
		foreach ( $registry as $key => $calc ) {
			$meta = $meta_all[ $key ] ?? array();
			if ( ! empty( $meta['hidden_from_listing'] ) || empty( $meta['is_popular'] ) ) { continue; }
			$_thumb = '';
			if ( ! empty( $meta['thumbnail_id'] ) ) {
				$_t = wp_get_attachment_image_url( (int) $meta['thumbnail_id'], 'thumbnail' );
				$_thumb = $_t ? (string) $_t : '';
			}
			$items[] = array(
				'icon'      => ! empty( $calc['icon'] ) ? (string) $calc['icon'] : adn_term( 'icons.tools', '🧮' ),
				'title'     => $calc['title'] ?? '',
				'url'       => ! empty( $meta['card_url'] ) ? (string) $meta['card_url'] : adn_calc_page_url( $key ),
				'thumbnail' => $_thumb,
				'highlight' => $meta['highlight'] ?? '',
				'desc'      => $meta['desc'] ?? '',
			);
		}
		return $items;
	}
}
