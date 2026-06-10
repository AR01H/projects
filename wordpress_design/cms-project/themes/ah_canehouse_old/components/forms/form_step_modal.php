<?php
/**
 * form_step_modal - reusable multi-step modal shell (backdrop + progress bar + form).
 *
 * Wraps any set of `.ch-bk-step` blocks in the shared modal chrome used by the
 * booking / franchise / order-to-deliver wizards. You supply the step markup as a
 * pre-rendered HTML string (build it with output buffering in the calling file).
 *
 * All ids and the close attribute are derived from 'prefix' so the existing
 * forms.js (which targets e.g. #ch-bk-modal, [data-bk-close]) keeps working.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  EXAMPLE                                                        │
 * └─────────────────────────────────────────────────────────────────┘
 *  ob_start();
 *  // … echo your <div class="ch-bk-step" data-step="1"> … </div> blocks …
 *  $steps_html = ob_get_clean();
 *
 *  get_template_part( 'components/forms/form_step_modal', null, [
 *    'prefix'       => 'bk',                       // → #ch-bk-modal, [data-bk-close], #ch-bk-msg
 *    'form_id'      => 'ch-booking-form',
 *    'modal_label'  => 'Book your order',
 *    'nonce_action' => 'ch_contact_nonce',
 *    'nonce_name'   => 'ch_booking_nonce_field',
 *    'steps'        => $_d['step_labels'],         // labels for the progress bar
 *    'steps_html'   => $steps_html,                // the buffered step markup
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  OPTIONS                                                        │
 * └─────────────────────────────────────────────────────────────────┘
 *   prefix        short slug → ids #ch-{prefix}-modal / #ch-{prefix}-msg + attr data-{prefix}-close
 *   form_id       id on the <form>
 *   modal_label   aria-label on the dialog
 *   nonce_action  wp_nonce_field action
 *   nonce_name    wp_nonce_field field name
 *   steps         array of progress-bar labels (one per step)
 *   steps_html    pre-rendered HTML of the .ch-bk-step blocks
 */

defined( 'ABSPATH' ) || exit;

$prefix       = sanitize_html_class( $args['prefix'] ?? 'bk' );
$form_id      = $args['form_id']      ?? "ch-{$prefix}-form";
$modal_label  = $args['modal_label']  ?? '';
$nonce_action = $args['nonce_action'] ?? 'ch_contact_nonce';
$nonce_name   = $args['nonce_name']   ?? "ch_{$prefix}_nonce_field";
$steps        = is_array( $args['steps'] ?? null ) ? $args['steps'] : [];
$steps_html   = $args['steps_html']   ?? '';

$close_attr = "data-{$prefix}-close";
?>

<div class="ch-bk-modal" id="ch-<?php echo esc_attr( $prefix ); ?>-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $modal_label ); ?>">
	<div class="ch-bk-modal-backdrop" <?php echo esc_attr( $close_attr ); ?>></div>

	<div class="ch-bk-modal-box">
		<button type="button" class="ch-bk-modal-close" <?php echo esc_attr( $close_attr ); ?> aria-label="Close">&times;</button>

		<div class="ch-bk-modal-scroll">

			<!-- Progress bar -->
			<div class="ch-bk-progress">
				<?php foreach ( $steps as $i => $lbl ) : ?>
					<div class="ch-bk-prog-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
						<div class="ch-bk-prog-dot"><?php echo $i + 1; ?></div>
						<span class="ch-bk-prog-label"><?php echo esc_html( $lbl ); ?></span>
					</div>
				<?php endforeach; ?>
				<div class="ch-bk-prog-line"><span class="ch-bk-prog-fill"></span></div>
			</div>

			<form id="<?php echo esc_attr( $form_id ); ?>" novalidate>
				<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
				<div id="ch-<?php echo esc_attr( $prefix ); ?>-msg" class="ch-form-feedback" style="display:none;" role="alert"></div>

				<?php echo $steps_html; // phpcs:ignore WordPress.Security.EscapeOutput -- pre-rendered, escaped at build time ?>

			</form>

		</div><!-- .ch-bk-modal-scroll -->
	</div>
</div>
