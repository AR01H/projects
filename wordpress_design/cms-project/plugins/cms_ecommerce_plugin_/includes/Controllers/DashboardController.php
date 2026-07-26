<?php
namespace CMS_ECOMMERCE\Controllers;

use CMS_ECOMMERCE\Helpers\Response;

if ( ! defined( 'ABSPATH' ) ) exit;

class DashboardController {
    public function get_stats( \WP_REST_Request $request ): \WP_REST_Response {
        global $wpdb;

        $total_products = (int) wp_count_posts( 'rh_product' )->publish ?? 0;
        $total_orders   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rh_orders" );
        $total_revenue  = (float) $wpdb->get_var( "SELECT COALESCE(SUM(total), 0) FROM {$wpdb->prefix}rh_orders WHERE status != 'cancelled'" );
        $total_customers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );

        $recent_orders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rh_orders ORDER BY created_at DESC LIMIT 5" );

        $orders_by_status = $wpdb->get_results( "SELECT status, COUNT(*) as count FROM {$wpdb->prefix}rh_orders GROUP BY status" );

        $revenue_by_month = $wpdb->get_results(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue
             FROM {$wpdb->prefix}rh_orders WHERE status != 'cancelled'
             GROUP BY month ORDER BY month DESC LIMIT 12"
        );

        return Response::success( [
            'totalProducts'   => $total_products,
            'totalOrders'     => $total_orders,
            'totalCustomers'  => $total_customers,
            'totalRevenue'    => $total_revenue,
            'revenueChange'   => 0,
            'orderChange'     => 0,
            'customerChange'  => 0,
            'productChange'   => 0,
            'recentOrders'    => $recent_orders,
            'topProducts'     => [],
            'ordersByStatus'  => $orders_by_status,
            'revenueByMonth'  => array_reverse( $revenue_by_month ),
        ]);
    }
}
