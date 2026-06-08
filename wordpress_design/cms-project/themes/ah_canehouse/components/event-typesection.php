<?php
defined( 'ABSPATH' ) || exit;

$_d      = CH_Shared_Data::section_heading( 'event_type' );
$packages = ch_get_hire_packages();

get_template_part( 'components/carousel_text_info', null, [

	/* CSS prefix — tells the component to emit ch-hire-* classes to match existing stylesheet */
	'prefix'          => 'ch-hire',

	/* Section wrapper */
	'section_id'      => 'hire',
	'section_class'   => 'ch-hire-section',
	'container_class' => 'ch-hire-container',

	/* Header */
	'tag'             => $_d['tag']   ?? '',
	'title'           => $_d['title'] ?? '',
	'body'            => $_d['body']  ?? '',
	'header_class'    => 'ch-hire__header',

	/* Carousel */
	'carousel_id'     => 'ch-hire-carousel',
	'track_id'        => 'ch-hire-track',
	'dots_id'         => 'ch-hire-dots',
	'items_visible'   => 3,
	'nav_label'       => 'Event packages navigation',

	/* Class overrides to match existing stylesheet (ch-h-card-icon / ch-h-card-list) */
	'card_icon_class' => 'ch-h-card-icon',
	'card_list_class' => 'ch-h-card-list',

	/* Cards — mapped from the data source */
	'items' => array_map( static function ( $pkg ): array {
		$pkg = (array) $pkg;
		return [
			'icon'  => $pkg['icon']  ?? '🎉',
			'title' => $pkg['title'] ?? '',
			'desc'  => $pkg['description'] ?? $pkg['desc'] ?? '',
			'items' => (array) ( $pkg['items'] ?? [] ),
			'color' => $pkg['color'] ?? '',
		];
	}, $packages ),

] );
