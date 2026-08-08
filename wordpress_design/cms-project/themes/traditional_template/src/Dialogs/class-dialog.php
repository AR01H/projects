<?php
/**
 * src/Dialogs/class-dialog.php
 *
 * FEATURE: Dialogs  (data on the server, rendering in the browser)
 * ----------------------------------------------------------------
 * ONE dialog system for the whole site. A dialog is DATA, declared once in
 * admin/data/dialogs.json:
 *
 *   "brochure": {
 *     "title":  "Download the brochure",
 *     "tone":   "info",
 *     "body":   "We'll email you a copy.",
 *     "form":   "form_brochure",          // any admin/data/<form>.json
 *     "actions": [ { "label": "…", "url": "/…", "style": "primary" } ]
 *   }
 *
 * …and opened from anywhere with no PHP markup at all:
 *
 *   <button <?php app_dialog_trigger( 'brochure' ); ?>>Get the brochure</button>
 *
 * WHERE THE MARKUP COMES FROM: nowhere on the server. This class only
 * validates the data and ships it to the page as `window.ntUi.dialogs`;
 * assets/js/ui-kit.js (NTDialogView) builds the DOM the moment a dialog is
 * actually opened. That means:
 *   - zero dialog HTML in the document for dialogs nobody opens,
 *   - one renderer instead of a PHP one and a JS one that must be kept in
 *     step,
 *   - a dialog can equally be created at runtime from an AJAX response,
 *     using the exact same shape (NT.dialog.show({ … })).
 *
 * A trigger only ships a dialog's data if something on the page references
 * it, so a page that never mentions "brochure" carries none of its payload.
 *
 * @package NT\Dialogs
 */

defined( 'ABSPATH' ) || exit;

class NT_Dialog {

	/**
	 * JSON registry (admin/data/<DATA_KEY>.json). Keys are dialog ids.
	 */
	public const DATA_KEY = 'dialogs';

	/**
	 * Prefix for the DOM id JS gives the built element, so a dialog declared
	 * as "brochure" becomes #app-dialog-brochure and can never collide with a
	 * section id.
	 */
	public const ID_PREFIX = 'app-dialog-';

	/**
	 * Dialog ids referenced by this page, so only their data is shipped.
	 *
	 * @var array<string,bool>
	 */
	protected static $queued = array();

	/**
	 * Ad-hoc dialogs built by a component from its own data (one per job
	 * opening, say) rather than declared in dialogs.json.
	 *
	 * @var array<string,array>
	 */
	protected static $inline = array();

	/**
	 * The whole dialogs registry, minus the `_doc` note.
	 *
	 * @return array<string,array>
	 */
	public static function registry(): array {
		static $registry = null;
		if ( null !== $registry ) {
			return $registry;
		}
		$data = function_exists( 'app_data' ) ? app_data( self::DATA_KEY ) : array();
		$data = is_array( $data ) ? $data : array();
		unset( $data['_doc'] );

		$registry = array();
		foreach ( $data as $id => $def ) {
			if ( is_array( $def ) ) {
				$registry[ (string) $id ] = $def;
			}
		}
		return $registry;
	}

	/**
	 * Is this id declared in the registry (or registered inline this request)?
	 */
	public static function exists( string $id ): bool {
		$registry = self::registry();
		return isset( $registry[ $id ] ) || isset( self::$inline[ $id ] );
	}

	/**
	 * The DOM id JS will give this dialog.
	 */
	public static function dom_id( string $id ): string {
		return self::ID_PREFIX . sanitize_html_class( $id );
	}

	/**
	 * Mark a declared dialog as used on this page, so its data is shipped.
	 *
	 * @return bool True when the id exists (so callers can skip their trigger).
	 */
	public static function queue( string $id ): bool {
		if ( ! self::exists( $id ) ) {
			return false;
		}
		self::$queued[ $id ] = true;
		return true;
	}

	/**
	 * Register a dialog that is not in dialogs.json - built by a component
	 * out of its own content, then opened by id like any other.
	 *
	 *   NT_Dialog::add( 'apply-driver', array( 'title' => …, 'form' => … ) );
	 *
	 * @param string $id  Unique id for this request.
	 * @param array  $def Same shape as a dialogs.json entry.
	 */
	public static function add( string $id, array $def ): string {
		self::$inline[ $id ] = $def;
		self::$queued[ $id ] = true;
		return self::dom_id( $id );
	}

	/**
	 * The attributes a trigger element needs, as an echo-ready string, which
	 * also marks the dialog as used.
	 *
	 *   <button <?php echo NT_Dialog::trigger_attrs( 'brochure' ); ?>>…</button>
	 *
	 * Returns '' for an unknown id, so a typo in JSON degrades to a plain
	 * button rather than a button that opens nothing.
	 */
	public static function trigger_attrs( string $id ): string {
		if ( ! self::queue( $id ) ) {
			return '';
		}
		return 'type="button" data-nt-dialog-open="' . esc_attr( self::dom_id( $id ) ) . '"'
			. ' aria-haspopup="dialog"';
	}

	/**
	 * Every dialog this page needs, keyed by DOM id and ready for JS.
	 *
	 * Called by NT_Ui::js_config() at footer time - after all the sections
	 * have rendered, so the queue is complete.
	 *
	 * @return array<string,array>
	 */
	public static function js_dialogs(): array {
		$registry = self::registry();

		// "global": true means "available on every page" - a welcome
		// announcement, say, with no button anywhere to open it.
		foreach ( $registry as $id => $def ) {
			if ( ! empty( $def['global'] ) ) {
				self::$queued[ $id ] = true;
			}
		}

		$out = array();
		foreach ( array_keys( self::$queued ) as $id ) {
			$def = self::$inline[ $id ] ?? ( $registry[ $id ] ?? null );
			if ( is_array( $def ) ) {
				$out[ self::dom_id( $id ) ] = self::normalise( $id, $def );
			}
		}
		return $out;
	}

