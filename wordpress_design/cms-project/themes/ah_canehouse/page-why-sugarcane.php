<?php
/**
 * Template Name: Why Sugarcane
 */
defined( 'ABSPATH' ) || exit;
get_header();

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? CONTACT_NUMBER;
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/page-hero', null, [
	'modifier' => 'ch-page-hero--sugarcane',
	'tag'      => 'Nature\'s Gift',
	'heading'  => 'Why <em>Sugarcane?</em>',
	'desc'     => 'Sugarcane has fuelled civilisations for over 2,000 years. Discover why fresh, live-pressed cane juice is the world\'s most natural energy drink - and why we\'re proud to bring it to the UK.',
] ); ?>

<?php 

get_template_part( 'components/history-info' ); 

get_template_part( 'components/story-cards' );

?>


<!-- ── What's Inside ─────────────────────────────────────────────────────────── -->
<?php
$nutrition_facts = [
	[ 'name' => '🍬 Natural Sugars', 'value' => '~13-15g', 'note' => 'Sucrose glucose fructose - natural energy' ],
	[ 'name' => '💊 Potassium',      'value' => '~300mg',  'note' => 'Electrolyte for heart & muscles' ],
	[ 'name' => '⚗️ Magnesium',      'value' => '~10mg',   'note' => 'Nervous system & energy' ],
	[ 'name' => '🌿 Antioxidants',   'value' => 'Rich',    'note' => 'Polyphenols & flavonoids' ],
	[ 'name' => '💧 Water Content',   'value' => '~70%',    'note' => 'Natural hydration' ],
];
$_nf_rows = '';
foreach ( $nutrition_facts as $nf ) {
	$_nf_rows .= '<div class="ch-nutrition-row">'
		. '<span class="ch-nutrition-name">' . esc_html( $nf['name'] ?? '' ) . '</span>'
		. '<span class="ch-nutrition-val">'  . esc_html( $nf['value'] ?? '' ) . '</span>'
		. '<span class="ch-nutrition-note">' . esc_html( $nf['note'] ?? '' ) . '</span>'
		. '</div>';
}
$_nf_disclaimer = get_option( 'ch_nutrition_disclaimer', '* Values are approximate for 350ml fresh-pressed yellow cane, no additives.' );
$_inside_extra  = '<div class="ch-nutrition-list">' . $_nf_rows . '</div>'
	. '<p style="margin-top:1rem;font-size:0.78rem;color:var(--client-color-15-muted);font-style:italic;">' . esc_html( $_nf_disclaimer ) . '</p>';
unset( $_nf_rows, $_nf_disclaimer );

$_inside_visual = '<div class="ch-inside-card">'
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
	'tag'           => 'Nutritional Profile',
	'title'         => 'What\'s Inside <span class="accent">Every Sip</span>',
	'body'          => 'A single 350ml glass of freshly pressed sugarcane juice delivers a surprising range of natural nutrients:',
	'extra_html'    => $_inside_extra,
	'visual_html'   => $_inside_visual,
	'visual_class'  => 'ch-inside-visual',
	'content_anim'  => 'fade-left',
	'visual_anim'   => 'fade-right',
] );
unset( $_inside_extra, $_inside_visual );
?>



<!-- Why Sugarcane Juice is Loved Worldwide -->
<?php get_template_part( 'components/sugarcane-benefits' ); ?>

<?php get_template_part( 'components/beyondjuice' ); ?>

<?php get_template_part( 'components/cta-section', null, [
	'tag'        => 'Experience It',
	'heading'    => 'Ready to <span class="accent" style="color:var(--client-color-7);">Taste the Tradition?</span>',
	'body'       => 'Book us for your next event or explore a franchise opportunity in your city.',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise →',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>


</main>
<?php get_footer(); ?>
