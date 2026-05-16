<?php
defined( 'ABSPATH' ) || exit;

/**
 * Centralized AJAX handler for all frontend forms.
 *
 * Registered for both logged-in (wp_ajax_) and guest (wp_ajax_nopriv_) requests.
 * Called by forms.js when any <form data-ah-form="TYPE"> is submitted.
 *
 * Supported form types: contact, consultation, newsletter, valuation
 */

add_action( 'wp_ajax_ah_form_submit',        'ah_handle_form_submit' );
add_action( 'wp_ajax_nopriv_ah_form_submit', 'ah_handle_form_submit' );

function ah_handle_form_submit(): void {
	// Verify nonce
	if ( ! check_ajax_referer( 'ah_frontend_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => 'Security check failed. Please refresh the page and try again.' ], 403 );
	}

	$form_type = sanitize_key( $_POST['form_type'] ?? 'contact' );

	switch ( $form_type ) {
		case 'contact':
			ah_process_contact_form();
			break;
		case 'consultation':
			ah_process_consultation_form();
			break;
		case 'newsletter':
			ah_process_newsletter_form();
			break;
		case 'valuation':
			ah_process_valuation_form();
			break;
		default:
			wp_send_json_error( [ 'message' => 'Unknown form type.' ] );
	}
}

// ── Newsletter shorthand ──────────────────────────────────────────────────────
add_action( 'wp_ajax_ah_newsletter_subscribe',        'ah_process_newsletter_form' );
add_action( 'wp_ajax_nopriv_ah_newsletter_subscribe', 'ah_process_newsletter_form' );

// ─────────────────────────────────────────────────────────────────────────────

function ah_process_contact_form(): void {
	$errors = [];
	$name    = sanitize_text_field( $_POST['name']    ?? '' );
	$email   = sanitize_email(      $_POST['email']   ?? '' );
	$phone   = sanitize_text_field( $_POST['phone']   ?? '' );
	$subject = sanitize_text_field( $_POST['subject'] ?? '' );
	$message = sanitize_textarea_field( $_POST['message'] ?? '' );

	if ( ! $name )                       $errors['name']    = 'Please enter your name.';
	if ( ! is_email( $email ) )          $errors['email']   = 'Please enter a valid email address.';
	if ( ! $message )                    $errors['message'] = 'Please enter your message.';

	if ( $errors ) {
		wp_send_json_error( [ 'message' => 'Please fix the errors below.', 'errors' => $errors ] );
	}

	$settings    = ah_get_settings();
	$to          = $settings['email'] ?? get_option( 'admin_email' );
	$blog_name   = get_bloginfo( 'name' );
	$headers     = [ 'Content-Type: text/html; charset=UTF-8', "Reply-To: {$name} <{$email}>" ];
	$email_subj  = "[{$blog_name}] Contact: " . ( $subject ?: 'New enquiry' );

	$body = ah_email_wrap(
		"<h2>New Contact Enquiry</h2>
		<table style='width:100%;border-collapse:collapse'>
			<tr><td style='padding:8px;font-weight:700;width:140px;vertical-align:top'>Name</td><td style='padding:8px'>" . esc_html( $name ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Email</td><td style='padding:8px'>" . esc_html( $email ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Phone</td><td style='padding:8px'>" . esc_html( $phone ?: '—' ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Subject</td><td style='padding:8px'>" . esc_html( $subject ?: '—' ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Message</td><td style='padding:8px'>" . nl2br( esc_html( $message ) ) . "</td></tr>
		</table>"
	);

	$sent = wp_mail( $to, $email_subj, $body, $headers );

	// Auto-reply to sender
	ah_send_auto_reply( $email, $name, 'contact' );

	// Log to CMS if plugin present
	ah_log_submission( 'contact', compact( 'name', 'email', 'phone', 'subject', 'message' ) );

	if ( $sent ) {
		wp_send_json_success( [ 'message' => "Thank you, {$name}! We'll be in touch within 2 hours." ] );
	} else {
		wp_send_json_error( [ 'message' => 'Email could not be sent. Please try calling us directly.' ] );
	}
}

function ah_process_consultation_form(): void {
	$errors = [];
	$name      = sanitize_text_field( $_POST['name']      ?? '' );
	$email     = sanitize_email(      $_POST['email']     ?? '' );
	$phone     = sanitize_text_field( $_POST['phone']     ?? '' );
	$budget    = sanitize_text_field( $_POST['budget']    ?? '' );
	$location  = sanitize_text_field( $_POST['location']  ?? '' );
	$buyer_type = sanitize_text_field( $_POST['buyer_type'] ?? '' );
	$notes     = sanitize_textarea_field( $_POST['notes'] ?? '' );

	if ( ! $name )          $errors['name']  = 'Please enter your name.';
	if ( ! is_email($email) ) $errors['email'] = 'Please enter a valid email address.';
	if ( ! $phone )         $errors['phone'] = 'Please enter your phone number.';

	if ( $errors ) {
		wp_send_json_error( [ 'message' => 'Please fix the errors below.', 'errors' => $errors ] );
	}

	$settings  = ah_get_settings();
	$to        = $settings['email'] ?? get_option( 'admin_email' );
	$blog_name = get_bloginfo( 'name' );
	$headers   = [ 'Content-Type: text/html; charset=UTF-8', "Reply-To: {$name} <{$email}>" ];
	$subj      = "[{$blog_name}] Consultation Request from {$name}";

	$body = ah_email_wrap(
		"<h2>Consultation Request</h2>
		<table style='width:100%;border-collapse:collapse'>
			<tr><td style='padding:8px;font-weight:700;width:140px'>Name</td><td style='padding:8px'>" . esc_html( $name ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700'>Email</td><td style='padding:8px'>" . esc_html( $email ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700'>Phone</td><td style='padding:8px'>" . esc_html( $phone ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700'>Budget</td><td style='padding:8px'>" . esc_html( $budget ?: '—' ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700'>Location</td><td style='padding:8px'>" . esc_html( $location ?: '—' ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700'>Buyer Type</td><td style='padding:8px'>" . esc_html( $buyer_type ?: '—' ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700'>Notes</td><td style='padding:8px'>" . nl2br( esc_html( $notes ?: '—' ) ) . "</td></tr>
		</table>"
	);

	wp_mail( $to, $subj, $body, $headers );
	ah_send_auto_reply( $email, $name, 'consultation' );
	ah_log_submission( 'consultation', compact( 'name', 'email', 'phone', 'budget', 'location', 'buyer_type', 'notes' ) );

	wp_send_json_success( [ 'message' => "Thank you, {$name}! We'll call you within 4 business hours to book your consultation." ] );
}

function ah_process_newsletter_form(): void {
	$email = sanitize_email( $_POST['email'] ?? '' );

	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
	}

	$settings  = ah_get_settings();
	$to        = $settings['email'] ?? get_option( 'admin_email' );
	$blog_name = get_bloginfo( 'name' );

	wp_mail( $to, "[{$blog_name}] Newsletter Signup: {$email}",
		ah_email_wrap( "<p>New newsletter subscriber: <strong>" . esc_html( $email ) . "</strong></p>" ),
		[ 'Content-Type: text/html; charset=UTF-8' ]
	);

	ah_log_submission( 'newsletter', [ 'email' => $email ] );

	wp_send_json_success( [ 'message' => 'You\'re subscribed! Expect our next update within the week.' ] );
}

function ah_process_valuation_form(): void {
	$name    = sanitize_text_field( $_POST['name']    ?? '' );
	$email   = sanitize_email(      $_POST['email']   ?? '' );
	$phone   = sanitize_text_field( $_POST['phone']   ?? '' );
	$address = sanitize_textarea_field( $_POST['address'] ?? '' );

	if ( ! $name || ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Please fill in your name and email.' ] );
	}

	$settings  = ah_get_settings();
	$to        = $settings['email'] ?? get_option( 'admin_email' );
	$blog_name = get_bloginfo( 'name' );
	$headers   = [ 'Content-Type: text/html; charset=UTF-8', "Reply-To: {$name} <{$email}>" ];

	wp_mail( $to, "[{$blog_name}] Valuation Request from {$name}",
		ah_email_wrap(
			"<h2>Valuation Request</h2>
			<p><strong>Name:</strong> " . esc_html( $name ) . "</p>
			<p><strong>Email:</strong> " . esc_html( $email ) . "</p>
			<p><strong>Phone:</strong> " . esc_html( $phone ?: '—' ) . "</p>
			<p><strong>Address:</strong> " . nl2br( esc_html( $address ?: '—' ) ) . "</p>"
		),
		$headers
	);

	ah_log_submission( 'valuation', compact( 'name', 'email', 'phone', 'address' ) );

	wp_send_json_success( [ 'message' => "Thanks, {$name}! We'll send a market appraisal within 24 hours." ] );
}

// ── Utilities ────────────────────────────────────────────────────────────────

function ah_email_wrap( string $inner ): string {
	$site  = get_bloginfo( 'name' );
	$url   = home_url( '/' );
	$year  = gmdate( 'Y' );
	return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;font-size:15px;color:#1e293b;background:#f8fafc;margin:0;padding:0'>
		<div style='max-width:600px;margin:32px auto;background:white;border-radius:12px;overflow:hidden;border:1px solid #e8e4f3'>
			<div style='background:#0f172a;padding:24px 32px;display:flex;align-items:center;gap:12px'>
				<div style='width:40px;height:40px;background:#b7791f;border-radius:8px;display:grid;place-items:center;color:white;font-weight:700;font-size:1rem;text-align:center;line-height:40px'>AH</div>
				<span style='color:white;font-size:1.2rem;font-weight:600'>{$site}</span>
			</div>
			<div style='padding:32px'>{$inner}</div>
			<div style='padding:16px 32px;background:#f8fafc;border-top:1px solid #e8e4f3;font-size:12px;color:#64748b;text-align:center'>
				&copy; {$year} <a href='{$url}' style='color:#b7791f'>{$site}</a>. All rights reserved.
			</div>
		</div>
	</body></html>";
}

function ah_send_auto_reply( string $to, string $name, string $type ): void {
	$blog_name = get_bloginfo( 'name' );
	$from      = '"' . $blog_name . '" <' . get_option( 'admin_email' ) . '>';
	$headers   = [ 'Content-Type: text/html; charset=UTF-8', "From: {$from}" ];

	$messages = [
		'contact'      => "We've received your message and a member of our team will reply within 2 hours during business hours (Mon–Sat, 9am–6pm).",
		'consultation' => "We've received your consultation request. One of our buyer's agents will call you within 4 business hours to confirm a time that works for you.",
	];

	$msg = $messages[ $type ] ?? "We've received your enquiry and will be in touch shortly.";

	$body = ah_email_wrap(
		"<p>Hi {$name},</p>
		<p>{$msg}</p>
		<p>In the meantime, you might find our <a href='" . esc_url( home_url( '/guides/' ) ) . "' style='color:#b7791f'>buying guides</a> useful.</p>
		<p>Best regards,<br><strong>The {$blog_name} Team</strong></p>"
	);

	wp_mail( $to, "We've received your enquiry — {$blog_name}", $body, $headers );
}

function ah_log_submission( string $type, array $data ): void {
	if ( ! class_exists( 'AH_Model_Submissions' ) ) return;

	try {
		AH_Model_Submissions::create( [
			'form_type' => $type,
			'data'      => wp_json_encode( $data ),
			'ip'        => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
			'status'    => 'new',
		] );
	} catch ( \Throwable $e ) {
		// Silent — logging failure should never break the form response
	}
}
