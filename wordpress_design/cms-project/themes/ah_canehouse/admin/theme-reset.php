<?php
/**
 * Theme Reset/Reseed - Clear all old data and reseed from CSV
 * Run this once when theme is activated
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class CH_Theme_Reset {

    public static function reset_all() {
        // Delete all theme options
        delete_option( 'ah_cms_navigation' );
        delete_option( 'ch_theme_navigation' );
        delete_option( 'ah_cms_nav_cta' );
        delete_option( 'ch_nav_cta' );
        delete_option( 'ch_site_settings' );
        delete_option( 'ch_home_settings' );
        delete_option( 'ch_contact_settings' );

        // Clear table data if tables exist
        global $wpdb;
        $tables = [
            ch_theme_table( 'news_bar' ),
            ch_theme_table( 'reviews' ),
            ch_theme_table( 'faqs' ),
            ch_theme_table( 'services' ),
            ch_theme_table( 'about_team' ),
            ch_theme_table( 'blog_posts' ),
        ];

        foreach ( $tables as $table ) {
            if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
                $wpdb->query( "TRUNCATE TABLE `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            }
        }

        // Reseed from CSV data via CH_Data class
        self::seed_news_bar();
        self::seed_reviews();
        self::seed_faqs();
        self::seed_services();
        self::seed_team();
    }

    private static function seed_news_bar() {
        if ( ! class_exists( 'CH_Data' ) ) return;
        global $wpdb;
        $table = ch_theme_table( 'news_bar' );
        $items = CH_Data::news_bar();

        foreach ( $items as $item ) {
            $wpdb->insert(
                $table,
                [
                    'message'   => $item['message'] ?? '',
                    'status'    => $item['status'] ?? 'active',
                    'sort_order' => $item['sort_order'] ?? 0,
                ],
                [ '%s', '%s', '%d' ]
            );
        }
    }

    private static function seed_reviews() {
        if ( ! class_exists( 'CH_Data' ) ) return;
        global $wpdb;
        $table = ch_theme_table( 'reviews' );
        $items = CH_Data::reviews();

        foreach ( $items as $item ) {
            $wpdb->insert(
                $table,
                [
                    'author_name' => $item['author_name'] ?? '',
                    'location'    => $item['location'] ?? '',
                    'review_text' => $item['review_text'] ?? '',
                    'rating'      => $item['rating'] ?? 5.0,
                    'result'      => $item['result'] ?? '',
                    'status'      => $item['status'] ?? 'active',
                ],
                [ '%s', '%s', '%s', '%f', '%s', '%s' ]
            );
        }
    }

    private static function seed_faqs() {
        if ( ! class_exists( 'CH_Data' ) ) return;
        global $wpdb;
        $table = ch_theme_table( 'faqs' );
        $items = CH_Data::faqs();

        foreach ( $items as $item ) {
            $wpdb->insert(
                $table,
                [
                    'topic'      => $item['topic'] ?? '',
                    'question'   => $item['question'] ?? '',
                    'answer'     => $item['answer'] ?? '',
                    'status'     => $item['status'] ?? 'active',
                    'sort_order' => $item['sort_order'] ?? 0,
                ],
                [ '%s', '%s', '%s', '%s', '%d' ]
            );
        }
    }

    private static function seed_services() {
        if ( ! class_exists( 'CH_Data' ) ) return;
        global $wpdb;
        $table = ch_theme_table( 'services' );
        $items = CH_Data::services();

        foreach ( $items as $item ) {
            $wpdb->insert(
                $table,
                [
                    'icon'        => $item['icon'] ?? '',
                    'title'       => $item['title'] ?? '',
                    'description' => $item['description'] ?? '',
                    'details'     => $item['details'] ?? '',
                    'image_url'   => $item['image_url'] ?? '',
                    'status'      => $item['status'] ?? 'active',
                    'sort_order'  => $item['sort_order'] ?? 0,
                ],
                [ '%s', '%s', '%s', '%s', '%s', '%s', '%d' ]
            );
        }
    }

    private static function seed_team() {
        if ( ! class_exists( 'CH_Data' ) ) return;
        global $wpdb;
        $table = ch_theme_table( 'about_team' );
        $items = CH_Data::about_team();

        foreach ( $items as $item ) {
            $wpdb->insert(
                $table,
                [
                    'name'       => $item['name'] ?? '',
                    'role'       => $item['role'] ?? '',
                    'bio'        => $item['bio'] ?? '',
                    'image_url'  => $item['image_url'] ?? '',
                    'status'     => $item['status'] ?? 'active',
                    'sort_order' => $item['sort_order'] ?? 0,
                ],
                [ '%s', '%s', '%s', '%s', '%s', '%d' ]
            );
        }
    }
}
