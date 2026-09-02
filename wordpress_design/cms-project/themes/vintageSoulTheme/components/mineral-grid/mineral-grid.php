<?php
/**
 * VintageSoulTheme - Reusable Periodic Mineral / Apothecary Grid Component
 *
 * Renders a grid of element-style cards with symbol stamps, amounts, names and benefits.
 *
 * Props:
 *   minerals (array) - Array of { symbol, name, amount, benefit } objects
 */
defined( 'ABSPATH' ) || exit;

$minerals = isset( $minerals ) ? (array) $minerals : array();

if ( empty( $minerals ) ) {
	return;
}
?>
<div class="mineral-apothecary-grid">
	<?php foreach ( $minerals as $min ) : ?>
		<div class="mineral-apothecary-card frame--rough-cut">
			<div class="mineral-apothecary-card__top">
				<div class="mineral-symbol-stamp"><?php echo esc_html( (string) ( $min['symbol'] ?? '' ) ); ?></div>
				<span class="mineral-amount-badge"><?php echo esc_html( (string) ( $min['amount'] ?? '' ) ); ?></span>
			</div>
			<h4 class="mineral-name"><?php echo esc_html( (string) ( $min['name'] ?? '' ) ); ?></h4>
			<p class="mineral-benefit"><?php echo esc_html( (string) ( $min['benefit'] ?? '' ) ); ?></p>
		</div>
	<?php endforeach; ?>
</div>
