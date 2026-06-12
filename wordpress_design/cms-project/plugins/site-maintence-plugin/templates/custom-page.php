<?php
/**
 * Custom Page template (Landing Page mode).
 *
 * Available variables:
 *   $smm  TemplateData - helper object.
 *
 * @package SiteModeManager
 */

declare( strict_types=1 );

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \SiteModeManager\TemplateData $smm */

// Get the custom page HTML.
$custom_html = $smm->custom_page_html();
if ( ! empty( $custom_html ) ) {
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo esc_html( $smm->page_title( __( 'Custom Page', 'site-mode-manager' ) ) ); ?></title>
		<?php
		/**
		 * Action: smm_custom_page_head
		 * Add custom <head> content (analytics, fonts, etc.).
		 */
		do_action( 'smm_custom_page_head' );
		?>
	</head>
	<body>
		<?php
		// Output the custom HTML - already sanitized in settings.
		echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput
		?>
		<?php
		/**
		 * Action: smm_custom_page_footer
		 * Add scripts / pixels before </body>.
		 */
		do_action( 'smm_custom_page_footer' );
		?>
	</body>
	</html>
	<?php
	return;
}

// Fallback if no custom HTML is set - show a simple message.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $smm->page_title( __( 'Custom Page', 'site-mode-manager' ) ) ); ?></title>
	<style>
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
		html, body { height: 100%; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
		.page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; text-align: center; }
		.card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 1rem; padding: 3rem 2.5rem; max-width: 540px; }
		h1 { font-size: 2.5rem; margin-bottom: 1rem; }
		p { font-size: 1.1rem; opacity: 0.9; line-height: 1.6; }
	</style>
</head>
<body>
	<main class="page">
		<div class="card">
			<h1><?php esc_html_e( 'Custom Page', 'site-mode-manager' ); ?></h1>
			<p><?php esc_html_e( 'Add custom HTML content in the Site Mode Manager settings to display on this page.', 'site-mode-manager' ); ?></p>
		</div>
	</main>
</body>
</html>
