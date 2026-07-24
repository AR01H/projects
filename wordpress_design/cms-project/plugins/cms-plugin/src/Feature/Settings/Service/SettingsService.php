<?php

namespace Ah\Cms\Feature\Settings\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Settings service — manages site settings CRUD.
 * Provides a clean interface for reading/writing settings.
 */
class SettingsService {

	/**
	 * Get a setting value by key.
	 */
	public static function get( string $key, string $default = '' ): string {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT setting_value FROM `{$table}` WHERE setting_key = %s LIMIT 1",
				$key
			)
		);
		return $value !== null ? (string) $value : $default;
	}

	/**
	 * Get all settings as key => value pairs.
	 */
	public static function getAll(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		$rows  = $wpdb->get_results( "SELECT setting_key, setting_value FROM `{$table}`", ARRAY_A );
		$settings = [];
		foreach ( $rows as $row ) {
			$settings[ $row['setting_key'] ] = $row['setting_value'];
		}
		return $settings;
	}

	/**
	 * Get settings grouped by group column.
	 */
	public static function getByGroup( string $group ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT setting_key, setting_value FROM `{$table}` WHERE `group` = %s",
				$group
			),
			ARRAY_A
		);
		$settings = [];
		foreach ( $rows as $row ) {
			$settings[ $row['setting_key'] ] = $row['setting_value'];
		}
		return $settings;
	}

	/**
	 * Set a setting value. Creates if doesn't exist.
	 */
	public static function set( string $key, string $value ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		$exists = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM `{$table}` WHERE setting_key = %s LIMIT 1", $key )
		);

		if ( $exists ) {
			return $wpdb->update( $table, [ 'setting_value' => $value ], [ 'setting_key' => $key ] ) !== false;
		}

		return $wpdb->insert( $table, [ 'setting_key' => $key, 'setting_value' => $value ] ) !== false;
	}

	/**
	 * Set multiple settings at once.
	 */
	public static function setMany( array $settings ): bool {
		$success = true;
		foreach ( $settings as $key => $value ) {
			if ( ! self::set( $key, (string) $value ) ) {
				$success = false;
			}
		}
		return $success;
	}

	/**
	 * Delete a setting.
	 */
	public static function delete( string $key ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		return $wpdb->delete( $table, [ 'setting_key' => $key ] ) !== false;
	}
}
