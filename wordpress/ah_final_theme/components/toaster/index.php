<?php

function ah_form_set_highlighted( $msg = '', $type = 'success' ) {
    if ( empty( $msg ) ) {
        return;
    }
    $class_array = array( 'ah-notice', 'ah-notice-' . $type );
    return '<div class="' . implode( ' ', $class_array ) . '">' . esc_html( $msg ) . '</div>';
}