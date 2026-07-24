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
$data      = nt_data( $pp_source );
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
<section class="nt-posts" id="latest-posts">
	<div class="container">

		<?php if ( $tag || $title || $sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?>
					<h2 class="section-title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
				<?php endif; ?>
				<?php if ( $sub ) : ?><p class="section-body"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-posts__grid">
			<?php
			while ( $nt_pp_query->have_posts() ) :
				$nt_pp_query->the_post();
				$thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
				?>
				<article class="nt-posts__card">
					<a class="nt-posts__link" href="<?php the_permalink(); ?>">
						<?php if ( $thumb ) : ?>
							<figure class="nt-posts__media">
								<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
							</figure>
						<?php endif; ?>
						<span class="nt-posts__date"><?php echo esc_html( get_the_date() ); ?></span>
						<h3 class="nt-posts__title"><?php echo esc_html( get_the_title() ); ?></h3>
						<p class="nt-posts__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
					</a>
				</article>
			<?php endwhile; ?>
		</div>

		<?php if ( $button && $button_url ) : ?>
			<p class="nt-posts__cta">
				<a class="btn" href="<?php echo esc_url( nt_link( $button_url ) ); ?>"><?php echo esc_html( $button ); ?></a>
			</p>
		<?php endif; ?>

	</div>
</section>
<?php wp_reset_postdata(); ?>
