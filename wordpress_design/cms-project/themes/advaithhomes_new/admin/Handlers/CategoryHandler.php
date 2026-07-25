<?php
/**
 * Category Pages Admin Handler - manages per-term settings and AJAX searches.
 */
defined( 'ABSPATH' ) || exit;

class ADN_Category_Handler extends ADN_Base_Handler {

	/** Save a category term's settings (appearance, journey, calculators, etc.). */
	public static function handle_save_term(): void {
		$slug = sanitize_key( wp_unslash( $_POST['term_slug'] ?? '' ) );
		if ( '' === $slug ) {
			wp_die( esc_html__( 'Invalid term slug.', ADN_TEXT_DOMAIN ) );
		}
		self::verify_request( 'adn_save_category_term_' . $slug );

		// Appearance.
		$raw_app = isset( $_POST['appearance'] ) && is_array( $_POST['appearance'] ) ? wp_unslash( $_POST['appearance'] ) : array();
		AH_Category_Settings::save( $slug, 'appearance', array(
			'thumbnail_id' => (int) ( $raw_app['thumbnail_id'] ?? 0 ),
		) );

		// Journey.
		$raw_j = isset( $_POST['journey'] ) && is_array( $_POST['journey'] ) ? wp_unslash( $_POST['journey'] ) : array();
		$steps = array();
		if ( ! empty( $raw_j['steps'] ) && is_array( $raw_j['steps'] ) ) {
			foreach ( $raw_j['steps'] as $s ) {
				if ( ! is_array( $s ) || empty( $s['label'] ) ) { continue; }
				$steps[] = array(
					'icon'  => sanitize_text_field( $s['icon'] ?? '' ),
					'label' => sanitize_text_field( $s['label'] ),
					'desc'  => sanitize_text_field( $s['desc'] ?? '' ),
					'url'   => esc_url_raw( $s['url'] ?? '' ),
				);
			}
		}
		AH_Category_Settings::save( $slug, 'journey', array(
			'heading'       => sanitize_text_field( $raw_j['heading'] ?? '' ),
			'steps'         => $steps,
			'tip_icon'      => sanitize_text_field( $raw_j['tip_icon'] ?? '' ),
			'tip_text'      => sanitize_textarea_field( $raw_j['tip_text'] ?? '' ),
			'tip_link_label'=> sanitize_text_field( $raw_j['tip_link_label'] ?? '' ),
			'tip_link_url'  => esc_url_raw( $raw_j['tip_link_url'] ?? '' ),
		) );

		// Calculators.
		$raw_calc = isset( $_POST['calc'] ) && is_array( $_POST['calc'] ) ? wp_unslash( $_POST['calc'] ) : array();
		$selected_keys = isset( $raw_calc['selected_keys'] ) && is_array( $raw_calc['selected_keys'] )
			? array_map( 'sanitize_key', $raw_calc['selected_keys'] )
			: array();
		AH_Category_Settings::save( $slug, 'calculators', array(
			'heading'       => sanitize_text_field( $raw_calc['heading'] ?? '' ),
			'selected_keys' => $selected_keys,
		) );

		// Sidebar.
		$raw_sb = isset( $_POST['sidebar'] ) && is_array( $_POST['sidebar'] ) ? wp_unslash( $_POST['sidebar'] ) : array();
		AH_Category_Settings::save( $slug, 'sidebar', array(
			'news_heading' => sanitize_text_field( $raw_sb['news_heading'] ?? '' ),
			'cta_label'    => sanitize_text_field( $raw_sb['cta_label'] ?? '' ),
			'cta_url'      => esc_url_raw( $raw_sb['cta_url'] ?? '' ),
		) );

		// Hot Topics.
		$raw_ht = isset( $_POST['hot_topics'] ) && is_array( $_POST['hot_topics'] ) ? wp_unslash( $_POST['hot_topics'] ) : array();
		$ht_items = array();
		if ( ! empty( $raw_ht['items'] ) && is_array( $raw_ht['items'] ) ) {
			foreach ( $raw_ht['items'] as $t ) {
				$label = sanitize_text_field( $t['label'] ?? '' );
				if ( '' === $label ) { continue; }
				$ht_items[] = array(
					'icon'  => sanitize_text_field( $t['icon'] ?? '' ),
					'label' => $label,
					'url'   => esc_url_raw( $t['url'] ?? '#' ),
				);
			}
		}
		AH_Category_Settings::save( $slug, 'hot_topics', array(
			'heading'       => sanitize_text_field( $raw_ht['heading'] ?? '' ),
			'items'         => $ht_items,
			'view_all_label'=> sanitize_text_field( $raw_ht['view_all_label'] ?? '' ),
			'view_all_url'  => esc_url_raw( $raw_ht['view_all_url'] ?? '' ),
		) );

		// Featured Topics.
		$raw_ft = isset( $_POST['featured_topics'] ) && is_array( $_POST['featured_topics'] ) ? wp_unslash( $_POST['featured_topics'] ) : array();
		$ft_items = array();
		if ( ! empty( $raw_ft['items'] ) && is_array( $raw_ft['items'] ) ) {
			foreach ( $raw_ft['items'] as $t ) {
				$name = sanitize_text_field( $t['name'] ?? '' );
				if ( '' === $name ) { continue; }
				$ft_items[] = array(
					'icon' => sanitize_text_field( $t['icon'] ?? '' ),
					'name' => $name,
					'url'  => esc_url_raw( $t['url'] ?? '#' ),
				);
			}
		}
		AH_Category_Settings::save( $slug, 'featured_topics', array(
			'heading' => sanitize_text_field( $raw_ft['heading'] ?? '' ),
			'items'   => $ft_items,
		) );

		// FAQs.
		$raw_faqs = isset( $_POST['faqs'] ) && is_array( $_POST['faqs'] ) ? wp_unslash( $_POST['faqs'] ) : array();
		$faq_items = array();
		if ( ! empty( $raw_faqs['items'] ) && is_array( $raw_faqs['items'] ) ) {
			foreach ( $raw_faqs['items'] as $fi ) {
				$faq_id = absint( $fi['faq_id'] ?? 0 );
				if ( $faq_id > 0 ) {
					$faq_items[] = array( 'faq_id' => $faq_id );
				}
			}
		}
		AH_Category_Settings::save( $slug, 'faqs', array(
			'heading' => sanitize_text_field( $raw_faqs['heading'] ?? '' ),
			'items'   => $faq_items,
		) );

		// CTA Banner.
		$raw_cta = isset( $_POST['cta_banner'] ) && is_array( $_POST['cta_banner'] ) ? wp_unslash( $_POST['cta_banner'] ) : array();
		AH_Category_Settings::save( $slug, 'cta_banner', array(
			'icon'        => sanitize_text_field( $raw_cta['icon'] ?? '' ),
			'title'       => sanitize_text_field( $raw_cta['title'] ?? '' ),
			'description' => sanitize_textarea_field( $raw_cta['description'] ?? '' ),
			'btn_label'   => sanitize_text_field( $raw_cta['btn_label'] ?? '' ),
			'btn_url'     => esc_url_raw( $raw_cta['btn_url'] ?? '' ),
		) );

		// Clear category context cache.
		if ( class_exists( 'ADN_Cache' ) ) {
			$_cache_file = \ADN_Cache::get_cache_dir() . '/pages/' . md5( 'page_category_context_' . $slug ) . '.json';
			if ( is_file( $_cache_file ) ) {
				@unlink( $_cache_file );
			}
		}

		self::redirect_success( 'category-pages', $slug, __( 'Category settings saved.', ADN_TEXT_DOMAIN ) );
	}

