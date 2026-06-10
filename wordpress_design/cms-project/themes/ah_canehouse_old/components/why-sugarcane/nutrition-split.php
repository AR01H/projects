<?php
defined( 'ABSPATH' ) || exit;
$nutrition_h     = $args['nutrition_h']     ?? [];
$nutrition_facts = $args['nutrition_facts'] ?? [];
$nf_disclaimer   = $args['nf_disclaimer']   ?? '';

$_nf_rows = '';
foreach ( $nutrition_facts as $nf ) {
	$_nf_rows .= '<div class="ch-nutrition-row">'
		. '<span class="ch-nutrition-name">' . esc_html( $nf['name']  ?? '' ) . '</span>'
		. '<span class="ch-nutrition-val">'  . esc_html( $nf['value'] ?? '' ) . '</span>'
		. '<span class="ch-nutrition-note">' . esc_html( $nf['note']  ?? '' ) . '</span>'
		. '</div>';
}
$extra_html = '<div class="ch-nutrition-list">' . $_nf_rows . '</div>'
	. '<p style="margin-top:1rem;font-size:0.78rem;color:var(--client-color-15-muted);font-style:italic;">' . esc_html( $nf_disclaimer ) . '</p>';

$visual_html = '<div class="ch-inside-card">'
	. '<div class="ch-inside-card__icon">🌾</div>'
	. '<div class="ch-inside-card__title">Zero Additives</div>'
	. '<div class="ch-inside-card__desc">No added sugar, no preservatives, no colouring, no flavouring. Just the pure cane, pressed live in front of you.</div>'
	. '</div>'
	. '<div class="ch-inside-card ch-inside-card--lime">'
	. '<div class="ch-inside-card__icon">♻️</div>'
	. '<div class="ch-inside-card__title">100% Sustainable</div>'
	. '<div class="ch-inside-card__desc">Even the leftover bagasse (cane fibre) is fully biodegradable. Sugarcane is one of the most eco-friendly crops on the planet.</div>'
	. '</div>'
	. '<div class="ch-inside-card">'
	. '<div class="ch-inside-card__icon">🤲</div>'
	. '<div class="ch-inside-card__title">Pressed Live</div>'
	. '<div class="ch-inside-card__desc">Every cup pressed fresh at your order - no pre-made batches, no bottles, no shortcuts. Maximum nutrition, maximum freshness.</div>'
	. '</div>';

get_template_part( 'components/image-text-split', null, [
	'layout'        => 'image-right',
	'section_class' => 'ch-inside-section',
	'inner_class'   => 'ch-inside-grid',
	'tag'           => $nutrition_h['tag']   ?? '',
	'title'         => $nutrition_h['title'] ?? '',
	'body'          => $nutrition_h['body']  ?? '',
	'extra_html'    => $extra_html,
	'visual_html'   => $visual_html,
	'visual_class'  => 'ch-inside-visual',
	'content_anim'  => 'fade-left',
	'visual_anim'   => 'fade-right',
] );
