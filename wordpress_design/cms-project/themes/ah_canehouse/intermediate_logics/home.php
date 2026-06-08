<?php
defined( 'ABSPATH' ) || exit;
return [
	'settings'    => ch_get_settings(),
	'eq_media'    => ch_get_equipment_media_gallery(),
	'showcase_h'  => CH_Shared_Data::section_heading( 'showcase_home' ),
	'video_h'     => CH_Shared_Data::section_heading( 'video_showcase_home' ),
	'video_media' => CH_Real_Loader::csv( 'video-showcase' ),
	'mini_h'      => CH_Shared_Data::section_heading( 'mini_video_home' ),
	'mini_media'  => CH_Real_Loader::csv( 'mini-video-showcase' ),
];
