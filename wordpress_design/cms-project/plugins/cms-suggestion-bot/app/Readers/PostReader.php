<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Readers;

use CmsSuggestionBot\Contracts\ReaderInterface;
use CmsSuggestionBot\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Reads WordPress Posts (post_type = 'post') into normalized cache records.
 */
final class PostReader implements ReaderInterface {

	public function type(): string {
		return 'post';
	}

	public function label(): string {
		return __( 'Posts', 'cms-suggestion-bot' );
	}

	public function isAvailable(): bool {
		return post_type_exists( 'post' );
	}

	public function count(): int {
		$counts = (array) wp_count_posts( 'post' );

		return (int) ( $counts['publish'] ?? 0 );
	}

	public function read( int $offset, int $limit ): array {
		$query = new \WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'no_found_rows'  => true,
		) );

		return array_map( array( $this, 'normalize' ), $query->posts );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function normalize( \WP_Post $post ): array {
		$content = (string) apply_filters( 'the_content', $post->post_content );

		return array(
			'source_type'  => $this->type(),
			'source_id'    => (int) $post->ID,
			'title'        => get_the_title( $post ),
			'slug'         => (string) $post->post_name,
			'url'          => (string) get_permalink( $post ),
			'excerpt'      => Str::excerpt( $content, 40 ),
			'content'      => $content,
			'content_hash' => Str::hash( $content ),
			'word_count'   => Str::wordCount( $content ),
			'meta'         => array(
				'categories' => wp_list_pluck( get_the_category( $post ), 'name' ),
				'tags'       => wp_list_pluck( get_the_tags( $post ) ?: array(), 'name' ),
				'author'     => (int) $post->post_author,
				'created_at' => (string) $post->post_date_gmt,
				'updated_at' => (string) $post->post_modified_gmt,
			),
			'status'       => 'active',
		);
	}
}
