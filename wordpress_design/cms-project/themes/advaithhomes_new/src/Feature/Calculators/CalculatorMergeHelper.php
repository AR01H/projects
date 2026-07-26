<?php
namespace Adn\Theme\Feature\Calculators;

defined( 'ABSPATH' ) || exit;

class CalculatorMergeHelper {

	public static function merge( $tools ) {
		if ( ! class_exists( 'AH_Calculator_DB' ) ) {
			return $tools;
		}
		foreach ( \AH_Calculator_DB::get_all( 'active' ) as $row ) {
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
}
