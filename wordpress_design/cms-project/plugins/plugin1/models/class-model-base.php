<?php
defined( 'ABSPATH' ) || exit;

/**
 * Base model class. Each domain model extends this and sets $table_suffix.
 */
abstract class AH_Model_Base {

	protected string $table_suffix = '';
	protected string $primary_key  = 'id';
	protected int    $per_page     = 20;

	protected function table(): string {
		return AH_DB_Helper::table( $this->table_suffix );
	}

	// ----------------------------------------------------------------
	// CRUD
	// ----------------------------------------------------------------

	public function find( int $id ): ?object {
		return AH_DB_Helper::get_row( $this->table(), $id );
	}

	public function find_by( string $col, $value ): ?object {
		return AH_DB_Helper::get_by( $this->table(), $col, $value );
	}

	public function all( array $args = array() ): array {
		$args['limit']  = $args['limit']  ?? 999;
		$args['offset'] = $args['offset'] ?? 0;
		return AH_DB_Helper::get_list( $this->table(), $args );
	}

	public function paginate( int $page = 1, array $args = array() ): array {
		$page     = max( 1, $page );
		$per_page = $args['per_page'] ?? $this->per_page;
		unset( $args['per_page'] );

		$where    = $args['where']    ?? '';
		$where_in = $args['where_in'] ?? array();

		$total = AH_DB_Helper::count( $this->table(), $where, $where_in );
		$meta  = AH_DB_Helper::paginate_meta( $total, $per_page, $page );

		$args['limit']  = $per_page;
		$args['offset'] = $meta['offset'];

		return array(
			'items' => AH_DB_Helper::get_list( $this->table(), $args ),
			'meta'  => $meta,
		);
	}

	public function create( array $data ): int {
		$id = AH_DB_Helper::insert( $this->table(), $data );
		if ( $id ) {
			AH_DB_Helper::log_action( 'create', $this->table_suffix, $id, null, $data );
		}
		return $id;
	}

	public function update( int $id, array $data ): bool {
		$old    = (array) $this->find( $id );
		$result = AH_DB_Helper::update( $this->table(), $data, $id );
		if ( $result ) {
			AH_DB_Helper::log_action( 'update', $this->table_suffix, $id, $old, $data );
		}
		return $result;
	}

	public function delete( int $id ): bool {
		$old    = (array) $this->find( $id );
		$result = AH_DB_Helper::delete( $this->table(), $id );
		if ( $result ) {
			AH_DB_Helper::log_action( 'delete', $this->table_suffix, $id, $old, null );
		}
		return $result;
	}

	public function set_status( int $id, string $status ): bool {
		return AH_DB_Helper::set_status( $this->table(), $id, $status );
	}

	public function set_sort_order( int $id, int $order ): bool {
		return AH_DB_Helper::update_sort_order( $this->table(), $id, $order );
	}

	public function count( string $where = '', array $where_in = array() ): int {
		return AH_DB_Helper::count( $this->table(), $where, $where_in );
	}

	// ----------------------------------------------------------------
	// Search
	// ----------------------------------------------------------------

	protected function search( string $term, array $search_columns, array $extra_args = array() ): array {
		if ( empty( $term ) ) {
			return $this->paginate( $extra_args['page'] ?? 1, $extra_args );
		}
		$search = AH_DB_Helper::search_where( $search_columns, $term );
		return $this->paginate( $extra_args['page'] ?? 1, array_merge( $extra_args, $search ) );
	}
}
