<?php
// -----------------------------------------------
// CONFIG — change only this section
// -----------------------------------------------

define('CORS_MODE', 'specific'); // 'all' or 'specific'

define('CORS_ALLOWED_DOMAINS', [
    'wp_advithhomes_project.test',
    'thecanehouse.co.uk.test',
    'unshaken-nutshell-aspire.ngrok-free.dev',
]);

// -----------------------------------------------
// DO NOT EDIT BELOW THIS LINE
// -----------------------------------------------

$current_host = '';

if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $current_host = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $current_host = $_SERVER['HTTP_HOST'];
}

// Auto-detect protocol from the actual incoming request
$protocol = 'http';

if (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
) {
    $protocol = 'https';
}

$allowed = (CORS_MODE === 'all') || in_array($current_host, CORS_ALLOWED_DOMAINS);

if ($allowed && $current_host) {
    $_SERVER['HTTP_HOST'] = $current_host;
    $_SERVER['HTTPS']     = ($protocol === 'https') ? 'on' : 'off';

    if (!defined('WP_HOME'))    define('WP_HOME',    $protocol . '://' . $current_host);
    if (!defined('WP_SITEURL')) define('WP_SITEURL', $protocol . '://' . $current_host);

    if (!defined('CONCATENATE_SCRIPTS')) define('CONCATENATE_SCRIPTS', false);
    if (!defined('FORCE_SSL_ADMIN'))     define('FORCE_SSL_ADMIN', false);

    // Store for use once WordPress has loaded
    $GLOBALS['_cors_host']     = $current_host;
    $GLOBALS['_cors_protocol'] = $protocol;

    // Register filters once WordPress is ready
    if ($protocol === 'http') {
        require_once ABSPATH . 'wp-includes/plugin.php';

        add_filter('script_loader_src', function($src) {
            $host = $GLOBALS['_cors_host'];
            return str_replace('https://' . $host, 'http://' . $host, $src);
        }, 99);

        add_filter('style_loader_src', function($src) {
            $host = $GLOBALS['_cors_host'];
            return str_replace('https://' . $host, 'http://' . $host, $src);
        }, 99);

        add_filter('admin_url', function($url) {
            $host = $GLOBALS['_cors_host'];
            return str_replace('https://' . $host, 'http://' . $host, $url);
        }, 99);
    }
}

// -----------------------------------------------
// USAGE
// -----------------------------------------------
/*
Include this file at the top of wp-config.php BEFORE wp-settings.php:
require_once __DIR__ . '/wp-content/themes/thecanehouse/cors-origin.php';

For ngrok tunnel:
ngrok http --host-header=rewrite wp_advithhomes_project.test:80
*/