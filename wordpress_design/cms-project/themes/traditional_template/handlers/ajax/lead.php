<?php
/**
 * handlers/ajax/lead.php - Generic lead / enquiry submission.
 *
 * ONE reusable handler for every multi-step form (order, franchise, events,
 * any future wizard). It doesn't care which fields a form has: it collects
 * whatever was posted, works out the name/email/phone heuristically, folds
 * the rest into the message body, saves to the submissions inbox (Theme ->
 * Contact Submissions) and emails the site owner.
 *
 * Registered in config/ajax.php as 'lead_submit'. The dispatcher has already
 * verified the nonce. JS: the wizard controller in assets/js/common.js posts
 * NT.ajax('lead_submit', {...all fields...}).
 */

defined( 'ABSPATH' ) || exit;

function nt_ajax_lead_submit() {
	$skip = array( 'action', 'nonce', '_wpnonce', '_wp_http_referer', 'nt_form_label' );

	$fields = array();
	foreach ( (array) $_POST as $key => $value ) {
		if ( in_array( $key, $skip, true ) ) {
			continue;
		}
		$key = sanitize_key( $key );
		if ( is_array( $value ) ) {
			$value = implode( ', ', array_map( 'sanitize_text_field', wp_unslash( $value ) ) );
		} else {
			$value = sanitize_textarea_field( wp_unslash( $value ) );
		}
		if ( '' !== trim( (string) $value ) ) {
			$fields[ $key ] = $value;
		}
	}

	// Work out contact basics from field-name hints.
	$name = '';
	$email = '';
	$phone = '';
	foreach ( $fields as $key => $value ) {
		$lk = strtolower( $key );
		if ( '' === $name && false !== strpos( $lk, 'name' ) ) {
			$name = $value;
		}
		if ( '' === $email && false !== strpos( $lk, 'email' ) ) {
			$email = $value;
		}
		if ( '' === $phone && ( false !== strpos( $lk, 'phone' ) || false !== strpos( $lk, 'whatsapp' ) ) ) {
			$phone = $value;
		}
	}

	if ( '' === $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', NT_TEXT_DOMAIN ) ) );
	}
	if ( '' === $name ) {
		$name = __( 'Website lead', NT_TEXT_DOMAIN );
	}

	$form_label = sanitize_text_field( wp_unslash( $_POST['nt_form_label'] ?? __( 'Enquiry', NT_TEXT_DOMAIN ) ) );

	// Human-readable message from every submitted field.
	$lines = array( 'Form: ' . $form_label );
	foreach ( $fields as $key => $value ) {
		$label   = preg_replace( '/^(otd|frn|ev|bk|nt|cf)_/', '', $key );
		$label   = ucwords( str_replace( array( '_', '-' ), ' ', $label ) );
		$lines[] = $label . ': ' . $value;
	}
	$message = implode( "\n", $lines );

	// Save to the inbox table (install lazily if needed).
	global $wpdb;
	if ( ! nt_db_table_exists( 'submissions' ) ) {
		nt_db_install( 'submissions' );
	}
	$saved = $wpdb->insert(
		nt_db_table( 'submissions' ),
		array(
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone,
			'message'    => $message,
			'status'     => 'new',
			'created_at' => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	$to      = nt_option( 'general', 'email', get_option( 'admin_email' ) );
	$subject = sprintf( '[%s] %s: %s', NT_BRAND_NAME, $form_label, $name );
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $name, $email ),
	);
	$mailed = wp_mail( $to, $subject, $message, $headers );

	if ( $saved || $mailed ) {
		wp_send_json_success( array( 'message' => __( "Thank you! We've received your details and will be in touch shortly.", NT_TEXT_DOMAIN ) ) );
	}
	wp_send_json_error( array( 'message' => __( 'Something went wrong. Please try again later.', NT_TEXT_DOMAIN ) ) );
}
