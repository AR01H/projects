<?php
// Redirect to home if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Location: ../' );
	exit;
}
// WordPress will use this as the fallback template when no more-specific template is found.
get_header();
?>
<main class="ah-main" id="main-content">
  <div class="ah-container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1><?php the_title(); ?></h1>
        <div><?php the_content(); ?></div>
      </article>
    <?php endwhile; else : ?>
      <p><?php esc_html_e( 'No content found.', 'ah-theme' ); ?></p>
    <?php endif; ?>
  </div>
</main>
<?php
get_footer();
