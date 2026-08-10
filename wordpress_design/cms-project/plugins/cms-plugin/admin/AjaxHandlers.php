<?php
defined( 'ABSPATH' ) || exit;

class AH_Ajax_Handlers {

	public static function init() {
		$actions = array(
			'ah_toggle_status',
			'ah_get_form_fields',
			'ah_delete_item',
			'ah_update_sort_order',
			'ah_get_media',
			'ah_upload_media',
			'ah_delete_media',
			'ah_save_nav_item',
			'ah_delete_nav_item',
			// Static pages
			'ah_save_static_page',
			// Admin actions
			'ah_flush_rewrites',
			'ah_clear_transients',
			'ah_clear_audit_log',
			'ah_db_health_check',
			'ah_clear_form_submissions',
			'ah_rebuild_schema',
			'ah_schema_setup',
			// Posts quick-edit
			'ah_quick_save_post_meta',
		);
		foreach ( $actions as $action ) {
			add_action( 'wp_ajax_' . $action, array( __CLASS__, str_replace( 'ah_', 'handle_', $action ) ) );
		}
	}

	private static function verify() {
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
		if ( ! check_ajax_referer( 'ah_admin_nonce', 'nonce', false ) ) wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
	}

	// -------------------------------------------------------------------------
	// ah_toggle_status
	// -------------------------------------------------------------------------
	public static function handle_toggle_status() {
		self::verify();

		$id     = (int) ( $_POST['id'] ?? 0 );
		$table  = sanitize_key( $_POST['table'] ?? '' );
		$action = sanitize_key( $_POST['toggle_action'] ?? '' );

		if ( ! $id || ! $table ) wp_send_json_error( array( 'message' => 'Missing parameters.' ) );

		$allowed_tables = array(
			'pages', 'posts', 'reviews', 'faqs',
			'taxonomies', 'news_bar_items', 'nav_menus',
			'client_gallery', 'client_video_links', 'home_highlights',
			'home_why_us_cards', 'home_guide_points', 'home_stack_items',
			'home_difference_rows', 'home_experience_cards', 'home_why_req_cards',
			'home_featured_items', 'footer_contact_links', 'footer_social_links',
		);

		if ( ! in_array( $table, $allowed_tables, true ) ) {
			wp_send_json_error( array( 'message' => 'Invalid table.' ) );
		}

		$new_status = ( $action === 'activate' ) ? 'active' : 'inactive';
		$full_table = AH_DB_Helper::table( $table );
		global $wpdb;
		$updated = $wpdb->update( $full_table, array( 'status' => $new_status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );

		if ( false === $updated ) {
			wp_send_json_error( array( 'message' => 'Database error. Please try again.' ) );
		}

		AH_DB_Helper::log_action( 'update', $table, $id, array( 'status' => $new_status ) );
		wp_send_json_success( array( 'status' => $new_status ) );
	}

	// -------------------------------------------------------------------------
	// ah_delete_item
	// -------------------------------------------------------------------------
	public static function handle_delete_item() {
		self::verify();

		$id    = (int) ( $_POST['id'] ?? 0 );
		$model = sanitize_key( $_POST['model'] ?? '' );

		if ( ! $id || ! $model ) wp_send_json_error( array( 'message' => 'Missing parameters.' ) );

		$allowed_models = array(
			'pages', 'posts', 'reviews', 'faqs',
			'taxonomies', 'taxonomy_types', 'news_bar_items',
			'home_highlights', 'home_why_us_cards', 'home_guide_points',
			'home_stack_items', 'home_difference_rows', 'home_experience_cards',
			'home_why_req_cards', 'home_featured_items',
			'footer_contact_links', 'footer_social_links',
		);

		if ( ! in_array( $model, $allowed_models, true ) ) {
			wp_send_json_error( array( 'message' => 'Invalid model.' ) );
		}

		global $wpdb;
		$full_table = AH_DB_Helper::table( $model );
		$deleted    = $wpdb->delete( $full_table, array( 'id' => $id ), array( '%d' ) );

		if ( false === $deleted ) {
			wp_send_json_error( array( 'message' => 'Database error. Please try again.' ) );
		}

		AH_DB_Helper::log_action( 'delete', $model, $id );
		wp_send_json_success( array( 'deleted' => $id ) );
	}

	// -------------------------------------------------------------------------
	// ah_update_sort_order
	// -------------------------------------------------------------------------
	public static function handle_update_sort_order() {
		self::verify();

		$model = sanitize_key( $_POST['model'] ?? '' );
		$order = json_decode( wp_unslash( $_POST['order'] ?? '[]' ), true );

		if ( ! $model || ! is_array( $order ) ) wp_send_json_error( array( 'message' => 'Missing parameters.' ) );

		$allowed_models = array(
			'pages', 'posts', 'reviews', 'faqs',
			'taxonomies', 'news_bar_items', 'nav_menu_items',
			'home_highlights', 'home_why_us_cards', 'home_guide_points',
			'home_stack_items', 'home_difference_rows', 'home_experience_cards',
			'home_why_req_cards', 'home_featured_items',
			'footer_contact_links', 'footer_social_links',
			'client_gallery', 'client_video_links',
		);

		if ( ! in_array( $model, $allowed_models, true ) ) {
			wp_send_json_error( array( 'message' => 'Invalid model.' ) );
		}

		global $wpdb;
		$full_table = AH_DB_Helper::table( $model );
		foreach ( $order as $item ) {
			$item_id  = (int) ( $item['id']    ?? 0 );
			$item_ord = (int) ( $item['order'] ?? 0 );
			if ( ! $item_id ) continue;
			$wpdb->update( $full_table, array( 'sort_order' => $item_ord ), array( 'id' => $item_id ), array( '%d' ), array( '%d' ) );
		}

		wp_send_json_success( array( 'updated' => count( $order ) ) );
	}

	// -------------------------------------------------------------------------
	// ah_get_media  - paginated grid for media picker modal
	// -------------------------------------------------------------------------
	public static function handle_get_media() {
		self::verify();

		$paged  = (int) ( $_POST['paged'] ?? 1 );
		$search = sanitize_text_field( $_POST['search'] ?? '' );
		$mime   = sanitize_text_field( $_POST['mime'] ?? '' );

		$model  = new AH_Media_Model();
		$result = $model->get_paginated( $paged, $search, $mime );

		$items = array();
		foreach ( $result['items'] as $m ) {
			$items[] = array(
				'id'  => (int) $m->id,
				'url' => $model->get_url( (int) $m->id ),
				'alt' => esc_attr( $m->alt_text ?? '' ),
			);
		}

		wp_send_json_success( array( 'items' => $items, 'meta' => $result['meta'] ) );
	}

	// -------------------------------------------------------------------------
	// ah_upload_media
	// -------------------------------------------------------------------------
	public static function handle_upload_media() {
		self::verify();

		if ( empty( $_FILES['file'] ) ) wp_send_json_error( array( 'message' => 'No file uploaded.' ) );

		$result = AH_Uploader::upload( 'file' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$model = new AH_Media_Model();
		wp_send_json_success( array(
			'id'  => $result,
			'url' => $model->get_url( $result ),
		) );
	}

	// -------------------------------------------------------------------------
	// ah_delete_media
	// -------------------------------------------------------------------------
	public static function handle_delete_media() {
		self::verify();

		$id = (int) ( $_POST['id'] ?? 0 );
		if ( ! $id ) wp_send_json_error( array( 'message' => 'Missing ID.' ) );

		global $wpdb;
		$table = AH_DB_Helper::table( 'media' );
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id ) );

		if ( ! $row ) wp_send_json_error( array( 'message' => 'Media not found.' ) );

		// Remove file from disk - realpath() ensures the path can't escape the upload directory.
		$upload_dir    = wp_upload_dir();
		$allowed_base  = realpath( $upload_dir['basedir'] . '/ah-media' );
		$file_path     = trailingslashit( $upload_dir['basedir'] ) . 'ah-media/' . ltrim( $row->file_path ?? '', '/' );
		$resolved_path = realpath( $file_path );
		if ( $allowed_base && $resolved_path && str_starts_with( $resolved_path, $allowed_base ) ) {
			@unlink( $resolved_path );
		}

		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		AH_DB_Helper::log_action( 'delete', 'media', $id );
		wp_send_json_success( array( 'deleted' => $id ) );
	}

