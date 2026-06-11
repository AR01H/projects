<?php
/**
 * components/form_builder/form_builder.php - Component: Form Builder
 *
 * PURPOSE: Render a complete, AJAX-ready form from one config array.
 *          Receives $form via adn_component() / adn_render_form().
 *          NO queries, NO hooks - components only render.
 *
 * Usage (from any template):
 *   adn_render_form( array(
 *       'id'              => 'contact',
 *       'endpoint'        => rest_url( ADN_API_NS . '/contact' ),
 *       'submit_label'    => 'Send Message',
 *       'success_message' => 'Thanks! We will be in touch.',
 *       'fields'          => array(
 *           array( 'type' => 'text',     'name' => 'name',    'label' => 'Your Name', 'required' => true ),
 *           array( 'type' => 'email',    'name' => 'email',   'label' => 'Email',     'required' => true ),
 *           array( 'type' => 'tel',      'name' => 'phone',   'label' => 'Phone',     'width' => 'half' ),
 *           array( 'type' => 'select',   'name' => 'topic',   'label' => 'Topic',
 *                  'options' => array( 'general' => 'General', 'buying' => 'Buying' ) ),
 *           array( 'type' => 'textarea', 'name' => 'message', 'label' => 'Message',   'required' => true ),
 *       ),
 *   ) );
 *
 * Field keys: type, name, label, required, placeholder, value, width ('half'|'full'),
 *             options (select/radio), rows (textarea), help.
 * Types: text, email, tel, number, url, date, textarea, select, radio, checkbox, hidden.
 *
 * Submission: assets/js/form-builder.js intercepts the submit and POSTs JSON
 * to data-endpoint. Without JS the form falls back to a normal POST.
 */

defined( 'ABSPATH' ) || exit;

$form = isset( $form ) && is_array( $form ) ? $form : array();

$form_id  = isset( $form['id'] ) ? sanitize_key( $form['id'] ) : 'form';
$endpoint = isset( $form['endpoint'] ) ? $form['endpoint'] : '';
$fields   = isset( $form['fields'] ) && is_array( $form['fields'] ) ? $form['fields'] : array();

if ( empty( $fields ) ) {
    return; // nothing to render
}

