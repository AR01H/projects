<?php
defined( 'ABSPATH' ) || exit;

class AH_Ajax_Handlers {

	public static function init() {
		$actions = array(
			'ah_toggle_status',
			'ah_delete_item',
			'ah_update_sort_order',
			'ah_get_media',
			'ah_upload_media',
			'ah_delete_media',
			'ah_mark_submission',
			'ah_save_nav_item',
			'ah_delete_nav_item',
			// Static pages
			'ah_save_static_page',
			// Admin actions
			'ah_flush_rewrites',
			'ah_clear_transients',
			'ah_load_demo_data',
			'ah_clear_audit_log',
			'ah_db_health_check',
			'ah_clear_form_submissions',
			'ah_rebuild_schema',
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
			'pages', 'posts', 'services', 'reviews', 'faqs', 'team_members',
			'taxonomies', 'news_bar_items', 'nav_menus', 'about_values',
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
			wp_send_json_error( array( 'message' => 'DB error: ' . $wpdb->last_error ) );
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
			'pages', 'posts', 'services', 'reviews', 'faqs', 'team_members',
			'taxonomies', 'taxonomy_types', 'news_bar_items', 'about_values',
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
			wp_send_json_error( array( 'message' => 'DB error: ' . $wpdb->last_error ) );
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
			'pages', 'posts', 'services', 'reviews', 'faqs', 'team_members',
			'taxonomies', 'news_bar_items', 'nav_menu_items', 'about_values',
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
		foreach ( $order as $position => $id ) {
			$wpdb->update( $full_table, array( 'sort_order' => (int) $position ), array( 'id' => (int) $id ), array( '%d' ), array( '%d' ) );
		}

		wp_send_json_success( array( 'updated' => count( $order ) ) );
	}

	// -------------------------------------------------------------------------
	// ah_get_media  — paginated grid for media picker modal
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

		// Remove file from disk
		$upload_dir = wp_upload_dir();
		$file_path  = trailingslashit( $upload_dir['basedir'] ) . 'ah-media/' . ltrim( $row->file_path ?? '', '/' );
		if ( file_exists( $file_path ) ) {
			@unlink( $file_path );
		}

		$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		AH_DB_Helper::log_action( 'delete', 'media', $id );
		wp_send_json_success( array( 'deleted' => $id ) );
	}

	// -------------------------------------------------------------------------
	// ah_mark_submission
	// -------------------------------------------------------------------------
	public static function handle_mark_submission() {
		self::verify();

		$id     = (int) ( $_POST['id'] ?? 0 );
		$status = sanitize_key( $_POST['status'] ?? '' );
		$allowed = array( 'new', 'read', 'replied', 'spam', 'archived' );

		if ( ! $id || ! in_array( $status, $allowed, true ) ) {
			wp_send_json_error( array( 'message' => 'Invalid parameters.' ) );
		}

		$model = new AH_Contact_Model();
		$model->mark_status( $id, $status );
		wp_send_json_success( array( 'status' => $status ) );
	}

	// -------------------------------------------------------------------------
	// ah_save_nav_item  — inline AJAX save for nav menu items
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
	// Public AJAX — frontend contact form submission (no login required)
	// -------------------------------------------------------------------------

	public static function init_public(): void {
		add_action( 'wp_ajax_ah_contact_submit',        array( __CLASS__, 'handle_contact_submit' ) );
		add_action( 'wp_ajax_nopriv_ah_contact_submit', array( __CLASS__, 'handle_contact_submit' ) );
		add_action( 'wp_ajax_ah_form_submit',           array( __CLASS__, 'handle_form_submit' ) );
		add_action( 'wp_ajax_nopriv_ah_form_submit',    array( __CLASS__, 'handle_form_submit' ) );
	}

	// -------------------------------------------------------------------------
	// ah_form_submit — dynamic Form Builder submissions (public, no login needed)
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

		AH_Form_Builder::install_tables();
		$form   = AH_Form_Builder::get( $form_id );
		$fields = AH_Form_Builder::get_fields( $form_id );

		if ( ! $form || 'active' !== $form->status || empty( $fields ) ) {
			wp_send_json_error( array( 'message' => 'This form is not available.' ) );
		}

