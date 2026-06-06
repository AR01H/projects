<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handles the AJAX contact form submission for The Cane House.
 * Creates the submissions table on first run if it doesn't exist.
 */

add_action( 'wp_ajax_ch_contact_submit',        'ch_handle_contact_submit' );
add_action( 'wp_ajax_nopriv_ch_contact_submit', 'ch_handle_contact_submit' );

add_action( 'wp_ajax_ch_order_submit',        'ch_handle_order_submit' );
add_action( 'wp_ajax_nopriv_ch_order_submit', 'ch_handle_order_submit' );

add_action( 'wp_ajax_ch_booking_submit',        'ch_handle_booking_submit' );
add_action( 'wp_ajax_nopriv_ch_booking_submit', 'ch_handle_booking_submit' );

add_action( 'wp_ajax_ch_franchise_submit',        'ch_handle_franchise_submit' );
add_action( 'wp_ajax_nopriv_ch_franchise_submit', 'ch_handle_franchise_submit' );

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
	$thank_you = $contact_settings['thank_you_msg'] ?? "Thanks for your message! We'll be in touch shortly.";

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
		AH_Rules_Engine::evaluate( CH_Rules::CONTACT_FORM, [
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

// ── Order-to-Deliver handler ──────────────────────────────────────────────────

function ch_handle_order_submit(): void {
	// ── Nonce ─────────────────────────────────────────────────────────────────
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ch_frontend_nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Security check failed. Please refresh and try again.' ] );
	}

	// ── Sanitise & validate ───────────────────────────────────────────────────
	$name    = sanitize_text_field( wp_unslash( $_POST['otd_name']    ?? '' ) );
	$email   = sanitize_email(      wp_unslash( $_POST['otd_email']   ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['otd_phone']   ?? '' ) );
	$address = sanitize_textarea_field( wp_unslash( $_POST['otd_address'] ?? '' ) );
	$area    = sanitize_text_field( wp_unslash( $_POST['otd_area']    ?? '' ) );
	$date    = sanitize_text_field( wp_unslash( $_POST['otd_date']    ?? '' ) );
	$time    = sanitize_text_field( wp_unslash( $_POST['otd_time']    ?? '' ) );
	$notes   = sanitize_textarea_field( wp_unslash( $_POST['otd_notes'] ?? '' ) );

	if ( empty( $name ) ) {
		wp_send_json_error( [ 'message' => 'Please enter your name.' ] );
	}
	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
	}
	if ( empty( $address ) ) {
		wp_send_json_error( [ 'message' => 'Please enter your delivery address.' ] );
	}

	// ── Build items array ─────────────────────────────────────────────────────
	$raw_items = isset( $_POST['otd_items'] ) ? (array) $_POST['otd_items'] : [];
	$raw_qty   = isset( $_POST['otd_qty'] )   ? (array) $_POST['otd_qty']   : [];
	$items_data = [];

	foreach ( $raw_items as $item_name ) {
		$item_name = sanitize_text_field( wp_unslash( $item_name ) );
		if ( $item_name === '' ) continue;
		$qty = isset( $raw_qty[ $item_name ] ) ? max( 1, absint( $raw_qty[ $item_name ] ) ) : 1;
		$items_data[] = [ 'name' => $item_name, 'qty' => $qty ];
	}


	if ( empty( $items_data ) ) {
		wp_send_json_error( [ 'message' => 'Please select at least one item.' ] );
	}

	// Validate preferred date format
	$preferred_date = '';
	if ( $date ) {
		$d = \DateTime::createFromFormat( 'Y-m-d', $date );
		if ( $d && $d->format( 'Y-m-d' ) === $date ) {
			$preferred_date = $date;
		}
	}

	// ── Ensure tables exist, then insert ─────────────────────────────────────
	CH_Order_Data::ensure_tables();

	$order_id = CH_Order_Data::insert( [
		'name'             => $name,
		'email'            => $email,
		'phone'            => $phone,
		'delivery_address' => $address,
		'delivery_area'    => $area,
		'preferred_date'   => $preferred_date,
		'preferred_time'   => $time,
		'items'            => wp_json_encode( $items_data ),
		'special_notes'    => $notes,
		'ip_address'       => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
	] );

	if ( ! $order_id ) {
		wp_send_json_error( [ 'message' => 'Sorry, there was a problem saving your order. Please try again.' ] );
	}

	// Log the initial submission
	CH_Order_Data::log_activity( [
		'order_id'        => $order_id,
		'action'          => 'order_submitted',
		'field_name'      => 'status',
		'old_value'       => '',
		'new_value'       => 'new',
		'admin_user_id'   => 0,
		'admin_user_name' => 'Customer',
	] );

	// ── Send response immediately, then run Rules Engine in background ────────
	$thank_you = 'Thank you for your order request! Our team will review it and contact you shortly. 🌿';

	$response = wp_json_encode( [ 'success' => true, 'data' => [ 'message' => $thank_you ] ] );

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

	$items_label = implode( ', ', array_map( static function ( $i ) {
		return $i['name'] . ' ×' . $i['qty'];
	}, $items_data ) );

	if ( class_exists( 'AH_Rules_Engine' ) ) {
		AH_Rules_Engine::evaluate( CH_Rules::ORDER_TO_DELIVER, [
			'order_id'       => $order_id,
			'name'           => $name,
			'email'          => $email,
			'phone'          => $phone,
			'address'        => $address,
			'area'           => $area,
			'preferred_date' => $preferred_date,
			'preferred_time' => $time,
			'items'          => $items_label,
			'special_notes'  => $notes,
			'site_url'       => home_url(),
			'submitted_at'   => current_time( 'Y-m-d H:i:s' ),
		], true );
	}
}

