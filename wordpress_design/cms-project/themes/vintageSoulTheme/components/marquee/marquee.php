<?php

defined( 'ABSPATH' ) || exit;

$items       = isset( $items ) && is_array( $items ) ? array_values( array_filter( array_map( 'strval', $items ) ) ) : array();
$variant_in  = isset( $variant ) ? (string) $variant : '';
$variant     = in_array( $variant_in, array( 'a', 'b', 'c', 'd' ), true ) ? $variant_in : '';
$id          = isset( $id ) ? sanitize_html_class( (string) $id ) : '';
$extra_class = isset( $class ) ? implode( ' ', array_map( 'sanitize_html_class', array_filter( explode( ' ', (string) $class ) ) ) ) : '';

if ( empty( $items ) ) {
	return;
}

$text = implode( ' • ', $items ) . ' • ';

$classes = array( 'marquee' );
if ( '' !== $variant ) {
	$classes[] = 'marquee--' . $variant;
}
if ( '' !== $extra_class ) {
	$classes[] = $extra_class;
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo '' !== $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?>>
	<div class="marquee__track">
		<span><?php echo esc_html( $text ); ?></span>
		<span aria-hidden="true"><?php echo esc_html( $text ); ?></span>
	</div>
</div>