$submit_label    = isset( $form['submit_label'] ) ? $form['submit_label'] : lang_translate( 'contact_us' );
$success_message = isset( $form['success_message'] ) ? $form['success_message'] : 'Thank you! Your submission has been received.';
?>
<form id="adn-form-<?php echo esc_attr( $form_id ); ?>"
      class="adn-form"
      method="post"
      action="<?php echo esc_url( $endpoint ); ?>"
      data-endpoint="<?php echo esc_url( $endpoint ); ?>"
      data-success="<?php echo esc_attr( $success_message ); ?>"
      novalidate>

    <?php wp_nonce_field( 'wp_rest', '_wpnonce', false ); ?>

    <?php // Honeypot: hidden from humans, bots fill it → server silently drops. ?>
    <p class="adn-form__hp" aria-hidden="true">
        <label for="adn-hp-<?php echo esc_attr( $form_id ); ?>">Leave this field empty</label>
        <input type="text" id="adn-hp-<?php echo esc_attr( $form_id ); ?>" name="adn_hp" value="" tabindex="-1" autocomplete="off">
    </p>

    <div class="adn-form__grid">
    <?php
    foreach ( $fields as $field ) :
        $type     = isset( $field['type'] ) ? $field['type'] : 'text';
        $name     = isset( $field['name'] ) ? sanitize_key( $field['name'] ) : '';
        if ( '' === $name ) {
            continue;
        }
        $input_id    = 'adn-' . $form_id . '-' . $name;
        $label       = isset( $field['label'] ) ? $field['label'] : ucfirst( $name );
        $required    = ! empty( $field['required'] );
        $placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
        $value       = isset( $field['value'] ) ? $field['value'] : '';
        $help        = isset( $field['help'] ) ? $field['help'] : '';
        $width_class = ( isset( $field['width'] ) && 'half' === $field['width'] ) ? ' adn-form__field--half' : '';

        if ( 'hidden' === $type ) : ?>
            <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <?php continue;
        endif;
    ?>
        <div class="adn-form__field adn-form__field--<?php echo esc_attr( $type ); ?><?php echo esc_attr( $width_class ); ?>">

            <?php if ( 'checkbox' !== $type ) : ?>
                <label class="adn-form__label" for="<?php echo esc_attr( $input_id ); ?>">
                    <?php echo esc_html( $label ); ?>
                    <?php if ( $required ) : ?><span class="adn-form__req" aria-hidden="true">*</span><?php endif; ?>
                </label>
            <?php endif; ?>

            <?php if ( 'textarea' === $type ) : ?>
                <textarea class="adn-form__input"
                          id="<?php echo esc_attr( $input_id ); ?>"
                          name="<?php echo esc_attr( $name ); ?>"
                          rows="<?php echo esc_attr( isset( $field['rows'] ) ? (int) $field['rows'] : 5 ); ?>"
                          placeholder="<?php echo esc_attr( $placeholder ); ?>"
                          <?php echo $required ? 'required' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>

            <?php elseif ( 'select' === $type ) : ?>
                <select class="adn-form__input"
                        id="<?php echo esc_attr( $input_id ); ?>"
                        name="<?php echo esc_attr( $name ); ?>"
                        <?php echo $required ? 'required' : ''; ?>>
                    <?php foreach ( (array) ( isset( $field['options'] ) ? $field['options'] : array() ) as $opt_value => $opt_label ) : ?>
                        <option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>>
                            <?php echo esc_html( $opt_label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            <?php elseif ( 'radio' === $type ) : ?>
                <div class="adn-form__radios" role="radiogroup" aria-label="<?php echo esc_attr( $label ); ?>">
                    <?php foreach ( (array) ( isset( $field['options'] ) ? $field['options'] : array() ) as $opt_value => $opt_label ) : ?>
                        <label class="adn-form__radio">
                            <input type="radio"
                                   name="<?php echo esc_attr( $name ); ?>"
                                   value="<?php echo esc_attr( $opt_value ); ?>"
                                   <?php checked( $value, $opt_value ); ?>
                                   <?php echo $required ? 'required' : ''; ?>>
                            <span><?php echo esc_html( $opt_label ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

            <?php elseif ( 'checkbox' === $type ) : ?>
                <label class="adn-form__checkbox" for="<?php echo esc_attr( $input_id ); ?>">
                    <input type="checkbox"
                           id="<?php echo esc_attr( $input_id ); ?>"
                           name="<?php echo esc_attr( $name ); ?>"
                           value="1"
                           <?php checked( $value, '1' ); ?>
                           <?php echo $required ? 'required' : ''; ?>>
                    <span>
                        <?php echo esc_html( $label ); ?>
                        <?php if ( $required ) : ?><span class="adn-form__req" aria-hidden="true">*</span><?php endif; ?>
                    </span>
                </label>

            <?php else : // text, email, tel, number, url, date ?>
                <input class="adn-form__input"
                       type="<?php echo esc_attr( $type ); ?>"
                       id="<?php echo esc_attr( $input_id ); ?>"
                       name="<?php echo esc_attr( $name ); ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       placeholder="<?php echo esc_attr( $placeholder ); ?>"
                       <?php echo $required ? 'required' : ''; ?>>
            <?php endif; ?>

            <?php if ( $help ) : ?>
                <small class="adn-form__help"><?php echo esc_html( $help ); ?></small>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>

    <div class="adn-form__msg" role="status" aria-live="polite" hidden></div>

    <button type="submit" class="adn-form__submit">
        <?php echo esc_html( $submit_label ); ?>
    </button>
</form>
