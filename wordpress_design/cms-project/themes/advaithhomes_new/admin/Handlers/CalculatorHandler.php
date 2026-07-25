<?php
/**
 * Calculator Admin Handler - manages calculator CRUD and settings.
 */
defined( 'ABSPATH' ) || exit;

class ADN_Calculator_Handler extends ADN_Base_Handler {

	/** Save calculator list settings (enabled/label/categories per calc). */
	public static function handle_save_list(): void {
		self::verify_request( 'adn_save_calc_list' );

		$input      = isset( $_POST['calc'] ) && is_array( $_POST['calc'] ) ? wp_unslash( $_POST['calc'] ) : array();
		$meta       = array();
		$allowed_cats = function_exists( 'adn_calculator_categories' )
			? array_keys( adn_calculator_categories() )
			: array();

		foreach ( adn_calculators() as $key => $calc ) {
			$row      = $input[ $key ] ?? array();
			$raw_cats = is_array( $row['categories'] ?? null ) ? $row['categories'] : array();
			$clean    = array();
			foreach ( $raw_cats as $c ) {
				$c = sanitize_key( $c );
				if ( in_array( $c, $allowed_cats, true ) ) { $clean[] = $c; }
			}
			$meta[ $key ] = array(
				'enabled'             => empty( $row['enabled'] ) ? 0 : 1,
				'label'               => sanitize_text_field( $row['label'] ?? '' ),
				'desc'                => sanitize_textarea_field( $row['desc'] ?? '' ),
				'categories'          => $clean,
				'thumbnail_id'        => absint( $row['thumbnail_id'] ?? 0 ),
				'highlight'           => sanitize_text_field( $row['highlight'] ?? '' ),
				'is_popular'          => empty( $row['is_popular'] ) ? 0 : 1,
				'hidden_from_listing' => empty( $row['hidden_from_listing'] ) ? 0 : 1,
				'help'                => sanitize_textarea_field( $row['help'] ?? '' ),
				'card_url'            => esc_url_raw( $row['card_url'] ?? '' ),
				'guide_label'         => sanitize_text_field( $row['guide_label'] ?? '' ),
				'guide_url'           => esc_url_raw( $row['guide_url'] ?? '' ),
				'hl_heading'          => sanitize_text_field( $row['hl_heading'] ?? '' ),
				'hl_links'            => self::sanitize_link_items( $row['hl_links'] ?? array() ),
			);
		}

		update_option( 'adn_calculators_meta', $meta );
		self::redirect_success( 'calculators', 'list', __( 'Calculator list saved.', ADN_TEXT_DOMAIN ) );
	}

