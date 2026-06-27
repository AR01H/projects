<?php
defined( 'ABSPATH' ) || exit;

/**
 * Theme-level AJAX handlers for contact and guidance form submissions.
 * Model: AH_Enquiry_Model (class-adn-enquiry.php)
 * Rules Engine call stays plugin-side; only the DB write and response are here.
 */
class ADN_Form_Ajax {

	public static function init(): void {
		add_action( 'wp_ajax_ah_update_enquiry', array( __CLASS__, 'handle_update_enquiry' ) );
	}

	public static function init_public(): void {
		add_action( 'wp_ajax_ah_contact_submit',         array( __CLASS__, 'handle_contact_submit' ) );
		add_action( 'wp_ajax_nopriv_ah_contact_submit',  array( __CLASS__, 'handle_contact_submit' ) );
		add_action( 'wp_ajax_ah_guidance_submit',        array( __CLASS__, 'handle_guidance_submit' ) );
		add_action( 'wp_ajax_nopriv_ah_guidance_submit', array( __CLASS__, 'handle_guidance_submit' ) );
	}

	// ── Contact form ──────────────────────────────────────────────────────────

	public static function handle_contact_submit(): void {
		if ( ! check_ajax_referer( 'ah_enquiry_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page.' ), 403 );
		}

		if ( ! empty( $_POST['ah_hp'] ) ) {
			wp_send_json_success( array( 'message' => 'Thank you! We\'ll be in touch shortly.' ) );
		}

		$name         = sanitize_text_field(     wp_unslash( isset( $_POST['name'] )         ? $_POST['name']         : '' ) );
		$email        = sanitize_email(          wp_unslash( isset( $_POST['email'] )        ? $_POST['email']        : '' ) );
		$whatsapp     = sanitize_text_field(     wp_unslash( isset( $_POST['whatsapp'] )     ? $_POST['whatsapp']     : '' ) );
		$postcode     = sanitize_text_field(     wp_unslash( isset( $_POST['postcode'] )     ? $_POST['postcode']     : '' ) );
		$enquiry_type = sanitize_key(            wp_unslash( isset( $_POST['enquiry_type'] ) ? $_POST['enquiry_type'] : '' ) );
		$message      = sanitize_textarea_field( wp_unslash( isset( $_POST['message'] )      ? $_POST['message']      : '' ) );
		$consent      = ! empty( $_POST['consent'] );

