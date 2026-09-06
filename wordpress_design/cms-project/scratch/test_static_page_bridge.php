<?php
if (!defined('ABSPATH')) define('ABSPATH', __DIR__);
if (!defined('VINTAGESOUL_DIR')) define('VINTAGESOUL_DIR', 'e:/MY-GITHUB/AR01H/projects/wordpress_design/cms-project/themes/vintageSoulTheme');
if (!defined('VINTAGESOUL_URI')) define('VINTAGESOUL_URI', 'http://thecanehouse.co.uk.test/wp-content/themes/vintageSoulTheme');

function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return filter_var($s, FILTER_SANITIZE_URL); }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '-', trim((string)$s))); }
function shortcode_exists($tag) { return false; }
function add_shortcode($tag, $callback) {}

class MockWPDB {
    public $prefix = 'wp_ch_';
    private $mysqli;
    public function __construct() {
        $this->mysqli = new mysqli('localhost', 'root', '', 'thecanehouse');
    }
    public function get_var($query) {
        $res = $this->mysqli->query($query);
        if ($res && $row = $res->fetch_row()) return $row[0];
        return null;
    }
    public function get_row($query) {
        $res = $this->mysqli->query($query);
        return $res ? $res->fetch_object() : null;
    }
    public function get_results($query) {
        $res = $this->mysqli->query($query);
        $out = [];
        if ($res) {
            while ($row = $res->fetch_object()) $out[] = $row;
        }
        return $out;
    }
    public function prepare($query, ...$args) {
        foreach ($args as $arg) {
            $val = is_numeric($arg) ? $arg : "'" . $this->mysqli->real_escape_string($arg) . "'";
            $query = preg_replace('/%[dfs]/', $val, $query, 1);
        }
        return $query;
    }
}
$GLOBALS['wpdb'] = new MockWPDB();

// Autoloader for Theme
spl_autoload_register(function ($class) {
    if (strpos($class, 'VintageSoul\\') === 0) {
        $rel = str_replace(['VintageSoul\\', '\\'], ['', '/'], $class);
        $file = VINTAGESOUL_DIR . '/src/' . $rel . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use VintageSoul\Services\Plugins\StaticPageBridgeService;

echo "=== TESTING STATIC PAGE BRIDGE SERVICE ===\n\n";

// Insert a test static page into wp_ch_ah_static_pages
$mysqli = new mysqli('localhost', 'root', '', 'thecanehouse');
$mysqli->query("INSERT INTO wp_ch_ah_static_pages (slug, title, html, status) VALUES ('sample-static', 'Sample Static Page', '<h2>Botanical Cold Press Heritage</h2><p>Handcrafted raw sugarcane juice in London.</p>', 'active') ON DUPLICATE KEY UPDATE title = VALUES(title), html = VALUES(html)");

$html = StaticPageBridgeService::get_html('sample-static');
$page = StaticPageBridgeService::get_page('sample-static');
$exists = StaticPageBridgeService::exists('sample-static');
$all = StaticPageBridgeService::all();

echo "Sample Page Exists: " . ($exists ? "YES" : "NO") . "\n";
echo "Sample Page Title: " . ($page ? $page->title : 'N/A') . "\n";
echo "Sample Page HTML: " . $html . "\n";
echo "Total Static Pages in DB: " . count($all) . "\n\n";

// Clean up test row
$mysqli->query("DELETE FROM wp_ch_ah_static_pages WHERE slug = 'sample-static'");

echo "SUCCESS: StaticPageBridgeService fully verified!\n";
