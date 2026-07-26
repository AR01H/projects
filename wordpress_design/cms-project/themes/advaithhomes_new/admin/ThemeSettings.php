<?php
/**
 * admin/ThemeSettings.php - Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Feature\Admin
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Feature/Admin/ThemeSettings.php';

class ADN_Theme_Settings {

	const ACTION = \Adn\Theme\Feature\Admin\ThemeSettings::ACTION;

	public static function init(): void {
		\Adn\Theme\Feature\Admin\ThemeSettings::init();
	}

	public static function raw( $group_id ): ?array {
		return \Adn\Theme\Feature\Admin\ThemeSettings::raw( (string) $group_id );
	}

	public static function render( $group_id, $tab, $subtab = '' ): void {
		\Adn\Theme\Feature\Admin\ThemeSettings::render( (string) $group_id, (string) $tab, (string) $subtab );
	}

	public static function handle_save(): void {
		\Adn\Theme\Feature\Admin\ThemeSettings::handleSave();
	}
}
