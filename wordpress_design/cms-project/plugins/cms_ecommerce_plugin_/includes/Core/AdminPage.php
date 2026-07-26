<?php
namespace CMS_ECOMMERCE\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminPage {

    public static function register(): void {
        add_action( 'admin_menu', [ self::class, 'add_menus' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue' ] );
        add_action( 'wp_ajax_cms_migrate_import', [ self::class, 'ajax_import' ] );
        add_action( 'wp_ajax_cms_migrate_status', [ self::class, 'ajax_status' ] );
        add_action( 'wp_ajax_cms_migrate_clear', [ self::class, 'ajax_clear' ] );
    }

    public static function add_menus(): void {

        // ─── CMS ECom (Single top menu) ───
        add_menu_page(
            'CMS ECom',
            'CMS ECom',
            'manage_options',
            'cms-ecom',
            [ self::class, 'page_dashboard' ],
            'dashicons-admin-multisite',
            3
        );

        // ─── Overview ───
        add_submenu_page( 'cms-ecom', 'Dashboard',           'Dashboard',           'manage_options', 'cms-ecom',              [ self::class, 'page_dashboard' ] );

        // ─── Content ───
        add_submenu_page( 'cms-ecom', 'Home Page Banners',   'Home Page Banners',   'manage_options', 'cms-ecom-banners',      [ self::class, 'page_banners' ] );
        add_submenu_page( 'cms-ecom', 'Products',            'Products',            'manage_options', 'cms-ecom-products',     [ self::class, 'page_products' ] );
        add_submenu_page( 'cms-ecom', 'Categories',          'Categories',          'manage_options', 'cms-ecom-categories',   [ self::class, 'page_categories' ] );
        add_submenu_page( 'cms-ecom', 'Collections',         'Collections',         'manage_options', 'cms-ecom-collections',  [ self::class, 'page_collections' ] );
        add_submenu_page( 'cms-ecom', 'Blog Posts',          'Blog Posts',          'manage_options', 'cms-ecom-blog',         [ self::class, 'page_blog' ] );
        add_submenu_page( 'cms-ecom', 'Blog Categories',     'Blog Categories',     'manage_options', 'cms-ecom-blog-cats',    [ self::class, 'page_blog_categories' ] );
        add_submenu_page( 'cms-ecom', 'Tags',                'Tags',                'manage_options', 'cms-ecom-tags',         [ self::class, 'page_tags' ] );
        add_submenu_page( 'cms-ecom', 'Certifications',      'Certifications',      'manage_options', 'cms-ecom-certs',        [ self::class, 'page_certs' ] );
        add_submenu_page( 'cms-ecom', 'Footer',              'Footer',              'manage_options', 'cms-ecom-footer',       [ self::class, 'page_footer' ] );

        // ─── Commerce ───
        add_submenu_page( 'cms-ecom', 'Orders',              'Orders',              'manage_options', 'cms-ecom-orders',       [ self::class, 'page_orders' ] );
        add_submenu_page( 'cms-ecom', 'Coupons',             'Coupons',             'manage_options', 'cms-ecom-coupons',      [ self::class, 'page_coupons' ] );
        add_submenu_page( 'cms-ecom', 'Reviews',             'Reviews',             'manage_options', 'cms-ecom-reviews',      [ self::class, 'page_reviews' ] );

        // ─── Users ───
        add_submenu_page( 'cms-ecom', 'Users',               'Users',               'manage_options', 'cms-ecom-users',        [ self::class, 'page_users' ] );

        // ─── Tools ───
        add_submenu_page( 'cms-ecom', 'Import / Export',     'Import / Export',     'manage_options', 'cms-ecom-import',       [ self::class, 'page_import' ] );
        add_submenu_page( 'cms-ecom', 'Settings',            'Settings',            'manage_options', 'cms-ecom-settings',     [ self::class, 'page_settings' ] );
    }

    // ══════════════════════════════════════════════
    //  ENQUEUE
    // ══════════════════════════════════════════════

    public static function enqueue( $hook ): void {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'cms-ecom' ) === false ) return;
        wp_enqueue_style( 'cms-ecom', false );
        wp_add_inline_style( 'cms-ecom', self::get_css() );
    }

