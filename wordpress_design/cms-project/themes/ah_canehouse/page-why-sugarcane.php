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

<!-- ── Stats bar ─────────────────────────────────────────────────────────────── -->
<div class="ch-stats-bar">
	<div class="container">
		<div class="ch-stats-grid">
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">2,000+</span>
				<span class="ch-stat-label">Years of Tradition</span>
			</div>
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">100%</span>
				<span class="ch-stat-label">Natural & Pure</span>
			</div>
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">0</span>
				<span class="ch-stat-label">Additives Added</span>
			</div>
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">5+</span>
				<span class="ch-stat-label">Health Benefits</span>
			</div>
		</div>
	</div>
</div>

<!-- ── Health Benefits Grid ──────────────────────────────────────────────────── -->
<section class="ch-benefits-page">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">Good For You</div>
			<h2 class="section-title">Natural <span class="accent">Benefits</span></h2>
			<p class="section-body">Packed with natural goodness your body recognises and loves - no lab, no additives, just the cane.</p>
		</div>
		<div class="ch-benefit-cards fade-up">
			<div class="ch-benefit-card">
				<div class="ch-benefit-card__icon">⚡</div>
				<h3>Instant Energy</h3>
				<p>Natural sucrose provides rapid, sustained energy - far better than caffeine or artificial sugar highs. Perfect before or after activity.</p>
				<div class="ch-benefit-card__tag">Natural Carbs</div>
			</div>
			<div class="ch-benefit-card">
				<div class="ch-benefit-card__icon">💧</div>
				<h3>Deep Hydration</h3>
				<p>Rich in electrolytes including potassium, calcium, and magnesium - nature's own sports drink. Rehydrates faster than water alone.</p>
				<div class="ch-benefit-card__tag">Electrolytes</div>
			</div>
			<div class="ch-benefit-card">
				<div class="ch-benefit-card__icon">🛡️</div>
				<h3>Immunity Support</h3>
				<p>Antioxidants in fresh cane juice help neutralise free radicals, supporting your immune system naturally throughout the year.</p>
				<div class="ch-benefit-card__tag">Antioxidants</div>
			</div>
			<div class="ch-benefit-card">
				<div class="ch-benefit-card__icon">🫁</div>
				<h3>Digestive Aid</h3>
				<p>Traditionally blended with lemon and ginger, sugarcane juice soothes the gut, reduces acidity, and aids healthy digestion.</p>
				<div class="ch-benefit-card__tag">Gut Health</div>
			</div>
			<div class="ch-benefit-card">
				<div class="ch-benefit-card__icon">✨</div>
				<h3>Skin Glow</h3>
				<p>Alpha hydroxy acids and antioxidants keep skin hydrated and radiant from within. A natural beauty drink that works from the inside out.</p>
				<div class="ch-benefit-card__tag">Glow Factor</div>
			</div>
			<div class="ch-benefit-card">
				<div class="ch-benefit-card__icon">🌱</div>
				<h3>Low Glycaemic</h3>
				<p>Despite its natural sweetness, raw cane juice has a lower glycaemic index than many processed drinks, making it a smarter choice.</p>
				<div class="ch-benefit-card__tag">Low GI</div>
			</div>
		</div>
	</div>
</section>

<!-- ── Image Gallery ─────────────────────────────────────────────────────────── -->
<section class="ch-gallery-section">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">From Cane to Cup</div>
			<h2 class="section-title">The <span class="accent">Journey</span></h2>
			<p class="section-body">From tropical fields to your hands - freshly pressed, never processed.</p>
		</div>
		<div class="ch-gallery-grid fade-up">
			<div class="ch-gallery-item ch-gallery-item--tall">
				<img src="https://images.unsplash.com/photo-1635329535997-c0a9b62e2d56?auto=format&fit=crop&w=600&h=800&q=80" alt="Sugarcane field" loading="lazy">
				<div class="ch-gallery-caption">Fresh Sugarcane Fields</div>
			</div>
			<div class="ch-gallery-item">
				<img src="https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=600&h=400&q=80" alt="Fresh juice" loading="lazy">
				<div class="ch-gallery-caption">Pure Yellow Cane</div>
			</div>
			<div class="ch-gallery-item">
				<img src="https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&h=400&q=80" alt="Zesty lemon blend" loading="lazy">
				<div class="ch-gallery-caption">Zesty Lemon Blend</div>
			</div>
			<div class="ch-gallery-item ch-gallery-item--wide">
				<img src="https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&w=800&h=400&q=80" alt="Spicy ginger" loading="lazy">
				<div class="ch-gallery-caption">Spicy Ginger Infusion</div>
			</div>
			<div class="ch-gallery-item">
				<img src="https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&w=600&h=400&q=80" alt="Cooling mint" loading="lazy">
				<div class="ch-gallery-caption">Cooling Mint Blend</div>
			</div>
		</div>
	</div>
