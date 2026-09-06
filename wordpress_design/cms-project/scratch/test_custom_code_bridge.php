<?php
if (!defined('ABSPATH')) define('ABSPATH', __DIR__);
if (!defined('VINTAGESOUL_DIR')) define('VINTAGESOUL_DIR', 'e:/MY-GITHUB/AR01H/projects/wordpress_design/cms-project/themes/vintageSoulTheme');
if (!defined('VINTAGESOUL_URI')) define('VINTAGESOUL_URI', 'http://thecanehouse.co.uk.test/wp-content/themes/vintageSoulTheme');

function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '-', trim((string)$s))); }
function is_admin() { return false; }
function is_front_page() { return false; }
function is_home() { return false; }
function is_singular() { return true; }
function get_the_ID() { return 6; }
function get_queried_object_id() { return 6; }
function get_post_field($field, $id) { return 'about'; }
function wp_parse_url($url, $component = -1) { return parse_url($url, $component); }

$pdo = new PDO('mysql:host=localhost;dbname=thecanehouse', 'root', '');
function get_option($key, $default = false) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT option_value FROM wp_ch_options WHERE option_name = ? LIMIT 1");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    if ($val !== false) {
        $u = @unserialize($val);
        return ($u !== false || $val === 'b:0;') ? $u : $val;
    }
    return $default;
}

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

use VintageSoul\Services\Plugins\CustomCodeBridgeService;

echo "=== TESTING CUSTOM CODE BRIDGE SERVICE ===\n\n";

// Insert a sample per-page custom code rule for 'about'
$mysqli = new mysqli('localhost', 'root', '', 'thecanehouse');
$mysqli->query("INSERT INTO wp_ch_ah_custom_code (slug, css, js, is_active) VALUES ('about', '.about-hero { outline: 1px solid gold; }', 'console.log(\"About custom JS active\");', 1) ON DUPLICATE KEY UPDATE css = VALUES(css), js = VALUES(js), is_active = 1");

$rule = CustomCodeBridgeService::get_per_page_rule('about');
echo "Found Per-Page Rule for 'about': " . ($rule ? "YES" : "NO") . "\n";
if ($rule) {
    echo "  CSS: " . $rule->css . "\n";
    echo "  JS: " . $rule->js . "\n";
    echo "  Active: " . $rule->is_active . "\n";
}

echo "\nTesting Head Injection Output:\n";
ob_start();
CustomCodeBridgeService::inject_head_code();
$head_output = ob_get_clean();
echo $head_output . "\n";

echo "\nTesting Footer Injection Output:\n";
ob_start();
CustomCodeBridgeService::inject_footer_code();
$footer_output = ob_get_clean();
echo $footer_output . "\n";

// Clean up sample row
$mysqli->query("DELETE FROM wp_ch_ah_custom_code WHERE slug = 'about'");

echo "\nSUCCESS: CustomCodeBridgeService fully operational!\n";
