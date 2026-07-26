<?php
namespace CMS_ECOMMERCE\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Activator {
    public static function activate(): void {
        self::create_tables();
    }

    private static function create_tables(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $tables = [
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rh_orders (
                id VARCHAR(36) PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                status VARCHAR(30) DEFAULT 'placed',
                subtotal DECIMAL(12,2) NOT NULL,
                shipping DECIMAL(12,2) DEFAULT 0,
                discount DECIMAL(12,2) DEFAULT 0,
                cod_charge DECIMAL(12,2) DEFAULT 0,
                total DECIMAL(12,2) NOT NULL,
                address LONGTEXT NOT NULL,
                payment_method VARCHAR(20) NOT NULL,
                payment_id VARCHAR(100),
                coupon_code VARCHAR(50),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_user (user_id),
                KEY idx_status (status)
            ) $charset;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rh_order_items (
                id VARCHAR(36) PRIMARY KEY,
                order_id VARCHAR(36) NOT NULL,
                product_id VARCHAR(36) NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                variant_id VARCHAR(50),
                KEY idx_order (order_id)
            ) $charset;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rh_cart (
                id VARCHAR(36) PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                product_id VARCHAR(36) NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                variant_id VARCHAR(50),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_user_product (user_id, product_id, variant_id)
            ) $charset;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rh_wishlist (
                id VARCHAR(36) PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                product_id VARCHAR(36) NOT NULL,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_user_product (user_id, product_id)
            ) $charset;",
        ];

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ( $tables as $sql ) dbDelta( $sql );
    }
}
