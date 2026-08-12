<?php

namespace Ah\Cms\Feature\Forms\Controller;

defined( 'ABSPATH' ) || exit;

class FormBuilderController {

	/**
	 * Structural field types. These carry no input and store no submission data -
	 * they are ordered markers that give the flat field list a shape:
	 *   step     - page break; everything after it belongs to the next step
	 *   fieldset - group start; following fields join it until the next
	 *              fieldset/step marker
	 */
	const STRUCTURE_TYPES = array( 'step', 'fieldset' );

	/** Expand/collapse behaviours a `fieldset` marker can be set to. */
	const GROUP_MODES = array( 'open', 'expanded', 'collapsed', 'accordion' );

	/**
	 * How a radio / checkbox field presents its options. Chosen per field in the
	 * builder's advanced panel, so the same field type can read as a plain list
	 * on one form and as selectable pills on another.
	 */
	const CHOICE_LAYOUTS = array( 'list', 'tiles', 'pills', 'cards', 'checks' );

	/** Field width => how many of the 12 grid columns the wrapper spans. */
	const WIDTHS = array(
		'full'       => 12,
		'two-thirds' => 8,
		'half'       => 6,
		'third'      => 4,
		'quarter'    => 3,
	);

	/** Comparison operators available to conditional logic. */
	const COND_OPS = array( 'is', 'not', 'any', 'empty', 'contains' );

	/** Uploads live under wp-content/uploads/<this>/<form id>/. */
	const UPLOAD_DIR = 'ah-forms';

	/** Hard ceiling, whatever a field is configured with. */
	const UPLOAD_MAX_MB = 20;

	/** Extensions that are never accepted, even if someone types them in. */
	const BLOCKED_EXTS = array(
		'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'phar', 'inc',
		'htaccess', 'htpasswd', 'htm', 'html', 'shtml', 'js', 'mjs', 'svg', 'xml',
		'exe', 'com', 'bat', 'cmd', 'sh', 'bash', 'cgi', 'pl', 'py', 'rb', 'jsp',
		'asp', 'aspx', 'jar', 'dll', 'so', 'msi', 'app',
	);

	/** Sensible default when a file field does not name its own list. */
	const DEFAULT_EXTS = array(
		'pdf', 'doc', 'docx', 'odt', 'rtf', 'txt', 'csv', 'xls', 'xlsx',
		'jpg', 'jpeg', 'png', 'gif', 'webp', 'heic',
	);

	public static function is_structural( string $type ): bool {
		return in_array( $type, self::STRUCTURE_TYPES, true );
	}

	/** Field types that accept a text prefix/suffix (they share one <input>). */
	public static function takes_affix( string $type ): bool {
		return in_array( $type, array( 'text', 'email', 'tel', 'url', 'number', 'date' ), true );
	}

	// ── Icons ────────────────────────────────────────────────────────────────

	public static function icons(): array {
		static $cache = null;
		if ( null === $cache ) {
			$cache = self::load_data( 'form-icons.json' );
		}
		return $cache;
	}

