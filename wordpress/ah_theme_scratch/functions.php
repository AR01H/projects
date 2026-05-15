<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load helper files
require_once get_template_directory() . '/function-helpers/setup.php';
require_once get_template_directory() . '/function-helpers/enqueue.php';
require_once get_template_directory() . '/function-helpers/menus.php';
require_once get_template_directory() . '/function-helpers/components.php';
require_once get_template_directory() . '/function-helpers/assets.php';
require_once get_template_directory() . '/function-helpers/routes.php';
require_once get_template_directory() . '/function-helpers/theme-settings.php';
require_once get_template_directory() . '/function-helpers/meta-boxes.php';
require_once get_template_directory() . '/function-helpers/ajax-handlers.php';
require_once get_template_directory() . '/function-helpers/cpt.php';
require_once get_template_directory() . '/function-helpers/helpers.php';
require_once get_template_directory() . '/database/schema.php';
require_once get_template_directory() . '/database/seeder.php';
