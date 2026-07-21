<?php
/**
 * Template Name: News
 *
 * Registered as 'news' in config/pages.php. First page of posts is
 * server-rendered; search + "Load More" go through the REST route
 * NT.rest('posts') -> config/rest.php -> handlers/rest/posts.php.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nt_per_page = 6;
$nt_news     = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $nt_per_page,
	'ignore_sticky_posts' => true,
) );
$nt_hdr = nt_data( 'page_headers' )['news'] ?? array();
?>
<div class="nt-container nt-section">

	<?php
	nt_component( 'parts/page_header', array(
		'tag'      => $nt_hdr['tag']      ?? '',
		'icon'     => $nt_hdr['icon']     ?? '',
		'title'    => $nt_hdr['title']    ?? __( 'News & Updates', NT_TEXT_DOMAIN ),
		'subtitle' => $nt_hdr['subtitle'] ?? '',
		'image'    => $nt_hdr['image']    ?? '',
	) );
	?>

	<div class="nt-news-controls">
		<input type="search" placeholder="<?php esc_attr_e( 'Search news...', NT_TEXT_DOMAIN ); ?>" data-nt-news-search>
	</div>

	<div class="nt-grid nt-grid-3" data-nt-news-grid data-per-page="<?php echo esc_attr( (string) $nt_per_page ); ?>" data-total-pages="<?php echo esc_attr( (string) $nt_news->max_num_pages ); ?>">
		<?php
		while ( $nt_news->have_posts() ) {
			$nt_news->the_post();
			nt_component( 'cards/post_card', array( 'post_id' => get_the_ID() ) );
		}
		wp_reset_postdata();
		?>
	</div>

	<p class="nt-center">
		<button class="nt-btn" data-nt-news-more <?php echo ( $nt_news->max_num_pages <= 1 ) ? 'hidden' : ''; ?>><?php esc_html_e( 'Load More', NT_TEXT_DOMAIN ); ?></button>
	</p>
	<p class="nt-center nt-news-status" data-nt-news-status role="status" aria-live="polite"></p>

</div>
<?php
get_footer();
