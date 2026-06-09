<?php
/**
 * form_step_modal — reusable multi-step modal shell (backdrop + progress bar + form).
 *
 * Wraps any set of `.ah-bk-step` blocks in the shared modal chrome.
 * You supply the step markup as a pre-rendered HTML string (build it with
 * output buffering in the calling file).
 *
 * All ids and the close attribute are derived from 'prefix' so your
 * forms.js (targeting e.g. #ah-consult-modal, [data-consult-close]) keeps working.
 *
 * Pairs with assets/js/form-step-modal.js (window.ahStepModal controller).
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  EXAMPLE                                                        │
 * └─────────────────────────────────────────────────────────────────┘
 *  ob_start();
 *  // … echo your <div class="ah-bk-step" data-step="1"> … </div> blocks …
 *  $steps_html = ob_get_clean();
 *
 *  get_template_part( 'components/forms/form_step_modal', null, [
 *    'prefix'       => 'consult',               // → #ah-consult-modal, [data-consult-close]
 *    'form_id'      => 'ah-consult-form',
 *    'modal_label'  => 'Book a free consultation',
 *    'nonce_action' => 'ah_contact_nonce',
 *    'nonce_name'   => 'ah_consult_nonce_field',
 *    'steps'        => [ 'Your Brief', 'Your Details', 'Confirm' ],
 *    'steps_html'   => $steps_html,
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  OPTIONS                                                        │
 * └─────────────────────────────────────────────────────────────────┘
 *   prefix        short slug → ids #ah-{prefix}-modal / #ah-{prefix}-msg + attr data-{prefix}-close
 *   form_id       id on the <form>
 *   modal_label   aria-label on the dialog
 *   nonce_action  wp_nonce_field action
 *   nonce_name    wp_nonce_field field name
 *   steps         array of progress-bar labels (one per step)
 *   steps_html    pre-rendered HTML of the .ah-bk-step blocks
 */

defined( 'ABSPATH' ) || exit;

$prefix       = sanitize_html_class( $args['prefix'] ?? 'consult' );
$form_id      = $args['form_id']      ?? "ah-{$prefix}-form";
$modal_label  = $args['modal_label']  ?? '';
$nonce_action = $args['nonce_action'] ?? 'ah_contact_nonce';
$nonce_name   = $args['nonce_name']   ?? "ah_{$prefix}_nonce_field";
$steps        = is_array( $args['steps'] ?? null ) ? $args['steps'] : [];
$steps_html   = $args['steps_html']   ?? '';

$close_attr = "data-{$prefix}-close";
?>

<div class="ah-bk-modal" id="ah-<?php echo esc_attr( $prefix ); ?>-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $modal_label ); ?>">
	<div class="ah-bk-modal-backdrop" <?php echo esc_attr( $close_attr ); ?>></div>

	<div class="ah-bk-modal-box">
		<button type="button" class="ah-bk-modal-close" <?php echo esc_attr( $close_attr ); ?> aria-label="Close">&times;</button>

		<div class="ah-bk-modal-scroll">

			<!-- Progress bar -->
			<div class="ah-bk-progress">
				<?php foreach ( $steps as $i => $lbl ) : ?>
					<div class="ah-bk-prog-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
						<div class="ah-bk-prog-dot"><?php echo $i + 1; ?></div>
						<span class="ah-bk-prog-label"><?php echo esc_html( $lbl ); ?></span>
					</div>
				<?php endforeach; ?>
				<div class="ah-bk-prog-line"><span class="ah-bk-prog-fill"></span></div>
			</div>

			<form id="<?php echo esc_attr( $form_id ); ?>" novalidate>
				<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
				<div id="ah-<?php echo esc_attr( $prefix ); ?>-msg" class="ah-form-feedback" style="display:none;" role="alert"></div>

				<?php echo $steps_html; // phpcs:ignore WordPress.Security.EscapeOutput -- pre-rendered, escaped at build time ?>

			</form>

		</div><!-- .ah-bk-modal-scroll -->
	</div>
</div>
