<?php
defined( 'ABSPATH' ) || exit;
get_header();

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? CONTACT_NUMBER;
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ──────────────────────────────────────────────────────────────────── -->
<section class="about-hero">
	<div class="container">
		<div class="about-hero-inner fade-up">
			<div class="section-tag">About Us</div>
			<h1 class="section-title">The Story Behind <span class="accent">The Cane House</span></h1>
			<p class="section-body">
				We believe in the power of nature's simplest gifts. At The Cane House, we're dedicated to bringing the freshest, most natural sugarcane juice experience to the UK - pressed live, served cool, with nothing added.
			</p>
		</div>
	</div>
</section>

<!-- ── Mission / Vision / Values Cards ───────────────────────────────────────── -->
<section class="about-mission">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">What Drives Us</div>
			<h2 class="section-title">Our <span class="accent">Foundation</span></h2>
		</div>
		<div class="mission-grid">
			<div class="mission-card fade-left">
				<div class="mission-icon">🎯</div>
				<h3>Our Mission</h3>
				<p>To deliver 100% natural, freshly-pressed sugarcane juice that brings health, happiness, and wholesome refreshment to every customer we serve.</p>
			</div>
			<div class="mission-card fade-up">
				<div class="mission-icon">🌿</div>
				<h3>Our Vision</h3>
				<p>To become the UK's most trusted brand for fresh, natural, live-pressed sugarcane juice - setting the standard for sustainability and quality.</p>
			</div>
			<div class="mission-card fade-right">
				<div class="mission-icon">💚</div>
				<h3>Our Values</h3>
				<p>Freshness, integrity, sustainability, and community. We stand behind every drop of juice we serve, with a commitment to natural goodness.</p>
			</div>
		</div>
	</div>
</section>

<!-- ── Team ──────────────────────────────────────────────────────────────────── -->
<?php
$team = ch_get_team_members();
if ( ! empty( $team ) ) : ?>
<section class="about-team">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">Meet the Team</div>
			<h2 class="section-title">The People Behind <span class="accent">The Cane House</span></h2>
		</div>
		<div class="team-grid">
			<?php foreach ( $team as $member ) :
				$member = (array) $member;
				$name      = esc_html( $member['name'] ?? '' );
				$role      = esc_html( $member['role'] ?? '' );
				$bio       = wp_kses_post( $member['bio'] ?? '' );
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

<!-- ── Values + Promise ───────────────────────────────────────────────────────── -->
<section class="about-values">
	<div class="container">
		<div class="values-content">
			<div class="fade-left">
				<div class="section-tag">Why We Do It</div>
				<h2 class="section-title">Our Commitment to <span class="accent">Quality</span></h2>
				<p class="section-body">Every cup of The Cane House juice is made with intention, care, and a deep respect for the sugarcane plant. We don't cut corners because our customers deserve nothing but the best.</p>
				<ul class="values-list">
					<li>✓ Pressed fresh to order, never pre-made</li>
					<li>✓ 100% natural ingredients, no additives</li>
					<li>✓ Fully certified and insured for events</li>
					<li>✓ Sustainable practices throughout</li>
					<li>✓ Community-focused and locally minded</li>
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

<!-- ── Story / Timeline ───────────────────────────────────────────────────────── -->
<section style="background:var(--ch-white);padding:5rem 2rem;">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">Our Journey</div>
			<h2 class="section-title">Beyond the <span class="accent">Juice</span></h2>
			<p class="section-body" style="margin-inline:auto;">Sugarcane has been cherished for over 2,000 years. At The Cane House, we bring that ancient goodness to every cup we press - fresh, natural, and live.</p>
		</div>
		<div class="ch-story-facts fade-up" style="max-width:900px;margin-inline:auto;">
			<div class="ch-story-fact">
				<div class="ch-fact-icon">🍬</div>
				<div class="ch-fact-title">Sugar &amp; Jaggery</div>
				<div class="ch-fact-desc">Traditional sweeteners from sugarcane</div>
			</div>
			<div class="ch-story-fact">
				<div class="ch-fact-icon">🫙</div>
				<div class="ch-fact-title">Molasses</div>
				<div class="ch-fact-desc">Rich syrup with deep mineral content</div>
			</div>
			<div class="ch-story-fact">
				<div class="ch-fact-icon">⛽</div>
				<div class="ch-fact-title">Ethanol</div>
				<div class="ch-fact-desc">Clean-burning biofuel from fermentation</div>
			</div>
			<div class="ch-story-fact">
				<div class="ch-fact-icon">🌱</div>
				<div class="ch-fact-title">Eco Fibre</div>
				<div class="ch-fact-desc">Biodegradable - fully sustainable crop</div>
			</div>
		</div>
	</div>
</section>

<!-- ── Contact CTA ────────────────────────────────────────────────────────────── -->
<section style="background:var(--ch-green-deep);padding:5rem 2rem;text-align:center;">
	<div class="container">
		<div class="fade-up">
			<div class="section-tag" style="color:var(--ch-lime);justify-content:center;margin-inline:auto;">
				<span style="background:var(--ch-lime);width:28px;height:2px;border-radius:1px;display:block;"></span>
				Say Hello
			</div>
			<h2 class="section-title" style="color:var(--ch-white);">Get in <span class="accent" style="color:var(--ch-lime);">Touch</span></h2>
			<p class="section-body" style="color:rgba(255,255,255,0.7);margin-inline:auto;margin-bottom:2.5rem;">Have a question, want to book us for an event, or interested in franchise opportunities? We'd love to hear from you.</p>
			<div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
				<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime">Send a Message 🌿</a>
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="btn-outline" style="border-color:rgba(255,255,255,0.4);color:#fff;">📞 <?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

</main>

<?php get_footer(); ?>
