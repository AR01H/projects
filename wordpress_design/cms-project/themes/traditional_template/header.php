<?php
/**
 * Site <head> + opening chrome. Every template starts with get_header().
 */
defined( 'ABSPATH' ) || exit;

// The theme's virtual router (core/router.php) stamps nt_active_page on every
// request, including before a real WP "front page" option is configured -
// check both so the blended-header state works whether or not the site has
// been through Admin -> Pages -> Sync Now yet.
$nt_is_home = is_front_page() || 'home' === (string) get_query_var( 'nt_active_page' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( $nt_is_home ? 'design-traditional nt-hero-top' : 'design-traditional' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#nt-main"><?php esc_html_e( 'Skip to content', NT_TEXT_DOMAIN ); ?></a>

<?php nt_component( 'parts/main_header' ); ?>

<main id="nt-main" class="nt-main">
