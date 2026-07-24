<?php
/**
 * Ticker - a scrolling ribbon of short phrases.
 *
 * GENERIC: renders whatever short lines the JSON supplies (offers, values,
 * opening times, awards). Switch data per page with `source` so each page can
 * run its own ribbon from its own file.
 *
 * The list is duplicated once in the markup so the loop joins seamlessly; the
 * copy is aria-hidden so screen readers announce each phrase only once. The
 * animation is disabled under prefers-reduced-motion (see vintage.css), where
 * it degrades to a plain wrapping strip.
 *
 * Data: { items[] (strings), speed (seconds per loop, optional) }
 */
defined( 'ABSPATH' ) || exit;

$tk_source = ( isset( $source ) && $source ) ? (string) $source : 'ticker';
$data      = nt_data( $tk_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
$items     = array_values( array_filter( array_map( 'strval', $items ), 'strlen' ) );
if ( empty( $items ) ) {
	return;
}

$speed = isset( $data['speed'] ) ? (float) $data['speed'] : 0;
$speed = ( $speed > 0 ) ? min( 200, max( 8, $speed ) ) : 32;
$style = 'animation-duration:' . $speed . 's';
?>
<section class="nt-ticker" aria-label="<?php esc_attr_e( 'Highlights', NT_TEXT_DOMAIN ); ?>">
	<div class="nt-ticker__viewport">
		<?php for ( $pass = 0; $pass < 2; $pass++ ) : ?>
			<ul class="nt-ticker__track" style="<?php echo esc_attr( $style ); ?>"
				<?php echo ( 1 === $pass ) ? 'aria-hidden="true"' : ''; ?>>
				<?php foreach ( $items as $item ) : ?>
					<li class="nt-ticker__item">
						<span class="nt-ticker__text"><?php echo esc_html( $item ); ?></span>
						<span class="nt-ticker__sep" aria-hidden="true">&#10022;</span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endfor; ?>
	</div>
</section>
