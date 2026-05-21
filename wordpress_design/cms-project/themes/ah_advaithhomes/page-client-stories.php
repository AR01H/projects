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

<!-- ── Hero ──────────────────────────────────────────────────────────────── -->
<section class="page-hero page-hero--sm" aria-label="Client stories">
  <div class="container">
    <div class="page-hero__copy text-center" style="max-width:680px;margin-inline:auto" data-aos="fade-up">
      <span class="section__eyebrow">Client Stories</span>
      <h1 class="page-hero__title">Real Results for<br><em>Real Buyers</em></h1>
      <p class="page-hero__desc">
        We let our clients do the talking. Here's what over <?php echo esc_html( $client_stat ); ?> buyers
        have said about working with Advaith Homes.
      </p>
    </div>
  </div>
</section>

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
<section class="section" aria-label="Client reviews">
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
get_template_part( 'components/cta-section', null, [
  'title'     => 'Join ' . esc_html( $client_stat ) . ' Buyers<br><em>Who Bought with Confidence</em>',
  'desc'      => 'Book a free consultation and find out how we can help you buy the right property at the right price.',
  'cta_label' => 'Book a Free Call',
  'cta_url'   => home_url( '/contact/' ),
  'sec_label' => 'See Our Services',
  'sec_url'   => home_url( '/services/' ),
] );

get_footer();
