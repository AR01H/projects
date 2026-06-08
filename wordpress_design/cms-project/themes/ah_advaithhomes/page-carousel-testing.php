<?php
/**
 * Template Name: Carousel Testing
 * Showcases the unified carousel.php component with different configurations.
 */

get_header();
?>

<style>
.carousel-test-page { padding: 40px 20px; max-width: 1400px; margin: 0 auto; }
.carousel-test-page h1 { text-align: center; margin-bottom: 8px; font-size: 2rem; }
.carousel-test-page .page-intro { text-align: center; color: var(--client-color-16); margin-bottom: 56px; font-size: 1rem; }
.ct-section { margin-bottom: 64px; }
.ct-section h2 { font-size: 1.2rem; font-weight: 700; margin-bottom: 6px; color: var(--client-color-1); }
.ct-section p  { font-size: 0.875rem; color: var(--client-color-16); margin-bottom: 20px; }
.ct-label {
	display: inline-flex; gap: 8px; flex-wrap: wrap;
	margin-bottom: 20px;
}
.ct-badge {
	font-size: 0.72rem; font-weight: 600; letter-spacing: 0.4px;
	padding: 3px 10px; border-radius: 4px;
	background: var(--client-color-11); color: var(--client-color-7);
}
</style>

<main class="carousel-test-page">

	<h1>Carousel Component</h1>
	<p class="page-intro">All examples use <code>components/carousels/carousel.php</code> — one component, fully configurable.</p>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 1. Default — dots + arrows, 3 visible, feature cards          -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>1. Feature Cards — Dots + Arrows (default)</h2>
		<div class="ct-label">
			<span class="ct-badge">showDots: true</span>
			<span class="ct-badge">showArrows: true</span>
			<span class="ct-badge">infiniteLoop: true</span>
			<span class="ct-badge">3 / 2 / 1 visible</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'  => 'feature',
			'items' => [
				[ 'icon' => '🏡', 'title' => 'Find Your Home',    'text' => 'Browse our curated listings across premium residential locations.' ],
				[ 'icon' => '💰', 'title' => 'Best Value',         'text' => 'Competitive pricing backed by local market expertise.' ],
				[ 'icon' => '🔑', 'title' => 'Smooth Handover',   'text' => 'From offer accepted to keys in hand — we manage every step.' ],
				[ 'icon' => '📐', 'title' => 'Custom Builds',     'text' => 'Design your dream home with our trusted construction partners.' ],
				[ 'icon' => '📊', 'title' => 'Market Insights',   'text' => 'Data-driven advice to help you buy or sell at the right time.' ],
				[ 'icon' => '🤝', 'title' => 'Dedicated Agent',   'text' => 'One point of contact from start to completion.' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 2. Arrows only — no dots, scrollStep 1                       -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>2. Process Steps — Arrows only, one card at a time</h2>
		<div class="ct-label">
			<span class="ct-badge">showDots: false</span>
			<span class="ct-badge">scrollStep: 1</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'       => 'step',
			'showDots'   => false,
			'scrollStep' => 1,
			'items'      => [
				[ 'step' => '01', 'icon' => '📝', 'title' => 'Register Interest',  'text' => 'Fill in your requirements — budget, location, size.' ],
				[ 'step' => '02', 'icon' => '🔍', 'title' => 'Property Search',    'text' => 'We curate a shortlist of matching properties for you.' ],
				[ 'step' => '03', 'icon' => '🏠', 'title' => 'Viewings',           'text' => 'Guided viewings at your convenience, in-person or virtual.' ],
				[ 'step' => '04', 'icon' => '📋', 'title' => 'Offer & Negotiate',  'text' => 'Our agents negotiate the best deal on your behalf.' ],
				[ 'step' => '05', 'icon' => '✅', 'title' => 'Completion',         'text' => 'Legal checks done, keys collected — you\'re home.' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 3. Testimonials — autoplay 4 s, 3 visible                    -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>3. Testimonials — Autoplay, 3 visible</h2>
		<div class="ct-label">
			<span class="ct-badge">variant: testimonial</span>
			<span class="ct-badge">autoplay: true</span>
			<span class="ct-badge">autoplaySpeed: 4000</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'autoplay'      => true,
			'autoplaySpeed' => 4000,
			'items'         => [
				[
					'variant' => 'testimonial',
					'quote'   => '"Advaith Homes made buying our first property completely stress-free. Exceptional communication throughout."',
					'author'  => 'Deepak & Ananya Sharma',
					'role'    => 'First-time buyers, Bangalore',
					'rating'  => 5,
				],
				[
					'variant' => 'testimonial',
					'quote'   => '"We sold our home in 12 days — above asking price. Couldn\'t have asked for a better outcome."',
					'author'  => 'Rajan Pillai',
					'role'    => 'Seller, Kochi',
					'rating'  => 5,
				],
				[
					'variant' => 'testimonial',
					'quote'   => '"The custom build process was managed impeccably. Every detail was handled with care."',
					'author'  => 'Meena & Suresh Nair',
					'role'    => 'Custom build clients',
					'rating'  => 5,
				],
				[
					'variant' => 'testimonial',
					'quote'   => '"Professional, honest, and genuinely invested in finding us the right home — not just any home."',
					'author'  => 'Arjun Menon',
					'role'    => 'Property investor',
					'rating'  => 4,
				],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 4. Stats                                                      -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>4. Stats Cards</h2>
		<div class="ct-label">
			<span class="ct-badge">variant: stat</span>
			<span class="ct-badge">showArrows: false</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'showArrows' => false,
			'items'      => [
				[ 'variant' => 'stat', 'icon' => '🏡', 'stat' => '1,200+', 'stat_label' => 'Homes Sold',       'title' => 'Proven Results',    'text' => 'Successfully completed across Tier 1 and Tier 2 cities.' ],
				[ 'variant' => 'stat', 'icon' => '⭐', 'stat' => '4.9',    'stat_label' => 'Client Rating',    'title' => 'Trusted by Buyers', 'text' => 'Based on 800+ independent reviews.' ],
				[ 'variant' => 'stat', 'icon' => '📅', 'stat' => '15',     'stat_label' => 'Years Experience', 'title' => 'Deep Expertise',    'text' => 'Advising families and investors since 2010.' ],
				[ 'variant' => 'stat', 'icon' => '📍', 'stat' => '8',      'stat_label' => 'City Offices',     'title' => 'Local Presence',    'text' => 'On-the-ground knowledge in every market we operate.' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 5. Mixed variants                                             -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>5. Mixed Variants</h2>
		<div class="ct-label">
			<span class="ct-badge">image-overlay</span>
			<span class="ct-badge">minimal</span>
			<span class="ct-badge">feature-detailed</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'items' => [
				[
					'variant' => 'image-overlay',
					'image'   => get_template_directory_uri() . '/real_data/images/hero-home.jpg',
					'tag'     => 'Featured',
					'title'   => 'Premium Listings',
					'text'    => 'Hand-picked properties in the most sought-after locations.',
				],
				[
					'variant' => 'minimal',
					'title'   => 'No Hidden Fees',
					'text'    => 'Transparent pricing at every step. What we quote is what you pay.',
				],
				[
					'variant'   => 'feature-detailed',
					'icon'      => '🔐',
					'title'     => 'Secure Transactions',
					'text'      => 'Every transaction is handled through regulated legal channels.',
					'checklist' => [ 'RERA compliant', 'Escrow-protected deposits', 'Full documentation support' ],
				],
				[
					'variant' => 'image-overlay',
					'image'   => get_template_directory_uri() . '/real_data/images/hero-home.jpg',
					'tag'     => 'New',
					'title'   => 'Off-Plan Projects',
					'text'    => 'Invest early for the best prices in upcoming developments.',
				],
				[
					'variant'   => 'feature-detailed',
					'icon'      => '📊',
					'title'     => 'Investment Advisory',
					'text'      => 'Data-backed guidance to maximise your return on property investment.',
					'checklist' => [ 'Rental yield analysis', 'Capital growth forecasts', 'Portfolio review' ],
				],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 6. Non-infinite                                               -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>6. Non-infinite — arrows disable at boundaries</h2>
		<div class="ct-label">
			<span class="ct-badge">infiniteLoop: false</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'         => 'feature',
			'infiniteLoop' => false,
			'items'        => [
				[ 'icon' => '🔴', 'title' => 'First Slide',  'text' => 'Previous arrow is disabled at this position.' ],
				[ 'icon' => '🟠', 'title' => 'Second Slide', 'text' => 'Navigate forward with arrows or tap a dot.' ],
				[ 'icon' => '🟡', 'title' => 'Third Slide',  'text' => 'Middle of the list.' ],
				[ 'icon' => '🟢', 'title' => 'Fourth Slide', 'text' => 'Nearly at the end.' ],
				[ 'icon' => '🔵', 'title' => 'Last Slide',   'text' => 'Next arrow is disabled at this position.' ],
			],
		] ); ?>
	</section>

</main>

<?php get_footer(); ?>
