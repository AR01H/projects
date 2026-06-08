<?php
/**
 * PT_Stories_API — WordPress REST API endpoints for stories.
 *
 * Base URL:  /wp-json/pt/v1/
 *
 * Public routes (no auth):
 *   GET  /stories              — list published stories (admin sees all)
 *   GET  /stories/{id}        — single story (admin sees unpublished too)
 *
 * Protected routes (manage_options):
 *   POST   /stories            — create a story
 *   PUT    /stories/{id}      — update a story
 *   DELETE /stories/{id}      — delete a story
 *
 * Response shape (single story):
 * {
 *   "id": "landmark-residences",
 *   "title": "...", "client": "...", "industry": "...",
 *   "tagline": "...", "summary": "...",
 *   "results": [{"label":"Units Sold","value":"120"}, ...],
 *   "image": "https://...",
 *   "featured": true, "published": true, "sort_order": 1,
 *   "created_at": "...", "updated_at": "..."
 * }
 */

defined( 'ABSPATH' ) || exit;

class PT_Stories_API {

	const NS = 'pt/v1';

	public static function init(): void {
		add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
	}

	/* ── Route registration ──────────────────────────────────────── */

	public static function register_routes(): void {
		/* Collection: /pt/v1/stories */
		register_rest_route( self::NS, '/stories', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ self::class, 'get_stories' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'published' => [
						'type'    => 'boolean',
						'default' => true,
					],
					'featured' => [
						'type' => 'boolean',
					],
				],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ self::class, 'create_story' ],
				'permission_callback' => [ self::class, 'admin_only' ],
				'args'                => self::story_schema_args(),
			],
		] );

		/* Single item: /pt/v1/stories/{id} */
		register_rest_route( self::NS, '/stories/(?P<id>[a-z0-9-]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ self::class, 'get_story' ],
				'permission_callback' => '__return_true',
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ self::class, 'update_story' ],
				'permission_callback' => [ self::class, 'admin_only' ],
				'args'                => self::story_schema_args( false ),
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ self::class, 'delete_story' ],
				'permission_callback' => [ self::class, 'admin_only' ],
			],
		] );

		/* Schema info endpoint */
		register_rest_route( self::NS, '/status', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ self::class, 'get_status' ],
			'permission_callback' => [ self::class, 'admin_only' ],
		] );
	}

	/* ── Permission ──────────────────────────────────────────────── */

	public static function admin_only(): bool {
		return current_user_can( 'manage_options' );
	}

	/* ── GET /stories ────────────────────────────────────────────── */

	public static function get_stories( WP_REST_Request $req ): WP_REST_Response {
		self::load_db();

		$admin_view     = current_user_can( 'manage_options' );
		$published_only = $admin_view ? (bool) $req->get_param( 'published' ) : true;

		$rows = PT_Stories_DB::all( $published_only );

		/* Optional filter: featured=true|false */
		if ( $req->has_param( 'featured' ) ) {
			$want_featured = (bool) $req->get_param( 'featured' );
			$rows = array_values( array_filter( $rows, static fn( $r ) => (bool) $r['featured'] === $want_featured ) );
		}

		return new WP_REST_Response( array_map( [ self::class, 'format' ], $rows ), 200 );
	}

	/* ── GET /stories/{id} ───────────────────────────────────────── */

	public static function get_story( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		self::load_db();

		$id  = sanitize_title( $req->get_param( 'id' ) );
		$row = PT_Stories_DB::find( $id );

		if ( ! $row ) {
			return new WP_Error( 'not_found', 'Story not found.', [ 'status' => 404 ] );
		}

		/* Non-admins cannot see unpublished stories */
		if ( ! $row['published'] && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'not_found', 'Story not found.', [ 'status' => 404 ] );
		}

		return new WP_REST_Response( self::format( $row ), 200 );
	}

	/* ── POST /stories ───────────────────────────────────────────── */

	public static function create_story( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		self::load_db();

		$data = self::extract_data( $req );

		if ( empty( $data['id'] ) ) {
			return new WP_Error( 'missing_id', 'id (slug) is required.', [ 'status' => 400 ] );
		}
		if ( PT_Stories_DB::find( $data['id'] ) ) {
			return new WP_Error( 'duplicate', 'A story with this id already exists. Use PUT to update.', [ 'status' => 409 ] );
		}

		$ok = PT_Stories_DB::save( $data );
		if ( ! $ok ) {
			return new WP_Error( 'db_error', 'Failed to save story.', [ 'status' => 500 ] );
		}

		return new WP_REST_Response( self::format( PT_Stories_DB::find( $data['id'] ) ), 201 );
	}

	/* ── PUT/PATCH /stories/{id} ─────────────────────────────────── */

	public static function update_story( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		self::load_db();

		$id = sanitize_title( $req->get_param( 'id' ) );
		if ( ! PT_Stories_DB::find( $id ) ) {
			return new WP_Error( 'not_found', 'Story not found.', [ 'status' => 404 ] );
		}

		$data       = self::extract_data( $req );
		$data['id'] = $id;

		$ok = PT_Stories_DB::save( $data );
		if ( ! $ok ) {
			return new WP_Error( 'db_error', 'Failed to update story.', [ 'status' => 500 ] );
		}

		return new WP_REST_Response( self::format( PT_Stories_DB::find( $id ) ), 200 );
	}

	/* ── DELETE /stories/{id} ────────────────────────────────────── */

	public static function delete_story( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		self::load_db();

		$id = sanitize_title( $req->get_param( 'id' ) );
		if ( ! PT_Stories_DB::find( $id ) ) {
			return new WP_Error( 'not_found', 'Story not found.', [ 'status' => 404 ] );
		}

		PT_Stories_DB::delete( $id );
		return new WP_REST_Response( [ 'deleted' => true, 'id' => $id ], 200 );
	}

	/* ── GET /status ─────────────────────────────────────────────── */

	public static function get_status( WP_REST_Request $req ): WP_REST_Response {
		self::load_db();
		require_once get_template_directory() . '/includes/admin/class-pt-ajax.php';

		return new WP_REST_Response( [
			'namespace' => self::NS,
			'routes'    => [ '/stories', '/stories/{id}', '/status' ],
			'schema'    => PT_Ajax::get_schema_state(),
			'counts'    => PT_Ajax::get_counts(),
		], 200 );
	}

	/* ── Helpers ─────────────────────────────────────────────────── */

	private static function load_db(): void {
		require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';
	}

	/**
	 * Map a raw DB row to the public API response shape.
	 */
	private static function format( array $row ): array {
		$results = [];
		for ( $i = 1; $i <= 3; $i++ ) {
			$label = trim( $row[ "result_{$i}_label" ] ?? '' );
			$value = trim( $row[ "result_{$i}_value" ] ?? '' );
			if ( $label || $value ) {
				$results[] = [ 'label' => $label, 'value' => $value ];
			}
		}

		return [
			'id'         => $row['id'],
			'title'      => $row['title'],
			'client'     => $row['client'],
			'industry'   => $row['industry'],
			'tagline'    => $row['tagline'],
			'summary'    => $row['summary'],
			'results'    => $results,
			'image'      => $row['image'],
			'featured'   => (bool) $row['featured'],
			'published'  => (bool) $row['published'],
			'sort_order' => (int)  $row['sort_order'],
			'url'        => home_url( '/stories/' . $row['id'] ),
			'created_at' => $row['created_at'],
			'updated_at' => $row['updated_at'],
		];
	}

	/**
	 * Pull story fields from a REST request.
	 */
	private static function extract_data( WP_REST_Request $req ): array {
		return [
			'id'             => sanitize_title( $req->get_param( 'id' )             ?? '' ),
			'title'          => sanitize_text_field( $req->get_param( 'title' )          ?? '' ),
			'client'         => sanitize_text_field( $req->get_param( 'client' )         ?? '' ),
			'industry'       => sanitize_text_field( $req->get_param( 'industry' )       ?? '' ),
			'tagline'        => sanitize_text_field( $req->get_param( 'tagline' )        ?? '' ),
			'summary'        => sanitize_textarea_field( $req->get_param( 'summary' )    ?? '' ),
			'result_1_label' => sanitize_text_field( $req->get_param( 'result_1_label' ) ?? '' ),
			'result_1_value' => sanitize_text_field( $req->get_param( 'result_1_value' ) ?? '' ),
			'result_2_label' => sanitize_text_field( $req->get_param( 'result_2_label' ) ?? '' ),
			'result_2_value' => sanitize_text_field( $req->get_param( 'result_2_value' ) ?? '' ),
			'result_3_label' => sanitize_text_field( $req->get_param( 'result_3_label' ) ?? '' ),
			'result_3_value' => sanitize_text_field( $req->get_param( 'result_3_value' ) ?? '' ),
			'image'          => esc_url_raw( $req->get_param( 'image' )      ?? '' ),
			'featured'       => (int) (bool) $req->get_param( 'featured' ),
			'published'      => (int) (bool) $req->get_param( 'published' ),
			'sort_order'     => (int) ( $req->get_param( 'sort_order' ) ?? 0 ),
		];
	}

	/**
	 * Argument schema for REST route registration.
	 * @param bool $id_required Whether the 'id' param is required (true for POST, false for PUT)
	 */
	private static function story_schema_args( bool $id_required = true ): array {
		return [
			'id'             => [ 'type' => 'string', 'required' => $id_required, 'sanitize_callback' => 'sanitize_title' ],
			'title'          => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'client'         => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'industry'       => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'tagline'        => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'summary'        => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ],
			'result_1_label' => [ 'type' => 'string' ],
			'result_1_value' => [ 'type' => 'string' ],
			'result_2_label' => [ 'type' => 'string' ],
			'result_2_value' => [ 'type' => 'string' ],
			'result_3_label' => [ 'type' => 'string' ],
			'result_3_value' => [ 'type' => 'string' ],
			'image'          => [ 'type' => 'string', 'format' => 'uri' ],
			'featured'       => [ 'type' => 'boolean' ],
			'published'      => [ 'type' => 'boolean' ],
			'sort_order'     => [ 'type' => 'integer', 'minimum' => 0 ],
		];
	}
}
