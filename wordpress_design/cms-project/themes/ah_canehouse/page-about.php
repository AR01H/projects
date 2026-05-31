<?php
/**
 * Template Name: About Us
 * Slug: /about/
 *
 * The business story — origin, mission, team, equipment, values.
 */
defined( 'ABSPATH' ) || exit;
get_header();

// Mission / Vision / Values
$mvv_raw = get_option( 'ch_about_mvv', [] );
if ( is_string( $mvv_raw ) ) $mvv_raw = json_decode( $mvv_raw, true ) ?: [];
$mvv = ! empty( $mvv_raw ) ? $mvv_raw : [
	[ 'icon' => '🎯', 'title' => 'Our Mission',  'text' => 'To deliver 100% natural, freshly-pressed sugarcane juice that brings health, happiness, and wholesome refreshment to every customer we serve.' ],
	[ 'icon' => '🌿', 'title' => 'Our Vision',   'text' => 'To become the UK\'s most trusted brand for fresh, natural, live-pressed sugarcane juice - setting the standard for sustainability and quality.' ],
	[ 'icon' => '💚', 'title' => 'Our Values',   'text' => 'Freshness, integrity, sustainability, and community. We stand behind every drop of juice we serve, with a commitment to natural goodness.' ],
];

// Quality commitment
$quality_raw = get_option( 'ch_about_quality', [] );
if ( is_string( $quality_raw ) ) $quality_raw = json_decode( $quality_raw, true ) ?: [];
$quality_items = ! empty( $quality_raw ) ? $quality_raw : [
	'Pressed fresh to order, never pre-made',
	'100% natural ingredients, no additives',
	'Fully certified and insured for events',
	'Sustainable practices throughout',
	'Community-focused and locally minded',
];

$team = ch_get_team_members();
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ──────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/page-hero', null, [
	'tag'        => 'About Us',
	'heading'    => 'The Story Behind <em>The Cane House</em>',
	'desc'       => 'We believe in the power of nature\'s simplest gifts. Live-pressed, served cool, with nothing added — and a whole lot of heart behind every glass.',
	'modifier'   => 'ch-page-hero--sugarcane',
	'btn1_label' => 'Our Origin Story',
	'btn1_url'   => '#our-story',
	'btn1_icon'  => '🌿',
	'btn2_label' => 'Meet the Team',
	'btn2_url'   => '#team',
	'btn2_class' => 'btn-outline ch-btn-outline-light',
] ); ?>

<!-- ── Why We Started ────────────────────────────────────────────────────────── -->
<div id="our-story">
	<?php get_template_part( 'components/about-origin' ); ?>
</div>

<!-- ── Mission / Vision / Values ─────────────────────────────────────────────── -->
<section class="about-mission">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">What Drives Us</div>
			<h2 class="section-title">Our <span class="accent">Foundation</span></h2>
		</div>
		<div class="mission-grid">
			<?php foreach ( $mvv as $i => $card ) :
				$card  = (array) $card;
				$anims = [ 'fade-left', 'fade-up', 'fade-right' ];
				$cls   = $anims[ $i % 3 ] ?? 'fade-up';
			?>
				<div class="mission-card <?php echo $cls; ?>">
					<div class="mission-icon"><?php echo esc_html( $card['icon'] ?? '🌿' ); ?></div>
					<h3><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
					<p><?php echo esc_html( $card['text'] ?? '' ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ── Team ──────────────────────────────────────────────────────────────────── -->
<?php if ( ! empty( $team ) ) : ?>
<section id="team" class="about-team">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">Meet the Team</div>
			<h2 class="section-title">The People Behind <span class="accent">The Cane House</span></h2>
		</div>
		<div class="team-grid">
			<?php foreach ( $team as $member ) :
				$member    = (array) $member;
				$name      = esc_html( $member['name']     ?? '' );
				$role      = esc_html( $member['role']     ?? '' );
				$bio       = wp_kses_post( $member['bio']  ?? '' );
				$image_url = esc_url( $member['image_url'] ?? '' );
			?>
				<div class="team-card fade-up">
					<div class="team-image">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>" loading="lazy">
						<?php else : ?>
							<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:4rem;">👤</div>
						<?php endif; ?>
					</div>
					<div class="team-info">
						<h3><?php echo $name; ?></h3>
						<?php if ( $role ) : ?><span class="team-role"><?php echo $role; ?></span><?php endif; ?>
						<?php if ( $bio )  : ?><p class="team-bio"><?php echo $bio; ?></p><?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ── About Gallery ─────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/gallery-strip', null, [
	'tag'      => 'Behind the Scenes',
	'title'    => 'Our Equipment, <span class="accent">Our Craft</span>',
	'body'     => 'The machines, the setup, the ingredients — everything that goes into every perfect glass.',
	'modifier' => 'ch-gstrip--about',
	'id'       => 'gstrip-about',
	'bg'       => 'var(--ch-white)',
	'images'   => [
		[ 'src' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Commercial Press',    'desc' => 'Stainless steel, purpose-built machine' ],
		[ 'src' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Event Stall',         'desc' => 'Mobile setup, ready in 30 minutes' ],
		[ 'src' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Live Pressing',        'desc' => 'Fresh to order, every single time' ],
		[ 'src' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Fresh Ingredients',    'desc' => 'Whole stalks, ginger, lemon, mint' ],
		[ 'src' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'First Glass Served',  'desc' => 'The moment it all comes together' ],
		[ 'src' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Our Team',            'desc' => 'Passionate about every pour' ],
	],
] ); ?>

<!-- ── Quality / Promise ─────────────────────────────────────────────────────── -->
<section class="about-values">
	<div class="container">
		<div class="values-content">
			<div class="fade-left">
				<div class="section-tag">Why We Do It</div>
				<h2 class="section-title">Our Commitment to <span class="accent">Quality</span></h2>
				<p class="section-body">Every cup of The Cane House juice is made with intention, care, and a deep respect for the sugarcane plant. We don't cut corners because our customers deserve nothing but the best.</p>
				<ul class="values-list">
					<?php foreach ( $quality_items as $item ) : ?>
						<li>✓ <?php echo esc_html( is_array( $item ) ? ( $item['text'] ?? '' ) : $item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="fade-right" style="display:flex;align-items:center;justify-content:center;">
				<div class="promise-card">
					<span class="promise-icon">🌱</span>
					<div class="promise-title">Our Promise</div>
					<div class="promise-sub">Pressed Fresh. Served Cool.</div>
					<div class="promise-tags">
						<div class="promise-tag">No added sugar</div>
						<div class="promise-tag">No preservatives</div>
						<div class="promise-tag">Pure, natural refreshment</div>
						<div class="promise-tag">Pressed live at every order</div>
						<div class="promise-tag">Served chilled, always fresh</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ── Events preview ────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/events-preview', null, [
	'tag'     => 'Events & Hire',
	'heading' => 'Need Us at Your <span class="accent">Event?</span>',
	'body'    => 'From weddings to corporate events, we bring freshly-pressed sugarcane juice live to your guests.',
] ); ?>

<!-- ── CTA ────────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/cta-section', null, [
	'tag'        => 'Work With Us',
	'heading'    => 'Let\'s Do Something <span class="accent" style="color:var(--ch-lime);">Amazing</span>',
	'body'       => 'Book us for your next event, or take the leap and bring The Cane House to your city with a franchise.',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise →',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
