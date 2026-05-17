<?php
defined( 'ABSPATH' ) || exit;

$signals = ah_get_trust_signals();
if ( empty( $signals ) ) return;

// Double the items so the -50% translateX loop is seamless
$doubled = array_merge( $signals, $signals );
?>
<div class="trust-bar" aria-label="Why clients trust us">
  <div class="trust-bar__inner">
    <div class="trust-bar__track">
      <?php foreach ( $doubled as $i => $signal ) :
        $signal = is_object( $signal ) ? (array) $signal : $signal;
      ?>
      <div class="trust-bar__item">
        <span class="trust-bar__item-icon"><?php echo esc_html( $signal['icon'] ?? '' ); ?></span>
        <span><?php echo esc_html( $signal['text'] ?? '' ); ?></span>
      </div>
      <?php if ( $i < count( $doubled ) - 1 ) : ?>
      <span class="trust-bar__sep" aria-hidden="true">|</span>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>
