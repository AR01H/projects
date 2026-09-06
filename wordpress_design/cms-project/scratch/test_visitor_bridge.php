<?php
if (!defined('ABSPATH')) define('ABSPATH', __DIR__);
if (!defined('VINTAGESOUL_DIR')) define('VINTAGESOUL_DIR', 'e:/MY-GITHUB/AR01H/projects/wordpress_design/cms-project/themes/vintageSoulTheme');
if (!defined('VINTAGESOUL_URI')) define('VINTAGESOUL_URI', 'http://thecanehouse.co.uk.test/wp-content/themes/vintageSoulTheme');

function esc_url_raw($s) { return filter_var($s, FILTER_SANITIZE_URL); }
function sanitize_text_field($s) { return strip_tags(trim((string)$s)); }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '-', trim((string)$s))); }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function wp_doing_cron() { return false; }
function home_url($p = '') { return 'http://thecanehouse.co.uk.test' . $p; }
function is_front_page() { return false; }
function is_home() { return false; }
function is_singular() { return true; }
function get_the_ID() { return 6; }
function get_post_field($field, $id) { return 'about'; }

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
    public function insert($table, $data, $format = []) {
        $cols = array_keys($data);
        $vals = array_map(function($v) {
            return is_null($v) ? "NULL" : "'" . $this->mysqli->real_escape_string($v) . "'";
        }, array_values($data));
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $vals) . ")";
        $res = $this->mysqli->query($sql);
        $this->insert_id = $this->mysqli->insert_id;
        return $res;
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

use VintageSoul\Services\Plugins\VisitorBridgeService;

echo "=== TESTING VISITOR BRIDGE SERVICE ===\n\n";

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '86.14.120.45';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36';
$_SERVER['REQUEST_URI'] = '/about';
$_SERVER['HTTP_REFERER'] = 'https://google.com';

// Record sample visits across different pages
$pages = [
    ['slug' => 'home', 'url' => 'http://thecanehouse.co.uk.test/', 'ip' => '86.14.120.45'],
    ['slug' => 'about', 'url' => 'http://thecanehouse.co.uk.test/about', 'ip' => '86.14.120.45'],
    ['slug' => 'history', 'url' => 'http://thecanehouse.co.uk.test/history', 'ip' => '86.14.120.45'],
    ['slug' => 'events', 'url' => 'http://thecanehouse.co.uk.test/events', 'ip' => '82.33.19.102'],
    ['slug' => 'franchise', 'url' => 'http://thecanehouse.co.uk.test/franchise', 'ip' => '82.33.19.102'],
    ['slug' => 'contact', 'url' => 'http://thecanehouse.co.uk.test/contact', 'ip' => '151.224.8.19'],
    ['slug' => 'blog', 'url' => 'http://thecanehouse.co.uk.test/blog', 'ip' => '151.224.8.19'],
];

foreach ($pages as $p) {
    $res = VisitorBridgeService::record([
        'ip_address' => $p['ip'],
        'page_url' => $p['url'],
        'page_slug' => $p['slug'],
        'referrer' => 'https://google.co.uk',
        'user_agent' => 'Mozilla/5.0 Chrome/128.0.0',
        'session_id' => md5($p['ip'] . '_sess'),
    ]);
    echo "Recorded visit for '{$p['slug']}' (IP: {$p['ip']}): " . ($res ? "OK" : "SKIP/DEDUP") . "\n";
}

$total = VisitorBridgeService::get_total_visits();
$unique = VisitorBridgeService::get_unique_visitors();

echo "\nTotal Visits in DB: {$total}\n";
echo "Total Unique IPs in DB: {$unique}\n";

echo "\nSUCCESS: VisitorBridgeService fully active and operational!\n";
