<?php
/**
 * Mortgages page - informational hub for the "Finance & Mortgages" category.
 * All content pulled from the DB (WP category + posts). No hardcoded copy
 * beyond labels/constants.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$cat_slug = 'finance-mortgages';
$term     = get_term_by( 'slug', $cat_slug, 'category' );
$paged    = max( 1, absint( $_GET['pg'] ?? 1 ) );

$q = $term ? new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'cat'            => $term->term_id,
	'posts_per_page' => 12,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
] ) : null;

// Featured = newest in category
$featured = ( $q && $q->have_posts() ) ? $q->posts[0] : null;

if ( ! function_exists( 'nhp_meta' ) ) {
	function nhp_meta( WP_Post $p ): array {
		$cats    = get_the_category( $p->ID );
		$cat     = $cats[0] ?? null;
		$thumb   = get_the_post_thumbnail_url( $p->ID, 'ah-card' )
			?: get_the_post_thumbnail_url( $p->ID, 'medium_large' ) ?: '';
		$excerpt = wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 22, '…' );
		$rt      = function_exists( 'ah_reading_time' ) ? ah_reading_time( $p->ID ) : '';
		return [ 'cat' => $cat, 'thumb' => $thumb, 'excerpt' => $excerpt, 'rt' => $rt,
		         'url' => get_permalink( $p->ID ), 'date' => get_the_date( 'M j, Y', $p->ID ) ];
	}
}
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Finance & Mortgages',
	'title'      => 'Understand UK',
	'title_em'   => 'Mortgages',
	'desc'       => 'Independent guides on mortgage rules, eligibility, rates, and the lending process - written to help you borrow with confidence.',
	'breadcrumb' => [
		[ 'Home', home_url( '/' ) ],
		[ 'Mortgages', '' ],
	],
] ); ?>

<div class="nhp-wrap">

<?php if ( $q && $q->have_posts() ) : ?>

<!-- Featured + intro -->
<?php if ( $featured ) : $fm = nhp_meta( $featured ); ?>
<section class="nhp-articles" style="padding-bottom:0">
  <div class="container">
    <a href="<?php echo esc_url( $fm['url'] ); ?>" class="nhp-mq-wide" data-aos="fade-up" style="--ac:#5a1a6a">
      <div class="nhp-mq-wide__img" style="<?php if ( $fm['thumb'] ) echo 'background-image:url(' . esc_url( $fm['thumb'] ) . ')'; ?>">
        <span class="nhp-mq-badge nhp-mq-badge--green">FEATURED</span>
      </div>
      <div class="nhp-mq-wide__body">
        <h2 class="nhp-mq-wide__title"><?php echo esc_html( get_the_title( $featured->ID ) ); ?></h2>
        <p class="nhp-mq-wide__excerpt"><?php echo esc_html( $fm['excerpt'] ); ?></p>
        <div class="nhp-mq-foot">
          <span class="nhp-mq-dots" aria-hidden="true"><i></i><i></i><i class="is-on"></i></span>
          <?php if ( $fm['rt'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $fm['rt'] ); ?></span><?php endif; ?>
        </div>
      </div>
    </a>
  </div>
</section>
<?php endif; ?>

<!-- Article grid -->
<section class="nhp-articles">
  <div class="container">
    <div class="nhp-section-head" data-aos="fade-up">
      <div>
        <span class="nhp-eyebrow">All Mortgage Guides</span>
        <h2 class="nhp-section-title">Browse Mortgage Articles</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/guides/?category=' . $cat_slug ) ); ?>" class="nhp-see-all">In Guides →</a>
    </div>
    <div class="nhp-articles__grid">
      <?php
      $i = 0;
      while ( $q->have_posts() ) : $q->the_post();
        $p = get_post(); $m = nhp_meta( $p );
        if ( $featured && $p->ID === $featured->ID ) continue;
      ?>
      <a href="<?php echo esc_url( $m['url'] ); ?>" class="nhp-article-card" data-cat="<?php echo esc_attr( $m['cat'] ? $m['cat']->slug : '' ); ?>"
         data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 70; ?>">
        <div class="nhp-article-card__img-wrap">
          <?php if ( $m['thumb'] ) : ?><img src="<?php echo esc_url( $m['thumb'] ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
          <?php else : ?><div class="nhp-article-card__ph">💷</div><?php endif; ?>
          <?php if ( $m['cat'] ) : ?><span class="nhp-pill nhp-pill--sm nhp-article-card__badge"><?php echo esc_html( $m['cat']->name ); ?></span><?php endif; ?>
        </div>
        <div class="nhp-article-card__body">
          <h3 class="nhp-article-card__title"><?php echo esc_html( get_the_title() ); ?></h3>
          <p class="nhp-article-card__excerpt"><?php echo esc_html( $m['excerpt'] ); ?></p>
          <div class="nhp-article-card__footer">
            <?php if ( $m['rt'] ) : ?><span class="nhp-article-card__time"><?php echo esc_html( $m['rt'] ); ?></span><?php endif; ?>
            <span class="nhp-article-card__read">Read guide <span aria-hidden="true">→</span></span>
          </div>
        </div>
      </a>
      <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>

    <?php if ( $q->max_num_pages > 1 ) :
      $links = paginate_links( [
        'base'    => add_query_arg( 'pg', '%#%', home_url( '/mortgages/' ) ),
        'format'  => '', 'current' => $paged, 'total' => $q->max_num_pages,
        'prev_text' => '← Prev', 'next_text' => 'Next →', 'type' => 'array',
      ] );
      if ( $links ) : ?>
      <nav class="pagination" style="margin-top:40px"><ul class="pagination__list">
        <?php foreach ( $links as $l ) echo '<li class="pagination__item">' . $l . '</li>'; ?>
      </ul></nav>
    <?php endif; endif; ?>
  </div>
</section>

<?php else : ?>
<section class="section"><div class="container text-center">
  <div style="font-size:3rem;margin-bottom:12px">💷</div>
  <h2 style="font-family:var(--font-display)">No mortgage guides yet</h2>
  <p style="color:var(--text-secondary)">Check back soon.</p>
  <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-outline" style="margin-top:16px">Browse all guides</a>
</div></section>
<?php endif; ?>

</div>

<?php get_template_part( 'components/cta-section', null, [] ); ?>
<?php get_footer(); ?>
