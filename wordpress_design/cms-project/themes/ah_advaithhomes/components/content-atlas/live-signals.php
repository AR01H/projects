<?php
defined( 'ABSPATH' ) || exit;
$news_items    = $args['news_items']    ?? [];
$trust_signals = $args['trust_signals'] ?? [];
?>
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_LIVE_SIGNALS ); ?>">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">Live Signals</span>
      <h2 class="section__title">What the Site Is Saying to Visitors Right Now</h2>
    </div>
    <div class="atlas-two-col">
      <div class="atlas-card" data-aos="fade-up">
        <h3>News Bar Items</h3>
        <ul class="atlas-list">
          <?php if ( $news_items ) : foreach ( $news_items as $item ) : ?>
            <li><?php echo esc_html( is_object( $item ) ? ( $item->text ?? '' ) : (string) $item ); ?></li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No news bar items found.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="atlas-card" data-aos="fade-up" data-delay="100">
        <h3>Trust Signals</h3>
        <div class="atlas-three-col">
          <?php if ( $trust_signals ) : foreach ( $trust_signals as $signal ) :
            $signal = is_object( $signal ) ? (array) $signal : (array) $signal;
          ?>
            <div class="atlas-mini-card">
              <div class="atlas-label"><?php echo esc_html( $signal['icon'] ?? 'Item' ); ?></div>
              <p><?php echo esc_html( $signal['text'] ?? '' ); ?></p>
            </div>
          <?php endforeach; else : ?>
            <p class="atlas-muted">No trust signals found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
