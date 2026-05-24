<?php
/**
 * Template Name: Client Stories
 */
get_header();

$reviews = ah_get_reviews( 12 );
$stats   = ah_get_site_stats();

// Extract rating and count from stats
$rating_num  = '4.9';
$client_stat = '500+';
foreach ( $stats as $s ) {
  $s = is_object($s) ? (array) $s : $s;
  $n = $s['num'] ?? '';
  if ( strpos( $n, '★' ) !== false ) $rating_num  = rtrim( str_replace( '★', '', $n ) );
  if ( strpos( $n, '500' ) !== false ) $client_stat = $n;
}
?>

<?php get_template_part(
	'components/page-header',
	null,
	[
		'eyebrow'  => 'Client Stories',
		'title'    => 'Real Results for',
		'title_em' => 'Real Buyers',
		'desc'     => sprintf(
			"We let our clients do the talking. Here's what over %s buyers have said about working with %s.",
			esc_html( $client_stat ),
			esc_html( CLIENT_FULL_TITLE )
		),
		'badge'      => '',
		'breadcrumb' => [
			[ 'Home', home_url( '/' ) ],
			[ 'Client Stories', '' ],
		],
	]
); ?>

<!-- ── Overall Rating ────────────────────────────────────────────────────── -->
<?php if ( $stats ) : ?>
<div class="section section--sm" style="background:var(--bg-alt)">
  <div class="container">
    <div class="review-summary" data-aos="fade-up">
      <div class="review-summary__rating">
        <?php ah_stars( (float) $rating_num ); ?>
        <span class="review-summary__score"><?php echo esc_html( $rating_num ); ?> / 5</span>
        <span class="review-summary__count">from <?php echo esc_html( $client_stat ); ?> verified clients</span>
      </div>
      <div class="review-summary__stats">
        <?php foreach ( $stats as $stat ) :
          $stat = is_object($stat) ? (array) $stat : $stat;
        ?>
        <div class="review-summary__stat">
          <div class="review-summary__stat-num"><?php echo esc_html( $stat['num'] ?? '' ); ?></div>
          <div class="review-summary__stat-label"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Review Carousel ────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/review-carousel' ); ?>

<!-- ── Reviews Grid ───────────────────────────────────────────────────────── -->
<?php if ( $reviews ) : ?>
<section class="section" aria-label="<?php echo esc_attr( TXT_CLIENT_REVIEWS ); ?>">
  <div class="container">
    <div class="grid-3">
      <?php foreach ( $reviews as $i => $rev ) : ?>
      <div class="review-card" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 100; ?>">
        <?php ah_stars( (float) ( $rev->rating ?? 5 ) ); ?>
        <p class="review-card__quote">"<?php echo esc_html( $rev->review_text ?? '' ); ?>"</p>
        <?php if ( ! empty( $rev->short_desc ) ) : ?>
          <div class="review-card__result"><?php echo esc_html( $rev->short_desc ); ?></div>
        <?php endif; ?>
        <div>
          <div class="review-card__author"><?php echo esc_html( $rev->reviewer_name ?? 'Client' ); ?></div>
          <div class="review-card__location"><?php echo esc_html( $rev->reviewer_title ?? '' ); ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Newsletter prompt ─────────────────────────────────────────────────── -->
<?php
get_template_part( 'components/cta-section', null, [] );

get_footer();
