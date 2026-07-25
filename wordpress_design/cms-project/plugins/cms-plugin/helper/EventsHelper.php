<?php
defined( 'ABSPATH' ) || exit;

/**
 * Helper functions for Events admin page.
 */
class AH_Events_Helper {

	/**
	 * Map color slug to hex background for admin swatch.
	 */
	public static function color_bg( string $color ): string {
		$map = array(
			'green'  => '#4a8c2a',
			'amber'  => '#d97706',
			'teal'   => '#0891b2',
			'purple' => '#7c3aed',
			'coral'  => '#e11d48',
			'indigo' => '#3730a3',
		);
		return $map[ $color ] ?? '#4a8c2a';
	}
}
