<?php
/**
 * Stats bar - a row of headline numbers (farms / glasses / outlets / years).
 *
 * Self-contained + JSON-driven: reads admin/data/stats.json (key => number).
 * Renders nothing when there are no stats. Extracted from page-home so the
 * home page can be fully section-registry driven like every other page.
 *
 * Icons are generic emoji keyed by stat name; CSS tones them to the vintage
 * palette. To re-skin, edit stats.json (and add an icon mapping below if a new
 * key is introduced).
 */
defined( 'ABSPATH' ) || exit;

$stats = nt_data( 'stats' ) ?: array();
if ( empty( $stats ) || ! is_array( $stats ) ) {
	return;
}

$icons = array(
	'farms'   => '🌾',
	'glasses' => '🥤',
	'outlets' => '📍',
	'years'   => '🏆',
);
?>
<div class="nt-stats-bar">
	<div class="nt-stats-bar__inner container">
		<?php foreach ( $stats as $key => $value ) :
			$icon = $icons[ $key ] ?? '';
			// Format: 'M+' for very large counts (glasses), '+' otherwise.
			$display = is_int( $value ) ? number_format( $value ) : $value;
			if ( 'glasses' === $key && is_numeric( $value ) ) {
				$display = number_format( $value / 1000000 ) . 'M+';
			} else {
				$display = $display . '+';
			}
			$label = ucfirst( (string) $key );
		?>
			<div class="nt-stats-bar__item">
				<span class="nt-stats-bar__icon"><?php echo esc_html( $icon ); ?></span>
				<div>
					<div class="nt-stats-bar__num"><?php echo esc_html( $display ); ?></div>
					<div class="nt-stats-bar__label"><?php echo esc_html( $label ); ?></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
