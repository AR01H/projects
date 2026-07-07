<?php
/**
 * Site <head> + opening chrome. Every template starts with get_header().
 */
defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#nt-main"><?php esc_html_e( 'Skip to content', NT_TEXT_DOMAIN ); ?></a>

<?php nt_component( 'parts/main_header' ); ?>

<main id="nt-main" class="nt-main">