	// -------------------------------------------------------------------------
	// ah_save_nav_item  - inline AJAX save for nav menu items
	// -------------------------------------------------------------------------
	public static function handle_save_nav_item() {
		self::verify();

		$item_id = (int) ( $_POST['item_id'] ?? 0 );
		$menu_id = (int) ( $_POST['menu_id'] ?? 0 );

		if ( ! $menu_id ) wp_send_json_error( array( 'message' => 'Menu ID required.' ) );

		$model = new AH_Nav_Model();
		$data  = array(
			'menu_id'    => $menu_id,
			'label'      => sanitize_text_field( $_POST['label'] ?? '' ),
			'url'        => esc_url_raw( $_POST['url'] ?? '' ),
			'target'     => sanitize_key( $_POST['target'] ?? '_self' ),
			'icon_class' => sanitize_text_field( $_POST['icon_class'] ?? '' ),
			'parent_id'  => (int) ( $_POST['parent_id'] ?? 0 ) ?: null,
			'sort_order' => (int) ( $_POST['sort_order'] ?? 0 ),
			'status'     => 'active',
		);

		if ( $item_id ) {
			$model->update_item( $item_id, $data );
			$saved_id = $item_id;
		} else {
			$saved_id = $model->add_item( $data );
		}

		wp_send_json_success( array( 'id' => $saved_id ) );
	}

