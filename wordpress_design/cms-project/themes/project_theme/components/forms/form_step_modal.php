<?php
/**
 * form_step_modal — reusable multi-step modal shell (backdrop + progress bar + form).
 *
 * Pairs with assets/js/form-step-modal.js (window.ptStepModal controller).
 *
 * ob_start();
 * // … <div class="pt-bk-step" data-step="1"> … </div> blocks …
 * $steps_html = ob_get_clean();
 *
 * get_template_part( 'components/forms/form_step_modal', null, [
 *   'prefix'       => 'consult',                // → #pt-consult-modal, [data-consult-close]
 *   'form_id'      => 'pt-consult-form',
 *   'modal_label'  => 'Book a free consultation',
 *   'nonce_action' => 'pt_contact_nonce',
 *   'nonce_name'   => 'pt_consult_nonce_field',
 *   'steps'        => [ 'Your Brief', 'Your Details', 'Confirm' ],
 *   'steps_html'   => $steps_html,
 * ] );
 *
 * OPTIONS: prefix, form_id, modal_label, nonce_action, nonce_name, steps, steps_html
 */

defined( 'ABSPATH' ) || exit;

$prefix       = sanitize_html_class( $args['prefix'] ?? 'consult' );
$form_id      = $args['form_id']      ?? "pt-{$prefix}-form";
$modal_label  = $args['modal_label']  ?? '';
$nonce_action = $args['nonce_action'] ?? 'pt_contact_nonce';
$nonce_name   = $args['nonce_name']   ?? "pt_{$prefix}_nonce_field";
$steps        = is_array( $args['steps'] ?? null ) ? $args['steps'] : [];
$steps_html   = $args['steps_html']   ?? '';

$close_attr = "data-{$prefix}-close";
?>

<div class="pt-bk-modal" id="pt-<?php echo esc_attr( $prefix ); ?>-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $modal_label ); ?>">
	<div class="pt-bk-modal-backdrop" <?php echo esc_attr( $close_attr ); ?>></div>

	<div class="pt-bk-modal-box">
		<button type="button" class="pt-bk-modal-close" <?php echo esc_attr( $close_attr ); ?> aria-label="Close">&times;</button>

		<div class="pt-bk-modal-scroll">

			<div class="pt-bk-progress">
				<?php foreach ( $steps as $i => $lbl ) : ?>
					<div class="pt-bk-prog-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
						<div class="pt-bk-prog-dot"><?php echo $i + 1; ?></div>
						<span class="pt-bk-prog-label"><?php echo esc_html( $lbl ); ?></span>
					</div>
				<?php endforeach; ?>
				<div class="pt-bk-prog-line"><span class="pt-bk-prog-fill"></span></div>
			</div>

			<form id="<?php echo esc_attr( $form_id ); ?>" novalidate>
				<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
				<div id="pt-<?php echo esc_attr( $prefix ); ?>-msg" class="pt-form-feedback" style="display:none;" role="alert"></div>

				<?php echo $steps_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>

			</form>

		</div>
	</div>
</div>
