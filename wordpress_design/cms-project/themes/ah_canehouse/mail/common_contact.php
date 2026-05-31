<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handles the AJAX contact form submission for The Cane House.
 * Creates the submissions table on first run if it doesn't exist.
 */

add_action( 'wp_ajax_ch_contact_submit',        'ch_handle_contact_submit' );
add_action( 'wp_ajax_nopriv_ch_contact_submit', 'ch_handle_contact_submit' );

function ch_handle_contact_submit(): void {
	// ── Nonce ─────────────────────────────────────────────────────────────────
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ch_frontend_nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Security check failed. Please refresh and try again.' ] );
	}

	// ── Sanitise & Validate ───────────────────────────────────────────────────
	$name    = sanitize_text_field( wp_unslash( $_POST['ch_name']    ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['ch_email']   ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['ch_phone']   ?? '' ) );
	$enquiry = sanitize_text_field( wp_unslash( $_POST['ch_enquiry'] ?? 'general' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['ch_message'] ?? '' ) );

	if ( empty( $name ) ) {
		wp_send_json_error( [ 'message' => 'Please enter your name.' ] );
	}

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
	}

	// ── Store in DB ───────────────────────────────────────────────────────────
	global $wpdb;
	$table = $wpdb->prefix . 'ch_contact_submissions';

	// Create table if not exists
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name          VARCHAR(200)    NOT NULL DEFAULT '',
			email         VARCHAR(200)    NOT NULL DEFAULT '',
			phone         VARCHAR(50)     NOT NULL DEFAULT '',
			enquiry_type  VARCHAR(100)    NOT NULL DEFAULT 'general',
			message       TEXT            NOT NULL DEFAULT '',
			ip_address    VARCHAR(50)     NOT NULL DEFAULT '',
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$charset_collate};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	$wpdb->insert(
		$table,
		[
			'name'         => $name,
			'email'        => $email,
			'phone'        => $phone,
			'enquiry_type' => $enquiry,
			'message'      => $message,
			'ip_address'   => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
			'created_at'   => current_time( 'mysql' ),
		],
		[ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
	);

	// ── Send Email ────────────────────────────────────────────────────────────
	$contact_settings = ch_get_contact_settings();
	$recipient        = $contact_settings['recipient_email'] ?? get_option( 'admin_email' );
	$subject_prefix   = $contact_settings['subject_prefix']  ?? '[The Cane House Enquiry]';

	$enquiry_labels = [
		'general'   => 'General Enquiry',
		'event'     => 'Event / Stall Hire',
		'wedding'   => 'Wedding or Asian Celebration',
		'franchise' => 'Franchise Opportunity',
		'other'     => 'Something Else',
	];

	$subject = sprintf( '%s New enquiry from %s - %s', $subject_prefix, $name, $enquiry_labels[ $enquiry ] ?? $enquiry );

	$body = "You have received a new enquiry via The Cane House website."
		. "Name:         {$name}\n"
		. "Email:        {$email}\n"
		. "Phone:        {$phone}\n"
		. "Enquiry Type: " . ( $enquiry_labels[ $enquiry ] ?? $enquiry ) . ""
		. "Message:\n{$message}"
		. "---\n"
		. "Sent from " . home_url() . "\n"
		. 'Time: ' . current_time( 'Y-m-d H:i:s' );

	wp_mail(
		$recipient,
		$subject,
		$body,
		[
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $name . ' <' . $email . '>',
			'From: The Cane House <' . get_option( 'admin_email' ) . '>',
		]
	);

	// ── Auto-reply to sender ─────────────────────────────────────────────────
	$auto_reply_body = "Hi {$name},"
		. "Thank you for getting in touch with The Cane House! 🌿"
		. "We've received your message and will get back to you very soon - usually within a few hours."
		. "In the meantime, if your enquiry is urgent, please call us directly:\n"
		. "📞 " . ( ch_get_settings()['phone'] ?? CONTACT_NUMBER ) . ""
		. "Pressed Fresh. Served Cool.\n"
		. "The Cane House Team"
		. "www.thecanehouse.co.uk";

	wp_mail(
		$email,
		'Thanks for contacting The Cane House - we\'ll be in touch soon! 🌿',
		$auto_reply_body,
		[
			'Content-Type: text/plain; charset=UTF-8',
			'From: The Cane House <' . get_option( 'admin_email' ) . '>',
		]
	);

	$thank_you = $contact_settings['thank_you_msg'] ?? "Thanks for your message! We'll be in touch shortly. Pressed Fresh. Served Cool. 🌿";

	wp_send_json_success( [ 'message' => $thank_you ] );
}
