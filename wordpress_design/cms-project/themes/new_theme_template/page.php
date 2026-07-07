<?php
/**
 * Default template for WP pages that have no registry entry
 * (editor-created content pages).
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nt-container nt-section">

	<?php
	while ( have_posts() ) {
		the_post();
		nt_component( 'parts/page_header', array( 'title' => get_the_title() ) );
		?>
		<article <?php post_class( 'nt-entry' ); ?>>
			<div class="nt-entry-content"><?php the_content(); ?></div>
		</article>
		<?php
	}
	?>

</div>
<?php
get_footer();
