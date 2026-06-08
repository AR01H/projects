<?php
defined( 'ABSPATH' ) || exit;
$stats       = $args['stats']       ?? [];
$rating_num  = $args['rating_num']  ?? '4.9';
$client_stat = $args['client_stat'] ?? '500+';
if ( ! $stats ) return;
?>
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
          $stat = is_object( $stat ) ? (array) $stat : $stat;
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