	/** Save new or edited DB calculator. */
	public static function handle_save_new(): void {
		self::verify_request( 'adn_save_calc_new' );
		if ( ! class_exists( 'AH_Calculator_DB' ) ) {
			wp_die( esc_html__( 'Calculator DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$edit_key = sanitize_key( wp_unslash( $_POST['edit_key'] ?? '' ) );
		$is_edit  = '' !== $edit_key;
		$key      = $is_edit ? $edit_key : sanitize_key( wp_unslash( $_POST['calc_key'] ?? '' ) );
		$title    = self::post_text( 'title' );

		if ( '' === $key ) {
			wp_die( esc_html__( 'Calculator key is required.', ADN_TEXT_DOMAIN ) );
		}
		if ( '' === $title ) {
			wp_die( esc_html__( 'Title is required.', ADN_TEXT_DOMAIN ) );
		}

		if ( ! $is_edit ) {
			$file_tools = function_exists( 'adn_calculators' ) ? array_keys( adn_calculators() ) : array();
			if ( in_array( $key, $file_tools, true ) || null !== AH_Calculator_DB::get( $key ) ) {
				wp_die( esc_html( sprintf(
					__( 'A calculator with the key "%s" already exists.', ADN_TEXT_DOMAIN ), $key
				) ) );
			}
		}

		AH_Calculator_DB::maybe_install();

		$saved = AH_Calculator_DB::save( array(
			'calc_key'     => $key,
			'title'        => wp_unslash( $_POST['title'] ?? '' ),
			'icon'         => wp_unslash( $_POST['icon'] ?? '' ),
			'label'        => wp_unslash( $_POST['label'] ?? '' ),
			'html_content' => wp_unslash( $_POST['html_content'] ?? '' ),
			'js_content'   => wp_unslash( $_POST['js_content'] ?? '' ),
			'status'       => wp_unslash( $_POST['status'] ?? 'active' ),
		) );

		if ( ! $saved ) {
			global $wpdb;
			wp_die( esc_html__( 'Could not save calculator.', ADN_TEXT_DOMAIN )
				. ( $wpdb->last_error ? ' Error: ' . esc_html( $wpdb->last_error ) : '' ) );
		}

		self::save_calc_meta( $key );

		$msg = $is_edit
			? __( 'Calculator updated.', ADN_TEXT_DOMAIN )
			: sprintf( __( 'Calculator saved. Embed with [ah_calculator key="%s"]', ADN_TEXT_DOMAIN ), $key );

		wp_safe_redirect( add_query_arg(
			array( 'edit_key' => $key, 'adn_done' => 1, 'adn_msg' => rawurlencode( $msg ) ),
			self::tab_url( 'calculators', 'new' )
		) );
		exit;
	}

	/** Delete a DB-stored calculator. */
	public static function handle_delete(): void {
		self::verify_request( 'adn_delete_calc' );
		if ( ! class_exists( 'AH_Calculator_DB' ) ) {
			wp_die( esc_html__( 'Calculator DB class not available.', ADN_TEXT_DOMAIN ) );
		}

		$key = sanitize_key( wp_unslash( $_POST['calc_key'] ?? '' ) );
		if ( '' === $key ) { wp_die( esc_html__( 'No key provided.', ADN_TEXT_DOMAIN ) ); }
		if ( null === AH_Calculator_DB::get( $key ) ) {
			wp_die( esc_html__( 'This calculator is file-based and cannot be deleted.', ADN_TEXT_DOMAIN ) );
		}

		AH_Calculator_DB::delete( $key );
		self::redirect_success( 'calculators', 'list', __( 'Calculator deleted.', ADN_TEXT_DOMAIN ) );
	}

	/** Save calculator page settings (hero, trust bar, search, etc.). */
	public static function handle_save_page(): void {
		self::verify_request( 'adn_save_tools_page' );

		$pg = array();
		$pg['hero_title'] = self::post_text( 'hero_title' );
		$pg['hero_desc']  = self::post_textarea( 'hero_desc' );
		$pg['hero_icon']  = self::post_text( 'hero_icon' );

		for ( $i = 1; $i <= 4; $i++ ) {
			$pg[ "trust_{$i}_icon" ]     = self::post_text( "trust_{$i}_icon" );
			$pg[ "trust_{$i}_title" ]    = self::post_text( "trust_{$i}_title" );
			$pg[ "trust_{$i}_subtitle" ] = self::post_text( "trust_{$i}_subtitle" );
		}

		$pg['search_placeholder'] = self::post_text( 'search_placeholder' );

		foreach ( array( 1, 2 ) as $sn ) {
			$pg[ "sidebar_hl{$sn}_heading" ] = self::post_text( "sidebar_hl{$sn}_heading" );
			$pg[ "sidebar_hl{$sn}_items" ]   = self::post_link_items( "sidebar_hl{$sn}_items", 6 );
		}

		$pg['sidebar_help_title']     = self::post_text( 'sidebar_help_title' );
		$pg['sidebar_help_text']      = self::post_textarea( 'sidebar_help_text' );
		$pg['sidebar_help_btn_label'] = self::post_text( 'sidebar_help_btn_label' );
		$pg['sidebar_help_btn_url']   = self::post_url( 'sidebar_help_btn_url' );
		$pg['find_cta_title']         = self::post_text( 'find_cta_title' );
		$pg['find_cta_desc']          = self::post_textarea( 'find_cta_desc' );
		$pg['find_cta_btn_label']     = self::post_text( 'find_cta_btn_label' );
		$pg['find_cta_btn_url']       = self::post_url( 'find_cta_btn_url' );

		update_option( 'adn_calculators_page', $pg );
		self::redirect_success( 'calculators', 'page', __( 'Page settings saved.', ADN_TEXT_DOMAIN ) );
	}

	/** Save meta for a single calculator key. */
	private static function save_calc_meta( string $key ): void {
		$all = get_option( 'adn_calculators_meta', array() );
		if ( ! is_array( $all ) ) { $all = array(); }

		$raw_cats = array_filter( array_map( 'sanitize_key',
			array_map( 'trim', explode( ',', self::post_text( 'meta_categories' ) ) )
		));

		$all[ $key ] = array_merge( $all[ $key ] ?? array(), array(
			'desc'                => self::post_textarea( 'meta_desc' ),
			'categories'          => $raw_cats,
			'parent_terms'        => $raw_cats,
			'thumbnail_id'        => absint( $_POST['meta_thumbnail_id'] ?? 0 ),
			'highlight'           => self::post_text( 'meta_highlight' ),
			'is_popular'          => empty( $_POST['meta_is_popular'] ) ? 0 : 1,
			'is_featured'         => empty( $_POST['meta_is_featured'] ) ? 0 : 1,
			'is_suggestion'       => empty( $_POST['meta_is_suggestion'] ) ? 0 : 1,
			'featured_title'      => self::post_text( 'meta_featured_title' ),
			'featured_desc'       => self::post_textarea( 'meta_featured_desc' ),
			'benefit_1'           => self::post_text( 'meta_benefit_1' ),
			'benefit_2'           => self::post_text( 'meta_benefit_2' ),
			'benefit_3'           => self::post_text( 'meta_benefit_3' ),
			'benefit_4'           => self::post_text( 'meta_benefit_4' ),
			'hidden_from_listing' => empty( $_POST['meta_hidden_from_listing'] ) ? 0 : 1,
			'card_url'            => self::post_url( 'meta_card_url' ),
			'help'                => self::post_textarea( 'meta_help' ),
			'guide_label'         => self::post_text( 'meta_guide_label' ),
			'guide_url'           => self::post_url( 'meta_guide_url' ),
			'before_content'      => wp_kses_post( wp_unslash( $_POST['meta_before_content'] ?? '' ) ),
			'after_content'       => wp_kses_post( wp_unslash( $_POST['meta_after_content'] ?? '' ) ),
			'hl_heading'          => self::post_text( 'meta_hl_heading' ),
			'hl_links'            => self::sanitize_link_items( $_POST['meta_hl_links'] ?? array() ),
		) );

		// Only one "Featured" at a time.
		if ( ! empty( $all[ $key ]['is_featured'] ) ) {
			foreach ( $all as $_k => &$_m ) {
				if ( $_k !== $key && is_array( $_m ) ) {
					$_m['is_featured'] = 0;
				}
			}
			unset( $_m );
		}

		update_option( 'adn_calculators_meta', $all );
	}

	/** Sanitize an array of link items {icon, label, url}. */
	private static function sanitize_link_items( array $raw ): array {
		$out = array();
		foreach ( $raw as $item ) {
			if ( ! is_array( $item ) ) { continue; }
			$label = sanitize_text_field( $item['label'] ?? '' );
			if ( '' === $label ) { continue; }
			$out[] = array(
				'icon'  => sanitize_text_field( $item['icon'] ?? '' ),
				'label' => $label,
				'url'   => esc_url_raw( $item['url'] ?? '' ),
			);
		}
		return $out;
	}
}
