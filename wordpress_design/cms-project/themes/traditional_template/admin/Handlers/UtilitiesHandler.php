<?php
class TT_Utilities_Handler extends TT_Base_Handler {
    public static function handle_request() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        
        $utility_id = sanitize_key( $_POST['utility_id'] ?? '' );
        
        if ( $utility_id === 'flush_rewrite' ) {
            flush_rewrite_rules();
            self::redirect_success( 'Rewrite rules flushed successfully.' );
        } elseif ( $utility_id === 'clear_cache' ) {
            global $wpdb;
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'" );
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%'" );
            self::redirect_success( 'Transients and Cache cleared.' );
        } elseif ( $utility_id === 'db_health' ) {
            global $wpdb;
            $required = [
                'tt_settings', 'tt_faqs', 'tt_reviews', 'tt_team', 'tt_locations', 
                'tt_history', 'tt_drinks', 'tt_events_features', 'tt_nav', 
                'tt_gallery', 'tt_values', 'tt_certifications', 'tt_logo_strip',
                'tt_ticker', 'tt_process_steps', 'tt_delivery_products', 'tt_forms', 'tt_form_fields',
                'tt_newsbar', 'tt_spotlights', 'tt_custom_code', 'tt_redirects',
                'tt_admin_shortcuts', 'tt_visitor_stats', 'tt_workflows'
            ];
            $missing = [];
            foreach ($required as $table) {
                $table_name = $wpdb->prefix . $table;
                if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
                    $missing[] = $table;
                }
            }
            if (empty($missing)) {
                self::redirect_success( 'DB Health Check Passed: All 25 tables exist.' );
            } else {
                self::redirect_error( 'Missing tables: ' . implode(', ', $missing) );
            }
        } elseif ( $utility_id === 'clear_stats' ) {
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}tt_visitor_stats");
            self::redirect_success( 'Visitor stats cleared.' );
        } elseif ( $utility_id === 'clear_workflows' ) {
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}tt_workflows");
            self::redirect_success( 'Workflows cleared.' );
        }
        
        self::redirect_error( 'Invalid utility action.' );
    }
}