	// -------------------------------------------------------------------------
	// ah_delete_nav_item
	// -------------------------------------------------------------------------
	public static function handle_delete_nav_item() {
		self::verify();

		$id = (int) ( $_POST['id'] ?? 0 );
		if ( ! $id ) wp_send_json_error( array( 'message' => 'Missing ID.' ) );

		$model = new AH_Nav_Model();
		$model->delete_item( $id );
		wp_send_json_success( array( 'deleted' => $id ) );
	}

	// -------------------------------------------------------------------------
	// ah_get_form_fields - return field keys for a form (used by Triggers Maker UI)
	// -------------------------------------------------------------------------
	public static function handle_get_form_fields(): void {
		self::verify();
		$form_id = (int) ( $_POST['form_id'] ?? 0 );
		if ( ! $form_id ) wp_send_json_error( array( 'message' => 'Missing form_id.' ) );
		$fields = AH_Form_Builder::get_fields( $form_id );
		wp_send_json_success( array(
			'fields' => array_map( static function ( $f ) {
				return array( 'field_key' => $f->field_key, 'label' => $f->label );
			}, $fields ),
		) );
	}

	// -------------------------------------------------------------------------
	// Public AJAX - frontend contact form submission (no login required)
	// -------------------------------------------------------------------------

	public static function init_public(): void {
		add_action( 'wp_ajax_ah_form_submit',              array( __CLASS__, 'handle_form_submit' ) );
		add_action( 'wp_ajax_nopriv_ah_form_submit',       array( __CLASS__, 'handle_form_submit' ) );
		add_action( 'wp_ajax_ah_newsletter_subscribe',     array( __CLASS__, 'handle_newsletter_subscribe' ) );
		add_action( 'wp_ajax_nopriv_ah_newsletter_subscribe', array( __CLASS__, 'handle_newsletter_subscribe' ) );
		add_action( 'wp_ajax_ah_save_submission_meta',     array( __CLASS__, 'handle_save_submission_meta' ) );
	}

