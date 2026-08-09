<?php
/**
 * Template Name: Blog
 *
 * The STANDARD blog archive - a plain, reusable WordPress posts listing.
 *
 * How it differs from pages/page-news.php: News is the bespoke, JS-driven
 * screen (live search + "Load More" through the REST route). This is the
 * conventional one - server-rendered, numbered pagination, category filter,
 * a sidebar - so it works with JavaScript off, is crawlable, and drops into
 * any site built on this theme without the REST route existing at all.
 *
 * Everything around the loop is data:
 *   admin/data/blog.json         headings, labels, sidebar switches
 *   admin/data/page_headers.json "blog" - the header board
 *   admin/data/page_sections.json "blog_before" / "blog_after" - any
 *                                reusable sections to run above/below the
 *                                listing (a promo block, a newsletter…)
 *
 * Registered as 'blog' in config/pages.php.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nt_blog     = App_Data_Provider::get( 'blog' );
$nt_per_page = max( 1, (int) ( $nt_blog['per_page'] ?? 9 ) );
$nt_paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

$nt_query = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $nt_per_page,
	'paged'               => $nt_paged,
	'ignore_sticky_posts' => false,
) );
?>
<div id="main-content" class="site-main app-blog">

	<?php
	// The page header board, then anything the JSON wants above the listing.
	app_render_sections( 'blog_before' );
	?>

	<div class="container app-blog__wrap">

		<div class="app-blog__main">

			<?php App_Helpers::component( 'parts/breadcrumbs' ); ?>

			<?php if ( ! empty( $nt_blog['intro'] ) ) : ?>
				<p class="app-blog__intro"><?php echo esc_html( $nt_blog['intro'] ); ?></p>
			<?php endif; ?>

			<?php if ( $nt_query->have_posts() ) : ?>

				<div class="app-blog__grid">
					<?php
					while ( $nt_query->have_posts() ) :
						$nt_query->the_post();
						App_Helpers::component( 'cards/post_card', array( 'post_id' => get_the_ID() ) );
					endwhile;
					?>
				</div>

				<?php
				// Standard numbered pagination - real links, so every page of
				// the archive is reachable and indexable.
				$nt_links = paginate_links( array(
					'total'     => (int) $nt_query->max_num_pages,
					'current'   => $nt_paged,
					'type'      => 'array',
					'mid_size'  => 1,
					'prev_text' => esc_html( NT_Ui::label( 'previous' ) ),
					'next_text' => esc_html( NT_Ui::label( 'next' ) ),
				) );

				if ( ! empty( $nt_links ) ) :
					?>
					<nav class="app-pagination" aria-label="<?php echo esc_attr( NT_Ui::aria( 'pagination', 'Pagination' ) ); ?>">
						<?php foreach ( $nt_links as $nt_link_html ) : ?>
							<?php echo wp_kses_post( $nt_link_html ); ?>
						<?php endforeach; ?>
					</nav>
					<?php
				endif;
				?>

			<?php else : ?>

				<?php
				app_alert( array(
					'tone' => 'note',
					'icon' => 'file',
					'body' => (string) ( $nt_blog['empty_text'] ?? '' ),
				) );
				?>

			<?php endif; ?>

			<?php wp_reset_postdata(); ?>
		</div>

		<?php if ( ! empty( $nt_blog['sidebar'] ) ) : ?>
			<aside class="app-blog__side">
				<?php App_Helpers::component( 'parts/blog-sidebar', array( 'config' => (array) $nt_blog['sidebar'] ) ); ?>
			</aside>
		<?php endif; ?>

	</div>

	<?php app_render_sections( 'blog_after' ); ?>

</div>
<?php get_footer(); ?>