		// Collect + validate each configured field
		$data       = array();
		$email_rows = array();

		foreach ( $fields as $field ) {
			$key = $field->field_key;
			$raw = $_POST[ $key ] ?? '';

			$val = ( 'textarea' === $field->field_type )
				? sanitize_textarea_field( wp_unslash( $raw ) )
				: ( ( 'url' === $field->field_type )
					? esc_url_raw( $raw )
					: sanitize_text_field( wp_unslash( $raw ) ) );

			if ( $field->is_required && '' === $val ) {
				/* translators: field label */
				wp_send_json_error( array( 'message' => $field->label . ' is required.' ) );
			}

			if ( 'email' === $field->field_type && $val && ! is_email( $val ) ) {
				wp_send_json_error( array( 'message' => 'Please enter a valid email address for ' . $field->label . '.' ) );
			}

			$data[ $key ] = $val;
			if ( $val ) {
				$email_rows[] = array( 'label' => $field->label, 'value' => $val );
			}
		}

		// Store submission
		$sub_id = AH_Form_Builder::submit( $form_id, $data );
		if ( ! $sub_id ) {
			wp_send_json_error( array( 'message' => 'Could not save your submission. Please try again.' ) );
		}

		// Send email notification
		$notify = ! empty( $form->notify_email ) ? $form->notify_email : get_option( 'admin_email' );
		if ( $notify ) {
			$subject = 'New submission: ' . $form->name;
			$body    = "You have a new form submission from your website.\n\n";
			foreach ( $email_rows as $row ) {
				$body .= $row['label'] . ":\n" . $row['value'] . "\n\n";
			}
			$body .= "---\nSubmitted: " . current_time( 'mysql' ) . "\nForm: " . $form->name . " (ID #{$form_id})";
			wp_mail( $notify, $subject, $body );
		}

