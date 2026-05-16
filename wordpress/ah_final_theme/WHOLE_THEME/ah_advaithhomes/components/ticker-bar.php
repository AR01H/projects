<?php defined( 'ABSPATH' ) || exit;

$items = [
	__( "We work for YOU — not the seller", 'ah-theme' ),
	__( "500+ successful UK home purchases", 'ah-theme' ),
	__( "Average £18,000 saved per buyer", 'ah-theme' ),
	__( "Nationwide coverage across England & Wales", 'ah-theme' ),
	__( "Free initial consultation — no obligation", 'ah-theme' ),
	__( "Expert negotiators on your side", 'ah-theme' ),
	__( "Independent of all estate agents", 'ah-theme' ),
	__( "Full legal & financial guidance", 'ah-theme' ),
];
// Duplicate for seamless infinite loop
$all = array_merge( $items, $items );
?>
<div class="ticker" aria-hidden="true">
  <div class="ticker__track">
    <?php foreach ( $all as $text ) : ?>
      <div class="ticker__item">
        <span class="ticker__dot"></span>
        <?php echo esc_html( $text ); ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
