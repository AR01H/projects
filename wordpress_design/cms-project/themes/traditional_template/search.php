<?php
/**
 * Search results template.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nt-container nt-section">

	<?php
	nt_component( 'parts/page_header', array(
		/* translators: %s: search query */
		'title'    => sprintf( __( 'Search results for "%s"', NT_TEXT_DOMAIN ), get_search_query() ),
		'subtitle' => sprintf( _n( '%d result found', '%d results found', (int) $GLOBALS['wp_query']->found_posts, NT_TEXT_DOMAIN ), (int) $GLOBALS['wp_query']->found_posts ),
	) );
	?>

	<form role="search" method="get" class="nt-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search again...', NT_TEXT_DOMAIN ); ?>">
		<button type="submit" class="nt-btn"><?php esc_html_e( 'Search', NT_TEXT_DOMAIN ); ?></button>
	</form>

	<?php if ( have_posts() ) : ?>
		<div class="nt-grid nt-grid-3">
			<?php
			while ( have_posts() ) {
				the_post();
				nt_component( 'cards/post_card', array( 'post_id' => get_the_ID() ) );
			}
			?>
		</div>
		<div class="nt-pagination"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<p><?php esc_html_e( 'No results. Try different keywords.', NT_TEXT_DOMAIN ); ?></p>
	<?php endif; ?>

</div>
<?php
get_footer();
