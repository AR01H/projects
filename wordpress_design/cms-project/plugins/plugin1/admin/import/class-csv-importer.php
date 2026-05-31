<?php
defined( 'ABSPATH' ) || exit;

class AH_CSV_Importer {

	// -------------------------------------------------------------------------
	// Config: expected columns per import type
	// -------------------------------------------------------------------------
	public static function get_config(): array {
		return array(
			'services'   => array(
				'label'    => 'Services',
				'required' => array( 'title' ),
				'columns'  => array(
					'title'             => 'Service title (required)',
					'slug'              => 'URL slug - auto-generated from title if blank',
					'short_description' => 'Short description shown in listings',
					'full_description'  => 'Full HTML/text description (detail page)',
					'sort_order'        => 'Integer - display order (default 0)',
					'status'            => 'active | inactive | draft  (default active)',
					'meta_title'        => 'SEO title',
					'meta_description'  => 'SEO description',
					'meta_keywords'     => 'SEO keywords',
				),
			),
			'reviews'    => array(
				'label'    => 'Reviews',
				'required' => array( 'reviewer_name', 'review_text', 'star_rating' ),
				'columns'  => array(
					'reviewer_name'  => 'Full name of reviewer (required)',
					'reviewer_title' => 'Job title / role',
					'company'        => 'Company name',
					'review_text'    => 'The review body (required)',
					'star_rating'    => '1–5 (required)',
					'source'         => 'google | facebook | website | direct',
					'is_featured'    => '1 = featured, 0 = not featured (default 0)',
					'sort_order'     => 'Integer display order (default 0)',
					'status'         => 'active | inactive (default active)',
				),
			),
			'faqs'       => array(
				'label'    => 'FAQs',
				'required' => array( 'question', 'answer' ),
				'columns'  => array(
					'question'   => 'FAQ question (required)',
					'answer'     => 'FAQ answer - plain text or HTML (required)',
					'link_text'  => 'Optional CTA link label',
					'link_url'   => 'Optional CTA link URL',
					'sort_order' => 'Integer display order (default 0)',
					'status'     => 'active | inactive (default active)',
				),
			),
			'posts'      => array(
				'label'    => 'Posts / Blog',
				'required' => array( 'title', 'post_type' ),
				'columns'  => array(
					'title'            => 'Post title (required)',
					'post_type'        => 'blog | news | article (required)',
					'slug'             => 'URL slug - auto-generated if blank',
					'excerpt'          => 'Short summary',
					'content'          => 'Full post content (HTML allowed)',
					'is_featured'      => '1 = featured (default 0)',
					'status'           => 'active | draft | inactive (default draft)',
					'meta_title'       => 'SEO title',
					'meta_description' => 'SEO description',
					'meta_keywords'    => 'SEO keywords',
				),
			),
			'team'       => array(
				'label'    => 'Team Members',
				'required' => array( 'name', 'designation' ),
				'columns'  => array(
					'name'         => 'Full name (required)',
					'designation'  => 'Job title / role (required)',
					'bio'          => 'Short biography',
					'email'        => 'Email address',
					'linkedin_url' => 'LinkedIn profile URL',
					'is_featured'  => '1 = featured (default 0)',
					'sort_order'   => 'Integer display order (default 0)',
					'status'       => 'active | inactive (default active)',
				),
			),
			'taxonomies' => array(
				'label'    => 'Categories & Tags',
				'required' => array( 'name', 'type_slug' ),
				'columns'  => array(
					'name'        => 'Term name (required)',
					'type_slug'   => 'Taxonomy type slug, e.g. category | tag | subtag (required)',
					'slug'        => 'URL slug - auto-generated if blank',
					'parent_slug' => 'Slug of parent term (leave blank for top-level)',
					'description' => 'Optional description',
					'sort_order'  => 'Integer display order (default 0)',
					'status'      => 'active | inactive (default active)',
				),
			),
			'news_bar'   => array(
				'label'    => 'News Bar',
				'required' => array( 'text' ),
				'columns'  => array(
					'text'       => 'Ticker text (required)',
					'link_url'   => 'Optional click-through URL',
					'start_date' => 'YYYY-MM-DD  (leave blank = always show)',
					'end_date'   => 'YYYY-MM-DD  (leave blank = no expiry)',
					'sort_order' => 'Integer display order (default 0)',
					'status'     => 'active | inactive (default active)',
				),
			),
			'events'     => array(
				'label'    => 'Events / Hire Packages',
				'required' => array( 'title' ),
				'columns'  => array(
					'title'       => 'Event / package name (required)',
					'icon'        => 'Emoji icon for the card (default 🎉)',
					'description' => 'Short description shown on the card',
					'items'       => 'Pipe-separated bullet points e.g. "Live pressing|200 servings|Setup included"',
					'color'       => 'green | amber | teal | purple | coral | indigo (default green)',
					'is_featured' => '1 = show in homepage preview, 0 = events page only (default 0)',
					'sort_order'  => 'Integer display order (default 0)',
					'status'      => 'active | inactive (default active)',
				),
			),
		);
	}

