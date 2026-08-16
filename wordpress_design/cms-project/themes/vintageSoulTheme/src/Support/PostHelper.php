<?php
namespace VintageSoul\Support;

defined( 'ABSPATH' ) || exit;

final class PostHelper {

	public static function excerpt( int $post_id, int $words = 30 ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		$source = has_excerpt( $post_id ) ? $post->post_excerpt : $post->post_content;
		return wp_trim_words( wp_strip_all_tags( $source ), $words );
	}

	public static function reading_time_minutes( int $post_id ): int {
		$post  = get_post( $post_id );
		$words = $post ? str_word_count( wp_strip_all_tags( $post->post_content ) ) : 0;
		return max( 1, (int) ceil( $words / 200 ) );
	}
}
