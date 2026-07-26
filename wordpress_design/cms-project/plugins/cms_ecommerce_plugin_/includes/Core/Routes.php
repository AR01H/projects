<?php
/**
 * Routes — THE single source of truth for all API endpoints.
 *
 * Format: 'HTTP_METHOD /path/{param}' => [ControllerClass::class, 'method', 'auth_level']
 * Auth levels: 'public' | 'auth' | 'admin'
 *
 * ~90 endpoints for storefront + admin + migration
 */

use CMS_ECOMMERCE\Controllers\AuthController;
use CMS_ECOMMERCE\Controllers\AdminAuth;
use CMS_ECOMMERCE\Controllers\StoreController;
use CMS_ECOMMERCE\Controllers\BannerController;
use CMS_ECOMMERCE\Controllers\BlogController;
use CMS_ECOMMERCE\Controllers\ReviewController;
use CMS_ECOMMERCE\Controllers\CertController;
use CMS_ECOMMERCE\Controllers\CartController;
use CMS_ECOMMERCE\Controllers\WishlistController;
use CMS_ECOMMERCE\Controllers\CouponController;
use CMS_ECOMMERCE\Controllers\OrderController;
use CMS_ECOMMERCE\Controllers\PaymentController;
use CMS_ECOMMERCE\Controllers\FooterController;
use CMS_ECOMMERCE\Controllers\SettingsController;
use CMS_ECOMMERCE\Controllers\UploadController;
use CMS_ECOMMERCE\Controllers\AdminController;
use CMS_ECOMMERCE\Controllers\DashboardController;
use CMS_ECOMMERCE\Controllers\MigrationController;

