<?php

defined( 'ABSPATH' ) || exit;

$id         = isset( $id ) ? sanitize_html_class( (string) $id ) : '';
$title      = isset( $title ) ? (string) $title : '';
$body       = isset( $body ) ? (string) $body : '';
$footer     = isset( $footer ) ? (string) $footer : '';
$fullscreen = ! empty( $fullscreen );

if ( '' === $id || '' === $title ) {
	return;
}
?>
<div class="dialog<?php echo $fullscreen ? ' dialog--fullscreen' : ''; ?>" id="<?php echo esc_attr( $id ); ?>" data-vs-dialog hidden>
	<div class="dialog__backdrop" data-vs-dialog-close></div>
	<div class="dialog__panel" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
		<header class="dialog__header">
			<h2 class="dialog__title" id="<?php echo esc_attr( $id ); ?>-title"><?php echo esc_html( $title ); ?></h2>
			<button type="button" class="dialog__close" data-vs-dialog-close aria-label="<?php esc_attr_e( 'Close', 'vintagesoul' ); ?>">&times;</button>
		</header>
		<?php if ( '' !== $body ) : ?>
			<div class="dialog__body"><?php echo wp_kses_post( $body ); ?></div>
		<?php endif; ?>
		<?php if ( '' !== $footer ) : ?>
			<footer class="dialog__footer"><?php echo wp_kses_post( $footer ); ?></footer>
		<?php endif; ?>
	</div>
</div>
