<?php
// Activate the plugin
$_SERVER['HTTP_HOST'] = 'advaithhomes.co.uk.test';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('ABSPATH', 'C:/laragon/www/wp_advaithhomes/');
require_once ABSPATH . 'wp-blog-header.php';

// Check current active plugins
$active = get_option('active_plugins', []);
echo "Current active plugins:\n";
foreach ($active as $p) echo "  - $p\n";

// Add our plugin to active plugins
$plugin_path = 'cms_ecommerce_plugin_/cms_ecommerce_plugin_.php';
if (!in_array($plugin_path, $active)) {
    $active[] = $plugin_path;
    update_option('active_plugins', $active);
    echo "\nPlugin ACTIVATED: $plugin_path\n";
} else {
    echo "\nPlugin already active\n";
}

// Run activation hook
include_once ABSPATH . 'wp-admin/includes/plugin.php';
activate_plugin($plugin_path);
echo "Activation hook executed\n";

// Verify
$active = get_option('active_plugins', []);
echo "\nActive plugins now:\n";
foreach ($active as $p) echo "  - $p\n";
