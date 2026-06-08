<?php
defined( 'ABSPATH' ) || exit;

/* ── Data ───────────────────────────────────────────────────────────────────── */

$_d  = CH_Shared_Data::certifications_section_settings();
$_s  = ch_get_settings();

$cards = array_map( static function ( $cert ): array {
	$cert = (array) $cert;
	return [
		'icon'  => $cert['icon']  ?? '✅',
		'title' => $cert['title'] ?? '',
		'desc'  => $cert['desc']  ?? '',
		'badge' => $cert['badge'] ?? '',
	];
}, ch_get_certifications() );

/* ── Render ─────────────────────────────────────────────────────────────────── */

get_template_part( 'components/carousel_mini_grid_with_badge_container', null, [

	/* Section wrapper */
	'section_id'    => 'certifications',
	'section_class' => 'ch-certs-section',

	/* Header — cert_heading / cert_subtext from settings override JSON fallback */
	'tag'          => $_d['tag']                              ?? '',
	'title'        => $_s['cert_heading'] ?? $_d['heading']  ?? '',
	'body'         => $_s['cert_subtext'] ?? $_d['sub']      ?? '',
	'header_class' => 'ch-certs-header',

	/* CSS prefix — structural classes (carousel, track, nav) use ch-certs-* */
	'prefix'       => 'ch-certs',

	/* Card class overrides — existing stylesheet uses ch-cert-* (singular) for cards */
	'card_class'        => 'ch-cert-card',
	'card_icon_class'   => 'ch-cert-icon',
	'card_body_class'   => 'ch-cert-body',
	'card_title_class'  => 'ch-cert-title',
	'card_desc_class'   => 'ch-cert-desc',
	'card_badge_class'  => 'ch-cert-badge',

	/* IDs — JS uses these to drive the carousel */
	'track_id' => 'ch-certs-track',
	'dots_id'  => 'ch-certs-dots',
	'prev_id'  => 'ch-certs-prev',
	'next_id'  => 'ch-certs-next',

	/* Nav labels */
	'nav_label'  => 'Certifications navigation',
	'prev_label' => 'Previous certification',
	'next_label' => 'Next certification',

	/* Cards */
	'items' => $cards,

	/* Visual panel */
	'visual_image'       => get_template_directory_uri() . '/assets/images/ncass_logo.png',
	'visual_alt'         => $_d['visual_alt']   ?? '',
	'visual_label'       => $_d['visual_label'] ?? '',
	'visual_class'       => 'ch-cert-visual',
	'visual_img_class'   => 'ch-cert-img',
	'visual_badge_class' => 'ch-cert-badge',

] );
