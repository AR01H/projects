<?php
namespace Ah\Cms\Admin\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Reusable data table builder for admin listing pages.
 *
 * Usage:
 *   $table = new TableBuilder( $items, [ 'model' => 'faqs', 'page_slug' => 'ah-faqs' ] );
 *   $table->column( 'question', 'Question', fn($item) => esc_html( wp_trim_words( $item->question, 12 ) ) );
 *   $table->column( 'status', 'Status', fn($item) => AdminComponents::statusBadge( $item->status ) );
 *   $table->actions( fn($item) => [ 'edit_url' => '...', 'delete_url' => '...', 'delete_nonce' => '...' ] );
 *   $table->render();
 */
class TableBuilder {

	private array  $items;
	private array  $columns = [];
	private $actions_fn = null;
	private bool   $sortable = false;
	private string $model = '';
	private string $page_slug = '';
	private string $empty_message = 'No items found.';
	private string $empty_icon = 'dashicons-media-default';

	public function __construct( array $items, array $opts = [] ) {
		$this->items      = $items;
		$this->model      = $opts['model'] ?? '';
		$this->page_slug  = $opts['page_slug'] ?? '';
		$this->sortable   = $opts['sortable'] ?? false;
		$this->empty_message = $opts['empty_message'] ?? 'No items found.';
		$this->empty_icon = $opts['empty_icon'] ?? 'dashicons-media-default';
	}

	/**
	 * Add a column.
	 * @param string $key    Column key
	 * @param string $label  Column header
	 * @param callable|null $renderer fn($item): string — cell HTML. Null = echo $item->$key
	 */
	public function column( string $key, string $label, ?callable $renderer = null ): self {
		$this->columns[] = [
			'key'      => $key,
			'label'    => $label,
			'renderer' => $renderer,
		];
		return $this;
	}

	/**
	 * Set row action renderer.
	 * @param callable $fn fn($item): array{ edit_url?: string, delete_url?: string, delete_nonce?: string, extra?: string }
	 */
	public function actions( callable $fn ): self {
		$this->actions_fn = $fn;
		return $this;
	}

	public function sortable( bool $on = true ): self {
		$this->sortable = $on;
		return $this;
	}

	public function emptyState( string $message, string $icon = '' ): self {
		$this->empty_message = $message;
		if ( $icon ) {
			$this->empty_icon = $icon;
		}
		return $this;
	}

	public function render(): void {
		if ( empty( $this->items ) ) {
			echo '<div class="ah-empty-state" style="text-align:center;padding:40px 20px;color:var(--ah-muted);">';
			echo '<i class="dashicons ' . esc_attr( $this->empty_icon ) . '" style="font-size:48px;display:block;margin-bottom:12px;"></i>';
			echo '<p style="font-size:15px;">' . esc_html( $this->empty_message ) . '</p>';
			echo '</div>';
			return;
		}

		$sortable_class = $this->sortable ? ' ah-sortable-list' : '';
		$model_attr     = $this->model ? ' data-model="' . esc_attr( $this->model ) . '"' : '';

		echo '<div class="ah-table-wrap">';
		echo '<table class="ah-table' . $sortable_class . '"' . $model_attr . '>';
		echo '<thead><tr>';

		if ( $this->sortable ) {
			echo '<th style="width:30px;"></th>';
		}

		foreach ( $this->columns as $col ) {
			echo '<th>' . esc_html( $col['label'] ) . '</th>';
		}

		if ( $this->actions_fn ) {
			echo '<th style="width:120px;text-align:right;">Actions</th>';
		}

		echo '</tr></thead>';
		echo '<tbody>';

		foreach ( $this->items as $item ) {
			echo '<tr data-id="' . esc_attr( $item->id ?? '' ) . '">';

			if ( $this->sortable ) {
				echo '<td class="ah-sort-handle">&#9776;</td>';
			}

			foreach ( $this->columns as $col ) {
				echo '<td>';
				if ( $col['renderer'] ) {
					echo call_user_func( $col['renderer'], $item );
				} else {
					$key = $col['key'];
					echo esc_html( $item->$key ?? '' );
				}
				echo '</td>';
			}

			if ( $this->actions_fn ) {
				echo '<td class="row-actions" style="text-align:right;">';
				$actions = call_user_func( $this->actions_fn, $item );
				if ( ! empty( $actions['edit_url'] ) ) {
					echo '<a href="' . esc_url( $actions['edit_url'] ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a> ';
				}
				if ( ! empty( $actions['delete_url'] ) ) {
					$nonce_url = $actions['delete_url'];
					if ( ! empty( $actions['delete_nonce'] ) ) {
						$nonce_url = wp_nonce_url( $actions['delete_url'], $actions['delete_nonce'] );
					}
					echo '<a href="' . esc_url( $nonce_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm(\'Delete?\');">Delete</a>';
				}
				if ( ! empty( $actions['extra'] ) ) {
					echo $actions['extra']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</td>';
			}

			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
	}
}
