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

	// ── Send response immediately, then run Rules Engine in background ───────
	$contact_settings = ch_get_contact_settings();
	$thank_you = $contact_settings['thank_you_msg'] ?? "Thanks for your message! We'll be in touch shortly. Pressed Fresh. Served Cool. 🌿";

	$response = wp_json_encode( [ 'success' => true, 'data' => [ 'message' => $thank_you ] ] );

	// Close the HTTP connection so the user gets their response now.
	header( 'Content-Type: application/json; charset=UTF-8' );
	header( 'Content-Length: ' . strlen( $response ) );
	header( 'Connection: close' );
	ob_end_clean();
	echo $response;
	flush();
	if ( function_exists( 'fastcgi_finish_request' ) ) {
		fastcgi_finish_request();
	}

	// ── Everything below runs AFTER the browser receives the response ─────────
	ignore_user_abort( true );
	set_time_limit( 60 );

	$enquiry_labels = [
		'general'   => 'General Enquiry',
		'event'     => 'Event / Stall Hire',
		'wedding'   => 'Wedding or Asian Celebration',
		'franchise' => 'Franchise Opportunity',
		'other'     => 'Something Else',
	];

	if ( class_exists( 'AH_Rules_Engine' ) ) {
		AH_Rules_Engine::evaluate( 'sugarcane_contact_form', [
			'name'          => $name,
			'email'         => $email,
			'phone'         => $phone,
			'enquiry'       => $enquiry,
			'enquiry_label' => $enquiry_labels[ $enquiry ] ?? $enquiry,
			'message'       => $message,
			'site_url'      => home_url(),
			'submitted_at'  => current_time( 'Y-m-d H:i:s' ),
		], true );
	}
}
