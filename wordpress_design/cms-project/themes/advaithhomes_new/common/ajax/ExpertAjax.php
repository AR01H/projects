<?php
/**
 * Expert AJAX Handlers
 *
 * Handles expert contact form, profile unlock, and profile page rendering.
 * All expert-related AJAX and template_redirect functions live here.
 *
 * @package Adn\Theme\Common\Ajax
 */
defined( 'ABSPATH' ) || exit;

/**
 * Expert profile page: serve pages/PageExpertSingle.php for /ask-expert/{slug}/
 */
function adn_expert_full_page_render() {
	$slug = isset( $_GET['ah_expert'] ) ? sanitize_key( wp_unslash( $_GET['ah_expert'] ) ) : '';
	if ( '' === $slug && function_exists( 'adn_pretty_path_slug' ) && defined( 'SITE_EXPERT_URL' ) ) {
		$slug = adn_pretty_path_slug( SITE_EXPERT_URL );
	}
	if ( '' === $slug ) { return; }
	if ( ! class_exists( 'AH_Expert_DB' ) ) { return; }
	$expert = AH_Expert_DB::get( $slug );
	if ( ! $expert || 'active' !== $expert['status'] ) { return; }
	$_GET['ah_expert'] = $slug;
	$base     = realpath( ADN_THEME_DIR . '/pages' );
	$template = realpath( ADN_THEME_DIR . '/pages/PageExpertSingle.php' );
	if ( $base && $template && 0 === strpos( $template, $base ) && is_file( $template ) ) {
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );
		nocache_headers();
		$_ver = defined( 'LOCAL_CACHE_VERSION' ) ? LOCAL_CACHE_VERSION : (defined( 'ADN_THEME_VERSION' ) ? ADN_THEME_VERSION : '1.0');
		wp_enqueue_style( 'adn-ask-expert-style', ADN_THEME_URI . '/assets/css/ask_expert.css', array(), $_ver );
		wp_enqueue_script( 'adn-ask-expert-script', ADN_THEME_URI . '/assets/js/ask_expert.js', array(), $_ver, true );
		wp_localize_script( 'adn-ask-expert-script', 'adnExpert', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'adn_expert_contact' ),
		) );
		include $template;
		exit;
	}
}

/**
 * Expert contact form AJAX handler
 */
function adn_expert_contact_ajax() {
	check_ajax_referer( 'adn_expert_contact', 'nonce' );
	$slug         = sanitize_key( wp_unslash( isset( $_POST['expert_slug'] )   ? $_POST['expert_slug']   : '' ) );
	$sender_name  = sanitize_text_field( wp_unslash( isset( $_POST['sender_name'] )  ? $_POST['sender_name']  : '' ) );
	$sender_email = sanitize_email( wp_unslash( isset( $_POST['sender_email'] ) ? $_POST['sender_email'] : '' ) );
	$sender_phone = sanitize_text_field( wp_unslash( isset( $_POST['sender_phone'] ) ? $_POST['sender_phone'] : '' ) );
	$message      = sanitize_textarea_field( wp_unslash( isset( $_POST['message'] )  ? $_POST['message']  : '' ) );

	if ( '' === $sender_name || '' === $sender_email || '' === $message ) {
		wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', ADN_TEXT_DOMAIN ) ) );
	}
	if ( ! is_email( $sender_email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', ADN_TEXT_DOMAIN ) ) );
	}

	$expert      = class_exists( 'AH_Expert_DB' ) ? AH_Expert_DB::get( $slug ) : null;
	$expert_name = $expert ? $expert['name'] : 'Expert';
	$to_email    = ( $expert && ! empty( $expert['email'] ) ) ? $expert['email'] : get_option( 'admin_email' );

	$subject = sprintf( '[' . SITE_BRAND_NAME . '] Enquiry for %s from %s', $expert_name, $sender_name );
	$body    = "Name: {$sender_name}\nEmail: {$sender_email}\nPhone: {$sender_phone}\n\nMessage:\n{$message}";
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', "Reply-To: {$sender_name} <{$sender_email}>" );

	$sent = wp_mail( $to_email, $subject, $body, $headers );
	if ( $sent ) {
		wp_send_json_success( array( 'message' => sprintf( __( '%s will be in touch shortly.', ADN_TEXT_DOMAIN ), $expert_name ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Message could not be sent. Please try again.', ADN_TEXT_DOMAIN ) ) );
	}
}

/**
 * Expert profile unlock AJAX handler
 */
function adn_expert_unlock_ajax() {
	check_ajax_referer( 'adn_expert_unlock', 'nonce' );
	$submitted = sanitize_text_field( wp_unslash( isset( $_POST['unlock_password'] ) ? $_POST['unlock_password'] : '' ) );
	$banner    = get_option( 'adn_expert_banner', array() );
	$stored    = isset( $banner['unlock_password'] ) ? (string) $banner['unlock_password'] : '';
	if ( '' === $stored ) {
		wp_send_json_error( array( 'message' => __( 'No unlock password is set.', ADN_TEXT_DOMAIN ) ) );
	}
	if ( '' === $submitted || ! hash_equals( $stored, $submitted ) ) {
		wp_send_json_error( array( 'message' => __( 'Incorrect password. Please try again.', ADN_TEXT_DOMAIN ) ) );
	}
	$token = hash_hmac( 'sha256', $stored, wp_salt( 'secure_auth' ) );
	wp_send_json_success( array( 'token' => $token ) );
}
