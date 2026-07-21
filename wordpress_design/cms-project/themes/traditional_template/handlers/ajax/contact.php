<?php
/**
 * handlers/ajax/contact.php - Contact form submission.
 *
 * Registered in config/ajax.php as 'contact_submit'. The dispatcher
 * (core/ajax.php) has ALREADY verified the nonce before this runs, so this
 * file only does: sanitize -> validate -> act -> respond.
 *
 * JS side (assets/js/pages/contact.js):
 *   NT.ajax( 'contact_submit', { name, email, phone, message } )
 */

defined( 'ABSPATH' ) || exit;

function nt_ajax_contact_submit() {
	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( '' === $name || '' === $email || '' === $message ) {
		wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', NT_TEXT_DOMAIN ) ) );
	}
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', NT_TEXT_DOMAIN ) ) );
	}

	if ( '' !== $subject ) {
		$message = "Subject: {$subject}\n\n{$message}";
	}

	// 1. Save to the inbox table (Theme -> Contact Submissions). The table
	// is registered in config/database.php; install lazily just in case the
	// theme was updated without re-activation.
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

	// 2. Notify by email (best effort - the inbox row is the source of truth).
	$to      = nt_option( 'general', 'email', get_option( 'admin_email' ) );
	$subject = sprintf( '[%s] Contact form: %s', NT_BRAND_NAME, $name );
	$body    = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\nMessage:\n{$message}";
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $name, $email ),
	);
	$mailed = wp_mail( $to, $subject, $body, $headers );

	if ( $saved || $mailed ) {
		wp_send_json_success( array( 'message' => __( 'Thank you! We will get back to you shortly.', NT_TEXT_DOMAIN ) ) );
	}
	wp_send_json_error( array( 'message' => __( 'Message could not be sent. Please try again later.', NT_TEXT_DOMAIN ) ) );
}
