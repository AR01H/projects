<?php
/**
 * Template Name: Content Atlas
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/content-atlas.php';

get_template_part( 'components/content-atlas/atlas-styles' );
get_template_part( 'components/content-atlas/hero-summary',     null, $data );
get_template_part( 'components/content-atlas/core-settings',    null, $data );
get_template_part( 'components/content-atlas/live-signals',     null, $data );
get_template_part( 'components/content-atlas/posts-pages',      null, $data );
get_template_part( 'components/content-atlas/structured-content', null, $data );
get_template_part( 'components/content-atlas/tools-resources',  null, $data );
get_template_part( 'components/content-atlas/process-stats',    null, $data );
get_footer();
