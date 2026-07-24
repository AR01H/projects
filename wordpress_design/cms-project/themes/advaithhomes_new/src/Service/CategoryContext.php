<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class CategoryContext {

	public static function repository() {
		static $repo = null;
		if ( null === $repo ) {
			$repo = new \Adn\Theme\Repository\CategoryRepository();
		}
		return $repo;
	}

	public static function cmsGuides( $slug ) {
		if ( ! function_exists( 'adn_cms_guides_by_category' ) ) {
			return array();
		}

		$repo = self::repository();

		$topic_ids = array();
		if ( $slug !== '' && function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
			$parent = $repo->get_parent_term_by_slug( $slug );

			if ( $parent && function_exists( 'adn_cms_topics' ) ) {
				$children = adn_cms_topics( (int) $parent->id, 50 );
				foreach ( (array) $children as $child ) {
					if ( ! empty( $child->id ) ) {
						$topic_ids[] = (int) $child->id;
					}
				}
			}

			if ( empty( $topic_ids ) && $parent ) {
				$topic_ids = $repo->get_child_topic_ids( (int) $parent->id );
			}

			if ( ! empty( $parent ) && empty( $topic_ids ) ) {
				return array();
			}
		}

		$rows  = adn_cms_guides_by_category( 1200, $topic_ids );
		$items = array();
		foreach ( $rows as $i => $post ) {
			$cat_name = isset( $post->category_name ) ? (string) $post->category_name : '';
			if ( '' === $cat_name ) {
				continue;
			}
			$term_url = home_url( '/' . trim( (string) $post->_term_slug, '/' ) . '/' );
			$_cg_img = '';
			if ( ! empty( $post->term_image_id ) ) {
				$_cgu    = wp_get_attachment_image_url( (int) $post->term_image_id, 'medium' );
				$_cg_img = $_cgu ? (string) $_cgu : '';
			}
			$items[] = array(
				'gradient'    => adn_cms_gradient( $i ),
				'image'       => $_cg_img,
				'parent_name' => ! empty( $post->parent_name ) ? $post->parent_name : '',
				'category'    => '',
				'icon'        => ! empty( $post->term_icon ) ? $post->term_icon : ( ! empty( $post->parent_icon ) ? $post->parent_icon : '📚' ),
				'title'       => $cat_name,
				'description' => ! empty( $post->_term_desc ) ? $post->_term_desc : '',
				'read_more'   => SITE_BTN_EXPLORE_ARROW,
				'url'         => $term_url,
			);
		}
		return $items;
	}

	public static function cmsNews( $limit = 3 ) {
		$items = array();

		if ( function_exists( 'adn_cms_newsbar_items' ) ) {
			foreach ( adn_cms_newsbar_items( $limit ) as $i => $item ) {
				$title = isset( $item->text ) ? $item->text : '';
				if ( '' === $title ) {
					continue;
				}
				$stamp     = ! empty( $item->start_date ) ? $item->start_date : '';
				$thumb_url = '';
				if ( ! empty( $item->image_id ) ) {
					$_tu = wp_get_attachment_image_url( (int) $item->image_id, 'thumbnail' );
					$thumb_url = $_tu ? (string) $_tu : '';
				}
				$items[] = array(
					'title'     => $title,
					'date'      => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
					'tag'       => ! empty( $item->label ) ? (string) $item->label : '',
					'thumbnail' => $thumb_url,
					'gradient'  => adn_cms_gradient( $i ),
					'url'       => ! empty( $item->link_url ) ? $item->link_url : SITE_NEWS_URL,
				);
			}
		}

		return $items;
	}

	public static function latestUpdates( $slug, $limit = 4 ) {
		$items = array();

		if ( function_exists( 'adn_cms_articles_for_parent' ) ) {
			$rows = adn_cms_articles_for_parent( $slug, $limit );
			foreach ( (array) $rows as $post ) {
				if ( empty( $post->title ) ) { continue; }
				$_thumb = '';
				if ( ! empty( $post->ID ) ) {
					$_u = get_the_post_thumbnail_url( $post->ID, 'medium' );
					if ( ! $_u ) { $_u = get_the_post_thumbnail_url( $post->ID, 'full' ); }
					if ( $_u ) { $_thumb = (string) $_u; }
				}
				if ( empty( $_thumb ) && ! empty( $post->featured_image_id ) ) {
					$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
					if ( $_u ) { $_thumb = (string) $_u; }
				}
				$_desc = ! empty( $post->excerpt ) ? wp_trim_words( wp_strip_all_tags( $post->excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( isset( $post->content ) ? $post->content : '' ), 15 );
				$items[] = array(
					'thumbnail'   => $_thumb,
					'icon'        => '📋',
					'title'       => (string) $post->title,
					'url'         => function_exists( 'adn_cms_post_url' )  ? adn_cms_post_url( $post )  : '#',
					'description' => $_desc,
				);
			}
		}

		if ( count( $items ) < $limit ) {
			$fallback_limit = $limit - count( $items );
			$wp_posts = get_posts( array(
				'numberposts' => $fallback_limit,
				'post_status' => 'publish',
			) );
			foreach ( $wp_posts as $p ) {
				$_thumb = get_the_post_thumbnail_url( $p->ID, 'medium' );
				if ( ! $_thumb ) { $_thumb = get_the_post_thumbnail_url( $p->ID, 'full' ); }
				$_desc = ! empty( $p->post_excerpt ) ? wp_trim_words( wp_strip_all_tags( $p->post_excerpt ), 15 ) : wp_trim_words( wp_strip_all_tags( $p->post_content ), 15 );
				$items[] = array(
					'thumbnail'   => $_thumb ? (string) $_thumb : '',
					'icon'        => '📋',
					'title'       => get_the_title( $p->ID ),
					'url'         => get_permalink( $p->ID ),
					'description' => $_desc,
				);
			}
		}

		return $items;
	}

	public static function parentTerm( $slug ) {
		return self::repository()->get_parent_term_by_slug( $slug );
	}

	public static function getContext( $slug = '' ) {

		$repo = self::repository();

		if ( '' === $slug ) {
			$qv = (string) get_query_var( 'adn_cat_slug', '' );
			if ( '' !== $qv ) {
				$slug = $qv;
			} else {
				$page = get_queried_object();
				$slug = ( $page instanceof \WP_Post ) ? (string) $page->post_name : '';
			}
		}
		$slug = sanitize_key( $slug );
		$cache_key = 'page_category_context_' . $slug;
		if ( class_exists( 'ADN_Cache' ) ) {
			$cached = \ADN_Cache::get( $cache_key, 'pages' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

		$term = $repo->get_parent_term_by_slug( $slug );
		$name = $term && isset( $term->name )        ? (string) $term->name        : ucwords( str_replace( '-', ' ', $slug ) );
		$desc = $term && isset( $term->description ) ? (string) $term->description : '';
		$icon = $term && isset( $term->icon_emoji )  ? (string) $term->icon_emoji  : '';
		$img  = $term && ! empty( $term->image_id )  ? (int)    $term->image_id    : 0;

		$_cs_all      = class_exists( 'AH_Category_Settings' ) ? \AH_Category_Settings::get_all( $slug ) : array();
		$_cs_app      = isset( $_cs_all['appearance'] ) && is_array( $_cs_all['appearance'] ) ? $_cs_all['appearance'] : array();
		$_cs_thumb_id = ! empty( $_cs_app['thumbnail_id'] ) ? (int) $_cs_app['thumbnail_id'] : 0;

		$_cs_mq     = isset( $_cs_all['marquee'] ) && is_array( $_cs_all['marquee'] ) ? $_cs_all['marquee'] : array();
		$_mq_parsed = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $_cs_mq ) : null;

		if ( ! $_mq_parsed ) {
			$_home_s    = get_option( 'adn_home_sections', array() );
			$_mq_parsed = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $_home_s ) : null;
		}

		$_trust_items = $_mq_parsed
			? $_mq_parsed['trust']
			:[];
		$hero = array(
			'title'       => $name,
			'description' => $desc,
			'image_icon'  => $icon,
			'image_id'    => $_cs_thumb_id ?: $img,
			'trust_items' => $_trust_items,
		);

		$meta = array(
			'slug'             => $slug,
			'page_title'       => $name . ' - ' . SITE_BRAND_NAME,
			'meta_description' => $desc,
		);
		$breadcrumb = array(
			array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
			array( 'label' => $name,           'url' => null ),
		);

		$guides = array(
			'heading' => array(
				'title'      => sprintf( adn_term( 'category_page.explore_guides_title', 'Explore %s' ), adn_term( 'taxonomy.parent_plural', 'Guides' ) ),
				'link_label' => adn_term( 'content.view_all_guides', 'View all →' ),
				'link_url'   => SITE_GUIDES_URL,
			),
			'items' => self::cmsGuides( $slug ),
		);

		$regulations = array(
			'heading' => array(
				'title'      => adn_term( 'category_page.latest_updates_title', 'Latest Updates' ),
				'link_label' => adn_term( 'category_page.latest_updates_view_all', 'View all →' ),
				'link_url'   => SITE_NEWS_URL,
			),
			'items' => self::latestUpdates( $slug, 5 ),
		);

		$_cs_journey  = isset( $_cs_all['journey'] )        && is_array( $_cs_all['journey'] )        ? $_cs_all['journey']        : array();
		$_cs_ht       = isset( $_cs_all['hot_topics'] )     && is_array( $_cs_all['hot_topics'] )     ? $_cs_all['hot_topics']     : array();
		$_cs_pp       = isset( $_cs_all['popular_posts'] )  && is_array( $_cs_all['popular_posts'] )  ? $_cs_all['popular_posts']  : array();
		$_cs_ft       = isset( $_cs_all['featured_topics'] ) && is_array( $_cs_all['featured_topics'] ) ? $_cs_all['featured_topics'] : array();
		$_cs_calc     = isset( $_cs_all['calculators'] )    && is_array( $_cs_all['calculators'] )    ? $_cs_all['calculators']    : array();
		$_cs_sidebar  = isset( $_cs_all['sidebar'] )        && is_array( $_cs_all['sidebar'] )        ? $_cs_all['sidebar']        : array();
		$_cs_cta      = isset( $_cs_all['cta_banner'] )     && is_array( $_cs_all['cta_banner'] )     ? $_cs_all['cta_banner']     : array();
		$_cs_faqs     = isset( $_cs_all['faqs'] )           && is_array( $_cs_all['faqs'] )           ? $_cs_all['faqs']           : array();
		$_cs_sp       = isset( $_cs_all['spotlights'] )     && is_array( $_cs_all['spotlights'] )     ? $_cs_all['spotlights']     : array();

		$journey = array();
		if ( ! empty( $_cs_journey['steps'] ) && is_array( $_cs_journey['steps'] ) ) {
			$steps = array();
			foreach ( $_cs_journey['steps'] as $s ) {
				if ( empty( $s['label'] ) ) { continue; }
				$steps[] = array(
					'icon'   => ! empty( $s['icon'] ) ? (string) $s['icon'] : '',
					'num'    => (string) ( count( $steps ) + 1 ),
					'label'  => (string) $s['label'],
					'desc'   => ! empty( $s['desc'] ) ? (string) $s['desc'] : '',
					'url'    => ! empty( $s['url'] )  ? (string) $s['url']  : '',
					'active' => ( 0 === count( $steps ) ),
				);
			}
			$tip = array();
			if ( ! empty( $_cs_journey['tip_text'] ) ) {
				$tip = array(
					'icon'       => ! empty( $_cs_journey['tip_icon'] )       ? (string) $_cs_journey['tip_icon']       : '💡',
					'text'       => (string) $_cs_journey['tip_text'],
					'link_label' => ! empty( $_cs_journey['tip_link_label'] ) ? (string) $_cs_journey['tip_link_label'] : '',
					'link_url'   => ! empty( $_cs_journey['tip_link_url'] )   ? (string) $_cs_journey['tip_link_url']   : '',
				);
			}
			$journey = array(
				'heading' => ! empty( $_cs_journey['heading'] ) ? (string) $_cs_journey['heading'] : sprintf( adn_term( 'category_page.journey_heading', 'Your %s Journey' ), $name ),
				'steps'   => $steps,
				'tip'     => $tip,
			);
		}

		$calculators = array();
		if ( function_exists( 'adn_get_parent_term_calculator_cards' ) ) {
			$items = adn_get_parent_term_calculator_cards( $slug );
			if ( ! empty( $items ) ) {
				$calculators = array(
					'heading' => array(
						'title'      => ! empty( $_cs_calc['heading'] ) ? (string) $_cs_calc['heading'] : sprintf( adn_term( 'category_page.calculators_heading', '%s for %s' ), SITE_TOOLS_PLURAL, $name ),
						'link_label' => adn_term( 'category_page.related_tools_heading', 'View all →' ),
						'link_url'   => SITE_CALCULATORS_URL,
					),
					'items' => array_map( static function( $item ) {
						return array(
							'icon'      => $item['icon'],
							'name'      => $item['label'],
							'desc'      => $item['desc'] ?? '',
							'url'       => $item['url'],
							'thumbnail' => $item['thumbnail'],
							'highlight' => $item['highlight'] ?? '',
						);
					}, $items ),
				);
			}
		}

		$sidebar = array();
		if ( ! empty( $_cs_sidebar['tools'] ) && is_array( $_cs_sidebar['tools'] ) ) {
			$tools = array();
			foreach ( $_cs_sidebar['tools'] as $t ) {
				if ( empty( $t['label'] ) ) { continue; }
				$tools[] = array(
					'icon'  => ! empty( $t['icon'] )  ? (string) $t['icon']  : '',
					'label' => (string) $t['label'],
					'url'   => ! empty( $t['url'] )   ? (string) $t['url']   : '#',
				);
			}
			if ( ! empty( $tools ) ) {
				$sidebar['quick_tools'] = array(
					'heading' => adn_term( 'category_page.quick_tools_heading', 'Quick Tools' ),
					'items'   => $tools,
					'cta'     => array(
						'label' => ! empty( $_cs_sidebar['cta_label'] ) ? (string) $_cs_sidebar['cta_label'] : '',
						'url'   => ! empty( $_cs_sidebar['cta_url'] )   ? (string) $_cs_sidebar['cta_url']   : '',
					),
				);
			}
		}

		if ( ! empty( $_cs_ht['items'] ) && is_array( $_cs_ht['items'] ) ) {
			$topics = array();
			foreach ( $_cs_ht['items'] as $t ) {
				if ( empty( $t['label'] ) ) { continue; }
				$topics[] = array(
					'icon'  => ! empty( $t['icon'] )  ? (string) $t['icon']  : '',
					'label' => (string) $t['label'],
					'url'   => ! empty( $t['url'] )   ? (string) $t['url']   : '#',
				);
			}
			if ( ! empty( $topics ) ) {
				$sidebar['hot_topics'] = array(
					'heading'  => ! empty( $_cs_ht['heading'] )        ? (string) $_cs_ht['heading']        : adn_term( 'category_page.hot_topics_heading', '🔥 Hot Topics' ),
					'items'    => $topics,
					'view_all' => array(
						'label' => ! empty( $_cs_ht['view_all_label'] ) ? (string) $_cs_ht['view_all_label'] : '',
						'url'   => ! empty( $_cs_ht['view_all_url'] )   ? (string) $_cs_ht['view_all_url']   : '',
					),
				);
			}
		}

		if ( ! empty( $_cs_sidebar['experts'] ) && is_array( $_cs_sidebar['experts'] ) ) {
			$expert_list = array();
			foreach ( $_cs_sidebar['experts'] as $e ) {
				if ( empty( $e['name'] ) ) { continue; }
				$expert_list[] = array(
					'icon' => ! empty( $e['icon'] ) ? (string) $e['icon'] : '',
					'name' => (string) $e['name'],
					'desc' => ! empty( $e['desc'] ) ? (string) $e['desc'] : '',
					'url'  => ! empty( $e['url'] )  ? (string) $e['url']  : '#',
				);
			}
			if ( ! empty( $expert_list ) ) {
				$sidebar['expert_help'] = array(
					'heading'  => ! empty( $_cs_sidebar['expert_heading'] )   ? (string) $_cs_sidebar['expert_heading']   : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' ),
					'subtitle' => ! empty( $_cs_sidebar['expert_subtitle'] )  ? (string) $_cs_sidebar['expert_subtitle']  : '',
					'experts'  => $expert_list,
					'cta'      => array(
						'label' => ! empty( $_cs_sidebar['expert_cta_label'] ) ? (string) $_cs_sidebar['expert_cta_label'] : '',
						'url'   => ! empty( $_cs_sidebar['expert_cta_url'] )   ? (string) $_cs_sidebar['expert_cta_url']   : '#',
					),
				);
			}
		}

		if ( ! empty( $_cs_ft['items'] ) && is_array( $_cs_ft['items'] ) ) {
			$ft_items = array();
			foreach ( $_cs_ft['items'] as $t ) {
				if ( empty( $t['name'] ) ) { continue; }
				$ft_items[] = array(
					'icon'  => ! empty( $t['icon'] ) ? (string) $t['icon'] : '',
					'label' => (string) $t['name'],
					'url'   => ! empty( $t['url'] )  ? (string) $t['url']  : '#',
				);
			}
			if ( ! empty( $ft_items ) ) {
				$sidebar['featured_topics'] = array(
					'heading' => ! empty( $_cs_ft['heading'] ) ? (string) $_cs_ft['heading'] : adn_term( 'category_page.browse_topics_heading', 'Browse Topics' ),
					'items'   => $ft_items,
				);
			}
		}

		if ( ! empty( $calculators['items'] ) ) {
			$_calc_tools = array();
			foreach ( $calculators['items'] as $c ) {
				$_calc_tools[] = array(
					'icon'  => isset( $c['icon'] ) ? (string) $c['icon'] : '🧮',
					'label' => isset( $c['name'] ) ? (string) $c['name'] : '',
					'url'   => isset( $c['url'] )  ? (string) $c['url']  : '#',
				);
			}
			if ( empty( $sidebar['quick_tools'] ) ) {
				$sidebar['quick_tools'] = array(
					'heading' => ! empty( $calculators['heading']['title'] ) ? (string) $calculators['heading']['title'] : sprintf( adn_term( 'category_page.related_tools_heading', 'Related %s' ), SITE_TOOLS_PLURAL ),
					'items'   => $_calc_tools,
					'cta'     => array( 'label' => sprintf( adn_term( 'category_page.related_tools_heading', 'All %s →' ), SITE_TOOLS_PLURAL ), 'url' => SITE_CALCULATORS_URL ),
				);
			}
		}

		if ( empty( $sidebar['expert_help'] ) ) {
			$_eh_pg = get_option( 'adn_calculators_page', array() );
			$sidebar['expert_help'] = array(
				'heading'  => ! empty( $_cs_sidebar['expert_heading'] )   ? (string) $_cs_sidebar['expert_heading']   : ( ! empty( $_eh_pg['sidebar_help_title'] ) ? (string) $_eh_pg['sidebar_help_title'] : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' ) ),
				'subtitle' => ! empty( $_cs_sidebar['expert_subtitle'] )  ? (string) $_cs_sidebar['expert_subtitle']  : ( ! empty( $_eh_pg['sidebar_help_text'] )  ? (string) $_eh_pg['sidebar_help_text']  : adn_term( 'sidebar.expert_help_subtitle', 'Get personalised guidance from our property experts.' ) ),
				'experts'  => array(),
				'cta'      => array(
					'label' => ! empty( $_cs_sidebar['expert_cta_label'] ) ? (string) $_cs_sidebar['expert_cta_label'] : ( ! empty( $_eh_pg['sidebar_help_btn_label'] ) ? (string) $_eh_pg['sidebar_help_btn_label'] : SITE_EXPERT_LABEL ),
					'url'   => ! empty( $_cs_sidebar['expert_cta_url'] )   ? (string) $_cs_sidebar['expert_cta_url']   : ( ! empty( $_eh_pg['sidebar_help_btn_url'] )   ? (string) $_eh_pg['sidebar_help_btn_url']   : SITE_EXPERT_URL ),
				),
			);
		}

		$_cs_res  = isset( $_cs_all['resources'] ) && is_array( $_cs_all['resources'] ) ? $_cs_all['resources'] : array();
		$_res_ids = ( isset( $_cs_res['library_ids'] ) && is_array( $_cs_res['library_ids'] ) )
			? array_filter( array_map( 'absint', $_cs_res['library_ids'] ) )
			: array();

		$resources = array(
			'items'   => array(),
			'heading' => isset( $_cs_res['heading'] ) && '' !== $_cs_res['heading'] ? (string) $_cs_res['heading'] : '',
		);

		if ( ! empty( $_res_ids ) && class_exists( 'AH_Resources_Model' ) ) {
			$resources['items'] = $repo->get_resources_by_ids( $_res_ids );
		}

		$faqs = array();
		if ( ! empty( $_cs_faqs['items'] ) && is_array( $_cs_faqs['items'] ) ) {
			$_faq_ids = array();
			foreach ( (array) $_cs_faqs['items'] as $_fi ) {
				if ( ! empty( $_fi['faq_id'] ) ) {
					$_faq_ids[] = (int) $_fi['faq_id'];
				}
				if ( count( $_faq_ids ) >= 100 ) { break; }
			}
			$_faq_ids = array_filter( $_faq_ids );

			if ( ! empty( $_faq_ids ) ) {
				$_faq_built = $repo->get_faqs_by_ids( $_faq_ids );
				if ( ! empty( $_faq_built ) ) {
					$faqs = array(
						'heading' => ! empty( $_cs_faqs['heading'] ) ? (string) $_cs_faqs['heading'] : sprintf( '%s FAQs', $name ),
						'items'   => $_faq_built,
					);
				}
			}
		}

		$cta_banner = array();
		if ( ! empty( $_cs_cta['title'] ) ) {
			$cta_banner = array(
				'icon'        => ! empty( $_cs_cta['icon'] )        ? (string) $_cs_cta['icon']        : '🏡',
				'title'       => (string) $_cs_cta['title'],
				'description' => ! empty( $_cs_cta['description'] ) ? (string) $_cs_cta['description'] : '',
				'cta'         => array(
					'label' => ! empty( $_cs_cta['btn_label'] ) ? (string) $_cs_cta['btn_label'] : '',
					'url'   => ! empty( $_cs_cta['btn_url'] )   ? (string) $_cs_cta['btn_url']   : '#',
				),
			);
		}

		$popular_posts = array();
		if ( ! empty( $_cs_pp['items'] ) && is_array( $_cs_pp['items'] ) ) {
			$_pp_ids = array();
			foreach ( (array) $_cs_pp['items'] as $_item ) {
				if ( ! empty( $_item['post_id'] ) ) {
					$_pp_ids[] = (int) $_item['post_id'];
				}
				if ( count( $_pp_ids ) >= 6 ) { break; }
			}
			$_pp_ids = array_filter( $_pp_ids );

			if ( ! empty( $_pp_ids ) ) {
				$_pp_q = new \WP_Query( array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'post__in'       => $_pp_ids,
					'orderby'        => 'post__in',
					'posts_per_page' => 6,
					'no_found_rows'  => true,
				) );
				$pp_cards = array();
				foreach ( $_pp_q->posts as $i => $_pp_post ) {
					$_ex    = $_pp_post->post_excerpt ? $_pp_post->post_excerpt : $_pp_post->post_content;
					$_thumb = get_the_post_thumbnail_url( $_pp_post->ID, 'medium_large' );
					$pp_cards[] = array(
						'image'       => $_thumb ? $_thumb : '',
						'icon'        => 'fa-solid fa-book-open',
						'gradient'    => adn_cms_gradient( $i ),
						'category'    => PARENT_TERM,
						'title'       => $_pp_post->post_title,
						'description' => wp_trim_words( $_ex, 18, '…' ),
						'read_more'   => adn_term( 'content.read_more', 'Explore' ),
						'url'         => get_permalink( $_pp_post ),
					);
				}
				wp_reset_postdata();

				if ( ! empty( $pp_cards ) ) {
					$popular_posts = array(
						'heading' => array(
							'title'      => ! empty( $_cs_pp['heading'] ) ? (string) $_cs_pp['heading'] : sprintf( adn_term( 'category_page.popular_guides_heading', 'Popular %s' ), adn_term( 'taxonomy.parent_plural', 'Guides' ) ),
							'link_label' => '',
							'link_url'   => '',
						),
						'items' => $pp_cards,
					);
				}
			}
		}

		$_main_news_items = array();
		foreach ( array_slice( self::cmsNews( 2 ), 0, 2 ) as $n ) {
			$_entry = array(
				'title' => $n['title'],
				'url'   => $n['url'],
				'date'  => $n['date'],
				'tag'   => isset( $n['tag'] ) ? $n['tag'] : '',
			);
			if ( ! empty( $n['thumbnail'] ) ) {
				$_entry['thumbnail'] = $n['thumbnail'];
			} else {
				$_entry['icon'] = '📰';
			}
			$_main_news_items[] = $_entry;
		}
		$news = array(
			'heading' => array(
				'title'      => adn_term( 'labels.latest_news', 'Latest News' ),
				'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
				'link_url'   => SITE_NEWS_URL,
			),
			'items' => $_main_news_items,
		);

		$ctx = array(
			'slug'          => $slug,
			'meta'          => $meta,
			'breadcrumb'    => $breadcrumb,
			'hero'          => $hero,
			'journey'       => $journey,
			'spotlights'    => array(
				'terms' => isset( $_cs_sp['terms'] ) && is_array( $_cs_sp['terms'] )
					? array_values( array_filter( array_map( 'sanitize_key', $_cs_sp['terms'] ) ) )
					: array(),
			),
			'guides'        => $guides,
			'popular_posts' => $popular_posts,
			'news'          => $news,
			'regulations'   => $regulations,
			'calculators'   => $calculators,
			'resources'     => $resources,
			'faqs'          => $faqs,
			'sidebar'       => $sidebar,
			'cta_banner'    => $cta_banner,
			'newsletter'    => array(
				'icon'         => '📬',
				'title'        => defined( 'SITE_NEWSLETTER_TITLE' ) ? SITE_NEWSLETTER_TITLE : 'Stay Informed',
				'description'  => defined( 'SITE_NEWSLETTER_DESC' )  ? SITE_NEWSLETTER_DESC  : 'Get the latest guides and updates delivered to your inbox.',
				'placeholder'  => defined( 'SITE_NEWSLETTER_PH' )    ? SITE_NEWSLETTER_PH    : 'Your email address',
				'button_label' => defined( 'SITE_BTN_SUBSCRIBE' )    ? SITE_BTN_SUBSCRIBE    : 'Subscribe',
				'note'         => defined( 'SITE_NEWSLETTER_NOTE' )  ? SITE_NEWSLETTER_NOTE  : 'No spam. Unsubscribe anytime.',
			),
			'chrome'        => $chrome,
		);

		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $cache_key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
		}
		return $ctx;
	}
}
