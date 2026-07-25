<?php
namespace Ah\Cms\Admin\Abstracts;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Admin\Components\AdminComponents;

/**
 * Abstract base for admin list pages.
 * Provides standard list + edit pattern with filterBar + dataTable.
 *
 * Usage: Extend this class and implement the abstract methods.
 */
abstract class AbstractListPage {

	/** Page slug in WP admin (e.g. 'ah-my-page'). */
	abstract protected function page_slug(): string;

	/** Page title shown in header. */
	abstract protected function page_title(): string;

	/** Page description shown below header. */
	abstract protected function page_description(): string;

	/** Dashicon name (without 'dashicons-' prefix). */
	abstract protected function page_icon(): string;

	/**
	 * Get all items for the list.
	 * @return array Array of item objects/arrays.
	 */
	abstract protected function get_items(): array;

	/**
	 * Get a single item by ID.
	 * @param int $id
	 * @return object|null
	 */
	abstract protected function find( int $id ): ?object;

	/**
	 * Save an item (create or update).
	 * @param int $id   0 for create, >0 for update.
	 * @param array $data Cleaned form data.
	 * @return int The saved item ID.
	 */
	abstract protected function save( int $id, array $data ): int;

	/**
	 * Delete an item.
	 * @param int $id
	 * @return bool
	 */
	abstract protected function delete( int $id ): bool;

	/**
	 * Build the table columns for dataTable().
	 * @return array Column configs.
	 */
	abstract protected function table_columns(): array;

	/**
	 * Build the table row actions for each item.
	 * @param object $item
	 * @return string HTML for action buttons.
	 */
	abstract protected function row_actions( object $item ): string;

	/**
	 * Build the edit form HTML.
	 * @param object|null $item  Null for create, object for edit.
	 * @return string Form HTML.
	 */
	abstract protected function edit_form( ?object $item ): string;

	/** Optional: extra fields for filterBar(). */
	protected function filter_fields(): array { return array(); }

	/** Optional: search placeholder text. */
	protected function search_placeholder(): string { return 'Search...'; }

	/** Optional: add button label. */
	protected function add_label(): string { return '+ Add New'; }

	/** Optional: items per page. */
	protected function per_page(): int { return 20; }

	/** Optional: POST handler for create/edit. */
	protected function handle_post(): void {}

	/** Optional: GET handler for delete. */
	protected function handle_delete(): void {}

	// ── Public API ──────────────────────────────────────────────

	public function run(): void {
		$this->handle_post();
		$this->handle_delete();

		$action = sanitize_key( $_GET['action'] ?? 'list' );
		if ( $action === 'edit' || $action === 'add' ) {
			$this->render_edit( $action );
		} else {
			$this->render_list();
		}
	}

	protected function render_list(): void {
		$search = sanitize_text_field( $_GET['s'] ?? '' );
		$items  = $this->get_items();

		if ( $search ) {
			$items = array_values( array_filter( $items, function ( $item ) use ( $search ) {
				return stripos( $item->name ?? $item->title ?? $item->slug ?? '', $search ) !== false;
			} ) );
		}

		echo '<div class="wrap ah-wrap">';
		AdminComponents::pageHeader( $this->page_icon(), $this->page_title(), $this->page_description() );

		if ( ! empty( $_GET['saved'] ) ) {
			AdminComponents::notice( 'Saved successfully.', 'success' );
		}

		AdminComponents::filterBar( array_merge( array(
			'page_slug'          => $this->page_slug(),
			'search_placeholder' => $this->search_placeholder(),
			'search_value'       => $search,
			'add_url'            => add_query_arg( array( 'page' => $this->page_slug(), 'action' => 'add' ), admin_url( 'admin.php' ) ),
			'add_label'          => $this->add_label(),
		), $this->filter_fields() ) );

		$rows = array();
		foreach ( $items as $item ) {
			$row = new \stdClass();
			$row->id = $item->id ?? 0;
			$row->edit_url = add_query_arg( array( 'page' => $this->page_slug(), 'action' => 'edit', 'edit' => $item->id ?? 0 ), admin_url( 'admin.php' ) );
			$row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => $this->page_slug(), 'delete_id' => $item->id ?? 0 ), admin_url( 'admin.php' ) ), 'ah_del_' . $this->page_slug() );
			$row->item = $item;
			$rows[] = $row;
		}

		AdminComponents::dataTable( array(
			'columns'       => $this->table_columns(),
			'items'         => $rows,
			'empty_message' => $search ? 'No items match your search.' : 'No items yet.',
			'actions'       => function ( $r ) {
				return $this->row_actions( $r->item );
			},
		) );

		echo '</div>';
	}

	protected function render_edit( string $action ): void {
		$edit_id = (int) ( $_GET['edit'] ?? 0 );
		$item    = ( $action === 'edit' && $edit_id ) ? $this->find( $edit_id ) : null;

		echo '<div class="wrap ah-wrap">';
		AdminComponents::pageHeader( $this->page_icon(), $this->page_title(), $this->page_description() );

		AdminComponents::backLink( add_query_arg( array( 'page' => $this->page_slug() ), admin_url( 'admin.php' ) ) );

		AdminComponents::card(
			$action === 'edit' ? 'Edit' : 'Create',
			$this->edit_form( $item )
		);

		echo '</div>';
	}
}
