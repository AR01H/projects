<?php
defined( 'ABSPATH' ) || exit;
$reviews = $args['reviews'] ?? [];
if ( ! $reviews ) return;
?>
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