    // ══════════════════════════════════════════════
    //  DASHBOARD
    // ══════════════════════════════════════════════

    public static function page_dashboard(): void {
        global $wpdb;
        $product_count = wp_count_posts( 'rh_product' );
        $products = isset( $product_count->publish ) ? (int) $product_count->publish : 0;
        $blog_count = wp_count_posts( 'rh_blog' );
        $blog = isset( $blog_count->publish ) ? (int) $blog_count->publish : 0;
        $orders   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rh_orders" );
        $revenue  = (float) $wpdb->get_var( "SELECT COALESCE(SUM(total),0) FROM {$wpdb->prefix}rh_orders WHERE status != 'cancelled'" );
        $users    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
        $recent   = $wpdb->get_results( "SELECT o.*, u.display_name FROM {$wpdb->prefix}rh_orders o LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID ORDER BY o.created_at DESC LIMIT 5" );
        ?>
        <div class="wrap">
            <h1>CMS ECom — Dashboard</h1>
            <div class="cms-grid cms-stats">
                <div class="cms-stat-card"><span class="cms-stat-num"><?php echo $products; ?></span><span class="cms-stat-label">Products</span></div>
                <div class="cms-stat-card"><span class="cms-stat-num"><?php echo $orders; ?></span><span class="cms-stat-label">Orders</span></div>
                <div class="cms-stat-card"><span class="cms-stat-num">₹<?php echo number_format( $revenue, 2 ); ?></span><span class="cms-stat-label">Revenue</span></div>
                <div class="cms-stat-card"><span class="cms-stat-num"><?php echo $users; ?></span><span class="cms-stat-label">Users</span></div>
                <div class="cms-stat-card"><span class="cms-stat-num"><?php echo $blog; ?></span><span class="cms-stat-label">Blog Posts</span></div>
            </div>
            <div class="cms-grid cms-grid-2">
                <div class="cms-card">
                    <h2>Recent Orders</h2>
                    <?php if ( empty( $recent ) ) : ?>
                        <p class="description">No orders yet.</p>
                    <?php else : ?>
                        <table class="widefat striped">
                            <thead><tr><th>ID</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead>
                            <tbody>
                            <?php foreach ( $recent as $o ) : ?>
                                <tr>
                                    <td><code><?php echo esc_html( substr( $o->id, 0, 8 ) ); ?></code></td>
                                    <td><?php echo esc_html( $o->display_name ?? 'Guest' ); ?></td>
                                    <td><span class="cms-badge cms-<?php echo $o->status === 'cancelled' ? 'empty' : 'ok'; ?>"><?php echo esc_html( $o->status ); ?></span></td>
                                    <td>₹<?php echo number_format( $o->total, 2 ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div class="cms-card">
                    <h2>Quick Actions</h2>
                    <ul class="cms-quick-links">
                        <li><a href="?page=cms-ecom-import">Import / Export Data</a></li>
                        <li><a href="?page=cms-ecom-products">Manage Products</a></li>
                        <li><a href="?page=cms-ecom-orders">View All Orders</a></li>
                        <li><a href="?page=cms-ecom-banners">Manage Banners</a></li>
                        <li><a href="?page=cms-ecom-coupons">Manage Coupons</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  BANNERS
    // ══════════════════════════════════════════════

    public static function page_banners(): void {
        $banners = get_option( 'cms_banners', [] );
        ?>
        <div class="wrap">
            <h1>Home Page Banners</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $banners ); ?> banners</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Title</th><th>Subtitle</th><th>CTA Text</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $banners ) ) : ?>
                        <tr><td colspan="5" style="text-align:center;padding:30px;">No banners imported. <a href="?page=cms-ecom-import">Import data</a>.</td></tr>
                    <?php else : foreach ( $banners as $i => $b ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><strong><?php echo esc_html( $b['title'] ?? '' ); ?></strong></td>
                            <td><?php echo esc_html( $b['subtitle'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $b['ctaText'] ?? '' ); ?></td>
                            <td><span class="cms-badge cms-ok">Active</span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  PRODUCTS
    // ══════════════════════════════════════════════

    public static function page_products(): void {
        $posts = get_posts( [ 'post_type' => 'rh_product', 'posts_per_page' => 50, 'post_status' => 'publish' ] );
        ?>
        <div class="wrap">
            <h1>Products</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $posts ); ?> products</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Title</th><th>SKU</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $posts ) ) : ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;">No products imported. <a href="?page=cms-ecom-import">Import data</a>.</td></tr>
                    <?php else : foreach ( $posts as $i => $p ) :
                        $sku   = get_post_meta( $p->ID, '_rh_sku', true );
                        $price = get_post_meta( $p->ID, '_rh_price', true );
                        $stock = get_post_meta( $p->ID, '_rh_stock', true );
                    ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><strong><?php echo esc_html( $p->post_title ); ?></strong></td>
                            <td><code><?php echo esc_html( $sku ); ?></code></td>
                            <td>₹<?php echo esc_html( $price ); ?></td>
                            <td><?php echo esc_html( $stock ?? '—' ); ?></td>
                            <td><span class="cms-badge cms-ok">Published</span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  CATEGORIES
    // ══════════════════════════════════════════════

