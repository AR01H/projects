<?php
defined( 'ABSPATH' ) || exit;

/**
 * Newsletters - manages the ah_newsletters table.
 */
class AH_Newsletters_Model extends AH_Model_Base {

	protected string $table_suffix = 'newsletters';

	public function get_active(): array {
		return $this->all( array(
			'where'    => "status = 'active'",
			'order_by' => 'created_at',
			'order'    => 'DESC',
		) );
	}

	public function subscribe( string $email, string $source = 'website' ): bool {
		global $wpdb;
		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) { return false; }

		$existing = $this->find_by( 'email', $email );
		if ( $existing ) {
			if ( $existing->status !== 'active' ) {
				$wpdb->update(
					$this->table(),
					array( 'status' => 'active' ),
					array( 'id' => $existing->id ),
					array( '%s' ),
					array( '%d' )
				);
			}
			return true;
		}

		$wpdb->insert(
			$this->table(),
			array(
				'email'  => $email,
				'source' => sanitize_text_field( $source ),
				'status' => 'active',
			),
			array( '%s', '%s', '%s' )
		);
		return (bool) $wpdb->insert_id;
	}

	public function toggle_status( int $id ): bool {
		global $wpdb;
		$row = $this->find( $id );
		if ( ! $row ) { return false; }
		return (bool) $wpdb->update(
			$this->table(),
			array( 'status' => $row->status === 'active' ? 'unsubscribed' : 'active' ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
	}
}
