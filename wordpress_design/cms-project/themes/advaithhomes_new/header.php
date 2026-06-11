<?php
/**
 * header.php — HTML document opener.
 *
 * Called via get_header() from every page template.
 * Contains ONLY the generic document wrapper: DOCTYPE, <html>, <head>,
 * <body>, wp_body_open(). Site navigation is NOT here because it needs
 * page-level context ($ctx['chrome']); page templates load it themselves.
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="scroll-progress"></div>
