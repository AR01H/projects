<?php
/**
 * src/Dialogs/class-alert.php
 *
 * FEATURE: Alerts & site notices
 * ------------------------------
 * The quiet half of the dialog system: messages that sit IN the page rather
 * than over it.
 *
 *   - Inline alert   nt_alert( array( 'tone' => 'warning', 'body' => '…' ) )
 *                    A framed parchment note used inside any section or form.
 *   - Site notices   the strip under the header, driven by
 *                    admin/data/site_notices.json - scheduled, per-page and
 *                    dismissible, with the dismissal remembered per visitor.
 *
 * Every string comes from JSON; this class only decides which notices are
 * eligible right now and hands a clean context to the dumb templates
 * components/parts/alert.php and components/notice-bar.php.
 *
 * A notice entry (admin/data/site_notices.json):
 *
 *   {
 *     "id": "launch",              // stable id - the dismissal is stored per id
 *     "tone": "info",              // NT_Ui::TONES
 *     "title": "…",
 *     "message": "…",
 *     "button_label": "…",
 *     "button_url": "/about/",
 *     "pages": ["home","about"],   // omit / "all" = every page
 *     "starts": "2026-08-01",      // optional schedule (site timezone)
 *     "ends":   "2026-09-30",
 *     "dismissible": true,
 *     "status": "active"
 *   }
 *
 * @package NT\Dialogs
 */

defined( 'ABSPATH' ) || exit;

class NT_Alert {

	/**
	 * JSON registry for the notice strip (admin/data/<DATA_KEY>.json).
	 */
	public const DATA_KEY = 'site_notices';

	/**
	 * Render an inline alert box.
	 *
	 * Args:
	 *   tone        string  info|success|warning|error|note|question.
	 *   title       string  Bold first line.
	 *   body        string  Message text.
	 *   html        string  Trusted rich text (wp_kses_post) instead of body.
	 *   icon        string  NT_Icons name or emoji. Defaults to the tone icon.
	 *   link_label  string  Optional inline call to action.
	 *   link_url    string
	 *   dismissible bool    Adds the × (client-side only, not remembered).
	 *   compact     bool    Tighter padding for use inside cards/forms.
	 *   class       string  Extra class.
	 */
	public static function render( array $args ): void {
		if ( function_exists( 'nt_component' ) ) {
			nt_component( 'parts/alert', self::normalise( $args ) );
		}
	}

	/**
	 * Same as render() but returns the markup.
	 */
	public static function get( array $args ): string {
		ob_start();
		self::render( $args );
		return (string) ob_get_clean();
	}

	/**
	 * Fill in every key parts/alert.php reads, so the template needs no `??`.
	 *
	 * @return array
	 */
	public static function normalise( array $args ): array {
		$tone = NT_Ui::tone( $args['tone'] ?? '' );

		return array(
			'tone'        => $tone,
			'title'       => (string) ( $args['title'] ?? '' ),
			'body'        => (string) ( $args['body'] ?? $args['message'] ?? '' ),
			'html'        => (string) ( $args['html'] ?? '' ),
			'icon'        => (string) ( $args['icon'] ?? NT_Ui::tone_icon( $tone ) ),
			'link_label'  => (string) ( $args['link_label'] ?? '' ),
			'link_url'    => (string) ( $args['link_url'] ?? '' ),
			'dismissible' => ! empty( $args['dismissible'] ),
			'compact'     => ! empty( $args['compact'] ),
			'dismiss_id'  => (string) ( $args['dismiss_id'] ?? '' ),
			'class'       => (string) ( $args['class'] ?? '' ),
		);
	}

	/**
	 * Every site notice that should show on the page being rendered.
	 *
	 * Filters out: status != active, notices scheduled for another window, and
	 * notices restricted to other pages. Ordering follows the JSON file.
	 *
	 * @param string $page_key Current page key; defaults to the router's value.
	 * @return array<int,array>
	 */
	public static function notices( string $page_key = '' ): array {
		$raw = function_exists( 'nt_data' ) ? nt_data( self::DATA_KEY ) : array();
		if ( ! is_array( $raw ) ) {
			return array();
		}
		// Support both a flat array of notices and { items: [...] }.
		$items = ( isset( $raw['items'] ) && is_array( $raw['items'] ) ) ? $raw['items'] : $raw;

		if ( '' === $page_key ) {
			$page_key = (string) get_query_var( 'nt_active_page' );
		}
		$now = (int) current_time( 'timestamp' );

		$out = array();
		foreach ( $items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$status = strtolower( (string) ( $item['status'] ?? 'active' ) );
			if ( 'active' !== $status ) {
				continue;
			}
			if ( ! self::in_window( $item, $now ) ) {
				continue;
			}
			if ( ! self::matches_page( $item, $page_key ) ) {
				continue;
			}

			$message = trim( (string) ( $item['message'] ?? $item['text'] ?? '' ) );
			$title   = trim( (string) ( $item['title'] ?? '' ) );
			if ( '' === $message && '' === $title ) {
				continue;
			}

			$tone = NT_Ui::tone( $item['tone'] ?? 'info', 'info' );
			$out[] = array(
				'id'           => (string) ( $item['id'] ?? 'notice-' . $index ),
				'tone'         => $tone,
				'icon'         => (string) ( $item['icon'] ?? NT_Ui::tone_icon( $tone ) ),
				'badge'        => (string) ( $item['badge_text'] ?? '' ),
				'title'        => $title,
				'message'      => $message,
				'button_label' => (string) ( $item['button_label'] ?? '' ),
				'button_url'   => (string) ( $item['button_url'] ?? '' ),
				'dialog'       => (string) ( $item['dialog'] ?? '' ),
				'dismissible'  => ! isset( $item['dismissible'] ) || ! empty( $item['dismissible'] ),
			);
		}

		return $out;
	}

	/**
	 * Is "now" inside this notice's optional start/end window?
	 * Dates are read in the site timezone; a bad date is treated as absent.
	 */
	protected static function in_window( array $item, int $now ): bool {
		$starts = trim( (string) ( $item['starts'] ?? '' ) );
		$ends   = trim( (string) ( $item['ends'] ?? '' ) );

		if ( '' !== $starts ) {
			$from = strtotime( $starts );
			if ( $from && $now < $from ) {
				return false;
			}
		}
		if ( '' !== $ends ) {
			// An end date with no time means "to the end of that day".
			$to = strtotime( preg_match( '/\d:\d/', $ends ) ? $ends : $ends . ' 23:59:59' );
			if ( $to && $now > $to ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Does this notice apply to the current page? No `pages` key = everywhere.
	 */
	protected static function matches_page( array $item, string $page_key ): bool {
		$pages = $item['pages'] ?? ( $item['context'] ?? '' );
		if ( empty( $pages ) ) {
			return true;
		}
		$pages = array_map( 'strtolower', array_map( 'strval', (array) $pages ) );
		if ( in_array( 'all', $pages, true ) ) {
			return true;
		}
		return in_array( strtolower( $page_key ), $pages, true );
	}
}
