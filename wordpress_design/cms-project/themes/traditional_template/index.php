<?php
/**
 * Final fallback template (required by WordPress).
 * Renders whatever the main query holds as a simple card list.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="app-container app-section">

	<?php App_Helpers::component( 'parts/page_header', array( 'title' => get_the_archive_title() ?: get_bloginfo( 'name' ) ) ); ?>

	<?php if ( have_posts() ) : ?>
		<div class="app-grid app-grid-3">
			<?php
			while ( have_posts() ) {
				the_post();
				App_Helpers::component( 'cards/post_card', array( 'post_id' => get_the_ID() ) );
			}
			?>
		</div>
		<div class="app-pagination"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', NT_TEXT_DOMAIN ); ?></p>
	<?php endif; ?>

</div>
<?php
get_footer();
