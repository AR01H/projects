<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class CmsDataService {

	public static function table( string $suffix ): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_' . \preg_replace( '/[^a-z0-9_]/', '', $suffix );
	}

	public static function tableExists( string $table ): bool {
		static $cache = [];
		if ( isset( $cache[ $table ] ) ) {
			return $cache[ $table ];
		}
		global $wpdb;
		$cache[ $table ] = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );
		return $cache[ $table ];
	}

	public static function isAvailable(): bool {
		static $ready = null;
		if ( null !== $ready ) {
			return $ready;
		}
		$tax = self::table( 'taxonomies' );
		$ct = self::table( 'content_taxonomies' );
		$ready = self::tableExists( $tax ) && self::tableExists( $ct );
		return $ready;
	}

	public static function getGuideTypeId(): int {
		if ( ! self::isAvailable() ) {
			return 0;
		}
		global $wpdb;
		$types = self::table( 'taxonomy_types' );
		return (int) $wpdb->get_var(
			"SELECT id FROM `{$types}`
			 WHERE slug IN ('category','guide','guides') OR name LIKE '%Categor%' OR name LIKE '%Guide%'
			 ORDER BY id ASC LIMIT 1"
		);
	}

	public static function getGuideParents( int $limit = 12 ): array {
		if ( ! self::isAvailable() ) {
			return [];
		}
		$limit = \max( 1, $limit );
		global $wpdb;
		$pt = self::table( 'taxonomy_parent_terms' );
		if ( ! self::tableExists( $pt ) ) {
			return [];
		}
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$pt}`
			 WHERE status = 'active' AND slug <> 'news'
			 ORDER BY sort_order ASC, id ASC
			 LIMIT %d",
			$limit
		) ) ?: [];
	}

	public static function getParentBySlug( string $slug ): ?object {
		if ( ! self::isAvailable() ) {
			return null;
		}
		global $wpdb;
		$pt = self::table( 'taxonomy_parent_terms' );
		if ( ! self::tableExists( $pt ) ) {
			return null;
		}
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$pt}` WHERE slug = %s AND status = 'active'",
			$slug
		) );
	}

	public static function getTopics( int $parentTermId, int $limit = 100 ): array {
		if ( ! self::isAvailable() ) {
			return [];
		}
		global $wpdb;
		$tax = self::table( 'taxonomies' );
		if ( ! self::tableExists( $tax ) ) {
			return [];
		}
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$tax}`
			 WHERE parent_term_id = %d AND status = 'active'
			 ORDER BY sort_order ASC, id ASC
			 LIMIT %d",
			$parentTermId,
			$limit
		) ) ?: [];
	}

	public static function getArticles( int $limit = 6, array $taxonomyIds = [] ): array {
		if ( ! self::isAvailable() ) {
			return [];
		}
		global $wpdb;
		$ct = self::table( 'content_taxonomies' );
		if ( ! self::tableExists( $ct ) ) {
			return [];
		}

		if ( empty( $taxonomyIds ) ) {
			return [];
		}

		$ids = \implode( ',', \array_map( 'absint', $taxonomyIds ) );
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT p.* FROM {$wpdb->posts} p
			 INNER JOIN `{$ct}` ct ON ct.object_id = p.ID
			 WHERE ct.taxonomy_id IN ({$ids})
			   AND ct.object_type = 'wp_post'
			   AND p.post_status = 'publish'
			   AND p.post_type IN ('post', 'page')
			 ORDER BY p.post_date DESC
			 LIMIT %d",
			$limit
		) ) ?: [];
	}

	public static function getLatestNews( int $limit = 4 ): array {
		if ( ! self::isAvailable() ) {
			return [];
		}
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts}
			 WHERE post_status = 'publish'
			   AND post_type = 'post'
			 ORDER BY post_date DESC
			 LIMIT %d",
			$limit
		) ) ?: [];
	}

	public static function getTermBySlug( string $slug ): ?object {
		if ( ! self::isAvailable() ) {
			return null;
		}
		global $wpdb;
		$tax = self::table( 'taxonomies' );
		if ( ! self::tableExists( $tax ) ) {
			return null;
		}
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$tax}` WHERE slug = %s",
			$slug
		) );
	}

	public static function getReadTime( string $content ): string {
		$wordCount = \str_word_count( \wp_strip_all_tags( $content ) );
		$minutes = \max( 1, (int) \ceil( $wordCount / 200 ) );
		return "{$minutes} min read";
	}

	public static function getPostDate( $post ): string {
		$date = \get_the_date( 'M j, Y', $post );
		return $date ?: '';
	}

	public static function getGradient( int $index ): string {
		$gradients = [
			'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
			'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
			'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
			'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
			'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
			'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
		];
		return $gradients[ $index % \count( $gradients ) ];
	}

	public static function getPostUrl( $post ): string {
		if ( \is_object( $post ) && isset( $post->ID ) ) {
			return \get_permalink( $post->ID ) ?: '';
		}
		return '';
	}

	public static function getTermUrl( $term ): string {
		if ( \is_object( $term ) && isset( $term->slug ) ) {
			$base = \defined( 'SITE_GUIDES_URL' ) ? \SITE_GUIDES_URL : '/guides/';
			return \home_url( $base . $term->slug . '/' );
		}
		return '';
	}
}
