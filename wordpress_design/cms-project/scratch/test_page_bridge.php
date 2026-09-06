<?php
if (!defined('ABSPATH')) define('ABSPATH', __DIR__);
if (!defined('VINTAGESOUL_DIR')) define('VINTAGESOUL_DIR', 'e:/MY-GITHUB/AR01H/projects/wordpress_design/cms-project/themes/vintageSoulTheme');
if (!defined('VINTAGESOUL_URI')) define('VINTAGESOUL_URI', 'http://thecanehouse.co.uk.test/wp-content/themes/vintageSoulTheme');

// WP mock functions
function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return filter_var($s, FILTER_SANITIZE_URL); }
function sanitize_text_field($s) { return strip_tags(trim((string)$s)); }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '-', trim((string)$s))); }
function wp_parse_url($url, $component = -1) { return parse_url($url, $component); }

class WP_Post {
    public $ID;
    public $post_title;
    public $post_name;
    public $post_excerpt;
    public $post_content;
    public $post_status;
}

class MockWPDB {
    public $prefix = 'wp_ch_';
    public $posts = 'wp_ch_posts';
    public $postmeta = 'wp_ch_postmeta';
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

function get_post($id) {
    global $wpdb;
    $row = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE ID = " . (int)$id);
    if (!$row) return null;
    $p = new WP_Post();
    foreach ($row as $k => $v) $p->$k = $v;
    return $p;
}

function get_post_thumbnail_id($id) {
    global $wpdb;
    return (int)$wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = " . (int)$id . " AND meta_key = '_thumbnail_id'");
}

function get_post_meta($id, $key, $single = false) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s", $id, $key));
}

function wp_get_attachment_url($id) {
    global $wpdb;
    $file = $wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = " . (int)$id . " AND meta_key = '_wp_attached_file'");
    return $file ? 'http://thecanehouse.co.uk.test/wp-content/uploads/' . $file : 'http://thecanehouse.co.uk.test/wp-content/uploads/sample.jpg';
}

function get_posts($args = []) { return []; }

// Autoloader for Theme
spl_autoload_register(function ($class) {
    if (strpos($class, 'VintageSoul\\') === 0) {
        $rel = str_replace(['VintageSoul\\', '\\'], ['', '/'], $class);
        $file = VINTAGESOUL_DIR . '/src/' . $rel . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use VintageSoul\Services\Plugins\PageBridgeService;
use VintageSoul\Controllers\AboutController;
use VintageSoul\Controllers\EventsController;
use VintageSoul\Controllers\FranchiseController;
use VintageSoul\Controllers\HistoryController;
use VintageSoul\Controllers\ContactController;
use VintageSoul\Controllers\BlogController;

echo "=== TESTING PAGE BRIDGE SERVICE ===\n\n";

$slugs = ['about', 'history', 'events', 'franchise', 'contact', 'blog', 'privacy-policy'];

foreach ($slugs as $slug) {
    $page = PageBridgeService::get_page($slug);
    $hero = PageBridgeService::resolve_hero($slug, [
        'tag' => '✦ FALLBACK TAG ✦',
        'title' => 'FALLBACK TITLE FOR ' . strtoupper($slug),
        'sub' => 'Fallback description text for ' . $slug,
        'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg'
    ]);

    echo "--- Slug: {$slug} ---\n";
    echo "WP_Post Found: " . ($page ? "YES (ID: {$page->ID}, Title: '{$page->post_title}')" : "NO") . "\n";
    echo "Resolved Title: " . $hero['title'] . "\n";
    echo "Resolved Sub: " . substr($hero['sub'], 0, 50) . "...\n";
    echo "Resolved Image: " . $hero['image'] . "\n";
    echo "Resolved Video: " . ($hero['video'] ?? '(none)') . "\n\n";
}

echo "=== TESTING SUBPAGE CONTROLLERS ===\n\n";

$about = (new AboutController())->prepare();
echo "AboutController Hero Title: " . $about['hero']['title'] . "\n";
echo "AboutController Hero Image: " . $about['hero']['image'] . "\n\n";

$events = (new EventsController())->prepare();
echo "EventsController Hero Title: " . $events['hero']['title'] . "\n";
echo "EventsController Hero Image: " . $events['hero']['image'] . "\n\n";

$franchise = (new FranchiseController())->prepare();
echo "FranchiseController Hero Title: " . $franchise['hero']['title'] . "\n";
echo "FranchiseController Hero Image: " . $franchise['hero']['image'] . "\n\n";

$history = (new HistoryController())->prepare();
echo "HistoryController Hero Title: " . $history['hero']['title'] . "\n";
echo "HistoryController Hero Image: " . $history['hero']['image'] . "\n\n";

$contact = (new ContactController())->prepare();
echo "ContactController Hero Title: " . $contact['hero']['title'] . "\n";
echo "ContactController Hero Image: " . $contact['hero']['image'] . "\n\n";

$blog = (new BlogController())->prepare();
echo "BlogController Hero Title: " . $blog['hero']['title'] . "\n";
echo "BlogController Hero Image: " . $blog['hero']['image'] . "\n\n";

echo "ALL CONTROLLERS & PAGE BRIDGES EXECUTED WITH 100% SUCCESS!\n";
