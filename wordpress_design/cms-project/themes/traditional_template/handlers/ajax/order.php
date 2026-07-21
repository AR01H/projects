<?php
/**
 * handlers/ajax/order.php - Order-to-deliver wizard submission.
 *
 * Registered in config/ajax.php as 'order_submit'. The dispatcher
 * (core/ajax.php) has ALREADY verified the nonce before this runs, so this
 * file only does: sanitize -> validate -> act -> respond.
 *
 * Saves into the same submissions inbox as the contact form
 * (Theme -> Contact Submissions), with the selected items and delivery
 * details folded into the message body.
 *
 * JS side: components/order-to-deliver.php's wizard, driven by the
 * chStepModal controller in assets/js/legacy.js (prefix 'otd').
 */

defined( 'ABSPATH' ) || exit;

function nt_ajax_order_submit() {
	$name    = sanitize_text_field( wp_unslash( $_POST['otd_name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['otd_email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['otd_phone'] ?? '' ) );
	$address = sanitize_textarea_field( wp_unslash( $_POST['otd_address'] ?? '' ) );
	$area    = sanitize_text_field( wp_unslash( $_POST['otd_area'] ?? '' ) );
	$date    = sanitize_text_field( wp_unslash( $_POST['otd_date'] ?? '' ) );
	$time    = sanitize_text_field( wp_unslash( $_POST['otd_time'] ?? '' ) );
	$notes   = sanitize_textarea_field( wp_unslash( $_POST['otd_notes'] ?? '' ) );

	$items = array_filter( array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['otd_items'] ?? array() ) ) ) );
	$qty   = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['otd_qty'] ?? array() ) ) );

	if ( '' === $name || '' === $email || '' === $address || '' === $area ) {
		wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', NT_TEXT_DOMAIN ) ) );
	}
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', NT_TEXT_DOMAIN ) ) );
	}
	if ( empty( $items ) ) {
		wp_send_json_error( array( 'message' => __( 'Please select at least one item.', NT_TEXT_DOMAIN ) ) );
	}

	$item_lines = array();
	foreach ( $items as $item ) {
		$item_lines[] = $item . ' x' . ( $qty[ $item ] ?? '1' );
	}

	$message = sprintf(
		"Order request\nItems:\n%s\n\nDeliver to: %s, %s\nPreferred date: %s\nPreferred time: %s\n\nNotes:\n%s",
		implode( "\n", $item_lines ),
		$address,
		$area,
		'' !== $date ? $date : 'Not specified',
		'' !== $time ? $time : 'Any time',
		'' !== $notes ? $notes : '-'
	);

	// Save to the inbox table (Theme -> Contact Submissions). The table is
	// registered in config/database.php; install lazily just in case the
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

	// Notify by email (best effort - the inbox row is the source of truth).
	$to      = nt_option( 'general', 'email', get_option( 'admin_email' ) );
	$subject = sprintf( '[%s] Order request: %s', NT_BRAND_NAME, $name );
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $name, $email ),
	);
	$mailed = wp_mail( $to, $subject, "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\n{$message}", $headers );

	if ( $saved || $mailed ) {
		wp_send_json_success( array( 'message' => __( "Thanks! We'll review your order and contact you shortly.", NT_TEXT_DOMAIN ) ) );
	}
	wp_send_json_error( array( 'message' => __( 'Order could not be sent. Please try again later.', NT_TEXT_DOMAIN ) ) );
}
