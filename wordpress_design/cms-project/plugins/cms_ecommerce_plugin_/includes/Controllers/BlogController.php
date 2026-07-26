<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlogController {
    public function get_posts( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_blog_post', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
        $items = array_map( function ( $post ) {
            $meta = json_decode( $post->post_content, true ) ?: [];
            return [
                'id' => (string) $post->ID, 'slug' => $post->post_name, 'title' => $post->post_title,
                'excerpt' => $meta['excerpt'] ?? wp_trim_words( $post->post_content, 20 ),
                'content' => $meta['content'] ?? [ $post->post_content ],
                'coverImage' => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: ( $meta['coverImage'] ?? '' ),
                'categorySlug' => $meta['categorySlug'] ?? '', 'author' => get_the_author_meta( 'display_name', $post->post_author ),
                'date' => $post->post_date, 'readMinutes' => (int)( $meta['readMinutes'] ?? 5 ),
                'tags' => $meta['tags'] ?? [],
            ];
        }, $posts );
        return Response::success( $items );
    }

    public function get_post( \WP_REST_Request $request ): \WP_REST_Response {
        $slug = $request->get_param( 'slug' );
        $post = get_posts( [ 'post_type' => 'rh_blog_post', 'name' => $slug, 'post_status' => 'publish', 'numberposts' => 1 ] );
        if ( empty( $post ) ) return Response::error( 'Post not found.', 404 );
        $p = $post[0]; $meta = json_decode( $p->post_content, true ) ?: [];
        return Response::success( [
            'id' => (string) $p->ID, 'slug' => $p->post_name, 'title' => $p->post_title,
            'excerpt' => $meta['excerpt'] ?? '', 'content' => $meta['content'] ?? [ $p->post_content ],
            'coverImage' => get_the_post_thumbnail_url( $p->ID, 'large' ) ?: '', 'categorySlug' => $meta['categorySlug'] ?? '',
            'author' => get_the_author_meta( 'display_name', $p->post_author ), 'date' => $p->post_date,
            'readMinutes' => (int)( $meta['readMinutes'] ?? 5 ), 'tags' => $meta['tags'] ?? [],
        ]);
    }

    public function get_categories( \WP_REST_Request $request ): \WP_REST_Response {
        $terms = get_terms( [ 'taxonomy' => 'rh_blog_cat', 'hide_empty' => false ] );
        if ( is_wp_error( $terms ) ) $terms = [];
        $cats = array_map( function ( $t ) {
            return [ 'id' => (string) $t->term_id, 'slug' => $t->slug, 'name' => $t->name ];
        }, $terms );
        return Response::success( array_values( $cats ) );
    }
}
