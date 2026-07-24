<?php
defined( 'ABSPATH' ) || exit;

class AH_Audit_Model extends AH_Model_Base {

	protected string $table_suffix = 'audit_logs';
	protected string $primary_key  = 'id';

	public function get_paginated( int $page = 1, array $filters = array() ): array {
		$where    = array();
		$where_in = array();
		if ( ! empty( $filters['action'] ) )     { $where[] = 'action = %s';     $where_in[] = $filters['action']; }
		if ( ! empty( $filters['table_name'] ) ) { $where[] = 'table_name = %s'; $where_in[] = $filters['table_name']; }
		if ( ! empty( $filters['user_id'] ) )    { $where[] = 'user_id = %d';    $where_in[] = (int) $filters['user_id']; }
		$args = array( 'order_by' => 'created_at', 'order' => 'DESC' );
		if ( $where ) { $args['where'] = implode( ' AND ', $where ); $args['where_in'] = $where_in; }
		return $this->paginate( $page, $args );
	}

	// Override delete to prevent log tampering
	public function delete( int $id ): bool {
		return false;
	}
}
