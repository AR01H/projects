<?php

namespace Adn\Theme\Feature\News\Controller;

defined( 'ABSPATH' ) || exit;

class NewsController {

	public static function getContext(): array {
		$cache_key = 'page_news_context';
		if ( \class_exists( 'ADN_Cache' ) ) {
			$cached = \ADN_Cache::get( $cache_key, 'pages' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$data   = \function_exists( '\adn_service_news_data' )   ? \adn_service_news_data()   : array();
		$chrome = \function_exists( '\adn_service_site_chrome' ) ? \adn_service_site_chrome() : array();

		$ctx = array(
			'meta'              => isset( $data['meta'] )              ? (array) $data['meta']              : array(),
			'breadcrumb'        => isset( $data['breadcrumb'] )        ? (array) $data['breadcrumb']        : array(),
			'hero'              => isset( $data['hero'] )              ? (array) $data['hero']              : array(),
			'categories'        => array( array( 'key' => 'all', 'label' => sprintf( SITE_LABEL_ALL_PREFIX, SITE_NEWS_NOUN ), 'count' => '' ) ),
			'featured'          => array(),
			'sections'          => array(),
			'sidebar'           => isset( $data['sidebar'] )           ? (array) $data['sidebar']           : array(),
			'bottom_newsletter' => isset( $data['bottom_newsletter'] ) ? (array) $data['bottom_newsletter'] : array(),
			'chrome'            => $chrome,
		);

		if ( \function_exists( '\adn_cms_newsbar_items' ) ) {
			$nb_rows = \adn_cms_newsbar_items( 100 );
			if ( ! empty( $nb_rows ) ) {
				$ctx['featured'] = self::newsbarFeatured( $nb_rows[0] );
				$nb_rest = array_slice( $nb_rows, 1 );
				if ( ! empty( $nb_rest ) ) {
					$ctx['sections'][] = array(
						'type'       => 'grid',
						'heading'    => sprintf( SITE_LABEL_ALL_PREFIX, SITE_NEWS_NOUN ),
						'link_label' => '',
						'link_url'   => '',
						'items'      => self::newsbarGridItems( $nb_rest ),
					);
				}

				$_seen_labels = array();
				foreach ( $nb_rows as $_li ) {
					$_lbl = isset( $_li->label ) ? trim( (string) $_li->label ) : '';
					if ( '' === $_lbl ) {
						continue;
					}
					$_lkey = sanitize_key( $_lbl );
					if ( isset( $_seen_labels[ $_lkey ] ) ) {
						$_seen_labels[ $_lkey ]['count']++;
					} else {
						$_seen_labels[ $_lkey ] = array( 'label' => $_lbl, 'count' => 1 );
					}
				}
				arsort( $_seen_labels );
				foreach ( $_seen_labels as $_lkey => $_ldata ) {
					$ctx['categories'][] = array(
						'key'   => $_lkey,
						'label' => $_ldata['label'],
						'count' => $_ldata['count'],
					);
				}
			}
		}

		$sidebar_topics = array();
		if ( \function_exists( '\adn_cms_guide_parents' ) ) {
			foreach ( \adn_cms_guide_parents( 12 ) as $parent ) {
				$pslug = isset( $parent->slug ) ? (string) $parent->slug : '';
				$pname = isset( $parent->name ) ? (string) $parent->name : ucwords( str_replace( '-', ' ', $pslug ) );
				if ( '' === $pslug ) {
					continue;
				}
				$sidebar_topics[] = array(
					'label' => $pname,
					'url'   => \home_url( '/' . $pslug . '/' ),
				);
			}
		}

		$sidebar_news = array();
		if ( \function_exists( '\adn_cms_newsbar_items' ) ) {
			foreach ( \adn_cms_newsbar_items( 5 ) as $sni ) {
				$sn_label = isset( $sni->text ) ? (string) $sni->text : '';
				if ( '' === $sn_label ) {
					continue;
				}
				$sn_thumb = '';
				if ( ! empty( $sni->image_id ) ) {
					$t = \wp_get_attachment_image_url( (int) $sni->image_id, 'thumbnail' );
					$sn_thumb = $t ? (string) $t : '';
				}
				$sn_stamp = ! empty( $sni->start_date ) ? $sni->start_date : ( isset( $sni->created_at ) ? $sni->created_at : '' );
				$sidebar_news[] = array(
					'label'     => $sn_label,
					'url'       => \function_exists( '\adn_newsbar_item_url' ) ? \adn_newsbar_item_url( $sni->id, isset( $sni->slug ) ? (string) $sni->slug : '' ) : '',
					'thumbnail' => $sn_thumb,
					'icon'      => $sn_thumb ? '' : 'fa-newspaper',
					'meta'      => $sn_stamp ? date_i18n( 'M j, Y', strtotime( $sn_stamp ) ) : '',
				);
			}
		}

		$ctx['sidebar']['topics']       = $sidebar_topics;
		$ctx['sidebar']['recent_news'] = $sidebar_news;

		if ( \class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $cache_key, $ctx, 'pages', \get_option( 'ah_cache_expiry', 3600 ) );
		}
		return $ctx;
	}

	private static function newsbarFeatured( $item ): array {
		$content = isset( $item->content ) ? (string) $item->content : '';
		$excerpt = wp_trim_words( wp_strip_all_tags( $content ), 30, '…' );
		$stamp   = ! empty( $item->start_date ) ? $item->start_date : ( isset( $item->created_at ) ? $item->created_at : '' );
		return array(
			'bg_icon'   => 'fa-newspaper',
			'label'     => SITE_LABEL_FEATURED . ' ' . SITE_NEWS_NOUN,
			'tag'       => SITE_NEWS_NOUN,
			'title'     => isset( $item->text ) ? (string) $item->text : '',
			'excerpt'   => $excerpt,
			'date'      => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
			'read_time' => \function_exists( '\adn_cms_read_time' ) ? \adn_cms_read_time( $content ) : '',
			'url'       => \function_exists( '\adn_newsbar_item_url' ) ? \adn_newsbar_item_url( $item->id, isset( $item->slug ) ? (string) $item->slug : '' ) : '',
			'thumbnail' => self::itemThumb( isset( $item->image_id ) ? $item->image_id : 0 ),
		);
	}

	private static function newsbarGridItems( $rows ): array {
		$items = array();
		foreach ( $rows as $item ) {
			$title = isset( $item->text ) ? (string) $item->text : '';
			if ( '' === $title ) {
				continue;
			}
			$content = isset( $item->content ) ? (string) $item->content : '';
			$excerpt = wp_trim_words( wp_strip_all_tags( $content ), 25, '…' );
			$stamp   = ! empty( $item->start_date ) ? $item->start_date : ( isset( $item->created_at ) ? $item->created_at : '' );
			$item_label = isset( $item->label ) && '' !== trim( (string) $item->label ) ? trim( (string) $item->label ) : SITE_NEWS_NOUN;
			$item_key   = sanitize_key( $item_label );
			$items[] = array(
				'cat_key'    => $item_key,
				'icon'       => 'fa-newspaper',
				'bg_class'   => '',
				'pill_class' => 'pill-news-label',
				'category'   => $item_label,
				'title'      => $title,
				'excerpt'    => $excerpt,
				'date'       => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'read_time'  => \function_exists( '\adn_cms_read_time' ) ? \adn_cms_read_time( $content ) : '',
				'url'        => \function_exists( '\adn_newsbar_item_url' ) ? \adn_newsbar_item_url( $item->id, isset( $item->slug ) ? (string) $item->slug : '' ) : '',
				'thumbnail'  => self::itemThumb( isset( $item->image_id ) ? $item->image_id : 0 ),
			);
		}
		return $items;
	}

	private static function itemThumb( $image_id, $size = 'medium' ): string {
		if ( empty( $image_id ) ) {
			return '';
		}
		$t = \wp_get_attachment_image_url( (int) $image_id, $size );
		return $t ? (string) $t : '';
	}
}
