<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<div class="ch-404" style="text-align:center;padding:8rem 2rem;">
		<div class="ch-404__icon" style="font-size:5rem;">🌿</div>
		<h1 class="ch-404__title">404 — Page Not Found</h1>
		<p class="ch-404__desc">Oops! This page seems to have been squeezed out. Let's get you back to something refreshing.</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-lime" style="margin-top:2rem;display:inline-block;">
			🥤 Back to Home
		</a>
	</div>
</main>

<?php get_footer(); ?>
