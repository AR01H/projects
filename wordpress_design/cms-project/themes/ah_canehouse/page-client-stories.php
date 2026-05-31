<?php
/**
 * Template Name: Client Stories
 */
defined( 'ABSPATH' ) || exit;
get_header();

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? CONTACT_NUMBER;
$reviews  = ch_get_reviews( 12 );
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<section class="ch-page-hero">
	<div class="container">
		<div class="fade-up" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
			<div class="section-tag">Real People, Real Results</div>
			<h1 class="ch-page-hero__title">Client <em>Stories</em></h1>
			<p class="ch-page-hero__desc">
				From Eid gatherings to corporate wellness days, see what our customers are saying about The Cane House experience.
			</p>
		</div>
	</div>
</section>

<!-- ── Trust stats ──────────────────────────────────────────────────────────── -->
<div class="ch-stats-bar">
	<div class="container">
		<div class="ch-stats-grid">
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">★ 5.0</span>
				<span class="ch-stat-label">Average Rating</span>
			</div>
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">500+</span>
				<span class="ch-stat-label">Happy Customers</span>
			</div>
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">200+</span>
				<span class="ch-stat-label">Events Served</span>
			</div>
			<div class="ch-stat-item fade-up">
				<span class="ch-stat-num">100%</span>
				<span class="ch-stat-label">Would Recommend</span>
			</div>
		</div>
	</div>
</div>

<!-- ── Reviews Masonry Grid ─────────────────────────────────────────────────── -->
<?php if ( ! empty( $reviews ) ) : ?>
<section style="background:var(--ch-green-bg);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">What They Said</div>
			<h2 class="section-title">Customer <span class="accent">Reviews</span></h2>
		</div>
		<div class="ch-stories-grid">
			<?php foreach ( $reviews as $i => $r ) :
				$r        = (array) $r;
				$name     = esc_html( $r['author_name'] ?? 'Happy Customer' );
				$location = esc_html( $r['location']    ?? 'Verified Customer' );
				$text     = esc_html( $r['review_text'] ?? '' );
				$rating   = (float) ( $r['rating'] ?? 5.0 );
				$avatar   = 'https://i.pravatar.cc/120?u=' . ( $i + 20 );
			?>
				<div class="ch-story-card fade-up">
					<div class="ch-story-card__header">
						<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo $name; ?>" class="ch-story-card__avatar" loading="lazy">
						<div>
							<div class="ch-story-card__name"><?php echo $name; ?></div>
							<div class="ch-story-card__role"><?php echo $location; ?></div>
						</div>
						<div class="ch-story-card__stars">
							<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
								<span style="color:<?php echo $s <= $rating ? '#f5b800' : 'rgba(0,0,0,0.15)'; ?>">★</span>
							<?php endfor; ?>
						</div>
					</div>
					<p class="ch-story-card__text"><?php echo $text ?: '"Absolutely incredible experience. Fresh, natural and delicious!"'; ?></p>
					<div class="ch-story-card__footer">
						<span class="ch-story-card__badge">✓ Verified Review</span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ── Event Showcases ───────────────────────────────────────────────────────── -->
<section style="background:var(--ch-white);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">Our Events</div>
			<h2 class="section-title">We've Been <span class="accent">Everywhere</span></h2>
			<p class="section-body">From intimate gatherings to large-scale events - The Cane House delivers every time.</p>
		</div>
		<div class="ch-showcase-events">
			<div class="ch-event-showcase fade-up">
				<div class="ch-event-showcase__img">
					<img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=700&h=460&q=80" alt="Wedding" loading="lazy">
					<div class="ch-event-showcase__badge">💒 Weddings</div>
				</div>
				<div class="ch-event-showcase__body">
					<h3>Wedding & Asian Celebrations</h3>
					<p>The Cane House has become the go-to live juice stall for Desi weddings, Mehndi nights, Walima receptions and Eid gatherings across the UK. A healthy, cultural touch your guests will never forget.</p>
					<ul class="ch-event-list">
						<li>Unlimited servings packages available</li>
						<li>Traditional and modern flavour blends</li>
						<li>Branded stall setup to match your theme</li>
						<li>Fully certified and insured</li>
					</ul>
					<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime" style="margin-top:1.5rem;display:inline-flex;">Enquire for Your Wedding →</a>
				</div>
			</div>

			<div class="ch-event-showcase ch-event-showcase--reverse fade-up">
				<div class="ch-event-showcase__img">
					<img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=700&h=460&q=80" alt="Corporate event" loading="lazy">
					<div class="ch-event-showcase__badge">🏢 Corporate</div>
				</div>
				<div class="ch-event-showcase__body">
					<h3>Corporate Events & Wellness Days</h3>
					<p>Refreshing, healthy, and genuinely impressive. The Cane House adds a unique, memorable element to your product launch, office wellness day, or exhibition stand.</p>
					<ul class="ch-event-list">
						<li>Branded signage and uniform available</li>
						<li>Bulk servings for large conferences</li>
						<li>Healthy alternative to sugary drinks</li>
						<li>Invoice & formal quote provided</li>
					</ul>
					<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime" style="margin-top:1.5rem;display:inline-flex;">Enquire for Corporate →</a>
				</div>
			</div>

			<div class="ch-event-showcase fade-up">
				<div class="ch-event-showcase__img">
					<img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=700&h=460&q=80" alt="Private party" loading="lazy">
					<div class="ch-event-showcase__badge">🎉 Parties</div>
				</div>
				<div class="ch-event-showcase__body">
					<h3>Private Parties & Festivals</h3>
					<p>Birthdays, garden parties, community festivals, or street fairs - our live-press stall brings genuine excitement and freshness to any gathering, big or small.</p>
					<ul class="ch-event-list">
						<li>Available across the whole UK</li>
						<li>Flexible packages from 50 to 500+ guests</li>
						<li>All ages love it - kids and adults alike</li>
						<li>Quick setup and pack-down</li>
					</ul>
					<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime" style="margin-top:1.5rem;display:inline-flex;">Enquire for a Party →</a>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ── CTA ───────────────────────────────────────────────────────────────────── -->
<section class="ch-inner-cta">
	<div class="container">
		<div class="ch-inner-cta__box fade-up">
			<h2>Add Your Story to Ours</h2>
			<p>Book The Cane House for your next event and give your guests something they'll be talking about for years.</p>
			<div class="ch-inner-cta__btns">
				<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime">📞 Get a Quote</a>
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="btn-outline" style="border-color:rgba(255,255,255,0.4);color:#fff;"><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

</main>
<?php get_footer(); ?>
