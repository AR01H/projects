<?php
defined( 'ABSPATH' ) || exit;

/* ── Data ───────────────────────────────────────────────────────────────────── */

$_d = CH_Shared_Data::section_heading( 'franchise_showcase' );

$cards = array_map( static function ( $card ): array {
	$card = (array) $card;
	return [
		'image' => $card['image'] ?? '',
		'title' => $card['title'] ?? '',
		'desc'  => $card['desc']  ?? '',
	];
}, ch_get_juice_showcase() );

$locations = array_map( static function ( $loc ): array {
	$loc = (array) $loc;
	return [
		'icon' => $loc['icon'] ?? '📍',
		'name' => $loc['name'] ?? '',
	];
}, ch_get_franchise_locations() );

/* ── Render ─────────────────────────────────────────────────────────────────── */

get_template_part( 'components/carousel_image_with_title', null, [

	/* Section wrapper */
	'section_id'    => 'franchise',
	'section_class' => 'ch-franchise-section',

	/* Header - sourced from real_data/json/section-headings.json → franchise_showcase */
	'tag'   => $_d['tag']   ?? '',
	'title' => $_d['title'] ?? '',
	'body'  => $_d['body']  ?? '',

	/* Showcase class overrides - original stylesheet uses ch-juice-showcase / ch-showcase-* names */
	'showcase_class'  => 'ch-juice-showcase',
	'track_class'     => 'ch-showcase-container',
	'card_class'      => 'ch-showcase-card',
	'card_info_class' => 'ch-showcase-info',
	'controls_class'  => 'ch-showcase-controls',
	'btn_class'       => 'ch-s-btn',

	/* IDs - JS uses these to wire up the carousel */
	'track_id' => 'ch-showcase-track',
	'prev_id'  => 'ch-showcase-prev',
	'next_id'  => 'ch-showcase-next',

	/* Cards + marquee - prepared above */
	'items'         => $cards,
	'marquee_items' => $locations,

	/* Marquee class overrides */
	'marquee_class'       => 'ch-franchise-marquee',
	'marquee_track_class' => 'ch-franchise-track',
	'marquee_item_class'  => 'ch-f-item',
	'marquee_icon_class'  => 'ch-f-icon',
	'marquee_name_class'  => 'ch-f-name',

] );