	// -------------------------------------------------------------------------
	// ah_form_submit - dynamic Form Builder submissions (public, no login needed)
	// -------------------------------------------------------------------------
	public static function handle_form_submit(): void {
		if ( ! check_ajax_referer( 'ah_frontend_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page.' ), 403 );
		}

		// Honeypot
		if ( ! empty( $_POST['ah_hp'] ) ) {
			wp_send_json_success( array( 'message' => 'Thank you! We\'ll be in touch shortly.' ) );
		}

		$form_id = (int) ( $_POST['form_id'] ?? 0 );
		if ( ! $form_id ) {
			wp_send_json_error( array( 'message' => 'Invalid form.' ) );
		}

		$form   = AH_Form_Builder::get( $form_id );
		$fields = AH_Form_Builder::get_fields( $form_id );

		if ( ! $form || 'active' !== $form->status || empty( $fields ) ) {
			wp_send_json_error( array( 'message' => 'This form is not available.' ) );
		}

		// Spam challenge, before any work is done on the payload.
		$captcha = AH_Form_Builder::verify_captcha( $form );
		if ( true !== $captcha ) {
			wp_send_json_error( array( 'message' => $captcha ) );
		}

		// ── Pass 1: collect and sanitize every posted value ──────────────────
		// Conditional logic can reference any field on the form, including one that
		// appears later, so nothing is validated until every value is in hand.
		$data       = array();
		$email_rows = array();
		$inputs     = array();

		foreach ( $fields as $field ) {
			// Step / group markers only shape the layout - they post no value.
			if ( AH_Form_Builder::is_structural( $field->field_type ) ) {
				continue;
			}

			$key = $field->field_key;

			// File fields carry their value in $_FILES, not $_POST.
			if ( 'file' === $field->field_type ) {
				$upload = AH_Form_Builder::handle_upload( $key, $field->settings, $form_id, $field->label );
				if ( is_wp_error( $upload ) ) {
					wp_send_json_error( array( 'message' => $upload->get_error_message() ) );
				}
				$data[ $key ] = $upload ? $upload['name'] : '';
				if ( $upload ) {
					$data[ '_file_' . $key ] = $upload['rel'];
				}
				$inputs[] = $field;
				continue;
			}

			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

			if ( 'checkbox' === $field->field_type && is_array( $raw ) ) {
				// Multi-select checkbox groups post as key[] => [value1, value2, ...].
				$val = implode( ', ', array_filter( array_map( 'sanitize_text_field', $raw ) ) );
			} elseif ( is_array( $raw ) ) {
				$val = ''; // Unexpected array for a non-checkbox field - ignore rather than fatal.
			} elseif ( 'textarea' === $field->field_type ) {
				$val = sanitize_textarea_field( $raw );
			} elseif ( 'url' === $field->field_type ) {
				$val = esc_url_raw( $raw );
			} else {
				$val = sanitize_text_field( $raw );
			}

			// A tel field with the country selector posts its dial code separately.
			if ( 'tel' === $field->field_type && ! empty( $field->settings['intl'] ) && '' !== $val ) {
				$cc = sanitize_text_field( wp_unslash( $_POST[ $key . '_cc' ] ?? '' ) );
				// "other" means the visitor's country is not in the list and they typed
				// their own code, so accept any plausible E.164 prefix instead.
				if ( 'other' === $cc ) {
					$typed = sanitize_text_field( wp_unslash( $_POST[ $key . '_cc_custom' ] ?? '' ) );
					$cc    = preg_match( '/^\+?[0-9]{1,4}$/', $typed ) ? '+' . ltrim( $typed, '+' ) : '';
				}
				$known = in_array( $cc, array_values( AH_Form_Builder::dial_codes() ), true );
				if ( '' !== $cc && ( $known || preg_match( '/^\+[0-9]{1,4}$/', $cc ) ) ) {
					$val = $cc . ' ' . $val;
				}
			}

			$data[ $key ] = $val;
			$inputs[]     = $field;
		}

