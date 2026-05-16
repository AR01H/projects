<?php
defined( 'ABSPATH' ) || exit;

class AH_Settings_Model extends AH_Model_Base {

	protected string $table_suffix = 'site_settings';

	public function get_all_grouped(): array {
		$rows   = $this->all( array( 'order_by' => 'group_name', 'order' => 'ASC' ) );
		$groups = array();
		foreach ( $rows as $row ) {
			$groups[ $row->group_name ][] = $row;
		}
		return $groups;
	}

	public function get_value( string $key ): string {
		global $wpdb;
		$table = $this->table();
		return (string) $wpdb->get_var(
			$wpdb->prepare( "SELECT setting_val FROM `{$table}` WHERE setting_key = %s", $key )
		);
	}

	public function set_value( string $key, string $value ): bool {
		global $wpdb;
		$table = $this->table();
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE setting_key = %s", $key ) );
		if ( $exists ) {
			return (bool) $wpdb->update( $table, array( 'setting_val' => $value ), array( 'setting_key' => $key ) );
		}
		return (bool) $wpdb->insert( $table, array( 'setting_key' => $key, 'setting_val' => $value ) );
	}

	public function save_group( string $group, array $key_values ): void {
		foreach ( $key_values as $key => $value ) {
			$this->set_value( sanitize_key( $key ), sanitize_textarea_field( $value ) );
		}
	}
}
