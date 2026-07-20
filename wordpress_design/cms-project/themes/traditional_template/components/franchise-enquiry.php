<?php
defined( 'ABSPATH' ) || exit;

$s           = [];
$_d          = nt_data('content')['franchise_enquiry'] ?? [];
$frn_heading = $s['franchise_wiz_heading'] ?? $_d['heading'] ?? '';
$frn_sub     = $s['franchise_wiz_sub']     ?? $_d['sub']     ?? '';
$frn_image   = $s['franchise_wiz_image']   ?? 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80';

/* ── Banner that opens the modal ─────────────────────────────────────────────── */
get_template_part( 'components/forms/form_cta_banner', null, [
	'section_id'     => 'franchise-enquiry',
	'section_class'  => 'nt-frn-section',
	'card_class'     => 'nt-frn-card fade-up',
	'image_side'     => 'left',
	'content_class'  => 'nt-frn-content',
	'visual_class'   => 'nt-frn-visual',
	'badge_class'    => 'nt-frn-visual-badge',
	'badge'          => 'Be Your Own Boss 🌿',
	'tag'            => 'Franchise Opportunity',
	'title_class'    => 'nt-frn-title',
	'heading'        => $frn_heading,
	'sub_class'      => 'nt-frn-sub',
	'sub'            => $frn_sub,
	'features_class' => 'nt-frn-features',
	'features'       => $_d['features'] ?? [],
	'image'          => $frn_image,
	'image_alt'      => 'Franchise opportunity',
	'button_id'      => 'nt-frn-open',
	'button_class'   => 'nt-frn-open btn-lime',
	'button_label'   => '🌿 Enquire Now',
] );

/* ── Generic Multistep Form ──────────────────────────────────────────────────── */
$json_path = get_template_directory() . '/admin/data/form_franchise.json';
$form_data = [];
if ( file_exists( $json_path ) ) {
	$form_data = json_decode( file_get_contents( $json_path ), true ) ?: [];
}

ob_start();
get_template_part( 'components/parts/generic-multistep-form', null, $form_data );
$steps_html = ob_get_clean();

/* ── Reusable modal shell ────────────────────────────────────────────────────── */
get_template_part( 'components/parts/generic-dialog', null, [
	'id'      => 'nt-frn-form',
	'title'   => 'Franchise enquiry',
	'content' => $steps_html,
] );
