<?php
// Test script - checks if plugin autoloader works
$_SERVER['HTTP_HOST'] = 'advaithhomes.co.uk.test';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Load WordPress
define('ABSPATH', 'C:/laragon/www/wp_advaithhomes/');
require_once ABSPATH . 'wp-blog-header.php';

// Test autoloader
echo "ABSPATH: " . (defined('ABSPATH') ? ABSPATH : 'NOT DEFINED') . "\n";
echo "CMS_ECOMMERCE_PATH: " . (defined('CMS_ECOMMERCE_PATH') ? CMS_ECOMMERCE_PATH : 'NOT DEFINED (plugin not loaded)') . "\n";

// Check if plugin is in active plugins
$active = get_option('active_plugins', []);
$found = false;
foreach ($active as $p) {
    if (strpos($p, 'cms_ecommerce') !== false) {
        $found = true;
        echo "Plugin is ACTIVE: $p\n";
        break;
    }
}
if (!$found) echo "Plugin is NOT in active_plugins list\n";

// Try to register routes manually
\CMS_ECOMMERCE\Core\Router::register();
echo "Router::register() completed without errors\n";

// List registered routes
$routes = rest_get_server()->get_routes();
$cms_routes = [];
foreach ($routes as $ns => $endpoints) {
    if (strpos($ns, 'cms/v1') !== false) {
        $cms_routes[] = $ns;
    }
}
echo "CMS routes found: " . count($cms_routes) . "\n";
foreach (array_slice($cms_routes, 0, 10) as $r) {
    echo "  - $r\n";
}