		wp_send_json_success( array( 'message' => $form->success_message ?: 'Thank you! We\'ll get back to you shortly.' ) );
	}

	public static function handle_contact_submit(): void {
		if ( ! check_ajax_referer( 'ah_frontend_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page.' ), 403 );
		}

		// Honeypot: bots fill this, humans don't
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_success( array( 'message' => 'Thank you! We\'ll be in touch shortly.' ) );
		}

		$name    = sanitize_text_field( $_POST['full_name'] ?? '' );
		$email   = sanitize_email( $_POST['email'] ?? '' );
		$phone   = sanitize_text_field( $_POST['phone'] ?? '' );
		$subject = sanitize_text_field( $_POST['subject'] ?? '' );
		$message = sanitize_textarea_field( $_POST['message'] ?? '' );

		if ( ! $name ) {
			wp_send_json_error( array( 'message' => 'Please enter your full name.' ) );
		}
		if ( ! $email || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
		}
		if ( ! $message ) {
			wp_send_json_error( array( 'message' => 'Please enter your message.' ) );
		}

		$model = new AH_Contact_Model();
		$id    = $model->submit( array(
			'full_name' => $name,
			'email'     => $email,
			'phone'     => $phone,
			'subject'   => $subject,
			'message'   => $message,
		) );

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Something went wrong. Please try again.' ) );
		}

		AH_DB_Helper::log_action( 'create', 'contact_form_submissions', (int) $id );
		wp_send_json_success( array( 'message' => 'Thank you! We\'ll get back to you shortly.' ) );
	}

	// -------------------------------------------------------------------------
	// ah_flush_rewrites
	// -------------------------------------------------------------------------
	public static function handle_flush_rewrites(): void {
		self::verify();
		flush_rewrite_rules( true );
		AH_DB_Helper::log_action( 'admin_action', 'system', null, array( 'action' => 'flush_rewrites' ) );
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
		AH_DB_Helper::log_action( 'admin_action', 'system', null, array( 'action' => 'clear_transients', 'deleted' => $deleted ) );
		wp_send_json_success( array( 'message' => "Cleared {$deleted} transient entries." ) );
	}

	// -------------------------------------------------------------------------
	// ah_load_demo_data
	// -------------------------------------------------------------------------
	public static function handle_load_demo_data(): void {
		self::verify();

		$samples_dir = AH_THEME_DIR . '/admin/import/samples/';
		$types       = array(
			'services'   => 'sample-services.csv',
			'reviews'    => 'sample-reviews.csv',
			'faqs'       => 'sample-faqs.csv',
			'posts'      => 'sample-posts.csv',
			'team'       => 'sample-team.csv',
			'taxonomies' => 'sample-taxonomies.csv',
			'news_bar'   => 'sample-news-bar.csv',
		);

		$summary = array();
		foreach ( $types as $type => $file ) {
			$path = $samples_dir . $file;
			if ( ! file_exists( $path ) ) {
				$summary[] = "{$type}: file missing";
				continue;
			}
			$rows   = AH_CSV_Importer::parse_file( $path );
			$result = AH_CSV_Importer::import( $type, $rows );
			$summary[] = "{$type}: {$result['imported']} imported, {$result['skipped']} skipped";
		}

		AH_DB_Helper::log_action( 'admin_action', 'system', null, array( 'action' => 'load_demo_data' ) );
		wp_send_json_success( array( 'message' => implode( ' | ', $summary ) ) );
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
			'media', 'posts', 'services', 'reviews', 'faqs', 'team_members', 'taxonomies',
			'taxonomy_types', 'news_bar_items', 'contact_submissions', 'contact_config', 'audit_logs',
			'home_hero', 'home_highlights', 'home_why_us', 'home_why_us_cards', 'home_guide',
			'home_guide_points', 'home_stack_items', 'home_difference', 'home_difference_rows',
			'home_experience', 'home_experience_cards', 'home_why_req', 'home_why_req_cards',
			'home_featured', 'home_featured_items', 'about_page_header', 'about_story',
			'about_story_points', 'about_values', 'services_page_header', 'reviews_page_header',
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
		AH_DB_Helper::log_action( 'admin_action', 'system', null, array( 'action' => 'clear_form_submissions', 'deleted' => $deleted ) );
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

		// Force reinstall — resets version key so install() runs fully
		delete_option( AH_DB_VERSION_KEY );
		AH_DB_Installer::install();

		wp_send_json_success( array(
			'message' => "Schema rebuilt: {$dropped} table(s) dropped and recreated from scratch.",
		) );
	}

	// -------------------------------------------------------------------------
	// ah_save_static_page
	// Writes HTML to static/{slug}.html and creates the WP page if needed.
	// -------------------------------------------------------------------------
	public static function handle_save_static_page(): void {
		self::verify();

		$slug = sanitize_file_name( wp_unslash( $_POST['slug'] ?? '' ) );
		// Enforce safe slug: lowercase letters, numbers, hyphens only.
		$slug = strtolower( preg_replace( '/[^a-z0-9-]/', '', $slug ) );
		$html = wp_unslash( $_POST['html'] ?? '' );

		if ( ! $slug ) {
			wp_send_json_error( array( 'message' => 'Invalid or empty slug.' ) );
		}

		$static_dir = get_template_directory() . '/static/';
		if ( ! file_exists( $static_dir ) ) {
			wp_mkdir_p( $static_dir );
		}

		$file_path = realpath( $static_dir ) . DIRECTORY_SEPARATOR . $slug . '.html';
		// Prevent path traversal: the resolved dir must match static_dir.
		if ( realpath( $static_dir ) === false || strpos( $file_path, realpath( $static_dir ) ) !== 0 ) {
			wp_send_json_error( array( 'message' => 'Invalid file path.' ) );
		}

		$is_new = ! file_exists( $file_path );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $file_path, $html );

		// Create or update the matching WordPress page.
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			update_post_meta( $existing->ID, '_wp_page_template', 'template-static-page.php' );
			$message  = 'HTML saved.';
			$redirect = null;
		} else {
			$page_id = wp_insert_post( array(
				'post_title'  => ucwords( str_replace( '-', ' ', $slug ) ),
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
			) );
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_wp_page_template', 'template-static-page.php' );
				$message  = 'Page created and HTML saved.';
				$redirect = admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $slug ) );
			} else {
				$message  = 'HTML saved (could not auto-create WP page — create it manually and set template to "Static HTML Page").';
				$redirect = null;
			}
		}

		wp_send_json_success( array( 'message' => $message, 'redirect' => $redirect ) );
	}
}
