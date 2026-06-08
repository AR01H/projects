<?php
defined( 'ABSPATH' ) || exit;
$_nutrition_h = CH_Shared_Data::section_heading( 'nutrition_split' );
return [
	'settings'        => ch_get_settings(),
	'hero'            => CH_Shared_Data::section_heading( 'page_hero_why_sugarcane' ),
	'nutrition_h'     => $_nutrition_h,
	'cta'             => CH_Shared_Data::section_heading( 'cta_why_sugarcane' ),
	'nutrition_facts' => CH_Real_Loader::csv( 'nutrition-facts' ),
	'nf_disclaimer'   => $_nutrition_h['disclaimer'] ?? '',
];
