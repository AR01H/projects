<?php
/**
 * Generic Form Component
 *
 * Args:
 *  id       (string) Form ID. Default: 'nt-generic-form'
 *  action   (string) Form action endpoint/ajax action.
 *  fields   (array)  Array of field configs: [ type, id, name, label, required, placeholder, options (if select) ]
 *  submit   (string) Submit button text. Default: 'Submit'
 *  class    (string) Extra CSS classes.
 */
defined( 'ABSPATH' ) || exit;

$form_id = $args['id'] ?? 'nt-generic-form';
$action  = $args['action'] ?? '';
$fields  = $args['fields'] ?? [];
$submit  = $args['submit'] ?? __( 'Submit', NT_TEXT_DOMAIN );
$class   = $args['class'] ?? '';
?>
<form id="<?php echo esc_attr( $form_id ); ?>" class="nt-form <?php echo esc_attr( $class ); ?>" data-nt-ajax-form="<?php echo esc_attr( $action ); ?>" novalidate>
	<?php foreach ( $fields as $field ) : 
		$type     = $field['type'] ?? 'text';
		$fid      = $field['id'] ?? uniqid('f_');
		$fname    = $field['name'] ?? $fid;
		$flabel   = $field['label'] ?? '';
		$freq     = !empty($field['required']) ? 'required' : '';
		$fplace   = $field['placeholder'] ?? '';
		$foptions = $field['options'] ?? [];
	?>
		<div class="nt-form-group nt-form-row">
			<?php if ( $flabel ) : ?>
				<label class="nt-form-label" for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $flabel ); ?><?php echo $freq ? ' *' : ''; ?></label>
			<?php endif; ?>

			<?php if ( $type === 'textarea' ) : ?>
				<textarea class="nt-form-textarea" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $fname ); ?>" placeholder="<?php echo esc_attr( $fplace ); ?>" rows="5" <?php echo $freq; ?>></textarea>
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

	<button type="submit" class="nt-btn button nt-form-submit"><?php echo esc_html( $submit ); ?></button>
	<p class="nt-form-status" role="status" aria-live="polite" style="display:none;"></p>
</form>
