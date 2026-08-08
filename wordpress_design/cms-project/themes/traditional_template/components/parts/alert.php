<?php
/**
 * components/parts/alert.php - inline alert / note box.
 *
 * The in-page half of the dialog system. Same tones, same icons, same
 * parchment language as parts/dialog.php, but it sits inside the content
 * instead of over it - form errors, "we are closed on Monday" notes,
 * highlighted tips inside an article.
 *
 * Rendered through NT_Alert::render() so every key below is guaranteed set.
 *
 *   app_alert( array( 'tone' => 'warning', 'title' => …, 'body' => … ) );
 *
 * Context: tone title body html icon link_label link_url dismissible
 *          compact dismiss_id class
 */

defined( 'ABSPATH' ) || exit;

if ( '' === trim( $title ) && '' === trim( $body ) && '' === trim( $html ) ) {
	return;
}

$nt_classes = trim(
	'app-alert app-alert--' . sanitize_html_class( $tone )
	. ( $compact ? ' app-alert--compact' : '' )
	. ' ' . $class
);
?>
<div class="<?php echo esc_attr( $nt_classes ); ?>"
     role="<?php echo ( 'error' === $tone || 'warning' === $tone ) ? 'alert' : 'note'; ?>"
	<?php if ( '' !== $dismiss_id ) : ?>data-nt-alert-remember="<?php echo esc_attr( $dismiss_id ); ?>"<?php endif; ?>>

	<span class="app-alert__icon" aria-hidden="true">
		<?php echo NT_Icons::get_or_text( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
	</span>

	<div class="app-alert__content">
		<?php if ( '' !== $title ) : ?>
			<p class="app-alert__title"><?php echo esc_html( $title ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $body ) : ?>
			<p class="app-alert__text"><?php echo esc_html( $body ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $html ) : ?>
			<div class="app-alert__rich"><?php echo wp_kses_post( $html ); ?></div>
		<?php endif; ?>

		<?php if ( '' !== $link_label && '' !== $link_url ) : ?>
			<a class="app-alert__link" href="<?php echo esc_url( App_Helpers::link( $link_url ) ); ?>">
				<?php echo esc_html( $link_label ); ?>
				<?php NT_Icons::render( 'arrow-right' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<?php if ( $dismissible ) : ?>
		<button type="button" class="app-alert__close" data-nt-alert-close
		        aria-label="<?php echo esc_attr( NT_Ui::label( 'dismiss' ) ); ?>">
			<?php NT_Icons::render( 'close' ); ?>
		</button>
	<?php endif; ?>
</div>
