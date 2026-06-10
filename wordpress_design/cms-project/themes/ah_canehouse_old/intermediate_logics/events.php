<?php
defined( 'ABSPATH' ) || exit;
return [
	'events_why' => class_exists( 'CH_Hire_Data' ) ? CH_Hire_Data::events_why() : [],
	'gallery'    => ch_get_events_media_gallery(),
	'hero'       => CH_Shared_Data::section_heading( 'page_hero_events' ),
	'gallery_h'  => CH_Shared_Data::section_heading( 'gallery_events' ),
];
