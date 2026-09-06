<?php
defined( 'ABSPATH' ) || exit;

// Theme override: Newsletter is disabled/hidden in this theme.
wp_safe_redirect( admin_url( 'admin.php?page=ah-dashboard' ) );
exit;
