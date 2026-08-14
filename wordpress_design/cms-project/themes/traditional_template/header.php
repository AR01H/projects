<?php
/**
 * Site <head> + opening chrome. Every template starts with get_header().
 */
defined( 'ABSPATH' ) || exit;

// The theme's virtual router (core/router.php) stamps app_active_page on every
// request, including before a real WP "front page" option is configured -
// check both so the blended-header state works whether or not the site has
// been through Admin -> Pages -> Sync Now yet.
$nt_is_home = is_front_page() || 'home' === (string) get_query_var( 'app_active_page' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<?php
// Inner pages (everything except home) get `nt-inner`, which switches on the
// richer decorative layer: ornate curved headings, stronger sugarcane edge
// artwork and flourish dividers. See "INNER PAGE DECORATIVE LAYER" in
// assets/css/vintage.css. Home stays deliberately cleaner.
?>
<body <?php body_class( $nt_is_home ? 'design-traditional app-hero-top' : 'design-traditional app-inner' ); ?>>
<?php wp_body_open(); ?>



<?php App_Helpers::component( 'navigation/main_header' ); ?>

<main id="app-main" class="app-main">
