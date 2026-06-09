<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Home_Data
 *
 * Fetches and normalises all data for front-page.php.
 * Returns a single flat array consumed by the home/* components.
 * No HTML here - only data transformation.
 */
class AH_Home_Data {

	// ── Public API ────────────────────────────────────────────────────────────

	public static function get(): array {
		$img_base = get_template_directory_uri() . '/assets/images/backgrounds/';
		$fb = [
			'news'   => $img_base . 'min_news.png',
			'blog'   => $img_base . 'mini_blog.png',
			'guides' => $img_base . 'mini_guides.png',
			'review' => $img_base . 'min_reviews.png',
		];

		$guide_cats   = function_exists( 'ah_get_guide_categories' ) ? ah_get_guide_categories() : [];
		$hp_tiles     = function_exists( 'get_client_hp_tiles' )     ? get_client_hp_tiles()     : [];
		$tiles        = array_values( $hp_tiles ?: [] );

		$parent_terms = [];
		if ( class_exists( 'AH_Taxonomy_Parent_Model' ) ) {
			$parent_terms = ( new AH_Taxonomy_Parent_Model() )->get_all_active();
		}

		$all_posts   = get_posts( [
			'posts_per_page'      => 13,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => false,
		] );

		$hero_post   = $all_posts[0] ?? null;
		$bento_posts = array_slice( $all_posts, 1, 6 );
		$grid_posts  = array_slice( $all_posts, 7, 6 );

		$news_q = array_map( [ self::class, '_news_card' ], self::_fetch_news_items() );
		$post_q = array_map( [ self::class, '_post_card' ], $bento_posts );

		return [
			'hero_post'    => $hero_post,
			'hero_meta'    => $hero_post ? self::_post_meta( $hero_post ) : null,
			'bento'        => [
				'wide' => array_shift( $news_q ) ?: array_shift( $post_q ),
				'dark' => array_shift( $post_q ),
				'art'  => array_shift( $post_q ),
				'n1'   => array_shift( $news_q ) ?: array_shift( $post_q ),
				'n2'   => array_shift( $news_q ) ?: array_shift( $post_q ),
			],
			'grid_cards'   => array_map( [ self::class, '_post_meta' ], $grid_posts ),
			'guide_cats'   => $guide_cats,
			'tiles'        => $tiles,
			'parent_terms' => $parent_terms,
			'fb'           => $fb,
		];
	}

	/**
	 * Accent colour mapped from a card slug - used by bento and article components.
	 */
	public static function slug_color( string $slug ): string {
		$map = [
			'news'    => '#16a34a',
			'buying'  => '#2ecc71',
			'first'   => '#3b8fd4',
			'finance' => '#a855f7',
			'legal'   => '#6366f1',
			'invest'  => '#14b8a6',
			'tips'    => '#eab308',
			'client'  => '#d97706',
		];
		foreach ( $map as $k => $c ) {
			if ( $slug && stripos( $slug, $k ) !== false ) return $c;
		}
		return 'var(--accent)';
	}

	// ── Private helpers ───────────────────────────────────────────────────────

	private static function _post_meta( WP_Post $p ): array {
		$cats    = get_the_category( $p->ID );
		$cat     = $cats[0] ?? null;
		$thumb   = get_the_post_thumbnail_url( $p->ID, 'ah-card' )
			?: get_the_post_thumbnail_url( $p->ID, 'medium_large' ) ?: '';
		$excerpt = wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 22, '…' );
		$rt      = function_exists( 'ah_reading_time' ) ? ah_reading_time( $p->ID ) : '';
		return [
			'post'    => $p,
			'title'   => get_the_title( $p->ID ),
			'cat'     => $cat,
			'thumb'   => $thumb,
			'excerpt' => $excerpt,
			'rt'      => $rt,
			'url'     => get_permalink( $p->ID ),
			'date'    => get_the_date( 'M j, Y', $p->ID ),
		];
	}

	private static function _post_card( WP_Post $p ): array {
		$m = self::_post_meta( $p );
		return [
			'title'   => $m['title'],
			'thumb'   => $m['thumb'],
			'excerpt' => $m['excerpt'],
			'meta'    => $m['rt'],
			'url'     => $m['url'],
			'badge'   => $m['cat'] ? html_entity_decode( $m['cat']->name, ENT_QUOTES ) : 'Article',
			'slug'    => $m['cat'] ? $m['cat']->slug : '',
			'is_news' => false,
		];
	}

	private static function _news_card( $item ): array {
		$thumb = ! empty( $item->image_id )
			? ( wp_get_attachment_image_url( (int) $item->image_id, 'ah-card' )
				?: wp_get_attachment_image_url( (int) $item->image_id, 'medium_large' ) )
			: '';
		$excerpt = ! empty( $item->content ) ? wp_trim_words( wp_strip_all_tags( $item->content ), 22, '…' ) : '';
		$url     = ! empty( $item->link_url ) ? $item->link_url : home_url( '/allnews/?item=' . (int) $item->id );
		$date    = ! empty( $item->start_date ) ? date_i18n( 'M j, Y', strtotime( $item->start_date ) ) : '';
		return [
			'title'   => $item->text ?? '',
			'thumb'   => $thumb,
			'excerpt' => $excerpt,
			'meta'    => $date,
			'url'     => $url,
			'badge'   => 'NEWS',
			'slug'    => 'news',
			'is_news' => true,
		];
	}

	private static function _fetch_news_items(): array {
		if ( ! class_exists( 'AH_DB_Helper' ) ) return [];
		global $wpdb;
		$table = AH_DB_Helper::table( 'news_bar_items' );
		$today = current_time( 'Y-m-d' );
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE status='active'
			 AND (start_date IS NULL OR start_date <= %s)
			 AND (end_date   IS NULL OR end_date   >= %s)
			 ORDER BY COALESCE(start_date,'1970-01-01') DESC, id DESC LIMIT 4",
			$today, $today
		) ) ?: [];
	}
}
