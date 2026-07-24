<?php
namespace Ah\Cms\Admin\Components;

defined( 'ABSPATH' ) || exit;

class AdminComponents {

	/**
	 * Render a notice banner (success/error/warning).
	 */
	public static function notice( string $message, string $type = 'success' ): void {
		if ( ! $message ) return;
		echo '<div class="ah-notice ah-notice-' . esc_attr( $type ) . '">' . esc_html( $message ) . '</div>';
	}

	/**
	 * Render a page header with dashicon, title, and optional description.
	 */
	public static function pageHeader( string $icon, string $title, string $description = '' ): void {
		echo '<h1><span class="dashicons dashicons-' . esc_attr( $icon ) . '"></span> ' . esc_html( $title ) . '</h1>';
		if ( $description ) {
			echo '<p class="ah-builder-note">' . esc_html( $description ) . '</p>';
		}
	}

	/**
	 * Render a search/filter bar with optional dropdowns and add button.
	 *
	 * @param array $args {
	 *     @type string $page_slug          Hidden page value.
	 *     @type string $search_placeholder Placeholder for search input.
	 *     @type string $search_value       Current search value.
	 *     @type string $search_name        Input name for search (default 's').
	 *     @type array  $filters            Array of filter configs: name, options, selected, show_if, onchange, style.
	 *     @type array  $hidden_inputs      Associative array of name => value for hidden inputs.
	 *     @type string $add_url            URL for the add button.
	 *     @type string $add_label          Label for the add button.
	 * }
	 */
	public static function filterBar( array $args ): void {
		$page_slug          = $args['page_slug'] ?? '';
		$search_placeholder = $args['search_placeholder'] ?? '';
		$search_value       = $args['search_value'] ?? '';
		$search_name        = $args['search_name'] ?? 's';
		$filters            = $args['filters'] ?? array();
		$hidden_inputs      = $args['hidden_inputs'] ?? array();
		$add_url            = $args['add_url'] ?? '';
		$add_label          = $args['add_label'] ?? '';
		$show_reset         = $args['show_reset'] ?? true;
		$extra_fields       = $args['extra_fields'] ?? '';
		$active_values      = $args['active_values'] ?? array();

		// Determine if any filter is active
		$has_active = $search_value !== '';
		if ( ! $has_active ) {
			foreach ( $filters as $f ) {
				if ( ! empty( $f['selected'] ) && (string) $f['selected'] !== '' ) {
					$has_active = true;
					break;
				}
			}
		}
		if ( ! $has_active ) {
			foreach ( $active_values as $v ) {
				if ( $v !== '' && $v !== null ) {
					$has_active = true;
					break;
				}
			}
		}

		$reset_url = admin_url( 'admin.php?page=' . esc_attr( $page_slug ) );
		foreach ( $hidden_inputs as $name => $value ) {
			if ( $name !== 'page' ) {
				$reset_url = add_query_arg( $name, $value, $reset_url );
			}
		}

		echo '<div class="ah-table-top">';
		echo '<form class="ah-search-form" method="get">';
		echo '<input type="hidden" name="page" value="' . esc_attr( $page_slug ) . '">';

		foreach ( $hidden_inputs as $name => $value ) {
			echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}

		if ( $search_placeholder ) {
			echo '<input type="search" name="' . esc_attr( $search_name ) . '" value="' . esc_attr( $search_value ) . '" placeholder="' . esc_attr( $search_placeholder ) . '">';
		}

		foreach ( $filters as $filter ) {
			if ( isset( $filter['show_if'] ) && ! $filter['show_if'] ) {
				continue;
			}
			$onchange = isset( $filter['onchange'] ) ? ' onchange="' . esc_attr( $filter['onchange'] ) . '"' : '';
			$style    = isset( $filter['style'] ) ? ' style="' . esc_attr( $filter['style'] ) . '"' : '';
			echo '<select name="' . esc_attr( $filter['name'] ) . '"' . $onchange . $style . '>';
			foreach ( $filter['options'] as $value => $label ) {
				$sel = selected( $filter['selected'] ?? '', (string) $value, false );
				echo '<option value="' . esc_attr( $value ) . '"' . $sel . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
		}

		if ( $extra_fields ) {
			echo $extra_fields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '<button class="ah-btn ah-btn-secondary">Filter</button>';

		if ( $show_reset && $has_active ) {
			echo '<a href="' . esc_url( $reset_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm" style="margin-left:4px;">Reset</a>';
		}

		echo '</form>';

		if ( $add_url && $add_label ) {
			echo '<a href="' . esc_url( $add_url ) . '" class="ah-btn ah-btn-primary">' . esc_html( $add_label ) . '</a>';
		}

		echo '</div>';
	}

	/**
	 * Render a data table with sortable rows.
	 *
	 * @param array $args {
	 *     @type array  $columns       Array of ['key' => ..., 'label' => ..., 'render' => callable, 'style' => ...].
	 *     @type array  $items         Array of row objects.
	 *     @type callable $actions     Callback receiving item, returns action HTML.
	 *     @type bool   $sortable      Whether rows are sortable.
	 *     @type string $model         Data-model attribute value.
	 *     @type string $empty_message Message when no items.
	 * }
	 */
	public static function dataTable( array $args ): void {
		$columns       = $args['columns'] ?? array();
		$items         = $args['items'] ?? array();
		$actions       = $args['actions'] ?? null;
		$sortable      = $args['sortable'] ?? false;
		$model         = $args['model'] ?? '';
		$empty_message = $args['empty_message'] ?? '';
		$colspan       = count( $columns ) + ( $sortable ? 1 : 0 ) + ( $actions ? 1 : 0 );

		$table_class = 'ah-table';
		if ( $sortable ) {
			$table_class .= ' ah-sortable-list';
		}

		echo '<div class="ah-table-wrap">';
		echo '<table class="' . esc_attr( $table_class ) . '"';
		if ( $model ) {
			echo ' data-model="' . esc_attr( $model ) . '"';
		}
		echo '>';

		echo '<thead><tr>';
		if ( $sortable ) {
			echo '<th></th>';
		}
		foreach ( $columns as $col ) {
			$th_style = isset( $col['style'] ) ? ' style="' . esc_attr( $col['style'] ) . '"' : '';
			echo '<th' . $th_style . '>' . ( $col['label'] ?? '' ) . '</th>';
		}
		if ( $actions ) {
			echo '<th>Actions</th>';
		}
		echo '</tr></thead>';

		echo '<tbody>';
		if ( empty( $items ) && $empty_message ) {
			echo '<tr><td colspan="' . esc_attr( $colspan ) . '" style="text-align:center;color:var(--ah-muted);padding:32px;">' . esc_html( $empty_message ) . '</td></tr>';
		}
		foreach ( $items as $item ) {
			$item_id = isset( $item->id ) ? $item->id : ( isset( $item->ID ) ? $item->ID : '' );
			echo '<tr data-id="' . esc_attr( $item_id ) . '">';
			if ( $sortable ) {
				echo '<td class="ah-sort-handle">&#9776;</td>';
			}
			foreach ( $columns as $col ) {
				$td_style = isset( $col['style'] ) ? ' style="' . esc_attr( $col['style'] ) . '"' : '';
				echo '<td' . $td_style . '>';
				if ( isset( $col['render'] ) && is_callable( $col['render'] ) ) {
					echo call_user_func( $col['render'], $item );
				} elseif ( isset( $col['key'] ) ) {
					echo esc_html( $item->{$col['key']} ?? '' );
				}
				echo '</td>';
			}
			if ( $actions ) {
				echo '<td class="row-actions">' . call_user_func( $actions, $item ) . '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
	}

	/**
	 * Render a card wrapper with title.
	 */
	public static function card( string $title, string $content ): void {
		echo '<div class="ah-card">';
		echo '<div class="ah-card-header"><h2>' . esc_html( $title ) . '</h2></div>';
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	/**
	 * Render a form row with label + input.
	 */
	public static function formRow( string $label, string $input, string $help = '', string $id = '' ): void {
		echo '<div class="ah-form-row"';
		if ( $id ) {
			echo ' id="' . esc_attr( $id ) . '"';
		}
		echo '><label>' . $label . '</label>' . $input;
		if ( $help ) {
			echo '<p class="description">' . $help . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Render a status badge. Returns HTML string.
	 */
	public static function statusBadge( string $status ): string {
		return '<span class="ah-badge ah-badge-' . esc_attr( $status ) . '">' . esc_html( $status ) . '</span>';
	}

	/**
	 * Render a back link.
	 */
	public static function backLink( string $url, string $label = '← Back' ): void {
		echo '<a href="' . esc_url( $url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:16px;display:inline-flex;">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Render pagination (wraps AH_Pagination::render).
	 */
	public static function pagination( array $meta ): void {
		echo \AH_Pagination::render( $meta );
	}

	/**
	 * Render a 2-column form grid.
	 *
	 * @param array $rows Array of [label, input, help?] tuples.
	 */
	public static function formGrid( array $rows ): void {
		echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">';
		foreach ( $rows as $row ) {
			$label = $row[0] ?? '';
			$input = $row[1] ?? '';
			$help  = $row[2] ?? '';
			self::formRow( $label, $input, $help );
		}
		echo '</div>';
	}

	/**
	 * Render a tab navigation bar (hash-based, JS switching).
	 */
	public static function tabBar( array $tabs, string $active = '' ): void {
		echo '<div class="ah-tabs">';
		foreach ( $tabs as $key => $label ) {
			$class = ( $key === $active ) ? 'ah-tab ah-tab-active' : 'ah-tab';
			echo '<a href="#ah-tab-' . esc_attr( $key ) . '" class="' . esc_attr( $class ) . '" data-tab="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</a>';
		}
		echo '</div>';
	}

	/**
	 * Render a tab navigation bar (URL-based, server-side page reload).
	 *
	 * @param array  $tabs     [ 'tab_key' => 'Tab Label', ... ]
	 * @param string $active   Currently active tab key.
	 * @param string $page_slug  WordPress admin page slug (e.g. 'ah-navigation').
	 * @param string $param     Query parameter name for tab (default 'tab').
	 */
	public static function tabBarUrl( array $tabs, string $active, string $page_slug = '', string $param = 'tab' ): void {
		if ( ! $page_slug ) {
			$page_slug = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		}
		echo '<div class="ah-tabs">';
		foreach ( $tabs as $key => $label ) {
			$class  = ( $key === $active ) ? 'ah-tab ah-tab-active' : 'ah-tab';
			$url    = admin_url( 'admin.php?page=' . esc_attr( $page_slug ) . '&' . esc_attr( $param ) . '=' . esc_attr( $key ) );
			echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a>';
		}
		echo '</div>';
	}

	/**
	 * Render an empty state placeholder.
	 */
	public static function emptyState( string $message, string $icon = 'dashicons-media-default' ): void {
		echo '<div class="ah-empty-state" style="text-align:center;padding:40px 20px;color:var(--ah-muted);">';
		echo '<i class="dashicons ' . esc_attr( $icon ) . '" style="font-size:48px;display:block;margin-bottom:12px;"></i>';
		echo '<p style="font-size:15px;">' . esc_html( $message ) . '</p>';
		echo '</div>';
	}

	/**
	 * Render a confirm-delete link with the plugin's modal dialog.
	 */
	public static function confirmDelete( string $url, string $nonce = '' ): void {
		$href = $nonce ? wp_nonce_url( $url, $nonce ) : $url;
		echo '<a href="' . esc_url( $href ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-confirm="Are you sure? This cannot be undone.">Delete</a>';
	}

	/**
	 * Render a form section card with heading.
	 */
	public static function formSection( string $title, string $content ): void {
		echo '<div class="ah-card" style="margin-bottom:16px;">';
		echo '<div class="ah-card-header"><h2 style="margin:0;font-size:15px;">' . esc_html( $title ) . '</h2></div>';
		echo '<div class="ah-card-body" style="padding:16px;">';
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></div>';
	}

	/**
	 * Render a stat card for dashboards.
	 */
	public static function statCard( $value, string $label, string $icon = '' ): void {
		echo '<div class="ah-stat-card">';
		if ( $icon ) {
			echo '<div class="stat-icon"><i class="dashicons ' . esc_attr( $icon ) . '"></i></div>';
		}
		echo '<div class="stat-number">' . esc_html( $value ) . '</div>';
		echo '<div class="stat-label">' . esc_html( $label ) . '</div>';
		echo '</div>';
	}

	/**
	 * Render a sticky header bar for edit forms (back + title + save).
	 */
	public static function stickyHeader( string $backUrl, string $title, string $submitLabel = 'Save' ): void {
		echo '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding:12px 16px;background:var(--ah-bg-light);border:1px solid var(--ah-border);border-radius:var(--ah-radius);">';
		echo '<div style="display:flex;align-items:center;gap:10px;">';
		echo '<a href="' . esc_url( $backUrl ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; Back</a>';
		echo '<h2 style="margin:0;font-size:16px;">' . esc_html( $title ) . '</h2>';
		echo '</div>';
		echo '<button type="submit" class="ah-btn ah-btn-primary">' . esc_html( $submitLabel ) . '</button>';
		echo '</div>';
	}

	/**
	 * Render visual radio card group.
	 */
	public static function radioCards( string $name, array $options, $selected = '' ): void {
		echo '<div style="display:flex;flex-wrap:wrap;gap:10px;">';
		foreach ( $options as $value => $label ) {
			$checked = (string) $value === (string) $selected ? ' checked' : '';
			echo '<label class="ah-style-card" style="cursor:pointer;display:flex;align-items:center;gap:8px;padding:10px 16px;border:2px solid var(--ah-border);border-radius:var(--ah-radius);transition:border-color .15s;' . ( $checked ? 'border-color:var(--ah-primary);' : '' ) . '">';
			echo '<input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . $checked . ' style="display:none;">';
			echo '<span>' . esc_html( $label ) . '</span></label>';
		}
		echo '</div>';
	}

	/**
	 * Render a media upload field with WP media picker.
	 *
	 * @param string $name  Input name.
	 * @param string $label Field label.
	 * @param mixed  $value Attachment ID (int) or URL (string).
	 * @param array  $opts  Options: id, type ('image'|'video'|'media').
	 */
	public static function mediaField( string $name, string $label, $value = '', array $opts = [] ): void {
		$type   = $opts['type'] ?? 'image';
		$id_attr = $opts['id'] ?? $name;

		// Resolve URL from ID or use raw URL.
		$url = '';
		if ( is_numeric( $value ) && (int) $value > 0 ) {
			$att = wp_get_attachment_image_src( (int) $value, 'medium' );
			if ( $att ) {
				$url = $att[0];
			} else {
				$url = wp_get_attachment_url( (int) $value );
			}
		} elseif ( $value ) {
			$url = $value;
		}

		$is_video = self::isVideoUrl( $url );

		$icon = $type === 'video' ? 'video-alt3' : ( $type === 'media' ? 'format-image' : 'format-image' );
		$choose_label = 'Choose Image';
		if ( $type === 'video' ) {
			$choose_label = 'Choose Video';
		} elseif ( $type === 'media' ) {
			$choose_label = 'Choose Media';
		}

		echo '<div class="ah-form-row">';
		echo '<label>' . esc_html( $label ) . '</label>';
		echo '<div class="ah-image-picker" data-picker-type="' . esc_attr( $type ) . '">';
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id_attr ) . '" value="' . esc_attr( $value ) . '">';

		// Preview.
		if ( $url ) {
			if ( $is_video ) {
				echo '<div class="ah-image-preview-wrap visible">';
				echo '<video class="ah-video-preview" src="' . esc_url( $url ) . '" controls muted style="width:100%;max-height:200px;object-fit:cover;border-radius:8px;"></video>';
				echo '</div>';
			} else {
				echo '<div class="ah-image-preview-wrap visible">';
				echo '<img class="ah-image-preview" src="' . esc_url( $url ) . '" alt="">';
				echo '</div>';
			}
		} else {
			echo '<div class="ah-image-placeholder"><i class="dashicons dashicons-' . esc_attr( $icon ) . '"></i><span>Click to choose ' . esc_html( strtolower( str_replace( 'media', 'image or video', $type ) ) ) . '</span></div>';
		}

		echo '<div class="ah-image-picker-btns">';
		echo '<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image" data-target="' . esc_attr( $id_attr ) . '">' . esc_html( $choose_label ) . '</button>';
		echo '<button type="button" class="ah-btn ah-btn-danger ah-btn-sm ah-remove-image" data-target="' . esc_attr( $id_attr ) . '">Remove</button>';
		echo '</div></div></div>';
	}

	/**
	 * Check if a URL points to a video file.
	 */
	private static function isVideoUrl( string $url ): bool {
		if ( ! $url ) return false;
		$ext = strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ) ?? '', PATHINFO_EXTENSION ) );
		return in_array( $ext, array( 'mp4', 'webm', 'ogv', 'ogg', 'mov', 'avi' ), true );
	}

	/**
	 * Render a generic form field by type.
	 */
	public static function field( string $type, string $name, string $label, $value = '', array $opts = [] ): void {
		switch ( $type ) {
			case 'text':
				self::formRow( $label, '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . ( ! empty( $opts['required'] ) ? ' required' : '' ) . ( ! empty( $opts['placeholder'] ) ? ' placeholder="' . esc_attr( $opts['placeholder'] ) . '"' : '' ) . '>' );
				break;
			case 'textarea':
				self::formRow( $label, '<textarea name="' . esc_attr( $name ) . '" rows="' . esc_attr( $opts['rows'] ?? 4 ) . '">' . esc_textarea( $value ) . '</textarea>' );
				break;
			case 'select':
				$select_html = '<select name="' . esc_attr( $name ) . '">';
				foreach ( $opts['options'] ?? [] as $k => $v ) {
					$select_html .= '<option value="' . esc_attr( $k ) . '"' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
				}
				$select_html .= '</select>';
				self::formRow( $label, $select_html );
				break;
			case 'number':
				self::formRow( $label, '<input type="number" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . ( isset( $opts['min'] ) ? ' min="' . esc_attr( $opts['min'] ) . '"' : '' ) . ( isset( $opts['max'] ) ? ' max="' . esc_attr( $opts['max'] ) . '"' : '' ) . '>' );
				break;
			case 'checkbox':
				echo '<div class="ah-form-row"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( $value, true, false ) . '> ' . esc_html( $label ) . '</label></div>';
				break;
			case 'toggle':
				echo '<div class="ah-form-row"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( $value, true, false ) . ' class="ah-toggle-input"> ' . esc_html( $label ) . '</label></div>';
				break;
			case 'image':
			case 'video':
			case 'media':
				self::mediaField( $name, $label, $value, array_merge( $opts, array( 'type' => $type ) ) );
				break;
			default:
				self::formRow( $label, '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">' );
		}
	}

	/**
	 * Render a complete admin list page: header + notice + filter bar + data table + pagination.
	 *
	 * @param array $args {
	 *     @type string $icon           Dashicon name (without dashicons- prefix).
	 *     @type string $title          Page title.
	 *     @type string $description    Optional description shown below title.
	 *     @type string $notice         Notice message (empty = no notice).
	 *     @type string $notice_type    'success' | 'error' | 'warning'.
	 *     @type array  $filter_bar     Args passed to filterBar(). Omit to skip filter bar.
	 *     @type array  $table          Args passed to dataTable(). Required.
	 *     @type array  $pagination     Pagination meta { total, total_pages, current_page }. Omit to skip.
	 * }
	 */
	public static function listPage( array $args ): void {
		$icon        = $args['icon'] ?? 'admin-post';
		$title       = $args['title'] ?? '';
		$description = $args['description'] ?? '';
		$notice      = $args['notice'] ?? '';
		$notice_type = $args['notice_type'] ?? 'success';
		$filter_bar  = $args['filter_bar'] ?? null;
		$table       = $args['table'] ?? array();
		$pagination  = $args['pagination'] ?? null;

		echo '<div class="wrap ah-wrap">';
		self::pageHeader( $icon, $title, $description );

		if ( $notice ) {
			self::notice( $notice, $notice_type );
		}

		if ( $filter_bar ) {
			self::filterBar( $filter_bar );
		}

		self::dataTable( $table );

		if ( $pagination ) {
			self::pagination( $pagination );
		}

		echo '</div>';
	}

	/**
	 * Render an action button with the plugin's modal dialog.
	 *
	 * @param string $label    Button text.
	 * @param string $url      Target URL.
	 * @param string $type     'primary' | 'secondary' | 'danger'.
	 * @param string $message  Confirmation message.
	 * @param string $icon     Optional dashicon name.
	 */
	public static function actionButton( string $label, string $url, string $type = 'secondary', string $message = '', string $icon = '' ): void {
		$class = 'ah-btn ah-btn-' . esc_attr( $type ) . ' ah-btn-sm';
		if ( $message ) {
			$class .= ' ah-confirm-delete';
		}
		echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '"';
		if ( $message ) {
			echo ' data-confirm="' . esc_attr( $message ) . '"';
		}
		echo ' title="' . esc_attr( $label ) . '">';
		if ( $icon ) {
			echo '<span class="dashicons dashicons-' . esc_attr( $icon ) . '" style="font-size:14px;width:14px;line-height:1.6;"></span> ';
		}
		echo esc_html( $label ) . '</a>';
	}

	/**
	 * Output the confirm modal JS (call once per page, typically at the bottom).
	 * Renders the modal HTML + JS that intercepts .ah-confirm-delete clicks.
	 */
	public static function confirmModal(): void {
		if ( did_action( 'ah_confirm_modal_html' ) ) {
			return;
		}
		do_action( 'ah_confirm_modal_html' );
		echo '<div id="ah-confirm-modal" class="ah-modal-overlay"><div class="ah-modal"><div class="ah-modal-icon"><span class="dashicons dashicons-warning"></span></div><h3 class="ah-modal-title">Confirm Action</h3><p class="ah-modal-message"></p><div class="ah-modal-actions"><button type="button" class="ah-btn ah-modal-cancel">Cancel</button><button type="button" class="ah-btn ah-btn-danger ah-modal-confirm">Confirm</button></div></div></div>';
	}
}
