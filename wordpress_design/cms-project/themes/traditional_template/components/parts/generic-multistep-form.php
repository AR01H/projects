<?php
/**
 * Generic Multi-Step Form Component
 *
 * Args:
 *  id           (string) Form ID. Default: 'nt-generic-multistep'
 *  action       (string) Form action endpoint/ajax action.
 *  nonce_action (string) Nonce action name.
 *  nonce_name   (string) Nonce field name.
 *  submit       (string) Submit button text. Default: 'Submit'
 *  steps        (array)  Array of step configs.
 */
defined( 'ABSPATH' ) || exit;

$form_id      = $args['id'] ?? 'nt-generic-multistep';
$action       = $args['action'] ?? '';
$nonce_action = $args['nonce_action'] ?? '';
$nonce_name   = $args['nonce_name'] ?? '';
$submit       = $args['submit'] ?? __( 'Submit', NT_TEXT_DOMAIN );
$steps        = $args['steps'] ?? [];
$total_steps  = count( $steps );
$form_label   = $args['form_label'] ?? '';

// The JS wizard controller (assets/js/common.js) posts through NT.ajax(), which
// adds the nt_ prefix + per-action nonce itself. Strip any nt_ prefix so the
// data-nt-action carries the bare action key registered in config/ajax.php.
$bare_action  = preg_replace( '/^nt_/', '', (string) $action );
?>
<div class="nt-bk-modal-scroll">
	<!-- Progress bar -->
	<div class="nt-bk-progress">
		<?php foreach ( $steps as $i => $step ) : ?>
			<div class="nt-bk-prog-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo $i + 1; ?>">
				<div class="nt-bk-prog-dot"><?php echo $i + 1; ?></div>
				<span class="nt-bk-prog-label"><?php echo esc_html( $step['title'] ?? '' ); ?></span>
			</div>
		<?php endforeach; ?>
		<div class="nt-bk-prog-line"><span class="nt-bk-prog-fill"></span></div>
	</div>

	<form id="<?php echo esc_attr( $form_id ); ?>-body" data-nt-wizard data-nt-action="<?php echo esc_attr( $bare_action ); ?>" novalidate>
		<?php if ( $form_label ) : ?>
			<input type="hidden" name="nt_form_label" value="<?php echo esc_attr( $form_label ); ?>">
		<?php endif; ?>
		
		<div id="<?php echo esc_attr( $form_id ); ?>-msg" class="nt-form-feedback" style="display:none;" role="alert"></div>

		<?php foreach ( $steps as $i => $step ) : 
			$step_num = $i + 1;
			$is_last  = ( $step_num === $total_steps );
		?>
			<div class="nt-bk-step<?php echo $i === 0 ? ' active' : ''; ?>" data-step="<?php echo esc_attr( $step_num ); ?>">
				
				<?php if ( ! empty( $step['custom_html'] ) ) : ?>
					<?php echo $step['custom_html']; ?>
				<?php else : ?>
					<h3 class="nt-bk-step-title"><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
					<p class="nt-bk-step-desc"><?php echo esc_html( $step['desc'] ?? '' ); ?></p>
					
					<?php if ( ! empty( $step['summary_id'] ) ) : ?>
						<div class="nt-bk-summary" id="<?php echo esc_attr( $step['summary_id'] ); ?>"></div>
					<?php endif; ?>

					<div class="nt-bk-fields">
						<?php 
						$fields = $step['fields'] ?? [];
						foreach ( $fields as $field ) : 
							$type     = $field['type'] ?? 'text';
							$fid      = $field['id'] ?? uniqid('f_');
							$fname    = $field['name'] ?? $fid;
							$flabel   = $field['label'] ?? '';
							$freq     = !empty($field['required']) ? 'required' : '';
							$fplace   = $field['placeholder'] ?? '';
							$foptions = $field['options'] ?? [];
						?>
							<div class="nt-bk-field">
								<?php if ( $flabel ) : ?>
									<label for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $flabel ); ?><?php echo $freq ? ' *' : ''; ?></label>
								<?php endif; ?>

								<?php if ( $type === 'textarea' ) : ?>
									<textarea class="nt-form-textarea" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $fname ); ?>" placeholder="<?php echo esc_attr( $fplace ); ?>" rows="3" <?php echo $freq; ?>></textarea>
								<?php elseif ( $type === 'select' ) : ?>
									<select class="nt-form-select" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $fname ); ?>" <?php echo $freq; ?>>
										<?php foreach ( $foptions as $opt_val => $opt_label ) : ?>
											<option value="<?php echo esc_attr( $opt_val ); ?>"><?php echo esc_html( $opt_label ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<input class="nt-form-input" type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $fname ); ?>" placeholder="<?php echo esc_attr( $fplace ); ?>" <?php echo $freq; ?>>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="nt-bk-nav">
					<?php if ( $i > 0 ) : ?>
						<button type="button" class="nt-bk-back btn-outline" data-back="<?php echo esc_attr( $step_num - 1 ); ?>">← Back</button>
					<?php else : ?>
						<span></span>
					<?php endif; ?>

					<?php if ( $is_last ) : ?>
						<button type="submit" class="nt-bk-submit btn-lime" id="<?php echo esc_attr( $form_id ); ?>-submit"><?php echo esc_html( $submit ); ?></button>
					<?php else : ?>
						<?php $next_title = $steps[$i + 1]['title'] ?? 'Next'; ?>
						<button type="button" class="nt-bk-next btn-lime" data-next="<?php echo esc_attr( $step_num + 1 ); ?>">Next: <?php echo esc_html( $next_title ); ?> →</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</form>
</div>
