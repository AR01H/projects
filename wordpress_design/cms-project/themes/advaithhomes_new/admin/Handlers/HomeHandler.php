<?php
/**
 * Home Page Admin Handler - manages home page sections, newsblocks, resources, journey.
 */
defined( 'ABSPATH' ) || exit;

class ADN_Home_Handler extends ADN_Base_Handler {

	/** Save Home Page - Regulations & Hot Topics. */
	public static function handle_save_newsblocks(): void {
		self::verify_request( 'adn_save_home_newsblocks' );

		$data = array();

		// Regulations items: post_id + badge.
		$reg_items = array();
		foreach ( array_values( $_POST['regulations']['items'] ?? array() ) as $row ) {
			$pid = (int) ( $row['post_id'] ?? 0 );
			if ( ! $pid ) { continue; }
			$reg_items[] = array(
				'post_id' => $pid,
				'badge'   => sanitize_textarea_field( wp_unslash( $row['badge'] ?? 'GOV UK' ) ),
			);
			if ( count( $reg_items ) >= 5 ) { break; }
		}
		$data['regulations']['items'] = $reg_items;

		// Hot Topics items: post_id + icon.
		$ht_items = array();
		foreach ( array_values( $_POST['hot_topics']['items'] ?? array() ) as $row ) {
			$pid = (int) ( $row['post_id'] ?? 0 );
			if ( ! $pid ) { continue; }
			$ht_items[] = array(
				'post_id' => $pid,
				'icon'    => sanitize_text_field( wp_unslash( $row['icon'] ?? '🔥' ) ),
			);
			if ( count( $ht_items ) >= 5 ) { break; }
		}
		$data['hot_topics']['items'] = $ht_items;

		update_option( 'adn_home_newsblocks', $data );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'adn-theme-home', 'subtab' => 'newsblocks', 'adn_saved' => 'regulations' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/** Save Home Page - Journey Card Images. */
	public static function handle_save_journey(): void {
		self::verify_request( 'adn_save_home_journey' );

		// Taxonomy term overrides.
		$clean_term = array();
		foreach ( ( $_POST['journey_images'] ?? array() ) as $tid => $aid ) {
			$tid = absint( $tid );
			$aid = absint( $aid );
			if ( $tid > 0 && $aid > 0 ) {
				$clean_term[ $tid ] = $aid;
			}
		}
		update_option( 'adn_journey_card_images', $clean_term );

		// JSON-card overrides.
		$clean_json = array();
		foreach ( ( $_POST['journey_json_images'] ?? array() ) as $slug => $aid ) {
			$slug = sanitize_key( (string) $slug );
			$aid  = absint( $aid );
			if ( '' !== $slug && $aid > 0 ) {
				$clean_json[ $slug ] = $aid;
			}
		}
		update_option( 'adn_journey_json_images', $clean_json );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'adn-theme-home', 'subtab' => 'journey', 'adn_saved' => 'journey' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/** Save Home Page - Resources. */
	public static function handle_save_resources(): void {
		self::verify_request( 'adn_save_home_resources' );

		$library_ids = array();
		foreach ( ( $_POST['resource_ids'] ?? array() ) as $rid ) {
			$rid = absint( $rid );
			if ( $rid > 0 ) { $library_ids[] = $rid; }
		}

		update_option( 'adn_home_resources', array(
			'library_ids' => $library_ids,
			'heading'     => self::post_text( 'heading' ),
		) );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'adn-theme-home', 'subtab' => 'resources', 'adn_saved' => 'resources' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}
}
