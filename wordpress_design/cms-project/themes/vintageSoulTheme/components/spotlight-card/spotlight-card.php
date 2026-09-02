<?php
/**
 * VintageSoulTheme - Reusable Spotlight Metrics Card Component
 *
 * A premium dark-botanical card showing key metrics / stats with
 * a badge, headline, metric rows, and summary paragraph.
 *
 * Props:
 *   badge  (string)  - Eyebrow badge text (e.g. "RAW BOTANICAL SCIENCE")
 *   title  (string)  - Headline (e.g. "100% LIVING PLANT WATER")
 *   metrics (array)  - Array of { val, unit, label } objects
 *   summary (string) - Closing paragraph text
 */
defined( 'ABSPATH' ) || exit;

$badge   = isset( $badge )   ? trim( (string) $badge )   : '';
$title   = isset( $title )   ? trim( (string) $title )   : '';
$metrics = isset( $metrics ) ? (array) $metrics           : array();
$summary = isset( $summary ) ? trim( (string) $summary ) : '';

if ( '' === $title && empty( $metrics ) ) {
	return;
}
?>
<div class="nutrition-spotlight-card">
	<?php if ( '' !== $badge || '' !== $title ) : ?>
		<div class="nutrition-spotlight-card__header">
			<?php if ( '' !== $badge ) : ?>
				<span class="spotlight-badge"><?php echo esc_html( $badge ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $title ) : ?>
				<h3 class="spotlight-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $metrics ) ) : ?>
		<div class="spotlight-metrics">
			<?php foreach ( $metrics as $metric ) :
				$val   = (string) ( $metric['val'] ?? '' );
				$unit  = (string) ( $metric['unit'] ?? '' );
				$label = (string) ( $metric['label'] ?? '' );
			?>
				<div class="spotlight-metric-item">
					<span class="spotlight-metric-val"><?php echo esc_html( $val ); ?><?php if ( '' !== $unit ) : ?><small><?php echo esc_html( $unit ); ?></small><?php endif; ?></span>
					<?php if ( '' !== $label ) : ?>
						<span class="spotlight-metric-lbl"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $summary ) : ?>
		<p class="spotlight-summary"><?php echo esc_html( $summary ); ?></p>
	<?php endif; ?>
</div>
