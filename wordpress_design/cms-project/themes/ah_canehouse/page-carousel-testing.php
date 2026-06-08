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
	<p class="page-intro">All examples use <code>components/carousels/carousel.php</code> - one component, fully configurable.</p>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 1. Default - dots + arrows, 3 visible, feature cards          -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>1. Feature Cards - Dots + Arrows (default)</h2>
		<div class="ct-label">
			<span class="ct-badge">showDots: true</span>
			<span class="ct-badge">showArrows: true</span>
			<span class="ct-badge">infiniteLoop: true</span>
			<span class="ct-badge">3 / 2 / 1 visible</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'  => 'feature',
			'items' => [
				[ 'icon' => '🌿', 'title' => 'Pure & Natural',   'text' => '100% fresh sugarcane, pressed on the spot with no additives.' ],
				[ 'icon' => '⚡', 'title' => 'Instant Energy',   'text' => 'Natural sugars and electrolytes for a quick, lasting boost.' ],
				[ 'icon' => '💪', 'title' => 'Nutrient Rich',    'text' => 'Potassium, magnesium, and antioxidants in every glass.' ],
				[ 'icon' => '🌍', 'title' => 'Globally Loved',   'text' => 'Enjoyed for thousands of years across continents.' ],
				[ 'icon' => '🎉', 'title' => 'Event Ready',      'text' => 'Weddings, festivals, corporates - we come to you.' ],
				[ 'icon' => '♻️', 'title' => 'Eco Friendly',     'text' => 'Biodegradable cups and zero-waste operation at every event.' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 2. Image Cards - dots only, infinite loop                     -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>2. Image Cards - Dots only, infinite loop</h2>
		<div class="ct-label">
			<span class="ct-badge">showArrows: false</span>
			<span class="ct-badge">showDots: true</span>
			<span class="ct-badge">infiniteLoop: true</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'        => 'image',
			'showArrows'  => false,
			'items'       => [
				[ 'image' => get_template_directory_uri() . '/assets/images/commercial-press.jpg', 'title' => 'Commercial Press',  'subtitle' => 'Stainless steel equipment' ],
				[ 'image' => get_template_directory_uri() . '/assets/images/event-stall.jpg',      'title' => 'Event Stall',        'subtitle' => 'Mobile setup for any event' ],
				[ 'image' => get_template_directory_uri() . '/assets/images/live-press.jpg',       'title' => 'Live Pressing',      'subtitle' => 'Fresh to order on site' ],
				[ 'image' => get_template_directory_uri() . '/assets/images/commercial-press.jpg', 'title' => 'Press Closeup',      'subtitle' => 'Precision engineering' ],
				[ 'image' => get_template_directory_uri() . '/assets/images/event-stall.jpg',      'title' => 'Festival Setup',     'subtitle' => 'High-volume outdoor catering' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 3. Arrows only - no dots, step=1 (item-by-item)              -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>3. Arrows only - scrollStep: 1 (one card at a time)</h2>
		<div class="ct-label">
			<span class="ct-badge">showDots: false</span>
			<span class="ct-badge">showArrows: true</span>
			<span class="ct-badge">scrollStep: 1</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'        => 'step',
			'showDots'    => false,
			'scrollStep'  => 1,
			'items'       => [
				[ 'step' => '01', 'icon' => '📋', 'title' => 'Fill the Form',     'text' => 'Tell us about your event - date, location, expected footfall.' ],
				[ 'step' => '02', 'icon' => '💬', 'title' => 'We Get in Touch',   'text' => 'Our team contacts you within 24 hours to confirm availability.' ],
				[ 'step' => '03', 'icon' => '🤝', 'title' => 'Lock the Booking',  'text' => 'Pay a small deposit to secure your date and chosen package.' ],
				[ 'step' => '04', 'icon' => '🚐', 'title' => 'We Arrive & Setup', 'text' => 'Our crew arrives early, sets up, and is ready before your guests.' ],
				[ 'step' => '05', 'icon' => '🥤', 'title' => 'Serve & Enjoy',     'text' => 'Fresh juice flows all event long - zero effort on your end.' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 4. Autoplay - no pause on hover                               -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>4. Autoplay - 3 s interval, no pause on hover</h2>
		<div class="ct-label">
			<span class="ct-badge">autoplay: true</span>
			<span class="ct-badge">autoplaySpeed: 3000</span>
			<span class="ct-badge">pauseOnHover: false</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'         => 'feature',
			'autoplay'     => true,
			'autoplaySpeed'=> 3000,
			'pauseOnHover' => false,
			'items'        => [
				[ 'icon' => '💒', 'title' => 'Weddings',   'text' => 'Live fresh juice at your reception, Mehndi, or Sangeet ceremony.' ],
				[ 'icon' => '🏛️', 'title' => 'Corporate',  'text' => 'Perfect for office wellness days and team celebrations.' ],
				[ 'icon' => '🎪', 'title' => 'Festivals',  'text' => 'High-volume catering for outdoor events and markets.' ],
				[ 'icon' => '🎓', 'title' => 'Education',  'text' => 'Graduation parties, school fairs, and college events.' ],
				[ 'icon' => '🎂', 'title' => 'Birthdays',  'text' => 'Make celebrations special with fresh natural juice.' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 5. Card variant - testimonials                                -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>5. Card Variants - Testimonials</h2>
		<div class="ct-label">
			<span class="ct-badge">variant: testimonial</span>
			<span class="ct-badge">cardsPerView: 3</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'items' => [
				[
					'variant' => 'testimonial',
					'quote'   => '"Absolutely incredible service. Every guest at our wedding was amazed by the freshness."',
					'author'  => 'Priya & Rahul',
					'role'    => 'Wedding, Mayfair',
					'rating'  => 5,
				],
				[
					'variant' => 'testimonial',
					'quote'   => '"We had Canehouse at our annual corporate away-day. The team was professional and the juice was brilliant."',
					'author'  => 'Sarah Mitchell',
					'role'    => 'Events Manager, TechCorp',
					'rating'  => 5,
				],
				[
					'variant' => 'testimonial',
					'quote'   => '"Best addition to our festival stall lineup. Long queues but very happy customers!"',
					'author'  => 'Omar Al-Farsi',
					'role'    => 'Festival Organiser',
					'rating'  => 4,
				],
				[
					'variant' => 'testimonial',
					'quote'   => '"Booked for a birthday party of 80 guests - everything ran smoothly and tasted amazing."',
					'author'  => 'Lakshmi Patel',
					'role'    => 'Private Client',
					'rating'  => 5,
				],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 6. Card variant - stats                                       -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>6. Card Variants - Stats</h2>
		<div class="ct-label">
			<span class="ct-badge">variant: stat</span>
			<span class="ct-badge">showArrows: false</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'showArrows' => false,
			'items'      => [
				[ 'variant' => 'stat', 'icon' => '🎉', 'stat' => '500+',  'stat_label' => 'Events Catered',   'title' => 'Proven Track Record',    'text' => 'From intimate parties to 2,000-person festivals.' ],
				[ 'variant' => 'stat', 'icon' => '🥤', 'stat' => '1M+',   'stat_label' => 'Glasses Served',   'title' => 'Real Fresh Juice',        'text' => 'Every glass pressed fresh on the day.' ],
				[ 'variant' => 'stat', 'icon' => '⭐', 'stat' => '4.9',   'stat_label' => 'Average Rating',   'title' => 'Consistently Excellent',  'text' => 'Based on 300+ verified reviews.' ],
				[ 'variant' => 'stat', 'icon' => '📍', 'stat' => '12',    'stat_label' => 'Cities Covered',   'title' => 'Nationwide Reach',        'text' => 'Available across major UK cities.' ],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 7. Mixed card variants                                        -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>7. Mixed Variants - image-overlay + minimal + feature-detailed</h2>
		<div class="ct-label">
			<span class="ct-badge">mixed variants</span>
			<span class="ct-badge">cardsPerView: 3</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'items' => [
				[
					'variant' => 'image-overlay',
					'image'   => get_template_directory_uri() . '/assets/images/commercial-press.jpg',
					'tag'     => 'Featured',
					'title'   => 'Our Equipment',
					'text'    => 'Premium stainless steel commercial-grade presses.',
				],
				[
					'variant' => 'minimal',
					'title'   => 'Clean. Fresh. Fast.',
					'text'    => 'We arrive, set up, and serve - all within 30 minutes of your event starting.',
				],
				[
					'variant'   => 'feature-detailed',
					'icon'      => '🛡️',
					'title'     => 'Fully Insured',
					'text'      => 'All equipment and operators are fully covered for your peace of mind.',
					'checklist' => [ 'Public liability cover', 'Food hygiene certified', 'Risk-assessed operations' ],
				],
				[
					'variant' => 'image-overlay',
					'image'   => get_template_directory_uri() . '/assets/images/event-stall.jpg',
					'tag'     => 'Live',
					'title'   => 'Events Stall',
					'text'    => 'Fully branded mobile setup for any venue.',
				],
				[
					'variant'   => 'feature-detailed',
					'icon'      => '🌿',
					'title'     => 'Sustainably Sourced',
					'text'      => 'Sugarcane from responsible farms. Biodegradable servingware.',
					'checklist' => [ 'Farm-to-glass supply chain', 'Zero single-use plastic', 'Carbon-neutral delivery' ],
				],
			],
		] ); ?>
	</section>

	<!-- ══════════════════════════════════════════════════════════════ -->
	<!-- 8. Non-infinite - arrows disabled at edges                   -->
	<!-- ══════════════════════════════════════════════════════════════ -->
	<section class="ct-section">
		<h2>8. Non-infinite - arrows disable at start / end</h2>
		<div class="ct-label">
			<span class="ct-badge">infiniteLoop: false</span>
		</div>
		<?php get_template_part( 'components/carousels/carousel', null, [
			'type'         => 'feature',
			'infiniteLoop' => false,
			'items'        => [
				[ 'icon' => '🔴', 'title' => 'First Slide',  'text' => 'Prev arrow is disabled here.' ],
				[ 'icon' => '🟠', 'title' => 'Second Slide', 'text' => 'Navigate with arrows or dots.' ],
				[ 'icon' => '🟡', 'title' => 'Third Slide',  'text' => 'Middle of the list.' ],
				[ 'icon' => '🟢', 'title' => 'Fourth Slide', 'text' => 'Almost at the end.' ],
				[ 'icon' => '🔵', 'title' => 'Last Slide',   'text' => 'Next arrow is disabled here.' ],
			],
		] ); ?>
	</section>

</main>

<?php get_footer(); ?>