	/**
	 * Read one of the bundled data files.
	 *
	 * The icon set and dial-code list are large reference data, so they live in
	 * data/*.json rather than inline arrays. Cached per request by the callers.
	 */
	private static function load_data( string $file ): array {
		$path = dirname( __FILE__, 5 ) . '/data/' . $file;
		$real = realpath( $path );
		if ( ! $real || ! is_file( $real ) ) {
			return array();
		}
		$json = json_decode( (string) file_get_contents( $real ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return is_array( $json ) ? $json : array();
	}

	/** Render an icon: a built-in key becomes SVG, anything else prints as text. */
	/**
	 * Is this icon value an image to render, rather than an icon name or emoji?
	 *
	 * Both halves must hold: it has to LOOK like a URL or site-relative path (so
	 * a stray word containing a dot stays a literal) and end in an image
	 * extension (so an arbitrary URL cannot be pulled into an <img> src).
	 *
	 * Shared by the renderer and the settings sanitiser - the sanitiser caps
	 * plain icon literals at a few characters, and without the same test here it
	 * would cut a pasted image URL down to "/wp-".
	 */
	public static function is_image_icon( string $icon ): bool {
		$icon = trim( $icon );
		return ( '' !== $icon )
			&& 1 === preg_match( '~^(?:https?://|//|/)~', $icon )
			&& 1 === preg_match( '~\.(?:png|jpe?g|gif|webp|svg|avif)(?:\?[^\s]*)?$~i', $icon );
	}

	public static function icon_svg( string $icon, string $class = 'ahf-ico' ): string {
		$icon = trim( $icon );
		if ( '' === $icon ) {
			return '';
		}
		$set = self::icons();
		if ( isset( $set[ $icon ] ) ) {
			return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
				. $set[ $icon ] . '</svg>';
		}
		/*
		 * An image URL renders as an <img>. Every icon in the form - field
		 * labels, tiles, pills, cards, step badges - comes through this one
		 * function, so pasting a media-library URL where an icon name goes works
		 * everywhere without a second syntax to remember. Decorative, hence
		 * alt="".
		 */
		if ( self::is_image_icon( $icon ) ) {
			return '<img class="' . esc_attr( $class ) . ' ' . esc_attr( $class ) . '-img" src="' . esc_url( $icon )
				. '" alt="" loading="lazy" decoding="async">';
		}

		// Emoji or short literal - cap the length so a pasted essay can't wreck layout.
		return '<span class="' . esc_attr( $class ) . ' ' . esc_attr( $class ) . '-txt" aria-hidden="true">'
			. esc_html( function_exists( 'mb_substr' ) ? mb_substr( $icon, 0, 4 ) : substr( $icon, 0, 4 ) ) . '</span>';
	}

	// ── Country dial codes (tel fields with the country selector enabled) ────

	public static function dial_codes(): array {
		static $cache = null;
		if ( null === $cache ) {
			$cache = self::load_data( 'dial-codes.json' );
		}
		return $cache;
	}

	/** ISO code -> regional-indicator flag emoji (falls back to the letters on Windows). */
	public static function flag_emoji( string $iso ): string {
		$iso = strtoupper( $iso );
		if ( 2 !== strlen( $iso ) ) {
			return '';
		}
		$out = '';
		for ( $i = 0; $i < 2; $i++ ) {
			$out .= mb_chr( 0x1F1E6 + ( ord( $iso[ $i ] ) - 65 ), 'UTF-8' );
		}
		return $out;
	}

	// ── Form CRUD ────────────────────────────────────────────────────────────

	public static function get_all(): array {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}ah_forms` ORDER BY id ASC" ) ?: array();
	}

	public static function get( int $id ): ?object {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_forms';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$t}` WHERE id = %d", $id ) ) ?: null;
	}

	public static function upsert( int $id, array $data ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_forms';
		if ( $id > 0 ) {
			$wpdb->update( $t, $data, array( 'id' => $id ) );
			return $id;
		}
		$wpdb->insert( $t, $data );
		return (int) $wpdb->insert_id;
	}

	public static function delete_form( int $id ): void {
		global $wpdb;
		foreach ( self::get_submissions_filtered( $id, '', 100000, 0 ) as $__s ) {
			self::delete_submission_files( (int) $__s['id'] );
		}
		$wpdb->delete( $wpdb->prefix . 'ah_form_submissions', array( 'form_id' => $id ), array( '%d' ) );
		$wpdb->delete( $wpdb->prefix . 'ah_form_fields',      array( 'form_id' => $id ), array( '%d' ) );
		$wpdb->delete( $wpdb->prefix . 'ah_forms',            array( 'id'      => $id ), array( '%d' ) );
	}

	// ── Fields CRUD ──────────────────────────────────────────────────────────

	public static function get_fields( int $form_id ): array {
		global $wpdb;
		$t    = $wpdb->prefix . 'ah_form_fields';
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$t}` WHERE form_id = %d ORDER BY sort_order ASC, id ASC", $form_id )
		) ?: array();
		foreach ( $rows as $row ) {
			$row->options  = ( $row->options ) ? json_decode( $row->options, true ) : array();
			// `settings` arrives late via migration, so an install that hits the
			// frontend before an admin load has run may not have the column yet.
			$raw            = isset( $row->settings ) ? $row->settings : '';
			$row->settings  = $raw ? ( json_decode( $raw, true ) ?: array() ) : array();
		}
		return $rows;
	}

	public static function save_fields( int $form_id, array $fields ): void {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_fields';
		$wpdb->delete( $t, array( 'form_id' => $form_id ), array( '%d' ) );
		foreach ( $fields as $i => $f ) {
			$type       = self::allowed_type( $f['field_type'] ?? 'text' );
			$structural = self::is_structural( $type );
			$label      = sanitize_text_field( $f['label'] ?? '' );
			// A step / group may legitimately be untitled; an input field may not.
			if ( ! $label && ! $structural ) continue;
			$opts = ( in_array( $type, array( 'select', 'radio', 'checkbox' ), true ) && ! empty( $f['options'] ) ) ? array_values( array_filter( array_map( 'sanitize_text_field', (array) $f['options'] ) ) ) : array();
			$sett = self::sanitize_settings( $type, (array) ( $f['settings'] ?? array() ) );
			// Structural rows get a synthetic key so an untitled marker still has one
			// and a marker titled like a real field can never collide with its data key.
			$key = $structural ? '_' . $type . '_' . ( $i + 1 ) : self::to_key( $label );
			$wpdb->insert( $t, array(
				'form_id'     => $form_id,
				'label'       => $label,
				'field_key'   => $key,
				'field_type'  => $type,
				'placeholder' => $structural ? '' : sanitize_text_field( $f['placeholder'] ?? '' ),
				'options'     => ! empty( $opts ) ? wp_json_encode( $opts ) : null,
				// wp_kses_post keeps links/bold/lists in help text but strips scripts.
				'description' => wp_kses_post( $f['description'] ?? '' ),
				'settings'    => ! empty( $sett ) ? wp_json_encode( $sett ) : null,
				'is_required' => ( 'hidden' === $type || $structural ) ? 0 : ( empty( $f['is_required'] ) ? 0 : 1 ),
				'sort_order'  => $i,
			) );
		}
	}

	/**
	 * Whitelist the per-field settings bag.
	 *   class - extra CSS classes for the field wrapper (any type)
	 *   mode  - expand/collapse behaviour (fieldset only)
	 */
	public static function sanitize_settings( string $type, array $raw ): array {
		$out = array();

		$class = isset( $raw['class'] ) ? trim( (string) $raw['class'] ) : '';
		if ( '' !== $class ) {
			// Space-separated class tokens; sanitize_html_class() drops anything
			// that could break out of the attribute.
			$tokens = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', $class ) ) );
			if ( $tokens ) {
				$out['class'] = implode( ' ', array_unique( $tokens ) );
			}
		}

		// Width applies to input fields and to groups - two half-width groups sit
		// side by side in the step's 12-column grid. A step is a page of its own,
		// so it always spans the full row.
		if ( 'step' !== $type ) {
			$width = isset( $raw['width'] ) ? sanitize_key( (string) $raw['width'] ) : 'full';
			if ( isset( self::WIDTHS[ $width ] ) && 'full' !== $width ) {
				$out['width'] = $width;
			}
		}

		// Icon: a built-in key, or any short literal (emoji) the builder typed.
		// Allowed on every type - steps and groups show a badge, fields show it
		// beside their label, markup shows it beside the text.
		$icon = isset( $raw['icon'] ) ? trim( (string) $raw['icon'] ) : '';
		if ( '' !== $icon ) {
			if ( isset( self::icons()[ $icon ] ) ) {
				$out['icon'] = $icon;
			} elseif ( self::is_image_icon( $icon ) ) {
				$out['icon'] = esc_url_raw( $icon );   // an image URL is kept whole
			} else {
				$out['icon'] = function_exists( 'mb_substr' ) ? mb_substr( $icon, 0, 4 ) : substr( $icon, 0, 4 );
			}
		}

		// Pre-filled value. Free text for input fields; for a choice field it is
		// the option value, or a comma-separated list for a multi-select.
		if ( ! self::is_structural( $type ) && 'markup' !== $type ) {
			$default = isset( $raw['default'] ) ? sanitize_text_field( (string) $raw['default'] ) : '';
			$default = function_exists( 'mb_substr' ) ? mb_substr( $default, 0, 300 ) : substr( $default, 0, 300 );
			if ( '' !== $default ) {
				$out['default'] = $default;
			}
		}

		// Per-step wording for the button that moves to the next step.
		if ( 'step' === $type ) {
			$next_label = isset( $raw['next_label'] ) ? sanitize_text_field( (string) $raw['next_label'] ) : '';
			$next_label = function_exists( 'mb_substr' ) ? mb_substr( $next_label, 0, 60 ) : substr( $next_label, 0, 60 );
			if ( '' !== $next_label ) {
				$out['next_label'] = $next_label;
			}
		}

		if ( 'fieldset' === $type ) {
			$mode        = isset( $raw['mode'] ) ? sanitize_key( (string) $raw['mode'] ) : 'open';
			$out['mode'] = in_array( $mode, self::GROUP_MODES, true ) ? $mode : 'open';
		}

		// Presentation for choice fields: list (default) / tiles / pills / cards.
		if ( in_array( $type, array( 'radio', 'checkbox' ), true ) ) {
			$layout = isset( $raw['layout'] ) ? sanitize_key( (string) $raw['layout'] ) : 'list';
			if ( in_array( $layout, self::CHOICE_LAYOUTS, true ) ) {
				$out['layout'] = $layout;
			}
		}

		// Prefix / suffix shown inside the input frame.
		if ( self::takes_affix( $type ) ) {
			foreach ( array( 'prefix', 'suffix' ) as $affix ) {
				$v = isset( $raw[ $affix ] ) ? sanitize_text_field( (string) $raw[ $affix ] ) : '';
				$v = function_exists( 'mb_substr' ) ? mb_substr( $v, 0, 8 ) : substr( $v, 0, 8 );
				if ( '' !== $v ) {
					$out[ $affix ] = $v;
				}
			}
		}

		// File upload limits.
		if ( 'file' === $type ) {
			$out['max_size'] = self::field_max_mb( array( 'max_size' => $raw['max_size'] ?? 5 ) );
			$exts            = self::parse_exts( isset( $raw['accept'] ) ? (string) $raw['accept'] : '' );
			if ( $exts ) {
				$out['accept'] = implode( ',', $exts );
			}
		}

		// Country dial-code selector, tel only.
		if ( 'tel' === $type && ! empty( $raw['intl'] ) ) {
			$out['intl'] = 1;
			$cc          = isset( $raw['intl_cc'] ) ? sanitize_text_field( (string) $raw['intl_cc'] ) : '+44';
			$out['intl_cc'] = in_array( $cc, array_values( self::dial_codes() ), true ) ? $cc : '+44';
		}

		// Conditional logic - show this field/group only when another field matches.
		if ( ! empty( $raw['cond'] ) && is_array( $raw['cond'] ) ) {
			$c_field = isset( $raw['cond']['field'] ) ? sanitize_text_field( (string) $raw['cond']['field'] ) : '';
			$c_op    = isset( $raw['cond']['op'] ) ? sanitize_key( (string) $raw['cond']['op'] ) : 'is';
			$c_value = isset( $raw['cond']['value'] ) ? sanitize_text_field( (string) $raw['cond']['value'] ) : '';
			if ( '' !== $c_field ) {
				$out['cond'] = array(
					'field' => $c_field,
					'op'    => in_array( $c_op, self::COND_OPS, true ) ? $c_op : 'is',
					'value' => $c_value,
				);
			}
		}

		return $out;
	}

	/** Grid class for a field wrapper, '' when it spans the full row. */
	public static function width_class( $settings ): string {
		if ( ! is_array( $settings ) || empty( $settings['width'] ) ) {
			return '';
		}
		$w = (string) $settings['width'];
		return isset( self::WIDTHS[ $w ] ) ? 'ah-col-' . self::WIDTHS[ $w ] : '';
	}

	// ── Conditional logic ────────────────────────────────────────────────────

	/**
	 * Map every data field key to the conditions that must hold for it to apply -
	 * its own, plus the one on the group that encloses it.
	 *
	 * @return array<string,array<int,array>>
	 */
	public static function resolve_conditions( array $fields ): array {
		$map        = array();
		$group_cond = null;

		foreach ( $fields as $f ) {
			if ( 'step' === $f->field_type ) {
				$group_cond = null;
				continue;
			}
			if ( 'fieldset' === $f->field_type ) {
				$group_cond = ! empty( $f->settings['cond'] ) ? $f->settings['cond'] : null;
				continue;
			}
			$list = array();
			if ( $group_cond ) {
				$list[] = $group_cond;
			}
			if ( ! empty( $f->settings['cond'] ) ) {
				$list[] = $f->settings['cond'];
			}
			if ( $list ) {
				$map[ $f->field_key ] = $list;
			}
		}
		return $map;
	}

	/**
	 * Does a single condition hold against the submitted values?
	 *
	 * Unknown target fields deliberately return true (fail open) - a typo in a
	 * condition should never make a field impossible to fill in.
	 */
	public static function condition_met( array $cond, array $data ): bool {
		$key = isset( $cond['field'] ) ? (string) $cond['field'] : '';
		if ( '' === $key || ! array_key_exists( $key, $data ) ) {
			return true;
		}
		$op   = isset( $cond['op'] ) ? (string) $cond['op'] : 'is';
		$want = isset( $cond['value'] ) ? (string) $cond['value'] : '';
		$have = (string) $data[ $key ];
		// Checkbox groups are stored as "a, b, c".
		$parts = array_map( 'trim', explode( ',', $have ) );

		switch ( $op ) {
			case 'is':
				return in_array( $want, $parts, true );
			case 'not':
				return ! in_array( $want, $parts, true );
			case 'any':
				return '' !== $have;
			case 'empty':
				return '' === $have;
			case 'contains':
				return '' !== $want && false !== stripos( $have, $want );
		}
		return true;
	}

	/** Condition attributes for a wrapper, only when the target field really exists. */
	public static function cond_attrs( $settings, array $valid_keys ): string {
		if ( ! is_array( $settings ) || empty( $settings['cond']['field'] ) ) {
			return '';
		}
		$c = $settings['cond'];
		if ( ! in_array( $c['field'], $valid_keys, true ) ) {
			return ''; // stale reference - show the field rather than hide it forever
		}
		return ' data-cond-field="' . esc_attr( $c['field'] ) . '"'
			. ' data-cond-op="' . esc_attr( $c['op'] ?? 'is' ) . '"'
			. ' data-cond-value="' . esc_attr( $c['value'] ?? '' ) . '"';
	}

	/** The sanitized custom class for a field/step/group row, '' when unset. */
	public static function css_class( $settings ): string {
		return ( is_array( $settings ) && ! empty( $settings['class'] ) ) ? (string) $settings['class'] : '';
	}

	/**
	 * Turn the flat, ordered field list into steps of blocks.
	 *
	 * A `step` marker opens a new step; a `fieldset` marker opens a group that
	 * swallows every following field until the next fieldset/step marker. Fields
	 * before any marker land in an implicit first step, so single-page forms with
	 * no markers come back as exactly one step and render as they always have.
	 *
	 * @return array<int,array{title:string,desc:string,blocks:array}>
	 */
	public static function build_structure( array $fields ): array {
		$steps = array();
		$step  = array( 'title' => '', 'desc' => '', 'class' => '', 'icon' => '', 'next' => '', 'blocks' => array() );
		$group = null; // Index into $step['blocks'] of the open group, if any.

		foreach ( $fields as $f ) {
			if ( 'step' === $f->field_type ) {
				$steps[] = $step;
				$step    = array(
					'title'  => (string) $f->label,
					'desc'   => (string) ( $f->description ?? '' ),
					'class'  => self::css_class( $f->settings ?? array() ),
					'icon'   => isset( $f->settings['icon'] ) ? (string) $f->settings['icon'] : '',
					// Wording for the button that leaves THIS step ("Continue to
					// Your Home Search"). Blank keeps the generic "Next".
					'next'   => isset( $f->settings['next_label'] ) ? (string) $f->settings['next_label'] : '',
					'blocks' => array(),
				);
				$group   = null;
				continue;
			}
			if ( 'fieldset' === $f->field_type ) {
				$step['blocks'][] = array(
					'type'     => 'group',
					'key'      => (string) $f->field_key,
					'title'    => (string) $f->label,
					'desc'     => (string) ( $f->description ?? '' ),
					'class'    => self::css_class( $f->settings ?? array() ),
					'icon'     => isset( $f->settings['icon'] ) ? (string) $f->settings['icon'] : '',
					'settings' => is_array( $f->settings ?? null ) ? $f->settings : array(),
					'mode'     => isset( $f->settings['mode'] ) ? (string) $f->settings['mode'] : 'open',
					'fields'   => array(),
				);
				$group = count( $step['blocks'] ) - 1;
				continue;
			}
			if ( null !== $group ) {
				$step['blocks'][ $group ]['fields'][] = $f;
			} else {
				$step['blocks'][] = array( 'type' => 'field', 'field' => $f );
			}
		}
		$steps[] = $step;

		// A form that opens with a step marker leaves an empty implicit step in front.
		if ( count( $steps ) > 1 && '' === $steps[0]['title'] && empty( $steps[0]['blocks'] ) ) {
			array_shift( $steps );
		}
		return $steps;
	}

	// ── Submissions ──────────────────────────────────────────────────────────

	public static function submit( int $form_id, array $data ): int {
		global $wpdb;
		$result = $wpdb->insert( $wpdb->prefix . 'ah_form_submissions', array(
			'form_id'    => $form_id,
			'data'       => wp_json_encode( $data ),
			'ip_address' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
		) );
		return $result ? (int) $wpdb->insert_id : false;
	}

	public static function get_submissions( int $form_id, int $limit = 50, int $offset = 0 ): array {
		global $wpdb;
		$t    = $wpdb->prefix . 'ah_form_submissions';
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$t}` WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", $form_id, $limit, $offset )
		) ?: array();
		foreach ( $rows as $row ) {
			$row->data = $row->data ? json_decode( $row->data, true ) : array();
		}
		return $rows;
	}

	public static function count_submissions( int $form_id ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_submissions';
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$t}` WHERE form_id = %d", $form_id ) );
	}

	public static function delete_submission( int $id ): void {
		global $wpdb;
		self::delete_submission_files( $id );
		$wpdb->delete( $wpdb->prefix . 'ah_form_submissions', array( 'id' => $id ), array( '%d' ) );
	}

	/** Save admin status and notes for a single submission. */
	public static function update_submission_meta( int $id, string $status, string $notes ): bool {
		global $wpdb;
		$allowed = array( 'new', 'read', 'replied', 'closed' );
		$status  = in_array( $status, $allowed, true ) ? $status : 'new';
		return (bool) $wpdb->update(
			$wpdb->prefix . 'ah_form_submissions',
			array(
				'sub_status'  => $status,
				'admin_notes' => sanitize_textarea_field( $notes ),
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Build the shared WHERE fragment for the submissions list.
	 *
	 * @return array{0:string,1:array} SQL with placeholders, and its arguments.
	 */
	private static function submissions_where( int $form_id, string $status, string $search ): array {
		global $wpdb;
		$sql  = 'WHERE form_id = %d';
		$args = array( $form_id );

		if ( '' !== $status ) {
			$sql   .= ' AND sub_status = %s';
			$args[] = $status;
		}
		if ( '' !== $search ) {
			// The answers live in one JSON blob, so a LIKE across it is the only
			// portable way to search values without a column per field.
			$like   = '%' . $wpdb->esc_like( $search ) . '%';
			$sql   .= ' AND (data LIKE %s OR admin_notes LIKE %s)';
			$args[] = $like;
			$args[] = $like;
		}
		return array( $sql, $args );
	}

	/** Get submissions filtered by status and/or a free-text search. */
	public static function get_submissions_filtered( int $form_id, string $status = '', int $limit = 100, int $offset = 0, string $search = '' ): array {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_submissions';
		list( $where, $args ) = self::submissions_where( $form_id, $status, $search );
		$args[] = $limit;
		$args[] = $offset;

		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM `{$t}` {$where} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", $args ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $where is built from literals above.
			ARRAY_A
		) ?: array();

		foreach ( $rows as &$row ) {
			$row['data'] = $row['data'] ? json_decode( $row['data'], true ) : array();
		}
		return $rows;
	}

	/** How many submissions match the current filter (for pagination). */
	public static function count_filtered( int $form_id, string $status = '', string $search = '' ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_submissions';
		list( $where, $args ) = self::submissions_where( $form_id, $status, $search );
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM `{$t}` {$where}", $args ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $where is built from literals above.
		);
	}

	/** One submission with its data decoded, or null. */
	public static function get_submission( int $id ): ?array {
		global $wpdb;
		$t   = $wpdb->prefix . 'ah_form_submissions';
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$t}` WHERE id = %d", $id ), ARRAY_A );
		if ( ! $row ) {
			return null;
		}
		$row['data'] = $row['data'] ? json_decode( $row['data'], true ) : array();
		return $row;
	}

	/** The previous/next submission id for this form, for record-to-record paging. */
	public static function neighbour_submission( int $form_id, int $id, string $dir = 'next' ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'ah_form_submissions';
		// "next" walks towards older entries, matching the newest-first listing.
		$cmp   = ( 'next' === $dir ) ? '<' : '>';
		$order = ( 'next' === $dir ) ? 'DESC' : 'ASC';
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM `{$t}` WHERE form_id = %d AND id {$cmp} %d ORDER BY id {$order} LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- both are literals chosen above.
			$form_id,
			$id
		) );
	}

	/**
	 * Pick the handful of fields worth showing as list columns.
	 *
	 * A long form can have dozens of fields; one column each makes the table
	 * unreadable. Prefer the fields that identify a person, then fall back to
	 * whatever comes first.
	 */
	public static function summary_fields( array $fields, int $max = 4 ): array {
		$data = array_values( array_filter( $fields, static function ( $f ) {
			return ! self::is_structural( $f->field_type ) && 'markup' !== $f->field_type && 'hidden' !== $f->field_type;
		} ) );

		$picked = array();
		$taken  = array();
		foreach ( array( 'email', 'tel' ) as $want ) {
			foreach ( $data as $i => $f ) {
				if ( $f->field_type === $want && ! isset( $taken[ $i ] ) ) {
					$picked[]      = $f;
					$taken[ $i ]   = true;
					break;
				}
			}
		}
		foreach ( $data as $i => $f ) {
			if ( count( $picked ) >= $max ) {
				break;
			}
			if ( ! isset( $taken[ $i ] ) ) {
				$picked[]    = $f;
				$taken[ $i ] = true;
			}
		}
		// Keep them in form order so the columns read naturally.
		usort( $picked, static function ( $a, $b ) {
			return (int) $a->sort_order <=> (int) $b->sort_order;
		} );
		return array_slice( $picked, 0, $max );
	}

	// ── Spam challenge (reCAPTCHA / Turnstile) ───────────────────────────────

	/** Keys are site-wide; each form only decides whether to use them. */
	public static function captcha_settings(): array {
		$saved = get_option( 'ah_form_captcha', array() );
		return array_merge(
			array(
				'provider'   => 'none',
				'site_key'   => '',
				'secret_key' => '',
				'threshold'  => '0.5',
			),
			is_array( $saved ) ? $saved : array()
		);
	}

	public static function save_captcha_settings( array $in ): void {
		$provider = isset( $in['provider'] ) ? sanitize_key( (string) $in['provider'] ) : 'none';
		$valid    = array( 'none', 'recaptcha_v2', 'recaptcha_v3', 'turnstile' );
		$score    = isset( $in['threshold'] ) ? (float) $in['threshold'] : 0.5;
		update_option( 'ah_form_captcha', array(
			'provider'   => in_array( $provider, $valid, true ) ? $provider : 'none',
			'site_key'   => sanitize_text_field( (string) ( $in['site_key'] ?? '' ) ),
			'secret_key' => sanitize_text_field( (string) ( $in['secret_key'] ?? '' ) ),
			'threshold'  => (string) max( 0, min( 1, $score ) ),
		) );
	}

	/** True when this form should challenge, and the keys are actually present. */
	public static function captcha_active( $form ): bool {
		if ( empty( $form->use_captcha ) ) {
			return false;
		}
		$c = self::captcha_settings();
		return 'none' !== $c['provider'] && '' !== $c['site_key'] && '' !== $c['secret_key'];
	}

	/** The POST field the chosen provider puts its token in. */
	public static function captcha_token_field( string $provider ): string {
		return ( 'turnstile' === $provider ) ? 'cf-turnstile-response' : 'g-recaptcha-response';
	}

	/**
	 * Check the token with the provider.
	 *
	 * @return true|string True on success, otherwise a message for the visitor.
	 */
	public static function verify_captcha( $form ) {
		if ( ! self::captcha_active( $form ) ) {
			return true;
		}
		$c     = self::captcha_settings();
		$field = self::captcha_token_field( $c['provider'] );
		$token = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === $token ) {
			return 'Please complete the spam check and try again.';
		}

		$url = ( 'turnstile' === $c['provider'] )
			? 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
			: 'https://www.google.com/recaptcha/api/siteverify';

		$res = wp_remote_post( $url, array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => $c['secret_key'],
				'response' => $token,
				'remoteip' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
			),
		) );
		if ( is_wp_error( $res ) ) {
			// Never lock people out of the form because the provider is unreachable.
			return true;
		}
		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $body ) || empty( $body['success'] ) ) {
			return 'Spam check failed. Please refresh the page and try again.';
		}
		if ( 'recaptcha_v3' === $c['provider'] && isset( $body['score'] ) ) {
			if ( (float) $body['score'] < (float) $c['threshold'] ) {
				return 'Your submission looked automated. Please try again or contact us directly.';
			}
		}
		return true;
	}

	// ── File uploads ─────────────────────────────────────────────────────────

	/** Normalise a user-typed extension list, dropping anything unsafe. */
	public static function parse_exts( string $raw ): array {
		$out = array();
		foreach ( preg_split( '/[\s,;]+/', strtolower( $raw ) ) as $bit ) {
			$bit = ltrim( trim( $bit ), '.' );
			if ( '' === $bit || ! preg_match( '/^[a-z0-9]{1,8}$/', $bit ) ) {
				continue;
			}
			if ( in_array( $bit, self::BLOCKED_EXTS, true ) ) {
				continue;
			}
			$out[ $bit ] = true;
		}
		return array_keys( $out );
	}

	/** The extensions a given file field accepts. */
	public static function field_exts( $settings ): array {
		$acc = ( is_array( $settings ) && ! empty( $settings['accept'] ) ) ? (string) $settings['accept'] : '';
		$list = self::parse_exts( $acc );
		return $list ?: self::DEFAULT_EXTS;
	}

	/** The size cap for a given file field, in MB. */
	public static function field_max_mb( $settings ): int {
		$mb = ( is_array( $settings ) && ! empty( $settings['max_size'] ) ) ? (int) $settings['max_size'] : 5;
		return max( 1, min( self::UPLOAD_MAX_MB, $mb ) );
	}

	/** Base upload directory, created and shielded on first use. */
	public static function upload_base(): array {
		$up   = wp_upload_dir();
		$base = trailingslashit( $up['basedir'] ) . self::UPLOAD_DIR;
		if ( ! is_dir( $base ) ) {
			wp_mkdir_p( $base );
		}
		// Belt: block direct web access where .htaccess applies. Braces: stored
		// names are random, so a URL cannot be guessed on servers that ignore it.
		$ht = $base . '/.htaccess';
		if ( ! file_exists( $ht ) ) {
			file_put_contents( $ht, "Require all denied\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n" );
		}
		$idx = $base . '/index.php';
		if ( ! file_exists( $idx ) ) {
			file_put_contents( $idx, "<?php // Silence is golden.\n" );
		}
		return array( 'dir' => $base, 'basedir' => trailingslashit( $up['basedir'] ) );
	}

	/**
	 * Validate and store one uploaded file.
	 *
	 * @return array{name:string,rel:string,size:int}|\WP_Error|null Null when nothing was sent.
	 */
	public static function handle_upload( string $key, $settings, int $form_id, string $label ) {
		if ( empty( $_FILES[ $key ] ) || ! isset( $_FILES[ $key ]['error'] ) ) {
			return null;
		}
		$file = $_FILES[ $key ];
		if ( UPLOAD_ERR_NO_FILE === $file['error'] ) {
			return null;
		}
		if ( UPLOAD_ERR_INI_SIZE === $file['error'] || UPLOAD_ERR_FORM_SIZE === $file['error'] ) {
			return new \WP_Error( 'too_big', $label . ' is larger than the server allows.' );
		}
		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			return new \WP_Error( 'upload', 'Could not upload ' . $label . '. Please try again.' );
		}
		// Cheap check first - no need to touch the file to know it is too big.
		$max_mb = self::field_max_mb( $settings );
		if ( (int) $file['size'] > $max_mb * 1024 * 1024 ) {
			return new \WP_Error( 'too_big', $label . ' must be ' . $max_mb . ' MB or smaller.' );
		}

		// Must come before anything reads tmp_name.
		if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new \WP_Error( 'upload', 'Invalid upload for ' . $label . '.' );
		}

		// Trust WordPress's sniffing over the posted filename.
		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		$ext   = strtolower( (string) ( $check['ext'] ?: pathinfo( $file['name'], PATHINFO_EXTENSION ) ) );
		$allow = self::field_exts( $settings );
		if ( '' === $ext || in_array( $ext, self::BLOCKED_EXTS, true ) || ! in_array( $ext, $allow, true ) ) {
			return new \WP_Error( 'ext', $label . ' must be one of: ' . strtoupper( implode( ', ', $allow ) ) . '.' );
		}

		$base = self::upload_base();
		$dir  = $base['dir'] . '/' . $form_id;
		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return new \WP_Error( 'dir', 'Could not store ' . $label . '.' );
		}
		// The stored name is entirely generated - no part of the visitor's filename
		// reaches the filesystem, so "photo.php.jpg" tricks cannot apply.
		$stored = wp_generate_password( 24, false, false ) . '.' . $ext;
		$dest   = $dir . '/' . $stored;
		if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
			return new \WP_Error( 'move', 'Could not store ' . $label . '.' );
		}
		@chmod( $dest, 0644 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		return array(
			'name' => sanitize_file_name( (string) $file['name'] ),
			'rel'  => self::UPLOAD_DIR . '/' . $form_id . '/' . $stored,
			'size' => (int) $file['size'],
		);
	}

	/** Resolve a stored relative path to a real file inside the upload dir, or ''. */
	public static function resolve_upload( string $rel ): string {
		if ( '' === $rel || false !== strpos( $rel, "\0" ) ) {
			return '';
		}
		$base = self::upload_base();
		$root = realpath( $base['dir'] );
		$path = realpath( $base['basedir'] . ltrim( $rel, '/\\' ) );
		if ( ! $root || ! $path || ! is_file( $path ) ) {
			return '';
		}
		// Containment check - never serve anything outside the upload directory.
		$root = rtrim( str_replace( '\\', '/', $root ), '/' ) . '/';
		$norm = str_replace( '\\', '/', $path );
		return ( 0 === strpos( $norm, $root ) ) ? $path : '';
	}

	/** Admin-only download for a submitted file. Hooked to admin_post. */
	public static function download_file(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.' );
		}
		check_admin_referer( 'ah_form_file' );

		$sub_id = (int) ( $_GET['sub'] ?? 0 );
		$key    = sanitize_key( wp_unslash( $_GET['key'] ?? '' ) );
		$row    = $sub_id ? self::get_submission( $sub_id ) : null;
		if ( ! $row || '' === $key ) {
			wp_die( 'File not found.' );
		}

		$rel  = (string) ( $row['data'][ '_file_' . $key ] ?? '' );
		$path = self::resolve_upload( $rel );
		if ( '' === $path ) {
			wp_die( 'File not found.' );
		}
		$name = (string) ( $row['data'][ $key ] ?? basename( $path ) );

		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $name ) . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}

	/** Remove every file attached to a submission. */
	public static function delete_submission_files( int $id ): void {
		$row = self::get_submission( $id );
		if ( ! $row || ! is_array( $row['data'] ) ) {
			return;
		}
		foreach ( $row['data'] as $k => $v ) {
			if ( 0 !== strpos( (string) $k, '_file_' ) || ! is_string( $v ) ) {
				continue;
			}
			$path = self::resolve_upload( $v );
			if ( '' !== $path ) {
				wp_delete_file( $path );
			}
		}
	}

	// ── CSV export ───────────────────────────────────────────────────────────

	/**
	 * Stream submissions as CSV. Hooked to admin_post so nothing has been sent
	 * to the browser yet and we can own the response headers.
	 */
	public static function export_csv(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.' );
		}
		check_admin_referer( 'ah_export_subs' );

		$form_id = (int) ( $_GET['form_id'] ?? 0 );
		$form    = $form_id ? self::get( $form_id ) : null;
		if ( ! $form ) {
			wp_die( 'Unknown form.' );
		}

		$status = sanitize_key( wp_unslash( $_GET['sub_status'] ?? '' ) );
		$search = sanitize_text_field( wp_unslash( $_GET['sub_s'] ?? '' ) );
		$only   = array_filter( array_map( 'intval', explode( ',', (string) ( $_GET['ids'] ?? '' ) ) ) );

		$fields = self::get_fields( $form_id );
		$cols   = array_values( array_filter( $fields, static function ( $f ) {
			return ! self::is_structural( $f->field_type ) && 'markup' !== $f->field_type;
		} ) );

		$rows = self::get_submissions_filtered( $form_id, $status, 10000, 0, $search );
		if ( $only ) {
			$rows = array_values( array_filter( $rows, static function ( $r ) use ( $only ) {
				return in_array( (int) $r['id'], $only, true );
			} ) );
		}

		$slug = sanitize_title( $form->name ) ?: 'form-' . $form_id;
		$file = $slug . '-submissions-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $file . '"' );

		$out = fopen( 'php://output', 'w' );
		// BOM so Excel reads UTF-8 (£, accents) instead of mangling it.
		fwrite( $out, "\xEF\xBB\xBF" );

		$head = array( 'ID', 'Submitted', 'Status', 'IP address' );
		foreach ( $cols as $c ) {
			$head[] = $c->label;
		}
		$head[] = 'Agreement';
		$head[] = 'Admin notes';
		fputcsv( $out, $head );

		foreach ( $rows as $r ) {
			$line = array(
				$r['id'],
				$r['created_at'],
				$r['sub_status'] ?? 'new',
				$r['ip_address'] ?? '',
			);
			foreach ( $cols as $c ) {
				$line[] = (string) ( $r['data'][ $c->field_key ] ?? '' );
			}
			$line[] = (string) ( $r['data']['_agreement'] ?? '' );
			$line[] = (string) ( $r['admin_notes'] ?? '' );
			fputcsv( $out, $line );
		}
		fclose( $out );
		exit;
	}

	/** Count submissions by status for a form. */
	public static function count_by_status( int $form_id ): array {
		global $wpdb;
		$t    = $wpdb->prefix . 'ah_form_submissions';
		$rows = $wpdb->get_results(
			$wpdb->prepare( "SELECT sub_status, COUNT(*) AS cnt FROM `{$t}` WHERE form_id = %d GROUP BY sub_status", $form_id ),
			ARRAY_A
		) ?: array();
		$counts = array( 'new' => 0, 'read' => 0, 'replied' => 0, 'closed' => 0 );
		foreach ( $rows as $r ) {
			if ( isset( $counts[ $r['sub_status'] ] ) ) { $counts[ $r['sub_status'] ] = (int) $r['cnt']; }
		}
		$counts['all'] = array_sum( $counts );
		return $counts;
	}

	// ── Shortcode renderer ───────────────────────────────────────────────────

	public static function render( array $atts ): string {
		$form_id = (int) ( $atts['id'] ?? 0 );
		$form    = $form_id ? self::get( $form_id ) : null;
		$fields  = $form_id ? self::get_fields( $form_id ) : array();

		if ( ! $form || empty( $fields ) ) {
			return '<p style="color:#6b7280;font-style:italic;">Form not configured yet.</p>';
		}

		$uid   = 'ahf_' . $form_id . '_' . wp_rand( 100, 999 );
		$nonce = wp_create_nonce( 'ah_frontend_nonce' );
		$ajax  = admin_url( 'admin-ajax.php' );
		$steps = self::build_structure( $fields );
		$multi = count( $steps ) > 1;
		$draft = ! empty( $form->save_draft );
		$head  = self::get_header_style( $form_id );
		// Conditions may only point at real input fields on this form.
		$valid_keys = array();
		foreach ( $fields as $vf ) {
			if ( ! self::is_structural( $vf->field_type ) && 'markup' !== $vf->field_type ) {
				$valid_keys[] = $vf->field_key;
			}
		}

		ob_start();
		?>
<style>
@keyframes ah-spin{to{transform:rotate(360deg)}}
.ah-fw{max-width:100%}
.ah-fw .ah-sp{animation:ah-spin .8s linear infinite;display:none}
.ah-fw .ah-req{color:#e53935;margin-left:2px}
/* Form fields */
.ch-form-group{margin-bottom:6px}
.ch-form-label{display:block;font-size:14px;font-weight:600;color:#1f2937;margin-bottom:7px}
.ch-form-input,
.ch-form-textarea,
.ch-form-select{width:100%;padding:6px 8px;border:1.5px solid #d1d5db;border-radius:8px;font-size:15px;font-family:inherit;color:#111827;background:#fff;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;outline:none;appearance:none}
.ch-form-input:focus,
.ch-form-textarea:focus,
.ch-form-select:focus{border-color:#1a3c5e;box-shadow:0 0 0 3px rgba(26,60,94,.1)}
.ch-form-input::placeholder,
.ch-form-textarea::placeholder{color:#9ca3af}
.ch-form-textarea{min-height:130px;resize:vertical;line-height:1.6}
.ch-form-select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:40px;cursor:pointer}
/* Radio / checkbox lists - larger targets than the browser default */
.ch-form-radio-group,.ch-form-checkbox-group{display:flex;flex-direction:column;gap:10px}
.ch-form-radio-group label,.ch-form-checkbox-group label{display:flex;align-items:center;gap:11px;font-size:15px;line-height:1.45;color:#374151;cursor:pointer}
.ch-form-radio-group input[type="radio"],.ch-form-checkbox-group input[type="checkbox"]{width:19px;height:19px;margin:0;flex-shrink:0;accent-color:#1a3c5e;cursor:pointer}
.ch-form-radio-group label:hover,.ch-form-checkbox-group label:hover{color:#111827}
.ch-form-desc{font-size:11.5px;color:#9ca3af;margin:4px 0 0;line-height:1.4}
.ch-form-submit{display:inline-flex;align-items:center;gap:8px;background:#1a3c5e;color:#fff;border:none;border-radius:8px;padding:13px 32px;font-size:15px;font-weight:600;cursor:pointer;font-family:inherit;letter-spacing:.01em;transition:background .15s,transform .1s}
.ch-form-submit:hover{background:#15304d}
.ch-form-submit:active{transform:scale(.98)}
.ch-form-submit:disabled{opacity:.6;cursor:not-allowed;transform:none}
/* Feedback messages */
.ch-form-feedback{display:none;border-radius:8px;padding:6px 8px;font-size:14px;margin-bottom:0}
.ch-form-feedback + .ch-form-feedback{margin-top:10px}
.ch-form-feedback.success{display:block;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;margin-bottom: 10px;}
.ch-form-feedback.error{display:block;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;margin-bottom: 10px;}
/* Agreement section */
.ch-agr-intro{font-size:14px;line-height:1.7;color:#4b5563;margin-bottom:14px;padding:14px 16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px}
.ch-agr-iframe-wrap{border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:14px}
.ch-agr-iframe{width:100%;height:240px;border:none;display:block}
.ch-form-agreement .ch-agreement-label{display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:14px;line-height:1.6;font-weight:400;color:#374151}
/* Agreement toggle switch (replaces the plain checkbox) */
.ch-toggle-switch{position:relative;display:inline-block;flex-shrink:0;width:40px;height:22px;margin-top:1px}
.ch-toggle-switch .ch-agreement-chk{position:absolute;inset:0;width:100%;height:100%;margin:0;opacity:0;cursor:pointer;z-index:1}
.ch-toggle-track{position:absolute;inset:0;background:#d1d5db;border-radius:999px;transition:background .15s}
.ch-toggle-track::before{content:"";position:absolute;left:3px;top:3px;width:16px;height:16px;background:#fff;border-radius:50%;box-shadow:0 1px 2px rgba(0,0,0,.25);transition:transform .15s}
.ch-toggle-switch .ch-agreement-chk:checked + .ch-toggle-track{background:var(--client-color,#1a3c5e)}
.ch-toggle-switch .ch-agreement-chk:checked + .ch-toggle-track::before{transform:translateX(18px)}
.ch-toggle-switch .ch-agreement-chk:focus-visible + .ch-toggle-track{box-shadow:0 0 0 3px var(--client-color,#1a3c5e)}
.ch-terms-link{color:#1a3c5e;text-decoration:underline;font-weight:600;margin-left:3px;margin-right:3px}
.ch-terms-link:hover{color:#15304d}
/* Multi-step: progress bar + steps */
.ah-fw [hidden]{display:none!important}
.ah-steps-bar{display:flex;list-style:none;margin:0 0 26px;padding:0}
.ah-step-chip{position:relative;flex:1 1 0;min-width:0;display:flex;flex-direction:column;align-items:center;gap:9px;padding:0 4px;text-align:center}
/* The rail runs behind the markers; each step draws its own half on either side. */
.ah-step-chip::before,.ah-step-chip::after{content:"";position:absolute;top:16px;height:2px;width:50%;background:#e5e7eb;transition:background .2s}
.ah-step-chip::before{left:0}
.ah-step-chip::after{right:0}
.ah-step-chip:first-child::before,.ah-step-chip:last-child::after{display:none}
.ah-step-chip.is-done::before,.ah-step-chip.is-done::after,.ah-step-chip.is-on::before{background:#1a3c5e}
.ah-step-num{position:relative;z-index:1;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;background:#fff;border:2px solid #e5e7eb;color:#9ca3af;font-size:13.5px;font-weight:700;flex-shrink:0;transition:background .2s,border-color .2s,color .2s,box-shadow .2s}
.ah-step-chip.is-on .ah-step-num{background:#1a3c5e;border-color:#1a3c5e;color:#fff;box-shadow:0 0 0 4px rgba(26,60,94,.12)}
.ah-step-chip.is-done .ah-step-num{background:#1a3c5e;border-color:#1a3c5e;color:transparent}
.ah-step-chip.is-done .ah-step-num::after{content:"\2713";position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px;font-weight:700}
/* A step icon sits inside the marker in place of the number. It strokes with
   currentColor, so the is-done rule above hides it and the tick takes over. */
.ah-step-ico{width:17px;height:17px;display:block}
.ah-step-ico-txt{font-size:15px;line-height:1;display:block}
/* width:100% makes it wrap inside the chip instead of sizing to max-content;
   break-word (never "anywhere") stops "Requirements" splitting mid-word. */
.ah-step-name{width:100%;font-size:12.5px;font-weight:600;color:#9ca3af;line-height:1.3;overflow-wrap:break-word;word-break:normal;hyphens:none;transition:color .2s}
.ah-step-chip.is-on .ah-step-name{color:#1a3c5e}
.ah-step-chip.is-done .ah-step-name{color:#4b5563}
.ah-steps-now{display:none;margin:-14px 0 20px;text-align:center;font-size:13px;font-weight:600;color:#1a3c5e;line-height:1.4}
.ah-steps-now b{display:block;font-size:11px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:#9ca3af}
/* ── Step header styles (per form; see get_header_style) ──
   'bar' is the original and needs no rules. 'split' puts the current step's
   name on the left and the chips on the right; 'plain' drops the chips and
   keeps the name alone. Both reuse .ah-steps-now, which showStep() already
   fills - so they cost no extra markup and no extra script. */
.ah-form-head--split{display:flex;align-items:center;justify-content:space-between;gap:24px;margin-bottom:12px}
/* order, not markup: the chips come first in the DOM so that the progress bar
   still reads first for a screen reader and for the no-CSS fallback. */
.ah-form-head--split .ah-steps-bar{order:2;flex:1 1 auto;min-width:0;max-width:60%;margin:0}
.ah-form-head--split .ah-steps-now{order:1;display:block;flex:0 1 auto;margin:0;text-align:left;font-size:24px;font-weight:700;color:#111827;line-height:1.25}
.ah-form-head--split .ah-steps-now b{margin-bottom:5px;color:#a8812f}
/* the in-step <h3> would repeat what the header now says */
.ah-form-head--split~.ah-step>.ah-step-title,.ah-form-head--plain~.ah-step>.ah-step-title{display:none}
.ah-form-head--plain .ah-steps-now{display:block;margin:0 0 14px;text-align:left;font-size:22px;font-weight:700;color:#111827;line-height:1.25}
.ah-form-head--plain .ah-steps-now b{margin-bottom:5px;color:#9ca3af}
@media(max-width:640px){
  /* Stacked: the wording leads, the chips become a slim progress strip under it
     rather than a squeezed copy of the desktop bar. */
  .ah-form-head--split{flex-direction:column;align-items:stretch;gap:10px;margin-bottom:12px}
  .ah-form-head--split .ah-steps-bar{order:2;max-width:none;width:100%;padding:0 2px}
  .ah-form-head--split .ah-steps-now{order:1;font-size:21px}
  .ah-form-head--split .ah-steps-now b{font-size:10.5px;margin-bottom:3px}
  .ah-form-head--split .ah-step-num{width:26px;height:26px;font-size:11.5px}
  .ah-form-head--split .ah-step-ico{width:13px;height:13px}
  .ah-form-head--split .ah-step-chip{gap:0}
  .ah-form-head--split .ah-step-chip::before,
  .ah-form-head--split .ah-step-chip::after{top:12px}
  .ah-form-head--plain .ah-steps-now{font-size:20px}
}
/* Below this width a four-across label row cannot hold a word like
   "Requirements" without breaking it, so keep the markers and name the
   current step underneath instead. */
@media (max-width:520px){
  .ah-step-num{width:28px;height:28px;font-size:12px}
  .ah-step-ico{width:14px;height:14px}
  .ah-step-chip::before,.ah-step-chip::after{top:13px}
  .ah-steps-bar{margin-bottom:12px}
  .ah-step-name{display:none}
  .ah-steps-now{display:block}
}
.ah-step-title{font-size:17px;font-weight:700;color:#111827;margin:0 0 4px;line-height:1.3}
.ah-step-desc{font-size:13.5px;color:#6b7280;margin:0 0 14px;line-height:1.6}
/* Field groups (fieldsets) */
.ah-group{border:1px solid #e5e7eb;border-radius:4px;margin-bottom:14px;background:#fff;overflow:hidden}
.ah-group-head{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;box-sizing:border-box;padding:12px 14px;margin:0;background:#f8fafc;border:none;border-bottom:1px solid #e5e7eb;font-family:inherit;font-size:14.5px;font-weight:700;color:#111827;text-align:left;cursor:pointer}
.ah-group-head.is-static{cursor:default}
.ah-group-head:hover:not(.is-static){background:#f1f5f9}
.ah-group-chev{width:9px;height:9px;border-right:2px solid #6b7280;border-bottom:2px solid #6b7280;transform:rotate(45deg);transition:transform .18s;flex-shrink:0;margin-right:4px}
.ah-group.is-open>.ah-group-head .ah-group-chev{transform:rotate(-135deg);margin-top:5px}
.ah-group-body{padding:14px}
.ah-group-desc{font-size:13px;color:#6b7280;margin:0 0 12px;line-height:1.6}
/* collapsible groups: same band as the head, since it cannot live inside the button */
.ah-group-desc--band{padding:9px 14px 11px;background:#f8fafc;border-bottom:1px solid #e5e7eb}
.ah-group-desc a{color:#1a3c5e;text-decoration:underline}
/* Step navigation - buttons size to their text, never stretch to fill */
.ah-form-nav{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.ah-nav-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:12px 20px;border-radius:8px;font-size:14.5px;font-weight:600;font-family:inherit;line-height:1.2;white-space:nowrap;cursor:pointer;border:1.5px solid #1a3c5e;background:#fff;color:#1a3c5e;transition:background .15s}
.ah-nav-btn:hover{background:#f1f5f9}
.ah-form-next{background:#1a3c5e;color:#fff}
.ah-form-next:hover{background:#15304d}
.ah-form-clear{margin-left:auto;border-color:#e5e7eb;color:#6b7280;padding:12px 16px}
.ah-form-clear:hover{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
.ah-fw .ch-form-submit{white-space:nowrap}
/* Narrow screens: primary action on its own row, the rest paired beneath it */
@media (max-width:520px){
  .ah-form-nav{gap:8px}
  .ah-form-nav .ah-form-next,.ah-form-nav .ch-form-submit,.ah-form-nav .ah-form-again{flex:1 1 100%}
  .ah-form-nav .ah-form-prev,.ah-form-nav .ah-form-clear{flex:1 1 calc(50% - 4px);margin-left:0;padding:12px 10px}
  .ah-fw .ch-form-submit{justify-content:center}
}
.ah-form-nav .ah-form-next {
	margin: unset ;
}
.ah-draft-note{font-size:12px;color:#9ca3af;margin:0 0 12px}
.ah-captcha{margin:0 0 14px}
.ah-captcha .g-recaptcha>div,.ah-captcha iframe{max-width:100%}
/* 12-column layout: fields span 12 unless given a width */
.ah-step,.ah-group-body{display:grid;grid-template-columns:repeat(12,1fr);column-gap:16px;align-content:start}
/* A field's width is a share of a FULL row. Inside a group that is itself half
   a row or less there is no room to subdivide again - a "half" field there ends
   up a quarter of the page, with the label wrapping over three lines - so those
   children span their group instead. */
.ah-group.ah-col-6>.ah-group-body>[class*="ah-col-"],
.ah-group.ah-col-4>.ah-group-body>[class*="ah-col-"],
.ah-group.ah-col-3>.ah-group-body>[class*="ah-col-"]{grid-column:1/-1}
/* Groups sharing a row stay the same height - grid stretches them by default,
   so they must NOT be align-self:start. */
.ah-step>.ah-group{align-self:stretch;display:flex;flex-direction:column}
.ah-step>.ah-group>.ah-group-body{flex:1 1 auto;align-content:start}
.ah-step>*,.ah-group-body>*{grid-column:1/-1;min-width:0}
.ah-step>.ah-col-8,.ah-group-body>.ah-col-8{grid-column:span 8}
.ah-step>.ah-col-6,.ah-group-body>.ah-col-6{grid-column:span 6}
.ah-step>.ah-col-4,.ah-group-body>.ah-col-4{grid-column:span 4}
.ah-step>.ah-col-3,.ah-group-body>.ah-col-3{grid-column:span 3}
@media (max-width:640px){
  .ah-step>[class*="ah-col-"],.ah-group-body>[class*="ah-col-"]{grid-column:1/-1}
}
/* Icons */
.ahf-ico{width:20px;height:20px;display:block;flex-shrink:0}
.ahf-ico-txt{font-size:17px;line-height:1;display:block}
.ahf-ico-sm{width:16px;height:16px}
.ahf-ico-sm.ahf-ico-sm-txt{font-size:14px}
/* Image icons sit in the same box as the built-in SVGs, whatever the source
   file's aspect ratio - so mixing artwork and icon names never jolts a row. */
.ahf-ico-img{object-fit:contain}
.ahf-ico-inline{display:inline-flex;align-items:center;vertical-align:-3px;margin-right:6px;color:#a8812f}
.ahf-ico-badge{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;border:1.5px solid #e2c98d;background:#fdf8ee;color:#a8812f;flex-shrink:0}
.ah-step-title{display:flex;align-items:center;gap:10px}
.ah-group-title{display:flex;align-items:center;gap:10px;min-width:0}
/* Option tiles */
.ah-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(115px,1fr));gap:10px}
.ah-tile{position:relative;display:block;cursor:pointer}
.ah-tile input{position:absolute;opacity:0;width:1px;height:1px;margin:0;pointer-events:none}
.ah-tile-in{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;height:100%;padding:14px 8px;border:1.5px solid #e5e7eb;border-radius:10px;background:#fff;text-align:center;transition:border-color .15s,background .15s,box-shadow .15s}
.ah-tile:hover .ah-tile-in{border-color:#cbd5e1;background:#f8fafc}
.ah-tile-ico{display:flex;align-items:center;justify-content:center;color:#a8812f}
.ah-tile-lbl{font-size:13px;font-weight:600;color:#374151;line-height:1.3}
.ah-tile input:checked+.ah-tile-in{border-color:#a8812f;background:#fdf8ee;box-shadow:0 0 0 2px rgba(168,129,47,.18)}
.ah-tile input:focus-visible+.ah-tile-in{box-shadow:0 0 0 3px rgba(26,60,94,.25)}
/* Pills / cards - the same choice field as selectable buttons instead of dots.
   Pills wrap inline at their own width; cards fill the row in equal columns. */
.ah-chips{display:flex;flex-wrap:wrap;gap:8px}
.ah-chips--cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}
.ah-chip{position:relative;display:block;cursor:pointer}
.ah-chip input{position:absolute;opacity:0;width:1px;height:1px;margin:0;pointer-events:none}
.ah-chip-in{display:flex;align-items:center;justify-content:center;gap:7px;height:100%;min-width:46px;padding:10px 16px;border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;font-size:13.5px;font-weight:600;color:#374151;line-height:1.3;text-align:center;transition:border-color .15s,background .15s,color .15s,box-shadow .15s}
.ah-chips--cards .ah-chip-in{padding:12px 14px}
.ah-chip:hover .ah-chip-in{border-color:#cbd5e1;background:#f8fafc}
.ah-chip-ico{display:flex;align-items:center;justify-content:center;color:#a8812f}
/* Selected state follows the site's own colour when the theme defines one
   (--client-color, else --color-primary); the navy is only the fallback for a
   site that sets neither. */
.ah-chip input:checked+.ah-chip-in{border-color:var(--client-color,var(--color-primary,#1a3c5e));background:var(--client-color,var(--color-primary,#1a3c5e));color:#fff}
.ah-chip input:checked+.ah-chip-in .ah-chip-ico{color:var(--color-accent,#e6c97a)}
.ah-chip:hover .ah-chip-in{border-color:var(--client-color,var(--color-primary,#cbd5e1))}
.ah-chip input:focus-visible+.ah-chip-in{box-shadow:0 0 0 3px rgba(26,60,94,.25)}
/* 'checks': boxes that keep a visible tick/dot, for lists where a visitor wants
   to see what is ticked at a glance rather than infer it from a filled button. */
.ah-chips--checks{display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr))}
.ah-chips--checks .ah-chip-in{justify-content:flex-start;text-align:left;gap:10px;padding:11px 13px;font-weight:500}
.ah-chips--checks .ah-chip-in::before{content:"";flex:0 0 auto;width:18px;height:18px;border:1.5px solid #cbd5e1;border-radius:5px;background:#fff;background-repeat:no-repeat;background-position:center;background-size:12px;transition:background-color .15s,border-color .15s}
.ah-chips--checks .ah-chip input[type="radio"]+.ah-chip-in::before,
.ah-chips--checks input[type="radio"]+.ah-chip-in::before{border-radius:50%}
.ah-chips--checks input:checked+.ah-chip-in{background:#fff;color:#111827;border-color:var(--client-color,var(--color-primary,#1a3c5e))}
.ah-chips--checks input:checked+.ah-chip-in::before{background-color:var(--client-color,var(--color-primary,#1a3c5e));border-color:var(--client-color,var(--color-primary,#1a3c5e));background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6 9 17l-5-5'/%3E%3C/svg%3E")}
.ah-chips--checks input:checked+.ah-chip-in .ah-chip-ico{color:var(--client-color,var(--color-primary,#1a3c5e))}
/* Prefix / suffix input frame */
.ah-affix,.ah-tel{display:flex;align-items:stretch;border:1.5px solid #d1d5db;border-radius:8px;background:#fff;overflow:hidden}
.ah-affix:focus-within,.ah-tel:focus-within{border-color:#1a3c5e;box-shadow:0 0 0 3px rgba(26,60,94,.1)}
.ah-affix .ch-form-input,.ah-tel .ch-form-input{border:none;border-radius:0;flex:1;min-width:0}
.ah-affix .ch-form-input:focus,.ah-tel .ch-form-input:focus{box-shadow:none}
.ah-affix-txt{display:flex;align-items:center;padding:0 11px;background:#f8fafc;color:#6b7280;font-size:14.5px;border-right:1.5px solid #e5e7eb;white-space:nowrap}
.ah-affix-txt.suf{border-right:none;border-left:1.5px solid #e5e7eb}
/* Phone country selector - fixed, compact; the number keeps the remaining width */
.ah-tel-cc{flex:0 0 auto;width:88px;max-width:38%;border:none;border-right:1.5px solid #e5e7eb;background:#f8fafc;color:#374151;font-family:inherit;font-size:13.5px;padding:0 4px 0 8px;cursor:pointer;outline:none;text-overflow:ellipsis}
.ah-tel .ch-form-input{flex:1 1 auto;min-width:0;padding-left:10px}
.ah-tel-other{flex:0 0 auto;width:64px;border:none;border-right:1.5px solid #e5e7eb;background:#fffdf5;font-family:inherit;font-size:13.5px;padding:0 8px;outline:none;text-align:center}
@media (max-width:400px){.ah-tel-cc{width:78px;font-size:12.5px;padding-left:6px}}
/* A field that failed validation: tinted, not just outlined, so a page of
   inputs shows at a glance which ones still need attention. */
.ah-invalid{border-color:#e53935!important;background:#fef2f2!important;box-shadow:0 0 0 3px rgba(229,57,53,.12)!important}
.ah-invalid::placeholder{color:#c98b88}
/* affix/tel wrappers hold the border, so tint the frame and its input together */
.ah-affix:has(.ah-invalid),.ah-tel:has(.ah-invalid){border-color:#e53935;background:#fef2f2;box-shadow:0 0 0 3px rgba(229,57,53,.12)}
.ah-affix:has(.ah-invalid) .ah-invalid,.ah-tel:has(.ah-invalid) .ah-invalid{box-shadow:none!important}
.ah-affix:has(.ah-invalid) .ah-affix-txt,.ah-tel:has(.ah-invalid) .ah-affix-txt{background:#fee2e2;border-color:#fecaca;color:#b91c1c}
/* choice fields have no box of their own - tint the options instead */
.ah-invalid+.ah-chip-in,.ah-invalid+.ah-tile-in{border-color:#e53935;background:#fef2f2}
</style>
<?php if ( ! empty( $form->custom_css ) ) : ?>
<style id="ah-form-<?php echo (int) $form_id; ?>-css">
<?php echo $form->custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted, manage_options-only input, same model as AH_Custom_Code_Service. ?>
</style>
<?php endif; ?>

<div class="ah-fw" id="<?php echo esc_attr( $uid ); ?>">

  <?php // method="post" matters: if the JS ever fails to attach, the browser falls
        // back to a native submit. Without it that is a GET, which would put every
        // answer in the URL bar, browser history and server logs. ?>
  <form method="post" novalidate>
    <input type="hidden" name="nonce"   value="<?php echo esc_attr( $nonce ); ?>">
    <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>">
    <div style="display:none;visibility:hidden" aria-hidden="true"><input type="text" name="ah_hp" tabindex="-1" autocomplete="off"></div>

    <?php if ( $multi ) : ?>
    <div class="ah-form-head ah-form-head--<?php echo esc_attr( $head ); ?>">
    <?php if ( 'plain' !== $head ) : ?>
    <ol class="ah-steps-bar" aria-label="Form progress">
      <?php foreach ( $steps as $si => $st ) : ?>
      <li class="ah-step-chip<?php echo 0 === $si ? ' is-on' : ''; ?>" data-i="<?php echo (int) $si; ?>">
        <span class="ah-step-num"><?php
        // A step's icon belongs here: the only other place it renders is the
        // <h3> step title, which most themes hide once the progress bar is shown.
        if ( $st['icon'] ) {
          echo self::icon_svg( $st['icon'], 'ah-step-ico' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text.
        } else {
          echo (int) $si + 1;
        }
        ?></span>
        <span class="ah-step-name"><?php echo esc_html( '' !== $st['title'] ? $st['title'] : 'Step ' . ( $si + 1 ) ); ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
    <?php /* Narrow screens hide the per-step labels; this names the current one
             instead - and in the split/plain headers it is the heading itself,
             on every width. Filled by showStep(). */ ?>
    <p class="ah-steps-now" aria-live="polite"></p>
    </div>
    <?php endif; ?>

    <?php foreach ( $steps as $si => $st ) : ?>
    <div class="ah-step<?php echo $st['class'] ? ' ' . esc_attr( $st['class'] ) : ''; ?>" data-step="<?php echo (int) $si; ?>"<?php echo ! empty( $st['next'] ) ? ' data-next="' . esc_attr( $st['next'] ) . '"' : ''; ?><?php echo $si > 0 ? ' hidden' : ''; ?>>
      <?php if ( $multi && '' !== $st['title'] ) : ?>
      <h3 class="ah-step-title">
        <?php if ( $st['icon'] ) : ?><span class="ahf-ico-badge"><?php echo self::icon_svg( $st['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text. ?></span><?php endif; ?>
        <span><?php echo esc_html( $st['title'] ); ?></span>
      </h3>
      <?php endif; ?>
      <?php if ( '' !== $st['desc'] ) : ?><p class="ah-step-desc"><?php echo wp_kses_post( $st['desc'] ); ?></p><?php endif; ?>

      <?php $acc_seen = false; // only the first accordion group in a step starts open ?>
      <?php foreach ( $st['blocks'] as $bi => $b ) : ?>
        <?php if ( 'field' === $b['type'] ) : ?>
          <?php echo self::render_field( $b['field'], $uid, $valid_keys ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside render_field(). ?>
        <?php else : ?>
          <?php
          $g_open = ( 'collapsed' !== $b['mode'] );          // starts expanded?
          $g_tog  = ( 'open' !== $b['mode'] );               // user can collapse it?
          if ( 'accordion' === $b['mode'] ) {
            // Accordions allow one open group at a time - honour that on first paint
            // too, otherwise every accordion group in the step would render open.
            $g_open   = ! $acc_seen;
            $acc_seen = true;
          }
          $g_id = esc_attr( $uid . '_g' . $si . '_' . $bi );
          ?>
          <?php $g_ico = $b['icon'] ? '<span class="ahf-ico-badge">' . self::icon_svg( $b['icon'] ) . '</span>' : ''; ?>
          <?php $g_col = self::width_class( $b['settings'] ); ?>
          <div class="ah-group<?php echo $g_open ? ' is-open' : ''; ?><?php echo $g_col ? ' ' . esc_attr( $g_col ) : ''; ?><?php echo $b['class'] ? ' ' . esc_attr( $b['class'] ) : ''; ?>" data-mode="<?php echo esc_attr( $b['mode'] ); ?>"<?php echo self::cond_attrs( $b['settings'], $valid_keys ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_attr(). ?>>
            <?php if ( $g_tog ) : ?>
              <button type="button" class="ah-group-head" aria-expanded="<?php echo $g_open ? 'true' : 'false'; ?>" aria-controls="<?php echo $g_id; ?>">
                <span class="ah-group-title"><?php echo $g_ico; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text. ?><span><?php echo esc_html( '' !== $b['title'] ? $b['title'] : 'Details' ); ?></span></span>
                <span class="ah-group-chev" aria-hidden="true"></span>
              </button>
            <?php elseif ( '' !== $b['title'] ) : ?>
              <div class="ah-group-head is-static"><span class="ah-group-title"><?php echo $g_ico; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text. ?><span><?php echo esc_html( $b['title'] ); ?></span></span></div>
            <?php endif; ?>
            <?php /* A togglable head is a <button>, and flow content cannot be
                     nested inside one, so for those the line follows the head and
                     is painted to read as part of the same band. Either way it is
                     OUTSIDE .ah-group-body, which is hidden while a collapsed
                     group is shut - and this is the line that tells you whether to
                     open it. HTML allowed; stored with wp_kses_post() too. */ ?>
            <?php if ( $g_tog && '' !== $b['desc'] ) : ?><p class="ah-group-desc ah-group-desc--band"><?php echo wp_kses_post( $b['desc'] ); ?></p><?php endif; ?>
            <div class="ah-group-body" id="<?php echo $g_id; ?>"<?php echo $g_open ? '' : ' hidden'; ?>>
              <?php if ( ! $g_tog && '' !== $b['desc'] ) : ?><p class="ah-group-desc"><?php echo wp_kses_post( $b['desc'] ); ?></p><?php endif; ?>
              <?php foreach ( $b['fields'] as $gf ) : ?>
                <?php echo self::render_field( $gf, $uid, $valid_keys ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside render_field(). ?>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>


    <?php
    // ── Form-level Agreement / Terms section ──────────────────────────────
    $agr = self::get_agreement( $form_id );
    if ( ! empty( $agr['enabled'] ) ) :
      $agr_uid  = esc_attr( $uid . '_agr' );
      $agr_url  = $agr['url'];
      $agr_type = $agr['type'];
    ?>
    <?php // On a multi-step form the agreement belongs with the submit button, so it stays hidden until the last step. ?>
    <div class="ah-form-final" style="margin-bottom:14px"<?php echo $multi ? ' hidden' : ''; ?>>
      <?php if ( $agr_type === 'iframe' && $agr_url ) : ?>
        <div class="ch-agr-iframe-wrap">
          <iframe class="ch-agr-iframe" src="<?php echo esc_url( $agr_url ); ?>" loading="lazy" title="<?php echo esc_attr( $agr['link_text'] ); ?>"></iframe>
        </div>
      <?php endif; ?>
      <div class="ch-form-group ch-form-agreement" style="margin-bottom:0">
        <label class="ch-agreement-label" for="<?php echo $agr_uid; ?>">
          <span class="ch-toggle-switch">
            <input type="checkbox" class="ch-agreement-chk" id="<?php echo $agr_uid; ?>" name="ah_agreement" value="1" required>
            <span class="ch-toggle-track"></span>
          </span>
          <span>
            <?php if ( $agr['before'] ) echo esc_html( $agr['before'] ); ?>
            <?php if ( $agr_url && $agr_type === 'link' ) : ?>
              <a class="ch-terms-link" href="<?php echo esc_url( $agr_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $agr['link_text'] ); ?></a>
            <?php elseif ( $agr_type === 'popup' ) : ?>
              <button type="button" class="ch-terms-popup-btn" data-popup="ch-tpop-<?php echo esc_attr( $uid ); ?>"><?php echo esc_html( $agr['link_text'] ); ?></button>
            <?php elseif ( $agr['link_text'] ) : ?>
              <strong class="ch-terms-link" style="text-decoration:none"><?php echo esc_html( $agr['link_text'] ); ?></strong>
            <?php endif; ?>
            <?php if ( $agr['after'] ) echo esc_html( $agr['after'] ); ?>
          </span>
        </label>
      </div>
      <?php if ( $agr_type === 'popup' && ! empty( $agr['popup_html'] ) ) : ?>
        <div id="ch-tpop-<?php echo esc_attr( $uid ); ?>" role="dialog" aria-modal="true" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;align-items:center;justify-content:center;padding:24px;box-sizing:border-box;backdrop-filter:blur(2px)">
          <div style="background:#fff;border-radius:16px;max-width:600px;width:100%;max-height:82vh;display:flex;flex-direction:column;position:relative;box-shadow:0 24px 64px rgba(0,0,0,.28);outline:1px solid rgba(255,255,255,0.12);ring:1px solid rgba(0,0,0,0.06)">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid #f0f0f0;flex-shrink:0">
              <span style="font-size:1rem;font-weight:700;color:#111827;letter-spacing:-0.01em">Terms &amp; Conditions</span>
              <button type="button" aria-label="Close" style="background:none;border:1px solid #e5e7eb;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-size:16px;line-height:1;cursor:pointer;color:#6b7280;padding:0;flex-shrink:0;transition:border-color .15s,color .15s">&times;</button>
            </div>
            <div class="ch-terms-popup-content" style="overflow-y:auto;padding:20px 24px 24px;flex:1;min-height:0"><?php echo wp_kses_post( $agr['popup_html'] ); ?></div>
          </div>
        </div>
        <style>
.ch-terms-popup-btn{background:none;border:none;padding:0;color:#1a3c5e;text-decoration:underline;font-weight:600;cursor:pointer;font-family:inherit;font-size:inherit;margin:0 3px}
.ch-terms-popup-content{color:#4b5563;font-size:0.875rem;line-height:1.75}
.ch-terms-popup-content h1,.ch-terms-popup-content h2,.ch-terms-popup-content h3{color:#111827;font-weight:700;margin:0 0 10px;line-height:1.3}
.ch-terms-popup-content h1{font-size:1.1rem}
.ch-terms-popup-content h2{font-size:1rem;margin-top:18px}
.ch-terms-popup-content h3{font-size:0.95rem;margin-top:14px}
.ch-terms-popup-content p{margin:0 0 10px}
.ch-terms-popup-content p:last-child{margin-bottom:0}
.ch-terms-popup-content ul,.ch-terms-popup-content ol{margin:0 0 12px;padding-left:18px}
.ch-terms-popup-content li{margin-bottom:5px}
.ch-terms-popup-content strong{color:#111827;font-weight:600}
.ch-terms-popup-content a{color:#1a3c5e;text-decoration:underline}
</style>
        <script>
        (function(){
          var ov=document.getElementById('ch-tpop-<?php echo esc_js( $uid ); ?>');
          if(!ov)return;
          document.querySelectorAll('[data-popup="ch-tpop-<?php echo esc_js( $uid ); ?>"]').forEach(function(b){
            b.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();ov.style.display='flex';});
          });
          var close=ov.querySelector('[aria-label="Close"]');
          if(close)close.addEventListener('click',function(){ov.style.display='none';});
          ov.addEventListener('click',function(e){if(e.target===ov)ov.style.display='none';});
          document.addEventListener('keydown',function(e){if(e.key==='Escape'&&ov.style.display==='flex')ov.style.display='none';});
        })();
        </script>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php $submit_label = ! empty( $form->submit_label ) ? $form->submit_label : 'Send Message'; ?>
    <div class="ah-form-nav">
      <button type="button" class="ah-nav-btn ah-form-prev" hidden>&#8592; Back</button>
      <button type="button" class="ah-nav-btn ah-form-next"<?php echo $multi ? '' : ' hidden'; ?>>Next &#8594;</button>
      <button type="submit" class="ch-form-submit ah-sb btn btn-primary"<?php echo $multi ? ' hidden' : ''; ?>>
        <svg class="ah-sp" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:none;vertical-align:middle;margin-right:6px"><circle cx="12" cy="12" r="10" stroke-dasharray="31 62"/></svg>
        <span class="ah-bt"><?php echo esc_html( $submit_label ); ?></span>
      </button>
      <?php if ( $draft ) : ?>
      <button type="button" class="ah-nav-btn ah-form-clear">Clear form</button>
      <?php endif; ?>
      <button type="button" class="ah-nav-btn ah-form-again" hidden>+ Submit another response</button>
    </div>

    <?php
  // ── Spam challenge ────────────────────────────────────────────────────
    $cap        = self::captcha_settings();
    $cap_on     = self::captcha_active( $form );
    $cap_widget = $cap_on && in_array( $cap['provider'], array( 'recaptcha_v2', 'turnstile' ), true );
    if ( $cap_on ) :
    ?>
    <?php if ( $cap_widget ) : ?>
      <div class="ah-captcha">
        <div class="<?php echo 'turnstile' === $cap['provider'] ? 'cf-turnstile' : 'g-recaptcha'; ?>"
             data-sitekey="<?php echo esc_attr( $cap['site_key'] ); ?>"></div>
      </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="ch-form-feedback" role="alert"><span></span></div>
    <div class="ch-form-feedback" role="alert"><span></span></div>
  </form>
</div>

<script>
(function(){
  var w = document.getElementById('<?php echo esc_js( $uid ); ?>');
  if (!w) return;
  var f   = w.querySelector('form');
  var btn = w.querySelector('.ah-sb');
  var btt = w.querySelector('.ah-bt');
  var sp  = w.querySelector('.ah-sp');
  var fb  = w.querySelectorAll('.ch-form-feedback');
  var sc  = fb[0];
  var ec  = fb[1];
  var agr = w.querySelector('.ch-agreement-chk');
  var steps = Array.prototype.slice.call(w.querySelectorAll('.ah-step'));
  var chips = Array.prototype.slice.call(w.querySelectorAll('.ah-step-chip'));
  var prev  = w.querySelector('.ah-form-prev');
  var next  = w.querySelector('.ah-form-next');
  var fin   = w.querySelector('.ah-form-final');
  var nav   = w.querySelector('.ah-form-nav');
  var cur   = 0;

  if (agr) {
    btn.disabled = !agr.checked;
    agr.addEventListener('change', function () { btn.disabled = !agr.checked; });
  }
  function msg(el, type, txt) {
    sc.className = 'ch-form-feedback';
    ec.className = 'ch-form-feedback';
    el.querySelector('span').textContent = txt;
    el.className = 'ch-form-feedback ' + type;
    el.scrollIntoView({behavior:'smooth',block:'nearest'});
  }
  function clearMsg() {
    sc.className = 'ch-form-feedback';
    ec.className = 'ch-form-feedback';
  }

  // ── Collapsible / accordion field groups ──────────────────────────────
  function setGroup(g, open) {
    var body = g.querySelector('.ah-group-body');
    var head = g.querySelector('.ah-group-head');
    if (!body) return;
    body.hidden = !open;
    g.classList.toggle('is-open', open);
    if (head && head.tagName === 'BUTTON') head.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  Array.prototype.forEach.call(w.querySelectorAll('.ah-group'), function (g) {
    var head = g.querySelector('.ah-group-head');
    var body = g.querySelector('.ah-group-body');
    if (!head || !body || head.tagName !== 'BUTTON') return;
    head.addEventListener('click', function () {
      var open = body.hidden; // about to open?
      if (open && g.getAttribute('data-mode') === 'accordion') {
        // Accordion groups close their siblings within the same step.
        var scope = g.closest('.ah-step') || w;
        Array.prototype.forEach.call(scope.querySelectorAll('.ah-group[data-mode="accordion"]'), function (o) {
          if (o !== g) setGroup(o, false);
        });
      }
      setGroup(g, open);
    });
  });

  // ── Conditional logic ─────────────────────────────────────────────────
  // A hidden block's inputs are disabled, so they neither block validation nor
  // post a value. The server re-checks every condition, so this is UX only.
  var condBlocks = Array.prototype.slice.call(f.querySelectorAll('[data-cond-field]'));
  var submitted = false;

  function valuesOf(key) {
    var out = [];
    var esc = key.replace(/"/g, '\\"');
    var els = f.querySelectorAll('[name="' + esc + '"], [name="' + esc + '[]"]');
    Array.prototype.forEach.call(els, function (el) {
      if (el.type === 'checkbox' || el.type === 'radio') {
        if (el.checked) out.push(el.value);
      } else if (el.value !== '') {
        out.push(el.value);
      }
    });
    return out;
  }
  function testCond(b) {
    var vals = valuesOf(b.getAttribute('data-cond-field') || '');
    var op   = b.getAttribute('data-cond-op') || 'is';
    var want = b.getAttribute('data-cond-value') || '';
    var joined = vals.join(', ');
    if (op === 'is')       return vals.indexOf(want) > -1;
    if (op === 'not')      return vals.indexOf(want) === -1;
    if (op === 'any')      return joined !== '';
    if (op === 'empty')    return joined === '';
    if (op === 'contains') return want !== '' && joined.toLowerCase().indexOf(want.toLowerCase()) > -1;
    return true;
  }
  function applyConds() {
    if (!condBlocks.length || submitted) return;
    // Document order, so an ancestor block is always resolved before its children.
    condBlocks.forEach(function (b) {
      var okOwn = testCond(b);
      var hiddenAncestor = b.parentElement && b.parentElement.closest('[data-cond-eff="0"]');
      var eff = okOwn && !hiddenAncestor;
      b.hidden = !eff;
      b.setAttribute('data-cond-eff', eff ? '1' : '0');
    });
    Array.prototype.forEach.call(f.querySelectorAll('input, select, textarea'), function (el) {
      if (el.name === 'nonce' || el.name === 'form_id') return;
      el.disabled = !!(el.closest && el.closest('[data-cond-eff="0"]'));
    });
  }
  if (condBlocks.length) {
    f.addEventListener('change', applyConds);
    f.addEventListener('input', applyConds);
  }

  // ── Validation ────────────────────────────────────────────────────────
  function stepOf(el) {
    var s = el.closest ? el.closest('.ah-step') : null;
    return s ? steps.indexOf(s) : -1;
  }
  function labelOf(el) {
    var grp = el.closest ? el.closest('.ch-form-group') : null;
    var lb  = grp ? grp.querySelector('.ch-form-label') : null;
    return lb ? lb.textContent.replace('*', '').trim() : 'This field';
  }
  function flag(el, text) {
    var si = stepOf(el);
    if (si > -1 && si !== cur) showStep(si, false);
    var g = el.closest ? el.closest('.ah-group') : null;
    if (g) setGroup(g, true); // never hide an error inside a collapsed group
    el.classList.add('ah-invalid');
    msg(ec, 'error', text);
    try { el.focus(); } catch (err) {}
    return false;
  }
  function validate(scope) {
    // Required checkbox groups: the markup flags only the first box of the group,
    // and a checkbox group can't use the native `required` attribute meaningfully.
    var reqGroups = scope.querySelectorAll('[data-required-group="true"]');
    for (var i = 0; i < reqGroups.length; i++) {
      var nm = reqGroups[i].getAttribute('name');
      if (nm && !scope.querySelector('input[name="' + nm.replace(/"/g, '\\"') + '"]:checked')) {
        return flag(reqGroups[i], labelOf(reqGroups[i]) + ' - please choose at least one option.');
      }
    }
    var els = scope.querySelectorAll('input, select, textarea');
    for (var j = 0; j < els.length; j++) {
      var el = els[j];
      if (el.disabled || el.type === 'hidden' || !el.willValidate) continue;
      if (!el.checkValidity()) {
        return flag(el, labelOf(el) + ' - ' + (el.validationMessage || 'please check this field.'));
      }
      // Catch an oversized upload here rather than after a long POST.
      if (el.type === 'file' && el.files && el.files.length) {
        var mx = parseInt(el.getAttribute('data-maxmb') || '0', 10);
        if (mx && el.files[0].size > mx * 1024 * 1024) {
          return flag(el, labelOf(el) + ' - that file is larger than ' + mx + ' MB.');
        }
      }
    }
    return true;
  }
  f.addEventListener('input', function (e) {
    if (e.target && e.target.classList) e.target.classList.remove('ah-invalid');
  });

  // ── Step navigation ───────────────────────────────────────────────────
  function showStep(i, scroll) {
    cur = i;
    var last = i === steps.length - 1;
    steps.forEach(function (s, n) { s.hidden = n !== i; });
    chips.forEach(function (c, n) {
      c.classList.toggle('is-on', n === i);
      c.classList.toggle('is-done', n < i);
    });
    var now = w.querySelector('.ah-steps-now');
    if (now && chips[i]) {
      var nm = chips[i].querySelector('.ah-step-name');
      now.innerHTML = '<b>Step ' + (i + 1) + ' of ' + steps.length + '</b>';
      now.appendChild(document.createTextNode(nm ? nm.textContent : ''));
    }
    if (prev) prev.hidden = i === 0;
    if (next) {
      next.hidden = last;
      // Each step may name its own forward button; blank falls back to "Next".
      // textContent, so an admin-authored label is never parsed as markup.
      var nlbl = steps[i] ? steps[i].getAttribute('data-next') : '';
      next.textContent = (nlbl || 'Next') + ' →';
    }
    if (btn)  btn.hidden  = !last;
    if (fin)  fin.hidden  = !last;
    clearMsg();
    if (scroll) w.scrollIntoView({behavior:'smooth', block:'start'});
  }
  if (steps.length > 1) {
    showStep(0, false);
    if (next) next.addEventListener('click', function () {
      if (validate(steps[cur])) showStep(cur + 1, true);
    });
    if (prev) prev.addEventListener('click', function () { showStep(cur - 1, true); });
  }

  // ── Draft autosave in the visitor's browser ───────────────────────────
  var draftKey = <?php echo $draft ? "'ah_form_draft_" . (int) $form_id . "'" : 'null'; ?>;
  var clearBtn = w.querySelector('.ah-form-clear');
  var draftTimer = null;

  // Never persist the nonce, the honeypot, or the agreement - consent is re-taken
  // on every visit rather than restored from an old session.
  function draftEls() {
    return f.querySelectorAll('input:not([type=hidden]):not([name="ah_hp"]):not([name="ah_agreement"]), select, textarea');
  }
  function saveDraft() {
    if (!draftKey || submitted) return;
    var d = {};
    Array.prototype.forEach.call(draftEls(), function (el) {
      if (!el.name) return;
      if (el.type === 'checkbox') {
        if (el.checked) { (d[el.name] = d[el.name] || []).push(el.value); }
      } else if (el.type === 'radio') {
        if (el.checked) d[el.name] = el.value;
      } else if (el.value !== '') {
        d[el.name] = el.value;
      }
    });
    try {
      if (Object.keys(d).length) { localStorage.setItem(draftKey, JSON.stringify(d)); }
      else { localStorage.removeItem(draftKey); }
    } catch (err) {} // private mode / quota - drafting is best-effort
  }
  function queueSave() {
    if (!draftKey) return;
    clearTimeout(draftTimer);
    draftTimer = setTimeout(saveDraft, 300);
  }
  function restoreDraft() {
    if (!draftKey) return;
    var raw = null, d = null;
    try { raw = localStorage.getItem(draftKey); } catch (err) { return; }
    if (!raw) return;
    try { d = JSON.parse(raw); } catch (err) { return; }
    if (!d || typeof d !== 'object') return;
    Array.prototype.forEach.call(draftEls(), function (el) {
      if (!el.name || !Object.prototype.hasOwnProperty.call(d, el.name)) return;
      var v = d[el.name];
      if (el.type === 'checkbox') {
        el.checked = Object.prototype.toString.call(v) === '[object Array]' && v.indexOf(el.value) > -1;
      } else if (el.type === 'radio') {
        el.checked = (el.value === v);
      } else if (typeof v === 'string') {
        el.value = v;
      }
    });
  }
  function clearDraft() {
    // Kill any debounced write first, or a keystroke made just before submitting
    // would land after this and resurrect the draft.
    clearTimeout(draftTimer);
    if (!draftKey) return;
    try { localStorage.removeItem(draftKey); } catch (err) {}
  }
  if (draftKey) {
    restoreDraft();
    f.addEventListener('input', queueSave);
    f.addEventListener('change', queueSave);
  }
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      f.reset(); // only this form's own controls - nothing else on the page
      clearDraft();
      Array.prototype.forEach.call(f.querySelectorAll('.ah-invalid'), function (el) {
        el.classList.remove('ah-invalid');
      });
      clearMsg();
      if (steps.length > 1) showStep(0, true);
      if (agr) btn.disabled = !agr.checked;
      applyConds();
    });
  }

  // Run once on load - after any draft restore, so restored answers drive it.
  applyConds();

  // ── "Submit another response" - reopen the form after a successful send ──
  var againBtn = w.querySelector('.ah-form-again');
  if (againBtn) {
    againBtn.addEventListener('click', function () {
      submitted = false;
      f.querySelectorAll('input:not([type=hidden]),textarea,select').forEach(function (el) {
        el.disabled = false; el.style.opacity = ''; el.style.cursor = '';
      });
      f.reset();
      clearDraft();
      Array.prototype.forEach.call(f.querySelectorAll('.ah-invalid'), function (el) {
        el.classList.remove('ah-invalid');
      });
      clearMsg();
      btt.textContent = '<?php echo esc_js( $submit_label ); ?>';
      sp.style.display = 'none';
      btn.style.opacity = ''; btn.style.cursor = '';
      btn.disabled = agr ? !agr.checked : false;
      againBtn.hidden = true;
      if (clearBtn) clearBtn.hidden = false;
      var bar2 = w.querySelector('.ah-steps-bar');
      if (bar2) bar2.hidden = false;
      applyConds();
      if (steps.length > 1) { showStep(0, true); } else { btn.hidden = false; }
      w.scrollIntoView({behavior:'smooth', block:'start'});
    });
  }

  // ── Phone: reveal a free-text dialling code when the country is not listed ──
  Array.prototype.forEach.call(w.querySelectorAll('.ah-tel'), function (tel) {
    var sel = tel.querySelector('.ah-tel-cc');
    var box = tel.querySelector('.ah-tel-other');
    if (!sel || !box) return;
    function sync() {
      var other = sel.value === 'other';
      box.hidden = !other;
      if (other) { box.focus(); } else { box.value = ''; }
    }
    sel.addEventListener('change', sync);
    sync();
  });

  // ── Spam challenge ────────────────────────────────────────────────────
  // v2/Turnstile widgets inject their own hidden input, so FormData picks the
  // token up. v3 has no widget - fetch a token just before sending.
  // ── Phone: reveal a free-text dialling code when the country is not listed ──
  Array.prototype.forEach.call(w.querySelectorAll('.ah-tel'), function (tel) {
    var sel = tel.querySelector('.ah-tel-cc');
    var box = tel.querySelector('.ah-tel-other');
    if (!sel || !box) return;
    function sync() {
      var other = sel.value === 'other';
      box.hidden = !other;
      if (other) { box.focus(); } else { box.value = ''; }
    }
    sel.addEventListener('change', sync);
    sync();
  });

  var CAP = <?php echo wp_json_encode( $cap_on ? array(
    'p' => $cap['provider'],
    'k' => $cap['site_key'],
    'f' => self::captcha_token_field( $cap['provider'] ),
  ) : null ); ?>;
  function withCaptcha(done) {
    if (!CAP || CAP.p !== 'recaptcha_v3' || typeof grecaptcha === 'undefined') { done(); return; }
    try {
      grecaptcha.ready(function () {
        grecaptcha.execute(CAP.k, {action: 'ah_form'}).then(function (tok) {
          var el = f.querySelector('input[name="' + CAP.f + '"]');
          if (!el) {
            el = document.createElement('input');
            el.type = 'hidden'; el.name = CAP.f;
            f.appendChild(el);
          }
          el.value = tok;
          done();
        }).catch(function () { done(); }); // let the server decide
      });
    } catch (err) { done(); }
  }
  function resetCaptcha() {
    try {
      if (CAP && CAP.p === 'recaptcha_v2' && typeof grecaptcha !== 'undefined') { grecaptcha.reset(); }
      if (CAP && CAP.p === 'turnstile' && typeof turnstile !== 'undefined') { turnstile.reset(); }
    } catch (err) {}
  }

  f.addEventListener('submit', function(e) {
    e.preventDefault();
    // Guard the whole form, not just the visible step - flag() jumps back to the
    // step that owns the offending field.
    if (!validate(f)) return;
    btn.disabled = true; btt.textContent = 'Sending…'; sp.style.display = 'inline-block';
    withCaptcha(function () {
    // FormData is posted as-is: it keeps every value of a repeated name (checkbox
    // groups) and carries file inputs, both of which a flattened object loses.
    var payload = new FormData(f);
    payload.append('action', 'ah_form_submit');
    fetch('<?php echo esc_js( $ajax ); ?>', { method: 'POST', body: payload })
    .then(function(r){ return r.json(); })
    .then(function(r){
      if (r.success) {
        msg(sc, 'success', r.data.message);
        f.reset();
        f.querySelectorAll('input:not([type=hidden]),textarea,select').forEach(function(el){ el.disabled = true; el.style.opacity = '0.5'; el.style.cursor = 'not-allowed'; });
        btn.disabled = true; btt.textContent = 'Sent'; sp.style.display = 'none'; btn.style.opacity = '0.55'; btn.style.cursor = 'not-allowed';
        submitted = true; // stop applyConds() re-enabling the locked-down inputs
        clearDraft(); // the answers are stored server-side now
        if (prev) prev.hidden = true;
        if (next) next.hidden = true;
        if (clearBtn) clearBtn.hidden = true;
        if (againBtn) againBtn.hidden = false; // let them file a second enquiry
        var bar = w.querySelector('.ah-steps-bar');
        if (bar) bar.hidden = true;
      } else {
        msg(ec, 'error', r.data && r.data.message ? r.data.message : 'Something went wrong.');
        resetCaptcha(); // a used token cannot be replayed
        btn.disabled = agr ? !agr.checked : false; btt.textContent = '<?php echo esc_js( $submit_label ); ?>'; sp.style.display = 'none';
      }
    })
    .catch(function(){
      msg(ec, 'error', 'Network error. Please try again.');
      resetCaptcha();
      btn.disabled = agr ? !agr.checked : false; btt.textContent = '<?php echo esc_js( $submit_label ); ?>'; sp.style.display = 'none';
    });
    }); // withCaptcha
  });
})();
</script>
<?php
// Provider script last, so the widgets it looks for already exist in the DOM.
if ( $cap_on ) :
	$cap_src = ( 'turnstile' === $cap['provider'] )
		? 'https://challenges.cloudflare.com/turnstile/v0/api.js'
		: ( 'recaptcha_v3' === $cap['provider']
			? 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $cap['site_key'] )
			: 'https://www.google.com/recaptcha/api.js' );
	?>
<script src="<?php echo esc_url( $cap_src ); ?>" async defer></script>
<?php endif; ?>
<?php if ( ! empty( $form->custom_js ) ) : ?>
<script id="ah-form-<?php echo (int) $form_id; ?>-js">
(function(){
<?php echo $form->custom_js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted, manage_options-only input, same model as AH_Custom_Code_Service. ?>
})();
</script>
<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single input field.
	 *
	 * Extracted from render() so a field can be emitted from anywhere in the
	 * step/group tree without duplicating this markup. Returns escaped HTML.
	 */
	private static function render_field( object $f, string $uid, array $valid_keys = array() ): string {
		if ( 'hidden' === $f->field_type ) {
			return '<input type="hidden" name="' . esc_attr( $f->field_key ) . '" value="' . esc_attr( (string) $f->placeholder ) . '">';
		}
		$fid   = esc_attr( $uid . '_' . $f->field_key );
		$fname = esc_attr( $f->field_key );
		$fph   = esc_attr( (string) $f->placeholder );
		$freq  = $f->is_required;
		$fdesc = isset( $f->description ) ? trim( (string) $f->description ) : '';
		$fset  = is_array( $f->settings ?? null ) ? $f->settings : array();
		// Custom class from the builder lands on the field wrapper, so CSS can target
		// the label, input and description together.
		$fcls  = trim( self::width_class( $fset ) . ' ' . self::css_class( $fset ) );
		$fcond = self::cond_attrs( $fset, $valid_keys );

		$fpre  = isset( $fset['prefix'] ) ? (string) $fset['prefix'] : '';
		$fsuf  = isset( $fset['suffix'] ) ? (string) $fset['suffix'] : '';
		$fintl = ! empty( $fset['intl'] ) && 'tel' === $f->field_type;
		$fcc   = isset( $fset['intl_cc'] ) ? (string) $fset['intl_cc'] : '+44';
		$flayout = ( isset( $fset['layout'] ) && in_array( $fset['layout'], self::CHOICE_LAYOUTS, true ) ) ? (string) $fset['layout'] : 'list';
		$ftile   = ( 'tiles' === $flayout );
		// pills and cards share one renderer - they differ only by CSS class.
		$fchip   = in_array( $flayout, array( 'pills', 'cards', 'checks' ), true );
		$ficon = isset( $fset['icon'] ) ? (string) $fset['icon'] : '';

		// One input, reused by the plain / affix / phone branches below.
		/*
		 * Default value. One string in the settings; for a multi-select checkbox
		 * it is a comma-separated list, so one field in the builder covers every
		 * type. Compared against the option VALUE (the part before "|").
		 */
		$fdef  = isset( $fset['default'] ) ? (string) $fset['default'] : '';
		$fdefs = ( '' === $fdef ) ? array() : array_map( 'trim', explode( ',', $fdef ) );
		$is_def = static function ( $value ) use ( $fdefs ) {
			return in_array( (string) $value, $fdefs, true ) ? ' checked' : '';
		};

		$input_html = '<input class="ch-form-input" type="' . esc_attr( $f->field_type ) . '" id="' . $fid
			. '" name="' . $fname . '" placeholder="' . $fph . '"'
			. ( '' !== $fdef ? ' value="' . esc_attr( $fdef ) . '"' : '' )
			. ( $freq ? ' required' : '' ) . '>';

		ob_start();
		?>
    <div class="ch-form-group<?php echo $fcls ? ' ' . esc_attr( $fcls ) : ''; ?>"<?php echo $fcond; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_attr(). ?>>
      <?php if ( 'markup' === $f->field_type ) : ?>
        <?php if ( $fdesc || $ficon ) : ?><div class="ch-form-markup" style="font-size:14px;color:#4b5563;line-height:1.6;margin-bottom:5px;"><?php if ( $ficon ) : ?><span class="ahf-ico-inline"><?php echo self::icon_svg( $ficon, 'ahf-ico-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text. ?></span><?php endif; ?><?php echo wp_kses_post( $fdesc ); ?></div><?php endif; ?>
      <?php else : ?>
        <label class="ch-form-label" for="<?php echo $fid; ?>"><?php if ( $ficon ) : ?><span class="ahf-ico-inline"><?php echo self::icon_svg( $ficon, 'ahf-ico-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text. ?></span><?php endif; ?><?php echo esc_html( $f->label ); ?><?php if ( $freq ) : ?><span class="ah-req">*</span><?php endif; ?></label>
        <?php if ( 'textarea' === $f->field_type ) : ?>
          <textarea class="ch-form-textarea" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>" placeholder="<?php echo $fph; ?>"<?php echo $freq ? ' required' : ''; ?>><?php echo esc_textarea( $fdef ); ?></textarea>
        <?php elseif ( 'select' === $f->field_type && ! empty( $f->options ) ) : ?>
          <select class="ch-form-select" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>"<?php echo $freq ? ' required' : ''; ?>>
            <option value=""><?php echo esc_html( $f->placeholder ?: '- Select an option -' ); ?></option>
            <?php foreach ( $f->options as $opt ) : $po = self::parse_option( $opt ); ?><option value="<?php echo esc_attr( $po['value'] ); ?>"<?php echo in_array( (string) $po['value'], $fdefs, true ) ? ' selected' : ''; ?>><?php echo esc_html( $po['label'] ); ?></option><?php endforeach; ?>
          </select>
        <?php elseif ( $ftile && in_array( $f->field_type, array( 'radio', 'checkbox' ), true ) && ! empty( $f->options ) ) : ?>
          <?php $t_type = ( 'radio' === $f->field_type ) ? 'radio' : 'checkbox'; ?>
          <div class="ah-tiles">
            <?php foreach ( $f->options as $idx => $opt ) : $po = self::parse_option( $opt, true ); ?>
              <label class="ah-tile">
                <input type="<?php echo esc_attr( $t_type ); ?>" name="<?php echo $fname; ?><?php echo 'checkbox' === $t_type ? '[]' : ''; ?>" value="<?php echo esc_attr( $po['value'] ); ?>"<?php echo $is_def( $po['value'] ); ?><?php echo ( $freq && 'radio' === $t_type ) ? ' required' : ''; ?><?php echo ( $freq && 'checkbox' === $t_type && 0 === $idx ) ? ' data-required-group="true"' : ''; ?>>
                <span class="ah-tile-in">
                  <?php if ( $po['icon'] ) : ?><span class="ah-tile-ico"><?php echo self::icon_svg( $po['icon'], 'ahf-ico' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text. ?></span><?php endif; ?>
                  <span class="ah-tile-lbl"><?php echo esc_html( $po['label'] ); ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php elseif ( $fchip && in_array( $f->field_type, array( 'radio', 'checkbox' ), true ) && ! empty( $f->options ) ) : ?>
          <?php $c_type = ( 'radio' === $f->field_type ) ? 'radio' : 'checkbox'; ?>
          <div class="ah-chips ah-chips--<?php echo esc_attr( $flayout ); ?>">
            <?php foreach ( $f->options as $idx => $opt ) : $po = self::parse_option( $opt, true ); ?>
              <label class="ah-chip">
                <input type="<?php echo esc_attr( $c_type ); ?>" name="<?php echo $fname; ?><?php echo 'checkbox' === $c_type ? '[]' : ''; ?>" value="<?php echo esc_attr( $po['value'] ); ?>"<?php echo $is_def( $po['value'] ); ?><?php echo ( $freq && 'radio' === $c_type ) ? ' required' : ''; ?><?php echo ( $freq && 'checkbox' === $c_type && 0 === $idx ) ? ' data-required-group="true"' : ''; ?>>
                <span class="ah-chip-in">
                  <?php if ( $po['icon'] ) : ?><span class="ah-chip-ico"><?php echo self::icon_svg( $po['icon'], 'ahf-ico-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built-in SVG or escaped text. ?></span><?php endif; ?>
                  <span class="ah-chip-lbl"><?php echo esc_html( $po['label'] ); ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php elseif ( 'radio' === $f->field_type && ! empty( $f->options ) ) : ?>
          <div class="ch-form-radio-group">
            <?php foreach ( $f->options as $opt ) : $po = self::parse_option( $opt ); ?>
              <label>
                <input type="radio" name="<?php echo $fname; ?>" value="<?php echo esc_attr( $po['value'] ); ?>"<?php echo $is_def( $po['value'] ); ?><?php echo $freq ? ' required' : ''; ?>>
                <?php echo esc_html( $po['label'] ); ?>
              </label>
            <?php endforeach; ?>
          </div>
        <?php elseif ( 'checkbox' === $f->field_type && ! empty( $f->options ) ) : ?>
          <div class="ch-form-checkbox-group">
            <?php foreach ( $f->options as $idx => $opt ) : $po = self::parse_option( $opt ); ?>
              <label>
                <input type="checkbox" name="<?php echo $fname; ?>[]" value="<?php echo esc_attr( $po['value'] ); ?>"<?php echo $is_def( $po['value'] ); ?><?php echo ( $freq && $idx === 0 ) ? ' data-required-group="true"' : ''; ?>>
                <?php echo esc_html( $po['label'] ); ?>
              </label>
            <?php endforeach; ?>
          </div>
        <?php elseif ( 'daterange' === $f->field_type ) : ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <div style="flex:1;min-width:140px;">
              <span style="display:block;font-size:12px;color:#6b7280;margin-bottom:4px;">Start Date</span>
              <input class="ch-form-input" type="date" id="<?php echo $fid; ?>_start" name="<?php echo $fname; ?>_start" <?php echo $freq ? ' required' : ''; ?>>
            </div>
            <div style="flex:1;min-width:140px;">
              <span style="display:block;font-size:12px;color:#6b7280;margin-bottom:4px;">End Date</span>
              <input class="ch-form-input" type="date" id="<?php echo $fid; ?>_end" name="<?php echo $fname; ?>_end" <?php echo $freq ? ' required' : ''; ?>>
            </div>
          </div>
        <?php elseif ( 'color' === $f->field_type ) : ?>
          <div style="display:flex;align-items:center;gap:10px;">
            <input type="color" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>" <?php echo $freq ? ' required' : ''; ?> style="width:44px;height:44px;padding:2px;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">
            <span style="font-size:13px;color:#6b7280;"><?php echo esc_html( $f->placeholder ?: 'Select a color' ); ?></span>
          </div>
        <?php elseif ( in_array( $f->field_type, array( 'select', 'radio', 'checkbox' ), true ) ) : ?>
          <?php /* Options-based type with no options saved - render nothing rather than a
             broken lone <input type="radio"/checkbox/select"> with no choices. Re-open this
             field in the Form Builder and add its options to fix it properly. */ ?>
          <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <p class="ch-form-desc" style="color:#b91c1c;">⚠ "<?php echo esc_html( $f->label ); ?>" has no options configured - edit this field in the Form Builder.</p>
          <?php endif; ?>
        <?php elseif ( 'file' === $f->field_type ) : ?>
          <?php
          $u_exts = self::field_exts( $fset );
          $u_max  = self::field_max_mb( $fset );
          $u_acc  = '.' . implode( ',.', $u_exts );
          ?>
          <input class="ch-form-input ah-file" type="file" id="<?php echo $fid; ?>" name="<?php echo $fname; ?>"
                 accept="<?php echo esc_attr( $u_acc ); ?>" data-maxmb="<?php echo (int) $u_max; ?>"<?php echo $freq ? ' required' : ''; ?>>
          <p class="ch-form-desc">Up to <?php echo (int) $u_max; ?> MB &middot; <?php echo esc_html( strtoupper( implode( ', ', $u_exts ) ) ); ?></p>
        <?php elseif ( $fintl ) : ?>
          <div class="ah-tel">
            <select class="ah-tel-cc" name="<?php echo $fname; ?>_cc" aria-label="Country dialling code">
              <?php foreach ( self::dial_codes() as $iso => $dial ) : ?>
                <?php // The flag is a regional-indicator pair, so platforms without flag
                      // glyphs (Windows) already render it as "GB" - adding the ISO code
                      // as well would read "GB GB +44". ?>
                <option value="<?php echo esc_attr( $dial ); ?>"<?php selected( $dial, $fcc ); ?>><?php echo esc_html( trim( self::flag_emoji( $iso ) . ' ' . $dial ) ); ?></option>
              <?php endforeach; ?>
              <?php // Last resort for a country the list does not cover. ?>
              <option value="other">Other&hellip;</option>
            </select>
            <input class="ah-tel-other" type="text" name="<?php echo $fname; ?>_cc_custom" inputmode="tel"
                   placeholder="+000" maxlength="5" aria-label="Your country dialling code" hidden>
            <?php echo $input_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts. ?>
          </div>
        <?php elseif ( '' !== $fpre || '' !== $fsuf ) : ?>
          <div class="ah-affix">
            <?php if ( '' !== $fpre ) : ?><span class="ah-affix-txt"><?php echo esc_html( $fpre ); ?></span><?php endif; ?>
            <?php echo $input_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts. ?>
            <?php if ( '' !== $fsuf ) : ?><span class="ah-affix-txt suf"><?php echo esc_html( $fsuf ); ?></span><?php endif; ?>
          </div>
        <?php else : ?>
          <?php echo $input_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts. ?>
        <?php endif; ?>
        <?php if ( $fdesc ) : ?><p class="ch-form-desc"><?php echo wp_kses_post( $fdesc ); ?></p><?php endif; ?>
      <?php endif; ?>
    </div>
		<?php
		return (string) ob_get_clean();
	}

	// ── Agreement config (form-level, stored in wp_option) ──────────────────

	/**
	 * How the multi-step header is laid out. Stored per form as an option, the
	 * same way the agreement block is - no schema change, and a form that has
	 * never been saved since this shipped simply gets the original 'bar'.
	 *
	 *   split  step title on the left, chips on the right (DEFAULT)
	 *   bar    progress chips across the top, step title underneath
	 *   plain  no chips at all - just "Step n of m" and the title
	 */
	const HEADER_STYLES  = array( 'split', 'bar', 'plain' );
	const HEADER_DEFAULT = 'split';

	public static function get_header_style( int $form_id ): string {
		$saved = get_option( 'ah_form_head_' . $form_id, self::HEADER_DEFAULT );
		return in_array( $saved, self::HEADER_STYLES, true ) ? $saved : self::HEADER_DEFAULT;
	}

	public static function save_header_style( int $form_id, string $style ): void {
		update_option(
			'ah_form_head_' . $form_id,
			in_array( $style, self::HEADER_STYLES, true ) ? $style : self::HEADER_DEFAULT
		);
	}

	public static function get_agreement( int $form_id ): array {
		$defaults = array(
			'enabled'    => 0,
			'before'     => 'I have read and agree to the',
			'link_text'  => 'Terms & Conditions',
			'type'       => 'link',
			'url'        => '',
			'after'      => '',
			'popup_html' => '',
		);
		$saved = get_option( 'ah_form_agr_' . $form_id, array() );
		return array_merge( $defaults, is_array( $saved ) ? $saved : array() );
	}

	public static function save_agreement( int $form_id, array $data ): void {
		$clean = array(
			'enabled'    => ! empty( $data['enabled'] ) ? 1 : 0,
			'before'     => sanitize_text_field( isset( $data['before'] )    ? $data['before']    : 'I have read and agree to the' ),
			'link_text'  => sanitize_text_field( isset( $data['link_text'] ) ? $data['link_text'] : 'Terms & Conditions' ),
			'type'       => in_array( isset( $data['type'] ) ? $data['type'] : '', array( 'link', 'iframe', 'popup' ), true ) ? $data['type'] : 'link',
			'url'        => esc_url_raw( isset( $data['url'] )        ? $data['url']        : '' ),
			'after'      => sanitize_text_field( isset( $data['after'] )     ? $data['after']     : '' ),
			'popup_html' => wp_kses_post( isset( $data['popup_html'] ) ? $data['popup_html'] : '' ),
		);
		update_option( 'ah_form_agr_' . $form_id, $clean );
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	public static function to_key( string $label ): string {
		return str_replace( '-', '_', sanitize_title( $label ) );
	}

	public static function allowed_type( string $t ): string {
		return in_array( $t, array( 'text', 'email', 'tel', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'daterange', 'color', 'url', 'hidden', 'markup', 'file', 'step', 'fieldset' ), true ) ? $t : 'text';
	}

	/**
	 * A saved select/radio/checkbox option is either a plain string (value === label)
	 * or "value|Label" so the submitted value can stay clean/simple while the
	 * displayed label carries spaces, punctuation, or emoji. Splits on the first "|"
	 * only, so a label that itself contains "|" still works.
	 */
	public static function parse_option( string $raw, bool $with_icon = false ): array {
		$raw  = trim( $raw );
		$icon = '';

		// Only the icon-carrying layouts (tiles / pills / cards) read a third
		// segment, so list-mode labels containing "|" keep behaving exactly as
		// they always have.
		if ( $with_icon && false !== strpos( $raw, '|' ) ) {
			$bits = explode( '|', $raw, 3 );
			if ( 3 === count( $bits ) ) {
				$icon = trim( $bits[2] );
				$raw  = trim( $bits[0] ) . '|' . trim( $bits[1] );
			}
		}

		if ( false !== strpos( $raw, '|' ) ) {
			list( $value, $label ) = explode( '|', $raw, 2 );
			$value = trim( $value );
			$label = trim( $label );
			if ( '' !== $value ) {
				return array( 'value' => $value, 'label' => ( '' !== $label ) ? $label : $value, 'icon' => $icon );
			}
		}
		return array( 'value' => $raw, 'label' => $raw, 'icon' => $icon );
	}

	public static function field_type_label( string $type ): string {
		$map = array(
			'text'      => 'Text',
			'email'     => 'Email',
			'tel'       => 'Phone / Tel',
			'textarea'  => 'Textarea',
			'select'    => 'Dropdown',
			'radio'     => 'Radio Buttons',
			'checkbox'  => 'Checkboxes',
			'number'    => 'Number',
			'date'      => 'Date',
			'daterange' => 'Date Range',
			'color'     => 'Color Picker',
			'url'       => 'URL',
			'hidden'    => 'Hidden Field',
			'markup'    => 'Markup / Instructions',
			'file'      => 'File Upload',
			'step'      => 'Step / Page Break',
			'fieldset'  => 'Field Group',
		);
		return $map[ $type ] ?? $type;
	}
}
