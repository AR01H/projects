<?php
defined( 'ABSPATH' ) || exit;
return [
	'header'     => CH_About_Data::page_header_info(),
	'values'     => ch_get_about_quality(),
	'origin'     => CH_About_Data::origin_settings(),
	'milestones' => CH_About_Data::origin_milestones(),
	'mvv'        => ch_get_about_mvv(),
	'gh_about'   => CH_Shared_Data::section_heading( 'gallery_about' ),
	'gh_strip'   => CH_Shared_Data::section_heading( 'gallery_strip_about' ),
	'gh_events'  => CH_Shared_Data::section_heading( 'events_preview' ),
	'gh_cta'     => CH_Shared_Data::section_heading( 'cta_about' ),
	'gallery'    => ch_get_about_gallery(),
	'eq_gallery' => ch_get_equipment_gallery(),
];
