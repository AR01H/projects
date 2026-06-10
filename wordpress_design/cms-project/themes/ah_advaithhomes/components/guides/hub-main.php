<?php
/**
 * Guides Hub main column (mockup #2): featured guide + "All Guides (N)" grid + load more.
 * Args: the full guides data array (featured_guide, all_guides_query, paged, base_url).
 */
defined( 'ABSPATH' ) || exit;

$featured = $args['featured_guide']   ?? null;
$q        = $args['all_guides_query'] ?? null;
$paged    = (int) ( $args['paged']    ?? 1 );
$base     = $args['base_url']         ?? get_permalink();
$total    = $q instanceof WP_Query ? (int) $q->found_posts : 0;
$sort     = sanitize_key( $_GET['sort'] ?? 'latest' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/* One guide card (grid). */
$card = static function ( $p ): void {
	$cats  = get_the_category( $p->ID );
	$cat   = $cats ? $cats[0] : null;
	$thumb = get_the_post_thumbnail_url( $p->ID, 'ah-card' ) ?: get_the_post_thumbnail_url( $p->ID, 'medium' ) ?: '';
	?>
	<a class="ghub-gcard" href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>">
		<span class="ghub-gcard__media"<?php echo $thumb ? ' style="background-image:url(\'' . esc_url( $thumb ) . '\')"' : ''; ?>>
			<?php if ( ! $thumb ) : ?><span class="ghub-gcard__ph" aria-hidden="true">📖</span><?php endif; ?>
			<?php if ( $cat ) : ?><em class="ghub-gcard__cat"><?php echo esc_html( $cat->name ); ?></em><?php endif; ?>
		</span>
		<span class="ghub-gcard__body">
			<span class="ghub-gcard__title"><?php echo esc_html( get_the_title( $p->ID ) ); ?></span>
			<?php $exc = get_the_excerpt( $p->ID ); if ( $exc ) : ?>
				<span class="ghub-gcard__desc"><?php echo esc_html( wp_trim_words( $exc, 16, '…' ) ); ?></span>
			<?php endif; ?>
			<span class="ghub-gcard__meta">⏱ <?php echo esc_html( ah_reading_time( $p->ID ) ); ?></span>
		</span>
	</a>
	<?php
};
?>
<div class="ghub-main">

  <!-- Featured guide -->
  <?php if ( $featured && 1 === $paged ) :
    $f_thumb = get_the_post_thumbnail_url( $featured->ID, 'large' ) ?: get_the_post_thumbnail_url( $featured->ID, 'medium' ) ?: '';
    $f_exc   = get_the_excerpt( $featured->ID );
  ?>
  <a class="ghub-featured" href="<?php echo esc_url( get_permalink( $featured->ID ) ); ?>">
    <span class="ghub-featured__media"<?php echo $f_thumb ? ' style="background-image:url(\'' . esc_url( $f_thumb ) . '\')"' : ''; ?>>
      <?php if ( ! $f_thumb ) : ?><span class="ghub-gcard__ph" aria-hidden="true">📘</span><?php endif; ?>
    </span>
    <span class="ghub-featured__body">
      <em class="ghub-featured__label">Featured Guide</em>
      <span class="ghub-featured__title"><?php echo esc_html( get_the_title( $featured->ID ) ); ?></span>
      <?php if ( $f_exc ) : ?><span class="ghub-featured__desc"><?php echo esc_html( wp_trim_words( $f_exc, 26, '…' ) ); ?></span><?php endif; ?>
      <span class="ghub-featured__btn">Read Guide <span aria-hidden="true">→</span></span>
    </span>
  </a>
  <?php endif; ?>

  <!-- All guides -->
  <div class="ghub-allhead">
    <h2 class="ghub-allhead__title">All Guides <?php if ( $total ) : ?><span class="ghub-allhead__count">(<?php echo (int) $total; ?>)</span><?php endif; ?></h2>
    <label class="ghub-sort">
      <span>Sort by:</span>
      <select onchange="if(this.value)window.location.href=this.value;">
        <option value="<?php echo esc_url( add_query_arg( 'sort', 'latest',  remove_query_arg( 'pg', $base ) ) ); ?>" <?php selected( $sort, 'latest' ); ?>>Latest</option>
        <option value="<?php echo esc_url( add_query_arg( 'sort', 'oldest',  remove_query_arg( 'pg', $base ) ) ); ?>" <?php selected( $sort, 'oldest' ); ?>>Oldest</option>
        <option value="<?php echo esc_url( add_query_arg( 'sort', 'popular', remove_query_arg( 'pg', $base ) ) ); ?>" <?php selected( $sort, 'popular' ); ?>>Most Popular</option>
      </select>
    </label>
  </div>

  <?php if ( $q instanceof WP_Query && $q->have_posts() ) : ?>
    <div class="ghub-grid">
      <?php while ( $q->have_posts() ) : $q->the_post(); $card( get_post() ); endwhile; wp_reset_postdata(); ?>
    </div>

    <?php if ( $paged < (int) $q->max_num_pages ) : ?>
      <div class="ghub-more">
        <a class="btn btn-primary" href="<?php echo esc_url( add_query_arg( 'pg', $paged + 1, $base ) ); ?>">Load More Guides</a>
      </div>
    <?php endif; ?>
  <?php else : ?>
    <p class="ghub-empty">No guides published yet - check back soon.</p>
  <?php endif; ?>

</div>