return [

    // ══════════════════════════════════════════════
    //  AUTH — Public
    // ══════════════════════════════════════════════
    'POST /auth/login'           => [AuthController::class, 'login'],
    'POST /auth/register'        => [AuthController::class, 'register'],

    // ══════════════════════════════════════════════
    //  AUTH — Authenticated
    // ══════════════════════════════════════════════
    'GET /auth/me'               => [AuthController::class, 'get_profile', 'auth'],
    'PUT /auth/me'               => [AuthController::class, 'update_profile', 'auth'],
    'POST /auth/logout'          => [AuthController::class, 'logout', 'auth'],

    // ══════════════════════════════════════════════
    //  PRODUCTS — Public
    // ══════════════════════════════════════════════
    'GET /products'              => [StoreController::class, 'get_products'],
    'GET /products/{slug}'       => [StoreController::class, 'get_product'],

    // ══════════════════════════════════════════════
    //  CATEGORIES — Public
    // ══════════════════════════════════════════════
    'GET /categories'            => [StoreController::class, 'get_categories'],
    'GET /categories/{slug}'     => [StoreController::class, 'get_category'],

    // ══════════════════════════════════════════════
    //  COLLECTIONS — Public
    // ══════════════════════════════════════════════
    'GET /collections'           => [StoreController::class, 'get_collections'],
    'GET /collections/{slug}'    => [StoreController::class, 'get_collection'],

    // ══════════════════════════════════════════════
    //  BANNERS — Public
    // ══════════════════════════════════════════════
    'GET /banners'               => [BannerController::class, 'get_banners'],

    // ══════════════════════════════════════════════
    //  BLOG — Public
    // ══════════════════════════════════════════════
    'GET /blog/posts'            => [BlogController::class, 'get_posts'],
    'GET /blog/posts/{slug}'     => [BlogController::class, 'get_post'],
    'GET /blog/categories'       => [BlogController::class, 'get_categories'],

    // ══════════════════════════════════════════════
    //  REVIEWS — Public
    // ══════════════════════════════════════════════
    'GET /reviews'               => [ReviewController::class, 'get_reviews'],
    'GET /reviews/stats'         => [ReviewController::class, 'get_stats'],

    // ══════════════════════════════════════════════
    //  CERTIFICATIONS — Public
    // ══════════════════════════════════════════════
    'GET /certifications'        => [CertController::class, 'get_all'],

    // ══════════════════════════════════════════════
    //  TAGS — Public
    // ══════════════════════════════════════════════
    'GET /tags'                  => [StoreController::class, 'get_tags'],

    // ══════════════════════════════════════════════
    //  FOOTER — Public
    // ══════════════════════════════════════════════
    'GET /footer'                => [FooterController::class, 'get_all'],

    // ══════════════════════════════════════════════
    //  CART — Authenticated
    // ══════════════════════════════════════════════
    'GET /cart'                  => [CartController::class, 'get_cart', 'auth'],
    'POST /cart/items'           => [CartController::class, 'add_item', 'auth'],
    'PUT /cart/items/{id}'       => [CartController::class, 'update_item', 'auth'],
    'DELETE /cart/items/{id}'    => [CartController::class, 'remove_item', 'auth'],

    // ══════════════════════════════════════════════
    //  WISHLIST — Authenticated
    // ══════════════════════════════════════════════
    'GET /wishlist'              => [WishlistController::class, 'get_wishlist', 'auth'],
    'POST /wishlist/items'       => [WishlistController::class, 'add_item', 'auth'],
    'PUT /wishlist/items/{id}'   => [WishlistController::class, 'update_item', 'auth'],
    'DELETE /wishlist/items/{id}' => [WishlistController::class, 'remove_item', 'auth'],
    'DELETE /wishlist'           => [WishlistController::class, 'clear_wishlist', 'auth'],
    'POST /wishlist/shared'      => [WishlistController::class, 'get_shared', 'auth'],

    // ══════════════════════════════════════════════
    //  COUPONS — Public + Auth
    // ══════════════════════════════════════════════
    'GET /coupons'               => [CouponController::class, 'list_coupons'],
    'POST /coupons/validate'     => [CouponController::class, 'validate', 'auth'],
    'POST /coupons/apply'        => [CouponController::class, 'apply', 'auth'],

    // ══════════════════════════════════════════════
    //  ORDERS — Authenticated
    // ══════════════════════════════════════════════
    'POST /orders'               => [OrderController::class, 'create_order', 'auth'],
    'GET /orders'                => [OrderController::class, 'get_orders', 'auth'],
    'GET /orders/{id}'           => [OrderController::class, 'get_order', 'auth'],
    'PUT /orders/{id}/cancel'    => [OrderController::class, 'cancel_order', 'auth'],

    // ══════════════════════════════════════════════
    //  PAYMENT — Authenticated
    // ══════════════════════════════════════════════
    'POST /payment/verify'       => [PaymentController::class, 'verify', 'auth'],
    'POST /payment/create-intent' => [PaymentController::class, 'create_intent', 'auth'],

    // ══════════════════════════════════════════════
    //  ADMIN AUTH
    // ══════════════════════════════════════════════
    'POST /admin/auth/login'     => [AdminAuth::class, 'login'],
    'GET /admin/auth/me'         => [AdminAuth::class, 'get_profile', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Products
    // ══════════════════════════════════════════════
    'GET /admin/products'              => [AdminController::class, 'list', 'admin'],
    'POST /admin/products'             => [AdminController::class, 'create', 'admin'],
    'PUT /admin/products/{id}'         => [AdminController::class, 'update', 'admin'],
    'DELETE /admin/products/{id}'      => [AdminController::class, 'delete', 'admin'],
    'PUT /admin/products/{id}/stock'   => [AdminController::class, 'update_stock', 'admin'],
    'POST /admin/products/bulk-delete' => [AdminController::class, 'bulk_delete', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Orders
    // ══════════════════════════════════════════════
    'GET /admin/orders'                 => [AdminController::class, 'list_orders', 'admin'],
    'GET /admin/orders/{id}'            => [AdminController::class, 'get_order', 'admin'],
    'PUT /admin/orders/{id}/status'     => [AdminController::class, 'update_order_status', 'admin'],
    'GET /admin/orders/stats'           => [DashboardController::class, 'get_stats', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Customers
    // ══════════════════════════════════════════════
    'GET /admin/customers'              => [AdminController::class, 'list_customers', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Categories
    // ══════════════════════════════════════════════
    'GET /admin/categories'             => [AdminController::class, 'list_categories', 'admin'],
    'POST /admin/categories'            => [AdminController::class, 'create_category', 'admin'],
    'PUT /admin/categories/{id}'        => [AdminController::class, 'update_category', 'admin'],
    'DELETE /admin/categories/{id}'     => [AdminController::class, 'delete_category', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Collections
    // ══════════════════════════════════════════════
    'GET /admin/collections'            => [AdminController::class, 'list_collections', 'admin'],
    'POST /admin/collections'           => [AdminController::class, 'create_collection', 'admin'],
    'PUT /admin/collections/{id}'       => [AdminController::class, 'update_collection', 'admin'],
    'DELETE /admin/collections/{id}'    => [AdminController::class, 'delete_collection', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Banners
    // ══════════════════════════════════════════════
    'GET /admin/banners'                => [AdminController::class, 'list_banners', 'admin'],
    'POST /admin/banners'               => [AdminController::class, 'create_banner', 'admin'],
    'PUT /admin/banners/{id}'           => [AdminController::class, 'update_banner', 'admin'],
    'DELETE /admin/banners/{id}'        => [AdminController::class, 'delete_banner', 'admin'],
    'PUT /admin/banners/reorder'        => [AdminController::class, 'reorder_banners', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Coupons
    // ══════════════════════════════════════════════
    'GET /admin/coupons'                => [AdminController::class, 'list_coupons', 'admin'],
    'POST /admin/coupons'               => [AdminController::class, 'create_coupon', 'admin'],
    'PUT /admin/coupons/{code}'         => [AdminController::class, 'update_coupon', 'admin'],
    'DELETE /admin/coupons/{code}'      => [AdminController::class, 'delete_coupon', 'admin'],
    'PUT /admin/coupons/{code}/toggle'  => [AdminController::class, 'toggle_coupon', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Blog
    // ══════════════════════════════════════════════
    'GET /admin/blog/posts'             => [AdminController::class, 'list_blog_posts', 'admin'],
    'POST /admin/blog/posts'            => [AdminController::class, 'create_blog_post', 'admin'],
    'PUT /admin/blog/posts/{id}'        => [AdminController::class, 'update_blog_post', 'admin'],
    'DELETE /admin/blog/posts/{id}'     => [AdminController::class, 'delete_blog_post', 'admin'],
    'GET /admin/blog/categories'        => [AdminController::class, 'list_blog_categories', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Tags
    // ══════════════════════════════════════════════
    'GET /admin/tags'                   => [AdminController::class, 'list_tags', 'admin'],
    'POST /admin/tags'                  => [AdminController::class, 'create_tag', 'admin'],
    'PUT /admin/tags/{tag}'             => [AdminController::class, 'update_tag', 'admin'],
    'DELETE /admin/tags/{tag}'          => [AdminController::class, 'delete_tag', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Reviews
    // ══════════════════════════════════════════════
    'GET /admin/reviews'                => [AdminController::class, 'list_reviews', 'admin'],
    'DELETE /admin/reviews/{id}'        => [AdminController::class, 'delete_review', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Certifications
    // ══════════════════════════════════════════════
    'GET /admin/certifications'         => [AdminController::class, 'list_certifications', 'admin'],
    'POST /admin/certifications'        => [AdminController::class, 'create_certification', 'admin'],
    'PUT /admin/certifications/{id}'    => [AdminController::class, 'update_certification', 'admin'],
    'DELETE /admin/certifications/{id}' => [AdminController::class, 'delete_certification', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Settings + Footer
    // ══════════════════════════════════════════════
    'GET /admin/settings'               => [SettingsController::class, 'get_all', 'admin'],
    'PUT /admin/settings'               => [SettingsController::class, 'update', 'admin'],
    'GET /admin/footer'                 => [FooterController::class, 'get_all_admin', 'admin'],
    'PUT /admin/footer'                 => [FooterController::class, 'update', 'admin'],

    // ══════════════════════════════════════════════
    //  ADMIN — Upload
    // ══════════════════════════════════════════════
    'POST /admin/upload'                => [UploadController::class, 'upload', 'admin'],
    'GET /admin/uploads'                => [UploadController::class, 'get_all', 'admin'],
    'DELETE /admin/uploads/{id}'        => [UploadController::class, 'delete', 'admin'],

    // ══════════════════════════════════════════════
    //  MIGRATION — Admin
    // ══════════════════════════════════════════════
    'POST /admin/migrate/{type}'        => [MigrationController::class, 'import', 'admin'],
    'GET /admin/migrate/status'         => [MigrationController::class, 'status', 'admin'],
    'DELETE /admin/migrate/{type}'      => [MigrationController::class, 'clear', 'admin'],
];
