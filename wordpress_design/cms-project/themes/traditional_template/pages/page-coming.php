<?php
/**
 * Template Name: Coming Soon
 *
 * Registered as 'coming-soon' in config/pages.php. All traffic lands here
 * while NT_COMING_SOON is true (config/theme.php); admins bypass the gate.
 * Standalone chrome on purpose - no site header/footer to leak navigation.
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'nt-coming' ); ?>>
<?php wp_body_open(); ?>

<div class="nt-coming-wrap">
	<h1><?php echo esc_html( NT_BRAND_NAME ); ?></h1>
	<p class="nt-coming-big"><?php esc_html_e( 'Something great is on the way.', NT_TEXT_DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We are working hard on the new site. Check back soon.', NT_TEXT_DOMAIN ); ?></p>
	<p class="nt-coming-contact"><?php echo esc_html( NT_BRAND_EMAIL ); ?> &middot; <?php echo esc_html( NT_BRAND_PHONE ); ?></p>
</div>

<?php wp_footer(); ?>
</body>
</html>
