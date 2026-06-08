<?php
/**
 * Intermediate Page: Stories
 *
 * Assembles all data needed by page-stories.php.
 * Returns an associative array — page template consumes $data['key'].
 *
 * Data flow:
 *   real_data/ → PT_Stories_Data → here → page-stories.php → components
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/includes/data/class-stories-data.php';

return [
	'hero'         => PT_Stories_Data::page_hero(),
	'featured'     => PT_Stories_Data::featured(),
	'stories'      => PT_Stories_Data::non_featured(),
	'grid_heading' => PT_Stories_Data::grid_heading(),
	'cta'          => PT_Stories_Data::cta_section(),
];