		// ── Pass 2: drop fields their conditions rule out, then validate ─────
		$conditions = AH_Form_Builder::resolve_conditions( $fields );

		foreach ( $inputs as $field ) {
			$key     = $field->field_key;
			$applies = true;
			foreach ( $conditions[ $key ] ?? array() as $cond ) {
				if ( ! AH_Form_Builder::condition_met( $cond, $data ) ) {
					$applies = false;
					break;
				}
			}
			if ( ! $applies ) {
				// Not shown to this visitor, so it is neither required nor stored.
				$data[ $key ] = '';
				continue;
			}

			if ( $field->is_required && '' === $data[ $key ] ) {
				wp_send_json_error( array( 'message' => $field->label . ' is required.' ) );
			}

			if ( 'email' === $field->field_type && $data[ $key ] && ! is_email( $data[ $key ] ) ) {
				wp_send_json_error( array( 'message' => 'Please enter a valid email address for ' . $field->label . '.' ) );
			}

			if ( $data[ $key ] ) {
				$email_rows[] = array( 'label' => $field->label, 'value' => $data[ $key ] );
			}
		}

		// Form-level agreement checkbox validation.
		$agr = AH_Form_Builder::get_agreement( $form_id );
		if ( ! empty( $agr['enabled'] ) ) {
			$agreed = isset( $_POST['ah_agreement'] ) && '1' === (string) $_POST['ah_agreement'];
			if ( ! $agreed ) {
				$link_label = ! empty( $agr['link_text'] ) ? $agr['link_text'] : 'Terms & Conditions';
				wp_send_json_error( array( 'message' => 'Please agree to the ' . $link_label . ' to continue.' ) );
			}
			$data['_agreement'] = 'agreed';
			$email_rows[]       = array( 'label' => 'Agreement', 'value' => 'Agreed' );
		}

		// Store submission
		$sub_id = AH_Form_Builder::submit( $form_id, $data );
		if ( ! $sub_id ) {
			wp_send_json_error( array( 'message' => 'Could not save your submission. Please try again.' ) );
		}

		// Conditionally fire rules engine (per-form setting).
		if ( empty( $form->disable_rules ) ) {
			AH_Workflow_Manager::evaluate( 'form_submit', array_merge( array( 'form_id' => $form_id ), $data ) );
		}

