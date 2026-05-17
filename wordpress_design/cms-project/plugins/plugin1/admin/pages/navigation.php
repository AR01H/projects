<?php
defined( 'ABSPATH' ) || exit;

$theme_nav = trailingslashit( get_template_directory() ) . 'admin/theme-nav.php';

if ( file_exists( $theme_nav ) ) {
	require $theme_nav;
	return;
}

echo '<div class="wrap ah-wrap"><h1>Navigation</h1><p>The active theme does not provide a navigation editor.</p></div>';
