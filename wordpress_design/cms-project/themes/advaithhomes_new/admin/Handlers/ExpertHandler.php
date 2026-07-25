<?php
/**
 * Experts Admin Handler - manages expert CRUD and banner settings.
 */
defined( 'ABSPATH' ) || exit;

class ADN_Expert_Handler extends ADN_Base_Handler {

	/** Save or update an expert. */
	public static function handle_save(): void {
		self::verify_request( 'adn_save_expert' );
		if ( ! class_exists( 'AH_Expert_DB' ) ) {
			wp_die( esc_html__( 'Expert DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$edit_id = absint( $_POST['edit_id'] ?? 0 );
		$is_edit = $edit_id > 0;

		$name  = self::post_text( 'name' );
		$slug  = sanitize_title( self::post_text( 'expert_slug' ) );
		$title = self::post_text( 'expert_title' );

		if ( '' === $name ) {
			wp_die( esc_html__( 'Name is required.', ADN_TEXT_DOMAIN ) );
		}
		if ( '' === $slug ) {
			$slug = sanitize_title( $name );
		}

		AH_Expert_DB::maybe_install();

		$saved = AH_Expert_DB::save( array(
			'id'            => $edit_id,
			'expert_slug'   => $slug,
			'name'          => $name,
			'title'         => $title,
			'category'      => self::post_text( 'category' ),
			'bio'           => self::post_textarea( 'bio' ),
			'location'      => self::post_text( 'location' ),
			'rating'        => (float) ( $_POST['rating'] ?? 0 ),
			'reviews_count' => absint( $_POST['reviews_count'] ?? 0 ),
			'photo_id'      => absint( $_POST['photo_id'] ?? 0 ),
			'bullets'       => wp_json_encode( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['bullets'] ?? array() ) ) ) ),
			'status'        => self::post_text( 'status', 'active' ),
			'is_locked'     => empty( $_POST['is_locked'] ) ? 0 : 1,
		) );

		if ( ! $saved ) {
			global $wpdb;
			wp_die( esc_html__( 'Could not save expert.', ADN_TEXT_DOMAIN )
				. ( $wpdb->last_error ? ' Error: ' . esc_html( $wpdb->last_error ) : '' ) );
		}

		$msg = $is_edit
			? __( 'Expert updated.', ADN_TEXT_DOMAIN )
			: __( 'Expert added.', ADN_TEXT_DOMAIN );
		self::redirect_success( 'experts', 'list', $msg );
	}

	/** Delete an expert. */
	public static function handle_delete(): void {
		self::verify_request( 'adn_delete_expert' );
		if ( ! class_exists( 'AH_Expert_DB' ) ) {
			wp_die( esc_html__( 'Expert DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$id = absint( $_POST['expert_id'] ?? 0 );
		if ( $id > 0 ) {
			AH_Expert_DB::delete( $id );
		}
		self::redirect_success( 'experts', 'list', __( 'Expert deleted.', ADN_TEXT_DOMAIN ) );
	}

	/** Save expert banner settings. */
	public static function handle_save_banner(): void {
		self::verify_request( 'adn_save_expert_banner' );

		$banner = array(
			'heading'         => self::post_text( 'heading' ),
			'info'            => self::post_textarea( 'info' ),
			'unlock_password' => self::post_text( 'unlock_password' ),
			'marquee_items'   => array(),
		);

		$raw_mq = $_POST['marquee_items'] ?? array();
		if ( is_array( $raw_mq ) ) {
			foreach ( array_slice( $raw_mq, 0, 6 ) as $item ) {
				if ( ! is_array( $item ) ) { continue; }
				$label = sanitize_text_field( $item['label'] ?? '' );
				if ( '' === $label ) { continue; }
				$banner['marquee_items'][] = array(
					'icon'  => sanitize_text_field( $item['icon'] ?? '' ),
					'label' => $label,
					'note'  => sanitize_text_field( $item['note'] ?? '' ),
				);
			}
		}

		update_option( 'adn_expert_banner', $banner );
		self::redirect_success( 'experts', 'banner', __( 'Expert banner settings saved.', ADN_TEXT_DOMAIN ) );
	}
}