// ══════════════════════════════════════════════════════════════════════════════
// BOOKING WIZARD HANDLER
// ══════════════════════════════════════════════════════════════════════════════

function ch_handle_booking_submit(): void {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ch_frontend_nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Security check failed. Please refresh and try again.' ] );
	}

	// ── Sanitise fields ───────────────────────────────────────────────────────
	$name       = sanitize_text_field( wp_unslash( $_POST['bk_name']     ?? '' ) );
	$email      = sanitize_email( wp_unslash( $_POST['bk_email']          ?? '' ) );
	$phone      = sanitize_text_field( wp_unslash( $_POST['bk_phone']     ?? '' ) );
	$occasion   = sanitize_text_field( wp_unslash( $_POST['bk_occasion']  ?? '' ) );
	$date_raw   = sanitize_text_field( wp_unslash( $_POST['bk_date']      ?? '' ) );
	$guests_raw = (int) ( $_POST['bk_guests']  ?? 0 );
	$location   = sanitize_text_field( wp_unslash( $_POST['bk_location']  ?? '' ) );
	$notes      = sanitize_textarea_field( wp_unslash( $_POST['bk_notes'] ?? '' ) );

	// Multi-value fields
	$cane_types = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['bk_cane']    ?? [] ) ) );
	$textures   = sanitize_text_field( wp_unslash( $_POST['bk_texture'] ?? '' ) );
	$flavours   = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['bk_flavour'] ?? [] ) ) );

	// Validate date
	$event_date = null;
	if ( $date_raw ) {
		$dt = DateTime::createFromFormat( 'Y-m-d', $date_raw );
		if ( $dt ) $event_date = $dt->format( 'Y-m-d' );
	}

	// ── Required field checks ─────────────────────────────────────────────────
	if ( ! $name )                          { wp_send_json_error( [ 'message' => 'Please enter your name.' ] ); }
	if ( ! is_email( $email ) )             { wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] ); }
	if ( empty( $cane_types ) )             { wp_send_json_error( [ 'message' => 'Please select at least one cane type.' ] ); }
	if ( ! $occasion )                      { wp_send_json_error( [ 'message' => 'Please select an occasion.' ] ); }
	if ( ! $location )                      { wp_send_json_error( [ 'message' => 'Please enter the venue or location.' ] ); }

	// ── Ensure tables exist ───────────────────────────────────────────────────
	CH_Schema::create_all();

	// ── Insert row ────────────────────────────────────────────────────────────
	global $wpdb;
	$table = $wpdb->prefix . 'ch_booking_requests';
	$wpdb->insert( $table, [
		'name'        => $name,
		'email'       => $email,
		'phone'       => $phone,
		'cane_types'  => implode( ', ', $cane_types ),
		'textures'    => $textures,
		'flavours'    => implode( ', ', $flavours ),
		'occasion'    => $occasion,
		'event_date'  => $event_date,
		'guest_count' => $guests_raw,
		'location'    => $location,
		'notes'       => $notes,
		'status'      => 'new',
		'ip_address'  => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
		'created_at'  => current_time( 'mysql' ),
	], [ '%s','%s','%s','%s','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s' ] );

	$booking_id = (int) $wpdb->insert_id;

	// Log submission
	if ( $booking_id ) {
		$wpdb->insert( $wpdb->prefix . 'ch_booking_logs', [
			'booking_id'      => $booking_id,
			'action'          => 'booking_submitted',
			'old_value'       => '',
			'new_value'       => 'new',
			'admin_user_id'   => 0,
			'admin_user_name' => 'visitor',
			'created_at'      => current_time( 'mysql' ),
		], [ '%d','%s','%s','%s','%d','%s','%s' ] );
	}

	// ── Fast response ─────────────────────────────────────────────────────────
	wp_send_json_success( [ 'message' => "Thanks {$name}! We've received your booking request and will be in touch within 24 hours. 🥤" ] );

	// ── Rules Engine (runs after response) ───────────────────────────────────
	if ( function_exists( 'fastcgi_finish_request' ) ) fastcgi_finish_request();

	if ( class_exists( 'AH_Rules_Engine' ) ) {
		AH_Rules_Engine::evaluate( CH_Rules::BOOKING_REQUEST, [
			'booking_id'  => $booking_id,
			'name'        => $name,
			'email'       => $email,
			'phone'       => $phone,
			'cane_types'  => implode( ', ', $cane_types ),
			'flavours'    => implode( ', ', $flavours ),
			'occasion'    => $occasion,
			'event_date'  => $event_date ?? '',
			'guests'      => $guests_raw,
			'location'    => $location,
			'notes'       => $notes,
			'site_url'    => home_url(),
			'submitted_at'=> current_time( 'Y-m-d H:i:s' ),
		], true );
	}
}

