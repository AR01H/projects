<?php
/**
 * calculators/registry.php — the list of available calculators.
 *
 * Each entry's KEY is what you pass to the shortcode: [ah_calculator key="stamp-duty"].
 * The view file is calculators/views/{key}.php and its script (optional) is
 * calculators/assets/calc-{key}.js. To add a calculator: drop those two files
 * in and add one line here. (A future admin tab can extend this list.)
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array<string,array{title:string,label:string,icon:string,view:string}>
 */
function adn_calculators() {
	$calculators = array(
		'stamp-duty' => array(
			'title' => 'Stamp Duty Calculator',
			'label' => 'Stamp Duty (SDLT) Calculator',
			'icon'  => '🧮',
			'view'  => 'stamp-duty.php',
		),
	);

	// Lets the theme admin (or plugins) register extra calculators later.
	return apply_filters( 'adn_calculators', $calculators );
}
