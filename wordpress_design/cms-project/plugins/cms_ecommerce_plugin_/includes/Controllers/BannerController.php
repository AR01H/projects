<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class BannerController {
    public function get_banners( \WP_REST_Request $request ): \WP_REST_Response {
        $posts = get_posts( [ 'post_type' => 'rh_banner', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC' ] );
        $banners = array_map( function ( $post ) {
            $meta = json_decode( $post->post_content, true ) ?: [];
            return [
                'id' => (string) $post->ID, 'title' => $post->post_title,
                'subtitle' => $meta['subtitle'] ?? '', 'ctaLabel' => $meta['ctaLabel'] ?? '',
                'ctaLink' => $meta['ctaLink'] ?? '', 'image' => get_the_post_thumbnail_url( $post->ID, 'full' ) ?: ( $meta['image'] ?? '' ),
                'theme' => $meta['theme'] ?? 'light', 'position' => $meta['position'] ?? 'hero',
                'sortOrder' => (int) $post->menu_order,
            ];
        }, $posts );
        return Response::success( $banners );
    }
}
