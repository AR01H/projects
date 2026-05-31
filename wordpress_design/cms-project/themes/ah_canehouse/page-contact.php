<?php
/**
 * Template Name: Contact
 */
defined( 'ABSPATH' ) || exit;
get_header();

$settings = ch_get_settings();
$phone    = $settings['phone']        ?? CONTACT_NUMBER;
$email    = $settings['email']        ?? CONTACT_EMAIL;
$address  = $settings['address']      ?? 'Available across the UK';
$whatsapp = $settings['whatsapp']     ?? preg_replace( '/[^0-9]/', '', $phone );
$insta    = $settings['instagram_url'] ?? '';
$fb       = $settings['facebook_url']  ?? '';
$tiktok   = $settings['tiktok_url']    ?? '';
$youtube  = $settings['youtube_url']   ?? '';
$wa_num   = preg_replace( '/[^0-9]/', '', $whatsapp );

// Quick "how can we help" routes
$help = [
	[ 'icon' => '🎪', 'title' => 'Events & Hire',  'desc' => 'Book our live juice stall for weddings, parties & corporate events.', 'url' => home_url( '/events/' ),    'cta' => 'Book an Event' ],
	[ 'icon' => '🤝', 'title' => 'Franchise',       'desc' => 'Bring The Cane House to your city - partner with us.',                'url' => home_url( '/franchise/' ), 'cta' => 'Franchise Info' ],
	[ 'icon' => '🥤', 'title' => 'Our Juices',      'desc' => 'Questions about flavours, sizes or ingredients? We\'re happy to help.', 'url' => home_url( '/our-juices/' ), 'cta' => 'View Menu' ],
];
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<!-- <section class="ch-page-hero ch-page-hero--sugarcane">
	<div class="container">
		<div class="fade-up" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
			<div class="section-tag">Say Hello</div>
			<h1 class="ch-page-hero__title">Get in <em>Touch</em></h1>
			<p class="ch-page-hero__desc">Questions about our juice, booking an event, or a franchise enquiry? We'd love to hear from you - we usually reply within 24 hours.</p>
			<div style="display:flex;gap:1rem;margin-top:2rem;flex-wrap:wrap;justify-content:center;">
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="btn-lime">📞 <?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
				<?php if ( $wa_num ) : ?>
					<a href="<?php echo esc_url( 'https://wa.me/' . $wa_num ); ?>" target="_blank" rel="noopener" class="btn-outline ch-btn-outline-light">💬 WhatsApp</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section> -->

<!-- ── Quick contact cards ──────────────────────────────────────────────────── -->
<!-- <div class="ch-stats-bar" style="background:var(--ch-white);">
	<div class="container">
		<div class="ch-contact-quick">
			<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="ch-cq-card">
					<span class="ch-cq-icon">📞</span>
					<span class="ch-cq-label">Call Us</span>
					<span class="ch-cq-val"><?php echo esc_html( $phone ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( $email ); ?>" class="ch-cq-card">
					<span class="ch-cq-icon">📧</span>
					<span class="ch-cq-label">Email Us</span>
					<span class="ch-cq-val"><?php echo esc_html( $email ); ?></span>
				</a>
			<?php endif; ?>
			<div class="ch-cq-card">
				<span class="ch-cq-icon">📍</span>
				<span class="ch-cq-label">Coverage</span>
				<span class="ch-cq-val"><?php echo esc_html( $address ); ?></span>
			</div>
			<div class="ch-cq-card">
				<span class="ch-cq-icon">🕒</span>
				<span class="ch-cq-label">Hours</span>
				<span class="ch-cq-val">Mon-Sat · 9am-9pm</span>
			</div>
		</div>
	</div>
</div> -->

<!-- ── How can we help ──────────────────────────────────────────────────────── -->
<!-- <section style="background:var(--ch-green-bg);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag">How Can We Help?</div>
			<h2 class="section-title">Pick a <span class="accent">Topic</span></h2>
			<p class="section-body">Point us in the right direction and we'll get back to you faster.</p>
		</div>
		<div class="ch-franchise-why-grid fade-up">
			<?php foreach ( $help as $h ) : ?>
				<div class="ch-fw-card" style="display:flex;flex-direction:column;">
					<div class="ch-fw-icon"><?php echo esc_html( $h['icon'] ); ?></div>
					<h3><?php echo esc_html( $h['title'] ); ?></h3>
					<p style="flex:1;"><?php echo esc_html( $h['desc'] ); ?></p>
					<a href="<?php echo esc_url( $h['url'] ); ?>" class="ch-package-card__cta"><?php echo esc_html( $h['cta'] ); ?> →</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section> -->

<!-- ── Contact form (reuses the shared component) ───────────────────────────── -->
<div class="ch-contact-page-form">
	<?php get_template_part( 'components/contact-section' ); ?>
</div>

<!-- ── Social + CTA ─────────────────────────────────────────────────────────── -->
<!-- <section class="ch-inner-cta">
	<div class="container">
		<div class="ch-inner-cta__box fade-up">
			<h2>Follow the Fresh Juice Journey</h2>
			<p>See our latest events, flavours and behind-the-scenes pressing on social media.</p>
			<div class="ch-contact-socials">
				<?php
				$socials = [
					[ 'url' => $insta,   'label' => 'Instagram', 'icon' => '📸' ],
					[ 'url' => $fb,      'label' => 'Facebook',  'icon' => '👍' ],
					[ 'url' => $tiktok,  'label' => 'TikTok',    'icon' => '🎵' ],
					[ 'url' => $youtube, 'label' => 'YouTube',   'icon' => '▶️' ],
				];
				foreach ( $socials as $soc ) :
					if ( empty( $soc['url'] ) ) continue;
				?>
					<a href="<?php echo esc_url( $soc['url'] ); ?>" target="_blank" rel="noopener" class="ch-contact-social">
						<span><?php echo esc_html( $soc['icon'] ); ?></span> <?php echo esc_html( $soc['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section> -->

</main>
<?php get_footer(); ?>
