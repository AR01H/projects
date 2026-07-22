<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Readers;

use CmsSuggestionBot\Contracts\ReaderInterface;
use CmsSuggestionBot\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Reads WordPress Pages (post_type = 'page') into normalized cache records.
 */
final class PageReader implements ReaderInterface {

	public function type(): string {
		return 'page';
	}

	public function label(): string {
		return __( 'Pages', 'cms-suggestion-bot' );
	}

	public function isAvailable(): bool {
		return post_type_exists( 'page' );
	}

	public function count(): int {
		$counts = (array) wp_count_posts( 'page' );

		return (int) ( $counts['publish'] ?? 0 );
	}

	public function read( int $offset, int $limit ): array {
		$query = new \WP_Query( array(
			'post_type'      => 'page',
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
				'parent'     => (int) $post->post_parent,
				'template'   => (string) get_page_template_slug( $post ),
				'author'     => (int) $post->post_author,
				'created_at' => (string) $post->post_date_gmt,
				'updated_at' => (string) $post->post_modified_gmt,
			),
			'status'       => 'active',
		);
	}
}