	// -------------------------------------------------------------------------
	// Parse uploaded CSV file → array of associative rows
	// -------------------------------------------------------------------------
	public static function parse_file( string $tmp_path ): array {
		if ( ! file_exists( $tmp_path ) || ! is_readable( $tmp_path ) ) {
			return array();
		}

		$rows   = array();
		$handle = fopen( $tmp_path, 'r' );
		if ( ! $handle ) return $rows;

		// Strip UTF-8 BOM if present
		$bom = fread( $handle, 3 );
		if ( $bom !== "\xEF\xBB\xBF" ) {
			rewind( $handle );
		}

		$headers = fgetcsv( $handle, 0, ',', '"', '\\' );
		if ( ! $headers ) {
			fclose( $handle );
			return $rows;
		}
		$headers = array_map( 'trim', $headers );

		while ( ( $row = fgetcsv( $handle, 0, ',', '"', '\\' ) ) !== false ) {
			if ( count( $row ) < count( $headers ) ) {
				$row = array_pad( $row, count( $headers ), '' );
			}
			$assoc = array_combine( $headers, array_slice( $row, 0, count( $headers ) ) );
			$assoc = array_map( 'trim', $assoc );
			// Skip blank rows
			if ( array_filter( $assoc ) ) {
				$rows[] = $assoc;
			}
		}

		fclose( $handle );
		return $rows;
	}

