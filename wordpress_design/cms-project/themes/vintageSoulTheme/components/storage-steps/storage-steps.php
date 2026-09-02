<?php
/**
 * VintageSoulTheme - Reusable Storage / Step Cards Grid Component
 *
 * Renders numbered step cards in a grid — useful for storage tips,
 * freshness guides, process steps, or any numbered instruction set.
 *
 * Props:
 *   steps (array) - Array of { step, title, desc } objects
 */
defined( 'ABSPATH' ) || exit;

$steps = isset( $steps ) ? (array) $steps : array();

if ( empty( $steps ) ) {
	return;
}
?>
<div class="storage-steps-grid">
	<?php foreach ( $steps as $tip ) : ?>
		<div class="storage-step-card frame--rough-cut">
			<span class="storage-step-card__num"><?php echo esc_html( (string) ( $tip['step'] ?? '' ) ); ?></span>
			<h4 class="storage-step-card__title"><?php echo esc_html( (string) ( $tip['title'] ?? '' ) ); ?></h4>
			<p class="storage-step-card__desc"><?php echo esc_html( (string) ( $tip['desc'] ?? '' ) ); ?></p>
		</div>
	<?php endforeach; ?>
</div>