		wp_send_json_success( array( 'message' => $form->success_message ?: 'Thank you! We will get back to you shortly.' ) );
	}

	// -------------------------------------------------------------------------
	// ah_save_submission_meta - admin-only: update status + notes per submission
	// -------------------------------------------------------------------------
	public static function handle_save_submission_meta(): void {
		if ( ! check_ajax_referer( 'ah_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Not allowed.' ), 403 );
		}
		$id     = (int) ( isset( $_POST['sub_id'] ) ? $_POST['sub_id'] : 0 );
		$status = sanitize_key( isset( $_POST['sub_status'] ) ? wp_unslash( $_POST['sub_status'] ) : 'new' );
		$notes  = sanitize_textarea_field( wp_unslash( isset( $_POST['admin_notes'] ) ? $_POST['admin_notes'] : '' ) );

		if ( AH_Form_Builder::update_submission_meta( $id, $status, $notes ) ) {
			wp_send_json_success( array( 'message' => 'Saved.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Could not save.' ) );
		}
	}

	// -------------------------------------------------------------------------
	// ah_newsletter_subscribe - public: save newsletter subscription
	// -------------------------------------------------------------------------
	public static function handle_newsletter_subscribe(): void {
		if ( ! check_ajax_referer( 'ah_newsletter_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ), 403 );
		}
		$email  = sanitize_email( wp_unslash( isset( $_POST['email'] ) ? $_POST['email'] : '' ) );
		$name   = sanitize_text_field( wp_unslash( isset( $_POST['name'] ) ? $_POST['name'] : '' ) );
		$source = sanitize_key( wp_unslash( isset( $_POST['source'] ) ? $_POST['source'] : 'website' ) );

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
		}

		AH_Newsletter::maybe_install();
		$result = AH_Newsletter::subscribe( $email, $name, $source );

		if ( 'already_subscribed' === $result ) {
			wp_send_json_success( array( 'message' => 'You are already subscribed - thank you!' ) );
		} elseif ( 'subscribed' === $result ) {
			if ( class_exists( 'AH_Workflow_Manager' ) ) {
				AH_Workflow_Manager::evaluate( 'newsletter_subscribe', array(
					'email'  => $email,
					'name'   => $name,
					'source' => $source,
				) );
			}
			wp_send_json_success( array( 'message' => 'Thank you for subscribing! You\'ll hear from us soon.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Could not save your subscription. Please try again.' ) );
		}
	}

	// -------------------------------------------------------------------------
	// ah_flush_rewrites
	// -------------------------------------------------------------------------
	public static function handle_flush_rewrites(): void {
		self::verify();
		flush_rewrite_rules( true );
		AH_DB_Helper::log_action( 'admin_action', 'system', 0, array( 'action' => 'flush_rewrites' ) );
		wp_send_json_success( array( 'message' => 'Rewrite rules flushed successfully.' ) );
	}

	// -------------------------------------------------------------------------
	// ah_clear_transients
	// -------------------------------------------------------------------------
	public static function handle_clear_transients(): void {
		self::verify();
		global $wpdb;
		$deleted = $wpdb->query(
			"DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_%' OR `option_name` LIKE '_site_transient_%'"
		);

		$cleared = array( "{$deleted} transient entries" );

		// This button is the plugin-side "Clear Cache" - make it a full clear,
		// same as the theme's Admin Actions -> Cache -> Save Settings & Clear
		// Cache, so either one fully clears everything (not just DB transients).
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
			$cleared[] = 'object cache';
		}
		if ( class_exists( 'ADN_Cache' ) ) {
			ADN_Cache::clear_all();
			$cleared[] = 'theme filesystem cache';
		}
		if ( function_exists( 'opcache_reset' ) ) {
			@opcache_reset(); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$cleared[] = 'OPcache';
		}

		AH_DB_Helper::log_action( 'admin_action', 'system', 0, array( 'action' => 'clear_transients', 'deleted' => $deleted ) );
		wp_send_json_success( array( 'message' => 'Cleared: ' . implode( ', ', $cleared ) . '.' ) );
	}

	// -------------------------------------------------------------------------
	// ah_clear_audit_log
	// -------------------------------------------------------------------------
	public static function handle_clear_audit_log(): void {
		self::verify();
		global $wpdb;
		$table = AH_DB_Helper::table( 'audit_logs' );
		$wpdb->query( "TRUNCATE TABLE `{$table}`" );
		wp_send_json_success( array( 'message' => 'Audit log cleared.' ) );
	}

	// -------------------------------------------------------------------------
	// ah_db_health_check
	// -------------------------------------------------------------------------
	public static function handle_db_health_check(): void {
		self::verify();
		global $wpdb;

		$required = array(
			'pages', 'page_sections', 'site_settings', 'admin_roles', 'nav_menus', 'nav_menu_items',
			'media', 'posts', 'reviews', 'faqs', 'taxonomies',
			'taxonomy_types', 'content_taxonomies', 'news_bar_items', 'contact_submissions', 'contact_config', 'audit_logs', 'reviews_page_header',
			'faqs_page_header', 'posts_listing_header', 'client_stories_header', 'client_gallery',
			'client_video_links', 'footer_config', 'footer_contact_links', 'footer_social_links',
			'file_links', 'forms', 'form_fields', 'form_submissions',
		);

		$missing = array();
		$ok      = 0;
		foreach ( $required as $suffix ) {
			$full = AH_DB_Helper::table( $suffix );
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full ) );
			if ( $exists ) {
				$ok++;
			} else {
				$missing[] = $full;
			}
		}

		$total   = count( $required );
		$message = "{$ok}/{$total} tables OK.";
		if ( $missing ) {
			$message .= ' Missing: ' . implode( ', ', $missing );
		}

		wp_send_json_success( array( 'message' => $message, 'ok' => empty( $missing ) ) );
	}

	// -------------------------------------------------------------------------
	// ah_clear_form_submissions
	// -------------------------------------------------------------------------
	public static function handle_clear_form_submissions(): void {
		self::verify();
		global $wpdb;
		$table   = AH_DB_Helper::table( 'form_submissions' );
		$deleted = $wpdb->query( "DELETE FROM `{$table}`" );
		AH_DB_Helper::log_action( 'admin_action', 'system', 0, array( 'action' => 'clear_form_submissions', 'deleted' => $deleted ) );
		wp_send_json_success( array( 'message' => "Cleared {$deleted} form submission(s)." ) );
	}

	// -------------------------------------------------------------------------
	// ah_rebuild_schema
	// Drops all wp_ah_* tables then runs the full installer to recreate them.
	// ALL DATA IN THOSE TABLES IS PERMANENTLY DELETED.
	// -------------------------------------------------------------------------
	public static function handle_rebuild_schema(): void {
		self::verify();
		global $wpdb;

		// Drop every table whose name starts with {prefix}ah_
		$prefix  = $wpdb->prefix . 'ah_';
		$tables  = $wpdb->get_col( $wpdb->prepare(
			'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME LIKE %s',
			DB_NAME,
			$prefix . '%'
		) );

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		$dropped = 0;
		foreach ( $tables as $tbl ) {
			$wpdb->query( "DROP TABLE IF EXISTS `{$tbl}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$dropped++;
		}
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		// Force reinstall - resets version key so install() runs fully
		delete_option( AH_DB_VERSION_KEY );
		AH_DB_Installer::install();

		wp_send_json_success( array(
			'message' => "Schema rebuilt: {$dropped} table(s) dropped and recreated from scratch.",
		) );
	}

	public static function handle_schema_setup(): void {
		self::verify();
		global $wpdb;
		AH_DB_Installer::install();
		AH_DB_Migrations::run();

		wp_send_json_success( array(
			'message' => "Schema Completed",
		) );
	}

	// -------------------------------------------------------------------------
	// ah_save_static_page
	// Writes HTML to static/{slug}.html and creates the WP page if needed.
	// -------------------------------------------------------------------------
	public static function handle_save_static_page(): void {
		self::verify();

		$slug     = sanitize_file_name( wp_unslash( $_POST['slug'] ?? '' ) );
		// Enforce safe slug: lowercase letters, numbers, hyphens only.
		$slug     = strtolower( preg_replace( '/[^a-z0-9-]/', '', $slug ) );
		$old_slug = sanitize_file_name( wp_unslash( $_POST['old_slug'] ?? '' ) );
		$old_slug = strtolower( preg_replace( '/[^a-z0-9-]/', '', $old_slug ) );
		$title    = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$html     = wp_unslash( $_POST['html'] ?? '' );
		$taxonomy_ids = array_map( 'absint', (array) ( $_POST['taxonomy_ids'] ?? array() ) );

		if ( ! $slug ) {
			wp_send_json_error( array( 'message' => 'Invalid or empty slug.' ) );
		}

		// Derive title from slug if not provided.
		$page_title = $title !== '' ? $title : ucwords( str_replace( '-', ' ', $slug ) );

		$slug_changed = $old_slug !== '' && $old_slug !== $slug;
		$model        = new AH_Static_Pages_Model();

		// Find the backing WP page - by old slug if renaming, otherwise by new slug.
		$existing = get_page_by_path( $slug_changed ? $old_slug : $slug );

		if ( $existing ) {
			$page_id = (int) $existing->ID;
			wp_update_post( array( 'ID' => $page_id, 'post_title' => $page_title, 'post_name' => $slug ) );
			update_post_meta( $page_id, '_wp_page_template', 'TemplateStaticPage.php' );
			if ( $slug_changed ) {
				$model->delete_by_slug( $old_slug );
				$message  = 'Page renamed and saved.';
				$redirect = admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $slug ) );
			} else {
				$message  = 'HTML saved.';
				$redirect = null;
			}
		} else {
			$page_id = wp_insert_post( array(
				'post_title'  => $page_title,
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
			) );
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_wp_page_template', 'TemplateStaticPage.php' );
				$message  = 'Page created and HTML saved.';
				$redirect = admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $slug ) );
			} else {
				$page_id  = 0;
				$message  = 'HTML saved (could not auto-create WP page).';
				$redirect = null;
			}
		}

		// Store the HTML in the database (source of truth - no more flat files).
		$model->upsert( $slug, $html, (int) $page_id, $page_title );

		if ( ! empty( $page_id ) ) {
			( new AH_Content_Taxonomy_Model() )->sync_terms( 'static_page', (int) $page_id, $taxonomy_ids );
		}

		wp_send_json_success( array( 'message' => $message, 'redirect' => $redirect ) );
	}

	// -------------------------------------------------------------------------
	// ah_quick_save_post_meta - save Featured, Tags, CMS Taxonomy from list quick-edit
	// -------------------------------------------------------------------------
	public static function handle_quick_save_post_meta() {
		self::verify();

		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		if ( ! $post_id ) wp_send_json_error( array( 'message' => 'Missing post ID.' ) );
		if ( ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );

		update_post_meta( $post_id, '_ah_is_featured',  ! empty( $_POST['is_featured'] )  ? '1' : '0' );
		update_post_meta( $post_id, '_ah_is_popular',   ! empty( $_POST['is_popular'] )   ? '1' : '0' );
		update_post_meta( $post_id, '_ah_is_suggested', ! empty( $_POST['is_suggested'] ) ? '1' : '0' );

		// Highlight Links - sent as JSON string from JS
		$hl_raw   = sanitize_text_field( wp_unslash( $_POST['highlight_links'] ?? '[]' ) );
		$hl_links = json_decode( $hl_raw, true );
		if ( is_array( $hl_links ) ) {
			$hl_clean = array();
			foreach ( $hl_links as $hl ) {
				$name = sanitize_text_field( $hl['name'] ?? '' );
				$url  = esc_url_raw( $hl['url'] ?? '' );
				if ( $name !== '' || $url !== '' ) {
					$hl_clean[] = array( 'name' => $name, 'url' => $url );
				}
			}
			update_post_meta( $post_id, '_ah_highlight_links', wp_json_encode( $hl_clean ) );
		}

		if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
			$taxonomy_ids = array_map( 'intval', (array) ( $_POST['taxonomy_ids'] ?? array() ) );
			( new AH_Content_Taxonomy_Model() )->sync_terms( 'wp_post', $post_id, $taxonomy_ids );
		}

		// Related Content - polymorphic links (articles, components, external/support)
		if ( class_exists( 'AH_Related_Links_Model' ) ) {
			$rl_raw  = json_decode( wp_unslash( $_POST['related_links'] ?? '[]' ), true );
			$rl_rows = is_array( $rl_raw ) ? $rl_raw : array();
			( new AH_Related_Links_Model() )->sync( 'wp_post', $post_id, $rl_rows );
		}

		AH_DB_Helper::log_action( 'update', 'posts', $post_id, array( 'action' => 'quick_edit' ) );
		wp_send_json_success( array( 'message' => 'Saved.' ) );
	}
}
