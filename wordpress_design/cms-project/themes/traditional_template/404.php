<?php
/**
 * 404 template. Note: URLs registered in config/pages.php or matched by
 * config/routes.php never reach this file - the router rescues them first.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="app-container app-section app-404">

	<h1 class="app-404-code">404</h1>
	<h2><?php esc_html_e( 'Page not found', NT_TEXT_DOMAIN ); ?></h2>
	<p><?php esc_html_e( 'The page you are looking for does not exist or has moved.', NT_TEXT_DOMAIN ); ?></p>

	<form role="search" method="get" class="app-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search the site...', NT_TEXT_DOMAIN ); ?>">
		<button type="submit" class="app-btn"><?php esc_html_e( 'Search', NT_TEXT_DOMAIN ); ?></button>
	</form>

	<p><a class="app-btn app-btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', NT_TEXT_DOMAIN ); ?></a></p>

</div>
<?php
get_footer();
