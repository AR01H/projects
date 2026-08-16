<?php

defined( 'ABSPATH' ) || exit;

$message     = isset( $message ) ? (string) $message : '';
if ( '' === trim( $message ) ) {
	return;
}

$allowed     = array( 'info', 'success', 'warning', 'danger' );
$variant_in  = isset( $variant ) ? (string) $variant : 'info';
$variant     = in_array( $variant_in, $allowed, true ) ? $variant_in : 'info';
$icon        = isset( $icon ) ? (string) $icon : '';
$dismissible = isset( $dismissible ) ? (bool) $dismissible : true;
$autodismiss = isset( $autodismiss ) ? (int) $autodismiss : 0;
?>
<div class="alert alert--<?php echo esc_attr( $variant ); ?>" role="alert" data-vs-alert
	<?php if ( $autodismiss > 0 ) : ?>data-vs-alert-autodismiss="<?php echo esc_attr( $autodismiss ); ?>"<?php endif; ?>>
	<?php if ( '' !== $icon ) : ?>
		<span class="alert__icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
	<?php endif; ?>
	<div class="alert__content"><?php echo esc_html( $message ); ?></div>
	<?php if ( $dismissible ) : ?>
		<button type="button" class="alert__dismiss" data-vs-alert-dismiss aria-label="<?php esc_attr_e( 'Dismiss', 'vintagesoul' ); ?>">&times;</button>
	<?php endif; ?>
</div>
