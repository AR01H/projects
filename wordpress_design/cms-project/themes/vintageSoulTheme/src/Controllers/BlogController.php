<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

final class BlogController {

	public function prepare(): array {
		$fallback_data = JsonFileProvider::read( 'data/content/posts.json' );
		$hero          = (array) ( $fallback_data['hero'] ?? array() );
		$categories    = (array) ( $fallback_data['categories'] ?? array() );
		$fallback_items = (array) ( $fallback_data['items'] ?? array() );

		// Query dynamic WordPress posts
		$wp_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 12,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$articles = array();

		if ( ! empty( $wp_posts ) ) {
			foreach ( $wp_posts as $post ) {
				$thumb_id  = get_post_thumbnail_id( $post->ID );
				$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';
				
				if ( ! $thumb_url ) {
					$thumb_url = UrlHelper::resolve( 'assets/images/sugarcane/hero_juice.jpg' );
				}

				$cats     = get_the_category( $post->ID );
				$cat_name = ! empty( $cats ) ? $cats[0]->name : 'Heritage Craft';

				$word_count   = str_word_count( strip_tags( (string) $post->post_content ) );
				$reading_time = max( 1, (int) ceil( $word_count / 200 ) );

				$articles[] = array(
					'id'           => $post->ID,
					'title'        => get_the_title( $post ),
					'permalink'    => get_permalink( $post ),
					'slug'         => $post->post_name,
					'category'     => $cat_name,
					'date'         => get_the_date( 'j F Y', $post ),
					'author'       => get_the_author_meta( 'display_name', $post->post_author ) ?: 'The Cane House',
					'reading_time' => $reading_time,
					'excerpt'      => wp_trim_words( $post->post_excerpt ?: $post->post_content, 22, '...' ),
					'image'        => $thumb_url,
					'content'      => apply_filters( 'the_content', $post->post_content ),
				);
			}
		}

		// If no posts in WP database, use rich fallback posts
		if ( empty( $articles ) ) {
			foreach ( $fallback_items as $item ) {
				$img = (string) ( $item['image'] ?? '' );
				if ( '' !== $img && 0 !== strpos( $img, 'http' ) ) {
					$img = UrlHelper::resolve( $img );
				}

				$articles[] = array(
					'id'           => (int) ( $item['id'] ?? 1 ),
					'title'        => (string) ( $item['title'] ?? '' ),
					'permalink'    => home_url( '/blog/?article=' . (string) ( $item['slug'] ?? '' ) ),
					'slug'         => (string) ( $item['slug'] ?? '' ),
					'category'     => (string) ( $item['category'] ?? '' ),
					'date'         => (string) ( $item['date'] ?? '' ),
					'author'       => (string) ( $item['author'] ?? '' ),
					'reading_time' => (int) ( $item['reading_time'] ?? 4 ),
					'excerpt'      => (string) ( $item['excerpt'] ?? '' ),
					'image'        => $img,
					'content'      => (string) ( $item['content'] ?? '' ),
				);
			}
		}

		return array(
			'hero'       => $hero,
			'categories' => $categories,
			'articles'   => $articles,
		);
	}
}
