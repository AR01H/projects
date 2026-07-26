<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * PostContext - Builds context for single post/article pages.
 * Split into small focused methods.
 */
class PostContext {

	// ── Cache helpers ───────────────────────────────────────────
	private static function cacheGet( string $key ) {
		if ( class_exists( 'ADN_Cache' ) ) {
			return \ADN_Cache::get( $key, 'posts' );
		}
		return false;
	}

	private static function cacheSet( string $key, array $ctx ): void {
		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $key, $ctx, 'posts', get_option( 'ah_cache_expiry', 3600 ) );
		}
	}

	// ── Meta section ────────────────────────────────────────────
	public static function buildMeta( $post ): array {
		return array(
			'slug'             => $post->post_name ?? '',
			'page_title'       => get_the_title() . ' - ' . SITE_BRAND_NAME,
			'meta_description' => wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 30 ),
		);
	}

	// ── Breadcrumb section ──────────────────────────────────────
	public static function buildBreadcrumb( $post ): array {
		return \Adn\Theme\Shared\BreadcrumbBuilder::post( $post );
	}

	// ── Header section ──────────────────────────────────────────
	public static function buildHeader( $post ): array {
		$icon = (string) get_post_meta( $post->ID, '_adn_article_icon', true );
		if ( '' === $icon ) { $icon = SITE_BRAND_ICON; }
		$read_time = (string) get_post_meta( $post->ID, '_adn_read_time', true );
		$cats = get_the_category( $post->ID );
		$category_tag = ! empty( $cats ) ? $cats[0]->name : '';
		$custom_tag = (string) get_post_meta( $post->ID, '_adn_category_tag', true );
		if ( '' !== $custom_tag ) { $category_tag = $custom_tag; }

		return array(
			'title'        => get_the_title(),
			'description'  => wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 25 ),
			'image'        => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '',
			'icon'         => $icon,
			'read_time'    => $read_time,
			'category_tag' => $category_tag,
			'date'         => get_the_date(),
			'url'          => get_permalink(),
		);
	}

	// ── Key takeaways section ───────────────────────────────────
	public static function buildKeyTakeaways( $post ): array {
		$raw = get_post_meta( $post->ID, '_adn_key_takeaways', true );
		$items = $raw ? json_decode( $raw, true ) : array();
		return is_array( $items ) ? $items : array();
	}

	// ── Author section ──────────────────────────────────────────
	public static function buildAuthor(): array {
		$name = get_the_author_meta( 'display_name' );
		if ( empty( $name ) ) {
			$name = defined( 'COMPANY_NAME' ) ? COMPANY_NAME . ' Team' : SITE_EXPERT_NOUN . 's';
		}
		$avatar = get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 80 ) );
		return array(
			'name'   => $name,
			'avatar' => $avatar ?: '',
			'bio'    => get_the_author_meta( 'description' ),
		);
	}

	// ── Body section ────────────────────────────────────────────
	public static function buildBody(): array {
		$content = get_the_content();
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		return array( 'html' => $content );
	}

	// ── Disclaimer section ──────────────────────────────────────
	public static function buildDisclaimer(): array {
		return array(
			'text' => adn_term( 'brand.disclaimer', '' ),
		);
	}

	// ── Feedback section ────────────────────────────────────────
	public static function buildFeedback(): array {
		return array();
	}

	// ── Share section ───────────────────────────────────────────
	public static function buildShare( $post ): array {
		return array(
			'url'   => get_permalink( $post ),
			'title' => get_the_title( $post ),
		);
	}

	// ── Highlight links (sidebar) ────────────────────────────────
	// Admin: Edit Meta modal on ah-posts, saved to _ah_highlight_links postmeta.
	public static function buildHighlightLinks( $post ): array {
		$raw   = get_post_meta( $post->ID, '_ah_highlight_links', true );
		$items = $raw ? json_decode( $raw, true ) : array();
		return is_array( $items ) ? $items : array();
	}

	// ── Related content (sidebar) ────────────────────────────────
	// Admin: Edit Meta modal on ah-posts, saved via AH_Related_Links_Model::sync().
	// Reshapes get_grouped()'s [{container, items:[{label,url,...}]}] into the
	// {container => [{icon,title,url}]} shape parts/post_sidebar_related_content.php expects.
	public static function buildRelatedContent( $post ): array {
		if ( ! class_exists( 'AH_Related_Links_Model' ) ) {
			return array();
		}
		$grouped = ( new \AH_Related_Links_Model() )->get_grouped( 'wp_post', $post->ID );
		$related_content = array();
		foreach ( $grouped as $group ) {
			$related_content[ $group['container'] ] = array_map( static function ( $item ) {
				return array(
					'icon'  => $item['icon']  ?? '',
					'title' => $item['label'] ?? '',
					'url'   => $item['url']   ?? '',
				);
			}, $group['items'] );
		}
		return $related_content;
	}

	// ── Main getContext ─────────────────────────────────────────
	public static function getContext() {
		global $post;
		$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
		$cache_key = 'post_context_' . $post_id;

		$cached = self::cacheGet( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$chrome  = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();
		$sidebar = function_exists( 'adn_service_post_sidebar_data' ) ? adn_service_post_sidebar_data() : array();

		$ctx = array(
			'slug'          => $post->post_name ?? '',
			'meta'          => self::buildMeta( $post ),
			'breadcrumb'    => self::buildBreadcrumb( $post ),
			'header'        => self::buildHeader( $post ),
			'key_takeaways' => self::buildKeyTakeaways( $post ),
			'body'          => self::buildBody(),
			'disclaimer'    => self::buildDisclaimer(),
			'feedback'      => self::buildFeedback(),
			'share'         => self::buildShare( $post ),
			'highlight_links' => self::buildHighlightLinks( $post ),
			'related_content' => self::buildRelatedContent( $post ),
			'author'        => self::buildAuthor(),
			'sidebar'       => $sidebar,
			'chrome'        => $chrome,
		);

		self::cacheSet( $cache_key, $ctx );
		return $ctx;
	}
}