    public static function page_categories(): void {
        $cats = get_option( 'cms_categories', [] );
        ?>
        <div class="wrap">
            <h1>Categories</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $cats ); ?> categories</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Slug</th><th>Name</th><th>Parent</th><th>Description</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $cats ) ) : ?>
                        <tr><td colspan="5" style="text-align:center;padding:30px;">No categories imported.</td></tr>
                    <?php else : foreach ( $cats as $i => $c ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><code><?php echo esc_html( $c['slug'] ?? '' ); ?></code></td>
                            <td><strong><?php echo esc_html( $c['name'] ?? '' ); ?></strong></td>
                            <td><?php echo esc_html( $c['parent'] ?? '-' ); ?></td>
                            <td><?php echo esc_html( $c['description'] ?? '' ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  COLLECTIONS
    // ══════════════════════════════════════════════

    public static function page_collections(): void {
        $cols = get_option( 'cms_collections', [] );
        ?>
        <div class="wrap">
            <h1>Collections</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $cols ); ?> collections</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Slug</th><th>Name</th><th>Description</th><th>Products</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $cols ) ) : ?>
                        <tr><td colspan="5" style="text-align:center;padding:30px;">No collections imported.</td></tr>
                    <?php else : foreach ( $cols as $i => $c ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><code><?php echo esc_html( $c['slug'] ?? '' ); ?></code></td>
                            <td><strong><?php echo esc_html( $c['name'] ?? '' ); ?></strong></td>
                            <td><?php echo esc_html( $c['description'] ?? '' ); ?></td>
                            <td><?php echo count( $c['products'] ?? [] ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  BLOG
    // ══════════════════════════════════════════════

    public static function page_blog(): void {
        $posts = get_posts( [ 'post_type' => 'rh_blog', 'posts_per_page' => 50, 'post_status' => 'publish' ] );
        ?>
        <div class="wrap">
            <h1>Blog Posts</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $posts ); ?> blog posts</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Title</th><th>Slug</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $posts ) ) : ?>
                        <tr><td colspan="5" style="text-align:center;padding:30px;">No blog posts imported.</td></tr>
                    <?php else : foreach ( $posts as $i => $p ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><strong><?php echo esc_html( $p->post_title ); ?></strong></td>
                            <td><code><?php echo esc_html( $p->post_name ); ?></code></td>
                            <td><?php echo esc_html( $p->post_date ); ?></td>
                            <td><span class="cms-badge cms-ok">Published</span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function page_blog_categories(): void {
        $cats = get_option( 'cms_blog_categories', [] );
        ?>
        <div class="wrap">
            <h1>Blog Categories</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $cats ); ?> blog categories</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Slug</th><th>Name</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $cats ) ) : ?>
                        <tr><td colspan="3" style="text-align:center;padding:30px;">No blog categories imported.</td></tr>
                    <?php else : foreach ( $cats as $i => $c ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><code><?php echo esc_html( $c['slug'] ?? '' ); ?></code></td>
                            <td><strong><?php echo esc_html( $c['name'] ?? '' ); ?></strong></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  TAGS
    // ══════════════════════════════════════════════

    public static function page_tags(): void {
        $tags = get_option( 'cms_tags', [] );
        ?>
        <div class="wrap">
            <h1>Tags</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $tags ); ?> tags</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Slug</th><th>Name</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $tags ) ) : ?>
                        <tr><td colspan="3" style="text-align:center;padding:30px;">No tags imported.</td></tr>
                    <?php else : foreach ( $tags as $i => $t ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><code><?php echo esc_html( $t['slug'] ?? '' ); ?></code></td>
                            <td><strong><?php echo esc_html( $t['name'] ?? '' ); ?></strong></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  CERTIFICATIONS
    // ══════════════════════════════════════════════

    public static function page_certs(): void {
        $certs = get_option( 'cms_certifications', [] );
        ?>
        <div class="wrap">
            <h1>Certifications</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $certs ); ?> certifications</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Name</th><th>Issuer</th><th>Description</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $certs ) ) : ?>
                        <tr><td colspan="4" style="text-align:center;padding:30px;">No certifications imported.</td></tr>
                    <?php else : foreach ( $certs as $i => $c ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><strong><?php echo esc_html( $c['name'] ?? '' ); ?></strong></td>
                            <td><?php echo esc_html( $c['issuer'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $c['description'] ?? '' ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  FOOTER
    // ══════════════════════════════════════════════

    public static function page_footer(): void {
        $footer = get_option( 'cms_footer', [] );
        ?>
        <div class="wrap">
            <h1>Footer</h1>
            <div class="cms-card">
                <?php if ( empty( $footer ) ) : ?>
                    <p class="description">No footer data imported. <a href="?page=cms-ecom-import">Import data</a>.</p>
                <?php else : ?>
                    <table class="widefat striped">
                        <thead><tr><th>Section</th><th>Content</th></tr></thead>
                        <tbody>
                        <?php foreach ( $footer as $key => $val ) : ?>
                            <tr>
                                <td><strong><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ); ?></strong></td>
                                <td><pre style="margin:0;white-space:pre-wrap;max-height:200px;overflow:auto;"><?php echo esc_html( is_array( $val ) ? wp_json_encode( $val, JSON_PRETTY_PRINT ) : $val ); ?></pre></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  ORDERS
    // ══════════════════════════════════════════════

    public static function page_orders(): void {
        global $wpdb;
        $orders = $wpdb->get_results( "SELECT o.*, u.display_name, u.user_email FROM {$wpdb->prefix}rh_orders o LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID ORDER BY o.created_at DESC LIMIT 50" );
        ?>
        <div class="wrap">
            <h1>Orders</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $orders ); ?> orders</p>
                <table class="widefat striped">
                    <thead><tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Total</th><th>Payment</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $orders ) ) : ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;">No orders yet.</td></tr>
                    <?php else : foreach ( $orders as $o ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( substr( $o->id, 0, 8 ) ); ?></code></td>
                            <td><strong><?php echo esc_html( $o->display_name ?? 'Guest' ); ?></strong><br><small><?php echo esc_html( $o->user_email ?? '' ); ?></small></td>
                            <td><span class="cms-badge cms-<?php echo $o->status === 'cancelled' ? 'empty' : 'ok'; ?>"><?php echo esc_html( $o->status ); ?></span></td>
                            <td>₹<?php echo number_format( $o->total, 2 ); ?></td>
                            <td><?php echo esc_html( $o->payment_method ); ?></td>
                            <td><?php echo esc_html( $o->created_at ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  COUPONS
    // ══════════════════════════════════════════════

    public static function page_coupons(): void {
        $coupons = get_option( 'cms_coupons', [] );
        ?>
        <div class="wrap">
            <h1>Coupons</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $coupons ); ?> coupons</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Uses</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $coupons ) ) : ?>
                        <tr><td colspan="7" style="text-align:center;padding:30px;">No coupons imported.</td></tr>
                    <?php else : foreach ( $coupons as $i => $c ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><code><?php echo esc_html( $c['code'] ?? '' ); ?></code></td>
                            <td><?php echo esc_html( $c['type'] ?? '' ); ?></td>
                            <td><?php echo esc_html( $c['value'] ?? '' ); ?></td>
                            <td>₹<?php echo esc_html( $c['minOrder'] ?? '0' ); ?></td>
                            <td><?php echo esc_html( $c['usedCount'] ?? '0' ); ?> / <?php echo esc_html( $c['usageLimit'] ?? '∞' ); ?></td>
                            <td><?php echo ! empty( $c['isActive'] ) ? '<span class="cms-badge cms-ok">Active</span>' : '<span class="cms-badge cms-empty">Inactive</span>'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  REVIEWS
    // ══════════════════════════════════════════════

    public static function page_reviews(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'rh_reviews';
        $has_table = $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table;
        $reviews = $has_table ? $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 50" ) : [];
        ?>
        <div class="wrap">
            <h1>Reviews</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $reviews ); ?> reviews</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Product</th><th>User</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $reviews ) ) : ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;">No reviews yet.</td></tr>
                    <?php else : foreach ( $reviews as $i => $r ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><code><?php echo esc_html( $r->product_id ); ?></code></td>
                            <td><?php echo esc_html( $r->user_id ); ?></td>
                            <td>⭐ <?php echo esc_html( $r->rating ); ?></td>
                            <td><?php echo esc_html( substr( $r->comment ?? '', 0, 80 ) ); ?></td>
                            <td><?php echo esc_html( $r->created_at ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  USERS
    // ══════════════════════════════════════════════

    public static function page_users(): void {
        global $wpdb;
        $users = $wpdb->get_results( "SELECT ID, display_name, user_email, user_registered, user_status FROM {$wpdb->users} ORDER BY user_registered DESC LIMIT 50" );
        ?>
        <div class="wrap">
            <h1>Users</h1>
            <div class="cms-card">
                <p class="description">Total: <?php echo count( $users ); ?> users</p>
                <table class="widefat striped">
                    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Joined</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ( $users as $i => $u ) : ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><strong><?php echo esc_html( $u->display_name ); ?></strong></td>
                            <td><?php echo esc_html( $u->user_email ); ?></td>
                            <td><?php echo esc_html( date( 'd M Y', strtotime( $u->user_registered ) ) ); ?></td>
                            <td><?php echo $u->user_status == 0 ? '<span class="cms-badge cms-ok">Active</span>' : '<span class="cms-badge cms-empty">Inactive</span>'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  IMPORT / EXPORT
    // ══════════════════════════════════════════════

    public static function page_import(): void {
        $status = self::get_status();
        $types = [
            'products'        => 'Products',
            'categories'      => 'Categories',
            'collections'     => 'Collections',
            'banners'         => 'Banners',
            'blog_posts'      => 'Blog Posts',
            'blog_categories' => 'Blog Categories',
            'tags'            => 'Tags',
            'certifications'  => 'Certifications',
            'coupons'         => 'Coupons',
        ];
        ?>
        <div class="wrap">
            <h1>Import / Export</h1>
            <div class="cms-card">
                <h2>Data Import</h2>
                <p>Select which data types to import from your JSON files into WordPress.</p>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="cms-check-all" /></th>
                            <th>Data Type</th>
                            <th width="120">Status</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $types as $key => $label ) :
                            $count = $status[ $key ] ?? 0;
                        ?>
                        <tr>
                            <td><input type="checkbox" class="cms-check" value="<?php echo esc_attr( $key ); ?>" /></td>
                            <td><strong><?php echo esc_html( $label ); ?></strong></td>
                            <td>
                                <span class="cms-status" id="cms-status-<?php echo esc_attr( $key ); ?>">
                                    <?php if ( $count > 0 ) : ?>
                                        <span class="cms-badge cms-ok"><?php echo (int) $count; ?> items</span>
                                    <?php else : ?>
                                        <span class="cms-badge cms-empty">Not imported</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <button class="button button-small cms-import-btn" data-type="<?php echo esc_attr( $key ); ?>">
                                    <?php echo $count > 0 ? 'Re-import' : 'Import'; ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:15px;display:flex;gap:10px;align-items:center;">
                    <button id="cms-import-selected" class="button button-primary">Import Selected</button>
                    <button id="cms-import-all" class="button">Import All</button>
                    <button id="cms-clear-all" class="button button-link-delete" style="color:#a00;">Clear All Data</button>
                    <span id="cms-logger" class="cms-logger"></span>
                </div>
            </div>
        </div>
        <script>
        (function(){
            var ajaxurl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
            var nonce = '<?php echo esc_js( wp_create_nonce( 'cms_migrate' ) ); ?>';
            function log(msg, type) { document.getElementById('cms-logger').innerHTML = '<span class="cms-log cms-log-' + type + '">' + msg + '</span>'; }
            function updateStatus(type, count) {
                var el = document.getElementById('cms-status-' + type);
                el.innerHTML = count > 0 ? '<span class="cms-badge cms-ok">' + count + ' items</span>' : '<span class="cms-badge cms-empty">Not imported</span>';
            }
            function doImport(type) {
                return fetch(ajaxurl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=cms_migrate_import&type='+encodeURIComponent(type)+'&_wpnonce='+nonce }).then(function(r){ return r.json(); });
            }
            document.getElementById('cms-import-all').addEventListener('click', function() {
                var types = <?php echo wp_json_encode( array_keys( $types ) ); ?>, i = 0;
                log('Importing all...', 'info');
                (function next() { if (i >= types.length) { log('Done!', 'success'); return; } doImport(types[i]).then(function(r) { if (r.data) updateStatus(types[i], r.data.imported); i++; next(); }); })();
            });
            document.getElementById('cms-import-selected').addEventListener('click', function() {
                var checked = document.querySelectorAll('.cms-check:checked');
                if (!checked.length) { log('Select at least one', 'error'); return; }
                var types = Array.from(checked).map(function(c){return c.value;}), i = 0;
                log('Importing selected...', 'info');
                (function next() { if (i >= types.length) { log('Done!', 'success'); return; } doImport(types[i]).then(function(r) { if (r.data) updateStatus(types[i], r.data.imported); i++; next(); }); })();
            });
            document.querySelectorAll('.cms-import-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var t = this.dataset.type;
                    log('Importing ' + t + '...', 'info');
                    doImport(t).then(function(r) { if (r.data) { updateStatus(t, r.data.imported); log('Imported ' + r.data.imported + '/' + r.data.total + ' ' + t, 'success'); } });
                });
            });
            document.getElementById('cms-check-all').addEventListener('change', function() { document.querySelectorAll('.cms-check').forEach(function(c){ c.checked = this.checked; }.bind(this)); });
            document.getElementById('cms-clear-all').addEventListener('click', function() {
                if (!confirm('Delete ALL imported data?')) return;
                log('Clearing...', 'info');
                fetch(ajaxurl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=cms_migrate_clear&type=all&_wpnonce='+nonce }).then(function(r){ return r.json(); }).then(function(){ log('Cleared! Refreshing...', 'success'); setTimeout(function(){ location.reload(); }, 1000); });
            });
        })();
        </script>
        <?php
    }

    // ══════════════════════════════════════════════
    //  SETTINGS
    // ══════════════════════════════════════════════

    public static function page_settings(): void {
        $settings = get_option( 'cms_settings', [] );
        ?>
        <div class="wrap">
            <h1>Settings</h1>
            <div class="cms-card">
                <table class="widefat striped">
                    <thead><tr><th>Setting</th><th>Value</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $settings ) ) : ?>
                        <tr><td colspan="2" style="text-align:center;padding:30px;">No settings configured yet.</td></tr>
                    <?php else : foreach ( $settings as $key => $val ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ); ?></strong></td>
                            <td><?php echo esc_html( is_array( $val ) ? wp_json_encode( $val ) : $val ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════
    //  AJAX HANDLERS
    // ══════════════════════════════════════════════

    public static function ajax_import(): void {
        check_ajax_referer( 'cms_migrate' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );
        $type = sanitize_text_field( $_POST['type'] ?? '' );
        $controller = new \CMS_ECOMMERCE\Controllers\MigrationController();
        $request = new \WP_REST_Request( 'POST' );
        $request->set_param( 'type', $type );
        $result = $controller->import( $request );
        wp_send_json_success( $result->get_data()['data'] ?? $result->get_data() );
    }

    public static function ajax_status(): void {
        check_ajax_referer( 'cms_migrate' );
        $controller = new \CMS_ECOMMERCE\Controllers\MigrationController();
        $request = new \WP_REST_Request( 'GET' );
        $result = $controller->status( $request );
        wp_send_json_success( $result->get_data()['data'] ?? $result->get_data() );
    }

    public static function ajax_clear(): void {
        check_ajax_referer( 'cms_migrate' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );
        $type = sanitize_text_field( $_POST['type'] ?? 'all' );
        $controller = new \CMS_ECOMMERCE\Controllers\MigrationController();
        $request = new \WP_REST_Request( 'DELETE' );
        $request->set_param( 'type', $type );
        $controller->clear( $request );
        wp_send_json_success( [ 'cleared' => $type ] );
    }

    private static function get_status(): array {
        $controller = new \CMS_ECOMMERCE\Controllers\MigrationController();
        $request = new \WP_REST_Request( 'GET' );
        $result = $controller->status( $request );
        return $result->get_data()['data'] ?? [];
    }

    // ══════════════════════════════════════════════
    //  CSS
    // ══════════════════════════════════════════════

    private static function get_css(): string {
        return '
            .cms-grid { display: grid; gap: 20px; margin-bottom: 20px; }
            .cms-stats { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
            .cms-grid-2 { grid-template-columns: 1fr 1fr; }
            @media (max-width: 960px) { .cms-grid-2 { grid-template-columns: 1fr; } }
            .cms-stat-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center; }
            .cms-stat-num { display: block; font-size: 28px; font-weight: 700; color: #1d2327; }
            .cms-stat-label { display: block; font-size: 13px; color: #646970; margin-top: 5px; }
            .cms-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
            .cms-card h2 { margin-top: 0; }
            .cms-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
            .cms-ok { background: #d4edda; color: #155724; }
            .cms-empty { background: #f8d7da; color: #721c24; }
            .cms-logger { margin-left: 10px; }
            .cms-log { padding: 4px 10px; border-radius: 4px; font-size: 13px; }
            .cms-log-info { background: #d1ecf1; color: #0c5460; }
            .cms-log-success { background: #d4edda; color: #155724; }
            .cms-log-error { background: #f8d7da; color: #721c24; }
            .cms-status { min-width: 100px; display: inline-block; }
            .cms-quick-links { list-style: none; padding: 0; margin: 0; }
            .cms-quick-links li { padding: 8px 0; border-bottom: 1px solid #f0f0f1; }
            .cms-quick-links li:last-child { border-bottom: none; }
            .cms-quick-links a { text-decoration: none; color: #2271b1; font-weight: 500; }
            .cms-quick-links a:hover { color: #135e96; }
        ';
    }
}
