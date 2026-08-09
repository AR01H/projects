<?php
/**
 * Advanced CMS Frontend Integration
 * Handles Newsbar, Custom Code, Redirects, and Visitor Stats
 */

class TT_Frontend_Features {
    public static function init() {
        // 1. Redirects
        add_action('template_redirect', [self::class, 'handle_redirects'], 1);
        
        // 2. Custom Code Injection
        add_action('wp_head', [self::class, 'inject_custom_code_head'], 999);
        add_action('wp_footer', [self::class, 'inject_custom_code_footer'], 999);
        
        // 3. Newsbar
        add_action('wp_body_open', [self::class, 'render_newsbar'], 1);
        
        // 4. Visitor Stats Tracking
        add_action('wp', [self::class, 'track_visitor_stats']);
    }

    public static function handle_redirects() {
        if (is_admin()) return;
        
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        $current_path = parse_url($current_url, PHP_URL_PATH);
        if (!$current_path) return;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'tt_redirects';
        
        // Fetch active redirects (in a real scenario, this would be cached)
        $redirects = $wpdb->get_results("SELECT source_url, target_url, status_code FROM {$table_name} WHERE is_active = 1", ARRAY_A);
        
        if (empty($redirects)) return;
        
        foreach ($redirects as $rule) {
            $source = '/' . trim($rule['source_url'], '/');
            $target = trim($rule['target_url']);
            $status = intval($rule['status_code']) ?: 301;
            
            if ($current_path === $source || $current_path === $source . '/') {
                wp_redirect($target, $status);
                exit;
            }
        }
    }

    public static function inject_custom_code_head() {
        self::render_custom_code('head');
    }

    public static function inject_custom_code_footer() {
        self::render_custom_code('footer');
    }

    private static function render_custom_code($placement) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tt_custom_code';
        
        $codes = $wpdb->get_results($wpdb->prepare(
            "SELECT code_snippet FROM {$table_name} WHERE is_active = 1 AND placement = %s ORDER BY id ASC",
            $placement
        ));
        
        if (!empty($codes)) {
            echo "\n<!-- TT Custom Code: {$placement} -->\n";
            foreach ($codes as $code) {
                // Ensure output is unescaped HTML/JS as it is intentional code injection
                echo html_entity_decode($code->code_snippet, ENT_QUOTES, 'UTF-8') . "\n";
            }
            echo "<!-- /TT Custom Code -->\n";
        }
    }

    public static function render_newsbar() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tt_newsbar';
        
        $news_items = $wpdb->get_results("SELECT message, link_url FROM {$table_name} WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        
        if (empty($news_items)) return;
        
        echo '<div class="tt-newsbar" style="background: var(--trad-green); color: var(--trad-cream); text-align: center; padding: 10px; font-size: 0.9rem;">';
        foreach ($news_items as $item) {
            $msg = esc_html($item->message);
            if (!empty($item->link_url)) {
                echo '<a href="' . esc_url($item->link_url) . '" style="color: inherit; text-decoration: underline; margin: 0 15px;">' . $msg . '</a>';
            } else {
                echo '<span style="margin: 0 15px;">' . $msg . '</span>';
            }
        }
        echo '</div>';
    }
    
    public static function track_visitor_stats() {
        if (is_admin() || is_user_logged_in()) return; // Don't track admins
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'tt_visitor_stats';
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ip_hash = md5($ip . wp_salt());
        $url = $_SERVER['REQUEST_URI'] ?? '';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        
        $wpdb->insert(
            $table_name,
            [
                'url' => $url,
                'ip_hash' => $ip_hash,
                'user_agent' => $ua,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s']
        );
        
        // Optional: Keep table size reasonable (e.g. keep only last 10,000 rows)
        // $wpdb->query("DELETE FROM {$table_name} WHERE id NOT IN (SELECT id FROM (SELECT id FROM {$table_name} ORDER BY id DESC LIMIT 10000) foo)");
    }
}
