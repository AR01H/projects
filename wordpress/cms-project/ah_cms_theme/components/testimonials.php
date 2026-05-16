<?php
defined( 'ABSPATH' ) || exit;

$reviews = ah_get_reviews( 6 );
if ( empty( $reviews ) ) return;
?>
<section class="section section--alt" aria-label="Client testimonials">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Client Stories</span>
      <h2 class="section__title">Real Results for Real Buyers</h2>
      <p class="section__desc" style="margin-inline:auto">
        Over 500 buyers have trusted us to find and secure their home. Here's what they say.
      </p>
    </div>

    <!-- Overall rating bar -->
    <div style="display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:40px">
      <?php ah_stars( 4.9 ); ?>
      <span style="font-size:1.1rem;font-weight:600">4.9 / 5</span>
      <span style="color:var(--text-muted);font-size:.875rem">from 500+ verified clients</span>
    </div>

    <div class="grid-3">
      <?php foreach ( $reviews as $i => $rev ) : ?>
      <div class="review-card" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 100; ?>">
        <?php ah_stars( (float) ( $rev->rating ?? 5 ) ); ?>
        <p class="review-card__quote">"<?php echo esc_html( $rev->review_text ?? $rev->body ?? '' ); ?>"</p>
        <?php if ( ! empty( $rev->result ) ) : ?>
          <div class="review-card__result"><?php echo esc_html( $rev->result ); ?></div>
        <?php endif; ?>
        <div>
          <div class="review-card__author"><?php echo esc_html( $rev->author_name ?? $rev->name ?? 'Anonymous' ); ?></div>
          <div class="review-card__location"><?php echo esc_html( $rev->location ?? '' ); ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:40px">
      <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="btn btn-outline">
        Read all stories →
      </a>
    </div>
  </div>
</section>