	/** AJAX: search posts for Hot Topics / Popular Posts. */
	public static function handle_post_search(): void {
		check_ajax_referer( 'adn_cat_search', 'nonce' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Unauthorised', 403 );
		}

		$q    = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
		$slug = sanitize_key( wp_unslash( $_GET['slug'] ?? '' ) );

		if ( mb_strlen( $q ) < 2 ) {
			wp_send_json_success( array() );
		}

		$results  = array();
		$ids_seen = array();

		if ( $slug && function_exists( 'adn_cms_articles_for_parent' ) ) {
			foreach ( (array) adn_cms_articles_for_parent( $slug, 60 ) as $p ) {
				$title = $p->title ?? '';
				if ( '' === $title || false === stripos( $title, $q ) ) { continue; }
				$results[] = array( 'id' => (int) $p->ID, 'title' => $title, 'url' => get_permalink( (int) $p->ID ) );
				$ids_seen[] = (int) $p->ID;
				if ( count( $results ) >= 8 ) { break; }
			}
		}

		if ( count( $results ) < 10 ) {
			$args = array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				's'              => $q,
				'posts_per_page' => 10 - count( $results ),
				'no_found_rows'  => true,
			);
			if ( ! empty( $ids_seen ) ) { $args['post__not_in'] = $ids_seen; }
			$wp_q = new WP_Query( $args );
			foreach ( (array) $wp_q->posts as $p ) {
				$results[] = array( 'id' => $p->ID, 'title' => $p->post_title, 'url' => get_permalink( $p->ID ) );
			}
			wp_reset_postdata();
		}

		wp_send_json_success( array_slice( $results, 0, 10 ) );
	}

	/** AJAX: search taxonomy terms for Featured Topics. */
	public static function handle_taxonomy_search(): void {
		check_ajax_referer( 'adn_cat_search', 'nonce' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Unauthorised', 403 );
		}

		$q    = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
		$slug = sanitize_key( wp_unslash( $_GET['slug'] ?? '' ) );

		if ( '' === $q || '' === $slug ) {
			wp_send_json_success( array() );
		}

		$parent = function_exists( 'adn_cms_parent_by_slug' ) ? adn_cms_parent_by_slug( $slug ) : null;
		if ( ! $parent ) { wp_send_json_success( array() ); }

		$topics = function_exists( 'adn_cms_topics' ) ? adn_cms_topics( (int) $parent->id, 100 ) : array();
		$results = array();
		foreach ( (array) $topics as $topic ) {
			$name = $topic->name ?? '';
			if ( '' === $name || false === stripos( $name, $q ) ) { continue; }
			$results[] = array(
				'id'    => (int) $topic->id,
				'title' => $name,
				'url'   => '/' . $slug . '/?topic=' . rawurlencode( $topic->slug ?? '' ),
				'icon'  => $topic->icon_emoji ?? '',
			);
			if ( count( $results ) >= 10 ) { break; }
		}

		wp_send_json_success( $results );
	}

	/** AJAX: search FAQs from the ah_faqs table. */
	public static function handle_faq_search(): void {
		check_ajax_referer( 'adn_cat_search', 'nonce' );
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Unauthorised', 403 );
		}

		$q = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
		if ( mb_strlen( $q ) < 2 ) { wp_send_json_success( array() ); }

		global $wpdb;
		$table = $wpdb->prefix . 'ah_faqs';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			wp_send_json_success( array() );
		}

		$like = '%' . $wpdb->esc_like( $q ) . '%';
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, question FROM `{$table}` WHERE status = 'active' AND question LIKE %s ORDER BY sort_order ASC, id ASC LIMIT 10",
			$like
		) );

		$results = array();
		foreach ( (array) $rows as $row ) {
			$results[] = array( 'id' => (int) $row->id, 'title' => (string) $row->question );
		}
		wp_send_json_success( $results );
	}
}
