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
	if ( ! check_ajax_referer( 'ah_frontend_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => 'Security check failed. Please refresh the page and try again.' ], 403 );
	}

	$form_type = sanitize_key( $_POST['form_type'] ?? 'contact' );

	switch ( $form_type ) {
		case 'contact':
		case 'consultation':
			ah_process_contact_form();
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
	$name         = sanitize_text_field( $_POST['name']         ?? '' );
	$email        = sanitize_email(      $_POST['email']        ?? '' );
	$phone        = sanitize_text_field( $_POST['phone']        ?? '' );
	$message      = sanitize_textarea_field( $_POST['message']  ?? '' );
	$short_quote  = sanitize_text_field( $_POST['short_quote']  ?? '' );
	$page_url     = esc_url_raw(         $_POST['page_url']     ?? '' );
	$user_agent   = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' );

	$allowed_types  = [ 'general', 'complaint', 'sales', 'support', 'media', 'other' ];
	$raw_type       = sanitize_key( $_POST['enquiry_type'] ?? 'general' );
	$enquiry_type   = in_array( $raw_type, $allowed_types, true ) ? $raw_type : 'general';

	if ( ! $name )          $errors['name']    = 'Please enter your name.';
	if ( ! is_email($email) ) $errors['email'] = 'Please enter a valid email address.';
	if ( ! $message )       $errors['message'] = 'Please enter your message.';

	if ( $errors ) {
		wp_send_json_error( [ 'message' => 'Please fix the errors below.', 'errors' => $errors ] );
	}

	// ── File upload ───────────────────────────────────────────────────────────
	$attachment_path  = '';
	$attachment_name  = '';
	$email_attachment = ''; // filesystem path for wp_mail

	if ( ! empty( $_FILES['attachment']['name'] ) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE ) {
		$file          = $_FILES['attachment'];
		$allowed_ext   = [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png' ];
		$ext           = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( [ 'message' => 'File upload error. Please try again.' ] );
		}
		if ( ! in_array( $ext, $allowed_ext, true ) ) {
			wp_send_json_error( [ 'message' => 'File type not allowed. Accepted: PDF, DOC, DOCX, JPG, PNG.' ] );
		}
		if ( $file['size'] > 2 * 1024 * 1024 ) {
			wp_send_json_error( [ 'message' => 'File exceeds 2 MB limit.' ] );
		}

		$upload_dir = wp_upload_dir();
		$dest_dir   = $upload_dir['basedir'] . '/ah-contact/';
		wp_mkdir_p( $dest_dir );
		$real_dest  = realpath( $dest_dir );

		if ( ! $real_dest ) {
			wp_send_json_error( [ 'message' => 'Upload directory unavailable. Please contact us directly.' ] );
		}

		$safe_name = uniqid( 'ahc_', true ) . '.' . $ext;
		$dest_file = $real_dest . DIRECTORY_SEPARATOR . $safe_name;

		if ( strpos( $dest_file, $real_dest ) !== 0 ) {
			wp_send_json_error( [ 'message' => 'Invalid file path.' ] );
		}

		if ( move_uploaded_file( $file['tmp_name'], $dest_file ) ) {
			$attachment_path  = $upload_dir['baseurl'] . '/ah-contact/' . $safe_name;
			$attachment_name  = sanitize_file_name( $file['name'] );
			$email_attachment = $dest_file;
		}
	}

	// ── Email routing by type ─────────────────────────────────────────────────
	$type_routes = [
		'general'   => 'general@advaithhomes.com',
		'complaint'  => 'complaint@advaithhomes.com',
		'sales'      => 'sales@advaithhomes.com',
		'support'    => 'support@advaithhomes.com',
		'media'      => 'media@advaithhomes.com',
		'other'      => 'contact@advaithhomes.com',
	];
	$settings  = ah_get_settings();
	$to        = $type_routes[ $enquiry_type ] ?? ( $settings['email'] ?? get_option( 'admin_email' ) );
	$blog_name = get_bloginfo( 'name' );
	$headers   = [ 'Content-Type: text/html; charset=UTF-8', "Reply-To: {$name} <{$email}>" ];
	$subj      = "[{$blog_name}] " . ucfirst( $enquiry_type ) . " Enquiry from {$name}";

	$type_labels = [
		'general'   => 'General',
		'complaint'  => 'Complaint',
		'sales'      => 'Sales',
		'support'    => 'Support',
		'media'      => 'Media / Press',
		'other'      => 'Other',
	];

	$body = ah_email_wrap(
		"<h2>New Contact Enquiry</h2>
		<table style='width:100%;border-collapse:collapse'>
			<tr><td style='padding:8px;font-weight:700;width:140px;vertical-align:top'>Type</td><td style='padding:8px'>" . esc_html( $type_labels[ $enquiry_type ] ?? $enquiry_type ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Name</td><td style='padding:8px'>" . esc_html( $name ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Email</td><td style='padding:8px'>" . esc_html( $email ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Phone</td><td style='padding:8px'>" . esc_html( $phone ?: '—' ) . "</td></tr>
			" . ( $short_quote ? "<tr><td style='padding:8px;font-weight:700;vertical-align:top'>In a sentence</td><td style='padding:8px'><em>" . esc_html( $short_quote ) . "</em></td></tr>" : '' ) . "
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Message</td><td style='padding:8px'>" . nl2br( esc_html( $message ) ) . "</td></tr>
			" . ( $attachment_name ? "<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Attachment</td><td style='padding:8px'>" . esc_html( $attachment_name ) . " (see attached)</td></tr>" : '' ) . "
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Page</td><td style='padding:8px'>" . esc_html( $page_url ?: '—' ) . "</td></tr>
		</table>"
	);

	$mail_attachments = $email_attachment ? [ $email_attachment ] : [];
	$sent = wp_mail( $to, $subj, $body, $headers, $mail_attachments );

	ah_send_auto_reply( $email, $name, 'contact' );

	// Log to CMS plugin if active
	if ( class_exists( 'AH_Contact_Model' ) ) {
		$model = new AH_Contact_Model();
		$model->submit( [
			'full_name'       => $name,
			'email'           => $email,
			'phone'           => $phone,
			'subject'         => $subj,
			'message'         => $message,
			'enquiry_type'    => $enquiry_type,
			'short_quote'     => $short_quote,
			'attachment_path' => $attachment_path,
			'attachment_name' => $attachment_name,
			'email_sent'      => $sent ? 1 : 0,
			'page_url'        => $page_url,
			'user_agent'      => $user_agent,
		] );
	}

	if ( $sent ) {
		wp_send_json_success( [ 'message' => "Thank you, {$name}! We'll be in touch within 24 hours." ] );
	} else {
		wp_send_json_error( [ 'message' => 'Email could not be sent. Please try calling us directly.' ] );
	}
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

	wp_send_json_success( [ 'message' => "Thanks, {$name}! We'll send a market appraisal within 24 hours." ] );
}

// ── Utilities ────────────────────────────────────────────────────────────────

function ah_email_wrap( string $inner ): string {
	$site = get_bloginfo( 'name' );
	$url  = home_url( '/' );
	$year = gmdate( 'Y' );
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
		'contact'      => "We've received your message and a member of our team will reply within 24 hours during business hours (Mon–Sat, 9am–6pm).",
		'consultation' => "We've received your consultation request. One of our buyer's agents will call you within 4 business hours to confirm a time that works for you.",
	];

	$msg  = $messages[ $type ] ?? "We've received your enquiry and will be in touch shortly.";
	$body = ah_email_wrap(
		"<p>Hi {$name},</p>
		<p>{$msg}</p>
		<p>In the meantime, you might find our <a href='" . esc_url( home_url( '/guides/' ) ) . "' style='color:#b7791f'>buying guides</a> useful.</p>
		<p>Best regards,<br><strong>The {$blog_name} Team</strong></p>"
	);

	wp_mail( $to, "We've received your enquiry — {$blog_name}", $body, $headers );
}

/**
 * Build and send the notification email from a stored submission object.
 * Used by the admin "Send Again" action in submissions.php.
 */
function ah_resend_submission_email( object $sub ): bool {
	$type_routes = [
		'general'   => 'general@advaithhomes.com',
		'complaint'  => 'complaint@advaithhomes.com',
		'sales'      => 'sales@advaithhomes.com',
		'support'    => 'support@advaithhomes.com',
		'media'      => 'media@advaithhomes.com',
		'other'      => 'contact@advaithhomes.com',
	];
	$type_labels = [
		'general'   => 'General',
		'complaint'  => 'Complaint',
		'sales'      => 'Sales',
		'support'    => 'Support',
		'media'      => 'Media / Press',
		'other'      => 'Other',
	];

	$settings  = ah_get_settings();
	$type      = $sub->enquiry_type ?? 'general';
	$to        = $type_routes[ $type ] ?? ( $settings['email'] ?? get_option( 'admin_email' ) );
	$blog_name = get_bloginfo( 'name' );
	$name      = $sub->full_name ?? '';
	$email     = $sub->email ?? '';
	$headers   = [ 'Content-Type: text/html; charset=UTF-8', "Reply-To: {$name} <{$email}>" ];
	$subj      = "[{$blog_name}] [RESENT] " . ucfirst( $type ) . " Enquiry from {$name}";

	$body = ah_email_wrap(
		"<p style='background:#fef3c7;padding:10px;border-radius:6px;font-size:13px;margin-bottom:16px'><strong>⚠ Resent from admin</strong> — original submission: " . esc_html( wp_date( 'M j, Y g:i a', strtotime( $sub->submitted_at ) ) ) . "</p>
		<h2>Contact Enquiry</h2>
		<table style='width:100%;border-collapse:collapse'>
			<tr><td style='padding:8px;font-weight:700;width:140px;vertical-align:top'>Type</td><td style='padding:8px'>" . esc_html( $type_labels[ $type ] ?? $type ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Name</td><td style='padding:8px'>" . esc_html( $name ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Email</td><td style='padding:8px'>" . esc_html( $email ) . "</td></tr>
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Phone</td><td style='padding:8px'>" . esc_html( $sub->phone ?: '—' ) . "</td></tr>
			" . ( $sub->short_quote ? "<tr><td style='padding:8px;font-weight:700;vertical-align:top'>In a sentence</td><td style='padding:8px'><em>" . esc_html( $sub->short_quote ) . "</em></td></tr>" : '' ) . "
			<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Message</td><td style='padding:8px'>" . nl2br( esc_html( $sub->message ) ) . "</td></tr>
			" . ( $sub->attachment_name ? "<tr><td style='padding:8px;font-weight:700;vertical-align:top'>Attachment</td><td style='padding:8px'>" . esc_html( $sub->attachment_name ) . " (see attached)</td></tr>" : '' ) . "
		</table>"
	);

	// Resolve file attachment from stored URL to filesystem path
	$mail_attachments = [];
	if ( ! empty( $sub->attachment_path ) ) {
		$upload_dir = wp_upload_dir();
		$file_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $sub->attachment_path );
		$real_base  = realpath( $upload_dir['basedir'] );
		$real_file  = realpath( $file_path );
		if ( $real_file && $real_base && strpos( $real_file, $real_base ) === 0 && file_exists( $real_file ) ) {
			$mail_attachments[] = $real_file;
		}
	}

	return wp_mail( $to, $subj, $body, $headers, $mail_attachments );
}
