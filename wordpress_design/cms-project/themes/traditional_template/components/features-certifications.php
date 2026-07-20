<?php
defined( 'ABSPATH' ) || exit;

/* ── Data ───────────────────────────────────────────────────────────────────── */

$content = nt_data( 'content' )['certifications'] ?? [];

$cards = array_map( static function ( $cert ): array {
	$cert = (array) $cert;
	return [
		'icon'  => $cert['icon']  ?? '✅',
		'title' => $cert['title'] ?? '',
		'desc'  => $cert['desc']  ?? '',
		'badge' => $cert['badge'] ?? '',
	];
}, nt_data( 'certifications' ) ?: [] );

/* ── Render ─────────────────────────────────────────────────────────────────── */

get_template_part( 'components/parts/carousel_mini_grid_with_badge_container', null, [

	/* Section wrapper */
	'section_id'    => 'certifications',
	'section_class' => 'nt-certs-section',

	/* Header */
	'tag'          => $content['tag'] ?? 'Quality',
	'title'        => $content['heading'] ?? 'Our <span class="accent">Certifications</span>',
	'body'         => $content['body'] ?? 'We meet the highest standards of safety and quality.',
	'header_class' => 'nt-certs-header',

	/* CSS prefix - structural classes */
	'prefix'       => 'nt-certs',

	/* Card class overrides */
	'card_class'        => 'nt-cert-card',
	'card_icon_class'   => 'nt-cert-icon',
	'card_body_class'   => 'nt-cert-body',
	'card_title_class'  => 'nt-cert-title',
	'card_desc_class'   => 'nt-cert-desc',
	'card_badge_class'  => 'nt-cert-badge',

	/* IDs - JS uses these to drive the carousel */
	'track_id' => 'nt-certs-track',
	'dots_id'  => 'nt-certs-dots',
	'prev_id'  => 'nt-certs-prev',
	'next_id'  => 'nt-certs-next',

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
	'visual_class'       => 'nt-cert-visual',
	'visual_img_class'   => 'nt-cert-img',
	'visual_badge_class' => 'nt-cert-badge',

] );
