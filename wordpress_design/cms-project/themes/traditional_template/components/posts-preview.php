<?php
/**
 * Posts preview - the latest blog posts as vintage cards.
 *
 * The ONE section here that reads WordPress rather than JSON (posts live in WP).
 * Headings/labels still come from JSON so nothing is hardcoded.
 * Data: { tag, title (em allowed), sub, count, button, button_url }
 *
 * Renders nothing when the site has no published posts, so it is safe to leave
 * registered on a page before any post exists.
 */
defined( 'ABSPATH' ) || exit;

$pp_source = ( isset( $source ) && $source ) ? (string) $source : 'posts_preview';
$data      = App_Helpers::data( $pp_source );
$count     = isset( $data['count'] ) ? max( 1, (int) $data['count'] ) : 3;

$nt_pp_query = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $count,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );

if ( ! $nt_pp_query->have_posts() ) {
	wp_reset_postdata();
	return;
}

$tag        = $data['tag']        ?? '';
$title      = $data['title']      ?? '';
$sub        = $data['sub']        ?? '';
$button     = $data['button']     ?? '';
$button_url = $data['button_url'] ?? '';
?>
<section class="app-posts" id="latest-posts">
	<div class="container">

		<?php get_template_part( 'components/parts/section-header', null, array(
	'tag'   => $tag ?? '',
	'title' => $title ?? '',
	'body'  => $sub ?? ''
) ); ?>

		<div class="app-posts__grid">
			<?php
			while ( $nt_pp_query->have_posts() ) :
				$nt_pp_query->the_post();
				$thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
				?>
				<article class="app-posts__card">
					<a class="app-posts__link" href="<?php the_permalink(); ?>">
						<?php if ( $thumb ) : ?>
							<figure class="app-posts__media">
								<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
							</figure>
						<?php endif; ?>
						<span class="app-posts__date"><?php echo esc_html( get_the_date() ); ?></span>
						<h3 class="app-posts__title"><?php echo esc_html( get_the_title() ); ?></h3>
						<p class="app-posts__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
					</a>
				</article>
			<?php endwhile; ?>
		</div>

		<?php if ( $button && $button_url ) : ?>
			<p class="app-posts__cta">
				<a class="btn" href="<?php echo esc_url( App_Helpers::link( $button_url ) ); ?>"><?php echo esc_html( $button ); ?></a>
			</p>
		<?php endif; ?>

	</div>
</section>
<?php wp_reset_postdata(); ?>
