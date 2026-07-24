<?php
defined( 'ABSPATH' ) || exit;

/**
 * Model for the ah_file_links table (uploaded files with link generation).
 */
class AH_File_Links_Model extends AH_Model_Base {

	protected string $table_suffix = 'file_links';

	/**
	 * Get paginated file list with optional search and type filter.
	 */
	public function get_paginated( int $page = 1, string $search = '', string $type = '' ): array {
		$args = array(
			'order_by' => 'created_at',
			'order'    => 'DESC',
		);

		$where_parts = array();

		if ( $search ) {
			$s         = AH_DB_Helper::search_where( array( 'original_name', 'mime_type' ), $search );
			$where_parts[] = $s['where'];
			$args['where_in'] = $s['where_in'];
		}

		if ( $type ) {
			switch ( $type ) {
				case 'image': $where_parts[] = "mime_type LIKE 'image/%'"; break;
				case 'video': $where_parts[] = "mime_type LIKE 'video/%'"; break;
				case 'audio': $where_parts[] = "mime_type LIKE 'audio/%'"; break;
				case 'pdf':   $where_parts[] = "mime_type = 'application/pdf'"; break;
				case 'doc':   $where_parts[] = "(mime_type LIKE '%word%' OR mime_type LIKE '%document%')"; break;
				case 'sheet': $where_parts[] = "(mime_type LIKE '%spreadsheet%' OR mime_type LIKE '%excel%' OR mime_type LIKE '%csv%')"; break;
			}
		}

		if ( $where_parts ) {
			$args['where'] = implode( ' AND ', $where_parts );
		}

		return $this->paginate( $page, $args );
	}

	/**
	 * Insert a new file record.
	 */
	public function create_file( array $data ): int {
		return $this->create( $data );
	}

	/**
	 * Delete a file record by ID.
	 */
	public function delete_file( int $id ): bool {
		return $this->delete( $id );
	}
}
