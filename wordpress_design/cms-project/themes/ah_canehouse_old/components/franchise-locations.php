<?php
/**
 * Franchise locations lime marquee strip.
 */
defined( 'ABSPATH' ) || exit;

$locations = ch_get_franchise_locations();
if ( empty( $locations ) ) return;
?>

<div class="ch-franchise-locations-strip" aria-hidden="true">
	<div class="ch-franchise-locations-track">
		<?php foreach ( $locations as $loc ) :
			$loc = (array) $loc;
		?>
			<div class="ch-fl-item">
				<span class="ch-fl-icon"><?php echo esc_html( $loc['icon'] ?? '📍' ); ?></span>
				<span class="ch-fl-name"><?php echo esc_html( $loc['name'] ?? '' ); ?></span>
			</div>
		<?php endforeach; ?>
		<?php foreach ( $locations as $loc ) :
			$loc = (array) $loc;
		?>
			<div class="ch-fl-item">
				<span class="ch-fl-icon"><?php echo esc_html( $loc['icon'] ?? '📍' ); ?></span>
				<span class="ch-fl-name"><?php echo esc_html( $loc['name'] ?? '' ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
