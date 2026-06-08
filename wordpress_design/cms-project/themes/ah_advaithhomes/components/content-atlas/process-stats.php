<?php
defined( 'ABSPATH' ) || exit;
$process_steps = $args['process_steps'] ?? [];
$site_stats    = $args['site_stats']    ?? [];
$properties    = $args['properties']    ?? [];
?>
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_PROCESS_AND_NUMBERS ); ?>">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">Operational Snapshot</span>
      <h2 class="section__title">Process Steps, Stats, and Property Showcase</h2>
    </div>
    <div class="atlas-two-col">
      <div class="atlas-card" data-aos="fade-up">
        <h3>Process Steps</h3>
        <ul class="atlas-list">
          <?php if ( $process_steps ) : foreach ( $process_steps as $step ) :
            $step = is_object( $step ) ? (array) $step : (array) $step;
          ?>
            <li>
              <strong><?php echo esc_html( $step['num'] ?? '' ); ?> <?php echo esc_html( $step['title'] ?? '' ); ?></strong>
              <div class="atlas-muted"><?php echo esc_html( $step['desc'] ?? $step['description'] ?? '' ); ?></div>
            </li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No process steps found.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="atlas-card" data-aos="fade-up" data-delay="100">
        <h3>Showcase Data</h3>
        <div class="atlas-three-col">
          <?php if ( $site_stats ) : foreach ( $site_stats as $stat ) :
            $stat = is_object( $stat ) ? (array) $stat : (array) $stat;
          ?>
            <div class="atlas-mini-card">
              <strong style="display:block;font-size:1.5rem;font-family:var(--font-display);margin-bottom:6px;"><?php echo esc_html( $stat['num'] ?? '' ); ?></strong>
              <div class="atlas-muted"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <?php if ( $properties ) : ?>
        <div style="margin-top:18px;">
          <h4>Featured Properties</h4>
          <ul class="atlas-list">
            <?php foreach ( array_slice( $properties, 0, 4 ) as $property ) :
              $property = is_object( $property ) ? (array) $property : (array) $property;
            ?>
              <li>
                <strong><?php echo esc_html( trim( ( $property['emoji'] ?? '' ) . ' ' . ( $property['location'] ?? '' ) ) ); ?></strong>
                <div class="atlas-muted"><?php echo esc_html( trim( ( $property['price'] ?? '' ) . ' ' . ( $property['saved'] ?? '' ) ) ); ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
