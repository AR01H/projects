<?php
defined( 'ABSPATH' ) || exit;

/* ── Data ───────────────────────────────────────────────────────────────────── */

$content = App_Data_Provider::get( 'content' )['certifications'] ?? [];

$cards = array_map( static function ( $cert ): array {
	$cert = (array) $cert;
	return [
		'icon'  => $cert['icon']  ?? '✅',
		'title' => $cert['title'] ?? '',
		'desc'  => $cert['desc']  ?? '',
		'badge' => $cert['badge'] ?? '',
	];
}, App_Data_Provider::get( 'certifications' ) ?: [] );

/* ── Render ─────────────────────────────────────────────────────────────────── */

get_template_part( 'components/parts/carousel_mini_grid_with_badge_container', null, [

	/* Section wrapper */
	'section_id'    => 'certifications',
	'section_class' => 'app-certs-section',

	/* Header */
	'tag'          => $content['tag'] ?? 'Quality',
	'title'        => $content['heading'] ?? 'Our <span class="accent">Certifications</span>',
	'body'         => $content['body'] ?? 'We meet the highest standards of safety and quality.',
	'header_class' => 'app-certs-header',

	/* CSS prefix - structural classes */
	'prefix'       => 'app-certs',

	/* Card class overrides */
	'card_class'        => 'app-cert-card',
	'card_icon_class'   => 'app-cert-icon',
	'card_body_class'   => 'app-cert-body',
	'card_title_class'  => 'app-cert-title',
	'card_desc_class'   => 'app-cert-desc',
	'card_badge_class'  => 'app-cert-badge',

	/* IDs - JS uses these to drive the carousel */
	'track_id' => 'app-certs-track',
	'dots_id'  => 'app-certs-dots',
	'prev_id'  => 'app-certs-prev',
	'next_id'  => 'app-certs-next',

	/* Nav labels */
	'nav_label'  => 'Certifications navigation',
	'prev_label' => 'Previous certification',
	'next_label' => 'Next certification',

	/* Cards */
	'items' => $cards,

	/* Visual panel */
	'visual_image'       => $content['visual_image'] ?? 'https://placehold.co/600x400',
	'visual_alt'         => $content['visual_alt']   ?? 'Badge',
	'visual_label'       => $content['visual_label'] ?? 'Top Rated',
	'visual_class'       => 'app-cert-visual',
	'visual_img_class'   => 'app-cert-img',
	'visual_badge_class' => 'app-cert-badge',

] );