		if ( '' === $name )        { wp_send_json_error( array( 'message' => 'Your name is required.' ) ); }
		if ( ! is_email( $email ) ) { wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) ); }
		if ( '' === $message )     { wp_send_json_error( array( 'message' => 'Please tell us how we can help.' ) ); }
		if ( ! $consent )          { wp_send_json_error( array( 'message' => 'Please agree to the privacy policy.' ) ); }

		$ip         = sanitize_text_field( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' );
		$region     = sanitize_text_field(
			isset( $_SERVER['HTTP_CF_IPCOUNTRY'] )     ? $_SERVER['HTTP_CF_IPCOUNTRY']    :
			( isset( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ? $_SERVER['HTTP_X_COUNTRY_CODE']  : '' )
		);
		$user_agent = sanitize_text_field( isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '' );

		$all_data = array(
			'name'         => $name,
			'email'        => $email,
			'whatsapp'     => $whatsapp,
			'postcode'     => $postcode,
			'enquiry_type' => $enquiry_type,
			'message'      => $message,
			'consent'      => 'agreed',
		);

		AH_Enquiry_Model::maybe_install();
		$id = AH_Enquiry_Model::create( 'contact', $name, $email, $enquiry_type, $all_data, $ip, $region, $user_agent );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Could not save your enquiry. Please try again.' ) );
		}

		if ( class_exists( 'AH_Workflow_Manager' ) ) {
			AH_Workflow_Manager::evaluate( 'contact_submit', array_merge( array(
				'full_name'    => $name,
				'email'        => $email,
				'phone'        => $whatsapp,
				'message'      => $message,
				'enquiry_type' => $enquiry_type,
				'postcode'     => $postcode,
			), $all_data ) );
		}

		wp_send_json_success( array( 'message' => "Thank you {$name}! We've received your enquiry and will be in touch soon." ) );
	}

	// ── Guidance form ─────────────────────────────────────────────────────────

	public static function handle_guidance_submit(): void {
		if ( ! check_ajax_referer( 'ah_enquiry_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page.' ), 403 );
		}

		if ( ! empty( $_POST['ah_hp'] ) ) {
			wp_send_json_success( array( 'message' => 'Thank you! We\'ll be in touch shortly.' ) );
		}

		$help_with      = sanitize_text_field(    wp_unslash( isset( $_POST['help_with'] )    ? $_POST['help_with']    : '' ) );
		$name           = sanitize_text_field(    wp_unslash( isset( $_POST['name'] )         ? $_POST['name']         : '' ) );
		$email          = sanitize_email(         wp_unslash( isset( $_POST['email'] )        ? $_POST['email']        : '' ) );
		$phone          = sanitize_text_field(    wp_unslash( isset( $_POST['phone'] )        ? $_POST['phone']        : '' ) );
		$i_am           = sanitize_text_field(    wp_unslash( isset( $_POST['i_am'] )         ? $_POST['i_am']         : '' ) );
		$requirement    = sanitize_textarea_field( wp_unslash( isset( $_POST['requirement'] )  ? $_POST['requirement']  : '' ) );
		$time_frame     = sanitize_text_field(    wp_unslash( isset( $_POST['time_frame'] )   ? $_POST['time_frame']   : '' ) );
		$contact_method = isset( $_POST['contact_method'] ) ? array_map( 'sanitize_key', (array) $_POST['contact_method'] ) : array();

		if ( '' === $help_with )   { wp_send_json_error( array( 'message' => 'Please select what you need help with.' ) ); }
		if ( '' === $name )        { wp_send_json_error( array( 'message' => 'Your name is required.' ) ); }
		if ( ! is_email( $email ) ) { wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) ); }
		if ( '' === $requirement ) { wp_send_json_error( array( 'message' => 'Please describe your requirement.' ) ); }

		$ip         = sanitize_text_field( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' );
		$region     = sanitize_text_field(
			isset( $_SERVER['HTTP_CF_IPCOUNTRY'] )     ? $_SERVER['HTTP_CF_IPCOUNTRY']    :
			( isset( $_SERVER['HTTP_X_COUNTRY_CODE'] ) ? $_SERVER['HTTP_X_COUNTRY_CODE']  : '' )
		);
		$user_agent = sanitize_text_field( isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '' );

		$all_data = array(
			'help_with'      => $help_with,
			'name'           => $name,
			'email'          => $email,
			'phone'          => $phone,
			'i_am'           => $i_am,
			'requirement'    => $requirement,
			'time_frame'     => $time_frame,
			'contact_method' => $contact_method,
		);

		AH_Enquiry_Model::maybe_install();
		$id = AH_Enquiry_Model::create( 'guidance', $name, $email, $help_with, $all_data, $ip, $region, $user_agent );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Could not save your request. Please try again.' ) );
		}

		if ( class_exists( 'AH_Workflow_Manager' ) ) {
			AH_Workflow_Manager::evaluate( 'guidance_submit', array(
				'full_name' => $name,
				'email'     => $email,
				'phone'     => $phone,
				'message'   => $requirement,
				'help_with' => $help_with,
				'i_am'      => $i_am,
			) );
		}

		wp_send_json_success( array( 'message' => "Thank you {$name}! We'll connect you with the right expert shortly." ) );
	}

	// ── Admin: update enquiry status + notes ──────────────────────────────────

	public static function handle_update_enquiry(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
		}
		if ( ! check_ajax_referer( 'ah_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
		}

		$id     = (int) ( isset( $_POST['enq_id'] )      ? $_POST['enq_id']      : 0 );
		$status = sanitize_key(           wp_unslash( isset( $_POST['sub_status'] )  ? $_POST['sub_status']  : 'new' ) );
		$notes  = sanitize_textarea_field( wp_unslash( isset( $_POST['admin_notes'] ) ? $_POST['admin_notes'] : '' ) );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Missing enquiry ID.' ) );
		}

		if ( AH_Enquiry_Model::update_meta( $id, $status, $notes ) ) {
			wp_send_json_success( array( 'message' => 'Saved.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Could not save changes.' ) );
		}
	}
}