	// -------------------------------------------------------------------------
	// Main dispatch
	// -------------------------------------------------------------------------
	public static function import( string $type, array $rows ): array {
		$config = self::get_config();
		if ( ! isset( $config[ $type ] ) || empty( $rows ) ) {
			return array( 'imported' => 0, 'skipped' => 0, 'errors' => array( 'Invalid import type or empty file.' ) );
		}

		switch ( $type ) {
			case 'services':   return self::import_services( $rows );
			case 'reviews':    return self::import_reviews( $rows );
			case 'faqs':       return self::import_faqs( $rows );
			case 'posts':      return self::import_posts( $rows );
			case 'team':       return self::import_team( $rows );
			case 'taxonomies': return self::import_taxonomies( $rows );
			case 'news_bar':   return self::import_news_bar( $rows );
			case 'events':     return self::import_events( $rows );
		}
		return array( 'imported' => 0, 'skipped' => 0, 'errors' => array( 'Unknown type.' ) );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------
	private static function status( string $raw, array $allowed = array( 'active', 'inactive', 'draft' ), string $default = 'active' ): string {
		return in_array( $raw, $allowed, true ) ? $raw : $default;
	}

	private static function result_add( array &$result, bool $ok, string $error = '' ): void {
		if ( $ok ) {
			$result['imported']++;
		} else {
			$result['skipped']++;
			if ( $error ) $result['errors'][] = $error;
		}
	}

	private static function page_id( string $type ): int {
		global $wpdb;
		$table = AH_DB_Helper::table( 'pages' );
		$id    = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE page_type = %s AND status = 'active' LIMIT 1", $type ) );
		return $id ? (int) $id : 0;
	}

	// -------------------------------------------------------------------------
	// Services
	// -------------------------------------------------------------------------
	private static function import_services( array $rows ): array {
		global $wpdb;
		$table   = AH_DB_Helper::table( 'services' );
		$page_id = self::page_id( 'services' );
		$result  = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $rows as $i => $row ) {
			$line  = $i + 2;
			$title = sanitize_text_field( $row['title'] ?? '' );
			if ( ! $title ) {
				self::result_add( $result, false, "Row {$line}: 'title' is required." );
				continue;
			}
			$raw_slug = sanitize_title( $row['slug'] ?? '' );
			$slug     = $raw_slug ?: AH_Slug_Helper::generate( $title, $table, 'slug' );
			if ( $raw_slug ) {
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s", $slug ) );
				if ( $exists ) $slug = AH_Slug_Helper::generate( $title, $table, 'slug' );
			}

			$wpdb->insert( $table, array(
				'page_id'           => $page_id ?: null,
				'title'             => $title,
				'slug'              => $slug,
				'short_description' => sanitize_textarea_field( $row['short_description'] ?? '' ),
				'full_description'  => wp_kses_post( $row['full_description'] ?? '' ),
				'sort_order'        => (int) ( $row['sort_order'] ?? 0 ),
				'status'            => self::status( $row['status'] ?? '' ),
				'meta_title'        => sanitize_text_field( $row['meta_title'] ?? '' ),
				'meta_description'  => sanitize_textarea_field( $row['meta_description'] ?? '' ),
				'meta_keywords'     => sanitize_text_field( $row['meta_keywords'] ?? '' ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// Reviews
	// -------------------------------------------------------------------------
	private static function import_reviews( array $rows ): array {
		global $wpdb;
		$table   = AH_DB_Helper::table( 'reviews' );
		$page_id = self::page_id( 'reviews' );
		$result  = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );
		$sources = array( 'google', 'facebook', 'website', 'direct' );

		foreach ( $rows as $i => $row ) {
			$line   = $i + 2;
			$name   = sanitize_text_field( $row['reviewer_name'] ?? '' );
			$text   = sanitize_textarea_field( $row['review_text'] ?? '' );
			$rating = (int) ( $row['star_rating'] ?? 0 );

			if ( ! $name )              { self::result_add( $result, false, "Row {$line}: 'reviewer_name' is required." ); continue; }
			if ( ! $text )              { self::result_add( $result, false, "Row {$line}: 'review_text' is required." ); continue; }
			if ( $rating < 1 || $rating > 5 ) { self::result_add( $result, false, "Row {$line}: 'star_rating' must be 1–5." ); continue; }

			$wpdb->insert( $table, array(
				'page_id'        => $page_id ?: null,
				'reviewer_name'  => $name,
				'reviewer_title' => sanitize_text_field( $row['reviewer_title'] ?? '' ),
				'company'        => sanitize_text_field( $row['company'] ?? '' ),
				'review_text'    => $text,
				'star_rating'    => $rating,
				'source'         => in_array( $row['source'] ?? '', $sources, true ) ? $row['source'] : 'website',
				'is_featured'    => (int) ( $row['is_featured'] ?? 0 ) ? 1 : 0,
				'sort_order'     => (int) ( $row['sort_order'] ?? 0 ),
				'status'         => self::status( $row['status'] ?? '', array( 'active', 'inactive' ) ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// FAQs
	// -------------------------------------------------------------------------
	private static function import_faqs( array $rows ): array {
		global $wpdb;
		$table  = AH_DB_Helper::table( 'faqs' );
		$result = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $rows as $i => $row ) {
			$line     = $i + 2;
			$question = sanitize_text_field( $row['question'] ?? '' );
			$answer   = wp_kses_post( $row['answer'] ?? '' );

			if ( ! $question ) { self::result_add( $result, false, "Row {$line}: 'question' is required." ); continue; }
			if ( ! $answer )   { self::result_add( $result, false, "Row {$line}: 'answer' is required." ); continue; }

			$wpdb->insert( $table, array(
				'question'   => $question,
				'answer'     => $answer,
				'link_text'  => sanitize_text_field( $row['link_text'] ?? '' ),
				'link_url'   => esc_url_raw( $row['link_url'] ?? '' ),
				'sort_order' => (int) ( $row['sort_order'] ?? 0 ),
				'status'     => self::status( $row['status'] ?? '' ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// Posts
	// -------------------------------------------------------------------------
	private static function import_posts( array $rows ): array {
		global $wpdb;
		$table      = AH_DB_Helper::table( 'posts' );
		$post_types = array( 'blog', 'news', 'article' );
		$result     = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $rows as $i => $row ) {
			$line      = $i + 2;
			$title     = sanitize_text_field( $row['title'] ?? '' );
			$post_type = sanitize_key( $row['post_type'] ?? '' );

			if ( ! $title )                                       { self::result_add( $result, false, "Row {$line}: 'title' is required." ); continue; }
			if ( ! in_array( $post_type, $post_types, true ) )   { self::result_add( $result, false, "Row {$line}: 'post_type' must be blog | news | article." ); continue; }

			$raw_slug = sanitize_title( $row['slug'] ?? '' );
			$slug     = $raw_slug ?: AH_Slug_Helper::generate( $title, $table, 'slug' );
			if ( $raw_slug ) {
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s AND post_type = %s", $slug, $post_type ) );
				if ( $exists ) $slug = AH_Slug_Helper::generate( $title, $table, 'slug' );
			}

			$wpdb->insert( $table, array(
				'title'            => $title,
				'slug'             => $slug,
				'post_type'        => $post_type,
				'excerpt'          => sanitize_textarea_field( $row['excerpt'] ?? '' ),
				'content'          => wp_kses_post( $row['content'] ?? '' ),
				'is_featured'      => (int) ( $row['is_featured'] ?? 0 ) ? 1 : 0,
				'status'           => self::status( $row['status'] ?? 'draft', array( 'active', 'draft', 'inactive' ), 'draft' ),
				'meta_title'       => sanitize_text_field( $row['meta_title'] ?? '' ),
				'meta_description' => sanitize_textarea_field( $row['meta_description'] ?? '' ),
				'meta_keywords'    => sanitize_text_field( $row['meta_keywords'] ?? '' ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// Team
	// -------------------------------------------------------------------------
	private static function import_team( array $rows ): array {
		global $wpdb;
		$table  = AH_DB_Helper::table( 'team_members' );
		$result = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $rows as $i => $row ) {
			$line        = $i + 2;
			$name        = sanitize_text_field( $row['name'] ?? '' );
			$designation = sanitize_text_field( $row['designation'] ?? '' );

			if ( ! $name )        { self::result_add( $result, false, "Row {$line}: 'name' is required." ); continue; }
			if ( ! $designation ) { self::result_add( $result, false, "Row {$line}: 'designation' is required." ); continue; }

			$wpdb->insert( $table, array(
				'name'         => $name,
				'designation'  => $designation,
				'bio'          => sanitize_textarea_field( $row['bio'] ?? '' ),
				'email'        => sanitize_email( $row['email'] ?? '' ),
				'linkedin_url' => esc_url_raw( $row['linkedin_url'] ?? '' ),
				'is_featured'  => (int) ( $row['is_featured'] ?? 0 ) ? 1 : 0,
				'sort_order'   => (int) ( $row['sort_order'] ?? 0 ),
				'status'       => self::status( $row['status'] ?? '', array( 'active', 'inactive' ) ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// Taxonomies
	// -------------------------------------------------------------------------
	private static function import_taxonomies( array $rows ): array {
		global $wpdb;
		$table      = AH_DB_Helper::table( 'taxonomies' );
		$types_t    = AH_DB_Helper::table( 'taxonomy_types' );
		$result     = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );
		$type_cache = array();

		foreach ( $rows as $i => $row ) {
			$line      = $i + 2;
			$name      = sanitize_text_field( $row['name'] ?? '' );
			$type_slug = sanitize_key( $row['type_slug'] ?? '' );

			if ( ! $name )      { self::result_add( $result, false, "Row {$line}: 'name' is required." ); continue; }
			if ( ! $type_slug ) { self::result_add( $result, false, "Row {$line}: 'type_slug' is required." ); continue; }

			// Resolve type_id (cached)
			if ( ! isset( $type_cache[ $type_slug ] ) ) {
				$type_cache[ $type_slug ] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$types_t}` WHERE slug = %s", $type_slug ) );
			}
			$type_id = $type_cache[ $type_slug ];
			if ( ! $type_id ) { self::result_add( $result, false, "Row {$line}: taxonomy type '{$type_slug}' not found." ); continue; }

			// Resolve parent_id
			$parent_id  = null;
			$parent_slug = sanitize_title( $row['parent_slug'] ?? '' );
			if ( $parent_slug ) {
				$parent_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s AND type_id = %d", $parent_slug, $type_id ) ) ?: null;
			}

			$raw_slug = sanitize_title( $row['slug'] ?? '' );
			$slug     = $raw_slug ?: AH_Slug_Helper::generate( $name, $table, 'slug' );
			if ( $raw_slug ) {
				$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE slug = %s", $slug ) );
				if ( $exists ) $slug = AH_Slug_Helper::generate( $name, $table, 'slug' );
			}

			$wpdb->insert( $table, array(
				'type_id'     => $type_id,
				'name'        => $name,
				'slug'        => $slug,
				'parent_id'   => $parent_id,
				'description' => sanitize_textarea_field( $row['description'] ?? '' ),
				'sort_order'  => (int) ( $row['sort_order'] ?? 0 ),
				'status'      => self::status( $row['status'] ?? '', array( 'active', 'inactive' ) ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// Events / Hire Packages
	// -------------------------------------------------------------------------
	private static function import_events( array $rows ): array {
		global $wpdb;
		AH_DB_Installer::ensure_events_table();
		$table        = AH_DB_Helper::table( 'events' );
		$valid_colors = array( 'green', 'amber', 'teal', 'purple', 'coral', 'indigo' );
		$result       = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $rows as $i => $row ) {
			$line  = $i + 2;
			$title = sanitize_text_field( $row['title'] ?? '' );
			if ( ! $title ) {
				self::result_add( $result, false, "Row {$line}: 'title' is required." );
				continue;
			}

			// Parse pipe-separated items into JSON
			$raw_items = sanitize_textarea_field( $row['items'] ?? '' );
			$items_arr = array();
			if ( $raw_items ) {
				$items_arr = array_values( array_filter( array_map( 'trim', explode( '|', $raw_items ) ) ) );
			}

			$color = sanitize_key( $row['color'] ?? 'green' );
			if ( ! in_array( $color, $valid_colors, true ) ) {
				$color = 'green';
			}

			$wpdb->insert( $table, array(
				'icon'        => sanitize_text_field( $row['icon'] ?? '🎉' ) ?: '🎉',
				'title'       => $title,
				'description' => sanitize_textarea_field( $row['description'] ?? '' ),
				'items'       => wp_json_encode( $items_arr ),
				'color'       => $color,
				'is_featured' => (int) ( $row['is_featured'] ?? 0 ) ? 1 : 0,
				'sort_order'  => (int) ( $row['sort_order'] ?? 0 ),
				'status'      => self::status( $row['status'] ?? '', array( 'active', 'inactive' ) ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// News Bar
	// -------------------------------------------------------------------------
	private static function import_news_bar( array $rows ): array {
		global $wpdb;
		$table  = AH_DB_Helper::table( 'news_bar_items' );
		$result = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $rows as $i => $row ) {
			$line = $i + 2;
			$text = sanitize_text_field( $row['text'] ?? '' );

			if ( ! $text ) { self::result_add( $result, false, "Row {$line}: 'text' is required." ); continue; }

			$start = sanitize_text_field( $row['start_date'] ?? '' );
			$end   = sanitize_text_field( $row['end_date'] ?? '' );

			$wpdb->insert( $table, array(
				'text'       => $text,
				'content'    => wp_kses_post( $row['content'] ?? '' ),
				'link_url'   => esc_url_raw( $row['link_url'] ?? '' ),
				'start_date' => $start && strtotime( $start ) ? $start : null,
				'end_date'   => $end   && strtotime( $end )   ? $end   : null,
				'sort_order' => (int) ( $row['sort_order'] ?? 0 ),
				'status'     => self::status( $row['status'] ?? '', array( 'active', 'inactive' ) ),
			) );
			self::result_add( $result, ! $wpdb->last_error, $wpdb->last_error ? "Row {$line}: " . $wpdb->last_error : '' );
		}

		return $result;
	}
}
