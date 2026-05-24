<?php
// -----------------------------------------------
// CONFIG — change only this section
// -----------------------------------------------

define('CORS_MODE', 'specific'); // 'all' or 'specific'

define('CORS_ALLOWED_DOMAINS', [
    'wp_advithhomes_project.test',
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

$local_domains = ['localhost', 'wp_advithhomes_project.test'];
$protocol = in_array($current_host, $local_domains) ? 'http' : 'https';

$allowed = (CORS_MODE === 'all') || in_array($current_host, CORS_ALLOWED_DOMAINS);

if ($allowed && $current_host) {
    $_SERVER['HTTP_HOST'] = $current_host;
    $_SERVER['HTTPS']     = ($protocol === 'https') ? 'on' : 'off';

    if (!defined('WP_HOME'))    define('WP_HOME',    $protocol . '://' . $current_host);
    if (!defined('WP_SITEURL')) define('WP_SITEURL', $protocol . '://' . $current_host);
}

// For setup with ngrox
/*
Incldue this file in the wp-config.php
require_once __DIR__ . '/wp-content/themes/ah_advaithhomes/cors-origin.php';
ngrok http --host-header=rewrite wp_advithhomes_project.test:80
**/

