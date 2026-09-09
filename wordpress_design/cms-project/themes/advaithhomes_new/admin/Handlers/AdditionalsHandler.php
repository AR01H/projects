<?php
/**
 * Additionals Admin Handler - manages additional features (Random CTAs, etc.)
 * 100% DB / WordPress Options-driven (No JSON dependency).
 */
defined( 'ABSPATH' ) || exit;

class ADN_Additionals_Handler extends ADN_Base_Handler {

	const OPTION_CTAS     = 'ah_random_cta_messages';
	const OPTION_SETTINGS = 'ah_random_cta_settings';

	/**
	 * Default fallback 7 CTAs array (pure PHP seed).
	 *
	 * @return array
	 */
	public static function get_default_ctas(): array {
		return array(
			array(
				'id'          => 'save_money',
				'heading'     => 'Want to Save Thousands on Your UK Home?',
				'message'     => 'Our independent buyer negotiation and fee checks typically save homebuyers £5,000–£20,000+.',
				'icon'        => 'fa-solid fa-piggy-bank',
				'color'       => '#1e3a2f',
				'color_name'  => 'emerald',
				'button_name' => 'Book Free Savings Call',
				'button_url'  => '/contact/',
				'status'      => 'active',
			),
			array(
				'id'          => 'struggling_search',
				'heading'     => 'Struggling to Find the Right Property?',
				'message'     => 'Stop endlessly scrolling portals. Get matched directly with curated on-market and off-market UK homes.',
				'icon'        => 'fa-solid fa-magnifying-glass-location',
				'color'       => '#1a3c5e',
				'color_name'  => 'navy',
				'button_name' => 'Explore Property Search',
				'button_url'  => '/how-it-works/',
				'status'      => 'active',
			),
			array(
				'id'          => 'budget_clarity',
				'heading'     => 'Unsure How Much You Can Truly Borrow?',
				'message'     => 'Calculate your exact borrowing power, stamp duty, and realistic monthly repayments in under 60 seconds.',
				'icon'        => 'fa-solid fa-calculator',
				'color'       => '#c9a84c',
				'color_name'  => 'gold',
				'button_name' => 'Check Buying Power',
				'button_url'  => '/tools/mortgage-calculator/',
				'status'      => 'active',
			),
			array(
				'id'          => 'instant_help',
				'heading'     => 'Need a Quick Expert Answer on a Property?',
				'message'     => 'Ask our senior property advisors anything on WhatsApp for fast, 100% independent guidance.',
				'icon'        => 'fa-brands fa-whatsapp',
				'color'       => '#25d366',
				'color_name'  => 'whatsapp',
				'button_name' => 'Chat on WhatsApp',
				'button_url'  => 'https://wa.me/447000000000',
				'status'      => 'active',
			),
			array(
				'id'          => 'first_time_buyer',
				'heading'     => 'First-Time Buyer Feeling Overwhelmed?',
				'message'     => 'Avoid costly beginner mistakes. Follow our structured, step-by-step roadmap from search to keys.',
				'icon'        => 'fa-solid fa-compass',
				'color'       => '#0f766e',
				'color_name'  => 'teal',
				'button_name' => 'View First-Time Guide',
				'button_url'  => '/guides/buying-step-by-step/',
				'status'      => 'active',
			),
			array(
				'id'          => 'legal_maze',
				'heading'     => 'Worried About Surveys, Solicitors & Legal Delays?',
				'message'     => 'We coordinate actively with vetted UK conveyancers and RICS surveyors to protect your interests.',
				'icon'        => 'fa-solid fa-file-shield',
				'color'       => '#7c2d12',
				'color_name'  => 'bronze',
				'button_name' => 'Speak to an Expert',
				'button_url'  => '/ask-an-expert/',
				'status'      => 'active',
			),
			array(
				'id'          => 'stress_free_keys',
				'heading'     => 'Want a Truly Stress-Free Homebuying Journey?',
				'message'     => 'From initial discovery and viewing evaluations all the way to key handover and moving in.',
				'icon'        => 'fa-solid fa-key',
				'color'       => '#312e81',
				'color_name'  => 'indigo',
				'button_name' => 'See How It Works',
				'button_url'  => '/how-it-works/',
				'status'      => 'active',
			),
		);
	}

	/**
	 * Get all Random CTAs stored in WP option.
	 *
	 * @return array
	 */
	public static function get_all_ctas(): array {
		$db_ctas = get_option( self::OPTION_CTAS, null );
		if ( is_array( $db_ctas ) && ! empty( $db_ctas ) ) {
			return $db_ctas;
		}

		// Initial seed
		$defaults = self::get_default_ctas();
		update_option( self::OPTION_CTAS, $defaults );
		return $defaults;
	}

	/**
	 * Check if master Random CTA feature is enabled globally.
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		$settings = self::get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Get active CTAs only (returns empty if feature is disabled globally).
	 *
	 * @return array
	 */
	public static function get_active_ctas(): array {
		if ( ! self::is_enabled() ) {
			return array();
		}

		$all = self::get_all_ctas();
		$active = array();
		foreach ( $all as $cta ) {
			if ( isset( $cta['status'] ) && 'inactive' === $cta['status'] ) {
				continue;
			}
			$active[] = $cta;
		}
		return $active;
	}