	/**
	 * Turn a raw JSON entry into the exact object assets/js/ui-kit.js expects.
	 *
	 * Accepted keys (all optional):
	 *   title       string  Heading.
	 *   kicker      string  Small caps line above the heading.
	 *   tone        string  info|success|warning|error|note|question.
	 *   icon        string  NT_Icons name or emoji; defaults to the tone icon.
	 *   size        string  sm|md|lg|full.
	 *   body        string|array  Paragraph(s).
	 *   image       string  Illustration shown above the body.
	 *   list        array   Ticked bullet points.
	 *   form        string  admin/data/<form>.json - resolved here and shipped
	 *                       as field data, so JS builds a real, working form.
	 *   actions     array   [{ label, url, style, dialog_close, new_tab, action }]
	 *   dismissible bool    Show the × and allow backdrop/Esc close. Default true.
	 *   auto_open   int     Milliseconds after load to open by itself (0 = never).
	 *   once        string  Storage key - auto_open then fires once per visitor.
	 *   class       string  Extra class on the dialog element.
	 *
	 * @return array
	 */
	public static function normalise( string $id, array $def ): array {
		$tone = NT_Ui::tone( $def['tone'] ?? '' );

		$body = $def['body'] ?? '';
		$body = is_array( $body ) ? array_map( 'strval', $body ) : array_filter( array( (string) $body ) );

		$actions = array();
		foreach ( (array) ( $def['actions'] ?? array() ) as $action ) {
			$action = (array) $action;
			$label  = trim( (string) ( $action['label'] ?? '' ) );
			if ( '' === $label ) {
				continue;
			}
			$actions[] = array(
				'label'        => $label,
				'url'          => '' !== (string) ( $action['url'] ?? '' ) ? app_link( (string) $action['url'] ) : '',
				'style'        => in_array( (string) ( $action['style'] ?? 'primary' ), array( 'primary', 'ghost', 'danger' ), true )
					? (string) $action['style'] : 'primary',
				'new_tab'      => ! empty( $action['new_tab'] ),
				'dialog_close' => ! empty( $action['dialog_close'] ),
				'action'       => (string) ( $action['action'] ?? '' ),
				'value'        => (string) ( $action['value'] ?? 'ok' ),
			);
		}

		return array(
			'id'          => self::dom_id( $id ),
			'key'         => $id,
			'title'       => (string) ( $def['title'] ?? '' ),
			'kicker'      => (string) ( $def['kicker'] ?? '' ),
			'tone'        => $tone,
			'icon'        => (string) ( $def['icon'] ?? NT_Ui::tone_icon( $tone ) ),
			'size'        => NT_Ui::size( $def['size'] ?? '' ),
			'body'        => array_values( $body ),
			'image'       => '' !== (string) ( $def['image'] ?? '' ) ? app_link( (string) $def['image'] ) : '',
			'image_alt'   => (string) ( $def['image_alt'] ?? '' ),
			'list'        => array_values( array_filter( array_map( 'strval', (array) ( $def['list'] ?? array() ) ) ) ),
			'form'        => self::form_data( (string) ( $def['form'] ?? '' ) ),
			'actions'     => $actions,
			'dismissible' => ! isset( $def['dismissible'] ) || ! empty( $def['dismissible'] ),
			'auto_open'   => max( 0, (int) ( $def['auto_open'] ?? 0 ) ),
			'once'        => (string) ( $def['once'] ?? '' ),
			'class'       => (string) ( $def['class'] ?? '' ),
		);
	}

	/**
	 * Resolve a form name into the field data JS needs to build it.
	 *
	 * The SAME admin/data/form_*.json files the inline generic-form component
	 * reads, so a form can be shown in the page on one route and in a dialog
	 * on another with one definition.
	 *
	 * @param string $name Form key, or '' for none.
	 * @return array Empty when there is no form.
	 */
	protected static function form_data( string $name ): array {
		if ( '' === $name || ! function_exists( 'app_data' ) ) {
			return array();
		}
		$form = app_data( $name );
		if ( empty( $form ) || ! is_array( $form ) ) {
			return array();
		}

		$fields = array();
		foreach ( (array) ( $form['fields'] ?? array() ) as $index => $field ) {
			$field = (array) $field;
			$fid   = (string) ( $field['id'] ?? ( 'f-' . $index ) );
			$fields[] = array(
				'type'        => (string) ( $field['type'] ?? 'text' ),
				'id'          => $fid,
				'name'        => (string) ( $field['name'] ?? $fid ),
				'label'       => (string) ( $field['label'] ?? '' ),
				'placeholder' => (string) ( $field['placeholder'] ?? '' ),
				'required'    => ! empty( $field['required'] ),
				'options'     => is_array( $field['options'] ?? null ) ? $field['options'] : array(),
			);
		}

		return array(
			'id'     => (string) ( $form['id'] ?? 'app-form-' . sanitize_html_class( $name ) ),
			'action' => (string) ( $form['action'] ?? '' ),
			'submit' => (string) ( $form['submit'] ?? NT_Ui::label( 'submit' ) ),
			'class'  => (string) ( $form['class'] ?? '' ),
			'fields' => $fields,
		);
	}
}
