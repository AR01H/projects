<?php
/**
 * Calculator Merge Helper — Thin wrapper delegating to OOP class.
 *
 * @package Adn\Theme\Common\Helpers
 */
defined( 'ABSPATH' ) || exit;

function adn_merge_db_calculators( $tools ) {
	return \Adn\Theme\Feature\Calculators\CalculatorMergeHelper::merge( $tools );
}
