<?php
defined( 'ABSPATH' ) || exit;

class AH_Slug_Helper {

	/**
	 * Generate a unique slug for a given table/column.
	 * Appends -2, -3 etc. until unique.
	 */
	public static function generate( string $source, string $table, string $column = 'slug', int $exclude_id = 0 ): string {
		global $wpdb;

		$base  = sanitize_title( $source );
		$slug  = $base;
		$i     = 1;

		while ( true ) {
			$sql  = "SELECT id FROM `{$table}` WHERE `{$column}` = %s";
			$args = array( $slug );
			if ( $exclude_id ) {
				$sql   .= ' AND id != %d';
				$args[] = $exclude_id;
			}
			$exists = $wpdb->get_var( $wpdb->prepare( $sql, ...$args ) );
			if ( ! $exists ) break;
			$i++;
			$slug = $base . '-' . $i;
		}

		return $slug;
	}

	/**
	 * Generate slug for the ah_posts table (scoped by post_type).
	 */
	public static function generate_post( string $source, string $post_type, int $exclude_id = 0 ): string {
		global $wpdb;
		$t    = AH_DB_Helper::table( 'posts' );
		$base = sanitize_title( $source );
		$slug = $base;
		$i    = 1;

		while ( true ) {
			$sql  = "SELECT id FROM `{$t}` WHERE slug = %s AND post_type = %s";
			$args = array( $slug, $post_type );
			if ( $exclude_id ) { $sql .= ' AND id != %d'; $args[] = $exclude_id; }
			if ( ! $wpdb->get_var( $wpdb->prepare( $sql, ...$args ) ) ) break;
			$i++; $slug = $base . '-' . $i;
		}
		return $slug;
	}
}
