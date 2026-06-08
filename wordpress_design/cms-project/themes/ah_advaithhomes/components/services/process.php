<?php
defined( 'ABSPATH' ) || exit;

$steps = $args['steps'] ?? [];
if ( ! $steps ) return;
?>
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_HOW_WE_WORK ); ?>">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">The Process</span>
      <h2 class="section__title">How We Work With You</h2>
      <p class="section__desc" style="margin-inline:auto">
        A clear, structured process with you in control at every step - from brief to completion.
      </p>
    </div>
    <div class="process-grid">
      <?php foreach ( $steps as $i => $step ) :
        $step = is_object( $step ) ? (array) $step : $step;
      ?>
      <div class="process-card" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 80; ?>">
        <div class="process-card__num"><?php echo esc_html( $step['num'] ?? sprintf( '%02d', $i + 1 ) ); ?></div>
        <div class="process-card__title"><?php echo esc_html( $step['title'] ); ?></div>
        <p class="process-card__desc"><?php echo esc_html( $step['desc'] ?? $step['description'] ?? '' ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
