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

<?php get_template_part( 'components/history-info' ); ?>
<!-- ── Stats bar ─────────────────────────────────────────────────────────────── -->
<!-- <div class="ch-stats-bar">
	<div class="container">
		<div class="ch-stats-grid">
			<?php foreach ( ch_get_sugarcane_stats() as $stat ) :
				$stat = (array) $stat;
			?>
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num"><?php echo esc_html( $stat['num'] ?? '' ); ?></span>
				<span class="ch-stat-label"><?php echo esc_html( $stat['label'] ?? '' ); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div> -->

<!-- ── Health Benefits Grid ──────────────────────────────────────────────────── -->
<section class="ch-benefits-page">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">Good For You</div>
			<h2 class="section-title">Natural <span class="accent">Benefits</span></h2>
			<p class="section-body">Packed with natural goodness your body recognises and loves - no lab, no additives, just the cane.</p>
		</div>
		<div class="ch-benefit-cards fade-up">
			<?php foreach ( ch_get_benefits() as $b ) :
				$b = (array) $b;
			?>
			<div class="ch-benefit-card">
				<div class="ch-benefit-card__icon"><?php echo esc_html( $b['icon'] ?? '🌿' ); ?></div>
				<h3><?php echo esc_html( $b['title'] ?? '' ); ?></h3>
				<p><?php echo esc_html( $b['desc'] ?? '' ); ?></p>
				<?php if ( ! empty( $b['tag'] ) ) : ?>
				<div class="ch-benefit-card__tag"><?php echo esc_html( $b['tag'] ); ?></div>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ── What's Inside ─────────────────────────────────────────────────────────── -->
<?php
$_nf_rows = '';
foreach ( ch_get_nutrition_facts() as $nf ) {
	$nf = (array) $nf;
	$_nf_rows .= '<div class="ch-nutrition-row">'
		. '<span class="ch-nutrition-name">' . esc_html( $nf['name'] ?? '' ) . '</span>'
		. '<span class="ch-nutrition-val">'  . esc_html( $nf['value'] ?? '' ) . '</span>'
		. '<span class="ch-nutrition-note">' . esc_html( $nf['note'] ?? '' ) . '</span>'
		. '</div>';
}
$_nf_disclaimer = get_option( 'ch_nutrition_disclaimer', '* Values are approximate for 350ml fresh-pressed yellow cane, no additives.' );
$_inside_extra  = '<div class="ch-nutrition-list">' . $_nf_rows . '</div>'
	. '<p style="margin-top:1rem;font-size:0.78rem;color:var(--ch-text-muted);font-style:italic;">' . esc_html( $_nf_disclaimer ) . '</p>';
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

get_template_part( 'components/story-cards' );

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


<!-- ── Global Love: Why the World Drinks Cane ────────────────────────────────── -->
<?php get_template_part( 'components/sugarcane-benefits', null, [
	'tag'   => 'Science & Tradition',
	'title' => 'Why the World <span class="accent">Swears By It</span>',
	'body'  => 'From Ayurvedic healers in ancient India to modern sports scientists - sugarcane juice has always stood apart. Here\'s what makes it extraordinary.',
] ); ?>



<!-- ── CTA ───────────────────────────────────────────────────────────────────── -->
<section class="ch-inner-cta">
	<div class="container">
		<div class="ch-inner-cta__box fade-up">
			<h2>Ready to Taste the Difference?</h2>
			<p>Experience 2,000 years of natural goodness - pressed fresh, served cool, just for you.</p>
			<div class="ch-inner-cta__btns">
				<a href="<?php echo esc_url( home_url( '/franchise' ) ); ?>" class="btn-lime">Join with Us</a>
				<a href="<?php echo esc_url( home_url( '/events' ) ); ?>" class="btn-outline" style="border-color:rgba(255,255,255,0.4);color:#fff;">Book for Events </a>
			</div>
		</div>
	</div>
</section>

</main>
<?php get_footer(); ?>
