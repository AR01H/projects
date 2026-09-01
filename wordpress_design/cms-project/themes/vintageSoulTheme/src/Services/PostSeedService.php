<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class PostSeedService {

	private const SEED_OPTION = 'vintagesoul_posts_seed_v2';

	public static function seed(): void {
		if ( get_option( self::SEED_OPTION ) === 'done_v2' ) {
			return;
		}

		$data = JsonFileProvider::read( 'data/content/posts.json' );
		$items = (array) ( $data['items'] ?? array() );

		if ( empty( $items ) ) {
			return;
		}

		// Clean up dummy "Hello World!" default starter post if present
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello instanceof \WP_Post ) {
			wp_delete_post( $hello->ID, true );
		}

		foreach ( $items as $item ) {
			$slug    = sanitize_title( (string) ( $item['slug'] ?? '' ) );
			$title   = (string) ( $item['title'] ?? '' );
			$content = (string) ( $item['content'] ?? '' );
			$excerpt = (string) ( $item['excerpt'] ?? '' );
			$cat     = (string) ( $item['category'] ?? 'Heritage' );

			if ( '' === $slug || '' === $title ) {
				continue;
			}

			$existing = get_page_by_path( $slug, OBJECT, 'post' );
			if ( $existing instanceof \WP_Post ) {
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'post',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
					'post_date'    => gmdate( 'Y-m-d H:i:s' ),
				)
			);

			if ( $post_id && ! is_wp_error( $post_id ) ) {
				wp_set_object_terms( $post_id, $cat, 'category' );
			}
		}

		update_option( self::SEED_OPTION, 'done_v2' );
	}
}
