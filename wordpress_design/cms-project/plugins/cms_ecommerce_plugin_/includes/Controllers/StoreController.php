<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class StoreController {
    public function get_products( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_product', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
        $products = array_map( [ $this, 'format_product' ], $posts );
        return Response::success( $products );
    }

    public function get_product( \WP_REST_Request $request ): \WP_REST_Response {
        $slug = $request->get_param( 'slug' );
        $post = get_posts( [ 'post_type' => 'rh_product', 'name' => $slug, 'post_status' => 'publish', 'numberposts' => 1 ] );
        if ( empty( $post ) ) return Response::error( 'Product not found.', 404 );
        return Response::success( $this->format_product( $post[0] ) );
    }

    public function get_categories( \WP_REST_Request $request ): \WP_REST_Response {
        $terms = get_terms( [ 'taxonomy' => 'rh_category', 'hide_empty' => false ] );
        if ( is_wp_error( $terms ) ) $terms = [];
        return Response::success( array_values( array_map( [ $this, 'format_category' ], $terms ) ) );
    }

    public function get_category( \WP_REST_Request $request ): \WP_REST_Response {
        $slug = $request->get_param( 'slug' );
        $term = get_term_by( 'slug', $slug, 'rh_category' );
        if ( ! $term ) return Response::error( 'Category not found.', 404 );
        return Response::success( $this->format_category( $term ) );
    }

    public function get_collections( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_collection', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        return Response::success( array_map( [ $this, 'format_collection' ], $posts ) );
    }

    public function get_collection( \WP_REST_Request $request ): \WP_REST_Response {
        $slug = $request->get_param( 'slug' );
        $post = get_posts( [ 'post_type' => 'rh_collection', 'name' => $slug, 'post_status' => 'publish', 'numberposts' => 1 ] );
        if ( empty( $post ) ) return Response::error( 'Collection not found.', 404 );
        return Response::success( $this->format_collection( $post[0] ) );
    }

    public function get_tags( \WP_REST_Request $request ): \WP_REST_Response {
        $terms = get_terms( [ 'taxonomy' => 'rh_tag', 'hide_empty' => false ] );
        if ( is_wp_error( $terms ) ) $terms = [];
        return Response::success( array_map( function ( $t ) {
            return [ 'tag' => $t->slug, 'label' => $t->name, 'parentTag' => $t->parent ? ( get_term( $t->parent, 'rh_tag' )->slug ?? null ) : null ];
        }, $terms ) );
    }

    private function format_product( \WP_Post $post ): array {
        $meta = json_decode( $post->post_content, true ) ?: [];
        return [
            'id' => (string) $post->ID, 'slug' => $post->post_name, 'name' => $post->post_title,
            'shortDescription' => $meta['shortDescription'] ?? '', 'description' => $meta['description'] ?? $post->post_excerpt,
            'categoryId' => $meta['categoryId'] ?? '', 'categorySlug' => $meta['categorySlug'] ?? '',
            'collectionIds' => $meta['collectionIds'] ?? [], 'price' => (float)( $meta['price'] ?? 0 ),
            'compareAtPrice' => isset( $meta['compareAtPrice'] ) ? (float) $meta['compareAtPrice'] : null,
            'currency' => $meta['currency'] ?? 'INR', 'images' => $meta['images'] ?? [],
            'videoUrl' => $meta['videoUrl'] ?? null, 'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: ( $meta['thumbnail'] ?? '' ),
            'sku' => $meta['sku'] ?? '', 'specs' => $meta['specs'] ?? [], 'qualityBadges' => $meta['qualityBadges'] ?? [],
            'makerName' => $meta['makerName'] ?? null, 'stock' => (int)( $meta['stock'] ?? 0 ),
            'lowStockThreshold' => (int)( $meta['lowStockThreshold'] ?? 5 ), 'rating' => (float)( $meta['rating'] ?? 0 ),
            'reviewCount' => (int)( $meta['reviewCount'] ?? 0 ), 'reviews' => $meta['reviews'] ?? [],
            'variants' => $meta['variants'] ?? [], 'tags' => $meta['tags'] ?? [],
            'isBestSeller' => (bool)( $meta['isBestSeller'] ?? false ), 'isNewArrival' => (bool)( $meta['isNewArrival'] ?? false ),
            'isFeatured' => (bool)( $meta['isFeatured'] ?? false ), 'isLimitedEdition' => (bool)( $meta['isLimitedEdition'] ?? false ),
            'isFestive' => (bool)( $meta['isFestive'] ?? false ), 'createdAt' => $post->post_date,
        ];
    }

    private function format_category( \WP_Term $term ): array {
        $meta = json_decode( $term->description, true ) ?: [];
        return [
            'id' => (string) $term->term_id, 'slug' => $term->slug, 'name' => $term->name,
            'description' => $meta['description'] ?? '', 'image' => $meta['image'] ?? '',
            'icon' => $meta['icon'] ?? null, 'productCount' => (int) $term->count,
            'featured' => (bool)( $meta['featured'] ?? false ),
            'parentSlug' => $term->parent ? ( get_term( $term->parent, 'rh_category' )->slug ?? null ) : null,
        ];
    }

    private function format_collection( \WP_Post $post ): array {
        $meta = json_decode( $post->post_content, true ) ?: [];
        return [
            'id' => (string) $post->ID, 'slug' => $post->post_name, 'name' => $post->post_title,
            'description' => $meta['description'] ?? $post->post_excerpt,
            'image' => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: ( $meta['image'] ?? '' ),
            'productIds' => $meta['productIds'] ?? [],
        ];
    }
}
