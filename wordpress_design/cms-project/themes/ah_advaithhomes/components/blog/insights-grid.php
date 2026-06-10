<?php
/**
 * Blog / Insights featured + grid + load more (mockup #4).
 * Reuses the .ghub-* card styles from guides-hub.css for a consistent look.
 * Args: blog_query (WP_Query), featured (WP_Post|null), paged (int), base_url.
 */
defined( 'ABSPATH' ) || exit;

$q        = $args['blog_query'] ?? null;
$featured = $args['featured']   ?? null;
$paged    = (int) ( $args['paged'] ?? 1 );
$base     = $args['base_url']   ?? get_permalink();

$card = static function ( $p ): void {
	$cats  = get_the_category( $p->ID );
	$cat   = $cats ? $cats[0] : null;
	$thumb = get_the_post_thumbnail_url( $p->ID, 'ah-card' ) ?: get_the_post_thumbnail_url( $p->ID, 'medium' ) ?: '';
	?>
	<a class="ghub-gcard" href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>">
		<span class="ghub-gcard__media"<?php echo $thumb ? ' style="background-image:url(\'' . esc_url( $thumb ) . '\')"' : ''; ?>>
			<?php if ( ! $thumb ) : ?><span class="ghub-gcard__ph" aria-hidden="true">📰</span><?php endif; ?>
			<?php if ( $cat ) : ?><em class="ghub-gcard__cat"><?php echo esc_html( $cat->name ); ?></em><?php endif; ?>
		</span>
		<span class="ghub-gcard__body">
			<span class="ghub-gcard__title"><?php echo esc_html( get_the_title( $p->ID ) ); ?></span>
			<?php $exc = get_the_excerpt( $p->ID ); if ( $exc ) : ?>
				<span class="ghub-gcard__desc"><?php echo esc_html( wp_trim_words( $exc, 16, '…' ) ); ?></span>
			<?php endif; ?>
			<span class="ghub-gcard__meta"><?php echo esc_html( get_the_date( '', $p->ID ) ); ?> · ⏱ <?php echo esc_html( ah_reading_time( $p->ID ) ); ?></span>
		</span>
	</a>
	<?php
};
?>
<div class="ghub-main bins-main">

  <?php if ( $featured && 1 === $paged ) :
    $f_thumb = get_the_post_thumbnail_url( $featured->ID, 'large' ) ?: get_the_post_thumbnail_url( $featured->ID, 'medium' ) ?: '';
    $f_cats  = get_the_category( $featured->ID );
    $f_exc   = get_the_excerpt( $featured->ID );
  ?>
  <a class="ghub-featured" href="<?php echo esc_url( get_permalink( $featured->ID ) ); ?>">
    <span class="ghub-featured__media"<?php echo $f_thumb ? ' style="background-image:url(\'' . esc_url( $f_thumb ) . '\')"' : ''; ?>>
      <?php if ( ! $f_thumb ) : ?><span class="ghub-gcard__ph" aria-hidden="true">🗞️</span><?php endif; ?>
    </span>
    <span class="ghub-featured__body">
      <em class="ghub-featured__label"><?php echo esc_html( $f_cats ? $f_cats[0]->name : 'Featured' ); ?></em>
      <span class="ghub-featured__title"><?php echo esc_html( get_the_title( $featured->ID ) ); ?></span>
      <?php if ( $f_exc ) : ?><span class="ghub-featured__desc"><?php echo esc_html( wp_trim_words( $f_exc, 26, '…' ) ); ?></span><?php endif; ?>
      <span class="ghub-featured__btn">Read Article <span aria-hidden="true">→</span></span>
    </span>
  </a>
  <?php endif; ?>

  <?php if ( $q instanceof WP_Query && $q->have_posts() ) : ?>
    <div class="ghub-grid">
      <?php while ( $q->have_posts() ) : $q->the_post(); $card( get_post() ); endwhile; wp_reset_postdata(); ?>
    </div>
    <?php if ( $paged < (int) $q->max_num_pages ) : ?>
      <div class="ghub-more">
        <a class="btn btn-primary" href="<?php echo esc_url( add_query_arg( 'pg', $paged + 1, $base ) ); ?>">Load More Articles</a>
      </div>
    <?php endif; ?>
  <?php else : ?>
    <p class="ghub-empty">No articles here yet - try another topic.</p>
  <?php endif; ?>

</div>
