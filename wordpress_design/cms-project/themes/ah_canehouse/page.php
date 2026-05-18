<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main" id="main-content">
	<?php while ( have_posts() ) : the_post(); ?>
		<div class="ch-page-hero">
			<div class="container">
				<h1 class="ch-page-hero__title"><?php the_title(); ?></h1>
			</div>
		</div>
		<div class="container ch-page-content">
			<?php the_content(); ?>
		</div>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
