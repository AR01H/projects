<?php
namespace Ah\Cms\Admin\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Reusable builder-style UI components for drag-and-drop / expand-collapse interfaces.
 *
 * Used by navigation, footer, repeater-style admin pages.
 * Each method outputs HTML that matches the existing ah-builder-* CSS classes.
 */
class BuilderComponents {

	/**
	 * Admin box section (wrapper with heading + description).
	 */
	public static function box( string $title, string $note, string $content ): void {
		echo '<div class="ah-admin-box">';
		echo '<h2>' . esc_html( $title ) . '</h2>';
		if ( $note ) {
			echo '<p class="ah-builder-note">' . esc_html( $note ) . '</p>';
		}
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	/**
	 * Builder action bar (Expand All / Collapse All buttons).
	 */
	public static function actionBar(): void {
		echo '<div class="ah-builder-actions">';
		echo '<button type="button" class="button button-secondary" id="ah-expand-all">Expand All</button>';
		echo '<button type="button" class="button button-secondary" id="ah-collapse-all">Collapse All</button>';
		echo '</div>';
	}

	/**
	 * A single builder item (expand/collapse card with handle, title bar, body).
	 *
	 * @param string $title       Item title shown in the bar.
	 * @param string $body        Content inside the expandable body.
	 * @param string $kind        Data attribute kind (e.g., 'nav-item', 'footer-column').
	 * @param bool   $open        Whether expanded by default.
	 * @param string $extra_bar   Extra HTML in the bar (badges, etc).
	 */
	public static function item( string $title, string $body, string $kind = '', bool $open = true, string $extra_bar = '' ): void {
		$open_class = $open ? ' is-open' : '';
		$arrow      = $open ? '&#9662;' : '&#9656;'; // ▾ or ▸
		$hidden     = $open ? '' : ' style="display:none"';
		$kind_attr  = $kind ? ' data-kind="' . esc_attr( $kind ) . '"' : '';

		echo '<div class="ah-builder-item' . $open_class . '"' . $kind_attr . '>';
		echo '<div class="ah-builder-item__bar">';
		echo '<span class="ah-builder-handle" title="Drag to reorder">&#9776;</span>';
		echo '<strong>' . esc_html( $title ) . '</strong>';
		echo $extra_bar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<button type="button" class="ah-toggle-item" aria-expanded="' . ( $open ? 'true' : 'false' ) . '" aria-label="Toggle">' . $arrow . '</button>';
		echo '<button type="button" class="button-link-delete ah-remove-item">Remove</button>';
		echo '</div>';
		echo '<div class="ah-builder-item__body"' . $hidden . '>';
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '</div>';
	}

	/**
	 * A simple inline item (submenu link, footer link, legal link).
	 * Smaller than a full builder item — no toggle arrow, just handle + title + remove.
	 *
	 * @param string $title   Item title.
	 * @param string $body    Content inside the body.
	 * @param string $badge   Optional badge HTML (e.g., placement badge).
	 * @param bool   $open    Whether expanded by default.
	 */
	public static function inlineItem( string $title, string $body, string $badge = '', bool $open = true ): void {
		$open_class = $open ? ' is-open' : '';
		$hidden     = $open ? '' : ' style="display:none"';

		echo '<div class="ah-submenu-item' . $open_class . '">';
		echo '<div class="ah-builder-inline-head">';
		echo '<span class="ah-builder-handle">&#9776;</span>';
		echo '<strong>' . esc_html( $title ) . '</strong>';
		echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<button type="button" class="ah-toggle-subitem" aria-expanded="' . ( $open ? 'true' : 'false' ) . '">&#9662;</button>';
		echo '<button type="button" class="button-link-delete ah-remove-item">Remove</button>';
		echo '</div>';
		echo '<div class="ah-submenu-item__body"' . $hidden . '>';
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Builder grid (label + input pairs in a grid layout).
	 *
	 * @param array  $fields  Array of [label, input_html, help_text?, class?].
	 * @param string $extra_class  Additional CSS class for the grid wrapper.
	 */
	public static function grid( array $fields, string $extra_class = '' ): void {
		$class = 'ah-builder-grid' . ( $extra_class ? ' ' . $extra_class : '' );
		echo '<div class="' . esc_attr( $class ) . '">';
		foreach ( $fields as $field ) {
			$label     = $field[0] ?? '';
			$input     = $field[1] ?? '';
			$help      = $field[2] ?? '';
			$fclass    = $field[3] ?? '';
			echo '<label' . ( $fclass ? ' class="' . esc_attr( $fclass ) . '"' : '' ) . '>';
			echo '<span>' . esc_html( $label ) . '</span>';
			echo $input; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $help ) {
				echo '<small class="ah-field-help">' . wp_kses_post( $help ) . '</small>';
			}
			echo '</label>';
		}
		echo '</div>';
	}

	/**
	 * Builder field — a single label + input in a builder grid context.
	 */
	public static function field( string $name, string $label, string $value, string $type = 'text', array $opts = [] ): string {
		$ph   = ! empty( $opts['placeholder'] ) ? ' placeholder="' . esc_attr( $opts['placeholder'] ) . '"' : '';
		$class = ! empty( $opts['class'] ) ? ' ' . esc_attr( $opts['class'] ) : '';
		$html  = '';

		switch ( $type ) {
			case 'textarea':
				$rows = $opts['rows'] ?? 4;
				$html = '<textarea name="' . esc_attr( $name ) . '" rows="' . esc_attr( $rows ) . '" class="large-text' . $class . '">' . esc_textarea( $value ) . '</textarea>';
				break;
			case 'checkbox':
				$checked = ! empty( $value ) ? ' checked' : '';
				$html = '<label class="ah-checkbox-field"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . $checked . '> <span>' . esc_html( $label ) . '</span></label>';
				break;
			case 'select':
				$options = $opts['options'] ?? array();
				$html = '<select name="' . esc_attr( $name ) . '" class="ah-nav-type-select' . $class . '">';
				foreach ( $options as $k => $v ) {
					$html .= '<option value="' . esc_attr( $k ) . '"' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
				}
				$html .= '</select>';
				break;
			default:
				$html = '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text' . $class . '"' . $ph . '>';
		}

		return $html;
	}

	/**
	 * Submenu head (section heading + add button inside a builder item).
	 */
	public static function submenuHead( string $title, string $button_label, string $button_class = '' ): void {
		echo '<div class="ah-submenu-head">';
		echo '<h3>' . esc_html( $title ) . '</h3>';
		echo '<button type="button" class="button ' . esc_attr( $button_class ) . '">' . esc_html( $button_label ) . '</button>';
		echo '</div>';
	}

	/**
	 * Add item button (at the bottom of a builder stack).
	 */
	public static function addButton( string $label, string $id ): void {
		echo '<p><button type="button" class="button button-secondary" id="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</button></p>';
	}

	/**
	 * Placement badge (header/footer visibility indicator).
	 */
	public static function badge( string $text, string $type = 'header', bool $active = true ): string {
		$active_class = $active ? ' is-active' : '';
		return '<span class="ah-placement-badge ah-badge-' . esc_attr( $type ) . $active_class . '" title="Visible in ' . esc_attr( $type ) . '">' . esc_html( $text ) . '</span>';
	}

	/**
	 * Autosuggest field (type-ahead link picker).
	 */
	public static function autosuggest( string $placeholder = 'Type page, blog, or static page name' ): string {
		return '<label class="ah-suggest-wrap"><span>Autosuggest</span>'
			. '<input type="text" class="regular-text ah-link-suggest-input" placeholder="' . esc_attr( $placeholder ) . '">'
			. '<div class="ah-suggestions" style="display:none"></div></label>';
	}
}
