<?php
/**
 * Search results template.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="app-container app-section">

	<?php
	App_Helpers::component( 'banners/page_header', array(
		/* translators: %s: search query */
		'title'    => sprintf( __( 'Search results for "%s"', NT_TEXT_DOMAIN ), get_search_query() ),
		'subtitle' => sprintf( _n( '%d result found', '%d results found', (int) $GLOBALS['wp_query']->found_posts, NT_TEXT_DOMAIN ), (int) $GLOBALS['wp_query']->found_posts ),
	) );
	?>

	<form role="search" method="get" class="app-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search again...', NT_TEXT_DOMAIN ); ?>">
		<button type="submit" class="app-btn"><?php esc_html_e( 'Search', NT_TEXT_DOMAIN ); ?></button>
	</form>

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
		<p><?php esc_html_e( 'No results. Try different keywords.', NT_TEXT_DOMAIN ); ?></p>
	<?php endif; ?>

</div>
<?php
get_footer();