	/**
	 * Get global CTA settings.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$defaults = array(
			'enabled'     => 1,
			'auto_inject' => 1,
		);
		$saved = get_option( self::OPTION_SETTINGS, null );
		if ( ! is_array( $saved ) ) {
			update_option( self::OPTION_SETTINGS, $defaults );
			return $defaults;
		}
		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Save / Update a single Random CTA message.
	 */
	public static function handle_save_cta(): void {
		self::verify_request( 'adn_save_random_cta' );

		$orig_id    = sanitize_key( wp_unslash( $_POST['orig_id'] ?? '' ) );
		$id         = sanitize_key( wp_unslash( $_POST['id'] ?? '' ) );
		$heading    = self::post_text( 'heading' );
		$message    = self::post_textarea( 'message' );
		$icon       = self::post_text( 'icon', 'fa-solid fa-sparkles' );
		$color      = sanitize_hex_color( wp_unslash( $_POST['color'] ?? '#1e3a2f' ) ) ?: '#1e3a2f';
		$color_name = sanitize_html_class( wp_unslash( $_POST['color_name'] ?? 'default' ) );
		$btn_name   = self::post_text( 'button_name', 'Learn More' );
		$btn_url    = self::post_text( 'button_url', '#' );
		$status     = sanitize_key( wp_unslash( $_POST['status'] ?? 'active' ) );
		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'active';
		}

		if ( '' === $id ) {
			$id = sanitize_key( substr( sanitize_title( $heading ?: 'cta' ), 0, 30 ) ) . '_' . wp_rand( 100, 999 );
		}

		if ( '' === $message ) {
			self::redirect_error( 'additionals', 'random-ctas', __( 'CTA Message copy cannot be empty.', ADN_TEXT_DOMAIN ) );
		}

		$item = array(
			'id'          => $id,
			'heading'     => $heading,
			'message'     => $message,
			'icon'        => $icon,
			'color'       => $color,
			'color_name'  => $color_name,
			'button_name' => $btn_name,
			'button_url'  => $btn_url,
			'status'      => $status,
		);

		$all = self::get_all_ctas();
		$found = false;

		foreach ( $all as $idx => $existing ) {
			$e_id = $existing['id'] ?? '';
			if ( ( '' !== $orig_id && $e_id === $orig_id ) || ( '' === $orig_id && $e_id === $id ) ) {
				$all[ $idx ] = $item;
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			$all[] = $item;
		}

		update_option( self::OPTION_CTAS, array_values( $all ) );

		self::redirect_success( 'additionals', 'random-ctas', __( 'Random CTA message saved successfully.', ADN_TEXT_DOMAIN ) );
	}

	/**
	 * Delete a Random CTA item.
	 */
	public static function handle_delete_cta(): void {
		self::verify_request( 'adn_delete_random_cta' );

		$id = sanitize_key( wp_unslash( $_POST['id'] ?? '' ) );
		if ( '' === $id ) {
			self::redirect_error( 'additionals', 'random-ctas', __( 'Invalid CTA ID.', ADN_TEXT_DOMAIN ) );
		}

		$all = self::get_all_ctas();
		$filtered = array();
		foreach ( $all as $item ) {
			if ( ( $item['id'] ?? '' ) !== $id ) {
				$filtered[] = $item;
			}
		}

		update_option( self::OPTION_CTAS, array_values( $filtered ) );

		self::redirect_success( 'additionals', 'random-ctas', __( 'CTA message removed.', ADN_TEXT_DOMAIN ) );
	}

	/**
	 * Toggle active / inactive status of a single CTA item.
	 */
	public static function handle_toggle_cta(): void {
		self::verify_request( 'adn_toggle_random_cta' );

		$id = sanitize_key( wp_unslash( $_POST['id'] ?? '' ) );
		if ( '' === $id ) {
			self::redirect_error( 'additionals', 'random-ctas', __( 'Invalid CTA ID.', ADN_TEXT_DOMAIN ) );
		}

		$all = self::get_all_ctas();
		foreach ( $all as &$item ) {
			if ( ( $item['id'] ?? '' ) === $id ) {
				$current = $item['status'] ?? 'active';
				$item['status'] = ( 'active' === $current ) ? 'inactive' : 'active';
				break;
			}
		}
		unset( $item );

		update_option( self::OPTION_CTAS, array_values( $all ) );

		self::redirect_success( 'additionals', 'random-ctas', __( 'Status updated.', ADN_TEXT_DOMAIN ) );
	}

	/**
	 * Toggle master feature enabled / disabled status.
	 */
	public static function handle_toggle_master(): void {
		self::verify_request( 'adn_toggle_cta_master' );

		$settings = self::get_settings();
		$settings['enabled'] = empty( $settings['enabled'] ) ? 1 : 0;
		update_option( self::OPTION_SETTINGS, $settings );

		$msg = ! empty( $settings['enabled'] )
			? __( 'Random CTAs feature enabled globally.', ADN_TEXT_DOMAIN )
			: __( 'Random CTAs feature disabled globally (hidden from all pages).', ADN_TEXT_DOMAIN );

		self::redirect_success( 'additionals', 'random-ctas', $msg );
	}

	/**
	 * Reset CTAs to default template array.
	 */
	public static function handle_reset_ctas(): void {
		self::verify_request( 'adn_reset_random_ctas' );

		$defaults = self::get_default_ctas();
		update_option( self::OPTION_CTAS, $defaults );

		self::redirect_success( 'additionals', 'random-ctas', __( 'Reset to default 7 CTA messages.', ADN_TEXT_DOMAIN ) );
	}

	/**
	 * Save global CTA settings.
	 */
	public static function handle_save_settings(): void {
		self::verify_request( 'adn_save_cta_settings' );

		$enabled     = empty( $_POST['enabled'] ) ? 0 : 1;
		$auto_inject = empty( $_POST['auto_inject'] ) ? 0 : 1;

		$settings = array(
			'enabled'     => $enabled,
			'auto_inject' => $auto_inject,
		);

		update_option( self::OPTION_SETTINGS, $settings );

		self::redirect_success( 'additionals', 'random-ctas', __( 'CTA settings updated.', ADN_TEXT_DOMAIN ) );
	}
}