</section>

<!-- ── What's Inside ─────────────────────────────────────────────────────────── -->
<?php
$_inside_extra = '<div class="ch-nutrition-list">'
	. '<div class="ch-nutrition-row"><span class="ch-nutrition-name">🍬 Natural Sugars</span><span class="ch-nutrition-val">~13–15g</span><span class="ch-nutrition-note">Sucrose, glucose, fructose - natural energy</span></div>'
	. '<div class="ch-nutrition-row"><span class="ch-nutrition-name">💊 Potassium</span><span class="ch-nutrition-val">~300mg</span><span class="ch-nutrition-note">Electrolyte for heart &amp; muscles</span></div>'
	. '<div class="ch-nutrition-row"><span class="ch-nutrition-name">🦴 Calcium</span><span class="ch-nutrition-val">~40mg</span><span class="ch-nutrition-note">Bone health support</span></div>'
	. '<div class="ch-nutrition-row"><span class="ch-nutrition-name">⚗️ Magnesium</span><span class="ch-nutrition-val">~10mg</span><span class="ch-nutrition-note">Nervous system &amp; energy</span></div>'
	. '<div class="ch-nutrition-row"><span class="ch-nutrition-name">🌿 Antioxidants</span><span class="ch-nutrition-val">Rich</span><span class="ch-nutrition-note">Polyphenols &amp; flavonoids</span></div>'
	. '<div class="ch-nutrition-row"><span class="ch-nutrition-name">💧 Water Content</span><span class="ch-nutrition-val">~70%</span><span class="ch-nutrition-note">Natural hydration</span></div>'
	. '</div>'
	. '<p style="margin-top:1rem;font-size:0.78rem;color:var(--ch-text-muted);font-style:italic;">* Values are approximate for 350ml fresh-pressed yellow cane, no additives.</p>';

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

<!-- ── The Experience: Sensory Journey ───────────────────────────────────────── -->
<?php 
// get_template_part( 'components/sugarcane-experience', null, [
// 	'tag'      => 'The Cane House Experience',
// 	'title'    => 'Why Fresh-Pressed <span class="accent">Beats Everything</span>',
// 	'subtitle' => 'From field to glass in minutes - the live pressing experience your guests will never forget.',
// ] ); 
?> 

<!-- ── Global Love: Why the World Drinks Cane ────────────────────────────────── -->
<?php get_template_part( 'components/sugarcane-benefits', null, [
	'tag'   => 'Science & Tradition',
	'title' => 'Why the World <span class="accent">Swears By It</span>',
	'body'  => 'From Ayurvedic healers in ancient India to modern sports scientists - sugarcane juice has always stood apart. Here\'s what makes it extraordinary.',
] ); ?>

<!-- ── 10,000 Years of Sweet History (interactive book) ──────────────────────── -->
<?php get_template_part( 'components/history-info' ); ?>

<!-- ── CTA ───────────────────────────────────────────────────────────────────── -->
<section class="ch-inner-cta">
	<div class="container">
		<div class="ch-inner-cta__box fade-up">
			<h2>Ready to Taste the Difference?</h2>
			<p>Experience 2,000 years of natural goodness - pressed fresh, served cool, just for you.</p>
			<div class="ch-inner-cta__btns">
				<a href="<?php echo esc_url( home_url( '/#build' ) ); ?>" class="btn-lime">🥤 Build Your Juice</a>
				<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-outline" style="border-color:rgba(255,255,255,0.4);color:#fff;">Book for Events →</a>
			</div>
		</div>
	</div>
</section>

</main>
<?php get_footer(); ?>
