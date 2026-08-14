<?php
/**
 * Reusable multi-step form modal.
 *
 * Wraps the generic multi-step form (components/forms/generic-multistep-form.php)
 * inside the lightweight vintage modal (assets/js/common.js data-nt-open /
 * .app-modal). One component powers every wizard - order, franchise, events -
 * you only change the JSON step config it is handed.
 *
 * Args:
 *   id      (string) Modal element id. A button elsewhere opens it with
 *                    data-nt-open="{id}". Required.
 *   title   (string) Heading inside the modal.
 *   sub     (string) Sub-heading line.
 *   config  (array)  The form config (steps/fields) - usually App_Helpers::data('form_x').
 *                    Its 'action' should be a registered ajax action (e.g.
 *                    'lead_submit'); 'form_label' names the form in the inbox.
 */
defined( 'ABSPATH' ) || exit;

$modal_id = $args['id'] ?? 'app-form-modal';
$title    = $args['title'] ?? '';
$sub      = $args['sub'] ?? '';
$config   = $args['config'] ?? array();

if ( empty( $config['steps'] ) ) {
	return;
}
?>
<div class="app-modal" id="<?php echo esc_attr( $modal_id ); ?>" aria-hidden="true">
	<div class="app-modal__box app-modal__box--wide" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $title ); ?>">
		<button type="button" class="app-modal__close" data-nt-close aria-label="<?php esc_attr_e( 'Close', NT_TEXT_DOMAIN ); ?>">&times;</button>
		<?php if ( $title ) : ?>
			<h3 class="app-modal__title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>
		<?php if ( $sub ) : ?>
			<p class="app-modal__sub"><?php echo esc_html( $sub ); ?></p>
		<?php endif; ?>

		<?php get_template_part( 'components/forms/generic-multistep-form', null, $config ); ?>
	</div>
</div>
