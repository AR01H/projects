<?php
defined( 'ABSPATH' ) || exit;

/* ── Data ───────────────────────────────────────────────────────────────────── */

$_d = nt_data('content')['franchise_showcase'] ?? [];

$cards = array_map( static function ( $card ): array {
	$card = (array) $card;
	return [
		'image' => $card['image'] ?? '',
		'title' => $card['title'] ?? '',
		'desc'  => $card['desc']  ?? '',
	];
}, nt_data('franchise_showcase') ?: [] );

$locations = array_map( static function ( $loc ): array {
	$loc = (array) $loc;
	return [
		'icon' => $loc['icon'] ?? '📍',
		'name' => $loc['name'] ?? '',
	];
}, nt_data('franchise_locations') ?: [] );

/* ── Render ─────────────────────────────────────────────────────────────────── */

get_template_part( 'components/carousel_image_with_title', null, [

	/* Section wrapper */
	'section_id'    => 'franchise',
	'section_class' => 'nt-franchise-section',

	/* Header - sourced from real_data/json/section-headings.json → franchise_showcase */
	'tag'   => $_d['tag']   ?? '',
	'title' => $_d['title'] ?? '',
	'body'  => $_d['body']  ?? '',

	/* Showcase class overrides */
	'showcase_class'  => 'nt-showcase-custom',
	'track_class'     => 'nt-showcase-container',
	'card_class'      => 'nt-showcase-card',
	'card_info_class' => 'nt-showcase-info',
	'controls_class'  => 'nt-showcase-controls',
	'btn_class'       => 'nt-s-btn',

	/* IDs - JS uses these to wire up the carousel */
	'track_id' => 'nt-showcase-track',
	'prev_id'  => 'nt-showcase-prev',
	'next_id'  => 'nt-showcase-next',

	/* Cards + marquee - prepared above */
	'items'         => $cards,
	'marquee_items' => $locations,

	/* Marquee class overrides */
	'marquee_class'       => 'nt-franchise-marquee',
	'marquee_track_class' => 'nt-franchise-track',
	'marquee_item_class'  => 'nt-f-item',
	'marquee_icon_class'  => 'nt-f-icon',
	'marquee_name_class'  => 'nt-f-name',

] );
