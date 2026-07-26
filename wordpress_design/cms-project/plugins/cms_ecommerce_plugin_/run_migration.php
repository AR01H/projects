<?php
$_SERVER['HTTP_HOST'] = 'advaithhomes.co.uk.test';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'POST';

define('ABSPATH', 'C:/laragon/www/wp_advaithhomes/');
require_once ABSPATH . 'wp-blog-header.php';

$controller = new CMS_ECOMMERCE\Controllers\MigrationController();
$request = new WP_REST_Request('POST');
$request->set_param('type', 'all');

$result = $controller->import($request);
echo "Migration result:\n";
print_r($result->get_data());
