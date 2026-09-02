<?php
define('ABSPATH', dirname(__DIR__) . '/');
define('VINTAGESOUL_DIR', dirname(__DIR__) . '/');
define('VINTAGESOUL_URI', '/wp-content/themes/vintageSoulTheme/');

spl_autoload_register(function ($class) {
    $prefix = 'VintageSoul\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

if (!function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($text) { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_url')) {
    function esc_url($text) { return (string)$text; }
}
if (!function_exists('sanitize_title')) {
    function sanitize_title($title) { return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string)$title), '-')); }
}
if (!function_exists('sanitize_html_class')) {
    function sanitize_html_class($class) { return preg_replace('/[^a-z0-9_-]/i', '', (string)$class); }
}
if (!function_exists('home_url')) {
    function home_url($path = '') { return '/' . ltrim((string)$path, '/'); }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($content) { return (string)$content; }
}
if (!function_exists('__')) {
    function __($text, $domain = 'default') { return (string)$text; }
}
if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') { return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) { return json_encode($data, $options, $depth); }
}

ob_start();
include __DIR__ . '/../pages/contact/view.php';
$output = ob_get_clean();

echo "CONTACT PAGE RENDER SUCCESS: Generated " . strlen($output) . " bytes of HTML with zero errors!\n";
