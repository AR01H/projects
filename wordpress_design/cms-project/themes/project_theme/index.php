<?php
/**
 * index.php - WordPress fallback template.
 *
 * WordPress requires this file. It is only rendered when no more specific
 * template (front-page.php, page-*.php, single.php, etc.) matches the request.
 *
 * In normal operation this file should never be seen. If it is, it means a
 * page was loaded without an assigned template - add the correct template and
 * assign it in WP Admin → Pages.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main style="padding:6rem 2rem;text-align:center;font-family:sans-serif;color:#1a2c4e">
	<h1 style="font-size:2rem;margin-bottom:1rem">Page Not Configured</h1>
	<p style="color:#5a6e8a;margin-bottom:2rem">
		This page doesn't have a template assigned yet.<br>
		Go to <strong>WP Admin → Pages</strong> and assign the correct template.
	</p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
	   style="display:inline-block;padding:12px 28px;background:#2a4a82;color:#fff;border-radius:100px;text-decoration:none;font-weight:700">
		Back to Home
	</a>
</main>

<?php get_footer(); ?>