// ══════════════════════════════════════════════════════════════════════════════
// FRANCHISE ENQUIRY HANDLER
// ══════════════════════════════════════════════════════════════════════════════

function ch_handle_franchise_submit(): void {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ch_frontend_nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Security check failed. Please refresh and try again.' ] );
	}

	// ── Sanitise fields ───────────────────────────────────────────────────────
	$name       = sanitize_text_field( wp_unslash( $_POST['frn_name']       ?? '' ) );
	$email      = sanitize_email( wp_unslash( $_POST['frn_email']            ?? '' ) );
	$phone      = sanitize_text_field( wp_unslash( $_POST['frn_phone']       ?? '' ) );
	$city       = sanitize_text_field( wp_unslash( $_POST['frn_city']        ?? '' ) );
	$frn_type   = sanitize_text_field( wp_unslash( $_POST['frn_type']        ?? '' ) );
	$timeline   = sanitize_text_field( wp_unslash( $_POST['frn_timeline']    ?? '' ) );
	$investment = sanitize_text_field( wp_unslash( $_POST['frn_investment']  ?? '' ) );
	$experience = sanitize_text_field( wp_unslash( $_POST['frn_experience']  ?? '' ) );
	$message    = sanitize_textarea_field( wp_unslash( $_POST['frn_message'] ?? '' ) );

	// ── Required field checks ─────────────────────────────────────────────────
	if ( ! $name )              { wp_send_json_error( [ 'message' => 'Please enter your name.' ] ); }
	if ( ! is_email( $email ) ) { wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] ); }
	if ( ! $city )              { wp_send_json_error( [ 'message' => 'Please enter your city or area.' ] ); }
	if ( ! $frn_type )          { wp_send_json_error( [ 'message' => 'Please select a franchise type.' ] ); }

	// ── Ensure tables exist ───────────────────────────────────────────────────
	CH_Schema::create_all();

	// ── Insert row ────────────────────────────────────────────────────────────
	global $wpdb;
	$table = $wpdb->prefix . 'ch_franchise_enquiries';
	$wpdb->insert( $table, [
		'name'             => $name,
		'email'            => $email,
		'phone'            => $phone,
		'city'             => $city,
		'franchise_type'   => $frn_type,
		'timeline'         => $timeline,
		'investment_range' => $investment,
		'experience'       => $experience,
		'message'          => $message,
		'status'           => 'new',
		'ip_address'       => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
		'created_at'       => current_time( 'mysql' ),
	], [ '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' ] );

	$enquiry_id = (int) $wpdb->insert_id;

	// Log submission
	if ( $enquiry_id ) {
		$wpdb->insert( $wpdb->prefix . 'ch_franchise_logs', [
			'enquiry_id'      => $enquiry_id,
			'action'          => 'enquiry_submitted',
			'old_value'       => '',
			'new_value'       => 'new',
			'admin_user_id'   => 0,
			'admin_user_name' => 'visitor',
			'created_at'      => current_time( 'mysql' ),
		], [ '%d','%s','%s','%s','%d','%s','%s' ] );
	}

	// ── Fast response ─────────────────────────────────────────────────────────
	wp_send_json_success( [ 'message' => "Thanks {$name}! We've received your franchise enquiry and will reply personally within 24 hours. 🌿" ] );

	// ── Rules Engine (runs after response) ───────────────────────────────────
	if ( function_exists( 'fastcgi_finish_request' ) ) fastcgi_finish_request();

	if ( class_exists( 'AH_Rules_Engine' ) ) {
		AH_Rules_Engine::evaluate( CH_Rules::FRANCHISE_ENQUIRY, [
			'enquiry_id'       => $enquiry_id,
			'name'             => $name,
			'email'            => $email,
			'phone'            => $phone,
			'city'             => $city,
			'franchise_type'   => $frn_type,
			'timeline'         => $timeline,
			'investment_range' => $investment,
			'experience'       => $experience,
			'message'          => $message,
			'site_url'         => home_url(),
			'submitted_at'     => current_time( 'Y-m-d H:i:s' ),
		], true );
	}
}
