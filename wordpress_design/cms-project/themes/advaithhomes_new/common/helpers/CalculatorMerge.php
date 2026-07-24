<?php
/**
 * Calculator Merge Helper
 *
 * Merges DB-stored calculators into the adn_calculators() registry.
 *
 * @package Adn\Theme\Common\Helpers
 */
defined( 'ABSPATH' ) || exit;

function adn_merge_db_calculators( $tools ) {
	if ( ! class_exists( 'AH_Calculator_DB' ) ) {
		return $tools;
	}
	foreach ( AH_Calculator_DB::get_all( 'active' ) as $row ) {
		$k = $row['calc_key'];
		if ( isset( $tools[ $k ] ) ) {
			continue;
		}
		$tools[ $k ] = array(
			'title' => $row['title'],
			'label' => '' !== $row['label'] ? $row['label'] : $row['title'],
			'icon'  => $row['icon'],
			'view'  => '__db__',
		);
	}
	return $tools;
}
