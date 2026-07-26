<?php
$config_path = 'C:/laragon/www/wp_advaithhomes/wp-config.php';
$config = file_get_contents($config_path);
// Replace WP_DEBUG false with true, and add WP_DEBUG_LOG
$config = str_replace(
    "define( 'WP_DEBUG', false );",
    "define( 'WP_DEBUG', true );\ndefine( 'WP_DEBUG_LOG', true );",
    $config
);
file_put_contents($config_path, $config);
echo "Debug enabled successfully\n";
