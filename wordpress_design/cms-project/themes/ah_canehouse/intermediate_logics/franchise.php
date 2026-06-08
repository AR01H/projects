<?php
defined( 'ABSPATH' ) || exit;
return [
	'hero'      => CH_Shared_Data::section_heading( 'page_hero_franchise' ),
	'gallery_h' => CH_Shared_Data::section_heading( 'gallery_franchise' ),
	'gallery'   => ch_get_franchise_media_gallery(),
];
