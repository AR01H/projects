<?php
/**
 * src/Content/class-blocks.php
 *
 * FEATURE: Shared content blocks
 * ------------------------------
 * ONE library of small reusable "say this, link there" blocks, so the same
 * wording never has to be typed twice.
 *
 * The problem it solves: a line like
 *
 *     Read the blog
 *     The latest on sugarcane, straight from the field.
 *     [ Read the stories ]  ->  /blog/
 *
 * is wanted in a nav dropdown, a footer column, a card on the home page, a
 * dialog and the sidebar of an article. Before, that meant the same three
 * strings copied into five JSON files, and five places to fix when the URL
 * changed. Now it is ONE entry in admin/data/blocks.json:
 *
 *   "read_blogs": {
 *     "title": "Read the blog",
 *     "text":  "The latest on sugarcane, straight from the field.",
 *     "label": "Read the stories",
 *     "url":   "/blog/",
 *     "icon":  "file"
 *   }
 *
 * …referenced anywhere by its KEY:
 *
 *   PHP        app_block( 'read_blogs' )                  -> the array
 *   Section    { "component": "promo-block", "args": { "block": "read_blogs" } }
 *   Any list   "blocks": [ "read_blogs", "visit_us" ]    (see self::many)
 *
 * Change the wording or the URL once, and every place that names the key
 * updates with it.
 *
 * @package NT\Content
 */

defined( 'ABSPATH' ) || exit;

class NT_Blocks {

	/**
	 * JSON library (admin/data/<DATA_KEY>.json). Keys are block ids.
	 */
	public const DATA_KEY = 'blocks';

	/**
	 * The whole library, minus the `_doc` note.
	 *
	 * @return array<string,array>
	 */
	public static function all(): array {
		static $blocks = null;
		if ( null !== $blocks ) {
			return $blocks;
		}
		$data = is_callable( array( 'App_Helpers', 'data' ) ) ? App_Data_Provider::get( self::DATA_KEY ) : array();
		$data = is_array( $data ) ? $data : array();
		unset( $data['_doc'] );

		$blocks = array();
		foreach ( $data as $key => $def ) {
			if ( is_array( $def ) ) {
				$blocks[ (string) $key ] = $def;
			}
		}
		return $blocks;
	}

	/**
	 * Is this key in the library?
	 */
	public static function exists( string $key ): bool {
		$all = self::all();
		return isset( $all[ $key ] );
	}

	/**
	 * One block, with every field filled in so callers never need `??`.
	 *
	 * Fields:
	 *   key      string  The id it was looked up by.
	 *   title    string  Heading line.
	 *   text     string  Supporting sentence.
	 *   label    string  Call-to-action wording ("Click here", "Read more").
	 *   url      string  Where the action goes.
	 *   dialog   string  A dialogs.json key - opens a dialog instead of a link.
	 *   icon     string  NT_Icons name or emoji.
	 *   image    string  Illustration path.
	 *   tag      string  Small caps kicker.
	 *   tone     string  Tone for blocks rendered as alerts.
	 *   new_tab  bool    Open the link in a new tab.
	 *
	 * @param string $key      Block id.
	 * @param array  $override Values that beat the JSON (per-placement tweaks).
	 * @return array Empty array when the key is unknown.
	 */
	public static function get( string $key, array $override = array() ): array {
		$all = self::all();
		if ( ! isset( $all[ $key ] ) ) {
			return array();
		}
		$def = array_merge( $all[ $key ], $override );

		return array(
			'key'     => $key,
			'title'   => (string) ( $def['title'] ?? '' ),
			'text'    => (string) ( $def['text'] ?? $def['description'] ?? '' ),
			'label'   => (string) ( $def['label'] ?? '' ),
			'url'     => (string) ( $def['url'] ?? '' ),
			'dialog'  => (string) ( $def['dialog'] ?? '' ),
			'icon'    => (string) ( $def['icon'] ?? '' ),
			'image'   => (string) ( $def['image'] ?? '' ),
			'tag'     => (string) ( $def['tag'] ?? '' ),
			'tone'    => NT_Ui::tone( $def['tone'] ?? 'note' ),
			'new_tab' => ! empty( $def['new_tab'] ),
		);
	}

	/**
	 * Several blocks at once, skipping unknown keys.
	 *
	 * Accepts a list of key strings, or a list of arrays each holding a `block`
	 * key plus per-placement overrides:
	 *
	 *   [ "read_blogs", { "block": "visit_us", "label": "Find the counter" } ]
	 *
	 * @param array $keys
	 * @return array<int,array>
	 */
	public static function many( array $keys ): array {
		$out = array();
		foreach ( $keys as $entry ) {
			if ( is_string( $entry ) ) {
				$block = self::get( $entry );
			} elseif ( is_array( $entry ) && ! empty( $entry['block'] ) ) {
				$ref = (string) $entry['block'];
				unset( $entry['block'] );
				$block = self::get( $ref, $entry );
			} else {
				continue;
			}
			if ( ! empty( $block ) ) {
				$out[] = $block;
			}
		}
		return $out;
	}

	/**
	 * Resolve whatever a component was handed into a list of blocks.
	 *
	 * A component may receive `block` (one key), `blocks` (a list) or nothing
	 * at all - this turns all three into the same array so section components
	 * stay two lines long.
	 *
	 * @param mixed $block  A single key, or an array of overrides with `block`.
	 * @param mixed $blocks A list of keys / override arrays.
	 * @return array<int,array>
	 */
	public static function resolve( $block = null, $blocks = null ): array {
		if ( ! empty( $blocks ) && is_array( $blocks ) ) {
			return self::many( $blocks );
		}
		if ( ! empty( $block ) ) {
			return self::many( array( $block ) );
		}
		return array();
	}
}
