<?php
/**
 * Newsletter signup band.
 *
 * The form is auto-wired by assets/js/common.js (any form with
 * data-nt-ajax-form) and submits through the generic 'lead_submit' AJAX action
 * registered in config/ajax.php - so there is no bespoke JS, nonce or handler
 * code here, and the form is genuinely live rather than decorative.
 *
 * Data: { tag, title (em allowed), sub, placeholder, button, note }
 */
defined( 'ABSPATH' ) || exit;

$nl_source = ( isset( $source ) && $source ) ? (string) $source : 'newsletter';
$data      = App_Data_Provider::get( $nl_source );
$title     = ( is_array( $data ) && ! empty( $data['title'] ) ) ? $data['title'] : '';
if ( '' === $title ) {
	return;
}
$tag         = $data['tag']         ?? '';
$sub         = $data['sub']         ?? '';
$placeholder = $data['placeholder'] ?? __( 'Enter your email address', NT_TEXT_DOMAIN );
$button      = $data['button']      ?? __( 'Subscribe', NT_TEXT_DOMAIN );
$note        = $data['note']        ?? '';
?>
<section class="app-newsletter">
	<div class="container app-newsletter__inner">

		<?php if ( $tag ) : ?><span class="app-newsletter__tag"><?php echo esc_html( $tag ); ?></span><?php endif; ?>
		<h2 class="app-newsletter__title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
		<?php if ( $sub ) : ?><p class="app-newsletter__sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>

		<form class="app-newsletter__form" data-nt-ajax-form="lead_submit">
			<input type="hidden" name="app_form_label" value="Newsletter signup">
			<label class="screen-reader-text" for="app-newsletter-email"><?php echo esc_attr( $placeholder ); ?></label>
			<input type="email" id="app-newsletter-email" name="email" required
			       class="app-newsletter__input"
			       placeholder="<?php echo esc_attr( $placeholder ); ?>">
			<button type="submit" class="btn app-newsletter__btn"><?php echo esc_html( $button ); ?></button>
		</form>

		<p class="app-form-status app-newsletter__status" role="status" aria-live="polite"></p>
		<?php if ( $note ) : ?><p class="app-newsletter__note"><?php echo esc_html( $note ); ?></p><?php endif; ?>

	</div>
</section>
